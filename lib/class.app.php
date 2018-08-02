<?php
class App
{
	protected $publicDir = "";
	protected $urlBase = "";
	protected $router;
	protected static $params;

	const PARAM_FILE = 'params.yaml';

	public function __construct($urlBase)
	{
		self::init();
		$this->publicDir = self::$params['system']['public dir'];
		$this->urlBase = $urlBase;
		FFRouter::init($this->publicDir, $urlBase);
	}

	public static function init()
	{
		$defaults = [
			'site' => [
				'name' => 'test',
				'description' => 'a longer test'
			],
			'date formats' => ['Y-m-d H:i:s'],
			'page defaults' => [
				'sort' => [
					0 => [
						'type' => 'title',
						'order' => 'asc'
					]
				],
				'render' => ['title'],
				'layout' => 'default',
				'assets dir' => 'assets'
			],
			'system' => [
				'public dir' => 'public'
			]
		];
		self::$params = new Params($defaults);
		self::$params->load(self::PARAM_FILE, '', Params::PUSH);
	}

	public static function siteName()
	{
		return self::$params['site']['name'];
	}

	public static function siteDescription()
	{
		return self::$params['site']['description'];
	}

	public static function dateFormats()
	{
		return self::$params['date formats'];
	}

	public static function pageDefaults()
	{
		return self::$params['page defaults'];
	}

	public function run()
	{
		if ($path = FFRouter::matchRoute()) {
			// adds trailing slash
			if (substr($path, -1) != DIRECTORY_SEPARATOR) {
				$this->redirectToRoute($path . DIRECTORY_SEPARATOR);
			}
			// include personnal php scripts
			foreach (glob("inc/*.php") as $fileName) {
				include_once $fileName;
			}
			// show the page
			$page = new Page($path);
			$page->init();
			if ($page->isAssetDir())
				$this->showNotFound();
			$page->list_recursive($page->getRenderLevel(), false);
			$page->sort();
			$page->show();
		} else {
			$this->showNotFound();
		}
	}

	function showNotFound()
	{
		$page = new Page($this->publicDir . DIRECTORY_SEPARATOR . '404' . DIRECTORY_SEPARATOR);
		$page->init();
		$page->list_recursive(0);
		$page->show();
		die;
	}

	public function redirectTo($url)
	{
		header("Location: $url");
	}

	public function redirectToRoute($path)
	{
		$url = FFRouter::genUrl($path);
		$this->redirectTo($url);
	}
}
?>