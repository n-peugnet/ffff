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
		<div class="titre">
			<h1><?= $breadcrumb . $title ?></h1>
			<h2 class="nav">
				<a class="nav-links" href="<?= $this->url('/a-propos/') ?>">à propos</a> |
				<a class="nav-links" href="<?= $this->url('/contact/') ?>">Contact</a> |
				<a class="nav-links" href="<?= $this->url('/a-propos/cv/') ?>">CV</a>
			</h2>
		</div>
<?php if (!$this->params->empty('date')) : ?>
			<p class="date">date : <?= $date->format('d/m/Y') ?>, dernière édition : <?= $this->getLastModif()->format('d/m/Y') ?></p>
<?php endif; ?>
			<?= $content ?>
	</section>
</body>
</html>