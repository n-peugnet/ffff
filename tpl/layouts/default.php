<!DOCTYPE html>
<html>
<head>
	<?= $head ?>
	<title><?= $title ?> - <?= $siteName ?></title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width" />
	<meta name="Revisit-After" content="15 days"/>
	<meta name="Robots" content="All"/>
	<meta name="Title" content="<?= $title ?>"/>
	<meta name="Keywords" content=""/>
	<meta name="Description" content=""/>
</head>
<body>
	<section id="accueil">
			<h1><?= $page->genBreadCrumb() . $title ?></h1>
<?php if ($date) : ?>
			<p class="date">date: <?= $date->format('d/m/Y') ?></p>
<?php endif; ?>
			<?= $content ?>
	</section>
</body>
</html>