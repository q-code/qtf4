==============================
UPGRADE vers QuickTalk v4.0
==============================

Pour passer de la version 3.x à 4.0, vous pouvez procéder à une installation standard (voir ci-après).

  ATTENTION: n'effacez pas les répertoires /avatar/ et /upload/ durant le transfert des fichiers.
  Les autres fichiers et répertoires peuvent être écrasés.
  
  NOTE: La configuration de la base de données (bin/config.php) est maintenant placée dans config/config_db.php (avec de nouvelles variables)
  Vous pouvez transférer les valeurs dans ce nouveau fichier ou bien utiliser le script d'installation


==============================
INSTALLATION de QuickTalk v4.0
==============================

AVANT de commencer l'installation, assurez-vous que vous connaissez :
- Le type de base de donnée que vous utilisez (MySQL/MariaDB, SQLserver, PostgreSQL, Oracle, ou SQLite).
- Le nom de l'hote de votre base de donnée (le nom du serveur de base de donnée, ex: "localhost" si les web- et database-serveur sont sur le même ordinateur).
- Le nom de votre base de donnée (où QuickTalk peut installer ses tables).
- Le nom d'utilisateur pour cette base de donnée (ayant le droit de créer des tables).
- Le mot de passe de celui-ci.


1. Envoyez l'application sur votre espace web
---------------------------------------------
Vous devez simplement envoyer (ftp) tous les fichiers et repertoires sur votre espace web (par exemple dans un répertoire /quicktalk/).

  IMPORTANT:
  Si vous faites un upgrade, n'effacez pas le fichier config/config_db.php ni les répertoires /avatar/ et /upload/.


2. Définir les permissions
--------------------------
Sans cette étape, le programme d'installation ne pourra pas s'exécuter et votre base de donnée ne pourra être configurée.

Changer les permissions sur le repertoire /config/ afin qu'il soit inscriptible (chmod 777)
Changer les permissions sur les répertoires /avatar/ et sous-répertoires afin qu'ils soient inscriptibles (chmod 777)
Changer les permissions sur les répertoires /upload/ et sous-répertoires afin qu'ils soient inscriptibles (chmod 777)

Les permissions peuvent être changée à distance avec un client FTP.


3. Lancer l'installation
------------------------
Depuis votre navigateur internet, allez sur la page d'installation : install/index.php
(ex: Tappez l'url https://www.votresiteweb.com/quicktalk/install/index.php)


4. Nettoyage
------------
Lorsque les étapes précédentes sont terminées, vous pouvez effacer le répertoire /install/ et changer les permissions du repertoire /config/ en lecture seule.