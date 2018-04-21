<?php
class Dir extends File
{
	protected $files = [];

	public function getListFiles()
	{
		$listFiles = [];
		foreach ($this->files as $key => $value) {
			if (get_class($value) == get_parent_class())
				$listFiles[$key] = $value;
		}
		return $listFiles;
	}

	public function getListDirs()
	{
		$listDirs = [];
		foreach ($this->files as $key => $value) {
			if (get_class($value) == get_class($this))
				$listDirs[$key] = $value;
		}
		return $listDirs;
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

	public function nbDirs()
	{
		return count($this->getListDirs());
	}

	public function nbFiles()
	{
		return count($this->getListFiles());
	}

	public function addDir($path, $name)
	{
		$this->files[$name] = new static($path, $name, $this->level + 1, $this);
		return $this;
	}

	public function addFile($path, $name)
	{
		$this->files[$name] = new File($path, $name, $this->level + 1, $this);
		return $this;
	}

	function list_recursive($level = 0, $dirOnly = false, $ignore = [])
	{
		if ($dir = opendir($this->path)) {
			while (($element = readdir($dir)) !== false) //pour tous les elements de ce dossier...
			{
				if ($element != '.' && $element != '..' && array_search($element, $ignore) === false) {
					$path = $this->path . $element;
					if (is_dir($this->path . $element)) //si c'est un dossier...
					{
						$this->addDir($path . DIRECTORY_SEPARATOR, $element);
						if ($level == -1 || $this->level < $level)
							$this->files[$element]->list_recursive($level, $dirOnly, $ignore);
					} elseif (!$dirOnly) {
						$this->addFile($path, $element);
					}
				}
			}
			closedir($dir);
		}
		return $this;
	}

	public function sortAlpha($order = SORT_ASC, $recursive = true)
	{
		if ($order == SORT_ASC) {
			ksort($this->files, SORT_NATURAL | SORT_FLAG_CASE);
		} elseif ($order == SORT_DESC) {
			krsort($this->files, SORT_NATURAL | SORT_FLAG_CASE);
		}
		if ($recursive) {
			foreach ($this->getListDirs() as $subDir)
				$subDir->sortAlpha($order, $recursive);
		}
		return $this;
	}

	public function sortLastModif($order = SORT_ASC, $recursive = true)
	{
		uasort($this->files, function ($f1, $f2) use ($order) {
			$cmp = self::cmpLastModif($f1, $f2);
			return $order == SORT_ASC ? $cmp : !$cmp;
		});
		if ($recursive) {
			foreach ($this->getListDirs() as $subDir)
				$subDir->sortLastModif($order, $recursive);
		}
		return $this;
	}


	public function makeDir($dirName)
	{
		mkdir($this->path . DIRECTORY_SEPARATOR . $dirName);
		return $this;
	}
}
?>