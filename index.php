
<?php
$publicPath = 'public';
$urlBase = $_SERVER['BASE_PATH']; // comes from the .htaccess

function autoload($className)
{
	require_once('lib/class.' . strtolower($className) . '.php');
}
spl_autoload_register("autoload");

foreach (glob("inc/*.php") as $fileName) {
	include_once $fileName;
}

$app = new App($publicPath, $urlBase);
$app->init();

?>