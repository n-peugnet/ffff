<?php
class Page extends Dir
{
	protected $router;
	protected $layout;

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
	}

	public function show()
	{
		$basePath = $this->router->staticFilesBasePath();
		$title = $this->name;
		$siteName = "test";
		$content = $this->render();
		include $this->layout;
	}

	public function dump()
	{
		return var_dump($this);
	}

	public function render()
	{
		$content = "";
		$content .= $this->renderDirs();
		$content .= $this->renderFiles();
		return $content;
	}

	public function renderDirs()
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

	public function renderFiles()
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
}

?>