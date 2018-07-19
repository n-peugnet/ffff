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
		$this->publicDir = self::$params['system']['dirs']['public'];
		$this->urlBase = $urlBase;
		self::init();
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
			'defaults' => [
				'sort' => [
					0 => [
						'type' => 'alpha',
						'order' => 'asc'
					]
				],
				'render' => ['title'],
				'layout' => 'default',
				'favicon' => 'favicon'
			],
			'system' => [
				'dirs' => [
					'public' => 'public'
				]
			]
		];
		self::$params = new Params($defaults);
		self::$params->load(self::PARAM_FILE, '', Params::PUSH);
		Page::setDefaults(self::$params['defaults']);
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
			$page->list_recursive($page->getRenderLevel(), false, $page->getIgnored());
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