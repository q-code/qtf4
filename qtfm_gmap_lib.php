<?php

/* ============
 * map_lib.php
 * ------
 * version: 4.0 build:20240210
 * This is a module library
 * ------
 * Class CMapPoint
 * gmapCan gmapHasKey gmapApi gmapEmpty gmapEmptycoord
 * gmapMarker gmapMarkerMapTypeId gmapMarkerPin
 * QTgetx QTgety QTgetz QTstr2yx QTdd2dms
 * ============ */

class CMapPoint
{
  public $y = 4.352;
  public $x = 50.847;
  public $title = '';  // marker tips
  public $info = '';   // html to display on click
  public $marker = ''; // default marker
  function __construct($y, $x, string $title='', string $info='', string $marker='')
  {
    if ( isset($y) && isset($x) ) {
      $this->y = (float)$y;
      $this->x = (float)$x;
    } else {
      if ( isset($_SESSION[QT]['m_gmap_gcenter']) ) {
        $this->y = (float)QTgety($_SESSION[QT]['m_gmap_gcenter']);
        $this->x = (float)QTgetx($_SESSION[QT]['m_gmap_gcenter']);
      }
    }
    $this->title = empty($title) ? '' : $title;
    $this->info = empty($info) ? '' : $info;
    $this->marker = empty($marker) || $marker==='default' ? '' : $marker;
  }
}

// Attention x,y,z MUST be FLOAT (or null)
// If x,y,z are NULL or not float, these functions will returns FALSE.
// When entity (item) is created, the x,y,z are null (i.e. no point, no display)

function gmapCan($section=null)
{
  if ( !gmapHasKey() || $section===-1 ) return false;
  if ( !isset($section) ) die('gmapCan: arg #1 must be a section ref');
  if ( !isset($_SESSION[QT]['m_gmap_sections']) ) $_SESSION[QT]['m_gmap_sections'] = '';
  // check section
  if ( $_SESSION[QT]['m_gmap_sections']!==$section ) return false; // only 'U' is supported in qtf
  // exit
  return true;
}

function gmapHasKey()
{
  return !empty($_SESSION[QT]['m_gmap_gkey']);
}
function gmapApi(string $key='',string $addLibrary='')
{
  if ( empty($key) ) return '';
  return '(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({ key: "'.$key.'", v: "weekly"});'.PHP_EOL.$addLibrary.PHP_EOL.'gmapInitialize();';
}
function gmapOption(string $key='mt', string $alt='')
{
  return qtExplodeGet($_SESSION[QT]['m_gmap_options'], $key, $alt);
}
function gmapEmpty($i)
{
  // Returns true when $i is empty or a value starting with '0.000000'
  if ( empty($i) ) return true;
  if ( !is_string($i) && !is_float($i) && !is_int($i) ) die('gmapEmpty: Invalid argument #1');
  if ( substr((string)$i,0,8)==='0.000000' ) return true;
  return false;
}
function gmapEmptycoord($a)
{
  // Returns true when $a has empty coordinates in both Y and X.
  // $a can be a CMapPoint or CTopic object or a string Y,X. ex: "51.75,4.12"
  // Note: returns true if $a is not correctly formatted or when properties x or y are missing.
  // Note: Z coordinate is NOT evaluated. ex: gmapEmptycoord("0,0,125") returns true.

  if ( is_a($a,'CMapPoint') || is_a($a,'CTopic') ) {
    if ( !property_exists($a,'y') ) return true;
    if ( !property_exists($a,'x') ) return true;
    if ( gmapEmpty($a->y) && gmapEmpty($a->x) ) return true;
    return false;
  }
  if ( is_string($a) ) {
    if ( gmapEmpty(QTgety($a,true)) && gmapEmpty(QTgetx($a,true)) ) return true;
    return false;
  }
  die('gmapEmptycoord: invalid argument #1');
}
function gmapMarker(string $centerLatLng='', bool $draggable=false, string $marker='', string $title='', string $info='')
{
  if ( $centerLatLng==='' || $centerLatLng==='0,0' ) return 'marker = null;';
  return gmapMarkerPin($marker).PHP_EOL.'marker = new google.maps.marker.AdvancedMarkerElement({
  position: '.($centerLatLng==='map' ? 'gmap.getCenter()' : 'new google.maps.LatLng('.$centerLatLng.')').',
  map: gmap,
  gmpDraggable: '.($draggable ? 'true' : 'false').',
  content: gmapPin,
  title: "'.$title.'"
  });'.PHP_EOL.'markers.push(marker);'.PHP_EOL.(empty($info) ? '' : 'gmapInfo(marker,`'.$info.'`);');
}
function gmapMarkerPin(string $marker='')
{
  if ( empty($marker) || $marker==='0.png' ) return 'gmapPin = null;';
  if ( file_exists(APP.'m_gmap/'.$marker) ) return 'gmapPin = document.createElement("img"); gmapPin.src = "'.APP.'m_gmap/'.$marker.'";';
  // svg in a glyph
  // if ( file_exists(APP.'m_gmap/'.$marker.'.svg') ) return 'gmapPin = document.createElement("img"); gmapPin.src = "'.APP.'m_gmap/'.$marker.'.svg"; gmapPin = new PinElement({glyph:gmapPin}); gmapPin = gmapPin.element;';
  return 'gmapPin = null;';
}
function gmapMarkerMapTypeId($maptype)
{
  switch((string)$maptype) {
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