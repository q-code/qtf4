<?php // v4.0 build:20240210

session_start();
require 'bin/init.php';
/**
* @var CHtml $oH
* @var CDatabase $oDB
* @var array $gmap_markers
* @var array $gmap_events
* @var array $gmap_functions
*/
include translate('lg_adm.php');
if ( SUser::role()!=='A' ) die('Access denied');

include translate(APP.'m_gmap.php');
include translate(APP.'m_gmap_adm.php');
include APP.'m_gmap_lib.php';

// INITIALISE
$oH->selfurl = APP.'m_gmap_adm.php';
$oH->selfname = 'Gmap';
$oH->selfparent = L('Module');
$oH->exiturl = $oH->selfurl;
$oH->exitname = $oH->selfname;
$oH->selfversion = L('Gmap.Version').' 4.0';
$useMap = true;

// check register initialized
foreach(['m_gmap_gkey','m_gmap_gcenter','m_gmap_gzoom','m_gmap_gfind','m_gmap_gsymbol','m_gmap_sections'] as $key) {
  if ( !isset($_SESSION[QT][$key]) ) $_SESSION[QT][$key] = '';
}

// m_gmap_options (mt=maptype[T|S|H|R] bg=background sc=scale fs=fullscreen mw=mousewhell sv=streetview gc=geocode)
if ( empty($_SESSION[QT]['m_gmap_options']) ) $_SESSION[QT]['m_gmap_options'] = 'mt=T;bg=1;sc=1;fs=1;mw=0;sv=0;gc=0';

// Read png/svg in directory
$files = [];
foreach(glob(APP.'m_gmap/*.*g') as $file) {
  $file = substr($file,10);
  if ( strpos($file,'_shadow') ) continue;
  $files[$file] = ucfirst(str_replace('_',' ',substr($file,0,-4)));
}

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) ) try {

  // save gkey
  if ( isset($_POST['m_gmap_gkey']) ) {
    $_SESSION[QT]['m_gmap_gkey'] = trim($_POST['m_gmap_gkey']);
    if ( strlen($_SESSION[QT]['m_gmap_gkey'])<8 ) $_SESSION[QT]['m_gmap_gkey'] = '';
    // store configuration
    $oDB->updSetting('m_gmap_gkey');
    if ( empty($_SESSION[QT]['m_gmap_gkey'] ) ) $useMap = false;
  }
  // save others if gkey
  if ( $useMap ) {
    if ( isset($_POST['m_gmap_gcenter']) ) $_SESSION[QT]['m_gmap_gcenter'] = trim($_POST['m_gmap_gcenter']);
    if ( isset($_POST['m_gmap_gzoom']) )   $_SESSION[QT]['m_gmap_gzoom'] = trim($_POST['m_gmap_gzoom']);
    if ( isset($_POST['m_gmap_gfind']) )   $_SESSION[QT]['m_gmap_gfind'] = qtAttr($_POST['m_gmap_gfind']);
    if ( isset($_POST['m_gmap_gsymbol']) ) $_SESSION[QT]['m_gmap_gsymbol'] = trim($_POST['m_gmap_gsymbol']);
    if ( empty($_SESSION[QT]['m_gmap_gsymbol']) || $_SESSION[QT]['m_gmap_gsymbol']==='0.png' ) $_SESSION[QT]['m_gmap_gsymbol'] = ''; // "iconname" (without extension) or '' default symbol
    if ( isset($_POST['m_gmap_section']) ) $_SESSION[QT]['m_gmap_section'] = substr(trim($_POST['sections']),0,1); // in qtf only one section (U) is supported
    if ( isset($_POST['options']) ) $_SESSION[QT]['m_gmap_options'] = qtImplode($_POST['options'],';');

    // store configuration
    $oDB->updSetting( ['m_gmap_gcenter','m_gmap_gzoom','m_gmap_options','m_gmap_gfind','m_gmap_gsymbol','m_gmap_sections'] );
  }
  $_SESSION[QT.'splash'] = L('S_save');

} catch (Exception $e) {

  // Splash short message and send error to ...inc_hd.php
  $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
  $oH->error = $e->getMessage();

}

// ------
// HTML BEGIN
// ------
// prepare section settings

$arrSections = explode(';',$_SESSION[QT]['m_gmap_sections']);
if ( $_SESSION[QT]['m_gmap_gzoom']==='' ) $_SESSION[QT]['m_gmap_gzoom']='7';

$oH->links[]='<link rel="stylesheet" type="text/css" href="qtfm_gmap.css"/>';
$oH->scripts[] = 'var enterkeyPressed=false;
function ValidateForm(theForm,enterkeyPressed) {
  if ( enterkeyPressed) return false;
}
';

