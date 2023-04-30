<?php // v4.0 build:20230430
// Library not loaded by the init.php but by qtx_adm_tags, qtx_item, qtx_search, srv_query
function readTagsFile(string $file, bool $lower=false)
{
  if ( empty($file) || !file_exists($file) ) return [];
  $arr = array();
  if ( $h=fopen($file,'r') ) {
    while( ($r=fgetcsv($h,500,';'))!==false )
    {
      $key = isset($r[0]) ? trim($lower ? strtolower($r[0]) : $r[0]) : ''; if ( empty($key) ) continue;
      $val = isset($r[1]) ? trim($r[1]) : '';
      $arr[$key] = $val;
    }
    fclose($h);
  }
  return $arr;
}