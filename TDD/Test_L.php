<?php
function QTattr1($str,$size=0,$unquote='')
{
  if ( !is_string($str) && !is_int($str) ) die('QTattr argument must be a string (or integer)');
  $str = trim($str);
  if ( $str==='' ) return '';
  if ( $size && isset($str[$size]) ) $str = substr($str,0,$size); // negatif will drop final $size characters
  return strpos($str,'"')!==false ? trim(str_replace('"',$unquote,$str)) : $str;
}
function L0($key='',$n=false,$asAttr=false,$show_n=true,$sep_n=' ')
{
   // REQUIREMENT: function QTattr

  $subDico = null; // sub array (if exist)
  $str = '';
  global $L;
  // Check sub-array
  if ( strpos($key,'.')>0 )
  {
    $parts = explode('.',$key,3); // maximum 3 parts
    if ( $parts[1]!=='' && isset($L[$parts[0]]) && is_array($L[$parts[0]]) ) {
      $subDico = $L[$parts[0]];
      $key = $parts[1];
      if ( count($parts)==3 && $parts[2]!=='' && isset($subDico[$parts[1]]) && is_array($subDico[$parts[1]]) ) {
        $subDico = $subDico[$parts[1]];
        $key = $parts[2];
      }
      if ( $key==='*' && is_array($subDico) ) return $asAttr ? array_map('QTattr1', $subDico) : $subDico; // support to return all sub-items
    }
  }

  // Process
  $dico = empty($subDico) ? $L : $subDico; // use dictionnary (or dictionnary subset)
  if ( isset($dico[$key]) )
  {
    if ( $n>1 && isset($dico[$key.'s']) ) $key .= 's';
    $str = $dico[$key];
  }
  elseif ( isset($dico[ucfirst($key)]) )
  {
    $key = ucfirst($key);
    if ( $n>1 && isset($dico[$key.'s']) ) $key .= 's';
    $str = strtolower($dico[$key]);
  }
  else
  {
    $str .= str_replace('_',' ',$key); // When word is missing, returns the key code without _ (with the parentkey if used)
    if ( substr($key,0,2)==='E_' ) $str = 'error: '.substr($str,2); // key is an (un-translated) error code, returns the error code
    //if ( isset($_SESSION['QTdebuglang']) && $_SESSION['QTdebuglang'] ) $str = '<span style="color:red">'.$str.'</span>';
  }

  // Post-process
  if ( $asAttr ) $str = QTattr($str);
  return ($n!==false && $show_n ? $n.$sep_n : '').$str; // When $show_n is true the $n value is added (before) the word with a $sep_n character.
}

function L(string $k='', $n=false, bool $show_n=true, string $sep_n=' ', array $A=[], string $pk='')
{
  if ( empty(trim($k)) ) throw new Exception('Invalid argument');
  // initialise
  $str = $pk.$k; // default is the key (if the key cannot match a dictionnary entry)
  if ( empty($pk) ) { global $L; $A=$L; } // sub-dictionnary (or full dictionnary $L)
  // check subarray
  if ( strpos($k,'.')>0 )
  {
    $part = explode('.', $k,2);
    if ( empty($A[$part[0]]) || !is_array($A[$part[0]]) ) return $k;
    if ( $part[1]==='*' ) return $A[$part[0]];
    return L($part[1], $n, $show_n, $sep_n, $A[$part[0]], $part[0].'.');
  }
  // check if plural can be used
  $s = $n===false || $n<2 ? '' : 's';
  // search word
  if ( !empty($A[$k.$s]) ) {
    $str = $A[$k.$s];
  } elseif ( !empty($A[ucfirst($k.$s)]) ) {
    $str = strtolower($A[ucfirst($k.$s)]);
  } elseif ( !empty($A[$k]) ) {
    $str = $A[$k];
  } elseif ( !empty($A[ucfirst($k)]) ) {
    $str = strtolower($A[ucfirst($k)]);
  } else {
    if ( substr($str,0,2)==='E_' ) $str = 'error: '.substr($str,2); // key is an (un-translated) error code, returns the error code
    if ( strpos($str,'_')!==false ) $str = str_replace('_',' ',$str); // When word is missing, returns the key code without _ (with the parentkey if used)
    //if ( isset($_SESSION['QTdebuglang']) && $_SESSION['QTdebuglang'] ) $str = '<span style="color:red">'.$str.'</span>';
  }
  // if show number
  return ($n!==false && $show_n ? $n.$sep_n : '').$str;
}

function L3($k='', $n=false, $show_n=true, $sep_n=' ', $A=[], $pk='')
{
  if ( trim($k)==='' ) throw new Exception('Invalid argument');
  if ( $n!==false && !is_int($n) ) throw new Exception('Invalid argument');
  if ( is_int($n) && $n<0 ) throw new Exception('Invalid argument');
  // initialise
  if ( !$A ) { global $L; $A=$L; } // on empty sub-dictionnary use full $L
  $str = $pk.$k; // default is the key (if the key cannot match a dictionnary entry)
  // check subarray
  if ( strpos($k,'.')>0 )
  {
    $part = explode('.', $k,2);
    if ( empty($A[$part[0]]) || !is_array($A[$part[0]]) ) return $k;
    if ( $part[1]==='*' ) return $A[$part[0]];
    return L($part[1], $n, $show_n, $sep_n, $A[$part[0]], $part[0].'.');
  }
  // check if plural can be used
  $s = $n==false || $n<2 ? '' : 's';
  // search word
  if ( !empty($A[$k.$s]) ) {
    $str = $A[$k.$s];
  } elseif ( !empty($A[ucfirst($k.$s)]) ) {
    $str = strtolower($A[ucfirst($k.$s)]);
  } elseif ( !empty($A[$k]) ) {
    $str = $A[$k];
  } elseif ( !empty($A[ucfirst($k)]) ) {
    $str = strtolower($A[ucfirst($k)]);
  } else {
    if ( substr($str,0,2)==='E_' ) $str = 'error: '.substr($str,2); // key is an (un-translated) error code, returns the error code
    if ( strpos($str,'_')!==false ) $str = str_replace('_',' ',$str); // When word is missing, returns the key code without _ (with the parentkey if used)
    //if ( isset($_SESSION['QTdebuglang']) && $_SESSION['QTdebuglang'] ) $str = '<span style="color:red">'.$str.'</span>';
  }
  // if show number
  return ($n!==false && $show_n ? $n.$sep_n : '').$str;
}

// tester
/*
 * @param array $L
 *  */
include '../language/en/qtf_main.php';

$time_start = microtime(true);

foreach($L as $k=>$w)
{
  if ( is_string($w) )
  {
  $l = strtolower($k);
  echo L($k);
  echo L($k,2);
  echo L($l);
  echo L($l,2);
  }
  if ( is_array($w) )
  {
    foreach($w as $sk=>$sw)
    {
      $sl = strtolower($sk);
      echo L($k.'.'.$sk);
      echo L($k.'.'.$sk,2);
      echo L($k.'.'.$sl);
      echo L($k.'.'.$sl,2);
    }
  }
}

$time_end = microtime(true);

echo $time_start.' - '.$time_end.' = '.($time_end-$time_start);