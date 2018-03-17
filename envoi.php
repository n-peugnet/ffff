<!DOCTYPE html>
<html>
	<head>
	
		<?php include("head.php"); ?>
		
	</head>
<?php
	$mailRegex = "#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#";
	$back = '<br/><a href="javascript:history.back()">Revenir au formulaire</a></p>';
 
	if(isset($_POST) && isset($_POST['nom']) && isset($_POST['email']) && isset($_POST['message']))
	{
		if(!empty($_POST['nom']) && !empty($_POST['email']) && !empty($_POST['message']))
		{
			if (preg_match($mailRegex, $_POST['email']))
			{
				$destinataire = "nicolas.pgnt@gmail.com";
				$sujet = "Contact via n.peugnet.free.fr";
				$message = "Nom : ".$_POST['nom']."\r\nAdresse email : ".$_POST['email']."\r\nMessage : ".$_POST['message']."\r\n";
				$entete = 'From: '.$_POST['email']."\r\n".
						'Reply-To: '.$_POST['email']."\r\n".
						'X-Mailer: PHP/'.phpversion();
				if (mail($destinataire,$sujet,$message,$entete))
				{
					echo '<p>Message envoyé <br/><a href="contact.php">Ok</a></p>';
				}
				else 
				{
					echo '<p>Une erreur est survenue lors de l\'envoi du formulaire par email'.$back;
				}
			}
			else
			{
			echo '<p>Adresse e-mail incorrecte'.$back;
			}
		}
		else
		{
			echo '<p>Vous n\'avez pas rempli tous les champs du formulaire de contact'.$back;
		}
	}
?>
</html>