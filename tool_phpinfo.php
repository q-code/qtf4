<?php
session_start();
require 'config/config_db.php';
const APP = 'qtf'; // application file prefix
define('QT', APP.(defined('QDB_INSTALL') ? substr(QDB_INSTALL,-1) : '')); // memory namespace "qtx{n}"
if ( !isset($_SESSION[QT.'_usr']['role']) || $_SESSION[QT.'_usr']['role']!=='A' ) die('Access restricted to administrator.');
phpinfo();