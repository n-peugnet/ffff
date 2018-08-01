**ffff** est un _flat file CMS_ codé en **PHP**. Il a donc pour seule base de données le système de fichiers. Ce type de CMS permet de créer des sites très facilement et avec peu de connaissances. En effet il suffit d'un accès FTP pour copier les dossiers du CMS et d'un éditeur de texte pour en créer le contenu.

Le principle est simple : dans le dossier `public`, _une page_ correspond à _un dossier_. Le contenu d'une page est **affiché automatiquement** et correspond à l'ensemble des fichiers textes et images que le dossier contient. Un sous-dossier correspond ainsi à une page enfant.

Il existe cependant 2 exceptions à ce principe :

1.  chaque page peut avoir **un dossier particulier** `assets` qui sera ignoré par défaut et qui ne correspondra donc pas à une page enfant
2.  chaque page peut avoir **un fichier particulier** `params.yaml` qui permet de spécifier les paramètres d'une pages

Ce projet est visible sur [github](https://github.com/n-peugnet/ffff).