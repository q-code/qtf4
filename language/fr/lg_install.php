<?php
/**
 * @var array $L
 */

$L['Ok'] = 'Ok';
$L['Save'] = 'Sauver';
$L['Done'] = 'Terminé';
$L['Back'] = '&lt;&nbsp;Précédent';
$L['Next'] = 'Suivant&nbsp;&gt;';
$L['Finish'] = 'Terminer';
$L['Restart'] = 'Redémarrer';
$L['Board_email'] = 'E-mail administrateur';
$L['User'] = 'Utilisateur';
$L['Password'] = 'Mot de passe';
$L['Installation'] = 'Installation';
$L['Install_db'] = 'Installation des tables';
$L['Connection_db'] = 'Connexion à la base de donnée (BDD)';
$L['Database_type'] = 'Type de BDD';
$L['Database_host'] = 'Hôte de la BDD';
$L['Database_name'] = 'Nom de la BDD';
$L['Database_user'] = 'Utilisateur BDD (login/password)';
$L['Table_prefix'] = 'Préfixe des tables';
$L['Htablecreator'] = 'Si l\'utilisateur BDD n\'a pas le droit de créer des tables, vous pouvez spécifier ici un autre login.';
$L['Create_tables'] = 'Créer les tables dans la base de donnée [%s]';
$L['End_message'] = 'Vous pouvez accéder au forum en tant qu\'Admin';
$L['Upgrade'] = 'Si vous faites un upgrade de la version 3.x, vos précédents paramètres sont affichés. Vous pouvez passer à l\'étape suivante.';
$L['Upgrade2'] = 'Si vous faites un upgrade de la version 3.x, vous ne devez PAS réinstaller les tables et vous pouvez passer à l\'étape suivante.';
$L['Check_install'] = 'Contrôler l\'installation';

$L['Default_setting'] = 'paramètres par défaut insérés.';
$L['Default_domain'] = 'domaine par défaut inséré.';
$L['Default_section'] = 'section par défaut insérée.';
$L['Default_user'] = 'utilisateurs par défaut insérés.';

$L['S_connect'] = 'Connexion réussie...';
$L['E_connect'] = "<b>Problème de connexion à la base de donnée [%s] sur le serveur [%s]</b><br>Causes possibles :<br>&raquo;&nbsp;Le nom du serveur est incorrect.<br>-&nbsp;Base de donnée manquante (ou nom incorrect).<br>-&nbsp;Le login (ou mot de passe) est incorrect.<br>";
$L['S_save'] = 'Savegarde réussie...';
$L['E_save'] = "<br><br><b>Problème pour écrire dans le répertoire /bin/</b><br><br>Causes possibles :<br>&raquo;&nbsp;Le fichier /config/config_db.php est absent.<br>&raquo;&nbsp;Le fichier /config/config_db.php est \'read-only\'.<br>";

$L['N_install'] = 'Ici se termine la procédure d\'installation.';
$L['S_install'] = 'Installation terminée...';
$L['E_install'] = "<b>Problème pour créer la table [%s] dans la base de donnée [%s]</b><br><br>Causes possibles :<br>&raquo;&nbsp;La table existe déjà (effacez-la ou utilisez un préfixe).<br>&raquo;&nbsp;L\'utilisateur [%s] n\'a pas le droit de créer des tables dans la base de donnée.<br>";
$L['S_install_exit'] = 'L\'installation s\'est correctement déroulée...<br><br>N\'oubliez pas de :<br>- Mettre le forum en-ligne (Panneau d\'administration)<br>- Changer le mot de passe de l\'administrateur<br>- Effacer le répertoire /install/<br><br>';

$L['Help_1'] = '<b>Pour la BDD et les logins</b>: Pour une BDD autre que SQLite, veillez à ce que la base de donnée et les utilisateurs EXISTENT car ce script ne fait qu\'ajouter les tables dans votre BDD.<br><br><b>Type de base de donnée</b>: Indiquez le type de base de donnée que vous utilisez.<br><br><b>Hôte de la BDD</b> (nom du serveur): Si la BDD est sur le même serveur que le serveur web, utilisez "localhost". Sur Azure, l\'hote a la forme "tcp:yourapp.database.windows.net,1433". Laissez le port vide à moins que vous n\'utilisiez une base de donnée PostgreSQL (port 5432).<br><br><b>Nom de la BDD</b>: Indiquez ici le nom de votre base de donnée. Pour SQLite c\'est le nom du fichier (ex: "quicktalk.db"). Pour Oracle Express, utilisez "//localhost/XE".<br><br><b>Préfixe des tables</b>: Si vous avez plusieurs applications dans la même BDD, vous pouvez ajouter un préfixe au nom des tables.<br><br><b>Utilisateur BDD</b>: L\'utilisateur ayant le droit d\'ajouter/modifier/effacer dans la base de donnée. Le second administrateur n\'est pas obligatoire.';
$L['Help_2'] = '<b>Database tables</b>: Ceci va installer les tables dans votre base de donnée. Si vous procédez  à un upgrade, veillez sauter cette étape.<br>';
$L['Help_3'] = '<b>E-mail administrateur</b>: Il est recommendé de donner une adresse de contact. Cette adresse est visible dans la page Conditions générales.<br>';

$L['Prevent_install'] = 'Sécuriser l\'installation';
$L['Disable_install'] = 'Pour des raisons de s&eacutecurit&eacute, il est obligatoire d\'encrypter ou d\'effacer le répertoire install lorsque la configuration est terminée.<br>
<br>Pour réaliser cela, sélectionnez l\'une des actions suivantes :<br>';
$L['Disable'][0] = 'Je ferai cette action plus tard';
$L['Disable'][1] = 'Encrypter le répertoire install (peut être décrypter via la page Administration)';
$L['Disable'][2] = 'Effacer le répertoire install (peut être restauré par un copy/ftp depuis le package source)';