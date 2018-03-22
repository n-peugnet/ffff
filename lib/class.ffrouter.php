<?php
class FFRouter
{
	protected $publicPath = "";
	protected $basePath = "";

	public function __construct($publicPath = "public", $basePath = "")
	{
		$this->publicPath = $publicPath;
		$this->basePath = $basePath;
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

	public function matchRoute()
	{
		// removes the basePath
		$uri = substr($this->uri(), strlen($this->basePath));

		// strip url parameters
		if (($strpos = strpos($uri, '?')) !== false) {
			$uri = substr($uri, 0, $strpos);
		}
		$path = str_replace('/', DIRECTORY_SEPARATOR, $uri); // replace '\' with '/' if on windows
		$path = $this->publicPath . $path;
		$return = is_dir($path) ? $path : false;
		return $return;
	}

	public function genUrl($path)
	{
		// if the path leads to a directory
		if (substr($path, -1) == DIRECTORY_SEPARATOR) {
			// removes the basePath
			$path = $this->pubRelativePath($path);
		}
		$path = str_replace(DIRECTORY_SEPARATOR, '/', $path); // replace '\' with '/' if on windows
		$path = rawurlencode(utf8_encode($path)); // replace special characters such as accentued chars
		$path = str_replace("%2F", '/', $path); // replace '/' html notation with the normal '/' char
		return $this->basePath . '/' . $path;
	}

	public function pubRelativePath($path)
	{
		return substr($path, strlen($this->publicPath) + 1);
	}

	protected function uri()
	{
		return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
	}
}

?>