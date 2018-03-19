<?php
class Page extends Dir
{
	protected $router;
	protected $layout;
	protected $params;

	/**
	 * @param FFRouter $router
	 * @param string $layout
	 */
	public function init($router, $layout = "layout.php")
	{
		$this->router = $router;
		$this->layout = $layout;
		$this->list(true, false);
		if (empty($this->name))
			$this->setNameFromPath();
		$this->loadParams();
	}

	public function show()
	{
		$basePath = $this->router->getBasePath();
		$title = $this->name;
		$siteName = "test";
		$content = $this->render();
		include $this->layout;
	}

	public function render()
	{
		$content = "";
		$content .= $this->renderFiles();
		$content .= $this->renderDirs(2);
		return $content;
	}

	public function renderDirs($level = 1)
	{
		$ignore = $this->getIgnoredDirs();
		$str = "<pre>";
		foreach ($this->listDirs as $id => $dir) {
			if (array_search($dir->getName(), $ignore) === false)
				$str .= $dir->toString($this->router->genUrl($dir->getPath()), $level);
		}
		return $str . "</pre>";
	}

	public function renderFiles()
	{
		$ignore = $this->getIgnoredFiles();
		$str = "";
		foreach ($this->listFiles as $index => $file) {
			if (array_search($file->getName(), $ignore) === false) {
				switch ($file->type()) {
					case 'image':
						$str .= '<img class="CadrePhoto" src="' . $this->router->genUrl($file->getPath()) . '" alt="' . $file->getName() . '"/>';
						break;

					case 'text':
						$contenu = file_get_contents($file->getPath());
						if ($file->ext() == "md") {
							$mdParser = new MarkdownExtra_Parser();
							$contenu = $mdParser->transform($contenu);
						}
						$str .= "<p>" . $contenu . "</p>";
						break;
				}
			}
		}
		$str .= "</pre>";
		return $str;
	}

	public function dump()
	{
		$content = "";
		$content .= $this->dumpDirs();
		$content .= $this->dumpFiles();
		return $content;
	}

	public function dumpDirs()
	{
		$size = count($this->listDirs);
		$str = "<pre><i>protected</i> 'listDirs' <font color='#888a85'>=&gt;</font>
  <b>array</b> <i>(size=$size)</i>\n";
		foreach ($this->listDirs as $id => $dir) {
			$str .= $dir->toString($this->router->genUrl($dir->getPath()));
		}
		$str .= "</pre>";
		return $str;
	}

	public function dumpFiles()
	{
		$size = count($this->listFiles);
		$str = "<pre><i>protected</i> 'listFiles' <font color='#888a85'>=&gt;</font>
  <b>array</b> <i>(size=$size)</i>\n";
		foreach ($this->listFiles as $index => $file) {
			$str .= $file->toString($this->router->genUrl($file->getPath()));
		}
		$str .= "</pre>";
		return $str;
	}

	public function loadParams()
	{
		$this->params = Spyc::YAMLLoad($this->path . 'params.yaml');
	}

	public function sort()
	{
		$order = !empty($this->params['sort']['order']) ? $this->params['sort']['order'] == 'asc' ? SORT_ASC : SORT_DESC : SORT_ASC;
		$type = !empty($this->params['sort']['type']) ? $this->params['sort']['type'] : 'alpha';
		$recursive = isset($this->params['sort']['recursive']) ? $this->params['sort']['recursive'] : false;
		switch ($type) {
			case 'alpha':
				$this->sortAlpha($order, $recursive);
				break;
			case 'lastModif':
				$this->sortLastModif();
				break;
		}
		foreach ($this->listDirs as $subDir) {
			if (!empty($subDir->params['sort']))
				$subDir->sort();
		}
	}

	public function relativeUrl($path)
	{
		return $this->router->genUrl($this->path . '/' . $path);
	}

	public function getIgnoredDirs()
	{
		return $this->getIgnored("dir");
	}

	public function getIgnoredFiles()
	{
		return $this->getIgnored("file");
	}

	public function getIgnored($type)
	{
		$ignore = [];
		if (!empty($this->params['ignore'])) {
			foreach ($this->params['ignore'] as $name) {
				$isDir = substr($name, -1) == '/';
				if ($isDir)
					$name = substr($name, 0, -1);
				if (($type == "dir" && $isDir) || ($type == "file" && !$isDir))
					array_push($ignore, $name);
			}
		}
		return $ignore;
	}

	public function addDir($path, $name)
	{
		parent::addDir($path, $name);
		$this->listDirs[$name]->init($this->router);
	}
}

?>