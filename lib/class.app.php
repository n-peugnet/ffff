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
		$this->router = new FFRouter($publicPath, $urlBase);
		$this->params = [
			'site' => [
				'name' => 'test',
				'description' => 'a longer test'
			],
			'public dir' => 'public'
		];
		$this->paramFile = 'params.yaml';
		$this->tmpPath = 'tmp' . DIRECTORY_SEPARATOR;
	}

	public function init()
	{
		$this->loadParams();
		if ($path = $this->router->matchRoute()) {
			// include personnal php scripts
			foreach (glob("inc/*.php") as $fileName) {
				include_once $fileName;
			}
			// show the page
			$page = new Page($path);
			$page->init($this->router);
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
		$page->init($this->router);
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
}
?>