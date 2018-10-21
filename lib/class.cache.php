<?php

class Cache extends File
{
	static $ext = 'cache';
	static $dir = 'tmp';

	public function getPath()
	{
		return Cache::$dir . DIRECTORY_SEPARATOR . $this->path . "." . Cache::$ext;
	}

	public function write($content)
	{
		if (!is_dir($this->getParentPath())) {
			// dir doesn't exist, make it
			mkdir($this->getParentPath(), 0777, true);
		}
		parent::write($content);
	}
}
