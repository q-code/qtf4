<?php

function qtAttr(string $str, int $size=0, string $unquote='')
{
  if ( strpos($str,'"')!==false ) $str = str_replace('"',$unquote,$str);
  $str = trim($str);
  return $size && isset($str[$size]) ? substr($str,0,$size) : $str;
}
function L(string $k, int $n=null, string $format='n w', array $A=[], string $pk='', bool $dropDoublequote=true)
{
  // Initialise
  if ( !$A ) { global $L; $A = $L; } // if no dictionnary, uses global dictionnary $L
  $res = substr($k,-2)==='.*' ? [] : $pk.$k; // failed returns the key (or an empty array)
  // On key with '.' works recursively (sub-dictionnary $A[...])
  if ( strpos($k, '.')>0 ) {
    $part = explode('.', $k, 2);
    $pk = $part[0];
    if ( empty($A[$pk]) || !is_array($A[$pk]) || $part[1]==='' ) return $res; // check sub-dictionnary exists (and a sub-key was in $k)
    return $part[1]==='*' ? $A[$pk] : L($part[1], $n, $format, $A[$pk], $pk.'.');
  }
  // Format (formula shortcut)
  // Note: php format can also be used, but pay attention that $format is only used when a $n exists (not null) and that the word will be the 2nd input in the formula
  switch($format){
    case 'n w': $f = '%1$d %2$s'; break;
    case 'k w': $f = '%1$s %2$s'; break;
    case 'w':
    case '': $f = '%2$s'; break;
    default: $f = $format;
  }
  // Check if plural form must be searched (i.e. search for key with '+')
  $p = $n===null || $n<2 ? '' : '+';
  // Resolve word
  if ( !empty($A[$k.$p]) ) {
    $res = $A[$k.$p];
  } elseif ( !empty($A[ucfirst($k.$p)]) ) {
    $res = mb_strtolower($A[ucfirst($k.$p)]);
  } elseif ( !empty($A[$k]) ) {
    $res = $A[$k];
  } elseif ( !empty($A[ucfirst($k)]) ) {
    $res = mb_strtolower($A[ucfirst($k)]);
  } else {
    if ( substr($res,0,2)==='E_' ) $res = 'error: '.substr($res,2); // key is an (un-translated) error code, returns the error code
    if ( strpos($res,'_')!==false ) $res = str_replace('_',' ',$res); // When word is missing, returns the key code without _ (with the parentkey if used)
    if ( isset($_SESSION['QTdebuglang']) && $_SESSION['QTdebuglang'] ) $res = '<span style="color:red;text-shadow:0 0 2px black">'.$res.'</span>';
  }
  // Return the word (with $n if not null)
  if ( $dropDoublequote && strpos($res,'"')!==false ) $res = str_replace('"','',$res);
  return $n===null ? $res : sprintf($f, $format==='k w' ? qtK($n) : $n, $res);
}
function saveToFile(string $file, string $str='', bool $create=true)
{
  if ( empty($file) )  die(__FUNCTION__.'arg #1 must be a string');
  $error = '';
  // Stop of no file and creation not allowed
  if ( !file_exists($file) && !$create ) return 'Impossible to open the file ['.$file.'].';
  // Update file (or create file)
  if ( !$handle=fopen($file, 'w') ) $error = 'Impossible to open the file ['.$file.'].';
  if ( empty($error) ) {
    if ( fwrite($handle,$str)===FALSE ) {
      $error = 'Impossible to write into the file ['.$file.'].';
    } else {
     fclose($handle);
    }
  }
  return $error;
}

include '../config/config_db.php';
require '../config/config_cst.php';
include '../bin/class/class.qt.db.php'; if ( strpos(QDB_SYSTEM,'sqlite') ) define ('QDB_SQLITEPATH', '../');
$error = '';

// Language (GET from url, otherwise use session)
if ( isset($_GET['lang']) ) $_SESSION['setup_lang']=$_GET['lang'];
if ( !isset($_SESSION['setup_lang']) ) $_SESSION['setup_lang']='en';

// load language
if ( file_exists('../language/'.$_SESSION['setup_lang'].'/'.'lg_install.php') ) {
  include '../language/'.$_SESSION['setup_lang'].'/'.'lg_install.php';
} else {
  include 'lg_install.php'; // fallback language
}