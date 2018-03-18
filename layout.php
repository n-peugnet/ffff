<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width" />
<?php if (empty($this->params['remove default style'])) : ?>
	<link rel="stylesheet" href="<?= $basePath ?>/public/assets/css/nicolaspeugnet.css" />
<?php endif; ?>
<?php
if (!empty($this->params['styles'])) {
	foreach ($this->params['styles'] as $style) { ?>
	<link rel="stylesheet" href="<?= $basePath ?>/<?= $this->path . $style ?>" />
<?php 
}
}
?>
	<link rel="icon" type="image/png" href="<?= $basePath ?>/public/assets/img/favicon.png" />
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
			<h2 class="nav"><a href="<?= $basePath ?>/a-propos/">à propos</a> | <a href="<?= $basePath ?>/contact/">Contact</a> | <a href="<?= $basePath ?>/cv/" target="_blank">CV</a></h2>
			<h1><?= $title ?></h1>
		</div>
		<p>
			<?= $content ?>
		</p>
	</section>
</body>
</html>