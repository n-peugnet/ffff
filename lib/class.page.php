<?php
class Page extends Dir
{
	protected $layout;
	protected $title;
	protected $params;
	protected $assets;

	const SORT = 'sort';
	const RENDER = 'render';
	const UNSHIFT = 0;
	const PUSH = 1;
	const HERITABLE_PARAMS = [self::RENDER, self::SORT];

	public function init($heritedParams = [])
	{
		$this->params = new Params(App::pageDefaults());
		if (empty($this->name))
			$this->autoSetName();
		if (empty($this->parent))
			$heritedParams = $this->autoSetParent();
		$this->params->override($heritedParams);
		$this->params->load(App::PARAM_FILE, $this->path, Params::OVERRIDE);
		$this->initAssets();
		$this->autoSetTitle();
		if ($this->level >= 0) {
			$layout = $this->params->get('layout');
			$this->layout = "tpl/layouts/$layout.php";
			if (is_dir($this->assets->getPath()))
				$this->assets->list_recursive();
		}
	}

	public function show()
	{
		$page = $this;
		$head = $this->genHead();
		$title = $this->title;
		$siteName = App::siteName();
		$date = $this->params->empty('date') ? false : $this->getDate();
		$content = $this->render();
		include $this->layout;
	}

	public function render()
	{
		$level = $this->getRenderLevel();
		$content = "";
		$content .= $this->renderFiles();
		$content .= $this->renderDirs($level);
		return $content;
	}

