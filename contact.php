<!DOCTYPE html>
<html>
<head>

	<?php include("head.php"); ?>
	
	<title>Contact - Nicolas Peugnet</title>
	<meta name="Title" content="Contact - Nicolas Peugnet"/>
	<meta name="Keywords" content=""/>
	<meta name="Description" content="Contacter Nicolas Peugnet"/>
</head>
<body>
	
	<section>
	<div class="titre">
	<?php include("nav.php"); ?>
		<h1><a href="/">Nicolas Peugnet</a> › Contact</h1>
	</div>
		<h2>Par E-mail</h2>
		<p>Vous pouvez m'envoyer un mail grâce au formulaire ci-dessous ou à partir de <a href="mailto:n.peugnet@free.fr?subject=Contact via n.peugnet.fr">votre application de messagerie</a>.</p> 
		<form action="envoi.php" method="post">
			<div class="labels">
				<label for="nom">Nom</label>
				<br/>
				<label for="email">Adresse email</label>
				<br/>
				<label for="message">Message</label>
			</div>
			<div class="champs">
				<input id="nom" name="nom" type="text" value=""/>
				<br/>
				<input id="email" name="email" type="text" value=""/>
				<br/>
				<textarea id="message" name="message"></textarea>
				<br/>
				<button type="submit">Envoyer</button>
			</div>
		</form>
	</section>
</body>
</html>