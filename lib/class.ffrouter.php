<?php
class FFRouter
{
	const SLASH = '/';
	const DISTANT = 0;
	const ABSOLUTE = 1;
	const RELATIVE = 2;
	const MAILTO = 3;

	protected static $publicPath = "";
	protected static $basePath = "";

	public static function init($publicPath = "public", $basePath = "")
	{
		self::$publicPath = $publicPath;
		self::$basePath = $basePath;
	}

	/**
	 * find the type of a given url
	 * @param string $url
	 */
	public static function analizeUrl($url)
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
		} elseif ($slashNb == 0 && substr($url, 0, 7) == 'mailto:') {
			return self::MAILTO;
		} else {
			return self::RELATIVE;
		}
	}

	public static function staticFilesBasePath()
	{
		return self::$basePath . self::SLASH . self::$publicPath . self::SLASH;
	}

	public static function getBasePath()
	{
		return self::$basePath;
	}

	public static function setBasePath($str)
	{
		return self::$basePath = $str;
	}

	public static function matchRoute()
	{
		// removes the basePath
		$uri = substr(self::uri(), strlen(self::$basePath));

		// strip url parameters
		if (($strpos = strpos($uri, '?')) !== false) {
			$uri = substr($uri, 0, $strpos);
		}
		$path = str_replace(self::SLASH, DIRECTORY_SEPARATOR, $uri); // replace '/' with '\' if on windows
		$path = utf8_decode(self::$publicPath . $path);
		$return = is_dir($path) ? $path : false;
		return $return;
	}

	public static function genUrl($path)
	{
		$path = str_replace('\\', self::SLASH, $path); // replace '\' with '/' if on windows
		// if the path leads to a directory
		if (is_dir($path)) {
			// removes the basePath
			$path = self::pubRelativePath($path);
		}
		$path = rawurlencode(utf8_encode($path)); // replace special characters such as accentued chars
		$path = str_replace("%2F", self::SLASH, $path); // replace '/' html notation with the normal '/' char
		return self::$basePath . self::SLASH . $path;
	}

	public static function pubRelativePath($path)
	{
		if (substr($path, 0, strlen(self::$publicPath)) == self::$publicPath)
			return substr($path, strlen(self::$publicPath) + 1);
		return $path;
	}

	protected static function uri()
	{
		return isset($_SERVER['REQUEST_URI']) ? urldecode($_SERVER['REQUEST_URI']) : self::SLASH;
	}
}

?>