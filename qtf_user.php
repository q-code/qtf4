<?php // v4.0 build:20230430

session_start();
/**
* @var CHtml $oH
* @var array $L
* @var CDatabase $oDB
*/
require 'bin/init.php';

$oH->selfurl = 'qtf_user.php';
if ( SUser::role()!=='A' && $_SESSION[QT]['board_offline'] ) exitPage(99,'tools.svg',false); //...
if ( SUser::role()==='V' ) exitPage(11,'user-lock.svg'); //...

$id = -1;
qtArgs('int:id!');
if ( $id<0 ) die('Wrong id');

if ( isset($_GET['edit']) ) $_SESSION[QT]['editing']=($_GET['edit']=='1' ? true : false);
if ( isset($_POST['edit']) ) $_SESSION[QT]['editing']=($_POST['edit']=='1' ? true : false);

// --------
// INITIALISE
// --------

include 'bin/class/class.phpmailer.php';
include translate('lg_reg.php');

$canEdit = false;
if ( SUser::id()==$id || SUser::role()==='A' ) $canEdit=true;
if ( SUser::role()==='M' ) $canEdit=true;
if ( $id==0 ) $canEdit=false;
if ( !isset($_SESSION[QT]['editing']) || !$canEdit) $_SESSION[QT]['editing']=false;

$oH->selfname = L('Profile');

// MAP MODULE

$bMap=false;
$arrMapData=array();
if ( qtModule('gmap') )
{
  include translate(APP.'m_gmap.php');
  include 'qtfm_gmap_lib.php';
  if ( gmapCan('U') ) $bMap=true;
  if ( $bMap )
  {
  $oH->links[]=  '<link rel="stylesheet" type="text/css" href="qtfm_gmap.css"/>';
  if ( !isset($_SESSION[QT]['m_gmap_symbols']) ) $_SESSION[QT]['m_gmap_symbols']='0';
  $arrSymbolByRole = empty($_SESSION[QT]['m_gmap_symbols']) ? array() : qtExplode($_SESSION[QT]['m_gmap_symbols']);
  }
}

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) ) try {

    // check form
  $strLoca = qtDb(trim($_POST['location']));
  $strMail = str_replace([' ',';'],',',$_POST['mail']);
  $strMail = implode(',',qtCleanArray($strMail));
  if ( !empty($strMail) && !qtIsMail($strMail) ) throw new Exception( L('Email').' '.$strMail.' '.L('invalid') );
  if ( empty($_POST['birth_y']) || empty($_POST['birth_d']) || empty($_POST['birth_d']) ) {
    $strBirth = '0';
  } else {
    $i = intval($_POST['birth_y'])*10000+intval($_POST['birth_m'])*100+intval($_POST['birth_d']);
    if ( !qtIsValiddate($i,true,false,false) ) throw new Exception( L('Birthday').' ('.$_POST['birth_y'].'-'.$_POST['birth_m'].'-'.$_POST['birth_d'].') '.L('invalid') );
    $strBirth = $i;
  }

  if ( isset($_POST['child']) ) { $strChild = substr($_POST['child'],0,1); } else { $strChild = '0'; }
  if ( $id==1 && $strChild!='0' ) throw new Exception( 'user id[1] is admin and child status cannot be changed...' );
  if ( $id==0 && $strChild!='0' ) throw new Exception( 'user id[0] is visitor and child status cannot be changed...' );

  if ( isset($_POST['parentmail']) ) { $strParentmail = trim($_POST['parentmail']); } else { $strParentmail=''; }
  if ( !empty($strParentmail) && !qtIsMail($strParentmail) ) throw new Exception( L('Parent_mail').' '.L('invalid') );

  $strWww = qtAttr($_POST['www']);
  if ( !empty($strWww) && substr($strWww,0,4)!=='http' ) throw new Exception( L('Website').' '.L('invalid') );
  if ( empty($strWww) || $strWww=='http://' || $strWww=='https://' ) $strWww='';

  // Save
  $oDB->exec( "UPDATE TABUSER SET birthday=?,location=?,mail=?,www=?,privacy=?,children=?,parentmail=? WHERE id=".$id,
    [$strBirth,$strLoca,$strMail,$strWww,$_POST['privacy'],$strChild,$strParentmail]
    );
  if ( isset($_POST['coord']) )
  {
    $coord = strip_tags(trim($_POST['coord']));
    $coord = str_replace(' ','',$coord); // remove spaces between coordinates y,x
    SUser::setCoord($oDB,$id,$coord); // coord can be empty (coordinates are removed)
  }

  // parent warning if coppa
  if ( $strChild=='1' && $_SESSION[QT]['register_coppa']=='1' ) {
    $strSubject='Profile updated';
    $strMessage="Your children (login: %s) has modified his/her profile on the board {$_SESSION[QT]['site_name']}.";
    $strFile = qtDirLang().'mail_profile_coppa.php';
    if ( file_exists($strFile) ) include $strFile;
    $strMessage = sprintf($strMessage, $_POST['name']);
    if ( !empty($_POST['parentmail']) ) qtMail($_POST['parentmail'],$strSubject,$strMessage,QT_HTML_CHAR);
  }

  // exit (if no error)
  $oH->exiturl = 'qtf_user.php?id='.$id;
  $oH->exitname = $L['Profile'];
  $_SESSION[QT.'splash'] = L('S_update');
  $oH->redirect($oH->exiturl);

} catch (Exception $e) {

  $oH->error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;
}