	public function renderDirs($levelLimit)
	{
		$renderType = $this->params->get(self::RENDER, 0);
		$buffer = "<ul class=\"pages\">";
		foreach ($this->getListPages() as $id => $page) {
			if (!$renderTypePage = $this->params->get('custom', self::RENDER, $page->getName()))
				$renderTypePage = $renderType;
			$url = $page->getRoute();
			$title = $page->getTitle();
			$longTitle = FFRouter::pubRelativePath($page->path);
			$cover = $page->getCoverUrl();
			ob_start();
			include "tpl/views/li.$renderTypePage.php";
			$buffer .= ob_get_clean();

			if ($page->level < $levelLimit)
				$buffer .= $page->renderDirs($levelLimit);
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
					case 'html':
						$contenu = $this->adaptUrls($contenu);
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
		// ------------------------ include .js script files -----------------------------
		$jsFiles = array_map(function ($f) {
			return $f->getPath();
		}, $this->assets->getListFiles(false, 'js')); // scripts from assets
		if ($this->params->empty('bypass', 'scripts')) {
			$jsFiles = array_merge($jsFiles, glob("inc/js/*.js")); // scripts from /inc/js
		}
		if (!$this->params->empty('scripts')) {
			$jsFiles = array_merge($jsFiles, $this->params->get('scripts')); // scripts from page params
		}
		foreach ($jsFiles as $filePath) {
			$buffer .= "\t<script src=\"" . $this->url($filePath) . "\" async ></script>\n";
		}
		// ------------------------ include .css stylesheets -----------------------------
		$cssFiles = array_map(function ($f) {
			return $f->getPath();
		}, $this->assets->getListFiles(false, 'css'));
		if ($this->params->empty('bypass', 'styles')) {
			$cssFiles = array_merge($cssFiles, glob("inc/css/*.css"));
		}
		if (!$this->params->empty('styles')) {
			$cssFiles = array_merge($cssFiles, $this->params->get('styles'));
		}
		foreach ($cssFiles as $filePath) {
			$buffer .= "\t<link rel=\"stylesheet\" href=\"" . $this->url($filePath) . "\" />\n";
		}
		// ----------------------------- include favicon ---------------------------------
		if (!$this->params->empty('favicon'))
			$faviconUrl = $this->url($this->params->get('favicon'));
		else {
			if ($faviconFile = $this->assets->getFile('favicon', false))
				$faviconUrl = FFRouter::genUrl($faviconFile->getPath(), FFRouter::VALID_PATH);
			else {
				$faviconFiles = glob("inc/img/favicon.{ico,png}", GLOB_BRACE);
				if (isset($faviconFiles[0]))
					$faviconUrl = FFRouter::genUrl($faviconFiles[0]);
			}
		}
		if (isset($faviconUrl)) {
			$ext = substr($faviconUrl, strrpos($faviconUrl, '.') + 1);
			$mime = 'image/' . ($ext == 'ico' ? 'x-icon' : $ext);
			$buffer .= "\t<link rel=\"icon\" type=\"$mime\" href=\"$faviconUrl\" />\n";
		}
		// ----------------------- include external links arrow --------------------------
		if (!$this->params->isset('external links arrow') || $this->params->get('external links arrow')) {
			$buffer .= "\t<style>
		a[href^=\"http\"]:not([href^=\"" . FFRouter::getBasePath() . "\"]) {
			background-image: url(data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2010%2010%22%3E%3Cg%20fill%3D%22blue%22%3E%3Cg%20xmlns%3Adefault%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M8.9%208.9H1.1V1.1h2.8V0H1.1C.5%200%200%20.5%200%201.1v7.8C0%209.5.5%2010%201.1%2010h7.8c.6%200%201.1-.5%201.1-1.1V6.1H8.9v2.8z%22%2F%3E%3Cpath%20d%3D%22M10%200H5.6l1.8%201.8L4.2%205l.8.8%203.2-3.2L10%204.4V0z%22%2F%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E);
			background-size: 10px 10px;
			background-repeat: no-repeat;
			background-position: center right;
			padding-right: 13px;
		}
	</style>\n";
		}
		return $buffer;
	}

	public function genBreadcrumb($separator = " › ")
	{
		$p = $this->parent;
		$buffer = "";
		if (!empty($p)) {
			$url = $p->getRoute();
			$title = $p->getTitle();
			$buffer = "<a class=\"nav-links\" href=\"$url\">$title</a>$separator";
			$buffer = $p->genBreadcrumb() . $buffer;
		}
		return $buffer;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getCoverUrl()
	{
		$cover = $this->params->get('cover');
		if ($cover != App::pageDefaults('cover')) {
			$pcover = $this->params->get('cover');
			$coverPathType = FFRouter::analizeUrl($pcover);
			if (($coverPathType == FFRouter::ASSET && $this->assets->getFile(substr($pcover, 2)))
				|| $this->getFile($pcover))
				return $this->url($pcover, $coverPathType);
		}
		$files = $this->getListFiles(true);
		if (!empty($this->assets))
			$files = array_merge($this->assets->getListFiles(true), $files);
		foreach ($files as $file) {
			if ($file->type() == 'image' && $file->getName(false) != 'favicon') {
				$cover = $file->getPath();
				break;
			}
		}
		return $this->url($cover);
	}

	public function getRenderLevel()
	{
		if (is_array($this->params->get(self::RENDER)))
			return count($this->params->get(self::RENDER));
		return 0;
	}

	public function getDate()
	{
		$formats = App::dateFormats();
		$date = false;
		if (!$this->params->empty('date')) {
			$numFormat = 0;
			while (!$date) {
				$date = DateTimeImmutable::createFromFormat($formats[$numFormat], $this->params->get('date'));
				$numFormat++;
			}
			return $date;
		}
		return $this->getLastModif();
	}

	public function sort()
	{
		$sortParams = $this->params->get(self::SORT, 0);
		$type = $sortParams['type'];
		$order = $sortParams['order'] == 'asc' ? SORT_ASC : SORT_DESC;
		$recursive = isset($sortParams['recursive']) ? $sortParams['recursive'] : 0; // not really used yet
		switch ($type) {
			case 'lastModif':
			case 'name':
				$properties = [$type];
				break;
			case 'date':
				$properties = [$type, 'lastModif'];
				break;
			case 'title':
			default:
				$properties = ['title', 'name'];
				break;
		}
		$this->sort_recursive($properties, $order, $recursive);

		// heritage
		foreach ($this->getListPages() as $subDir) {
			if (!($subDir->params->empty(self::SORT)))
				$subDir->sort();
			elseif (!($this->params->empty(self::SORT, 1)))
				$subDir->sort($this->params->get(self::SORT, 1));
		}
		$this->sortCustom();
	}

	public function sortCustom()
	{
		if ($sort = $this->params->get('custom', self::SORT)) {
			$mode = self::UNSHIFT;
			$unshift = [];
			$push = [];
			foreach ($sort as $name) {
				$name = utf8_decode($name);
				if ($name == '*')
					$mode = self::PUSH;
				elseif ($this->fileExist($name)) {
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

	public function getHeritableParams($childName)
	{
		$params = [];
		if (array_search($childName, $this->ignoredList()) !== false) return $params;
		foreach (self::HERITABLE_PARAMS as $param) {
			if (count($this->params->get($param)) > 1)
				$params[$param] = array_slice($this->params->get($param), 1);
		}
		return $params;
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
			case FFRouter::ASSET:
				return FFRouter::genUrl($this->path . $this->params->get('assets dir') . DIRECTORY_SEPARATOR . substr($path, 2));
				break;
			case FFRouter::VALID_PATH:
				return FFRouter::genUrl($path);
				break;
			default:
				return $path;
				break;
		}
	}

	private function adaptUrls($content)
	{
		$content = preg_replace_callback('/(src|href)=[\'"](.+?)[\'"]/', function ($matches) {
			$url = $this->url($matches[2]);
			return $matches[1] . "=\"$url\"";
		}, $content);
		return $content;
	}

	public function getRoute(Type $var = null)
	{
		return FFRouter::genUrl($this->path);
	}

	public function ignoredList()
	{
		$ignored = !$this->params->empty('ignore') ? $this->params->get('ignore') : [];
		array_push($ignored, $this->params->get('assets dir'));
		return $ignored;
	}

	public function initAssets()
	{
		$name = $this->params->get('assets dir');
		$path = $this->path . $name . DIRECTORY_SEPARATOR;
		$this->assets = new Dir($path, $name, $this->level + 1, $this);
	}

	public function isAssetDir()
	{
		if (empty($this->parent))
			return false;
		return $this->parent->params->get('assets dir') == $this->name;
	}

	public function getListPages()
	{
		$listDirs = parent::getListDirs();
		unset($listDirs[$this->params->get('assets dir')]);  //removes assets dir
		return $listDirs;
	}

	public function addDir($path, $name, $ignored = false)
	{
		$subDir = parent::addDir($path, $name, $ignored);
		$subDir->init($this->getHeritableParams($name));
	}

	public function list_recursive($level = 0, $dirOnly = false, $ignore = [])
	{
		parent::list_recursive($level, $dirOnly, $this->ignoredList());
	}

	public function autoSetTitle()
	{
		if (!$this->params->empty('title'))
			$title = $this->params->get('title');
		else {
			$title = $this->name;
		}
		$this->title = ucwords($title);
		return $this;
	}

	/**
	 * @return array heritable params
	 */
	public function autoSetParent()
	{
		parent::autoSetParent();
		if (empty($this->parent)) return [];
		$this->parent->init();
		return $this->parent->getHeritableParams($this->name);
	}

}

?>