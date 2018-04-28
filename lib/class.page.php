<?php
class Page extends Dir
{
	protected $layout;
	protected $title;
	protected $params;

	public static $default_layout;
	public static $default_sort;
	public static $default_render;

	const SORT = 'sort';
	const RENDER = 'render';
	const UNSHIFT = 0;
	const PUSH = 1;
	const HERITABLE_PARAMS = [self::RENDER, self::SORT];

	public static function setDefaults($defaults)
	{
		foreach ($defaults as $key => $value) {
			$default = "default_$key";
			if (property_exists('Page', $default)) {
				static::$$default = $value;
			}
		}
	}

	public function init()
	{
		$this->params = new Params();
		if (empty($this->name))
			$this->autoSetName();
		if (empty($this->parent))
			$this->autoSetParent();
		$this->loadParams();
		$this->autoSetTitle();
		$layout = !empty($this->params['layout']) ? $this->params['layout'] : self::$default_layout;
		$this->layout = "tpl/layouts/$layout.php";
	}

	public function loadParams()
	{
		$params = [];
		if ($this->parent != null) {
			foreach (self::HERITABLE_PARAMS as $param) {
				$params[$param] = [$this->parent->getChildrenParam($param, $this)];
			}
		}
		$this->params->override($params);
		$this->params->load(App::PARAM_FILE, $this->path);
	}

	public function show()
	{
		$head = $this->genHead();
		$title = $this->title;
		$breadcrumb = $this->genBreadcrumb();
		$siteName = App::siteName();
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

	public function renderDirs($levelLimit, $renderTypeDefault = null)
	{
		$renderType = is_null($renderTypeDefault) ? self::$default_render : $renderTypeDefault;
		if (!empty($this->params[self::RENDER][0]))
			$renderType = $this->params[self::RENDER][0];
		$buffer = "<ul class=\"pages\">";
		foreach ($this->getListDirs() as $id => $page) {
			if (!$renderTypePage = $this->params->getCustomKey(self::RENDER, $page->getName()))
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

	public function genHead()
	{
		$buffer = "\n";
		// ------------------------ include default .js files ----------------------------
		if (empty($this->params['remove default']['scripts'])) {
			foreach (glob("inc/js/*.js") as $fileName) {
				$buffer .= "\t<script src=\"" . FFRouter::genUrl($fileName) . "\" async ></script>\n";
			}
		}
		// ----------------------- include default stylesheets ---------------------------
		if (empty($this->params['remove default']['styles'])) {
			foreach (glob("inc/css/*.css") as $fileName) {
				$buffer .= "\t<link rel=\"stylesheet\" href=\"" . FFRouter::genUrl($fileName) . "\" />\n";
			}
		}
		// ----------------------- include specific stylesheets --------------------------
		if (!empty($this->params['styles'])) {
			foreach ($this->params['styles'] as $style) {
				$buffer .= "\t<link rel=\"stylesheet\" href=\"" . $this->url($style) . "\" />\n";
			}
		}
		// ----------------------------- include favicon ---------------------------------
		if (empty($this->params['favicon'])) {
			$faviconFiles = glob("inc/img/favicon.{ico,png}", GLOB_BRACE);
			if (isset($faviconFiles[0])) {
				$favicon = $faviconFiles[0];
				$faviconUrl = FFRouter::genUrl($favicon);
			}
		} else {
			$favicon = $this->params['favicon'];
			$faviconUrl = $this->url($favicon);
		}
		if (isset($favicon)) {
			$ext = substr($favicon, strrpos($favicon, '.') + 1);
			$mime = 'image/' . ($ext == 'ico' ? 'x-icon' : $ext);
			$buffer .= "\t<link rel=\"icon\" type=\"$mime\" href=\"$faviconUrl\" />\n";
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
		return new File('inc/img/default-cover.png', 'default-cover');
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

	public function sort()
	{
		if (!empty($this->params[self::SORT][0]))
			$sortParams = $this->params[self::SORT][0];
		$order = !empty($sortParams['order']) ? $sortParams['order'] : self::$default_sort['order'];
		$order = $order == 'asc' ? SORT_ASC : SORT_DESC;
		$type = !empty($sortParams['type']) ? $sortParams['type'] : self::$default_sort['type'];
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
			elseif (!empty($this->params[self::SORT][1]))
				$subDir->sort($this->params[self::SORT][1]);
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
		if ($sort = $this->params->getCustom(self::SORT)) {
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

	public function getRoute(Type $var = null)
	{
		return FFRouter::genUrl($this->path);
	}

	public function getIgnored()
	{
		return !empty($this->params['ignore']) ? $this->params['ignore'] : [];
	}

	public function addDir($path, $name)
	{
		parent::addDir($path, $name);
		$this->files[$name]->init();
	}

	public function autoSetTitle()
	{
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