// --------
// STATS AND USER
// --------

// COUNT TOPICS
$items = $oDB->count( TABTOPIC.' WHERE firstpostuser='.$id );
// COUNT MESSAGES
$countmessages = $oDB->count( TABPOST.' WHERE userid='.$id );
// QUERY USER
$oDB->query( "SELECT * FROM TABUSER WHERE id=$id" );
$row = $oDB->getRow();
$row['privacy'] = (int)$row['privacy']; // int
// check staff edit grants
if ( SUser::role()==='M' && SUser::id()!==$id) {
  if ( $row['role']=='U' && !QT_STAFFEDITUSER ) { $canEdit=false; $_SESSION[QT]['editing']=false; }
  if ( $row['role']=='M' && !QT_STAFFEDITSTAFF ) { $canEdit=false; $_SESSION[QT]['editing']=false; }
  if ( $row['role']=='A' && !QT_STAFFEDITADMIN ) { $canEdit=false; $_SESSION[QT]['editing']=false; }
}
// check privacy
if ( !SUser::canSeePrivate($row['privacy'],$id) ) { $row['y']=null; $row['x']=null; }

// map settings
if ( $bMap && !gmapEmpty($row['x']) && !gmapEmpty($row['y']) )
{
  $y = (float)$row['y']; $x = (float)$row['x'];
  $strPname = $row['name'];
  $oMapPoint = new CMapPoint($y,$x,$strPname);
  if ( !empty($arrSymbolByRole[$row['role']]) ) $oMapPoint->icon = $arrSymbolByRole[$row['role']];
  $arrMapData[$id] = $oMapPoint;
}

// DEFAULT
$strMail = '';  if ( !empty($row['mail']) && SUser::canSeePrivate($row['privacy'],$id) ) $strMail = renderEmail($row['mail'],'txt'.(QT_JAVA_MAIL ? 'java' : ''));
$strLocation = ''; if ( !empty($row['location']) && SUser::canSeePrivate($row['privacy'],$id) ) $strLocation = $row['location'];
$strCoord = ''; // coordinates with visual units
$strYX = ''; // coordinates in map unit [y,x]
if ( $bMap && !empty($row['x']) && !empty($row['y']) && SUser::canSeePrivate($row['privacy'],$id) )
{
  $strYX = round((float)$row['y'],8).','.round((float)$row['x'],8);
  $strCoord = QTdd2dms((float)$row['y']).', '.QTdd2dms((float)$row['x']).' '.$L['Coord_latlon'].' <span class="small disabled">DD '.$strYX.'</span>';
}
$strPriv = renderUserPrivSymbol($row);

// --------
// HTML BEGIN
// --------

include 'qtf_inc_hd.php';

if ( $id<0 )  die('Wrong id in qtf_user.php');

