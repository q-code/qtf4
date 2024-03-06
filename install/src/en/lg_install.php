<?php

$L['Ok'] = 'Ok';
$L['Save'] = 'Save';
$L['Done'] = 'Done';
$L['Back'] = '&lt;&nbsp;Back';
$L['Next'] = 'Next&nbsp;&gt;';
$L['Finish'] = 'Finish';

$L['Load_from_template'] = 'Load from template';
$L['Board_email'] = 'Board e-mail';
$L['User'] = 'User';
$L['Password'] = 'Password';
$L['Installation'] = 'Installation';
$L['Install_db'] = 'Installing application tables';
$L['Connection_db'] = 'Connection parametres for the database';
$L['Database_type'] = 'Database type';
$L['Database_host'] = 'Database host';
$L['Database_name'] = 'Database name';
$L['Database_user'] = 'Database user ('.strtolower($L['User']).'/'.strtolower($L['Password']).')';
$L['Table_prefix'] = 'Table prefix';
$L['Create_tables'] = 'Create tables into database [%s]';
$L['Htablecreator'] = 'If the database user is not granted to create table, you can enter here an alternate login.';
$L['End_message'] = 'You can access the board as Admin.';
$L['Not_install_on_upgrade'] = 'If you upgrade from version 3.x, do NOT create the tables. You can continue to the next step.';
$L['Check_install'] = 'Check installation';

$L['Default_setting'] = 'default settings inserted.';
$L['Default_domain'] = 'default domain inserted.';
$L['Default_section'] = 'default forum inserted.';
$L['Default_user'] = 'default users inserted.';

$L['S_connect'] = 'Connection successful...';
$L['E_connect'] = "<b>Problem to connect database [%s] on server [%s]</b><br>Possible causes:<br>-&nbsp;Host is incorrect.<br>-&nbsp;Database is missing (or name is incorrect).<br>-&nbsp;User login (or password) is incorrect.<br>";
$L['S_save'] = 'Save successful...';
$L['E_save'] = "<br><br><b>Problem to write into /config/ folder</b><br><br>Possible causes:<br>&raquo;&nbsp;File /config/config_db.php is missing.<br>&raquo;&nbsp;File /config/config_db.php is read-only.<br>";

$L['N_install'] = 'This ends the installation procedure.';
$L['S_install'] = 'Installation successful...';
$L['E_install'] = "<b>Problem to create the table [%s] into dabase [%s]</b><br><br>Possible causes:<br>&raquo;&nbsp;Table already exists (delete existing table or use prefix).<br>&raquo;&nbsp;The user [%s] is not granted to create table.<br>";
$L['S_install_exit'] = 'Installation have been successfully completed.<br><br>Don\'t forget to :<br>- Turn the board on-line<br>- <b>Change your admin password</b><br>- Delete the /install/ folder<br><br>';

$L['Help_1'] = '<b>About database and logins</b>: For database other than SQLite, be sure that database or users are EXISTING as the script will just add tables in an existing database.<br><br><b>Database type</b>: The database type you are using.<br><br><b>Database host</b> (server name): If the database server is on the same server as the webserver, use "localhost". Azure uses "tcp:yourapp.database.windows.net,1433". Let the port empty unless you are using PostgreSQL (port 5432).<br><br><b>Database name</b>: Type here the name of your database. For SQLite, use the filename (ex: "quicktalk.db"). For Oracle Express use "//localhost/XE".<br><br><b>Table prefix</b>: If you have several boards in the same database, you can add a prefix to the tablename.<br><br><b>Database user</b>: User granted to perform update/delete actions in your database. The second administrator is not mandatory.';
$L['Help_2'] = '<b>Database tables</b>: This will create the tables in your database. If you are making an update, you must skip this step.<br>';
$L['Help_3'] = '<b>Board e-mail</b>: It\'s recommended to provide a contact e-mail address. This adress is visible in the page: General conditions.<br>';
$L['Prevent_install'] = 'Secure your installation';
$L['Disable_install'] = 'For security reason it\'s mandatory to encrypt or remove your install folder.<br>
<br>Select your preference:<br>';
$L['Disable'][0] = 'I will perform this action later';
$L['Disable'][1] = 'Encrypt my install folder (can be decrypted from the Administration page)';
$L['Disable'][2] = 'Delete my install folder (can be restored by copy/ftp from the source package)';