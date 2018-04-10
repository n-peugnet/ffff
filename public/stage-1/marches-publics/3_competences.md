### Compétences mises en oeuvre

#### A1.3.1 Test d'intégration et d'acceptation d'un service

Au cours du développement, 2 démonstrations ont été réalisés en présence des
futurs utilisateurs de l'application, lesquels ont fait des retours sur les points validés et ceux à retravailler.

#### A1.3.4 Déploiement d'un service

L'application a été déployée sur un serveur de la coopérative à l'adresse suivante :
[https://marchespublics.coopaname.coop](https://marchespublics.coopaname.coop)

La mise à jour de la version de production est assurée par git.

#### A1.4.1 Participation à un projet

J'ai réalisé une [liste des fonctionnalités à implémenter](https://docs.google.com/document/d/1b-F_Fh2jqYwZxvAMv9QheL0OfiN5-tlXOlkzrPJLRkA/edit?usp=sharing)
en fonction de leur importance

#### A2.1.1 Accompagnement des utilisateurs dans la prise en main d'un service

Lors des démontrations, une formation a été donnée aux membres de l'équipe de coopaname.
Lequels ont du transmettre leur savoir aux entrepreneurs.
Elle est actuellement correctement utilisée.

#### A2.2.1 Suivi et résolution d'incidents

Un problème interne à l'appliocation empêchait le bon fonctionnement de l'envoi
automatique de mails.

J'ai résolu cet incident en modifiant le contenu du fichier de configuration au
niveau des paramètres de la connexion SMTP.

#### A2.3.1 Identification, qualification et évaluation d'un problème

#### A4.1.1 Proposition d'une solution applicative

Une application avait été commencée utilisant le CMS Wordpress.

Ne maîtrisant pas ce logiciel et au vu des limitations qu'il m'imposait et des
délais à respecter, j'ai fait les choix de repartir sur une nouvelle base.
J'ai donc proposé une application PHP reliée à un serveur MySql basée sur une logique MVC.
Sans framework car je n'en connaissais pas encore.

#### A4.1.2 Conception ou adaptation de l'interface utilisateur d'une solution applicative

L'interface, inspirée de la première application Wordpress a été rapidement maquettée

#### A4.1.3 Conception ou adaptation d'une base de données

j'ai entièrement concu et réalisé la base de donnée, en voici le [modèle de données](https://drive.google.com/open?id=0B1T9tkseoI0qXzFJTDk3czl2TEE)

#### A4.1.4 Définition des caractéristiques d'une solution applicative

#### A4.1.7 Développement, utilisation ou adaptation de composants logiciels

#### A4.1.9 Rédaction d'une documentation technique

L'ensemble des caractéristiques techniques de l'application ont été compilées dans une [documentation technique](https://drive.google.com/open?id=0B1T9tkseoI0qUUM0Zk9IYXc3bzg) au format Word

#### A4.2.1 Analyse et correction d'un dysfonctionnement, d'un problème de qualité de sevice

#### A4.2.2 Adaptation d'une solution applicative aux évolutions de ses composants

Basculement sur l'API sécurisée de Geonames pour l'autocomplétion suite
à la mise à jour de Google Chrome empêchant les requêtes AJAX en HTTP
sur depuis un site en HTTPS.

#### A5.1.1 Mise en place d'une gestion de configuration

Utilisation de git pour la gestion de version et mise en place d'un
fichier de configuration PHP à la racine du site pour adapter la
configuration a l'environnement.

