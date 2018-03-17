<?php
class Dossier
{
	protected $name;
	protected $path = "";
	protected $listSubDir = [];
	protected $listFiles = [];
	protected $level = 0;

	public function __construct($dirName, $path, $level = 0)
	{
		$this->name = $dirName;
		$this->path = $path . DIRECTORY_SEPARATOR;
		$this->level = $level;
	}

	public function getListFiles()
	{
		return $this->listFiles;
	}

	public function getLevel()
	{
		return $this->level;
	}

	public function getName()
	{
		return $this->name;
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

	public function addSubDir($name, $path)
	{
		$this->listSubDir[$name] = new Dossier($name, $path, $this->level + 1);
	}

	public function addFile($fileName)
	{
		$this->listFiles[] = $fileName;
	}

	function listage()
	{
		if ($dossier = opendir($this->path)) {
			while (($element = readdir($dossier)) !== false) //pour tous les elements de ce dossier...
			{
				if ($element != '.' && $element != '..') {
					if (is_dir($this->path . $element)) //si c'est un dossier...
					{
						$this->addSubDir($element, $this->path . $element);
						$this->listSubDir[$element]->listage();
					} else {
						$this->addFile($element);
					}
				}
			}
		}
	}

	public function triAlpha()
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