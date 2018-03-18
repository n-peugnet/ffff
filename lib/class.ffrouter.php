<?php
class FFRouter
{
	protected $basePath = "";
	protected $escapeChars = [];
	protected $routes = [];

	public function __construct($path, $basePath = "")
	{
		$this->basePath = $basePath;
		$this->escapeChars = [
			["dir" => " ", "url" => "-", ],
			["dir" => "'", "url" => "-", ]
		];
		$dir = new Dir($path . DIRECTORY_SEPARATOR);
		$dir->list(true, true);
		$this->mapRoutes($dir);
	}

	/**
	 * @param Dir $dir
	 * @param string $path
	 */
	public function mapRoutes($dir, $path = "")
	{
		$routePath = $path . $dir->getName() . '/';
		$routePath = $this->escapeRoute($routePath);
		if (!$dir->isEmpty()) {
			$this->routes[$routePath] = $dir->getPath();
			foreach ($dir->getListDirs() as $subDir) {
				$this->mapRoutes($subDir, $routePath);
			}
		}
	}

	public function getRoutes()
	{
		return $this->routes;
	}

	public function getBasePath()
	{
		return $this->basePath;
	}

	public function setBasePath($str)
	{
		return $this->basePath = $str;
	}

	public function setEscapeChars($arrayChars)
	{

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

	public function genUrl($path)
	{
		// Check if named route exists
		$route = array_search($path, $this->routes);
		if ($route === false) {
			throw new \Exception("Route '{$path}' does not exist.");
		}
		
		// prepend base path to route url again
		$url = $this->basePath . $route;
		return $url;
	}

	protected function escapeRoute($route)
	{
		$route = Diacritics::remove($route);
		$route = strtolower($route);
		$route = str_replace(array_column($this->escapeChars, "dir"), array_column($this->escapeChars, "url"), $route);
		return $route;
	}

	protected function uri()
	{
		return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
	}

}

?>