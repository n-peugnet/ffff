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

	public function toString($url = null)
	{
		$class = get_called_class();
		$size = strlen($this->name);
		$str = str_repeat(' ', $this->level * 8 - 4);
		$str .= !empty($url) ? "<a href='$url'>$this->name</a>" : "$this->name";
		$str .= " <font color='#888a85'>=&gt;</font>\n";
		$str .= str_repeat(' ', $this->level * 8 - 2) . "<b>object</b>(<i>$class</i>)\n";
		$str .= str_repeat(' ', $this->level * 8) . "<i>protected</i> 'name' <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'$this->name'</font> <i>(length=$size)</i>\n";
		return $str;
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