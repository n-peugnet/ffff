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
						$mdParser = new MarkdownFF_Parser($this->page);
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
		if ($this->page->params->get('external links', 'arrow')) {
			$buffer .= "\t<link rel=\"stylesheet\" href=\"" . $this->url('res/styles/external-links.css') . "\" />\n";
			ob_start();
			include "res/styles/external-links.php";
			$buffer .= ob_get_clean();
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