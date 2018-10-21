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
		$this->publicDir = self::$params->get('system', 'public dir');
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
				'cover' => '/inc/img/default-cover.png',
				'sort' => [
					0 => [
						'type' => 'title',
						'order' => 'asc'
					]
				],
				'render' => ['cover'],
				'layout' => 'default',
				'assets dir' => 'assets',
				'external links' => [
					'arrow' => true,
					'new tab' => true
				]
			],
			'system' => [
				'public dir' => 'public'
			]
		];
		self::$params = new Params($defaults, self::PARAM_FILE);
		self::$params->load(Params::PUSH);
	}

	public static function siteName()
	{
		return self::$params->get('site', 'name');
	}

	public static function siteDescription()
	{
		return self::$params->get('site', 'description');
	}

	public static function dateFormats()
	{
		return self::$params->get('date formats');
	}

	public static function pageDefaults($key = false)
	{
		$defaults = self::$params->get('page defaults');
		if ($key) {
			if (isset($defaults[$key]))
				return $defaults[$key];
			else
				return false;
		}
		return $defaults;
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
			$engine = new FFEngine($page);
			$engine->show();
		} else {
			$this->showNotFound();
		}
	}

	function showNotFound()
	{
		$page = new Page($this->publicDir . DIRECTORY_SEPARATOR . '404' . DIRECTORY_SEPARATOR);
		$page->init();
		$page->list_recursive(0);
		$engine = new FFEngine($page);
		$engine->show();
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
