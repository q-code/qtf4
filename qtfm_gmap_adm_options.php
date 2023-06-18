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
* @version    4.0 build:20230618
*/

session_start();
/**
* @var CHtml $oH
* @var CHtml $oH
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

$oH->selfurl = 'qtfm_gmap_adm_options.php';
$oH->selfname = L('Gmap.Mapping_settings');
$oH->selfparent = L('Module');
$oH->exiturl = 'qtfm_gmap_adm.php';
$oH->exitname = 'Gmap';
$oH->selfversion = L('Gmap.Version').' 4.0<br>';

// --------
// SUBMITTED for cancel
// --------

if ( isset($_POST['cancel']) )
{
  $_SESSION[QT]['m_gmap_symbols'] = '0';
  $oDB->exec( 'UPDATE TABSETTING SET setting="'.$_SESSION[QT]['m_gmap_symbols'].'" WHERE param="m_gmap_symbols"');
  // exit
  $_SESSION[QT.'splash'] = empty($oH->error) ? L('S_save') : 'E|'.$oH->error;
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
  $_SESSION[QT.'splash'] = empty($oH->error) ? L('S_save') : 'E|'.$oH->error;
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

$oH->links[]='<link rel="stylesheet" type="text/css" href="qtfm_gmap.css"/>';
$oH->scripts[] = 'function previewMarker(id,src) { document.getElementById(id).src = src; }';

include 'qtf_adm_inc_hd.php';

echo '
<form method="post" action="',url($oH->selfurl),'">
<h2 class="config">',L('Gmap.Symbol_by_role'),'</h2>
<table class="t-conf">
<tr><td colspan="3" class="right" style="background-color:transparent"><small>',L('Gmap.Click_to_change'),'</small></td></tr>
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
<th style="padding-right:10px">',L('Role_'.$key.'+'),'</th>
<td style="display:flex;gap:1rem;align-items:flex-end">
<p><img id="previewmarker-'.$key.'" class="markerpicked" title="default" src="qtfm_gmap/',$current,'.png"/></p>
<p class="markerpicker small">
';
foreach ($arrFiles as $file=>$name)
{
  echo '<input type="radio" data-preview="previewmarker-'.$key.'" data-src="qtfm_gmap/'.$file.'.png" name="symbol_'.$key.'" value="'.$file.'" id="symbol_'.$file.'_'.$key.'"'.($current===$file ? ' checked' : '').' onchange="previewMarker(this.dataset.preview,this.dataset.src);qtFormSafe.not();" style="display:none"/><label for="symbol_'.$file.'_'.$key.'"><img class="marker" title="'.$name.'" src="qtfm_gmap/'.$file.'.png" aria-checked="'.($current===$file ? 'true' : 'false').'"/></label>'.PHP_EOL;
}
echo '</p>
</td>
</tr>
';
}
echo '</table>';

echo '
<p style="text-align:center"><button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</form>
<p>'.qtSVG('angle-left').' <a href="'.$oH->exiturl.'" onclick="return qtFormSafe.exit(e0);">'.$oH->exitname.'</a></p>
';

include 'qtf_adm_inc_ft.php';