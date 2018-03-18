<?php
class FFRouter
{
	protected $basePath = "";
	protected $routes = [];

	public function __construct($path, $basePath = "")
	{
		$this->basePath = $basePath;
		$dir = new Dossier("", $path);
		$dir->listage(true, true);
		$this->mapRoutes($dir);
	}

	/**
	 * @param Dossier $dir
	 * @param string $path
	 */
	public function mapRoutes($dir, $path = "")
	{
		$routePath = $path . $dir->getName() . '/';
		if (!$dir->isEmpty()) {
			$this->routes[$routePath] = $dir->getPath();
			foreach ($dir->getListSubDir() as $subDir) {
				$this->mapRoutes($subDir, $routePath);
			}
		}
	}

	public function getRoutes()
	{
		return $this->routes;
	}

	public function setBesPath($str)
	{
		return $this->basePath = $str;
	}

	public function matchRoute()
	{
		$uri = substr($this->uri(), strlen($this->basePath));

		// strip url parameters
		if (($strpos = strpos($uri, '?')) !== false) {
			$uri = substr($uri, 0, $strpos);
		}
		$path = isset($this->routes[$uri]) ? $this->routes[$uri] : false;
		return $path;
	}

	protected function uri()
	{
		return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
	}

}

?>