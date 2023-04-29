<?php

/* ============
 * map_lib.php
 * ------------
 * version: 4.0 build:20230205
 * This is a module library
 * ------------
 * Class CMapPoint
 * gmapCan gmapHasKey gmapApi QTgempty QTgemptycoord
 * gmapMarker gmapMarkerMapTypeId gmapMarkerIcon
 * QTgetx QTgety QTgetz QTstr2yx QTdd2dms
 * ============ */

class CMapPoint
{
  public $y = 4.352;
  public $x = 50.847;
  public $title = ''; // marker tips
  public $info = '';  // html to display on click
  public $icon = false;
  function __construct($y,$x,$title='',$info='')
  {
    if ( isset($y) && isset($x) )
    {
      $this->y = $y;
      $this->x = $x;
    }
    else
    {
      if ( isset($_SESSION[QT]['m_gmap_gcenter']) )
      {
      $this->y = floatval(QTgety($_SESSION[QT]['m_gmap_gcenter']));
      $this->x = floatval(QTgetx($_SESSION[QT]['m_gmap_gcenter']));
      }
    }
    if ( !empty($title) ) $this->title = $title;
    if ( !empty($info) ) $this->info = $info;
  }
}

// Attention x,y,z MUST be FLOAT (or null)
// If x,y,z are NULL or not float, these functions will returns FALSE.
// When entity (item) is created, the x,y,z are null (i.e. no point, no display)

function gmapCan($section=null,$strRole='')
{
   if ( !gmapHasKey() ) return FALSE;

  // Check

  if ( !isset($section) ) die('gmapCan: arg #1 must be a section ref');
  if ( !is_string($strRole) ) die('gmapCan: arg #2 must be an string');
  if ( $section===-1 ) return FALSE;

  // Added section registery if missing
  if ( !isset($_SESSION[QT]['m_gmap_sections']) ) $_SESSION[QT]['m_gmap_sections']='';

  // check section
  if ( $_SESSION[QT]['m_gmap_sections']!==$section ) return FALSE; // only 'U' is supported in qtf

  // exit
  return TRUE;
}

