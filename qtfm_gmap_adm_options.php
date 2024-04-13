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
* @version    4.0 build:20240210
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
include APP.'m_gmap_lib.php';

// INITIALISE

$oH->selfurl = APP.'m_gmap_adm_options.php';
$oH->selfname = L('Gmap.Mapping_settings');
$oH->selfparent = L('Module');
$oH->exiturl = APP.'m_gmap_adm.php';
$oH->exitname = 'Gmap';
$oH->selfversion = L('Gmap.Version').' 4.0<br>';

// ------
// SUBMITTED for save
// ------
if ( isset($_POST['ok']) ) try {

  $symbols = [];
  foreach(['U','M','A'] as $role) $symbols[$role] = empty($_POST['symbols'][$role]) || $_POST['symbols'][$role]==='0.png' ? '0' : $_POST['symbols'][$role];
  $_SESSION[QT]['m_gmap_symbols'] = qtImplode($symbols,';');
  $oDB->updSetting('m_gmap_symbols');
  // exit
  $_SESSION[QT.'splash'] = empty($oH->error) ? L('S_save') : 'E|'.$oH->error;

} catch (Exception $e) {

  // Splash short message and send error to ...inc_hd.php
  $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
  $oH->error = $e->getMessage();

}

// ------
// HTML BEGIN
// ------
// read symbols values
if ( empty($_SESSION[QT]['m_gmap_symbols']) ) $_SESSION[QT]['m_gmap_symbols']='U=0;M=0;A=0'; // empty, not set or false
$symbols = qtExplode($_SESSION[QT]['m_gmap_symbols']);

// reset if incoherents values
if ( count($symbols)!==3 ) $symbols = ['U'=>'0','M'=>'0','A'=>'0'];
if ( !isset($symbols['U']) ) $symbols['U'] = '0';
if ( !isset($symbols['M']) ) $symbols['M'] = '0';
if ( !isset($symbols['A']) ) $symbols['A'] = '0';

$oH->links[]='<link rel="stylesheet" type="text/css" href="'.APP.'m_gmap.css"/>';

include APP.'_adm_inc_hd.php';

echo '
<form class="formsafe" method="post" action="'.url($oH->selfurl).'">
<h2 class="config">'.L('Gmap.Symbol_by_role').'</h2>
<table class="t-conf">
<tr><td colspan="3" class="right" style="background-color:transparent"><small>'.L('Gmap.Click_to_change').'</small></td></tr>
';

// Read png/svg in directory
$files = [];
foreach(glob(APP.'m_gmap/*.*g') as $file) {
  $file = substr($file,10);
  if ( strpos($file,'_shadow') ) continue;
  $name = ucfirst(str_replace('_',' ',substr($file,0,-4)));
  $files[$file] = empty($name) ? 'Default' : $name;
}

foreach($symbols as $role=>$symbol) {
  // current symbol
  $currentFile = empty($symbol) ? '0.png' : $symbol;
  echo '<tr>
  <th>'.L('Role_'.$role.'+').'</th>
  <td style="display:flex;gap:1.5rem;align-items:flex-end">
  <p><img id="preview-'.$role.'" class="markerpicked" title="default" src="'.APP.'m_gmap/'.$currentFile.'"/></p>
  <p class="markerpicker small">';
  $i = 0;
  foreach ($files as $file=>$name) {
    echo '<input type="radio" data-preview="preview-'.$role.'" data-src="'.APP.'m_gmap/'.$file.'" name="symbols['.$role.']" value="'.$file.'" id="symb_'.$i.'_'.$role.'"'.($currentFile===$file ? ' checked' : '').' onchange="document.getElementById(this.dataset.preview).src=this.dataset.src;" style="display:none"/><label for="symb_'.$i.'_'.$role.'"><img class="marker" title="'.$name.'" src="'.APP.'m_gmap/'.$file.'" aria-checked="'.($currentFile===$file ? 'true' : 'false').'"/></label>'.PHP_EOL;
    ++$i;
  }
  echo '</p>
  </td>
  </tr>
  ';
}
echo '</table>';

echo '
<p style="text-align:center">
<button type="button" name="cancel" value="cancel" onclick="window.location=`'.url($oH->exiturl).'`;">'.L('Cancel').'</button>&nbsp;<button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</form>
';

include APP.'_adm_inc_ft.php';