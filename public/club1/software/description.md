## Système d'exploitation

La partie software a consisté dans un premier temps à installer une version de GNU/linux. Je choisis d'utiliser **ubuntu server** pour plusieurs raisons :

-   sa 'minimal install' ne contenant pas de GUI
-   sa grande communauté
-   son suivi et mises-à-jour
-   son grand nombre de packets disponible
-   son installeur textuel pas à pas

Vient ensuite le choix de la version, je m'oriente vers la dernière version <abbr title="Long-Term Support" >LTS</abbr> : à ce moment là **16.04**. Tout allait bien jusqu'à la configuration du réseau. Malheureusement la carte mère était tellement récente que les drivers Intel compatibles n'étaient pas encore présents cette version d'ubuntu.
La version 17.10 aurait résolu ce problème mais il ne s'agit pas d'une LTS.

![installation d'ubuntu serveur](~/install_ubuntu_server.png)
![configuration de la carte réseau](~/config_ubuntu_server.png)

Mais heureusement un [article de ServerTheHome](https://www.servethehome.com/day-0-with-intel-atom-c3000-getting-nics-working/) correspondant exactement à ce problème ainsi qu'une <abbr title="Intelligent Platform Management Interface">IPMI</abbr> gérant les disque virtuels m'ont permit de faire fonctionner la carte réseau avec ubuntu 16.04.

## Logiciels

La mise en place de ce serveur avait notamment pour but de remplacer
mon actuel hebergement web, partagé avec mon frère. Il fallait donc le
mettre au niveau.

La première chose à faire pour cela a été d'installer un serveur HTTP, dans mon cas Apache2.

### Déploiement d'applications

Mise en place d'un certain nombre de services :

-   [GitLab](http://gitlab.club1.fr)
-   [Plex](http://club1.fr:32400/web/)
-   [PhpMyAdmin](http://club1.fr/phpmyadmin)
-   [Froxlor](http://club1.fr/froxlor)

## Continuité de service

Afin d'assurer une continuité de service plusieurs solutions ont été mises en oeuvre. La plus importante a été la mise en place d'une <abbr title="Ulimited Power Supply">UPS</abbr>. Pour en recevoir les alertes, il a fallu installer et configurer NUT.
![ssh nut config](~/ups.png)

Dans le cas d'une trop longue coupure de courant le serveur s'éteint de lui même avant que la batterie de l'UPS ne sépuise, le BIOS a ensuite été paramétré pour un redémarrage "on power in".
A l'aide de systemd j'ai pu faire en sorte de relancer tous mes services lors du démarrage.
