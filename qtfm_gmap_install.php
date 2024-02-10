<?php // v4.0 build:20240210

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
require 'bin/init.php';
include translate('lg_adm.php');
if ( SUser::role()!=='A' ) die('Access denied');

// INITIALISE

$strVersion='v4.0';

$oH->selfurl = 'qtfm_gmap_install.php';
$oH->selfname = 'Installation module Gmap '.$strVersion;

$bStep1 = true;
$bStepZ = true;

// STEP 1

foreach(array('qtfm_gmap_uninstall.php','qtfm_gmap_adm.php','qtfm_gmap_load.php','qtfm_gmap_lib.php') as $strFile)
{
if ( !file_exists($strFile) ) $oH->error='Missing file: '.$strFile.'<br>This module cannot be used.';
}
if ( !empty($oH->error) ) $bStep1 = false;

// STEP Z
if ( empty($oH->error) )
{
  $_SESSION[QT]['module_gmap'] = 'Gmap';
  $_SESSION[QT]['m_gmap_gkey'] = '';
  $_SESSION[QT]['m_gmap_gcenter'] = '50.8468142558,4.35238838196';
  $_SESSION[QT]['m_gmap_gzoom'] = '10';
  $_SESSION[QT]['m_gmap_gbuttons'] = 'P011100'; //order: maptype.streetview.background.scale.fullscreen.mousewheel.geocode
  $_SESSION[QT]['m_gmap_gfind'] = 'Brussels, Belgium';
  $_SESSION[QT]['m_gmap_gsymbol'] = '0'; // "icon_filename". 0=Default symbol (v4.0 shadow is obsolete)
  $_SESSION[QT]['m_gmap_sections'] = 'U';  // "U,S,id" section id, U=userlist, S=search result
  $_SESSION[QT]['m_gmap_symbols'] = '0'; // symbols by user role (0=No symbols by userrole)
  $oDB->exec( "DELETE FROM TABSETTING WHERE param='module_gmap' OR param LIKE 'm_gmap_%'" );
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('module_gmap','" .   $_SESSION[QT]["module_gmap"].    "')" );
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_gmap_gkey','" .   $_SESSION[QT]["m_gmap_gkey"].    "')" );
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_gmap_gcenter','". $_SESSION[QT]["m_gmap_gcenter"]. "')" );
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_gmap_gzoom','" .  $_SESSION[QT]["m_gmap_gzoom"].   "')" );
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_gmap_gbuttons','".$_SESSION[QT]["m_gmap_gbuttons"]."')" );
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_gmap_gfind','" .  $_SESSION[QT]["m_gmap_gfind"].   "')" );
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_gmap_gsymbol','" .$_SESSION[QT]["m_gmap_gsymbol"]. "')" );
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_gmap_sections','".$_SESSION[QT]["m_gmap_sections"]."')" );
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_gmap_symbols','" .$_SESSION[QT]["m_gmap_symbols"]. "')" );
  SMem::set('settingsage',time());
}

// ------
// Html start
// ------
include 'qtf_adm_inc_hd.php';

echo '<h2>Checking components</h2>';
if ( !$bStep1 )
{
  echo '<p class="error">',$oH->error,'</p>';
  include 'qtf_adm_inc_ft.php';
  exit;
}
echo '<p>Ok</p>';
echo '<h2>Database settings</h2>';
if ( !$bStepZ )
{
  echo '<p class="error">',$oH->error,'</p>';
  include 'qtf_adm_inc_ft.php';
  exit;
}
echo '<p>Ok</p>';
echo '<h2>Installation completed</h2>';

if ( substr($_SESSION[QT]['version'],0,1)==='2' )
{
  echo '<p class="error">Your database version is 2.x. We recommend you to upgrade to 3.0 (use the installation wizard of QuickTalk).</p>';
}

// ------
// Html end
// ------