<?php // v4.0 build:20230430

/**
 * Convert empty/null values to 0 (and reset the index-keys by default)
 * @param array $arr
 * @param boolean $resetkeys true to reset the index-keys
 * @return array
 */
function qtArrayzero(array $arr, bool $resetkeys=true)
{
  // Works for integers, floats or numeric-strings. Other types remain unchanged or become 0 if they are evaluated as empty.
  foreach($arr as $key=>$value) if ( empty($value) ) $arr[$key]=0;
  return $resetkeys ? array_values($arr) : $arr;
}

/**
 * Returns the next roof of the maximum values
 * @param array $arr list of values
 * @return integer 5,10,20,30,50,100,200,500,1000 or n000 above the maximum value
 */
function qtRoof($arr)
{
  // This is supposed to works with frequencies (0 or positive values only)
  // Works with integers, floats or numeric-strings. BUT returns the first roof (5) if the array contains a non-numeric string
  if ( is_numeric($arr) ) $arr = array($arr);  // support for a single number
  if ( !is_array($arr) ) die('qtRoof: Arg #1 must be an array');
  $intTop = 5;
  $i = max($arr);
  if ( $i>1000 ) return (floor($i/1000)+1)*1000;
  if ( $i>500 ) return 1000;
  if ( $i>5 ) $intTop = 10;
  if ( $i>10 ) $intTop = 20;
  if ( $i>20 ) $intTop = 30;
  if ( $i>30 ) $intTop = 50;
  if ( $i>50 ) $intTop = 100;
  if ( $i>100 ) $intTop = 200;
  if ( $i>200 ) $intTop = 500;
  return $intTop;
}

/**
 * Compute cumulative values for the array: 1,2,3,1 becomes 1,3,6,7
 * @param array $arr
 * @param integer $d number of decimals, required for float values (ex: percent)
 * @return array of float values
 */
function qtCumul(array $arr, int $d=0)
{
  // This is supposed to works with frequencies (0 or positive values only)
  $arrC = array();
  $i=0;
  foreach($arr as $k=>$value)
  {
    $i += $value;
    $arrC[$k]=round($i,$d);
  }
  return $arrC;
}

/**
 * Returns the percentage of each value in the serie. Ex: 100,20,30,50 becomes 50,10,15,25 (%)
 * @param array $arr list of positive values
 * @param integer $d number of decimals
 * @param boolean $percent true to get results in percent-unit (false to get the ratio between 0 and 1)
 * @return array of float values
 */
function qtPercent(array $arr, int $d=0, bool $percent=true)
{
  // This is supposed to works with frequencies (0 or positive values only), negative values are reset to 0.
  if ( $d<0 ) die ('qtPercent: Arg #2 must be an integer');
  if ( !$percent && $d<1 ) $d=1; // for ratio, at least 1 decimal
  $arrP = array();
  foreach($arr as $k=>$value) if ( $value<0 ) $arr[$k]=0; // frequences cannot be negative
  $intTotal = array_sum($arr); // if 0, each value will be set to 0.0
  foreach($arr as $k=>$value)
  {
    $i = (empty($value) ? 0 : $value/$intTotal);
    if ( $percent ) $i = $i*100;
    $arrP[$k]=round($i,$d);
  }
  return $arrP;
}
function serieExtract($key, array $a, array $b=array(null))
{
  // Returns the $key array inside the $a serie (values are unindexed)
  // If $b is an array containing also a $key-value, this value is added to the array (push)
  // Note: returns an empty array if the $key is not found
  $arr = array();
  if ( isset($a[$key]) )
  {
    if ( !is_array($a[$key]) ) $a[$key] = array($a[$key]);
    $arr = array_values($a[$key]);
    if ( isset($b[$key]) ) array_push($arr,$b[$key]);
  }
  return $arr;
}
function getArrDiff($a,$b,$percent=0, int $round=-1, $null=0)
{
  // Compute difference (or percentage diff) between 2 arrays in sequential order (array keys are not used)
  if ( is_numeric($a) ) $a = array($a);
  if ( is_numeric($b) ) $b = array($b);
  if ( $percent===false ) $percent=0;
  // $percent 0 to skip percentage, use 1 or 100 to get ratio or % (0.5 or 50)
  // $round -1 to skip round
  // return $null if value is not set in $a or $b
  if ( !is_array($a) || !is_array($b) ) die('getArrDiff: invalid arguments #1 or #2');
  if ( !is_numeric($percent) || $percent<0 ) die('getArrDiff: invalid#3');
  $arr = array();
  $a = array_values($a); // drops array keys
  $b = array_values($b);
  for($i=0;$i<count($a);++$i)
  {
    $arr[$i] = is_numeric($a[$i]) && is_numeric($b[$i]) ? $b[$i]-$a[$i] : $null;
    if ( !is_numeric($arr[$i]) ) continue;
    if ( $percent>0 && $arr[$i]!==0 ) $arr[$i] = $a[$i]===0 ? $arr[$i]*$percent : $arr[$i]*$percent/$a[$i];
    if ( $round>=0 ) $arr[$i] = round($arr[$i],$round);
  }
  return $arr;
}
function getAbscissa(string $block='m', int $maxBlock=12, int $dayshift=-10)
{
  // Note: for ease of use, blocktime array index starts at 1. Ex: Jan-Dec have index 1 to 12
  $arr = array();
  switch($block)
  {
  case 'q': for ($i=1;$i<=$maxBlock;++$i) { $arr[$i]='Q'.$i; } break;
  case 'm': for ($i=1;$i<=$maxBlock;++$i) { $arr[$i]=substr(L('dateMM.'.$i),0,1); } break; // 2 chars only
  case 'd': for ($i=1;$i<=$maxBlock;++$i) { $arr[$i]=substr(addDate($dayshift,$i,'day'),-2,2); } break;
  }
  return $arr;
}