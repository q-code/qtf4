<?php // v4.0 build:20240210

// SERVEUR SCRIPT
// Perform async queries on request from web pages (ex: using ajax) with GET method
// Ouput (echo) results as string or json string object {rItem,rInfo}

if ( empty($_GET['q']) || !in_array($_GET['q'],['T','R','attach','unreplied']) ) { echo json_encode(array(array('rItem'=>'','rInfo'=>'configuration error'))); return; }

// INITIALIZE

include '../config/config_db.php'; if ( strpos(QDB_SYSTEM,'sqlite') ) define ('QDB_SQLITEPATH', '../');
define( 'QT', 'qtf'.(defined('QDB_INSTALL') ? substr(QDB_INSTALL,-1) : '') );
include 'class/class.qt.db.php';
// Using constants
const TABTOPIC = QDB_PREFIX.'qtatopic';
const TABPOST = QDB_PREFIX.'qtapost';
const TABTABLES = ['TABTOPIC','TABPOST'];
// --- allows app impersonation [qt f|i ] here after

// FUNCTIONS
function addDate(string $d='', int $i=-1, string $str='year')
{
  if ( empty($d) ) die('addDate: Argument #1 must be a string');
  $intY = (int)substr($d,0,4);
  $intM = (int)substr($d,4,2);
  $intD = (int)substr($d,6,2);
  switch($str)
  {
    case 'year': $intY += $i; break;
    case 'month': $intM += $i; break;
    case 'day': $intD += $i; break;
  }
  if ( in_array($intM,array(1,3,5,7,8,10,12)) && $intD>31 ) { $intM++; $intD -= 31; }
  if ( in_array($intM,array(4,6,9,11)) && $intD>30 ) { $intM++; $intD -= 30; }
  if ( $intD<1 ) { $intM--; $intD += 30; }
  if ( $intM>12 ) { $intY++; $intM -= 12; }
  if ( $intM<1 ) { $intY--; $intM += 12; }
  if ( $intM==2 && $intD>28 ) { $intM++; $intD -= 28; }
  return (string)($intY*10000+$intM*100+$intD).(strlen($d)>8 ? substr($d,8) : '');
}
function qtCtype_digit($str) {
  // Servers may have ctype disabled. Use qtCtype_digit instead
  if ( function_exists('ctype_digit') ) return ctype_digit($str);
  if ( is_string($str) && $str!=='' && preg_match('/^[0-9]+$/',$str) ) return true;
  return false;
}
/**
 * Returns a sql date condition seclecting a timeframe
 * @param string $dbtype database type
 * @param string $ti timeframe {y|m|w|1..12|YYYY|YYYYMM|old}
 * @param string $prefix AND
 * @param string $field
 * @return string
 */
function getSqlTimeframe($dbtype, $ti='', $prefix=' AND ', $field='t.firstpostdate') {
  if ( empty($ti) ) return ''; // no timeframe
  if ( !is_string($dbtype) || !is_string($ti) || !is_string($prefix) || !is_string($prefix) || empty($field) ) die('getSqlTimeframe: requires string arguments');
  // $ti can be {y|m|w|1..12|YYYY|YYYYMM|old}
  // i.e. this year, this month, last week, previous month#, a specific year YYYY, a specific yearmonth YYYYMM, 2 years or more
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
      $strDate = (string)date('Ymd', strtotime('-8 day', strtotime(date('Ymd'))));
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
  switch($dbtype) {
    case 'pdo.pg':
    case 'pg': return $prefix . "SUBSTRING($field FROM 1 FOR $len) $operator '$strDate'"; break;
    case 'pdo.sqlite':
    case 'sqlite':
    case 'pdo.oci':
    case 'oci': return $prefix . "SUBSTR($field,1,$len) $operator '$strDate'"; break;
    default: return $prefix . "LEFT($field,$len) $operator '$strDate'";
  }
}

// SERVICE ARGUMENTS {T|R|attach|unreplied} topics, replies, attachments or unreplied

$q = $_GET['q'];

// errors
$L = []; include '../language/'.(isset($_GET['lang']) ? $_GET['lang'] : 'en').'/app_error.php';
if ( empty($L['No_result']) )           $L['No_result'] = 'No result';
if ( empty($L['Try_other_lettres']) )   $L['Try_other_lettres'] = 'Try other lettres';
if ( empty($L['Try_without_options']) ) $L['Try_without_options'] = 'Try without options';
if ( empty($L['E_failed']) )            $L['E_failed'] = 'Action failed';

// options
$s = isset($_GET['s']) ? (int)$_GET['s'] : -1; // section [int]
$ft = isset($_GET['ft']) ? $_GET['ft'] : ''; // item type {A|T|...} or user type {A|M|U}
$fs = isset($_GET['fs']) ? $_GET['fs'] : ''; // status {0|1}, 1=closed
$y = isset($_GET['y']) ? $_GET['y'] : ''; // year
$ti = isset($_GET['ti']) ? $_GET['ti'] : ''; // timeframe
$ids = isset($_GET['ids']) ? $_GET['ids'] : ''; // list of topic id

// defaults (1 char to avail injection)
if ( strlen($ft)>1 || empty($ft) ) $ft = '';
if ( strlen($fs)>1 || $fs==='-1' ) $fs = '';
if ( empty($y) || !qtCtype_digit($y) ) $y = ''; // if not a year, use '*' (note: case tag-y uses current year)

// INITIALIZE

$oDB = new CDatabase();

// General Where options (for topics)
$where = 't.id>=0';
if ( $s>=0 ) $where .= " AND t.forum=$s";
if ( $ft!=='' ) $where .= " AND t.type='$ft'";
if ( $fs!=='' ) $where .= " AND t.status='$fs'"; // '1'=closed
if ( !empty($ids) ) $where .= " AND t.id IN ($ids)";

// PROCESSES

switch($q) {
  case 'T': // topics
    if ( $ti!=='' ) $where .= getSqlTimeframe($oDB->type, $ti);
    echo $oDB->count( TABTOPIC." t WHERE $where" );
    return;
    break;
  case 'R': // replies
    if ( $ti!=='' ) $where .= getSqlTimeframe($oDB->type, $ti);
    echo $oDB->count( TABPOST." p INNER JOIN TABTOPIC t ON p.topic=t.id WHERE p.type<>'P' AND $where" );
    return;
    break;
  case 'attach': // replies
    if ( $ti!=='' ) $where .= getSqlTimeframe($oDB->type, $ti);
    echo $oDB->count( TABPOST." p INNER JOIN TABTOPIC t ON p.topic=t.id WHERE p.attach<>'' AND $where" );
    return;
    break;
  case 'unreplied': // topics (status must be already "0"=opened)
    $d = isset($_GET['d']) ? $_GET['d'] : '10'; // days (use for Prune)
    $d = addDate(date('Ymd His'),-$d,'day');
    echo $oDB->count( TABTOPIC." t WHERE $where AND t.replies=0 AND t.firstpostdate<'$d'" );
    return;
    break;
  default: // posts
    echo json_encode(array(array('rItem'=>'','rInfo'=>'invalid argument [q]')));
    return;
}

// RESPONSE FAILED

echo json_encode( array(array('rItem'=>'', 'rInfo'=>$L['No_result'].', '.($s.$ft.$fs==='-1' ? $L['Try_other_lettres'] : $L['Try_without_options']))) );