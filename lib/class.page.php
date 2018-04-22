<?php
class Page extends Dir
{
	protected $layout;
	protected $title;
	protected $params;
	protected $paramFile;

	const RENDER = 'render';
	const SORT = 'sort';
	const UNSHIFT = 0;
	const PUSH = 1;

	/**
	 * @param string $layout
	 */
	public function init($layout = "default.php", $paramFile = "params.yaml")
	{
		$this->paramFile = $paramFile;
		$this->layout = "tpl/layouts/$layout";
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
		$level = !empty($this->params[self::RENDER]) ? count($this->params[self::RENDER]) : 1;
		$content = "";
		$content .= $this->renderFiles();
		$content .= $this->renderDirs($level);
		return $content;
	}

	public function renderDirs($levelLimit, $renderTypeDefault = 'list')
	{
		$renderType = $renderTypeDefault;
		if (!empty($this->params[self::RENDER][0]))
			$renderType = $this->params[self::RENDER][0];
		elseif ($this->parent != null) {
			$parentChildType = $this->parent->getChildrenParam(self::RENDER, $this);
			if ($parentChildType)
				$renderType = $parentChildType;
		}
		$buffer = "<ul class=\"pages\">";
		foreach ($this->getListDirs() as $id => $page) {
			if (!$renderTypePage = $this->getCustomParamKey(self::RENDER, $page->getName()))
				$renderTypePage = $renderType;
			$url = $page->getRoute();
			$title = $page->getTitle();
			$longTitle = FFRouter::pubRelativePath($page->path);
			$cover = $page->getCover();
			$cover = $cover ? FFRouter::genUrl($cover->getPath()) : 'rien';

			ob_start();
			include "tpl/views/li.$renderTypePage.php";
			$buffer .= ob_get_clean();

			if ($page->level < $levelLimit)
				$buffer .= $page->renderDirs($levelLimit, $renderTypeDefault);
		}
		return $buffer . "</ul>";
	}

	public function renderFiles()
	{
		$buffer = "";
		foreach ($this->getListFiles() as $index => $file) {
			$type = $file->type();
			if ($type == 'image') {
				$buffer .= '<img class="CadrePhoto" src="' . FFRouter::genUrl($file->getPath()) . '" alt="' . $file->getName() . '"/>';
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
				$buffer .= $contenu;
			}
		}
		return $buffer;
	}

	public function genBreadcrumb()
	{
		$p = $this->parent;
		$buffer = "";
		if (!empty($p)) {
			$url = $p->getRoute();
			$title = $p->getTitle();
			$buffer = "<a class=\"nav-links\" href=\"$url\">$title</a> › ";
			$buffer = $p->genBreadcrumb() . $buffer;
		}
		return $buffer;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getCover()
	{
		if (!empty($this->params['cover']) && !empty($this->files[$this->params['cover']]))
			return $this->files[$this->params['cover']];
		foreach ($this->files as $file) {
			if ($file->type() == 'image')
				return $file;
		}
		return new File('public/assets/img/default-cover.png', 'default-cover');
	}

	public function getRenderLevel()
	{
		if (!empty($this->params[self::RENDER]))
			return count($this->params[self::RENDER]);
		return 2;
	}

	public function getDate()
	{
		$formats = ['d/m/Y H:i:s', 'd/m/Y H:i', 'd/m/Y'];
		$date = false;
		if (!empty($this->params['date'])) {
			$numFormat = 0;
			while (!$date) {
				$date = DateTimeImmutable::createFromFormat($formats[$numFormat], $this->params['date']);
				$numFormat++;
			}
			return $date;
		}
		return $this->lastModif();
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
		if (!empty($this->params[self::SORT][0]))
			$sortParams = $this->params[self::SORT][0];
		elseif ($this->parent != null)
			$sortParams = $this->parent->getChildrenParam(self::SORT, $this);

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
			case 'date':
				$this->sortDate($order, $recursive);
				break;
		}
		foreach ($this->getListDirs() as $subDir) {
			if (!empty($subDir->params[self::SORT]))
				$subDir->sort();
			elseif (!empty($this->params[self::SORT][$this->level + 1]))
				$subDir->sort($this->params[self::SORT][$this->level + 1]);
		}
		$this->sortCustom();
	}

	public function sortDate($order = SORT_DESC, $recursive = true)
	{
		uasort($this->files, function ($p1, $p2) use ($order) {
			$cmp = self::cmpDate($p1, $p2);
			return $order == SORT_ASC ? $cmp : !$cmp;
		});
		if ($recursive) {
			foreach ($this->getListDirs() as $subDir)
				$subDir->sortDate($order, $recursive);
		}
		return $this;
	}

	public function sortCustom()
	{
		if ($sort = $this->getCustomParam(self::SORT)) {
			$mode = self::UNSHIFT;
			$unshift = [];
			$push = [];
			foreach ($sort as $name) {
				$name = utf8_decode($name);
				if ($name == '*')
					$mode = self::PUSH;
				elseif (!empty($this->files[$name])) {
					if ($mode == self::UNSHIFT)
						$unshift[$name] = $this->files[$name];
					else
						$push[$name] = $this->files[$name];
					unset($this->files[$name]);
				}
			}
			$this->files = array_merge($unshift, $this->files, $push);
		}
	}

	/**
	 * @param self $p1
	 * @param self $p2
	 */
	public static function cmpDate($p1, $p2)
	{
		return $p1->getDate() > $p2->getDate();
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
				return FFRouter::genUrl($this->path . $path);
				break;
			case FFRouter::ABSOLUTE:
				return FFRouter::genUrl(substr($path, 1));
				break;
			case FFRouter::DISTANT:
				return $path;
				break;
		}
	}

	public function assetUrl($path)
	{
		return FFRouter::staticFilesBasePath() . 'assets/' . $path;
	}

	public function getRoute(Type $var = null)
	{
		return FFRouter::genUrl($this->path);
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
		$this->files[$name]->init($this->layout, $this->paramFile);
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
		$this->parent->init();
		return $this;
	}

}

?>