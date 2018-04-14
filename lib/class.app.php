<?php
class App
{
	protected $publicPath = "";
	protected $urlBase = "";
	protected $router;
	protected $params;
	protected $paramFile;
	protected $tmpPath;

	public function __construct($publicPath, $urlBase)
	{
		$this->publicPath = $publicPath;
		$this->urlBase = $urlBase;
		$this->params = [
			'site' => [
				'name' => 'test',
				'description' => 'a longer test'
			],
			'public dir' => 'public'
		];
		$this->paramFile = 'params.yaml';
		$this->tmpPath = 'tmp' . DIRECTORY_SEPARATOR;
		FFRouter::init($publicPath, $urlBase);
	}

	public function init()
	{
		$this->loadParams();
		if ($path = FFRouter::matchRoute()) {
			// adds trailing slash
			if (substr($path, -1) != DIRECTORY_SEPARATOR) {
				$this->redirectToRoute($path . DIRECTORY_SEPARATOR);
			}
			// include personnal php scripts
			foreach (glob("inc/*.php") as $fileName) {
				include_once $fileName;
			}
			// show the page
			$page = new Page($path);
			$page->init();
			$page->list_recursive($page->getRenderLevel(), false, $page->getIgnored());
			$page->sort();
			$page->show();
		} else {
			$this->showNotFound();
		}
	}

	function showNotFound()
	{
		$page = new Page($this->publicPath . DIRECTORY_SEPARATOR . '404' . DIRECTORY_SEPARATOR);
		$page->init();
		$page->list_recursive(0);
		$page->show();
	}

	public function loadParams()
	{
		$paramCache = $this->tmpPath . $this->paramFile . '.cache';
		if (is_file($this->paramFile)) {
			if (is_file($paramCache) && (filemtime($this->paramFile) <= filemtime($paramCache)))
				$this->params = unserialize(file_get_contents($paramCache));
			else {
				$this->params = Spyc::YAMLLoad($this->paramFile);
				$this->cacheParams();
			}
		}
	}

	public function cacheParams()
	{
		if (!is_dir($this->tmpPath)) {
			// dir doesn't exist, make it
			mkdir($this->tmpPath, 0777, true);
		}
		file_put_contents($this->tmpPath . $this->paramFile . '.cache', serialize($this->params));
	}

	public function redirectTo($url)
	{
		header("Location: $url");
	}

	public function redirectToRoute($path)
	{
		$url = FFRouter::genUrl($path);
		$this->redirectTo($url);
	}
}
?>