<?php
class Page extends Dir
{
	protected $router;
	protected $layout;
	protected $params;

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
		$this->loadParams();
	}

	public function show()
	{
		$basePath = $this->router->getBasePath();
		$title = $this->name;
		$siteName = "test";
		$content = $this->render();
		include $this->layout;
	}

	public function render()
	{
		$content = "";
		$content .= $this->dumpDirs();
		$content .= $this->renderFiles();
		return $content;
	}

	public function renderFiles()
	{
		$str = "";
		foreach ($this->listFiles as $index => $file) {
			switch ($file->type()) {
				case 'image':
					$str .= '<img class="CadrePhoto" src="' . $this->router->genUrl($file->getPath()) . '" alt="' . $file->getName() . '"/>';
					break;

				case 'text':
					$contenu = file_get_contents($file->getPath());
					$str .= "<p>" . $contenu . "</p>";
					break;
			}
		}
		$str .= "</pre>";
		return $str;
	}

	public function dump()
	{
		$content = "";
		$content .= $this->dumpDirs();
		$content .= $this->dumpFiles();
		return $content;
	}

	public function dumpDirs()
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

	public function loadParams()
	{
		$this->params = Spyc::YAMLLoad($this->path . 'params.yaml');
	}
}

?>