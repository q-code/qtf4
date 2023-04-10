==============================
UPGRADE QuickTalk to v4.0
==============================

To upgrade from version 3.x to 4.0, you can proceed with a normal installation (see here after).

  IMPORTANT: Do not delete your 'avatar', and 'upload' folders during the file transfert.
  Other files and folders can be overwritten.
  
  NOTE: The database configuration (bin/config.php) is now stored in config/config_db.php (with new variablenames)
  You can transfert your connection values into this new file, or use the installation procedure. 


==============================
INSTALLATION of QuickTalk v4.0
==============================

BEFORE starting the installation procedure, make sure you know:
- The type of database you will use (MySQL/MariaDB, SQLserver, PostgreSQL, Oracle or SQLite).
- Your database host (the name of your database server, ex: "localhost" when using web- and database-server on the same computer)
- The name of your database (where the QuickTalk can install the tables).
- The user name for this database (having the right to create table).
- The user password for this database.


1. Upload the application on your web server
--------------------------------------------
Just send (ftp) all the files and folders on your webserver. For example in a folder {www-root-path}/quicktalk/.

  IMPORTANT:
  If you are making an upgrade, do not overwrite the file config/config_db.php and the existing /avatar/ or /upload/ folders.


2. Configure the permissions
----------------------------
Without this configuration, the installation programme will not work and the database will not be configured.

Change the permission of the folder /config/ to make it writable (chmod 777).
Change the permission of the folders /avatar/ and subfolders to make them writable (chmod 777).
Change the permission of the folders /upload/ and subfolders to make them writable (chmod 777).

Permissions can be changed remotely with a FTP client 


3. Start the installation
-------------------------
From your web browser, go to the installation page: install/index.php
(example https://www.yourwebsite.com/quicktalk/install/index.php)


4. Clean up
-----------
When previous steps are completed, you can delete the /install/ folder on your website and set the permission for /config/ to readonly.

