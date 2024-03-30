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
include 'qtfm_gmap_lib.php';

// INITIALISE

$oH->selfurl = 'qtfm_gmap_adm.php';
$oH->selfname = 'Gmap';
$oH->selfparent = L('Module');
$oH->exiturl = $oH->selfurl;
$oH->exitname = $oH->selfname;
$oH->selfversion = L('Gmap.Version').' 4.0<br>';

// check register initialized
foreach(['m_gmap_gkey','m_gmap_gcenter','m_gmap_gzoom','m_gmap_gfind','m_gmap_gsymbol','m_gmap_sections'] as $key)
{
  if ( !isset($_SESSION[QT][$key]) ) $_SESSION[QT][$key]='';
}
// m_gmap_gbuttons = maptype.streetview.background.scale.fullscreen.mousewheel.geocode
if ( !isset($_SESSION[QT]['m_gmap_gbuttons']) || strlen($_SESSION[QT]['m_gmap_gbuttons'])!==7 ) $_SESSION[QT]['m_gmap_gbuttons']='P011100';

// Read png in directory (shadow is obsolete)
$arrFiles = [];
foreach(glob('qtfm_gmap/*.png') as $file) {
  $file = substr($file,10,-4);
  if ( strpos($file,'_shadow') ) continue;
  $arrFiles[$file] = ucfirst(str_replace('_',' ',$file));
}

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) )
{
  // save gkey
  if ( isset($_POST['m_gmap_gkey']) ) {
    $_SESSION[QT]['m_gmap_gkey'] = trim($_POST['m_gmap_gkey']); if ( strlen($_SESSION[QT]['m_gmap_gkey'])<8 ) $_SESSION[QT]['m_gmap_gkey'] = '';
    // store configuration
    $oDB->updSetting('m_gmap_gkey');
  }
  // save others if gkey
  if ( !empty($_SESSION[QT]['m_gmap_gkey']) ) {
    if ( isset($_POST['m_gmap_gcenter']) ) $_SESSION[QT]['m_gmap_gcenter'] = trim($_POST['m_gmap_gcenter']);
    if ( isset($_POST['m_gmap_gzoom']) )   $_SESSION[QT]['m_gmap_gzoom'] = trim($_POST['m_gmap_gzoom']);
    if ( isset($_POST['m_gmap_gfind']) )   $_SESSION[QT]['m_gmap_gfind'] = qtAttr($_POST['m_gmap_gfind']);
    if ( isset($_POST['m_gmap_gsymbol']) ) $_SESSION[QT]['m_gmap_gsymbol'] = trim($_POST['m_gmap_gsymbol']); if ( empty($_SESSION[QT]['m_gmap_gsymbol']) || $_SESSION[QT]['m_gmap_gsymbol']=='default' ) $_SESSION[QT]['m_gmap_gsymbol']='0'; // "iconname" (without extension) or '0' default symbol
    if ( isset($_POST['m_gmap_section']) ) $_SESSION[QT]['m_gmap_section'] = substr(trim($_POST['sections']),0,1); // in qtf only one section (U) is supported
    // m_gmap_gbuttons = maptype.streetview.background.scale.fullscreen.mousewheel.geocode
    if ( isset($_POST['maptype']) )        $_SESSION[QT]['m_gmap_gbuttons'][0] = substr($_POST['maptype'],0,1);
    if ( isset($_POST['streetview']) )     $_SESSION[QT]['m_gmap_gbuttons'][1] = '1';
    if ( isset($_POST['map']) )            $_SESSION[QT]['m_gmap_gbuttons'][2] = '1';
    if ( isset($_POST['scale']) )          $_SESSION[QT]['m_gmap_gbuttons'][3] = '1';
    if ( isset($_POST['fullscreen']) )     $_SESSION[QT]['m_gmap_gbuttons'][4] = '1';
    if ( isset($_POST['mousewheel']) )     $_SESSION[QT]['m_gmap_gbuttons'][5] = '1';
    if ( isset($_POST['geocode']) )        $_SESSION[QT]['m_gmap_gbuttons'][6] = '1';
    // store configuration
    $oDB->updSetting( ['m_gmap_gcenter','m_gmap_gzoom','m_gmap_gbuttons','m_gmap_gfind','m_gmap_gsymbol','m_gmap_sections'] );
  }
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
function previewMarker(src) { document.getElementById("previewmarker").src = src; }';

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
if ( !empty($_SESSION[QT]['m_gmap_gkey']) ) {
//------

// current symbol
$current = empty($_SESSION[QT]['m_gmap_gsymbol']) ? 'default' : $_SESSION[QT]['m_gmap_gsymbol'];

echo '<tr>
<th style="width:150px">'.L('Gmap.API_ctrl').'</th>
<td>
<input type="checkbox" id="map" name="map"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],2,1)==='1' ? 'checked' : '').'/> <label for="map">'.L('Gmap.Ctrl.Background').'</label>
&nbsp; <input type="checkbox" id="scale" name="scale"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],3,1)==='1' ? 'checked' : '').'/> <label for="scale">'.L('Gmap.Ctrl.Scale').'</label>
&nbsp; <input type="checkbox" id="fullscreen" name="fullscreen"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],4,1)==='1' ? 'checked' : '').'/> <label for="fullscreen">'.L('Gmap.Ctrl.Fullscreen').'</label>
&nbsp; <input type="checkbox" id="mousewheel" name="mousewheel"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],5,1)==='1' ? 'checked' : '').'/> <label for="mousewheel">'.L('Gmap.Ctrl.Mousewheel').'</label>
</td>
</tr>
<th style="width:150px">'.L('Gmap.API_services').'</th>
<td><input type="checkbox" id="streetview" name="streetview"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],1,1)==='1' ? 'checked' : '').'/> <label for="streetview">'.L('Gmap.Ctrl.Streetview').'</label>
&nbsp; <input type="checkbox" id="geocode" name="geocode"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],6,1)==='1' ? 'checked' : '').'/> <label for="geocode">'.L('Gmap.Ctrl.Geocode').'</label></td>
</tr>
<tr>
<th style="width:150px">'.L('Gmap.Default_symbol').'</th>
<td style="display:flex;gap:1rem;align-items:flex-end">
<p><img id="previewmarker" class="markerpicked" title="default" src="qtfm_gmap/'.$current.'.png" alt="i"/></p>
<p class="markerpicker small">'.L('Gmap.Click_to_change').'<br>
';
foreach ($arrFiles as $file=>$name){
  echo '<input type="radio" id="symbol_'.$file.'" data-src="qtfm_gmap/'.$file.'.png" name="m_gmap_gsymbol" value="'.$file.'"'.($current===$file ? 'checked' : '').' onchange="previewMarker(this.dataset.src);" style="display:none"/><label for="symbol_'.$file.'"><img class="marker" title="'.$name.'" src="qtfm_gmap/'.$file.'.png" alt="i" aria-checked="'.($current===$file ? 'true' : 'false').'"/></label>'.PHP_EOL;
}
echo '</p></td>
</tr>
<tr>
<th style="width:150px;">'.L('Gmap.Memberlist').'</th>
<td>
<select name="sections" size="1">
<option value=""'.(in_array('U',$arrSections) ? '' : ' selected').'>'.L('N').'</option>
<option value="U"'.(in_array('U',$arrSections) ? ' selected' : '').'>'.L('Y').'</option>
</select><span class="indent small">'.L('Gmap.H_Memberlist').'</span> &middot; <a href="qtfm_gmap_adm_options.php">'.L('Gmap.Symbol_by_role').'...</a></td>
</tr>
</table>
';

