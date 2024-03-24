<?php // v4.0 build:20240210

// SERVEUR SCRIPT
// Perform async queries on request from web pages (ex: using ajax) with GET method
// Ouput (echo) results as string or a json array of objects [{...},{...}]

// INITIALIZE

include '../config/config_db.php'; if ( strpos(QDB_SYSTEM,'sqlite') ) define ('QDB_SQLITEPATH', '../');
define( 'QT', 'qtf'.(defined('QDB_INSTALL') ? substr(QDB_INSTALL,-1) : '') );
include 'class/class.qt.db.php';
include 'lib_qtf_base.php';
function qtDirData(string $root='', int $id=0, bool $check=false)
{
  // Get directory/subdirectory for Id (with final /).
  $i1 = $id>0 ? floor($id/1000) : 0;
  $i2 = $id-($i1*1000);
  $i2 = $i2>0 ? floor($i2/100) : 0;
  $path = $root.$i1.'000/'.$i1.$i2.'00';
  if ( !$check ) return $path.'/';
  return is_dir($path) ? $path.'/' : ''; // returns '' if directory not existing
}
function getUserImg(string $root='', int $id=0, string $altSrc='bin/css/user.gif'){
  // NOSQL, uses file_exists(). Returns '' when image not found *and* $altSrc=''
  $path = qtDirData($root,$id,true); if ( empty($path) ) return empty($altSrc) ? '' : '<img id="userimg" src="'.$altSrc.'" alt="user0"/>';
  $src = '';
  foreach(['.jpg','.jpeg','.png','.gif'] as $mime) if ( file_exists($path.$id.$mime) ) { $src = $path.$id.$mime; break; }
  if ( empty($src) ) $src = $altSrc;
  return '<img id="userimg" src="'.str_replace('../', '', $src).'" alt="user"/>';
}
function getSimpleSVG(string $id='info', bool $addClass=true) {
  if ( !file_exists('svg/'.$id.'.svg') ) return '#';
  // svg is inserted directly, or inside a span when attributes are added. This allows svg inherit style (fontsize/color...)
  $svg = file_get_contents('svg/'.$id.'.svg');
  if ( $addClass) $svg = '<svg class="svg-'.$id.'" '.substr($svg,4);
  return $svg;
}

// Using constants
const TABUSER = QDB_PREFIX.'qtauser';
const TABTABLES = ['TABUSER'];

// SERVICE ARGUMENTS
$L = []; include '../language/'.(isset($_GET['lang']) ? $_GET['lang'] : 'en').'/app_error.php';
$e0 = empty($L['No_result']) ? 'No result' : $L['No_result'];
$q = isset($_GET['q']) ? $_GET['q'] : 'u';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0; // 0 visitor
$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$alt = isset($_GET['alt']) ? $_GET['alt'] : 'bin/css/user.gif';
$oDB = new CDatabase();

// PROCESSES
$data = '';
switch($q)
{
  case 'u':
    $oDB->query( "SELECT u.name,u.role,u.location,birthday FROM TABUSER u WHERE u.id=$id" );
    $row = $oDB->getRow();
    //output the response
    $data .= '<p class="small">'.substr($row['birthday'],0,4).'-'.substr($row['birthday'],4,2).'-'.substr($row['birthday'],6,2).'</p>';
    $data .= getUserImg('../'.$dir, $id, $alt); // output to page without "../"
    $data .= '<p class="ellipsis">'.$row['name'].'</p>';
    switch($row['role']) {
      case 'A':  $data .= '<p><span data-role="A" onmouseover="titleRole(this);">'.getSimpleSVG('user-a').'</span></p>'; break;
      case 'M':  $data .= '<p><span data-role="M" onmouseover="titleRole(this);">'.getSimpleSVG('user-m').'</span></p>'; break;
    }
    if ( !empty($row['location']) ) $data .= '<p class="ellipsis small">('.$row['location'].')</p>';
    break;
  default:
    echo 'invalid argument';
    break;
}

// RESPONSE

echo empty($data) ? $e0 : $data;