// USER name and UI
echo '<div id="user-name"><h1>'.$row['name'].' '.SUser::getStamp($row['role']).'</h1></div>
<div id="user-ui" class="right">';
include 'qtf_user_ui.php';
echo '</div>
';

// USER PROFILE
echo '<div id="user-menu">
';
echo SUser::getPicture($id, 'id=userimg').PHP_EOL;

if ( $canEdit ) {
  if ( !empty(qtExplodeGet($_SESSION[QT]['formatpicture'],'mime')) ) {
  echo '<p><a href="'.url('qtf_user_img.php').'?id='.$id.'">'.L('Change_picture').'</a></p>';
  }
  echo '<p><a href="'.url('qtf_register.php').'?a=sign&id='.$id.'">'.L('Change_signature').'</a></p>';
  if ( SUser::role()==='A' || SUser::id()==$id ) echo '<p><a href="'.url('qtf_register.php').'?a=pwd&id='.$id.'">'.L('Change_password').'</a></p>';
  if ( SUser::role()==='A' || SUser::id()==$id ) echo '<p><a href="'.url('qtf_register.php').'?a=qa&id='.$id.'">'.L('Secret_question').'</a></p>';
  if ( SUser::role()==='A' || (SUser::id()==$id && QT_CHANGE_USERNAME) ) echo '<p><a href="'.url('qtf_register.php').'?a=name&id='.$id.'">'.L('Change_name').'</a></p>';
  if ( SUser::id()===$id || SUser::role()==='A' ) echo '<p><a href="'.url('qtf_register.php').'?a=out&id='.$id.'">'.L('Unregister').'</a></p>';
}
if ( SUser::canAccess('show_calendar') ) {
echo '<p><a href="'.url('qtf_calendar.php').(empty($row['birthday']) ? '' : '?m='.substr($row['birthday'],4,2)).'">'.L('Birthdays_calendar').'</a></p>';
}

echo '</div>
<div id="user-main">
';

