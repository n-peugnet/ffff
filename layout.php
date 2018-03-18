<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width" />
	<link rel="stylesheet" href="<?= $basePath ?>/assets/css/nicolaspeugnet.css" />
	<link rel="icon" type="image/png" href="<?= $basePath ?>/assets/img/favicon.png" />
	<meta name="Revisit-After" content="15 days"/>
	<meta name="Robots" content="All"/>
	<title><?= $title ?> - <?= $siteName ?></title>
	<meta name="Title" content="$title"/>
	<meta name="Keywords" content=""/>
	<meta name="Description" content=""/>
</head>
<body>
	<section id="accueil">
		<div class="titre">

		<?php include("nav.php"); ?> 
			<h1><?= $title ?></h1>
		</div>
		<p>
			<?= $content ?>
		</p>
	</section>
</body>
</html>