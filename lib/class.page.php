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
		$content .= $this->renderDirs();
		$content .= $this->renderFiles();
		return $content;
	}

	public function renderDirs()
	{
		$ignore = $this->getIgnoredDirs();
		$str = "<pre>";
		foreach ($this->listDirs as $id => $dir) {
			if (array_search($dir->getName(), $ignore) === false)
				$str .= $dir->toString($this->router->genUrl($dir->getPath()));
		}
		return $str . "</pre>";
	}

	public function renderFiles()
	{
		$ignore = $this->getIgnoredFiles();
		$str = "";
		foreach ($this->listFiles as $index => $file) {
			if (array_search($file->getName(), $ignore) === false) {
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

	public function getIgnoredDirs()
	{
		return $this->getIgnored("dir");
	}

	public function getIgnoredFiles()
	{
		return $this->getIgnored("file");
	}

	public function getIgnored($type)
	{
		$ignore = [];
		if (!empty($this->params['ignore'])) {
			foreach ($this->params['ignore'] as $name) {
				$isDir = substr($name, -1) == '/';
				if ($isDir)
					$name = substr($name, 0, -1);
				if (($type == "dir" && $isDir) || ($type == "file" && !$isDir))
					array_push($ignore, $name);
			}
		}
		return $ignore;
	}
}

?>