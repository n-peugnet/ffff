<?php
class FFRouter
{
	protected $publicPath = "";
	protected $basePath = "";
	protected $routes = [];
	protected $escapeChars = [
		["dir" => " ", "url" => "-", ],
		["dir" => "'", "url" => "-", ]
	];

	public function __construct($publicPath = "public", $basePath = "")
	{
		$this->publicPath = $publicPath;
		$this->basePath = $basePath;
		$dir = new Dir($publicPath . DIRECTORY_SEPARATOR);
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
		$this->routes[$routePath] = $dir->getPath();
		foreach ($dir->getListDirs() as $subDir) {
			$this->mapRoutes($subDir, $routePath);
		}
	}

	public function getRoutes()
	{
		return $this->routes;
	}

	public function staticFilesBasePath()
	{
		return $this->basePath . '/' . $this->publicPath . '/';
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
			$route = $this->genStaticFileUrl($path);
		}
		
		// prepend base path to route url again
		$url = $this->basePath . $route;
		return $url;
	}

	public function genStaticFileUrl($path)
	{
		$path = '/' . str_replace(DIRECTORY_SEPARATOR, '/', $path); // replace '\' with '/' if on windows
		$path = rawurlencode(utf8_encode($path)); // replace special characters such as accentued chars
		$path = str_replace("%2F", '/', $path); // replace '/' html notation with the normal '/' char
		return $path;
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