<?php
class Page extends Dir
{
	protected $router;
	protected $layout;
	protected $title;
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
		$this->autoSetTitle();
		if (empty($this->parent))
			$this->autoSetParent();
	}

	public function show()
	{
		$title = $this->title;
		$breadcrumb = $this->genBreadcrumb();
		$siteName = "test";
		$content = $this->render();
		include $this->layout;
	}

	public function render()
	{
		$level = !empty($this->params['render']) ? count($this->params['render']) : 1;
		$content = "";
		$content .= $this->renderFiles();
		$content .= $this->renderDirs($level);
		return $content;
	}

	public function renderDirs($levelLimit, $defaultType = 'list')
	{
		$type = $defaultType;
		if ($this->parent != null) {
			$parentChildType = $this->parent->getChildrenParam('render', $this);
			if ($parentChildType)
				$type = $parentChildType;
		}
		if (!empty($this->params['render'][0]))
			$type = $this->params['render'][0];
		$str = "<ul class=\"dirs\">";
		foreach ($this->listDirs as $id => $dir) {
			if (!$typeDir = $this->getCustomParamKey('render', $dir->getName()))
				$typeDir = $type;
			$url = $dir->getRoute();
			$title = $dir->getTitle();
			switch ($typeDir) {
				case 'list':
					$str .= "<li><p><a class=\"nav-links\" href=\"$url\" class=\"date\">$title</a></p></li>";
					break;

				case 'covers':
					$longTitle = $this->router->pubRelativePath($dir->path);
					$cover = $dir->getCover();
					$cover = $cover ? $this->router->genUrl($cover->getPath()) : 'rien';
					$str .= "<li class=\"couverture level$dir->level\" id=\"projet-$longTitle\"><a href=\"$url\"><div>$title</div><img src=\"$cover\" alt=\"cover-$title\" /></a>";
					break;
			}
			if ($dir->level < $levelLimit)
				$str .= $dir->renderDirs($levelLimit, $defaultType);
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
						$mdParser = new MarkdownFF_Parser($this);
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
			$title = $p->getTitle();
			$str = "<a class=\"nav-links\" href=\"$url\">$title</a> › ";
			$str = $p->genBreadcrumb() . $str;
		}
		return $str;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getCover()
	{
		if (!empty($this->params['cover']) && !empty($this->listFiles[$this->params['cover']]))
			return $this->listFiles[$this->params['cover']];
		foreach ($this->listFiles as $file) {
			if ($file->type() == 'image')
				return $file;
		}
		return new File('public/assets/img/default-cover.png', 'default-cover');
	}

	public function getRenderLevel()
	{
		if (!empty($this->params['render']))
			return count($this->params['render']);
		return 2;
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
		if (!empty($this->params['sort'][0]))
			$sortParams = $this->params['sort'][0];
		elseif ($this->parent != null)
			$sortParams = $this->parent->getChildrenParam('sort', $this);

		$order = !empty($sortParams['order']) ? $sortParams['order'] == 'asc' ? SORT_ASC : SORT_DESC : SORT_ASC;
		$type = !empty($sortParams['type']) ? $sortParams['type'] : 'alpha';
		$recursive = isset($sortParams['recursive']) ? $sortParams['recursive'] : false;
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
		$this->sortCustom();
	}

	public function sortCustom()
	{
		if ($sort = $this->getCustomParam('sort')) {
			$mode = 'unshift';
			$unshift = [];
			$push = [];
			foreach ($sort as $name) {
				$name = utf8_decode($name);
				if ($name == '*')
					$mode = 'push';
				elseif (!empty($this->listDirs[$name])) {
					if ($mode == 'unshift')
						$unshift[$name] = $this->listDirs[$name];
					else
						$push[$name] = $this->listDirs[$name];
					unset($this->listDirs[$name]);
				}
			}
			$this->listDirs = array_merge($unshift, $this->listDirs, $push);
		}
	}

	public function getChildrenParam($param, $child)
	{
		$levelDiff = $this->diffLevel($child);
		if ($levelDiff == 1)
			$directChild = $child;
		else
			$directChild = $child->getParent($levelDiff - 1);
		if (!empty($this->params[$param][$levelDiff]) && array_search($directChild->getName(), $this->getIgnored()) === false)
			return $this->params[$param][$levelDiff];
		elseif (!empty($this->parent))
			return $this->parent->getChildrenParam($param, $child);
		return false;
	}

	public function getCustomParam($param)
	{
		if (!empty($this->params['custom'][$param]))
			return $this->params['custom'][$param];
		return false;
	}

	public function getCustomParamKey($param, $key)
	{
		if ($param = $this->getCustomParam($param)) {
			if (!empty($param[$key]))
				return $param[$key];
		}
		return false;
	}

	public function url($path, $type = false)
	{
		$type = $type ? $type : FFRouter::analizeUrl($path);
		switch ($type) {
			case FFRouter::RELATIVE:
				return $this->router->genUrl($this->path . $path);
				break;
			case FFRouter::ABSOLUTE:
				return $this->router->genUrl(substr($path, 1));
				break;
			case FFRouter::DISTANT:
				return $path;
				break;
		}
	}

	public function assetUrl($path)
	{
		return $this->router->staticFilesBasePath() . 'assets/' . $path;
	}

	public function getRoute(Type $var = null)
	{
		return $this->router->genUrl($this->path);
	}

	public function tmpPath()
	{
		return 'tmp' . DIRECTORY_SEPARATOR . $this->path;
	}

	public function getIgnored()
	{
		return !empty($this->params['ignore']) ? $this->params['ignore'] : [];
	}

	public function addDir($path, $name)
	{
		parent::addDir($path, $name);
		$this->listDirs[$name]->init($this->router, $this->layout, $this->paramFile);
	}

	public function autoSetTitle()
	{
		if (empty($this->name))
			$this->autoSetName();
		if (!empty($this->params['title']))
			$title = $this->params['title'];
		else {
			$title = $this->name;
		}
		$this->title = ucwords($title);
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