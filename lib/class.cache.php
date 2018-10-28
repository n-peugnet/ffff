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
		parent::__construct($path, $name, $level, $parent, $ignored);
		$path = hash('md5', $this->getParentPath() . $this->getName(false));
		$path .= ".{$this->ext()}";
		$this->setPath(Cache::$dir . DIRECTORY_SEPARATOR . $path . Cache::$ext);
	}

	static public function setDir($str)
	{
		self::$dir = $str;
	}
}