// -- EDIT PROFILE --
if ( $_SESSION[QT]['editing'] ) {
// -- EDIT PROFILE --

if ( SUser::id()!==$id ) echo '<p>'.qtSVG('exclamation-triangle', 'style=color:orange').' '.L('Not_your_account').'</p>';
if ( !isset($oH->scripts['e0']) ) $oH->scripts['e0'] = 'var e0 = '.(empty(L('E_editing')) ? 'Data not yet saved. Quit without saving?' : '"'.L('E_editing').'"').';';

echo '<form method="post" action="'.url('qtf_user.php').'?id='.$id.'">
<table class="t-profile">
<tr><th>'.L('Username').'</th><td clss="c-name">'.$row['name'].'</td></tr>
<tr><th>'.L('Role').'</th><td>'.L('Role_'.$row['role']).($row['role']==='A' ? ' <small>'.qtSVG('user-A', 'title='.L('Role_A')).'</small>' : '').'</td></tr>
<tr><th>'.L('Location').'</th><td><input type="text" name="location" size="35" maxlength="24" value="'.$row['location'].'" onchange="qtFormSafe.not()"/></td></tr>
<tr><th>'.L('Email').'</th><td><input type="email" name="mail" size="35" maxlength="64" value="'.$row['mail'].'" onchange="qtFormSafe.not()" multiple/></td></tr>
<tr><th>'.L('Website').'</th><td><input type="text" name="www" pattern="^(http://|https://).*" size="35" maxlength="64" value="'.(empty($row['www']) ? '' : $row['www']).'" title="'.L('H_Website').'" onchange="qtFormSafe.not()"/></td></tr>
<tr><th>'.L('Birthday').'</th>
';
$strBrith_y = '';
$strBrith_m = '';
$strBrith_d = '';
if ( !empty($row['birthday']) ) {
  $strBrith_y = intval(substr(strval($row['birthday']),0,4));
  $strBrith_m = intval(substr(strval($row['birthday']),4,2));
  $strBrith_d = intval(substr(strval($row['birthday']),6,2));
}
echo '<td><select name="birth_d" size="1" onchange="qtFormSafe.not()">'.PHP_EOL;
echo qtTags([0=>'',1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31],$strBrith_d);
echo '</select>'.PHP_EOL;
echo '<select name="birth_m" size="1" onchange="qtFormSafe.not()">'.PHP_EOL;
echo '<option value="0"></option>'.qtTags(L('dateMMM.*'),$strBrith_m);
echo '</select>'.PHP_EOL;
echo '<input type="text" id="birth_y" name="birth_y" pattern="(19|20)[0-9]{2}" size="4" maxlength="4" value="'.$strBrith_y.'"/>';
echo '</td></tr>'.PHP_EOL;
if ( SUser::role()==='A' && $id>1 ) {
  if ( $_SESSION[QT]['register_coppa']==='1' ) {
  echo '<tr>'.PHP_EOL;
  echo '<th>'.L('Child').'</th>';
  echo '<td>';
  echo '<select size="1" name="child" onchange="qtFormSafe.not()">';
  echo '<option value="0"'.($row['children']==='0' ? ' selected' : '').'>'.L('N').'</option>';
  echo '<option value="1"'.($row['children']==='1' ? ' selected' : '').'>'.L('Y').' '.L('With_parent_agree').'</option>';
  echo '<option value="2"'.($row['children']==='2' ? ' selected' : '').'>'.L('Y').' '.L('Without_parent_agree').'</option>';
  echo '</select>';
  echo '</td>';
  echo '</tr>'.PHP_EOL;
  echo '<tr>';
  echo '<th>'.L('Parent_mail').'</th>';
  echo '<td><input type="email" name="parentmail" size="32" maxlength="255ù" value="'.$row['parentmail'].'" onchange="qtFormSafe.not()" multiple/></td>';
  echo '</tr>'.PHP_EOL;
  }
}

echo '<tr>
<th>'.L('Privacy').'</th>
<td>'.L('Email').'/'.L('Location').($bMap ? '/'.$L['Gmap']['position'] : '').' <select size="1" name="privacy" onchange="qtFormSafe.not()">
<option value="2"'.($row['privacy']===2 ? ' selected' : '').'>'.L('Privacy_visible_2').'</option>
<option value="1"'.($row['privacy']===1 ? ' selected' : '').'>'.L('Privacy_visible_1').'</option>
<option value="0"'.($row['privacy']===0 ? ' selected' : '').'>'.L('Privacy_visible_0').'</option>
</select></td>
</tr>
';

if ( $bMap )
{
  $strPosition  = '<p class="small commands" style="margin:2px 0 4px 2px;text-align:right">'.$L['Gmap']['cancreate'];
  if ( !empty($row['x']) && !empty($row['y']) )
  {
    $_SESSION[QT]['m_gmap_gcenter'] = $strYX;
    $strPosition  = '<p class="small commands" style="margin:2px 0 4px 2px;text-align:right">'.$L['Gmap']['canmove'];
  }
  $strPosition .= ' | <a class="small" href="javascript:void(0)" onclick="createMarker(); return false;" title="'.$L['Gmap']['H_pntadd'].'">'.$L['Gmap']['pntadd'].'</a>';
  $strPosition .= ' | <a class="small" href="javascript:void(0)" onclick="deleteMarker(); return false;">'.$L['Gmap']['pntdelete'].'</a>';
  $strPosition .= '</p>'.PHP_EOL;
  $strPosition .= '<div id="map_canvas"></div>'.PHP_EOL;
  if ( substr($_SESSION[QT]['m_gmap_gbuttons'],6,1)==='1' ) {
    $strPosition .= '< class="small commands" style="margin:4px 0 2px 2px;text-align:right">'.$L['Gmap']['addrlatlng'].' ';
    $strPosition .= '<input type="text" size="24" id="find" name="find" class="small" value="'.$_SESSION[QT]['m_gmap_gfind'].'" title="'.$L['Gmap']['H_addrlatlng'].'" onkeypress="enterkeyPressed=qtKeyEnter(event); if ( enterkeyPressed) showLocation(this.value,null);"/>';
    $strPosition .= qtSVG('search', 'id=btn-geocode|class=clickable|onclick=showLocation(document.getElementById(`find`).value,null)|title='.L('Search') );
    $strPosition .= '</p>'.PHP_EOL;
  }
  echo '<tr>'.PHP_EOL;
  echo '<th>'.L('Coord').'</th>';
  echo '<td><input type="text" id="yx" name="coord" pattern="^(-?\d+(\.\d+)?),\s*(-?\d+(\.\d+)?)$" size="32" value="'.$strYX.'" title="y,x in decimal degree (without trailing spaces)"/> <span class="small">'.L('Coord_latlon').'</span></td>';
  echo '</tr>'.PHP_EOL;
}

echo '<tr>
<th><input type="hidden" name="id" value="'.$id.'"/><input type="hidden" name="name" value="'.$row['name'].'"/></th>
<td><button type="submit" name="ok" value="ok">'.L('Save').'</button>'.( !empty($oH->error) ? ' <span class="error">'.$oH->error.'</span>' : '' ).'</td>
</tr>
';

if ( $bMap ) {
  echo '<tr>
<td colspan="2" id="gmapcontainer">'.$strPosition.'</td>
</tr>
';
}

echo '
</table>
</form>
';

// ------
} else {
// ------

$strParticip = '';
if ( $items>0 ) {
  $strParticip .= '<a href="'.url('qtf_items.php').'?q=user&v2='.$id.'&v='.urlencode($row['name']).'">'.L('Item',$items).'</a>, ';
}
if ( $countmessages>0 ) {
  $strParticip .= '<a href="'.url('qtf_items.php').'?q=userm&v2='.$id.'&v='.urlencode($row['name']).'">'.L('Message',$countmessages).'</a>';
  $strParticip .= ', '.strtolower($L['Last_message']).' '.qtDatestr($row['lastdate'],'$','$',true);
  $oDB->query( "SELECT p.id,p.topic,p.forum FROM TABPOST p WHERE p.userid=$id ORDER BY p.issuedate DESC" );
  $row2 = $oDB->getRow();
  $strParticip .= ' <a href="'.url('qtf_item.php').'?t='.$row2['topic'].'#p'.$row2['id'].'" title="'.L('Goto_message').'">'.qtSVG('caret-square-right').'</a>';
}
echo '
<table class="t-profile">
<tr><th>'.L('Username').'</th><td>'.$row['name'].'</td></tr>
<tr><th>'.L('Role').'</th><td>'.L('Role_'.$row['role']).'</td></tr>
<tr><th>'.L('Location').'</th><td class="fix-sp"><span>'.$strLocation.'</span><span>'.$strPriv.'</span></td></tr>
<tr><th>'.L('Email').'</th><td class="fix-sp"><span>'.$strMail.'</span><span>'.$strPriv.'</span></td></tr>
<tr><th>'.L('Website').'</th><td>'.( empty($row['www']) ? '&nbsp;' : '<a href="'.$row['www'].'" target="_blank">'.$row['www'].'</a>' ).'</td></tr>
<tr><th>'.L('Birthday').'</th><td>'.(empty($row['birthday']) ? '&nbsp;' : qtDatestr($row['birthday'],'$','')).'</td></tr>
<tr><th>'.L('Joined').'</th><td>'.qtDatestr($row['firstdate'],'$','$',true).'</td></tr>
<tr><th>'.L('Messages').'</th><td>'.$strParticip.'</td></tr>
';

if ( is_null($row['x']) || is_null($row['y']) ) $bMap = false;
if ( $bMap ) {
  $strPlink = '<a href="http://maps.google.com?q='.$row['y'].','.$row['x'].'" class="small" title="'.$L['Gmap']['In_google'].'" target="_blank">[G]</a>';
  $strPosition = '<div id="map_canvas" style="width:100%; height:350px;"></div>';
  echo '<tr><th>'.L('Coord').'</th><td class="fix-sp"><span>'.$strCoord.' '.$strPlink.'</span><span>'.$strPriv.'</span></td></tr>'.PHP_EOL;
  echo '<tr><td colspan="2" id="gmapcontainer">'.$strPosition.'</td></tr>'.PHP_EOL;
}

echo '</table>
';

// ------
}
// ------