include 'qtf_adm_inc_hd.php';

echo '
<form class="formsafe" method="post" action="'.url($oH->selfurl).'" onsubmit="return ValidateForm(this,enterkeyPressed);">
<h2 class="config">'.L('Gmap.Mapping_settings').'</h2>
<table class="t-conf">
<tr>
<th style="width:150px"><label for="m_gmap_gkey">Google API key</label></th>
<td><input id="m_gmap_gkey" name="m_gmap_gkey" size="40" maxlength="100" value="'.$_SESSION[QT]['m_gmap_gkey'].'"/></td>
</tr>
';

//------
if ( $useMap ) {
//------

$currentSymbol = empty($_SESSION[QT]['m_gmap_gsymbol']) ? '0.png' : $_SESSION[QT]['m_gmap_gsymbol']; // current symbol
echo '<tr>
<th style="width:150px">'.L('Gmap.API_ctrl').'</th>
<td>
<input type="checkbox" id="cb-bg" value="1" name="options[bg]"'.(empty(gmapOption('bg')) ? '' : 'checked').'/> <label for="cb-bg">'.L('Gmap.Ctrl.Background').'</label>
&nbsp; <input type="checkbox" id="cb-sc" value="1" name="options[sc]"'.(empty(gmapOption('sc')) ? '' : 'checked').'/> <label for="cb-sc">'.L('Gmap.Ctrl.Scale').'</label>
&nbsp; <input type="checkbox" id="cb-fs" value="1" name="options[fs]"'.(empty(gmapOption('fs')) ? '' : 'checked').'/> <label for="cb-fs">'.L('Gmap.Ctrl.Fullscreen').'</label>
&nbsp; <input type="checkbox" id="cb-mw" value="1" name="options[mw]"'.(empty(gmapOption('mw')) ? '' : 'checked').'/> <label for="cb-mw">'.L('Gmap.Ctrl.Mousewheel').'</label>
</td>
</tr>
<th style="width:150px">'.L('Gmap.API_services').'</th>
<td><input type="checkbox" id="cb-sv" value="1" name="options[sv]"'.(empty(gmapOption('sv')) ? '' : 'checked').'/> <label for="cb-sv">'.L('Gmap.Ctrl.Streetview').'</label>
&nbsp; <input type="checkbox" id="cb-gc" value="1" name="options[gc]"'.(empty(gmapOption('gc')) ? '' : 'checked').'/> <label for="cb-gc">'.L('Gmap.Ctrl.Geocode').'</label></td>
</tr>
<tr>
<th style="width:150px">'.L('Gmap.Default_symbol').'</th>
<td style="display:flex;gap:1.5rem;align-items:flex-end">
<p><img id="dflt-marker" class="markerpicked" src="'.APP.'m_gmap/'.$currentSymbol.'" alt="i" title="default"/></p>
<p class="markerpicker small">'.L('Gmap.Click_to_change').'<br>
';
$i = 0;
foreach ($files as $file=>$name) {
  echo '<input type="radio" id="symb_'.$i.'" data-preview="dflt-marker" data-src="'.APP.'m_gmap/'.$file.'" name="m_gmap_gsymbol" value="'.$file.'"'.($currentSymbol===$file ? 'checked' : '').' onchange="document.getElementById(this.dataset.preview).src=this.dataset.src;" style="display:none"/><label for="symb_'.$i.'"><img class="marker" title="'.$name.'" src="'.APP.'m_gmap/'.$file.'" alt="i" aria-checked="'.($currentSymbol===$file ? 'true' : 'false').'"/></label>'.PHP_EOL;
  ++$i;
}
echo '</p></td>
</tr>
<tr>
<th style="width:150px;">'.L('Gmap.Memberlist').'</th>
<td>
<select name="sections" size="1">
<option value=""'.(in_array('U',$arrSections) ? '' : ' selected').'>'.L('N').'</option>
<option value="U"'.(in_array('U',$arrSections) ? ' selected' : '').'>'.L('Y').'</option>
</select><span class="indent small">'.L('Gmap.H_Memberlist').'</span> &middot; <a href="'.APP.'m_gmap_adm_options.php">'.L('Gmap.Symbol_by_role').'...</a></td>
</tr>
</table>
';

echo '<h2 class="config">'.L('Gmap.Mapping_config').'</h2>
<table class="t-conf">
<tr>
<th style="width:150px;">'.L('Gmap.Center').'</th>
<td style="width:310px;"><input type="text" id="yx" name="m_gmap_gcenter" size="26" maxlength="100" value="'.$_SESSION[QT]['m_gmap_gcenter'].'"/><small> '.L('Gmap.Latlng').'</small></td>
<td><small>'.L('Gmap.H_Center').'</small></td>
</tr>
<tr>
<th style="width:150px;">'.L('Gmap.Zoom').'</th>
<td>
<input type="text" id="m_gmap_gzoom" name="m_gmap_gzoom" size="2" maxlength="2" value="'.$_SESSION[QT]['m_gmap_gzoom'].'"/></td>
<td><small>'.L('Gmap.H_Zoom').'</small></td>
</tr>
<tr>
<th style="width:150px;">'.L('Gmap.Background').'</th>
<td><select id="maptype" name="options[mt]" size="1">'.qtTags(L('Gmap.Back.*'), gmapOption('mt')).'</select></td>
<td><small>'.L('Gmap.H_Background').'</small></td>
</tr>
<tr>
<th style="width:150px;">'.L('Gmap.Address_sample').'</th>
<td>
<input'.(empty(gmapOption('gc')) ? 'disabled' : '').' type="text" id="m_gmap_gfind" name="m_gmap_gfind" size="20" maxlength="100" value="'.$_SESSION[QT]['m_gmap_gfind'].'"/></td>
<td><small>'.(empty(gmapOption('gc')) ? L('Gmap.Ctrl.Geocode').' (off)' : L('Gmap.H_Address_sample')).'</small></td>
</tr>
';

//------
}
//------

