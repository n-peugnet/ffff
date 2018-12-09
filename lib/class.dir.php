<?php
class Dir extends File
{
	/** @var File[] */
	protected $files = [];

	/**
	 * @param bool $all - return ignored files or not
	 * @param bool $ext - file names with extention or not
	 * @return File[]
	 */
	public function getListFiles($all = false, $ext = false)
	{
		$listFiles = [];
		foreach ($this->files as $key => $file) {
			if (($all || !$file->getIgnored()) && get_class($file) == get_parent_class() && (!$ext || $ext == $file->ext()))
				$listFiles[$key] = $file;
		}
		return $listFiles;
	}

	/**
	 * Get the Dirs from the list of files
	 * @param bool $all - return ignored dirs or not
	 * @return Dir[]
	 */
	public function getListDirs($all = false)
	{
		$listDirs = [];
		foreach ($this->files as $key => $file) {
			if (($all || !$file->getIgnored()) && static::isSameClass($file))
				$listDirs[$key] = $file;
		}
		return $listDirs;
	}

	/**
	 * @param string $name
	 * @param bool $full
	 * @return File
	 */
	public function getFile($name, $full = true)
	{
		if ($full) {
			if (isset($this->files[$name]))
				return $this->files[$name];
		} else {
			foreach ($this->getListFiles() as $file) {
				if ($file->getName(false) == $name)
					return $file;
			}
		}
		return false;
	}

	public function setPath($path)
	{
		parent::setPath($path);
		if (substr($this->path, -1) != DIRECTORY_SEPARATOR) {
			$this->path .= DIRECTORY_SEPARATOR;
		}
	}

	static public function existAt($path)
	{
		return is_dir($path);
	}

	public function fileExist($name)
	{
		return !empty($this->files[$name]);
	}

	public function findParentPath()
	{
		return substr(parent::findParentPath(), 0, -1);
	}

	public function autoSetParent()
	{
		$parentPath = $this->findParentPath();
		if (empty($parentPath)) return false;
		$this->parent = new static($parentPath, "", $this->level - 1);
		return $this;
	}

	public function toString($url = null, $level = 1)
	{
		$sDirs = $this->nbDirs();
		$sFiles = $this->nbFiles();
		$str = parent::toString($url);
		$str .= str_repeat(' ', $this->level * 8) . "<i>protected</i> 'listDirs' <font color='#888a85'>=&gt;</font> <b>array</b> (size=$sDirs)\n";
		if ($this->level < $level)
			$str .= $this->renderDirs();
		$str .= str_repeat(' ', $this->level * 8) . "<i>protected</i> 'listFiles' <font color='#888a85'>=&gt;</font> <b>array</b> (size=$sFiles)\n";
		return $str;
	}

	public function isEmpty()
	{
		return count($this->files) == 0;
	}

	public function hasChild()
	{
		return $this->nbDirs() > 0;
	}

	public function hasFiles()
	{
		return $this->nbFiles() > 0;
	}

	public function type()
	{
		return 'dir';
	}

	public function getLastModif($level = -1)
	{
		$date = parent::getLastModif();
		foreach ($this->getListFiles() as $file) {
			$fileDate = $file->getLastModif();
			$date = $fileDate > $date ? $fileDate : $date;
		}
		if ($level !== 0) {
			foreach ($this->getListDirs() as $subDir) {
				$subDirDate = $subDir->getLastModif($level - 1);
				$date = $subDirDate > $date ? $subDirDate : $date;
			}
		}
		return $date;
	}

	public function nbDirs()
	{
		return count($this->getListDirs());
	}

	public function nbFiles()
	{
		return count($this->getListFiles());
	}

	public function addDir($path, $name, $ignored = false)
	{
		$dir = new static($path, $name, $this->level + 1, $this, $ignored);
		$this->files[$name] = $dir;
		return $dir;
	}

	public function addFile($path, $name, $ignored = false)
	{
		$this->files[$name] = new File($path, $name, $this->level + 1, $this, $ignored);
		return $this;
	}

	public function list_recursive($level = -1, $dirOnly = false, $ignore = [])
	{
		if ($dir = opendir($this->path)) {
			while (($element = readdir($dir)) !== false) //pour tous les elements de ce dossier...
			{
				if ($element != '.' && $element != '..') {
					$ignored = array_search($element, $ignore) !== false;
					$path = $this->path . $element;
					if (is_dir($this->path . $element)) //si c'est un dossier...
					{
						$this->addDir($path . DIRECTORY_SEPARATOR, $element, $ignored);
						if (!$ignored && $level !== 0)
							$this->files[$element]->list_recursive($level - 1, $dirOnly, $ignore);
					} elseif (!$dirOnly) {
						$this->addFile($path, $element, $ignored);
					}
				}
			}
			closedir($dir);
		}
		return $this;
	}

	public function sort_recursive($properties, $order = SORT_ASC, $level = -1, $flags = SORT_NATURAL)
	{
		File::uasort($this->files, $properties, $order, $flags);
		if ($level !== 0) {
			foreach ($this->getListDirs() as $subDir)
				$subDir->sort_recursive($properties, $order, $level - 1, $flags);
		}
		return $this;
	}

	/**
	 * @param int $level
	 * @param bool $dirOnly
	 * @param callable $function A method to apply to every element
	 * @return File[]|static
	 */
	public function flatten($level = -1, $dirOnly = false, $function = null)
	{
		$array = [];
		foreach ($this->files as $key => $file) {
			$isDir = static::isSameClass($file);
			if (!$dirOnly || $isDir) {
				$value = is_callable($function) ? $function($file) : $file;
				$array[$file->getPath()] = $value;
				if ($isDir && $level !== 0) {
					$array = array_merge($array, $file->flatten($level - 1, $dirOnly, $function));
				}
			}
		}
		return $array;
	}


	public function makeDir($dirName)
	{
		mkdir($this->path . DIRECTORY_SEPARATOR . $dirName);
		return $this;
	}
}
