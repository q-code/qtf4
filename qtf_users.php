<?php // v4.0 build:20240210

session_start();
/**
 * @var string $formAddUser
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
require 'bin/init.php';

if ( SUser::role()!=='A' && $_SESSION[QT]['board_offline'] ) $oH->voidPage('tools.svg',99,true,false); //█
if ( !SUser::canAccess('show_memberlist') ) $oH->voidPage('user-lock.svg',11,true); //█

// CHANGE USER INTERFACE
if ( isset($_GET['view'])) $_SESSION[QT]['viewmode'] = substr($_GET['view'],0,1);

// INITIALISE
$oH->name = L('Memberlist');
$pn = 1; $po = 'name'; $pd = 'asc'; // page number,order,direction
$fg = 'all'; // filter by group
qtArgs('int+:pn po char4:pd fg', true, false);

// MAP MODULE
$useMap=false;
$arrMapData = [];
if ( qtModule('gmap') ) {
  include translate(APP.'m_gmap.php');
  include 'qtfm_gmap_lib.php';
  if ( gmapCan('U') ) $useMap=true;
  if ( $useMap ) $oH->links[]='<link rel="stylesheet" type="text/css" href="qtfm_gmap.css"/>';
  if ( isset($_GET['hidemap']) ) $_SESSION[QT]['m_gmap_hidelist']=true;
  if ( isset($_GET['showmap']) ) $_SESSION[QT]['m_gmap_hidelist']=false;
  if ( !isset($_SESSION[QT]['m_gmap_hidelist']) ) $_SESSION[QT]['m_gmap_hidelist']=false;
  if ( !isset($_SESSION[QT]['m_gmap_symbols']) ) $_SESSION[QT]['m_gmap_symbols']='0';
  $arrSymbolByRole = ( empty($_SESSION[QT]['m_gmap_symbols']) ? array() : qtExplode($_SESSION[QT]['m_gmap_symbols']) );
}

// Query by lettre
$arrGroup = array_filter(explode('|',$fg)); // filter to remove empty
if ( count($arrGroup)===1 ) {
  switch((string)$fg) {
    case 'all': $sqlWhere = ''; break;
    case '~':   $sqlWhere = ' AND '.sqlFirstChar('name','~'); break;
    default:    $sqlWhere = ' AND '.sqlFirstChar('name','u',strlen($fg)).'="'.strtoupper($fg).'"'; break;
  }
} else {
  $arr = [];
  foreach($arrGroup as $str) $arr[] = sqlFirstChar('name','u').'="'.strtoupper($str).'"';
  $sqlWhere = ' AND ('.implode(' OR ',$arr).')';
}

// COUNT
$intTotal = $oDB->count( TABUSER." WHERE id>0" );
$intCount = $fg=='all' ? $intTotal : $oDB->count( TABUSER." WHERE id>0".$sqlWhere );

// Defines FORM $formAddUser and handles POST
if ( SUser::isStaff() ) include APP.'_inc_adduser.php';

// ------
// HTML BEGIN
// ------
include 'qtf_inc_hd.php';

// ------
// Title and top 5
// ------
echo '<div id="ct-title" class="fix-sp top">
<div><h2>'.$oH->name.'</h2>
<p>'.( $fg=='all' ? $intTotal.' '.L('Members') : $intCount.' / '.$intTotal.' '.L('Members') );
if ( SUser::canAccess('show_calendar') )echo ' &middot; <a href="'.url('qtf_calendar.php').'" style="white-space:nowrap">'.L('Birthdays_calendar').'</a>';
if ( !empty($formAddUser) )
echo ' &middot; <span style="white-space:nowrap">'.SUser::getStamp(SUser::role(), 'class=stamp08').' <a id="tgl-ctrl" href="javascript:void(0)" class="tgl-ctrl" onclick="qtToggle(`#participants,#tgl-container`);qtToggle(`svg`,`nodisplay toggle`,this);">'.L('User_add').qtSvg('angle-down', isset($_POST['title']) ? 'class=nodisplay' : '').qtSvg('angle-up', isset($_POST['title']) ? '' : 'class=nodisplay').'</a></span>';
echo '</p>
</div>
';

echo '<div id="participants"'.(isset($_POST['title']) ? ' style="display:none"' : '').' class="strongbox">
<p class="title">'.L('Top_participants').'</p>
<table>
';
// Top 5 participants
$sqlState = 'name, id, numpost FROM TABUSER WHERE id>0';
$oDB->query( sqlLimit($sqlState, 'numpost DESC', 0, $_SESSION[QT]['viewmode']==='C' ? 2 : 5) );
while($row = $oDB->getRow()) {
  echo '<tr><td><a href="'.url('qtf_user.php').'?id='.$row['id'].'">'.$row['name'].'</a></td><td class="right">'.qtK((int)$row['numpost']).'</td></tr>';
}

echo '</table>
</div>';

// Form Add User
if ( !empty($formAddUser) ) echo $formAddUser;

echo '</div>
';

// ------
// Button line and paging
// ------
// -- build paging --
$paging = makePager( url('qtf_users.php?fg='.$fg.'&po='.$po.'&pd='.$pd), $intCount, (int)$_SESSION[QT]['items_per_page'], $pn );
if ( !empty($paging) ) $paging = L('Page').$paging;
if ( $intCount<$intTotal ) $paging = L('user',$intCount).' '.L('from').' '.$intTotal.(empty($paging) ? '' : ' | '.$paging);

// -- Display button line (if more that tpp users) and paging --
if ( $intCount>$_SESSION[QT]['items_per_page'] || $fg!=='all' )
echo htmlLettres(url($oH->php), $fg, L('All'), L('Username_starting').' ', $intTotal>300 ? 1 : ($intTotal>2*$_SESSION[QT]['items_per_page'] ? 2 : 3)).PHP_EOL;

if ( !empty($paging) )
echo '<p class="paging">'.$paging.'</p>'.PHP_EOL;

// end if no result
if ( $intCount==0) {
  echo '<p>'.L('None').'</p><br>';
  include 'qtf_inc_ft.php';
  exit;
}

// ------
// Memberlist
// ------
$bCompact = FALSE;
if ( empty(qtExplodeGet($_SESSION[QT]['formatpicture'],'mime')) ||  $_SESSION[QT]['viewmode']=='C' ) $bCompact = true;

// Table definition
$t = new TabTable('id=t1|class=t-user',$intCount);
$t->thead();
$t->tbody();
$t->activecol = 'user'.$po;
$t->activelink = '<a href="'.$oH->php.'?fg='.$fg.'&po='.$po.'&pd='.($pd=='asc' ? 'desc' : 'asc').'">%s</a> '.qtSvg('caret-'.($pd==='asc' ? 'down' : 'up'));
// TH
if ( !$bCompact )
$t->arrTh['userphoto'] = new TabHead(qtSvg('camera'), 'title='.L('Picture'));
$t->arrTh['username'] = new TabHead(L('Username'), '', '<a href="'.$oH->php.'?fg='.$fg.'&po=name&pd=asc">%s</a>');
$t->arrTh['userrole'] = new TabHead(L('Role'), '', '<a href="'.$oH->php.'?fg='.$fg.'&po=role&pd=asc">%s</a>');
$t->arrTh['usercontact'] = new TabHead(L('Contact'));
$t->arrTh['userlocation'] = new TabHead(L('Location'), '', '<a href="'.$oH->php.'?fg='.$fg.'&po=location&pd=asc">%s</a>');
$t->arrTh['usernumpost'] = new TabHead(qtSvg('comments'), 'title="'.L('Messages').'"', '<a href="'.$oH->php.'?fg='.$fg.'&po=numpost&pd=desc">%s</a>');
if ( SUser::isStaff() )
$t->arrTh['userpriv'] = new TabHead(qtSvg('info'), 'title="'.L('Privacy').'"');
foreach(array_keys($t->arrTh) as $key) $t->arrTh[$key]->append('class','c-'.$key);
// TD
$t->cloneThTd();

// === TABLE START DISPLAY ===

echo $t->start();
echo $t->thead->start();
echo $t->getTHrow();
echo $t->thead->end();
echo $t->tbody->start();

$sqlStart = ($pn-1)*$_SESSION[QT]['items_per_page'];
$oDB->query( sqlLimit('* FROM TABUSER WHERE id>0'.$sqlWhere, $po.' '.strtoupper($pd), $sqlStart, $_SESSION[QT]['items_per_page'], $intCount) );

$intWhile=0;
while($row=$oDB->getRow()) {

	// privacy control for map and location field
	if ( !SUser::canSeePrivate((int)$row['privacy'],(int)$row['id']) ) { $row['y']=null; $row['x']=null; }

	// prepare row
  if ( !$bCompact )
  $t->arrTd['userphoto']->content = '<div class="magnifier center">'.SUser::getPicture((int)$row['id'], 'data-magnify=0|onclick=this.dataset.magnify=this.dataset.magnify==1?0:1;', '').'</div>';
  $t->arrTd['username']->content = '<a href="'.url('qtf_user.php').'?id='.$row['id'].'">'.qtTrunc($row['name'],24).'</a>';
  $t->arrTd['userrole']->content = L('Role_'.strtoupper($row['role']));
  $t->arrTd['usercontact']->content = renderUserMailSymbol($row).' '.renderUserWwwSymbol($row);
  $t->arrTd['userlocation']->content = empty($row['location']) ? '' : $row['location'];
  $t->arrTd['usernumpost']->content = qtK((int)$row['numpost']);
  if ( isset($t->arrTh['userpriv']) )
  $t->arrTd['userpriv']->content = renderUserPrivSymbol($row);

	// map settings
	if ( $useMap && !gmapEmpty($row['x']) && !gmapEmpty($row['y']) ) {
	  $y = (float)$row['y']; $x = (float)$row['x'];
		$strPname = $row['name'];
		$strPinfo = $row['name'].'<br><a class="gmap" href="'.url('qtf_user.php').'?id='.$row['id'].'">'.L('Profile').'</a>';
		$strPinfo = SUser::getPicture((int)$row['id'], 'class=markerprofileimage', '').$strPinfo;
		$oMapPoint = new CMapPoint($y,$x,$strPname,$strPinfo);
		if ( !empty($arrSymbolByRole[$row['role']]) ) $oMapPoint->marker = $arrSymbolByRole[$row['role']];
		$arrMapData[(int)$row['id']] = $oMapPoint;
		if ( $_SESSION[QT]['m_gmap_hidelist'] ) {
		  $t->arrTd['userlocation']->content .= ' '.qtSvg('#map-marker-alt');
		} else {
		  $t->arrTd['userlocation']->content .= ' <span class="clickable" data-coord="'.$y.','.$x.'" onclick="gmapPan(this.dataset.coord)" title="'.L('Show').'">'.qtSvg('#map-marker-alt').'</span>';
		}
	}

	//show row content
	echo $t->getTDrow('class=t-user hover');

	$intWhile++;
	if ( $intWhile>=$_SESSION[QT]['items_per_page'] ) break;

}

// === TABLE END DISPLAY ===

echo $t->tbody->end();
echo $t->end();

// -- Display paging --

if ( !empty($paging) ) echo '<p class="paging">'.$paging.'</p>'.PHP_EOL;

// MAP MODULE, Show map

if ( $useMap ) {
  if ( count($arrMapData)==0 ) {
    echo '<div class="gmap_disabled">'.$L['Gmap']['E_noposition'].'</div>';
    $useMap = false;
  } else {
    //select zoomto (maximum 20 items in the list)
    $str = '';
    if ( count($arrMapData)>1 ) {
      $str = '<p class="gmap commands" style="margin:0 0 4px 0"><a class="gmap" href="javascript:void(0)" onclick="zoomToFullExtend(); return false;">'.$L['Gmap']['zoomtoall'].'</a> | '.L('Show').' <select class="gmap" id="zoomto" name="zoomto" size="1" onchange="gmapPan(this.value);">';
      $str .= '<option class="small_gmap" value="'.$_SESSION[QT]['m_gmap_gcenter'].'"> </option>';
      $i=0;
      foreach($arrMapData as $oMapPoint) {
        $str .= '<option class="small_gmap" value="'.$oMapPoint->y.','.$oMapPoint->x.'">'.$oMapPoint->title.'</option>';
        $i++; if ( $i>20 ) break;
      }
      $str .= '</select></p>'.PHP_EOL;
    }

    echo '<div class="gmap">'.PHP_EOL;
    echo ($_SESSION[QT]['m_gmap_hidelist'] ? '' : $str.PHP_EOL.'<div id="map_canvas"></div>'.PHP_EOL);
    echo '<p class="gmap" style="margin:4px 0 0 0">'.sprintf($L['Gmap']['items'],strtolower( L('User',count($arrMapData))),strtolower(L('User',$intCount)) ).'</p>'.PHP_EOL;
    echo '</div>'.PHP_EOL;

    // Show/Hide
    if ( $_SESSION[QT]['m_gmap_hidelist'] ) {
    echo '<div class="canvashandler"><a class="canvashandler" href="'.url($oH->php).'?showmap">'.qtSvg('chevron-down').' '.$L['Gmap']['Show_map'].'</a></div>'.PHP_EOL;
    } else {
    echo '<div class="canvashandler"><a class="canvashandler" href="'.url($oH->php).'?hidemap">'.qtSvg('chevron-up').' '.$L['Gmap']['Hide_map'].'</a></div>'.PHP_EOL;
    }
  }
}

// ------
// HTML END
// ------
// MAP MODULE
if ( $useMap && !$_SESSION[QT]['m_gmap_hidelist'] ) {

  /**
  * @var array $gmap_markers
  * @var array $gmap_events
  * @var array $gmap_functions
  */
  $gmap_symbol = empty($_SESSION[QT]['m_gmap_gsymbol']) ? '' : $_SESSION[QT]['m_gmap_gsymbol'];

  // check new map center
  $y = floatval(QTgety($_SESSION[QT]['m_gmap_gcenter']));
  $x = floatval(QTgetx($_SESSION[QT]['m_gmap_gcenter']));

  // center on the first item
  foreach($arrMapData as $oMapPoint)
  {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
    $y=$oMapPoint->y;
    $x=$oMapPoint->x;
    break;
    }
  }
  // update center
  $_SESSION[QT]['m_gmap_gcenter'] = $y.','.$x;

  $gmap_markers = [];
  $gmap_events = [];
  $gmap_functions = [];
  foreach($arrMapData as $oMapPoint)
  {
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) )
    {
      $user_symbol = $gmap_symbol; // required to reset symbol on each user
      if ( !empty($oMapPoint->marker) ) $user_symbol = $oMapPoint->marker;
      $gmap_markers[] = gmapMarker($oMapPoint->y.','.$oMapPoint->x,false,$user_symbol,$oMapPoint->title,$oMapPoint->info);
    }
  }
  $gmap_functions[] = '
  function zoomToFullExtend() {
    if ( markers.length<2 ) return;
    var bounds = new google.maps.LatLngBounds();
    for (var i=markers.length-1; i>=0; i--) bounds.extend(markers[i].position);
    gmap.fitBounds(bounds);
  }
  function showLocation(address) {
    if ( gmapInfoBox ) gmapInfoBox.close();
    gmapCoder.geocode( { "address": address}, function(results, status) {
      if ( status==google.maps.GeocoderStatus.OK) {
        gmap.setCenter(results[0].geometry.location);
        if ( marker ) {
          marker.position = results[0].geometry.location;
        } else {
          marker = new google.maps.marker.AdvancedMarkerElement({map: gmap, position: results[0].geometry.location, draggable: true, title: "Move to define the default map center"});
        }
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }

  ';
  include 'qtfm_gmap_load.php';

}

// Symbols
$oH->symbols[] = qtSvgSymbol('envelope');
$oH->symbols[] = qtSvgSymbol('home');
$oH->symbols[] = qtSvgSymbol('map-marker-alt');
$oH->symbols[] = qtSvgSymbol('key');
$oH->symbols[] = qtSvgSymbol('door-open');

// hide fix-sp-bottom-controls if less than 5 table rows
$oH->scripts[] = 'qtHideAfterTable(".table-ui.bot");';

include 'qtf_inc_ft.php';