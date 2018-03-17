<!DOCTYPE html>
<html>
<head>
<?php 
include("head.php"); 
	//ini_set("display_errors",0);error_reporting(0);
if (isset($_GET['source'])) {
	$type = '';
	$pieces = explode('/', $_GET['source']);
	$nomFichier = $pieces[sizeof($pieces) - 1];
	$pieces = explode('.', $nomFichier);
	$ext = $pieces[sizeof($pieces) - 1];
	switch ($ext) {
		case 'pde':
			$type = 'processing';
			break;
	}
	if ($contenu = file_get_contents('./projets/' . $_GET['source'])) {
		?>
	<title>Code Source - <?php echo $nomFichier; ?></title>
	<meta name="Title" content="Code Source - <?php echo $nomFichier; ?>"/>
	<meta name="Keywords" content=""/>
	<meta name="Description" content="Code source du programme <?php echo $nomFichier; ?>"/>
</head>
<body>	

<?php 
include_once("analyticstracking.php");
?>

	<link rel="stylesheet" href="highlight/styles/arduino-light.css">
	<script src="assets/js/highlight/highlight.pack.js"></script>
	<script>hljs.initHighlightingOnLoad();</script>
	
<?php
echo '<pre><code class="' . $type . '">' . htmlspecialchars($contenu) . '</code></pre>';
?>
</body>
<?php

} else {
	echo "</head><body><p>Ce fichier n'existe pas</p></body><html>";
}
} else {
	echo "</head><body><p>Aucun fichier spécifié</p></body><html>";
}
?>
</html>