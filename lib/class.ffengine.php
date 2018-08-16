<?php
class FFEngine
{
	protected $page;
	protected $layout = "tpl/layouts/default.php";

	/**
	 * @param Page $page
	 */
	public function __construct($page)
	{
		$this->page = $page;
		$layout = $page->getParam('layout');
		$this->layout = "tpl/layouts/$layout.php";
	}

	public function show()
	{
		$page = $this->page;
		$head = $this->genHead();
		$title = $page->getTitle();
		$siteName = App::siteName();
		$date = $page->getParam('date') === null ? false : $page->getDate();
		$content = $this->renderContent();
		include $this->layout;
	}

	public function renderContent()
	{
		$level = $this->page->getRenderLevel();
		$buffer = "";
		$buffer .= $this->renderFiles();
		$buffer .= $this->renderDirs($this->page, $level);
		return $buffer;
	}

	public function renderDirs($p, $levelLimit)
	{
		$renderType = $p->getParam(Page::RENDER)[0];
		$buffer = "<ul class=\"pages\">";
		foreach ($p->getListPages() as $id => $page) {
			if (!$viewName = $p->getParam('custom', Page::RENDER, $page->getName()))
				$viewName = $renderType;
			$url = $page->getRoute();
			$title = $page->getTitle();
			$titleLong = FFRouter::pubRelativePath($page->path);
			$cover = $page->getCoverUrl();
			$data = compact("url", "title", "titleLong", "cover", "page", "viewName");
			$View = new View('li', $viewName, $data);

			if ($page->level < $levelLimit)
				$View->insert($this->renderDirs($page, $levelLimit));
			$buffer .= $View;
		}
		return $buffer . "</ul>";
	}

	public function renderFiles()
	{
		$buffer = "";
		foreach ($this->page->getListFiles() as $index => $file) {
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
		}, $this->page->assets->getListFiles(false, 'js')); // scripts from assets
		if ($this->page->params->empty('bypass', 'scripts')) {
			$jsFiles = array_merge($jsFiles, glob("inc/js/*.js")); // scripts from /inc/js
		}
		if (!$this->page->params->empty('scripts')) {
			$jsFiles = array_merge($jsFiles, $this->page->params->get('scripts')); // scripts from page params
		}
		foreach ($jsFiles as $filePath) {
			$buffer .= "\t<script src=\"" . $this->url($filePath) . "\" async ></script>\n";
		}
		// ------------------------ include .css stylesheets -----------------------------
		$cssFiles = array_map(function ($f) {
			return $f->getPath();
		}, $this->page->assets->getListFiles(false, 'css'));
		if ($this->page->params->empty('bypass', 'styles')) {
			$cssFiles = array_merge($cssFiles, glob("inc/css/*.css"));
		}
		if (!$this->page->params->empty('styles')) {
			$cssFiles = array_merge($cssFiles, $this->page->params->get('styles'));
		}
		foreach ($cssFiles as $filePath) {
			$buffer .= "\t<link rel=\"stylesheet\" href=\"" . $this->url($filePath) . "\" />\n";
		}
		// ----------------------------- include favicon ---------------------------------
		if (!$this->page->params->empty('favicon'))
			$faviconUrl = $this->page->url($this->params->get('favicon'));
		else {
			if ($faviconFile = $this->page->assets->getFile('favicon', false))
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
		if (!$this->page->params->isset('external links arrow') || $this->page->params->get('external links arrow')) {
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

	public function breadCrumb($separator = " › ")
	{
		return $this->genBreadcrumb($this->page, $separator);
	}

	protected function genBreadcrumb($page, $separator)
	{
		$p = $page->getParent();
		$buffer = "";
		if (!empty($p)) {
			$url = $p->getRoute();
			$title = $p->getTitle();
			$buffer = "<a class=\"nav-links\" href=\"$url\">$title</a>$separator";
			$buffer = $this->genBreadcrumb($p, $separator) . $buffer;
		}
		return $buffer;
	}

	private function adaptUrls($content)
	{
		$content = preg_replace_callback('/(src|href)=[\'"](.+?)[\'"]/', function ($matches) {
			$url = $this->url($matches[2]);
			return $matches[1] . "=\"$url\"";
		}, $content);
		return $content;
	}

	public function url($path, $type = false)
	{
		return $this->page->url($path, $type);
	}
}

?>