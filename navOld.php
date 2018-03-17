	<nav>
		<div class="couverture menu">
			<a href="/" ><img id="retour" src="retour.svg" alt="Retour" border="0"/><div>Tous les projets</div></a>
		</div>
		<a id="Button" href="#" onclick="Affichage('Menu');return false;"><img id="ButtonImg" src="menu.svg" alt="menu"/></a>
		
		<div id="Menu">
			<?php 
			if(isset($_GET['projet']))  //si la page active est la page afficher une projet...
			{
				$pieces = explode("/", $_GET['projet']);
				$actif = $pieces[0];  //... cette projet prendra la classe actif
			}else  //autrement
			{
				$actif = 'NaP';  //... aucune projet ne prendra la classe actif
			}
			
			function Menu($path)
			{
				$favorite = false;
				$photoCouv = '';
				if($dossier = opendir('./projets/'.$path))
				{
					while(($element = readdir($dossier)) !== false && $favorite == false ) //lit le contenu du dossier tant qu'aucune favorite n'a été trouvée
					{
						if(is_dir('./projets/'.$path.'/'.$element) == false)                 //si $element n'est pas un dossier
						{
							$pieces = explode(".", $element); //permet de découper le nom du fichier selon les points
							if ($pieces[sizeof($pieces)-1] != 'txt')
							{ 
								if (substr($pieces[count($pieces)-2] , -1) == '*')
								{ 
									return $element;
								}
							}
						}
						elseif($element != '.' && $element != '..' && $favorite == false)
						{
							$result = Menu($path.'/'.$element, $niveau + 1);
							if ($result !='')
							{
								return $element.'/'.$result;
							}
						}
					}
				}
				
				
			}
			//SETUP
			if( $dossierProjets = opendir('./projets'))  //ouvre le dossier contenant les dossiers de projets
			{
				while(($element = readdir($dossierProjets)) != false) {
					if(is_dir('./projets/'.$element) == true && $element != '.' && $element != '..')
					{
						$dossierTriMenu[] = $element;
					}
				}
				rsort($dossierTriMenu);
				foreach($dossierTriMenu as $element) //pour tous les elements de ce dossier...
				{
					if(is_dir('./projets/'.$element) != false && $element != '.' && $element != '..')
					{
						$result = Menu($element);  //...fonction iterative pour afficher en les dossiers en ajoutant une tabulation
						if ($result !='')
							{
	?>	<div class="couverture menu<?php if($element == $actif) : echo ' actif'; endif;?>" id="year_<?php echo $element; ?>">
			<a href="afficher?projet=<?php echo $element; ?>"><div><?php echo $element; ?></div><img src="<?php echo './projets/'.$element.'/'.$result; ?>" alt="<?php echo $element.' '.substr($result,0,-4); ?>" /></a>
		</div>
	<?php
								
							}
					}
				}
			}else
			{
				echo "<p>il n'y a pas de projet</p>";
			}
			?>
			<br>
			<div class="couverture menu<?php 
					if ($_SERVER['PHP_SELF']=='/about.php' || $_SERVER['PHP_SELF']=='/about')
					{
						$actif = 'about';
						echo ' actif';
					}
				?>">
				<a href="about"><img src="svgabout.svg" alt="A Propos" border="0" id="about"/><div>À Propos</div></a>
			</div>
		</div>
	</nav>
	
	<script language=javascript> 
		var width = (window.innerWidth | 0) ? window.innerWidth : screen.width;
		function Affichage(object_id) { 
			var obj = document.getElementById(object_id); 
			if(obj.style.display == 'block') {
				obj.style.display='none' ;
				document.getElementById('ButtonImg').src="menu.svg";
			} else {
				obj.style.display='block' ;
				document.getElementById('ButtonImg').src="close.svg";
			}
		} 
		if (width < 721){
			document.getElementById('Menu').style.display='none' ; 
			document.getElementById('Button').style.display='block' ;
		}
			
	</script>