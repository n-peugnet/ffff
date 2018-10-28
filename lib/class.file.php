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
	public function __construct($path, $name = null, $level = 0, &$parent = null, $ignored = false)
	{
		$this->setPath($path);
		if ($name != null)
			$this->setName($name);
		$this->setLevel($level);
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

	protected function setLevel($level)
	{
		$this->level = $level;
	}

	/**
	 * Get the name of the file without the path
	 * @param boolean $ext - with or without the extension
	 * @return string
	 */
	public function getName($ext = true)
	{
		if (!$ext)
			return substr($this->name, 0, strrpos($this->name, '.'));
		return $this->name;
	}

	public function setName($str)
	{
		$this->name = utf8_encode($str);
		return $this;
	}

	/**
	 * Get the full path of the file including its name
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	protected function setPath($str)
	{
		$this->path = str_replace('/', DIRECTORY_SEPARATOR, $str);
		$this->autoSetName();
		return $this;
	}

	/**
	 * Get the parent dir of the file
	 * @return Dir|false
	 */
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

	public function getParentPath()
	{
		if (!empty($this->parent))
			return $this->parent->getPath();
		else
			return $this->findParentPath();
	}

	public function getIgnored()
	{
		return $this->ignored;
	}

	static public function existAt($path)
	{
		return is_file($path);
	}

	public function exist()
	{
		return static::existAt($this->getPath());
	}

	/**
	 * Reads the content of the file
	 * @return string
	 */
	public function read()
	{
		return file_get_contents($this->getPath());
	}

	/**
	 * Write the given content in the file
	 * @param string $content
	 * @return int
	 */
	public function write($content)
	{
		if (!is_dir($this->getParentPath())) {
			// dir doesn't exist, make it
			mkdir($this->getParentPath(), 0777, true);
		}
		return file_put_contents($this->getPath(), $content);
	}

	protected function findParentPath()
	{
		// FixMe : pas top de trouver le parent par rapport au nom
		return substr($this->getPath(), 0, -mb_strlen($this->name));
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
		$pieces = preg_split('/[\\' . DIRECTORY_SEPARATOR . ']/', $this->getPath(), -1, PREG_SPLIT_NO_EMPTY);
		$this->setName($pieces[count($pieces) - 1]);
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
		return $date->setTimestamp(filemtime($this->getPath()));
	}

	/**
	 * Comparision function useful for array sorting functions
	 * @param self $f1
	 * @param self $f2
	 * @return int
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
