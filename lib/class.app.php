<?php
class App
{
	protected $publicDir = "public";
	protected $urlBase = "";
	protected $router;
	protected static $params;

	const PARAM_FILE = 'params.yaml';

	public function __construct($urlBase)
	{
		self::init();
		$this->publicDir = self::$params->get('system', 'public dir');
		$this->setUrlBase($urlBase);
		FFRouter::init($this->publicDir, $this->urlBase);
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

	protected function setUrlBase($urlBase)
	{
		if (http_response_code() != 404) {
			$this->urlBase = $urlBase;
		} else {
			// fallback hack when RewriteModule is not enabled
			$htaccess = file_get_contents('.htaccess');
			preg_match('/ErrorDocument 404 (\/.+?)\/index.php/', $htaccess, $matches);
			$this->urlBase = isset($matches[1]) ? $matches[1] : "";
		}
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
			if ($page->isAssetDir()) {
				$this->showNotFound();
			} else {
				$this->showPage($page);
			}
		} else {
			$this->showNotFound();
		}
	}

	public function showPage($page)
	{
		http_response_code(200);
		$page->list_recursive($page->getRenderLevel(), false);
		$page->sort();
		$engine = new FFEngine($page);
		$engine->show();
	}

	public function showNotFound()
	{
		http_response_code(404);
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
