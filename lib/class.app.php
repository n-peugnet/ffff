<?php
class App
{
	protected $publicPath = "";
	protected $urlBase = "";
	protected $router;

	public function __construct($publicPath, $urlBase)
	{
		$this->publicPath = $publicPath;
		$this->urlBase = $urlBase;
		$this->router = new FFRouter($publicPath, $urlBase);
	}

	public function init()
	{
		if ($path = $this->router->matchRoute()) {
			// include personnal php scripts
			foreach (glob("inc/*.php") as $fileName) {
				include_once $fileName;
			}
			// show the page
			$page = new Page($path);
			$page->init($this->router);
			$page->list(1, false);
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
		$page->list(0);
		$page->show();
	}
}
?>