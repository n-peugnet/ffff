<?php
class FFRouter
{
	const SLASH = '/';
	const DISTANT = 0;
	const ABSOLUTE = 1;
	const RELATIVE = 2;

	protected $publicPath = "";
	protected $basePath = "";

	public function __construct($publicPath = "public", $basePath = "")
	{
		$this->publicPath = $publicPath;
		$this->basePath = $basePath;
	}

	/**
	 * find the type of a given url
	 * @param string $url
	 */
	static function analizeUrl($url)
	{
		$url = str_replace('\\', self::SLASH, $url);
		$slashNb = substr_count($url, self::SLASH);
		$slashIndex = strpos($url, self::SLASH);
		if ($slashNb >= 2                   // has at least 2 /
		&& $slashIndex > 3                  // has room for the scheme
		&& strlen($url) > $slashIndex + 2   // has room for a domain
		&& $url[$slashIndex - 1] == ':'     // has : before
		&& $url[$slashIndex + 1] == self::SLASH) {  // has / after
			return self::DISTANT;
		} elseif ($slashIndex === 0) {
			return self::ABSOLUTE;
		} else {
			return self::RELATIVE;
		}
	}

	public function staticFilesBasePath()
	{
		return $this->basePath . self::SLASH . $this->publicPath . self::SLASH;
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
		$path = str_replace(self::SLASH, DIRECTORY_SEPARATOR, $uri); // replace '/' with '\' if on windows
		$path = utf8_decode($this->publicPath . $path);
		$return = is_dir($path) ? $path : false;
		return $return;
	}

	public function genUrl($path)
	{
		$path = str_replace('\\', self::SLASH, $path); // replace '\' with '/' if on windows
		// if the path leads to a directory
		if (substr($path, -1) == self::SLASH) {
			// removes the basePath
			$path = $this->pubRelativePath($path);
		}
		$path = rawurlencode(utf8_encode($path)); // replace special characters such as accentued chars
		$path = str_replace("%2F", self::SLASH, $path); // replace '/' html notation with the normal '/' char
		return $this->basePath . self::SLASH . $path;
	}

	public function pubRelativePath($path)
	{
		if (substr($path, 0, strlen($this->publicPath)) == $this->publicPath)
			return substr($path, strlen($this->publicPath) + 1);
		return $path;
	}

	protected function uri()
	{
		return isset($_SERVER['REQUEST_URI']) ? urldecode($_SERVER['REQUEST_URI']) : self::SLASH;
	}
}

?>