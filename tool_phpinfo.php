<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require 'config/config_db.php';
const APP = 'qtf'; // application file prefix

// check log as admin
define('QT', APP.(defined('QDB_INSTALL') ? substr(QDB_INSTALL,-1) : '')); // memory namespace "qtx{n}"
if ( isset($_SESSION[QT.'_usr']['role']) && $_SESSION[QT.'_usr']['role']==='A' ) {
  phpinfo();
  exit;
}
// alternate using GET
if ( isset($_GET['app']) && $_GET['app']===APP ) {
  phpinfo();
  exit;
}

die('Access restricted to administrator (log as admin or ?app=APP).');