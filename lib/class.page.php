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
		$basePath = $this->router->getBasePath();
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
			$url = $this->router->genUrl($dir->getPath());
			$size = strlen($id);
			$name = $dir->getName();
			$sName = strlen($name);
			$sDirs = $dir->nbDirs();
			$sFiles = $dir->nbFiles();
			$str .= "    <a href='$url'>$id</a> <font color='#888a85'>=&gt;</font>
      <b>object</b>(<i>Page</i>)
        <i>protected</i> 'name' <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'$name'</font> <i>(length=$sName)</i>
        <i>protected</i> 'listDirs' <font color='#888a85'>=&gt;</font> <b>array</b> (size=$sDirs)
        <i>protected</i> 'listFiles' <font color='#888a85'>=&gt;</font> <b>array</b> (size=$sFiles)\n";
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
			$name = $file->getName();
			$size = strlen($name);
			$str .= "    $index <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'$name'</font> <i>(length=$size)</i>\n";;
		}
		$str .= "</pre>";
		return $str;
	}


}

?>