<?php

/**
 * Class representing a Page as an extension of a directory. It is limited to
 * functions concerning the files and dirs it contains, such as sorting and
 * properties loading and other files related functions.
 */
class Page extends Dir
{
	protected $title;
	protected $assets;

	/** @var Params */
	protected $params;

	const SORT = 'sort';
	const RENDER = 'render';
	const UNSHIFT = 0;
	const PUSH = 1;
	const HERITABLE_PARAMS = [self::RENDER, self::SORT];

	public function init($heritedParams = [])
	{
		$this->params = new Params(App::pageDefaults(), $this->path . App::PARAM_FILE);
		if (empty($this->name))
			$this->autoSetName();
		if (empty($this->parent))
			$heritedParams = $this->autoSetParent();
		$this->params->override($heritedParams);
		$this->params->load(Params::OVERRIDE);
		$this->initAssets();
		$this->autoSetTitle();
		if ($this->level >= 0) {
			if (is_dir($this->assets->getPath()))
				$this->assets->list_recursive();
		}
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

	public function getParams()
	{
		return $this->params;
	}

	public function getParam(...$param)
	{
		return $this->params->get(...$param);
	}

	public function emptyParam(...$param)
	{
		return $this->params->empty(...$param);
	}

	public function getAssets()
	{
		return $this->assets;
	}

	public function getDate()
	{
		$formats = App::dateFormats();
		$date = false;
		if (!$this->params->empty('date')) {
			$numFormat = 0;
			while (!$date && $numFormat < count($formats)) {
				$date = DateTimeImmutable::createFromFormat($formats[$numFormat], $this->params->get('date'));
				$numFormat++;
			}
		}
		return $date ? $date : $this->getLastModif();
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

	/**
	 * Get all the subpages
	 * @return Page[]
	 */
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

