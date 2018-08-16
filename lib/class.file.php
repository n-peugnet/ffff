<?php
class File
{
	protected $name;
	protected $level;
	protected $path;
	protected $parent;
	protected $ignored = false;

	/**
	 * @param string $path
	 * @param string $name
	 * @param int $level
	 * @param Dir $parent
	 */
	public function __construct($path, $name = "", $level = 0, &$parent = null, $ignored = false)
	{
		$this->setName($name);
		$this->level = $level;
		$this->path = $path;
		$this->parent = $parent;
		$this->ignored = $ignored;
	}

	public function __get($prop)
	{
		$method = "get" . ucfirst($prop);
		if (method_exists($this, $method))
			return $this->$method();
	}

	public function getLevel()
	{
		return $this->level;
	}

	public function getName($full = true)
	{
		if (!$full)
			return substr($this->name, 0, strpos($this->name, '.'));
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

	public function getIgnored()
	{
		return $this->ignored;
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
		return strtolower(substr($this->name, strrpos($this->name, '.') + 1));
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

	public function getLastModif()
	{
		$date = new DateTimeImmutable();
		return $date->setTimestamp(filemtime($this->path));
	}

	/**
	 * @param self $f1
	 * @param self $f2
	 */
	public static function compare($f1, $f2, $properties, $flags = 0)
	{
		$i1 = 0;
		$i2 = 0;
		$methods = array_map(function ($prop) {
			return "get" . ucfirst($prop);
		}, $properties);
		while (!method_exists($f1, $methods[$i1]) && $i1 < count($methods))
			$i1++;
		while (!method_exists($f2, $methods[$i2]) && $i2 < count($methods))
			$i2++;
		$method1 = $methods[$i1];
		$method2 = $methods[$i2];
		$val1 = $f1->$method1();
		$val2 = $f2->$method2();
		if (is_string($val1) && $flags & SORT_NATURAL)
			return strnatcmp($val1, $val2);
		if ($val1 == $val2)
			return 0;
		return $val1 > $val2 ? 1 : -1;
	}

	public function diffLevel($file)
	{
		return $file->level - $this->level;
	}
}

?>