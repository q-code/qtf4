<?php // v4.0 build:20240210

// SERVEUR SCRIPT srv_tagupdate.php
// Perform async queries on request from web pages (ex: using ajax) with GET method
// Ouput (echo) results as string

session_start(); // uses session_id() for update security reason
include '../config/config_db.php';
if ( strpos(QDB_SYSTEM,'sqlite') ) define ('QDB_SQLITEPATH', '../');
define( 'QT', 'qtf'.(defined('QDB_INSTALL') ? substr(QDB_INSTALL,-1) : '') );

try {

  if ( !isset($_GET['ref']) || $_GET['ref']!== MD5(QT.session_id()) ) throw new Exception('Unable to save tags');
  if ( !isset($_GET['id']) ) throw new Exception('Unable to save tags');
  if ( !isset($_GET['tag']) ) throw new Exception('Unable to save tags');
  if ( substr($_GET['tag'],-1,1)===';' ) $_GET['tag'] = substr($_GET['tag'],0,-1);
  if ( !isset($_GET['max']) ) $_GET['max'] = 0;
  if ( $_GET['max']>0 && substr_count($_GET['tag'],';')>=$_GET['max'] ) throw new Exception('Too many tags. Last tag is not saved.');

  include 'class/class.qt.db.php';
  function srvDropDiacritics(string $str) {
    $tl = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', Transliterator::FORWARD);
    $res = $tl->transliterate($str);
    return $res===false ? $str : $res;
  }

  // format input
  $str = str_replace('"','',trim($_GET['tag'])); // trim and no doublequote
  $str = srvDropDiacritics($str);
  // query (without table constants)
  $oDBAJAX = new CDatabase();
  if ( !empty($oDBAJAX->error) ) throw new Exception('Unable to save tags');
  $oDBAJAX->debug = false;
  $oDBAJAX->exec( "UPDATE ".QDB_PREFIX."qtatopic SET tags=?,modifdate='".date('Ymd His')."' WHERE id=".$_GET['id'], [$str] );

  $output = ['status'=>'ok', 'info'=>$str];

} catch (Exception $e) {

  $output = ['status'=>'error', 'info'=>$e->getMessage()];

} finally {

  echo json_encode($output);

}