echo '<h2 class="config">'.L('Gmap.Mapping_config').'</h2>
<table class="t-conf">
<tr>
<th style="width:150px;">'.L('Gmap.Center').'</th>
<td style="width:310px;"><input type="text" id="m_gmap_gcenter" name="m_gmap_gcenter" size="26" maxlength="100" value="'.$_SESSION[QT]['m_gmap_gcenter'].'"/><small> '.L('Gmap.Latlng').'</small></td>
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
<td><select id="maptype" name="maptype" size="1">'.qtTags(L('Gmap.Back.*'),substr($_SESSION[QT]['m_gmap_gbuttons'],0,1)).'</select></td>
<td><small>'.L('Gmap.H_Background').'</small></td>
</tr>
<tr>
<th style="width:150px;"><label for="m_gmap_gfind">'.L('Gmap.Address_sample').'</label></th>
<td>
<input'.(substr($_SESSION[QT]['m_gmap_gbuttons'],6,1)==='1' ? '' : 'disabled').' type="text" id="m_gmap_gfind" name="m_gmap_gfind" size="20" maxlength="100" value="'.$_SESSION[QT]['m_gmap_gfind'].'"/></td>
<td><small>'.(substr($_SESSION[QT]['m_gmap_gbuttons'],6,1)==='1' ? L('Gmap.H_Address_sample') : L('Gmap.Ctrl.Geocode').' (off)').'</small></td>
</tr>
';

