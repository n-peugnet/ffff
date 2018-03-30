
<?php
function autoload($className)
{
	require_once('lib/class.' . strtolower($className) . '.php');
}
spl_autoload_register("autoload");

$publicPath = 'public';
$urlBase = $_SERVER['BASE_PATH']; // comes from the .htaccess
$app = new App($publicPath, $urlBase);
$app->init();

?>