function gmapHasKey()
{
  return !empty($_SESSION[QT]['m_gmap_gkey']);
}
function gmapApi($strKey='',$strAddLibrary='')
{
  if ( empty($strKey) ) return '';
  return '<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key='.$strKey.'&callback=gmapInitialize"></script>'.PHP_EOL.(empty($strAddLibrary) ? '' : $strAddLibrary);
}
function QTgempty($i)
{
  // Returns true when $i is empty or a value starting with '0.000000'
  if ( empty($i) ) return true;
  if ( !is_string($i) && !is_float($i) && !is_int($i) ) die('QTgempty: Invalid argument #1');
  if ( substr((string)$i,0,8)==='0.000000' ) return true;
  return false;
}
function QTgemptycoord($a)
{
  // Returns true when $a has empty coordinates in both Y and X.
  // $a can be a CMapPoint or CTopic object or a string Y,X. ex: "51.75,4.12"
  // Note: returns true if $a is not correctly formatted or when properties x or y are missing.
  // Note: Z coordinate is NOT evaluated. ex: QTgemptycoord("0,0,125") returns true.

  if ( is_a($a,'CMapPoint') || is_a($a,'CTopic') )
  {
    if ( !property_exists($a,'y') ) return true;
    if ( !property_exists($a,'x') ) return true;
    if ( QTgempty($a->y) && QTgempty($a->x) ) return true;
    return false;
  }
  if ( is_string($a) )
  {
    if ( QTgempty(QTgety($a,true)) && QTgempty(QTgetx($a,true)) ) return true;
    return false;
  }
  die('QTgemptycoord: invalid argument #1');
}
function gmapMarker($centerLatLng='',$draggable=false,$gsymbol=false,$title='',$info='')
{
  if ( $centerLatLng==='' || $centerLatLng==='0,0' ) return 'marker = null;';
  if ( $centerLatLng=='map' )
  {
    $centerLatLng = 'map.getCenter()';
  }
  else
  {
    $centerLatLng = 'new google.maps.LatLng('.$centerLatLng.')';
  }
  if ( $draggable=='1' || $draggable==='true' || $draggable===true )
  {
  	$draggable='draggable:true, animation:google.maps.Animation.DROP,';
  }
  else
  {
  	$draggable='draggable:false,';
  }
  return '	marker = new google.maps.Marker({
		position: '.$centerLatLng.',
		map: map,
		' . $draggable . gmapMarkerIcon($gsymbol) . '
		title: "'.$title.'"
		});
		markers.push(marker); '.PHP_EOL.(empty($info) ? '' : '	gmapInfo(marker,\''.$info.'\');');
}
function gmapMarkerIcon($gsymbol=false)
{
  // returns the google.maps.Marker.icon argument
  if ( empty($gsymbol) ) return ''; // no icon source means that the default symbol is used
  $str = '';
  // icons are 32x32 pixels and the anchor depends on the name: (10,32) for puhspin, (16,32) for point, center form others
  $arr = explode('_',$gsymbol);
  switch($arr[0])
  {
    case 'pushpin':
      $str = 'icon: new google.maps.MarkerImage("qtfm_gmap/'.$gsymbol.'.png",new google.maps.Size(32,32),new google.maps.Point(0,0),new google.maps.Point(10,32)),';
      break;
    case 'point':
     $str = 'icon: new google.maps.MarkerImage("qtfm_gmap/'.$gsymbol.'.png",new google.maps.Size(32,32),new google.maps.Point(0,0),new google.maps.Point(16,32)),';
     break;
    default:
     $str = 'icon: new google.maps.MarkerImage("qtfm_gmap/'.$gsymbol.'.png",new google.maps.Size(32,32),new google.maps.Point(0,0),new google.maps.Point(16,16)),';
     break;
  }
  return $str;
}
function gmapMarkerMapTypeId($gbuttons)
{
  switch((string)$gbuttons)
  {
	case 'S':
	case 'SATELLITE': return 'google.maps.MapTypeId.SATELLITE'; break;
	case 'H':
	case 'HYBRID': return 'google.maps.MapTypeId.HYBRID'; break;
	case 'P':
	case 'T':
	case 'TERRAIN': return 'google.maps.MapTypeId.TERRAIN'; break;
	default: return 'google.maps.MapTypeId.ROADMAP';
  }
}
function QTgetx($str=null,$onerror=0.0)
{
  // checks
  if ( !is_string($str) ) { if ( isset($onerror) ) return $onerror; die('QTgetx: arg #1 must be a string'); }
  if ( !strpos($str,',') ) { { if ( isset($onerror) ) return $onerror; die('QTgetx: arg #1 must be a string with 2 values'); }}
  $arr = explode(',',$str);
  if ( count($arr)<2 ) { if ( isset($onerror) ) return $onerror; die('QTgetx: coordinate must include at least 2 values'); }
  $str = trim($arr[1]);
  if ( !is_numeric($str) ) { if ( isset($onerror) ) return $onerror; die('QTgetx: x-coordinate is not a float'); }
  return (float)$str;
}
function QTgety($str=null,$onerror=0.0)
{
  // checks
  if ( !is_string($str) ) { if ( isset($onerror) ) return $onerror; die('QTgety: arg #1 must be a string'); }
  if ( !strpos($str,',') ) { { if ( isset($onerror) ) return $onerror; die('QTgety: arg #1 must be a string with 2 values'); }}
  $arr = explode(',',$str);
  if ( count($arr)<2 ) { if ( isset($onerror) ) return $onerror; die('QTgety: coordinate must include at least 2 values'); }
  $str = trim($arr[0]);
  if ( !is_numeric($str) ) { if ( isset($onerror) ) return $onerror; die('QTgety: y-coordinate is not a float'); }
  return (float)$str;
}
function QTgetz($str=null,$onerror=0.0)
{
  // checks
  if ( !is_string($str) ) { if ( isset($onerror) ) return $onerror; die('QTgetz: arg #1 must be a string'); }
  if ( !strpos($str,',') ) { { if ( isset($onerror) ) return $onerror; die('QTgetz: arg #1 must be a string with at least 3 values'); }}
  $arr = explode(',',$str);
  if ( count($arr)<3 ) { if ( isset($onerror) ) return $onerror; die('QTgetz: coordinate must include at least 3 values'); }
  $str = trim($arr[2]);
  if ( !is_numeric($str) ) { if ( isset($onerror) ) return $onerror; die('QTgetz: z-coordinate is not a float'); }
  return (float)$str;
}
function QTstr2yx($str)
{
  // check

  if ( !is_string($str) ) die('QTstr2dd: arg #1 must be a string');
  $str = trim($str);
  $str = str_replace('+','',$str);
  $str = str_replace(';',',',$str);
  $arr = explode(',',$str);
  if ( count($arr)!=2 ) return false;

  // analyse each values

  foreach($arr as $intKey=>$str)
  {
    $str = trim(strtoupper($str));
    if ( substr($str,0,1)==='N' || substr($str,0,1)==='E' ) $str = substr($str,1);
    if ( substr($str,0,1)==='S' || substr($str,0,1)==='W' ) $str = '-'.substr($str,1);
    if ( substr($str,-1,1)==='N' || substr($str,-1,1)==='E' ) $str = trim(substr($str,0,-1));
    if ( substr($str,-1,1)==='S' || substr($str,-1,1)==='W' ) $str = '-'.trim(substr($str,0,-1));
    $str = str_replace('--','-',$str);

    // convert dms to dd
    if ( strpos($str,'D') || strpos($str,'?') || strpos($str,"'") || strpos($str,'"') || strpos($str,'?') )
    {
      $str = str_replace(array('SEC','S',"''",'??','"'),'/',$str);
      $str = str_replace(array('MIN','M',"'",'?'),'/',$str);
      $str = str_replace(array('DEG','D','?',':'),'/',$str);
      if ( substr($str,-1,1)==='/' ) $str = substr($str,0,-1);
      $arrValues = explode('/',$str);
      $intD = intval($arrValues[0]); if ( !qtIsBetween($intD,($intKey==0 ? -90 : -180),($intKey==0 ? 90 : 180)) ) return false;
      $intM = 0;
      $intS = 0;
      if ( isset($arrValues[1]) ) { $intM = intval($arrValues[1]); if ( !qtIsBetween($intM,0,59) ) return false; }
      if ( isset($arrValues[2]) ) { $intS = intval($arrValues[2]); if ( !qtIsBetween($intS,0,59) ) return false; }
      $str = $intD+($intM/60)+($intS/3600);
    }

    if ( !qtIsBetween(intval($str),($intKey==0 ? -90 : -180),($intKey==0 ? 90 : 180)) ) return false;
    $arr[$intKey]=$str;
  }

  // returns 2 dd in a string

  return $arr[0].','.$arr[1];
}
function QTdd2dms($dd,$intDec=0)
{
  $dms_d = intval($dd);
  $dd_m = abs($dd - $dms_d);
  $dms_m_float = 60 * $dd_m;
  $dms_m = intval($dms_m_float);
  $dd_s = abs($dms_m_float - $dms_m);
  $dms_s = 60 * $dd_s;
  return $dms_d.'&#176;'.$dms_m.'&#039;'.round($dms_s,$intDec).'&quot;';
}