<?php
class File
{
	protected $name;
	protected $path;

	public function __construct($path, $name = "", $level = 0)
	{
		$this->setName($name);
		$this->level = $level;
		$this->path = $path;
	}

	public function getLevel()
	{
		return $this->level;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function setNameFromPath()
	{
		$pieces = explode(DIRECTORY_SEPARATOR, $this->path);
		$this->setName($pieces[count($pieces) - 2]);
	}

	public function setName($str)
	{
		$this->name = utf8_encode($str);
	}

	public function ext()
	{
		$pieces = explode('.', $this->name);
		return strtolower(array_pop($pieces));
	}

	public function type()
	{
		$ext = $this->ext();
		$imgExt = ["png", "jpg", "gif", "jpeg", "tiff"];
		$txtExt = ["txt", "md", "php", "js", "gitignore", "css", "html", "xml", "json", "py", "sql"];
		if (array_search($ext, $imgExt) !== false)
			return 'image';
		if (array_search($ext, $txtExt) !== false)
			return 'text';
		return 'unknown';
	}
}

?>