//------
}
//------

echo '</table>
<p style="text-align:center"><button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</form>
';

if ( !empty($_SESSION[QT]['m_gmap_gkey']) )
{
  echo '<div class="gmap">'.PHP_EOL;
  echo '<p class="small commands" style="margin:2px 0 4px 2px;text-align:right">'.L('Gmap.canmove').' | <a class="small" href="javascript:void(0)" onclick="undoChanges(); return false;">'.L('Gmap.undo').'</a></p>'.PHP_EOL;
  echo '<div id="map_canvas"></div>'.PHP_EOL;
  if ( substr($_SESSION[QT]['m_gmap_gbuttons'],6,1)==='1' )
  {
  echo '<p class="small commands" style="margin:4px 0 2px 2px;text-align:right">'.L('Gmap.addrlatlng');
  echo ' <input type="text" size="24" id="find" name="find" class="small" value="'.$_SESSION[QT]['m_gmap_gfind'].'" title="'.L('Map.H_addrlatlng').'" onkeypress="enterkeyPressed=qtKeyEnter(event); if ( enterkeyPressed) showLocation(this.value,null);"/>';
  echo '<span id="btn-geocode" class="clickable" onclick="showLocation(document.getElementById(`find`).value,null);" title="'.L('Search').'">'.qtSVG('search').'</span></p>'.PHP_EOL;
  }
  echo '</div>'.PHP_EOL;
}
else
{
  echo '<p class="minor">'.L('Gmap.E_disabled').'</p>';
}

// HTML END

if ( !empty($_SESSION[QT]['m_gmap_gkey']) )
{
  $gmap_symbol = empty($_SESSION[QT]['m_gmap_gsymbol']) ? false : $_SESSION[QT]['m_gmap_gsymbol']; // false = no icon but default marker
  $gmap_markers = [];
  $gmap_events = [];
  $gmap_functions = [];

  $gmap_markers[] = gmapMarker($_SESSION[QT]['m_gmap_gcenter'],true,$gmap_symbol,L('Gmap.Default_center'));
  $gmap_events[] = '
  markers[0].addListener("drag", ()=>{ document.getElementById("yx").value = gmapRound(markers[0].position.lat,10) + "," + gmapRound(markers[0].position.lng,10); });
	google.maps.event.addListener(markers[0], "dragend", function() { map.panTo(markers[0].position);	});';
  $gmap_functions[] = '
  function undoChanges()
  {
  	if ( infowindow) infowindow.close();
  	if ( markers[0]) markers[0].setPosition(mapOptions.center);
  	if ( mapOptions) map.panTo(mapOptions.center);
  	return null;
  }
  function showLocation(address,title) {
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if ( status == google.maps.GeocoderStatus.OK) {
        map.setCenter(results[0].geometry.location);
        if ( markers[0] ) {
          markers[0].setPosition(results[0].geometry.location);
        } else {
          markers[0] = new google.maps.marker.AdvancedMarkerElement({map: map, position: results[0].geometry.location, draggable: true, title: title});
        }
        gmapYXfield("yx",markers[0]);
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
  ';
  include 'qtfm_gmap_load.php';
}

include 'qtf_adm_inc_ft.php';