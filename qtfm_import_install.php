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
require 'bin/init.php';
/**
* @var CHtml $oH
* @var CHtml $oH
* @var array $L
* @var CDatabase $oDB
*/
include translate('lg_adm.php');
if ( SUser::role()!=='A' ) die('Access denied');

// INITIALISE

$strVersion='v4.0';
$oH->selfurl = 'qtfm_import_install.php';
$oH->selfname = 'Installation module IMPORT '.$strVersion;
$bStep1 = true;

// STEP 1

if ( empty($oH->error) )
{
  $strFile = 'qtfm_import_adm.php';
  if ( !file_exists($strFile) ) $oH->error="Missing file: $strFile. Check installation instructions.<br>This module cannot be used.";
  if ( !empty($oH->error) ) $bStep1 = false;
}

// STEP 2

if ( empty($oH->error) )
{
  $oDB->exec( 'DELETE FROM TABSETTING WHERE param="module_import"');
  $oDB->exec( 'INSERT INTO TABSETTING (param,setting) VALUES ("module_import","Import")');
  SMem::set('settingsage',time());
}

// --------
// Html start
// --------
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
echo '<p>Ok</p>';
echo '<h2>Installation completed</h2>';

include 'qtf_adm_inc_ft.php';