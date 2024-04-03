<?php // v4.0 build:20240210

// SERVEUR SCRIPT
// Perform async queries on request from web pages (ex: using ajax) with GET method
// Ouput (echo) results as string (or 'no description')

if ( empty($_GET['fv']) ) { echo '[missing data]'; exit; }
$fv = $_GET['fv']; // tag to search
$s = isset($_GET['s']) ? (int)$_GET['s'] : -1;
$dir = isset($_GET['dir']) ? $_GET['dir'] : 'upload/';
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
$ci = isset($_GET['ci']) && $_GET['ci']!=='1' ? false : true; // default is case-insensitive
include 'lib_qt_tags.php';

// Search in specific section
if ( $s>=0 ) {
  $desc = findInTagsFile('../'.$dir.'tags_'.$lang.'_'.$s.'.csv', $fv, $ci);
  if ( !empty($desc) ) { echo $desc; exit; }
}

// Search in common
$desc = findInTagsFile('../'.$dir.'tags_'.$lang.'.csv', $fv, $ci);
if ( !empty($desc) ) { echo $desc; exit; }

// search cross-sections [cs]
if ( isset($_GET['cs']) ) {
  for ($i=0;$i<20;$i++) {
    if ( $i===$s ) continue;
    $desc = findInTagsFile('../'.$dir.'tags_'.$lang.'_'.$i.'.csv', $fv, $ci);
    if ( !empty($desc) ) { echo $desc; exit; }
  }
}

// No result
$L = []; include '../language/'.$lang.'/app_error.php';
echo empty($L['No_descr']) ? 'no description' : mb_strtolower($L['No_descr']);