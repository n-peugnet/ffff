<!DOCTYPE html>
<html>
<head>
	<?php 
	include("head.php"); 

	//ini_set("display_errors",0);error_reporting(0);

	$nbPhoto = 0;
	$scriptProcessing = false;
	if(isset($_GET['projet']) && $dossier = opendir('./projets/'.$_GET['projet']))
	{
		$titre = str_replace(array('_','/'), array(' ',' | '), $_GET['projet']);
	?>
	<title><?php echo $titre; ?> - Nicolas Peugnet</title>
	<meta name="Title" content="<?php echo $titre; ?> - Nicolas Peugnet"/>
	<meta name="Keywords" content="<?php echo $titre; ?>"/>
	<meta name="Description" content="<?php echo $titre; ?>, projet de Nicolas Peugnet"/>
	<script type="text/javascript"> 
		function GoToId(toucheClavier,last) { //permet de passer d'une image à une autre
			currentId = location.hash;
			if (currentId != '' && currentId != '#_') {
				if (toucheClavier == 27){
					window.location.href = "#_";
				} else if (toucheClavier == 39 || toucheClavier == 37) {
					var cut = currentId.split("#img");
					var currentNb = parseInt(cut[1]);
					if (toucheClavier == 39) {
						if (currentNb < last) {
							window.location.href = "#img" + (currentNb+1);
						}
					} else {
						if (currentNb > 1) {
							window.location.href = "#img" + (currentNb-1);
						}
					}
				}
			}
		}
	</script>
</head>
<body onKeyDown="GoToId(event.keyCode,nbPhoto)">

<?php 
	include_once("analyticstracking.php");
?>

	<section>
	<div class="titre">
	
	<?php include("nav.php"); ?>
		<h1><a href="/">Projets</a> › <?php 
		$i = 0;
		$lien = 'afficher?projet=';
		$pieces = explode('/', $_GET['projet']);
		for ($i; $i < sizeof($pieces)-1; $i++)
		{
			if ($i>0)
			{
				$lien = $lien.'/';
			}
			$lien = $lien.$pieces[$i];
			echo '<a href="'.$lien.'">'.str_replace('_', ' ', $pieces[$i]).'</a> › ';
		}
		echo str_replace('_', ' ', $pieces[$i]);
		?></h1>
		
		<?php
		echo '</div>';
				$chemin = './projets/'.$_GET['projet'];
				function couverture($base, $path)
				{
					$fichiers = false;
					$favorite = false;
					$photoCouv = '';
					if($dossier = opendir('./projets/'.$base.$path))
					{
						while(($element = readdir($dossier)) !== false && $favorite == false )
						{
							if(is_dir('./projets/'.$base.$path.'/'.$element) != true)
							{
								$pieces = explode(".", $element); //permet de découper le nom du fichier selon les points
								$ext = $pieces[sizeof($pieces)-1];
								if ($ext != 'txt' && $ext != 'pde')
								{ 
									if ($fichiers == false)
									{
										$fichiers = true;                 //Il y a au moins une image dans $dossier
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
								couverture($base, $path.'/'.$element);
							}
						}
								?><div class="couverture">
									<a href="afficher?projet=<?php echo $base.$path; ?>"><div><?php echo str_replace(array('_','/'), array(' ',' | '), $path); ?></div><img src="<?php echo 'projets/'.$base.$path.'/'.$photoCouv; ?>" alt="<?php echo $path; ?>" /></a>
								</div>
								<?php
					}
				}
				$dossier = opendir($chemin);
				while(($element = readdir($dossier)) != false) {
					if(is_dir($chemin.'/'.$element) == true && $element != '.' && $element != '..')
					{
						couverture($_GET['projet'].'/', $element);
					} else 
					{
						$dossierTri[] = $element;
					}
				}
				sort($dossierTri);
				foreach($dossierTri as $element)
				{
					if(is_dir($chemin.'/'.$element) == false)
					{
						$pieces = explode(".", $element); //permet de découper le nom du fichier selon les points
						$ext = $pieces[sizeof($pieces)-1];
						if ($ext == 'txt')
						{
							$contenu = file_get_contents($chemin.'/'.$element); 
							echo "<p>".$contenu."</p>";
						} 
						elseif ($ext == 'pde')
						{
							if (!$scriptProcessing)
								echo '<script src="processing.min.js"></script>';
		?>
		
		<canvas width="900" height="500" data-processing-sources="<?php echo $chemin.'/'.$element; ?>" class="photo"></canvas>
		<p><a href ="code.php?source=<?php echo $_GET['projet'].'/'.$element; ?>" target="_blank">Voir le code</a></p>

		<?php
						}
						else
						{
							$nbPhoto ++;
		?><a href="#img<?php echo $nbPhoto ?>" class="CadrePhoto" ><img src="<?php echo $chemin.'/'.$element; ?>" alt="<?php echo $_GET['projet'].' '.substr($element,0,-4); ?>" /></a>
		<div id="img<?php echo $nbPhoto ?>" class="lightbox" >
			<img class="photo" src="<?php echo $chemin.'/'.$element; ?>" alt="<?php echo $_GET['projet'].' '.substr($element,0,-4); ?>" />
			<a href="#img<?php echo $nbPhoto - 1 ?>" class="gauche" ><img src="fleche.svg" alt="précédent" /><div class="cale"></div></a>
			<a href="#_" class="close" ></a>
			<a href="#img<?php echo $nbPhoto + 1 ?>" class="droite" ><div class="cale"></div><img src="fleche.svg" alt="suivant" /></a>
		</div>
		
		<?php
						}
					}			
				}
		?><script type="text/javascript"> 
			var nbPhoto = <?php echo $nbPhoto ?>;
		</script>
	</section>
	<?php
		}
		else
		{
			include("nav.php");
			echo "<section><p>Il n'y a pas de projet correspondant.</p></section>";
		}
	?>
</body>
</html>