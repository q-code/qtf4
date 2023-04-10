<?php // v4.0 build:20221111

session_start();
require 'bin/init.php';
/**
* @var string $error
* @var CVip $oV
* @var cHtml $oHtml
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

$oV->selfurl = 'qtfm_gmap_adm.php';
$oV->selfname = 'Gmap';
$oV->selfparent = L('Module');
$oV->exiturl = $oV->selfurl;
$oV->exitname = $oV->selfname;
$oV->selfversion = L('Map.Version').' 4.0<br>';

// check register initialized
foreach(['m_gmap_gkey','m_gmap_gcenter','m_gmap_gzoom','m_gmap_gfind','m_gmap_gsymbol','m_gmap_sections'] as $key)
{
  if ( !isset($_SESSION[QT][$key]) ) $_SESSION[QT][$key]='';
}
// m_gmap_gbuttons = maptype.streetview.background.scale.fullscreen.mousewheel.geocode
if ( !isset($_SESSION[QT]['m_gmap_gbuttons']) || strlen($_SESSION[QT]['m_gmap_gbuttons'])!==7 ) $_SESSION[QT]['m_gmap_gbuttons']='P011100';

// Read png in directory (shadow is obsolete)
$arrFiles = array();
foreach(glob('qtfm_gmap/*.png') as $file) {
  $file = substr($file,10,-4);
  if ( strpos($file,'_shadow') ) continue;
  $arrFiles[$file] = ucfirst(str_replace('_',' ',$file));
}

// --------
// SUBMITTED
// --------

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

// --------
// HTML BEGIN
// --------

// prepare section settings

$arrSections = explode(';',$_SESSION[QT]['m_gmap_sections']);
if ( $_SESSION[QT]['m_gmap_gzoom']==='' ) $_SESSION[QT]['m_gmap_gzoom']='7';

$oHtml->links[]='<link rel="stylesheet" type="text/css" href="qtfm_gmap.css"/>';
$oHtml->scripts[] = 'var enterkeyPressed=false;
function ValidateForm(theForm,enterkeyPressed) {
  if (enterkeyPressed) return false;
}
function radioHighlight(src) { document.getElementById("markerpicked").src = src; }';

include 'qtf_adm_inc_hd.php';

echo '
<form method="post" action="',Href($oV->selfurl),'" onsubmit="return ValidateForm(this,enterkeyPressed);">
<h2 class="subtitle">',L('Map.Mapping_settings'),'</h2>
<table class="t-conf">
<tr>
<th style="width:150px"><label for="m_gmap_gkey">Google API key</label></th>
<td colspan="2"><input id="m_gmap_gkey" name="m_gmap_gkey" size="40" maxlength="100" value="',$_SESSION[QT]['m_gmap_gkey'],'" onchange="qtFormSafe.not();"/></td>
</tr>
';

//-----------
if ( !empty($_SESSION[QT]['m_gmap_gkey']) ) {
//-----------

// current symbol
$current = empty($_SESSION[QT]['m_gmap_gsymbol']) ? 'default' : $_SESSION[QT]['m_gmap_gsymbol'];

echo '<tr>
<th style="width:150px">',L('Map.API_ctrl'),'</th>
<td colspan="2">
<input type="checkbox" id="map" name="map"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],2,1)=='1' ? 'checked' : '').' onchange="qtFormSafe.not();"/> <label for="map">',L('Map.Ctrl.Background'),'</label>
&nbsp; <input type="checkbox" id="scale" name="scale"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],3,1)=='1' ? 'checked' : '').' onchange="qtFormSafe.not();"/> <label for="scale">',L('Map.Ctrl.Scale'),'</label>
&nbsp; <input type="checkbox" id="fullscreen" name="fullscreen"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],4,1)=='1' ? 'checked' : '').' onchange="qtFormSafe.not();"/> <label for="fullscreen">',L('Map.Ctrl.Fullscreen'),'</label>
&nbsp; <input type="checkbox" id="mousewheel" name="mousewheel"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],5,1)=='1' ? 'checked' : '').' onchange="qtFormSafe.not();"/> <label for="mousewheel">',L('Map.Ctrl.Mousewheel'),'</label>
</td>
</tr>
<th style="width:150px">',L('Map.API_services'),'</th>
<td colspan="2">
<input type="checkbox" id="streetview" name="streetview"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],1,1)=='1' ? 'checked' : '').' onchange="qtFormSafe.not();"/> <label for="streetview">',L('Map.Ctrl.Streetview'),'</label>
&nbsp; <input type="checkbox" id="geocode" name="geocode"'.(substr($_SESSION[QT]['m_gmap_gbuttons'],6,1)=='1' ? 'checked' : '').' onchange="qtFormSafe.not();"/> <label for="geocode">',L('Map.Ctrl.Geocode'),'</label>
</td>
</tr>
<tr>
<th style="width:150px">',L('Map.Symbol'),'</th>
<td style="width:70px;text-align:center">
<img id="markerpicked" title="default" src="qtfm_gmap/',$current,'.png" alt="i"/>
</td>
<td>
<p class="small" style="text-align:center">',L('Map.Click_to_change'),'</p>
<div class="markerpicker">
';
foreach ($arrFiles as $strFile=>$strName)
{
  echo '<input type="radio" id="symbol_'.$strFile.'" data-src="qtfm_gmap/'.$strFile.'.png" name="m_gmap_gsymbol" value="'.$strFile.'"'.($current===$strFile ? 'checked' : '').' onchange="radioHighlight(this.dataset.src);qtFormSafe.not();"/><label for="symbol_'.$strFile.'"><img class="marker" title="'.$strName.'" src="qtfm_gmap/'.$strFile.'.png" alt="i"/></label>'.PHP_EOL;
}
echo '</div>
</td>
</tr>
<tr>
<th style="width:150px;">',L('Map.Memberlist'),'</th>
<td style="width:70px;text-align:center">
<select name="sections" size="1">
<option value=""',(in_array('U',$arrSections) ? '' : ' selected'),'>',L('N'),'</option>
<option value="U"',(in_array('U',$arrSections) ? ' selected' : ''),'>',L('Y'),'</option>
</select>
</td>
<td><span class="small">',L('Map.H_Memberlist'),'</span> &middot; <a href="qtfm_gmap_adm_options.php" onclick="return qtFormSafe.exit(e0);">',L('Map.Symbol_by_role'),'...</a></td>
</tr>
</table>
';
echo '<h2 class="subtitle">',L('Map.Mapping_config'),'</h2>
<table class="t-conf">
<tr>
<th style="width:150px;">',L('Map.Center'),'</th>
<td style="width:310px;"><input type="text" id="m_gmap_gcenter" name="m_gmap_gcenter" size="26" maxlength="100" value="',$_SESSION[QT]['m_gmap_gcenter'],'" onchange="qtFormSafe.not();"/><span class="small"> ',L('Map.Latlng'),'</span></td>
<td><span class="small">',L('Map.H_Center'),'</span></td>
</tr>
<tr>
<th style="width:150px;">',L('Map.Zoom'),'</th>
<td>
<input type="text" id="m_gmap_gzoom" name="m_gmap_gzoom" size="2" maxlength="2" value="',$_SESSION[QT]['m_gmap_gzoom'],'" onchange="qtFormSafe.not();"/></td>
<td><span class="small">',L('Map.H_Zoom'),'</span></td>
</tr>
<tr>
<th style="width:150px;">',L('Map.Background'),'</th>
<td><select id="maptype" name="maptype" size="1" onchange="qtFormSafe.not();">',asTags(L('Map.Back.*'),substr($_SESSION[QT]['m_gmap_gbuttons'],0,1)),'</select></td>
<td><span class="small">',L('Map.H_Background'),'</span></td>
</tr>
<tr>
<th style="width:150px;"><label for="m_gmap_gfind">',L('Map.Address_sample'),'</label></th>
<td>
<input'.(substr($_SESSION[QT]['m_gmap_gbuttons'],6,1)=='1' ? '' : 'disabled').' type="text" id="m_gmap_gfind" name="m_gmap_gfind" size="20" maxlength="100" value="',$_SESSION[QT]['m_gmap_gfind'],'" onchange="qtFormSafe.not();"/></td>
<td><span class="small">'.(substr($_SESSION[QT]['m_gmap_gbuttons'],6,1)=='1' ? L('Map.H_Address_sample') : L('Map.Ctrl.Geocode').' (off)').'</span></td>
</tr>
';

//-----------
}
//-----------

echo '</table>
<p style="text-align:center"><button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</form>
';

if ( !empty($_SESSION[QT]['m_gmap_gkey']) )
{
  echo '<div class="gmap">'.PHP_EOL;
  echo '<p class="small commands" style="margin:2px 0 4px 2px;text-align:right">',L('Map.canmove'),' | <a class="small" href="javascript:void(0)" onclick="undoChanges(); return false;">',L('Map.undo'),'</a></p>'.PHP_EOL;
  echo '<div id="map_canvas"></div>'.PHP_EOL;
  if ( substr($_SESSION[QT]['m_gmap_gbuttons'],6,1)=='1' )
  {
  echo '<p class="small commands" style="margin:4px 0 2px 2px;text-align:right">',L('Map.addrlatlng');
  echo ' <input type="text" size="24" id="find" name="find" class="small" value="'.$_SESSION[QT]['m_gmap_gfind'].'" title="'.Lh('Map.H_addrlatlng').'" onkeypress="enterkeyPressed=qtKeyEnter(event); if (enterkeyPressed) showLocation(this.value,null);"/>';
  echo '<i id="btn-geocode" class="fa fa-search" onclick="showLocation(document.getElementById(\'find\').value,null);" title="'.Lh('Search').'"></i></p>'.PHP_EOL;
  }
  echo '</div>'.PHP_EOL;
}
else
{
  echo '<p class="minor">',L('Map.E_disabled'),'</p>';
}

// HTML END

if ( !empty($_SESSION[QT]['m_gmap_gkey']) )
{
  $gmap_symbol = empty($_SESSION[QT]['m_gmap_gsymbol']) ? false : $_SESSION[QT]['m_gmap_gsymbol']; // false = no icon but default marker
  $gmap_markers = array();
  $gmap_events = array();
  $gmap_functions = array();

  $gmap_markers[] = gmapMarker($_SESSION[QT]['m_gmap_gcenter'],true,$gmap_symbol,L('Map.Default_center'));
  $gmap_events[] = '
	google.maps.event.addListener(marker, "position_changed", function() {
		if (document.getElementById("m_gmap_gcenter")) {document.getElementById("m_gmap_gcenter").value = gmapRound(marker.getPosition().lat(),10) + "," + gmapRound(marker.getPosition().lng(),10);}
	});
	google.maps.event.addListener(marker, "dragend", function() {
		map.panTo(marker.getPosition());
	});';
  $gmap_functions[] = '
  function undoChanges()
  {
  	if (infowindow) infowindow.close();
  	if (markers[0]) markers[0].setPosition(mapOptions.center);
  	if (mapOptions) map.panTo(mapOptions.center);
  	return null;
  }
  function showLocation(address,title)
  {
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK)
      {
        map.setCenter(results[0].geometry.location);
        if ( markers[0] )
        {
          markers[0].setPosition(results[0].geometry.location);
        } else {
          markers[0] = new google.maps.Marker({map: map, position: results[0].geometry.location, draggable: true, animation: google.maps.Animation.DROP, title: title});
        }
        gmapYXfield("qtf_gcenter",markers[0]);
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
  ';
  include 'qtfm_gmap_load.php';
}

include 'qtf_adm_inc_ft.php';