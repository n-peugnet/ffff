<?php

/**
 * Static class to resolve url troug the whole project
 */
class FFRouter
{
	const SLASH = '/';
	const EXTERNAL = 1;
	const ABSOLUTE = 2;
	const RELATIVE = 3;
	const VALID_PATH = 4;
	const MAILTO = 5;
	const ASSET = 6;

	protected static $publicDir = "";
	protected static $basePath = "";

	public static function init($publicDir, $basePath = "")
	{
		self::$publicDir = $publicDir;
		self::$basePath = $basePath;
	}

	/**
	 * find the style of a given url :
	 * - External   : ----://-----
	 * - Absolute   : /-----------
	 * - Mailto     : mailto:-----
	 * - Asset      : ~/----------
	 * - Valid Path : path ready to be used with self::genUrl()
	 * - Relative   : everything else
	 * @param string $url
	 */
	public static function analizeUrl($url)
	{
		$url = self::cleanSlash($url);
		if (strpos($url, '://') > 3) {
			return self::EXTERNAL;
		} elseif (substr($url, 0, 1) == self::SLASH) {
			return self::ABSOLUTE;
		} elseif (substr($url, 0, 7) == 'mailto:') {
			return self::MAILTO;
		} elseif (substr($url, 0, 2) == '~/') {
			return self::ASSET;
		} elseif (file_exists($url)) {
			return self::VALID_PATH;
		} else {
			return self::RELATIVE;
		}
	}

	public static function staticFilesBasePath()
	{
		return self::$basePath . self::SLASH . self::$publicDir . self::SLASH;
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
		$path = utf8_decode(self::$publicDir . $path);
		$return = is_dir($path) ? $path : false;
		return $return;
	}

	public static function genUrl($path)
	{
		$path = self::cleanSlash($path); // replace '\' with '/' if on windows
		// if the path leads to a directory
		if (is_dir($path)) {
			$path = self::pubRelativePath($path);
		}
		$path = rawurlencode(utf8_encode($path)); // replace special characters such as accentued chars
		$path = str_replace("%2F", self::SLASH, $path); // replace '/' html notation with the normal '/' char
		return self::$basePath . self::SLASH . $path;
	}

	/**
	 * Removes the public dir from the path
	 * @param string $path
	 * @return string
	 */
	public static function pubRelativePath($path)
	{
		if (substr($path, 0, strlen(self::$publicDir)) == self::$publicDir)
			return substr($path, strlen(self::$publicDir) + 1);
		return $path;
	}

	protected static function uri()
	{
		return isset($_SERVER['REQUEST_URI']) ? urldecode($_SERVER['REQUEST_URI']) : self::SLASH;
	}

	protected static function cleanSlash($url)
	{
		return str_replace('\\', self::SLASH, $url);
	}
}
