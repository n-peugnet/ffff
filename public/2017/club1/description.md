## L'installation d'un server@home sous ubuntu 16.04.3

De le recherche du matériel à la mise en place de PhpMyAdmin, en passant
par l'installation du pilote de la carte réseau, ce projet aura été
riche en nouveautées et une expérience très formatrice.

![installation d'ubuntu serveur](~/install_ubuntu_server.png)
![configuration de la carte réseau](~/config_ubuntu_server.png)

### Compétences mises en oeuvre

#### A1.1.2 Étude de l'impact de l'intégration d'un service sur le système informatique

La mise en place de ce serveur avait notamment pour but de remplacer
mon actuel hebergement web, partagé avec mon frère. Il fallait donc le
mettre au niveau.
[Une liste](https://keep.google.com/#LIST/1611cfef855.bfcece6ba2254d64)
récapitule les services à installer.

#### A1.2.1 Élaboration et présentation d'un dossier de choix de solution technique

À l'occasion du choix de la solution matérielle, j'ai dressé [un tableau comparatif](https://docs.google.com/spreadsheets/d/1fqC80WLydTdOhbI8A3uf50F9sOipqHA9acqcoDRrncw/edit#gid=0)
des configurations répondant à notre cahier des charges

#### A1.3.2 Définition des éléments nécessaires à la continuité d'un service

-   Paramétrage du BIOS pour un redémarrage "on power in"
-   Paramétrage de systemd pour relancer les services au démarrage
-   Installation et configuration de NUT pour recevoir les alertes de l'UPS ![ssh nut config](~/ups.png)

#### A1.3.4 Déploiement d'un service

Mise en place d'un grand nombre de services :

-   [GitLab](http://gitlab.club1.fr)
-   [Plex](http://club1.fr:32400/web/)
-   [PhpMyAdmin](http://club1.fr/phpmyadmin)
-   [Froxlor](http://club1.fr/froxlor)

#### A3.2.1 Installation et configuration d'éléments d'infrastructure

-   Sélection, montage et installation des composants du serveur.
-   Installation d'Ubuntu serveur 16.04.3 et des pilotes necéssaires au
    fonctionnement de la carte réseau.
-   Connection d'un minitel en tant que terminal.
-   Configuration d'une UPS avec NUT.
-   Configuration du pare-feu du routeur et des redirections NAT

#### A5.1.6 Évaluation d'un investissement informatique

Calcul de la différence entre le cout d'un abonnement mensuel à un service
d'hebergement mutualisé plus celui d'un VPS et l'investissement du serveur
en prenant en compte sa consommation électrique

#### A5.2.2 Veille technologique

Micro blog de [synthèse des résultats de veille](/a-propos/veille/)

#### A5.2.3 Repérage des compléments de formation ou d'auto-formation

Réalisation d'un
[fichier récapitulatif](https://docs.google.com/document/d/1FStutJIX12AZzZb_YYt_TVha7wh8uWrZ9WYdOEaU6MM/edit#)
des besoins de formations et des cours suivis ou à suivre.

#### A5.2.4 Étude d'une technologie, d'un composant, d'un outil ou d'une méthode