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
* @package    QuickTalk forum
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2012 The PHP Group
* @version    4.0 build:20240210
*/

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 * @var CSection $oS
 */
require 'bin/init.php';

$oDB->debug = true;

$x = CDomain::getAllDomains($oDB);
var_dump($x);