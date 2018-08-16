
<?php
$urlBase = $_SERVER['BASE_PATH']; // comes from the .htaccess

function autoload($className)
{
	require_once('lib/class.' . strtolower($className) . '.php');
}
spl_autoload_register("autoload");
require_once "lib/simple_html_dom.php";

foreach (glob("inc/php/*.php") as $fileName) {
	include_once $fileName;
}

$app = new App($urlBase);
$app->run();

?>