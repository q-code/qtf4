<?php // v4.0 build:20230205

// SERVEUR SCRIPT
// Perform async queries on request from web pages (ex: using ajax) with GET method
// Ouput (echo) results as string (or 'no description')

if ( empty($_GET['v']) || empty($_GET['src']) ) { echo '[missing data]'; exit; }
$v = mb_strtolower($_GET['v']); // searched element (lowercase to be case insensitive), not encoded!
$s = isset($_GET['s']) ? $_GET['s'] : '*'; if ( $s==='' || $s<0 ) $s = '*';
include 'lib_qt_tags.php';

// search in specific (if value provided)
if ( $s!=='*' )
{
  $arrTags = readTagsFile($_GET['src'].'_'.$s.'.csv', true); // lowercase keys
  if ( isset($arrTags[$v]) ) { echo $arrTags[$v]; exit; }
}

// search in common
$arrTags = readTagsFile($_GET['src'].'.csv', true); // lowercase keys
if ( isset($arrTags[$v]) ) { echo $arrTags[$v]; exit; }

// search cross-sections [cs]
if ( isset($_GET['cs']) ) {
  for ($i=0;$i<20;$i++) {
    if ( $i==$s ) continue;
    $arrTags = readTagsFile($_GET['src'].'_'.$i.'.csv', true); // lowercase keys
    if ( isset($arrTags[$v]) ) { echo $arrTags[$v]; exit; }
  }
}

// No result
$L = array(); include '../language/'.(isset($_GET['lang']) ? $_GET['lang'] : 'en').'/app_error.php';
echo empty($L['No_descr']) ? 'no description' : mb_strtolower($L['No_descr']);