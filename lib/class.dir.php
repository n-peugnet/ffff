<?php
class Dir
{
	protected $name;
	protected $path = "";
	protected $listSubDir = [];
	protected $listFiles = [];
	protected $level = 0;

	public function __construct($path, $dirName = "", $level = 0)
	{
		$this->setName($dirName);
		$this->path = $path;
		$this->level = $level;
	}

	public function getListFiles()
	{
		return $this->listFiles;
	}

	public function getListSubDir()
	{
		return $this->listSubDir;
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

	public function isEmpty()
	{
		return $this->nbSubDir() + $this->nbFiles() == 0;
	}

	public function hasChild()
	{
		return $this->nbSubDir() > 0;
	}

	public function hasFiles()
	{
		return $this->nbFiles() > 0;
	}

	public function nbSubDir()
	{
		return count($this->listSubDir);
	}

	public function nbFiles()
	{
		return count($this->listFiles);
	}

	public function addSubDir($path, $name)
	{
		$this->listSubDir[$name] = new static($path, $name, $this->level + 1);
	}

	public function addFile($fileName)
	{
		$this->listFiles[] = $fileName;
	}

	function list($recursive = true, $dirOnly = false)
	{
		if ($dir = opendir($this->path)) {
			while (($element = readdir($dir)) !== false) //pour tous les elements de ce dossier...
			{
				if ($element != '.' && $element != '..') {
					if (is_dir($this->path . $element)) //si c'est un dossier...
					{
						$this->addSubDir($this->path . $element . DIRECTORY_SEPARATOR, $element);
						if ($recursive)
							$this->listSubDir[$element]->list();
					} elseif (!$dirOnly) {
						$this->addFile($element);
					}
				}
			}
			closedir($dir);
		}
	}

	public function alphaSort()
	{
		sort($this->listFiles, SORT_NATURAL | SORT_FLAG_CASE);
		ksort($this->listSubDir, SORT_NATURAL | SORT_FLAG_CASE);
		foreach ($this->listSubDir as $subDir)
			$subDir->triAlpha();
	}

	public function makeDir($dirName)
	{
		mkdir($this->path . DIRECTORY_SEPARATOR . $dirName);
	}
}
?>