<!DOCTYPE html>
<html>
<head>
	
<?php include("head.php"); ?>
		
	<title>Nicolas Peugnet</title>
	<meta name="Title" content="Nicolas Peugnet - Projets"/>
	<meta name="Keywords" content=""/>
	<meta name="Description" content=""/>
</head>
<body>
	
<?php include_once("analyticstracking.php"); ?>
	
	<section id="accueil">
	<div class="titre">

	<?php include("nav.php");?> 
		<h1>Nicolas Peugnet - Projets</h1>
	</div>
	<?php
			function couverture($path,$niveau)
			{
				$fichiers = false;
				$favorite = false;
				$photoCouv = '';
				if($dossier = opendir('./projets/'.$path))
				{
					while(($element = readdir($dossier)) !== false && $favorite == false ) //lit le contenu du dossier tant qu'aucune favorite n'a été trouvée
					{
						if(is_dir('./projets/'.$path.'/'.$element) == false)                 //si $element n'est pas un dossier
						{
							$pieces = explode(".", $element); //permet de découper le nom du fichier selon les points
							$ext = $pieces[sizeof($pieces)-1];
							if ($ext != 'txt' && $ext != 'pde')
							{ 
								if ($fichiers == false)
								{
									$fichiers = true;                 //Il y a au moins un fichier dans $dossier
								}
								$photoCouv = $element;
							}
							if (substr($pieces[sizeof($pieces)-2] , -1) == '*')
							{ 
								$favorite = true;
							}
						}
						elseif($element != '.' && $element != '..')
						{
							couverture($path.'/'.$element, $niveau + 1);
						}
					}
					if ($fichiers == true)
					{
	?>	<li class="<?php if($niveau >= 2): echo 'sousdossier '; endif;?>couverture" id="projet_<?php echo $path; ?>">
			<a href="afficher?projet=<?php echo $path; ?>"><div><?php echo str_replace(array('_','/'), array(' ',' | '), substr($path, 5)); ?></div><img src="<?php echo './projets/'.$path.'/'.$photoCouv; ?>" alt="<?php echo $path.' '.substr($photoCouv,0,-4); ?>" /></a>
		</li>
	<?php			}
				}
			}  //fin de la fonction
			//SETUP
			if( $dossierProjets = opendir('./projets'))
			{
				echo "<ul>";
				while(($element = readdir($dossierProjets)) == true) {
					$dossierTri[] = $element;
				}
				rsort($dossierTri);
				foreach($dossierTri as $element)
				{
					if(is_dir('./projets/'.$element) == true && $element != '.' && $element != '..')
					{
	?>	<li><p><a href="afficher?projet=<?php echo $element ?>" class="date"><?php echo $element ?></a></p></li>
			<ul id="<?php echo $element ?>">
	<?php
						couverture($element, 0);
	?>	</ul>
	<?php
					}
				}
				echo "</ul>";
			}
			else
			{
				echo "il n'y a pas de projets";
			}
	?></section>
</body>
</html>