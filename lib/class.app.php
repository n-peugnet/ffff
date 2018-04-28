<?php
class App
{
	protected $publicPath = "";
	protected $urlBase = "";
	protected $router;
	protected $params;

	const PARAM_FILE = 'params.yaml';
	const DEFAULT_LAYOUT = 'default';

	public function __construct($publicPath, $urlBase)
	{
		$this->publicPath = $publicPath;
		$this->urlBase = $urlBase;
		$this->params = new Params([
			'site' => [
				'name' => 'test',
				'description' => 'a longer test'
			],
			'public dir' => 'public'
		]);
		FFRouter::init($publicPath, $urlBase);
	}

	public function init()
	{
		$this->params->load(self::PARAM_FILE);
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