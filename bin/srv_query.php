<?php // v4.0 build:20240210

// SERVEUR SCRIPT
// Perform async queries on request from web pages (ex: using ajax) with GET method
// Ouput (echo) results as string or json string object {rItem,rInfo}

/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */

// INITIALIZE

include '../config/config_db.php'; if ( strpos(QDB_SYSTEM,'sqlite') ) define ('QDB_SQLITEPATH', '../');
define( 'QT', 'qtf'.(defined('QDB_INSTALL') ? substr(QDB_INSTALL,-1) : '') );
include 'class/class.qt.db.php';
// Using constants
const TABSECTION = QDB_PREFIX.'qtaforum';
const TABUSER = QDB_PREFIX.'qtauser';
const TABTOPIC = QDB_PREFIX.'qtatopic';
const TABPOST = QDB_PREFIX.'qtapost';
const TABTABLES = ['TABTOPIC','TABPOST','TABSECTION','TABUSER'];
// --- allows app impersonation [qt f|i ] here after

// FUNCTIONS

function qtCtype_digit($str) {
  // Servers may have ctype disabled. Use qtCtype_digit instead
  if ( function_exists('ctype_digit') ) return ctype_digit($str);
  if ( is_string($str) && $str!=='' && preg_match('/^[0-9]+$/',$str) ) return true;
  return false;
}
/**
 * Returns a sql date condition seclecting a timeframe
 * @param string $dbtype database type
 * @param string $ti timeframe {y|m|w|1..12|YYYY|YYYYMM|*}
 * @param string $prefix AND
 * @param string $field
 * @return string
 */
function getSqlTimeframe($dbtype, $ti='', $prefix=' AND ', $field='t.firstpostdate') {
  if ( empty($ti) ) return ''; // no timeframe
  if ( !is_string($dbtype) || !is_string($ti) || !is_string($prefix) || !is_string($prefix) || empty($field) ) die('getSqlTimeframe: requires string arguments');
  // $ti can be {y|m|w|1..12|YYYY|YYYYMM|old} i.e. this year, this month, last week, previous month#, a specific year YYYY, a specific yearmonth YYYYMM
  $operator = '=';
  switch($ti) {
    case 'y':	// this year
      $strDate = date('Y');
      break;
    case 'm': // this month
      $strDate = date('Ym');
      break;
    case 'w':	// last week
      $operator = '>';
      $strDate = (string)date('Ymd', strtotime("-8 day", strtotime(date('Ymd'))));
      break;
    case 'old': // 2 year or more
      $operator = '<=';
      $strDate = (int)date('Y')-2;
      break;
    default: // $ti is the month number or a specific datemonth
      if ( !qtCtype_digit($ti) ) die('getSqlTimeframe: invalid tf argument');
      switch(strlen($ti)) {
        case 1:
        case 2:
          $intMonth = (int)$ti;
          $intYear = (int)date('Y'); if ( $intMonth>date('n') ) --$intYear; // check if month from previous year
          $strDate = (string)($intYear*100+$intMonth);
          break;
        case 4:
          $strDate = $ti;
          break;
        case 6:
          $strDate = $ti;
          break;
        default: die('getSqlTimeframe: invalid tf argument');
      }
  }
  $len = strlen($strDate);
  switch($dbtype)
  {
    case 'pdo.pg':
    case 'pg': return $prefix . "SUBSTRING($field FROM 1 FOR $len) $operator '$strDate'"; break;
    case 'pdo.sqlite':
    case 'sqlite':
    case 'pdo.oci':
    case 'oci': return $prefix . "SUBSTR($field,1,$len) $operator '$strDate'"; break;
    default: return $prefix . "LEFT($field,$len) $operator '$strDate'";
  }
}
function getSimpleSVG(string $id='info', bool $addClass=true) {
  if ( !file_exists('svg/'.$id.'.svg') ) return '#';
  // svg is inserted directly, or inside a span when attributes are added. This allows svg inherit style (fontsize/color...)
  $svg = file_get_contents('svg/'.$id.'.svg');
  if ( $addClass) $svg = '<svg class="svg-'.$id.'" '.substr($svg,4);
  return $svg;
}

// SERVICE ARGUMENTS

if ( empty($_GET['fv']) ) { echo json_encode(array(array('rItem'=>'','rInfo'=>'configuration error'))); return; }
$fv = CDatabase::sqlEncode(strtoupper($_GET['fv'])); // searched element (uppercase to be case insensitive)
$q = isset($_GET['q']) ? $_GET['q'] : 's'; // search type {s|qkw|tag|username|userexists}

// errors
$L = []; include '../language/'.(isset($_GET['lang']) ? $_GET['lang'] : 'en').'/app_error.php';
$e0 = empty($L['No_result'])             ? 'No result'           : $L['No_result'];
$e1 = empty($L['E_try_other_lettres'])   ? 'Try other lettres'   : $L['E_try_other_lettres'];
$e2 = empty($L['E_try_without_options']) ? 'Try without options' : $L['E_try_without_options'];
$e4 = empty($L['E_failed'])              ? 'Action failed'       : $L['E_failed'];

