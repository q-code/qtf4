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
 * @version    4.0 build:20221111
 */

session_start();
require 'bin/init.php';
/**
* @var CVip $oV'lg_adm.php'
* @var cHtml $oHtml
* @var array $L
* @var CDatabase $oDB
*/
include translate('lg_adm.php');
if ( SUser::role()!=='A' ) die('Access denied');

// INITIALISE

$strVersion='v4.0';
$oV->selfurl = 'qtfm_export_install.php';
$oV->selfname = 'Installation module EXPORT '.$strVersion;

$bStep1 = true;
$bStep2 = true;
$bStep3 = true;

// STEP 1

$strFile = 'qtfm_export_uninstall.php';
if ( !file_exists($strFile) ) $error='Missing file: '.$strFile.'<br>This module cannot be used.';
$strFile = 'qtfm_export_adm.php';
if ( !file_exists($strFile) ) $error='Missing file: '.$strFile.'<br>This module cannot be used.';
if ( !empty($error) ) $bStep1 = false;

// STEP Z
if ( empty($error) )
{
  $oDB->exec( 'DELETE FROM TABSETTING WHERE param="module_export" OR param="m_export_conf"');
  $oDB->exec( 'INSERT INTO TABSETTING (param,setting) VALUES ("module_export","Export")');
  SMem::set('settingsage',time());
}


// --------
// Html start
// --------
include 'qtf_adm_inc_hd.php';

echo '<h2>Checking components</h2>';
if ( !$bStep1 )
{
  echo '<p class="error">',$error,'</p>';
  include 'qtf_adm_inc_ft.php';
  exit;
}
echo '<p>Ok</p>';
echo '<h2>Checking export subdirectory</h2>'.PHP_EOL;
if ( !$bStep2 )
{
  echo '<p class="error">',$error,'</p>';
  include 'qtf_adm_inc_ft.php';
  exit;
}
echo '<p>Ok</p>';
echo '<h2>Database settings</h2>';
if ( !$bStep3 )
{
  echo '<p class="error">',$error,'</p>';
  include 'qtf_adm_inc_ft.php';
  exit;
}
echo '<p>Ok</p>';
echo '<h2>Installation completed</h2>';

// --------
// Html end
// --------