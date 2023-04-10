<?php

/**
 * PHP version 7
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package    QuickTalk
 * @author     Philippe Vandenberghe <info@qt-cute.org>
 * @copyright  2012 The PHP Group
 * @version    4.0 build:20230205
 */

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
$oH->selfurl = 'qtfm_antispam_install.php';
$oH->selfname = 'Installation module ANTISPAM '.$strVersion;

$bStep0 = true;
$bStep1 = true;
if ( isset($_SESSION[QT]['m_antispam']) ) unset($_SESSION[QT]['m_antispam']);
if ( isset($_SESSION[QT]['m_antispam_conf']) ) unset($_SESSION[QT]['m_antispam_conf']);

// STEP 0: check version

$strQTF = VERSION; if ( substr(VERSION,0,1)=='v') $strQTF = substr(VERSION,1);
$arrQTF = explode('.',$strQTF);
if ( intval($arrQTF[0])<2 ) $oH->error="Your QuickTalk version is $strQTF. Please, upgrade to QuickTalk 2.0 before installing this module...";
if ( !empty($oH->error) ) $bStep0 = false;

// STEP 1

if ( empty($oH->error) )
{
  $strFile = 'qtfm_antispam.php';
  if ( !file_exists($strFile) ) $oH->error="Missing file: $strFile<br>This module cannot be used.";
  $strFile = 'qtfm_antispam_adm.php';
  if ( !file_exists($strFile) ) $oH->error="Missing file: $strFile<br>This module cannot be used.";
  $strFile = 'qtfm_antispam_uninstall.php';
  if ( !file_exists($strFile) ) $oH->error="Missing file: $strFile<br>This module cannot be used.";
  if ( !empty($oH->error) ) $bStep1 = false;
}

// INSTALL

if ( empty($oH->error) )
{
  $oDB->exec( "DELETE FROM TABSETTING WHERE param='module_antispam' OR param='m_antispam' OR param='m_antispam_conf'" );
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('module_antispam','Antispam')" );
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_antispam','0')" );
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_antispam_conf','V 2.0 3')" );
  $_SESSION[QT]['module_antispam'] = 'Antispam';
  $_SESSION[QT]['m_antispam'] = 0;
  $_SESSION[QT]['m_antispam_conf'] = 'V 2.0 3';
  SMem::set('settingsage',time());
}

// --------
// Html start
// --------

include 'qtf_adm_inc_hd.php';

if ( !$bStep0 )
{
  echo '<p class="error">',$oH->error,'</p>';
  include 'qtf_adm_inc_ft.php';
  exit;
}

echo '<h2>Checking components</h2>';
if ( !$bStep1 )
{
  echo '<p class="error">',$oH->error,'</p>';
  include 'qtf_adm_inc_ft.php';
  exit;
}
echo '<p>Ok</p>';

echo '<h2>Database settings</h2>';
echo '<p>Ok</p>';

echo '<h2>Installation completed</h2>';

echo '<p style="margin:10px 0"><a href="qtfm_antispam_adm.php">Configure...</a></p>';

// --------
// Html end
// --------

include 'qtf_adm_inc_ft.php';