<?php

function qtAttr($str,$size=0,$unquote='')
{
  if ( !is_string($str) && !is_int($str) ) die('qtAttr argument must be a string (or integer)');
  $str = trim($str);
  if ( $str==='' ) return '';
  if ( $size && isset($str[$size]) ) $str = substr($str,0,$size); // negatif will drop final $size characters
  return strpos($str,'"')!==false ? str_replace('"',$unquote,$str) : $str;
}
function L(string $k, int $n=null, string $format='n w', array $A=[], string $pk='')
{
  // Initialise
  if ( empty($A) ) { global $L; $A = $L; } // if no dictionnary, uses global dictionnary $L
  $str = substr($k,-2)==='.*' ? [] : $pk.$k; // default result is the key (if dico entry not found) or an empty array (if dico array not found)
  // Format (formula shortcut)
  // Note: php format can also be used, but pay attention that $format is only used when a $n exists (not null) and that the word will be the 2nd input in the formula
  if ( $format==='n w') $format = '%1$d %2$s';
  if ( $format==='' || $format==='w' ) $format = '%2$s';
  // Check subarray request (use recursive call)
  if ( strpos($k, '.')>0 ) {
    $part = explode('.', $k, 2);
    if ( empty($A[$part[0]]) || !is_array($A[$part[0]]) ) return $str;
    if ( $part[1]==='*' ) return $A[$part[0]];
    return L($part[1], $n, $format, $A[$part[0]], $part[0].'.');
  }
  // Check if plural can be used
  $s = $n===null || $n<2 ? '' : '+';
  // Resolve word
  if ( !empty($A[$k.$s]) ) {
    $str = $A[$k.$s];
  } elseif ( !empty($A[ucfirst($k.$s)]) ) {
    $str = mb_strtolower($A[ucfirst($k.$s)]);
  } elseif ( !empty($A[$k]) ) {
    $str = $A[$k];
  } elseif ( !empty($A[ucfirst($k)]) ) {
    $str = mb_strtolower($A[ucfirst($k)]);
  } elseif ( trim($k)==='' ) {
    throw new Exception('Invalid argument');
  } else {
    if ( substr($str,0,2)==='E_' ) $str = 'error: '.substr($str,2); // key is an (un-translated) error code, returns the error code
    if ( strpos($str,'_')!==false ) $str = str_replace('_',' ',$str); // When word is missing, returns the key code without _ (with the parentkey if used)
    if ( isset($_SESSION['QTdebuglang']) && $_SESSION['QTdebuglang'] ) $str = '<span style="color:red">'.$str.'</span>';
  }
  // Return the word (with $n if not null)
  return $n===null ? $str : sprintf($format,$n,$str);
}
function saveToFile($file,$str='',$create=true)
{
  if ( empty($file) || !is_string($file) )  die('saveToFile:#1 must be a string');
  if ( !is_string($str) )  die('saveToFile:#2 must be a string');
  if ( !is_bool($create) )  die('saveToFile:#3 must be a boolean');
  $error = '';
  // Stop of no file and creation not allowed
  if ( !file_exists($file) && !$create ) return 'Impossible to open the file ['.$file.'].';
  // Update file (or create file)
  if ( !$handle=fopen($file, 'w') ) $error='Impossible to open the file ['.$file.'].';
  if ( empty($error) )
  {
    if ( fwrite($handle,$str)===FALSE )
    {
      $error = 'Impossible to write into the file ['.$file.'].';
    }
    else
    {
     fclose($handle);
    }
  }
  return $error;
}

include '../config/config_db.php';
require '../config/config_cst.php';
include '../bin/class/class.qt.db.php'; if ( strpos(QDB_SYSTEM,'sqlite') ) define ('QDB_SQLITEPATH', '../');
$error = '';

$_SESSION[QT.'_usr']['role'] = 'A'; // admin impersonation

// Language (GET from url, otherwise use session)
if ( isset($_GET['lang']) ) $_SESSION[APP.'_setup_lang']=$_GET['lang'];
if ( !isset($_SESSION[APP.'_setup_lang']) ) $_SESSION[APP.'_setup_lang']='en';

// load language
if ( file_exists('../language/'.$_SESSION[APP.'_setup_lang'].'/'.'lg_install.php') ) {
  include '../language/'.$_SESSION[APP.'_setup_lang'].'/'.'lg_install.php';
} else {
  include 'lg_install.php'; // fallback language
}