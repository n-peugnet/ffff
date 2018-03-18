<?php
class Dir extends File
{
	protected $listDirs = [];
	protected $listFiles = [];

	public function getListFiles()
	{
		return $this->listFiles;
	}

	public function getListDirs()
	{
		return $this->listDirs;
	}

	public function isEmpty()
	{
		return $this->nbDirs() + $this->nbFiles() == 0;
	}

	public function hasChild()
	{
		return $this->nbDirs() > 0;
	}

	public function hasFiles()
	{
		return $this->nbFiles() > 0;
	}

	public function nbDirs()
	{
		return count($this->listDirs);
	}

	public function nbFiles()
	{
		return count($this->listFiles);
	}

	public function addDir($path, $name)
	{
		$this->listDirs[$name] = new static($path, $name, $this->level + 1);
	}

	public function addFile($path, $name)
	{
		$this->listFiles[$name] = new File($path, $name, $this->level + 1);
	}

	function list($recursive = true, $dirOnly = false)
	{
		if ($dir = opendir($this->path)) {
			while (($element = readdir($dir)) !== false) //pour tous les elements de ce dossier...
			{
				if ($element != '.' && $element != '..') {
					$path = $this->path . $element . DIRECTORY_SEPARATOR;
					if (is_dir($this->path . $element)) //si c'est un dossier...
					{
						$this->addDir($path, $element);
						if ($recursive)
							$this->listDirs[$element]->list();
					} elseif (!$dirOnly) {
						$this->addFile($path, $element);
					}
				}
			}
			closedir($dir);
		}
	}

	public function alphaSort()
	{
		sort($this->listFiles, SORT_NATURAL | SORT_FLAG_CASE);
		ksort($this->listDirs, SORT_NATURAL | SORT_FLAG_CASE);
		foreach ($this->listDirs as $subDir)
			$subDir->triAlpha();
	}

	public function makeDir($dirName)
	{
		mkdir($this->path . DIRECTORY_SEPARATOR . $dirName);
	}
}
?>