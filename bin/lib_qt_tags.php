<?php // v4.0 build:20240210
// Library not loaded by the init.php but by qtx_adm_tags, qtx_item, qtx_search, srv_query
function readTagsFile(string $file, bool $ci=true)
{
  if ( empty($file) || !file_exists($file) ) return [];
  $arr = [];
  if ( $h=fopen($file,'r') ) {
    while( ($r=fgetcsv($h,500,';'))!==false ) {
      $key = isset($r[0]) ? trim($r[0]) : ''; if ( empty($key) ) continue;
      $val = isset($r[1]) ? trim($r[1]) : '';
      if ( $ci ) { $arr[mb_strtolower($key)] = mb_strtolower($val); } else { $arr[$key] = $val; }
    }
    fclose($h);
  }
  return $arr;
}
function findInTagsFile(string $file, string $tag, bool $ci=true)
{
  // Search the description in the csv file for $tag
  // If not found OR description not defined, returns ''
  if ( empty($file) || !file_exists($file) || empty($tag) ) return '';
  if ( $h=fopen($file,'r') ) {
    $desc = '';
    while( ($r=fgetcsv($h,500,';'))!==false ) {
      $key = isset($r[0]) ? trim($r[0]) : ''; if ( empty($key) ) continue;
      if ( $key===$tag || ( $ci && mb_strtolower($key)===mb_strtolower($tag) ) ) {
        $desc = isset($r[1]) ? trim($r[1]) : '';
        break;
      }
    }
    fclose($h);
    return $desc;
  }
}