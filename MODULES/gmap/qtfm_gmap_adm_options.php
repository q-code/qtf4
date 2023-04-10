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
/**
* @var string $error
* @var CVip $oV
* @var cHtml $oHtml
* @var CDatabase $oDB
*/
require 'bin/init.php';
include translate('lg_adm.php');
if ( SUser::role()!=='A' ) die('Access denied');
if ( empty($_SESSION[QT]['m_gmap_gkey']) ) die('Missing google map api key. First go to the Map administration page.');

include translate(APP.'m_gmap.php');
include translate(APP.'m_gmap_adm.php');
include 'qtfm_gmap_lib.php';

// INITIALISE

$oV->selfurl = 'qtfm_gmap_adm_options.php';
$oV->selfname = L('Map.Mapping_settings');
$oV->selfparent = L('Module');
$oV->exiturl = 'qtfm_gmap_adm.php';
$oV->exitname = 'Gmap';
$oV->selfversion = L('Map.Version').' 4.0<br>';

// --------
// SUBMITTED for cancel
// --------

if ( isset($_POST['cancel']) )
{
  $_SESSION[QT]['m_gmap_symbols'] = '0';
  $oDB->exec( 'UPDATE TABSETTING SET setting="'.$_SESSION[QT]['m_gmap_symbols'].'" WHERE param="m_gmap_symbols"');
  // exit
  $_SESSION[QT.'splash'] = empty($error) ? L('S_save') : 'E|'.$error;
}

// --------
// SUBMITTED for save
// --------

if ( isset($_POST['ok']) )
{
  $arrSymbols = array();
  foreach(array('U','M','A') as $key)
  {
  if ( isset($_POST['symbol_'.$key]) ) $arrSymbols[$key]=$_POST['symbol_'.$key];
  }
  $_SESSION[QT]['m_gmap_symbols'] = qtImplode($arrSymbols,';');
  $oDB->exec( 'UPDATE TABSETTING SET setting="'.$_SESSION[QT]['m_gmap_symbols'].'" WHERE param="m_gmap_symbols"');
  // exit
  $_SESSION[QT.'splash'] = empty($error) ? L('S_save') : 'E|'.$error;
}

// --------
// HTML BEGIN
// --------

// read symbols values
if ( empty($_SESSION[QT]['m_gmap_symbols']) ) $_SESSION[QT]['m_gmap_symbols']='U=0;M=0;A=0'; // empty, not set or false
$arrSymbols = qtExplode($_SESSION[QT]['m_gmap_symbols']);

// reset if incoherents values
if ( count($arrSymbols)!=3 ) $arrSymbols = array('U'=>'0','M'=>'0','A'=>'0');
if ( !isset($arrSymbols['A']) || !isset($arrSymbols['M']) || !isset($arrSymbols['U']) ) $arrSymbols = array('U'=>'0','M'=>'0','A'=>'0');

$oHtml->links[]='<link rel="stylesheet" type="text/css" href="qtfm_gmap.css"/>';
$oHtml->scripts[] = 'function radioHighlight(src,key) { document.getElementById("markerpicked_"+key).src = src; }';

include 'qtf_adm_inc_hd.php';

echo '
<form method="post" action="',Href($oV->selfurl),'">
<h2 class="subtitle">',L('Map.Symbol_by_role'),'</h2>
<table class="t-conf">
<tr><td colspan="3" class="right" style="background-color:transparent"><small>',L('Map.Click_to_change'),'</small></td></tr>
';

// Read png in directory (shadow is obsolete)
$arrFiles = array();
foreach(glob('qtfm_gmap/*.png') as $file) {
  $file = substr($file,10,-4);
  if ( strpos($file,'_shadow') ) continue;
  $arrFiles[$file] = ucfirst(str_replace('_',' ',$file));
}

foreach($arrSymbols as $key=>$strSymbol)
{
  // current symbol
  $current = empty($strSymbol) ? 'default' : $strSymbol;

echo '<tr>
<th style="padding-right:10px">',L('Role_'.$key.'s'),'</th>
<td id="symbol_cb_'.$key.'" style="width:60px;text-align:center"><img id="markerpicked_'.$key.'" title="default" src="qtfm_gmap/',$current,'.png"/></td>
<td id="picker_cb_'.$key.'">
<div class="markerpicker">
';
foreach ($arrFiles as $strFile=>$strName)
{
  echo '<input type="radio" data-key="'.$key.'" data-src="qtfm_gmap/'.$strFile.'.png" name="symbol_'.$key.'" value="'.$strFile.'" id="symbol_'.$strFile.'_'.$key.'"'.($current===$strFile ? 'checked' : '').' onchange="radioHighlight(this.dataset.src,this.dataset.key);qtFormSafe.not();"/><label for="symbol_'.$strFile.'_'.$key.'"><img class="marker'.($current==$strFile ? ' checked' : '').'" title="'.$strName.'" src="qtfm_gmap/'.$strFile.'.png"/></label>'.PHP_EOL;
}
echo '</div>
</td>
</tr>
';
}
echo '</table>';

echo '
<p style="text-align:center"><button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</form>
<p><i class="fa fa-angle-left"></i> <a href="'.$oV->exiturl.'" onclick="return qtFormSafe.exit(e0);">'.$oV->exitname.'</a></p>
';

include 'qtf_adm_inc_ft.php';