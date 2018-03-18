<?php
class Page extends Dir
{
	protected $basePath;
	protected $layout;

	public function init($basePath, $layout = "layout.php")
	{
		$this->basePath = $basePath;
		$this->layout = $layout;
		$this->list(true, false);
		if (empty($this->name))
			$this->setNameFromPath();
	}

	public function show()
	{
		$basePath = $this->basePath;
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
		$content .= $this->renderSubDirs();
		$content .= $this->renderFiles();
		return $content;
	}

	public function renderSubDirs()
	{
		$size = count($this->listSubDir);
		$str = "<pre><i>protected</i> 'listSubDir' <font color='#888a85'>=&gt;</font>
  <b>array</b> <i>(size=$size)</i>\n";
		foreach ($this->listSubDir as $id => $dir) {
			$size = strlen($id);
			$name = $dir->getName();
			$sName = strlen($name);
			$sSubDir = $dir->nbSubDir();
			$sFiles = $dir->nbFiles();
			$str .= "    $id <font color='#888a85'>=&gt;</font>
      <b>object</b>(<i>Page</i>)
        <i>protected</i> 'name' <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'$name'</font> <i>(length=$sName)</i>
        <i>protected</i> 'listSubDir' <font color='#888a85'>=&gt;</font> <b>array</b> (size=$sSubDir)
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
			$size = strlen($file);
			$str .= "    $index <font color='#888a85'>=&gt;</font> <small>string</small> <font color='#cc0000'>'$file'</font> <i>(length=$size)</i>\n";;
		}
		$str .= "</pre>";
		return $str;
	}


}

?>