if ( substr($fv,0,1)==='*' ) { echo $e4,'|',$e1.PHP_EOL; return; }
// options
$s = isset($_GET['s']) ? (int)$_GET['s'] : -1; // section [int]
$ft = isset($_GET['ft']) ? $_GET['ft'] : ''; // item type {A|T|...} or user type {A|M|U}
$fs = isset($_GET['fs']) ? $_GET['fs'] : ''; // status {0|1}, 1=closed
$y = isset($_GET['y']) ? $_GET['y'] : ''; // year
$ti = isset($_GET['ti']) ? $_GET['ti'] : ''; // timeframe
// defaults (1 char to avail injection)
if ( strlen($ft)>1 || empty($ft) ) $ft = '';
if ( strlen($fs)>1 || empty($fs) || $fs==='-1' ) $fs = '';
$to = empty($_GET['to']) || $_GET['to']==='false' ? 0 : 1; // 1=in title only
if ( empty($y) || !qtCtype_digit($y) ) $y = ''; // if not a year, use '' (note: case tag-y uses current year)

$oDB = new CDatabase();
$arrDistinct = [];
$arr = []; // results

// General Where options (for topics)
$where = 't.id>=0';
if ( $s>=0 ) $where .= " AND t.forum=$s";
if ( $ft!=='' ) $where .= " AND t.type='$ft'";
if ( $fs!=='' ) $where .= " AND t.status='$fs'"; // '1'=closed

