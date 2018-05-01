<?php
class File
{
	protected $name;
	protected $path;
	protected $parent;

	/**
	 * @param string $path
	 * @param string $name
	 * @param int $level
	 * @param Dir $parent
	 */
	public function __construct($path, $name = "", $level = 0, &$parent = null)
	{
		$this->setName($name);
		$this->level = $level;
		$this->path = $path;
		$this->parent = $parent;
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

	public function getParent($level = 1)
	{
		if (!empty($this->parent)) {
			if ($level > 1)
				return $this->parent->getParent($level - 1);
			else
				return $this->parent;
		}
		return false;
	}

	protected function findParentPath()
	{
		return substr($this->path, 0, -mb_strlen($this->name));
	}

	public function autoSetParent()
	{
		$parentPath = $this->findParentPath();
		if (empty($parentPath)) return false;
		$this->parent = new Dir($parentPath);
		return $this;
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

	public function autoSetName()
	{
		$pieces = preg_split('/[\\' . DIRECTORY_SEPARATOR . ']/', $this->path, -1, PREG_SPLIT_NO_EMPTY);
		$this->setName($pieces[count($pieces) - 1]);
		return $this;
	}

	public function setName($str)
	{
		$this->name = utf8_encode($str);
		return $this;
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

	public function lastModif()
	{
		$date = new DateTimeImmutable();
		return $date->setTimestamp(filemtime($this->path));
	}

	/**
	 * @param self $f1
	 * @param self $f2
	 */
	public static function cmpLastModif($f1, $f2)
	{
		$date1 = $f1->lastModif();
		$date2 = $f2->lastModif();
		if ($date1 == $date2)
			return 0;
		return $date1 > $date2 ? 1 : -1;
	}

	public function diffLevel($file)
	{
		return $file->level - $this->level;
	}
}

?>