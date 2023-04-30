<?php // v4.0 build:20230430 allows app impersonation [qt f|i|e ]

session_start();
/**
* @var CHtml $oH
* @var CDatabase $oDB
*/
require 'bin/init.php';
if ( SUser::role()!=='A' ) die('Access denied');
$oH->selfurl = APP.'_adm_const.php';
$oH->selfname ='PHP constants';

function constToString($str)
{
  if ( is_string($str) ) return htmlentities($str);
  if ( is_bool($str) ) return $str ? 'TRUE' : 'FALSE';
  if ( is_array($str) ) return 'array of '.count($str).' values';
  if ( is_null($str) ) return '(null)';
  return $str;
}

// HTML start

include translate('lg_adm.php');
include APP.'_adm_inc_hd.php';

// Constants
$arr = get_defined_constants(true); if ( isset($arr['user']) ) $arr = $arr['user']; // userdefined constants
echo '<p class="article">Here are the major constants. To have a full list of constants see the file /config/config_cst.php.</p>'.PHP_EOL;
echo '<table class="t-conf const">'.PHP_EOL;
foreach($arr as $k=>$v)
{
  if ( $k==='QT_HASHKEY' ) continue;
  if ( substr($k,0,3)==='QT_' ) echo '<tr><th>'.$k.'</th><td>'.constToString($v).'</td></tr>'.PHP_EOL;
}
echo '</table>'.PHP_EOL;

// Config
echo '<p class="article">Here are the database connection parameters (except passwords)</p>'.PHP_EOL;
echo '<table class="t-conf const">'.PHP_EOL;
foreach(['QDB_SYSTEM','QDB_HOST','QDB_DATABASE','QDB_PREFIX','QDB_USER','QDB_PWD','QDB_USER2','QDB_PWD2','QDB_INSTALL'] as $k)
{
  if ( !defined($k) ) continue;
  echo '<tr><th>'.$k.'</th><td>'.($k==='QDB_PWD' || $k==='QDB_PWD2' ? '&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;' : constant($k)).'</td></tr>'.PHP_EOL;
}
echo '</table>'.PHP_EOL;

include APP.'_adm_inc_ft.php';