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
* @copyright  2012 The PHP Group
* @version    1.0 build:20230618
*/

session_start();
require 'bin/init.php';
/**
* @var CHtml $oH
* @var array $L
* @var CDatabase $oDB
*/
include translate('lg_adm.php');
if ( SUser::role()!=='A' ) die('Access denied');

// INITIALISE

$strVersion='v1.0';
$oH->selfurl = 'qtfm_ldap_uninstall.php';
$oH->selfname = 'Uninstallation module LDAP '.$strVersion;

// UNINSTALL

$oDB->exec( "DELETE FROM TABSETTING WHERE param='module_ldap' OR param='m_ldap:login' OR param='m_ldap'" );
unset($_SESSION[QT]['m_ldap']);
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