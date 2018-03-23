<?php
class Page extends Dir
{
	protected $router;
	protected $layout;
	protected $params;
	protected $paramFile;

	/**
	 * @param FFRouter $router
	 * @param string $layout
	 */
	public function init(&$router, $layout = "layout.php", $paramFile = "params.yaml")
	{
		$this->paramFile = $paramFile;
		$this->router = $router;
		$this->layout = $layout;
		$this->loadParams();
		$this->autoSetName();
		if (empty($this->parent))
			$this->autoSetParent();
	}

	public function show()
	{
		$basePath = $this->router->getBasePath();
		$title = $this->name;
		$breadcrumb = $this->genBreadcrumb();
		$siteName = "test";
		$content = $this->render();
		include $this->layout;
	}

	public function render()
	{
		$level = !empty($this->params['render']) ? count($this->params['render']) : 1;
		$type = !empty($this->params['render']) ? $this->params['render'] : ['list'];
		$content = "";
		$content .= $this->renderFiles();
		$content .= $this->renderDirs($level, $type);
		return $content;
	}

	public function renderDirs($levelLimit, $types)
	{
		$str = "<ul class=\"dirs\">";
		foreach ($this->listDirs as $id => $dir) {
			$url = $dir->getRoute();
			switch ($types[$this->level]) {
				case 'list':
					$str .= "<li><p><a href=\"$url\" class=\"date\">$dir->name</a></p></li>";
					break;

				case 'covers':
					$longName = $this->router->pubRelativePath($dir->path);
					$cover = $this->router->genUrl($dir->getCover()->getPath());
					$name = $dir->getName();
					$str .= "<li class=\"couverture level$dir->level\" id=\"projet_$longName\"><a href=\"$url\"><div>$name</div><img src=\"$cover\" alt=\"test\" /></a>";
					break;
			}
			if ($dir->level < $levelLimit)
				$str .= $dir->renderDirs($levelLimit, $types);
		}
		return $str . "</ul>";
	}

	public function renderFiles()
	{
		$str = "";
		foreach ($this->listFiles as $index => $file) {
			$type = $file->type();
			if ($type == 'image') {
				$str .= '<img class="CadrePhoto" src="' . $this->router->genUrl($file->getPath()) . '" alt="' . $file->getName() . '"/>';
			} elseif ($type == 'text') {
				$contenu = file_get_contents($file->getPath());
				$ext = $file->ext();
				switch ($ext) {
					case 'md':
						$mdParser = new Markdown_Parser();
						$contenu = $mdParser->transform($contenu);
						break;
					case 'txt':
						$contenu = "<p>" . $contenu . "</p>";
						break;
				}
				$str .= $contenu;
			}
		}
		return $str;
	}

	public function dump()
	{
		$content = "";
		$content .= $this->dumpDirs();
		$content .= $this->dumpFiles();
		return $content;
	}

	public function dumpDirs($level = 1)
	{
		$str = "<pre>";
		foreach ($this->listDirs as $id => $dir) {
			$str .= $dir->toString($dir->getRoute(), $level);
		}
		return $str . "</pre>";
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

	public function genBreadcrumb()
	{
		$p = $this->parent;
		$str = "";
		if (!empty($p)) {
			$url = $p->getRoute();
			$str = "<a href=\"$url\">$p->name</a> › ";
			$str = $p->genBreadcrumb() . $str;
		}
		return $str;
	}

	public function getCover()
	{
		if (!empty($this->params['cover']) && !empty($this->listFiles[$this->params['cover']]))
			return $this->listFiles[$this->params['cover']];
		foreach ($this->listFiles as $file) {
			if ($file->type() == 'image')
				return $file;
		}
		return false;
	}

	public function loadParams()
	{
		$paramFile = $this->path . $this->paramFile;
		$paramCache = $this->tmpPath() . $this->paramFile . '.cache';
		if (is_file($paramFile)) {
			if (is_file($paramCache) && (filemtime($paramFile) <= filemtime($paramCache)))
				$this->params = unserialize(file_get_contents($paramCache));
			else {
				$this->params = Spyc::YAMLLoad($paramFile);
				$this->cacheParams();
			}
		}
	}

	public function cacheParams()
	{
		$tmpPath = $this->tmpPath();
		if (!is_dir($tmpPath)) {
			// dir doesn't exist, make it
			mkdir($tmpPath, 0777, true);
		}
		file_put_contents($tmpPath . $this->paramFile . '.cache', serialize($this->params));
	}

	public function sort()
	{
		if ($this->parent != null)
			$sortParams = $this->parent->getChildrenSort($this->level);
		$order = SORT_ASC;
		$type = 'alpha';
		$recursive = false;
		if (!empty($this->params['sort'][0]))
			$sortParams = $this->params['sort'][0];
		if (!empty($sortParams)) {
			$order = !empty($sortParams['order']) ? $sortParams['order'] == 'asc' ? SORT_ASC : SORT_DESC : SORT_ASC;
			$type = !empty($sortParams['type']) ? $sortParams['type'] : 'alpha';
			$recursive = isset($sortParams['recursive']) ? $sortParams['recursive'] : false;
		}
		switch ($type) {
			case 'alpha':
				$this->sortAlpha($order, $recursive);
				break;
			case 'lastModif':
				$this->sortLastModif($order, $recursive);
				break;
		}
		foreach ($this->listDirs as $subDir) {
			if (!empty($subDir->params['sort']))
				$subDir->sort();
			elseif (!empty($this->params['sort'][$this->level + 1]))
				$subDir->sort($this->params['sort'][$this->level + 1]);
		}
	}

	public function getChildrenSort($childLevel)
	{
		$sortLevel = $childLevel - $this->level;
		if (!empty($this->params['sort'][$sortLevel]))
			return $this->params['sort'][$sortLevel];
		return false;
	}

	public function relativeUrl($path)
	{
		return $this->router->genUrl($this->path . '/' . $path);
	}

	public function getRoute(Type $var = null)
	{
		return $this->router->genUrl($this->path);
	}

	public function tmpPath()
	{
		return 'tmp' . DIRECTORY_SEPARATOR . $this->path;
	}

	public function getIgnoredDirs()
	{
		return $this->getIgnored("dir");
	}

	public function getIgnoredFiles()
	{
		return $this->getIgnored("file");
	}

	public function getIgnored($type = 'all')
	{
		$ignore = [];
		if (!empty($this->params['ignore'])) {
			foreach ($this->params['ignore'] as $name) {
				$isDir = substr($name, -1) == '/';
				if ($isDir)
					$name = substr($name, 0, -1);
				if ($type == 'all' || ($type == "dir" && $isDir) || ($type == "file" && !$isDir))
					array_push($ignore, $name);
			}
		}
		return $ignore;
	}

	public function addDir($path, $name)
	{
		parent::addDir($path, $name);
		$this->listDirs[$name]->init($this->router, $this->layout, $this->paramFile);
	}

	public function autoSetName()
	{
		if (!empty($this->params['title']))
			$this->name = $this->params['title'];
		elseif (empty($this->name))
			parent::autoSetName();
		return $this;
	}

	public function autoSetParent()
	{
		parent::autoSetParent();
		if (empty($this->parent)) return false;
		$this->parent->init($this->router);
		return $this;
	}

}

?>