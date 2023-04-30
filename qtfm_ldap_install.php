<?php

/**
* PHP version 7
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license. If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QTF
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2008-2012 The PHP Group
* @version    1.0 build:20230430
*/

session_start();
/**
* @var CHtml $oH
* @var CHtml $oH
* @var CDatabase $oDB
*/
require 'bin/init.php';
if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');

// INITIALISE

$strVersion='v1.0';
$oH->selfurl = 'qtfm_ldap_install.php';
$oH->selfname = 'Installation module LDAP '.$strVersion;

$bStep1 = true;

// STEP 1

if ( empty($oH->error) )
{
  $strFile = 'qtfm_ldap_adm.php';
  if ( !file_exists($strFile) ) $oH->error="Missing file: $strFile. Check installation instructions.<br>This module cannot be used.";
  if ( !empty($oH->error) ) $bStep1 = false;
}

// STEP 2

if ( empty($oH->error) )
{
  $oDB->exec( "DELETE FROM TABSETTING WHERE param='module_ldap' OR param='m_ldap:login' OR param='m_ldap'" );
  // Declare ldap as a module, then add configuration settings with param='m_ldap'
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('module_ldap','LDAP')" ); // register a new module (alias LDAP)
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_ldap','0')" ); // module is disabled on installation
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_ldap:login','LDAP')" ); // declare m_ldap (alias LDAP) as a possible login authority
  SMem::set('settingsage',time());
}

// STEP 3

if ( empty($oH->error) )
{
  if ( !function_exists('ldap_connect') ) $oH->error = 'LDAP function not found. It seems that module LDAP is not activated on your webserver';
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
echo '
<p>Ok</p>
<h2>Database settings</h2>
<p>Ok</p>
<h2>Installation completed</h2>
';

include 'qtf_adm_inc_ft.php';