if ( !$_SESSION[QT]['editing'] ) {
if ( SUser::id()==$id || SUser::isStaff() ) {
  echo '<p class="right"><small>'.$strPriv.' '.L('Privacy_visible_'.$row['privacy']).'</small></p>';
  $intBan = empty($row['closed']) ? 0 : (int)$row['closed'];
  $days = BAN_DAYS;
  if ( $intBan && array_key_exists($intBan,$days) ) echo '<p class="right"><small>'.qtSVG('ban').' '.$row['name'].' '.strtolower(sprintf(L('Is_banned_since'),L('day',$days[$intBan]))).'</small></p>';
}}

echo '</div>
';

// --------
// HTML END
// --------

// MAP MODULE

if ( $bMap )
{
  /**
  * @var array $gmap_markers
  * @var array $gmap_events
  * @var array $gmap_functions
  */
  $gmap_symbol = empty($_SESSION[QT]['m_gmap_gsymbol']) ? false : $_SESSION[QT]['m_gmap_gsymbol']; // false = no icon but default marker

  // check new map center
  $y = (float)QTgety($_SESSION[QT]['m_gmap_gcenter']);
  $x = (float)QTgetx($_SESSION[QT]['m_gmap_gcenter']);

  // First item is the user's location and symbol
  if ( isset($arrMapData[$id]) )  {
    // symbol by role
    $oMapPoint = $arrMapData[$id];
    if ( !empty($oMapPoint->icon) ) $gmap_symbol = $oMapPoint->icon;

    // center on user
    if ( !empty($oMapPoint->y) && !empty($oMapPoint->x) ) {
    $y=$oMapPoint->y;
    $x=$oMapPoint->x;
    }
  }

  // update center
  $_SESSION[QT]['m_gmap_gcenter'] = $y.','.$x;

  $gmap_markers = array();
  $gmap_events = array();
  $gmap_functions = array();
  if ( isset($arrMapData[$id]) && !empty($oMapPoint->y) && !empty($oMapPoint->x) ) {
  $gmap_markers[] = gmapMarker($oMapPoint->y.','.$oMapPoint->x,true,$gmap_symbol,$row['name']);
  $gmap_events[] = '
	google.maps.event.addListener(markers[0], "position_changed", function() {
		if ( document.getElementById("yx")) {document.getElementById("yx").value = gmapRound(marker.getPosition().lat(),10) + "," + gmapRound(marker.getPosition().lng(),10);}
	});
	google.maps.event.addListener(markers[0], "dragend", function() {
		map.panTo(marker.getPosition());
	});';
  }
  $gmap_functions[] = '
  function showLocation(address,title)
  {
    if ( infowindow ) infowindow.close();
    geocoder.geocode( { "address": address}, function(results, status) {
      if ( status == google.maps.GeocoderStatus.OK)
      {
        map.setCenter(results[0].geometry.location);
        if ( markers[0] )
        {
          markers[0].setPosition(results[0].geometry.location);
        } else {
          markers[0] = new google.maps.Marker({map: map, position: results[0].geometry.location, draggable: true, animation: google.maps.Animation.DROP, title: title});
        }
        gmapYXfield("yx",markers[0]);
      } else {
        alert("Geocode was not successful for the following reason: " + status);
      }
    });
  }
  function createMarker()
  {
    if ( !map ) return;
    if ( infowindow) infowindow.close();
    deleteMarker();
    '.gmapMarker('map',true,$gmap_symbol).'
    gmapYXfield("yx",markers[0]);
    google.maps.event.addListener(markers[0], "position_changed", function() { gmapYXfield("yx",markers[0]); });
    google.maps.event.addListener(markers[0], "dragend", function() { map.panTo(markers[0].getPosition()); });
  }
  function deleteMarker()
  {
    if ( infowindow) infowindow.close();
    for(var i=markers.length-1;i>=0;i--)
    {
      markers[i].setMap(null);
    }
    gmapYXfield("yx",null);
    markers=[];
  }
  ';
  include 'qtfm_gmap_load.php';
}

// Symbols
echo '<svg xmlns="http://www.w3.org/2000/svg" style="display:none">'.PHP_EOL;
echo qtSVG('symbol-key').PHP_EOL;
echo qtSVG('symbol-door-open').PHP_EOL;
echo '</svg>'.PHP_EOL;

include 'qtf_inc_ft.php';