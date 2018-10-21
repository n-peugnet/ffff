<?php

class Cache extends File
{
	static $ext = '';
	static $dir = 'tmp';

	/**
	 * @param string $path
	 * @param string $name
	 * @param int $level
	 * @param Dir $parent
	 */
	public function __construct($path, $name = null, $level = 0, &$parent = null, $ignored = false)
	{
		$path = Cache::$dir . DIRECTORY_SEPARATOR . $path . Cache::$ext;
		parent::__construct($path, $name, $level, $parent, $ignored);
	}

	static public function setDir($str)
	{
		self::$dir = $str;
	}
}
