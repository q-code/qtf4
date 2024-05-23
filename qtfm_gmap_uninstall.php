<?php // v4.0 build:20240210

session_start();
require 'bin/init.php'; /**
* @var CHtml $oH
* @var array $L
* @var CDatabase $oDB
*/
include translate('lg_adm.php');
if ( SUser::role()!=='A' ) die('Access denied');

// INITIALISE

$strVersion='v4.0';

$oH->name = 'Uninstall module Gmap '.$strVersion;

// UNINSTALL

$oDB->exec( "DELETE FROM TABSETTING WHERE param='module_gmap' OR param LIKE 'm_gmap_%'" );
unset($_SESSION[QT]['module_gmap']);
SMem::set('settingsage',time());

// ------
// Html start
// ------
include 'qtf_adm_inc_hd.php';

echo '<h2>Removing database settings</h2>
<p>Ok</p>
<h2>Uninstall completed</h2>
';

include 'qtf_adm_inc_ft.php';