echo '</table>
<p style="text-align:center"><button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</form>
';

if ( $useMap ) {
  echo '<div class="gmap">'.PHP_EOL;
  echo '<p class="small commands" style="margin:2px 0 4px 2px;text-align:right">'.L('Gmap.canmove').' | <a class="small" href="javascript:void(0)" onclick="undoChanges(); return false;">'.L('Gmap.undo').'</a></p>'.PHP_EOL;
  echo '<div id="map_canvas"></div>'.PHP_EOL;
  if ( !empty(gmapOption('gc')) ) {
    echo '<p class="small commands" style="margin:4px 0 2px 2px;text-align:right">'.L('Gmap.addrlatlng');
    echo ' <input type="text" size="24" id="find" name="find" class="small" value="'.$_SESSION[QT]['m_gmap_gfind'].'" title="'.L('Map.H_addrlatlng').'" onkeypress="if ((event.key!==undefined && event.key==`Enter`) || (event.keyCode!==undefined && event.keyCode==13)) showLocation(this.value,null);"/>';
    echo '<span id="btn-geocode" class="clickable" onclick="showLocation(document.getElementById(`find`).value,null);" title="'.L('Search').'">'.qtSVG('search').'</span></p>'.PHP_EOL;
  }
  echo '</div>'.PHP_EOL;
} else {
  echo '<p class="minor">'.L('Gmap.E_disabled').'</p>';
}

// HTML END

if ( $useMap ) {
  $gmap_symbol = empty($_SESSION[QT]['m_gmap_gsymbol']) ? false : $_SESSION[QT]['m_gmap_gsymbol']; // false = no icon but default marker
  $gmap_markers = [];
  $gmap_events = [];
  $gmap_functions = [];
  $gmap_markers[] = gmapMarker($_SESSION[QT]['m_gmap_gcenter'],true,$gmap_symbol,L('Gmap.Default_center'));
  $gmap_events[] = '
  markers[0].addListener("drag", ()=>{ document.getElementById("yx").value = gmapRound(markers[0].position.lat,10) + "," + gmapRound(markers[0].position.lng,10); });
	google.maps.event.addListener(markers[0], "dragend", function() { gmap.panTo(markers[0].position);	});';
  $gmap_functions[] = '
  function undoChanges() {
  	if ( gmapInfoBox) gmapInfoBox.close();
  	if ( markers[0]) markers[0].setPosition(gmapOptions.center);
  	if ( gmapOptions) gmap.panTo(gmapOptions.center);
  	return null;
  }
  function showLocation(address,title) {
    if ( gmapInfoBox ) gmapInfoBox.close();
    gmapCoder.geocode( { "address":address}, function(results, status) {
      if ( status==google.maps.GeocoderStatus.OK ) {
        gmap.setCenter(results[0].geometry.location);
        if ( markers[0] ) {
          markers[0].setPosition(results[0].geometry.location);
        } else {
          markers[0] = new google.maps.marker.AdvancedMarkerElement({map:map, position:results[0].geometry.location, draggable:true, title:title});
        }
        gmapYXfield("yx",markers[0]);
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
  ';
  include APP.'m_gmap_load.php';
}

include APP.'_adm_inc_ft.php';