// PROCESSES
switch($q) {

case 'behalf':
case 'user':
case 'userm':
case 'username':
  $where = 'id>0';
  if ( $ft==='A' ) $where = "role='A'";
  if ( $ft==='M' ) $where = "(role='A' OR role='M')";
  $e2=$e1; //on no result forces 'try other lettres'
  $oDB->query( "SELECT id,name,role FROM TABUSER WHERE $where AND UPPER(name) LIKE ?", ['%'.$fv.'%'] );
  while($row=$oDB->getRow())
  {
    $id = (int)$row['id'];
    $arr[$id] = array('rId'=>$id,'rSelect'=>$row['name'],'rItem'=>$row['name'],'rInfo'=>'('.(isset($L['Role_'.$row['role']]) ? $L['Role_'.$row['role']] : 'role '.$row['role']).')');
    if ( count($arr)>=10 ) break;
  }
  break;
case 'ref':
case 'qkw':
  $bRef=false;
  if ( qtCtype_digit($fv) )
  {
    $where .= ' AND s.numfield<>"N" AND p.type="P" AND t.numid=:v';
    $bRef=true;
  }
  else
  {
    switch($oDB->type)
    {
    case 'pdo.sqlsrv':
    case 'sqlsrv': $where .= ' AND (UPPER(p.title) LIKE :v' . ( $to==1 ? ')' : ' OR UPPER(CAST(p.textmsg AS VARCHAR(2000))) LIKE :v)' ); break;
    default: $where .= ' AND (UPPER(p.title) LIKE :v' . ( $to==1 ? ')' : ' OR UPPER(p.textmsg) LIKE :v)' ); break;
    }
  }
  $oDB->query(
    "SELECT t.id,t.numid,t.type,p.title,p.textmsg,s.numfield,p.type as posttype FROM TABTOPIC t INNER JOIN TABPOST p ON p.topic=t.id INNER JOIN TABSECTION s ON s.id=t.forum WHERE $where",
      [':v'=>$bRef ? (int)$fv : '%'.$fv.'%']
    );
  while($row=$oDB->getRow())
  {
    $ref = empty($row['numfield']) || $row['numfield']=='N' ? '' : sprintf($row['numfield'],$row['numid']);
    $id = (int)$row['id'];
    $image = 'envelope';
    if ( $row['posttype']==='R' ) $image = 'comment-dots';
    if ( $row['type']==='I' ) $image = 'check';
    if ( $row['type']==='A' ) $image = 'thumbtack';
    if ( $bRef )
    {
      if ( empty($row['title']) ) $row['title']=substr($row['textmsg'],0,30);
      if ( !isset($arr[$id]) )
        $arr[$id] = array(
        'rItem'=>getSimpleSVG($image).' '.$ref,
        'rInfo'=>substr($row['title'],0,25).(isset($row['title'][25]) ? '&hellip;' : ''),
        'rSelect'=>'#'.$id
        );
    }
    else
    {
      if ( stripos($row['title'],$fv) !== false ) $row['textmsg'] = $row['title']; // when title contains the term, use title instead of textmsg
      $n = stripos($row['textmsg'],$fv);
      if ( $n<0) continue;
      if ( $n>10) { $n-=10; } else { $n=0; }
      $strArg = substr($row['textmsg'],$n,25);
      if ( $n>0 ) $strArg = '&hellip;'.$strArg;
      if ( isset($row['textmsg'][$n+25]) ) $strArg .='&hellip;';
      if ( !isset($arr[$id]) )
        $arr[$id] = array(
        'rItem'=>getSimpleSVG($image).' '.$ref,
        'rInfo'=>$strArg,
        'rSelect'=>'#'.$id
        );
    }
    if ( count($arr)>8 ) break;
  }
  break;

case 'tag-y':
  // in stat page year is required, force this year when year not specified (or previous year when still in january)
  if ( strlen($y)!==4 ) { $y = (int)date('Y'); if ( (int)date('n')<2 ) $y--; $y=(string)$y; }
  // no break, continue with case 'tag'

case 'tag-edit':
  // search in predefined tags
  if ( empty($_GET['dir']) ) break;
  if ( empty($_GET['lang']) ) $_GET['lang']='en';
  require 'lib_qt_tags.php';
  // search matching in section tags
  if ( $s>=0 ) {
    $arrTags = readTagsFile('../'.$_GET['dir'].'tags_'.$_GET['lang'].'_'.$s.'.csv');
    foreach($arrTags as $str=>$strDesc) {
      if ( stripos($str, $_GET['fv'])!==false ) $arrDistinct[$str] = substr($strDesc,0,64);
      if ( count($arrDistinct)>10 ) break;
    }
  }
  // search matching in common tags
  if ( count($arrDistinct)<10 ) {
    $arrTags = readTagsFile('../'.$_GET['dir'].'tags_'.$_GET['lang'].'.csv');
    foreach($arrTags as $str=>$strDesc) {
      if ( stripos($str, $_GET['fv'])!==false ) $arrDistinct[$str] = substr($strDesc,0,64);
      if ( count($arrDistinct)>10 ) break;
    }
  }
  // search in used tags
  if ( count($arrDistinct)<10 ) {
    $where .= getSqlTimeframe($oDB->type, $ti);
    $arrDistinctKey = array_map('mb_strtolower', array_keys($arrDistinct));
    // search in used tags
    $oDB->query( "SELECT t.tags,count(t.id) as countid FROM TABTOPIC t WHERE $where AND UPPER(t.tags) LIKE ?", ['%'.$fv.'%'] );
    while($row=$oDB->getRow()) {
      $arrTags=explode(';',$row['tags']);
      foreach($arrTags as $str) {
        if ( stripos($str, $fv)!==false && !in_array(mb_strtolower($str), $arrDistinctKey) ) $arrDistinct[$str] = '('.$row['countid'].')';
        if ( count($arrDistinct)>8 ) break;
      }
    }
  }
  // responses
  foreach($arrDistinct as $key=>$str) $arr[]=array('rItem'=>$key,'rInfo'=>$str);
  break;

case 'userexists':
  $where = '';
  if ( $ft=='A' ) $where = "role='A' AND";
  if ( $ft=='M' ) $where = "(role='A' OR role='M') AND";
  echo $oDB->count( TABUSER." WHERE $where name=?", [CDatabase::sqlEncode($_GET['fv'])] )!==0 ? 'true' : 'false'; // case sensitive: use $_GET['fv'] instead of $fv
  return;
  break;

case 'kw':
  switch($oDB->type) {
  case 'pdo.sqlsrv':
  case 'sqlsrv': $where .= ' AND (UPPER(p.title) LIKE :v' . ( $to==1 ? ')' : ' OR UPPER(CAST(p.textmsg AS VARCHAR(2000))) LIKE :v)' ); break;
  default:      $where .= ' AND (UPPER(p.title) LIKE :v' . ( $to==1 ? ')' : ' OR UPPER(p.textmsg) LIKE :v)' ); break;
  }
  $oDB->query(
    "SELECT t.id,t.type,p.title,p.textmsg,p.type as posttype FROM TABTOPIC t INNER JOIN TABPOST p ON p.topic=t.id WHERE $where",
      [':v'=>'%'.$fv.'%']
    );
  while($row=$oDB->getRow()) {
    $id = (int)$row['id'];
    $image = 'envelope';
    if ( $row['posttype']==='R' ) $image = 'comment-dots';
    if ( $row['type']==='I' ) $image = 'check';
    if ( $row['type']==='A' ) $image = 'thumbtack';
    if ( stripos($row['title'],$fv) !== false ) $row['textmsg'] = $row['title']; // when title contains the term, use title instead of textmsg
    $n = stripos($row['textmsg'],$fv);
    if ( $n<0 ) continue;
    if ( $n>10 ) { $n-=10; } else { $n=0; }
    // substring of result
    $row['textmsg'] = str_replace("\r\n"," ",substr($row['textmsg'],$n,25));
    $str = ($n>0 ? '&hellip;' : '').$row['textmsg'].(isset($row['textmsg'][24]) ?  '&hellip;' : '');
    if ( !isset($arr[$id]) )
      $arr[$id] = array(
      'rItem'=>getSimpleSVG($image).' '.$str,
      'rInfo'=>$row['title'],
      'rSelect'=>$row['textmsg']
      );
    if ( count($arr)>8 ) break;
  }
  break;

default: // posts
  echo json_encode(array(array('rItem'=>'','rInfo'=>'unkown query type '.$q)));
}

// RESPONSE
if ( count($arr)==0 )
{
  echo json_encode( array(array('rItem'=>'', 'rInfo'=>$e0.', '.($s.$ft.$fs==='-1' ? $e1 : $e2))) );
}
else
{
  echo json_encode( array_values($arr) );
}