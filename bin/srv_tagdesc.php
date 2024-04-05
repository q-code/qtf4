<?php // v4.0 build:20240210
/*
SERVEUR SCRIPT [async]. GET arguments {fv|s|dir|lang|ci|xs|na|sep}
Perform queries to search a tag description
Ouput (echo) results as string (or 'no description')
*/
if ( empty($_GET['fv']) ) { echo '[missing data]'; exit; }
$fv = $_GET['fv']; // tag to search
$s = isset($_GET['s']) ? (int)$_GET['s'] : -1; // section id (or -1 only common tags)
$dir = isset($_GET['dir']) ? $_GET['dir'] : 'upload/'; // csv tagfiles repository
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en'; // langue
$ci = isset($_GET['ci']) && $_GET['ci']==='0' ? false : true; // default case-insensitive
$xs = isset($_GET['xs']) && $_GET['xs']==='0' ? false : true; // default cross-section
$na = isset($_GET['na']) && $_GET['na']==='1' ? true : false; // default void on description not found
$sep = isset($_GET['sep']) ? $_GET['sep'] : ' - ';

include 'lib_qt_tags.php';

// Search in specific section
if ( $s>=0 ) {
  $desc = findInTagsFile('../'.$dir.'tags_'.$lang.'_'.$s.'.csv', $fv, $ci);
  if ( !empty($desc) ) { echo $sep.$desc; exit; }
}
// Search in common
$desc = findInTagsFile('../'.$dir.'tags_'.$lang.'.csv', $fv, $ci);
if ( !empty($desc) ) { echo $sep.$desc; exit; }
// Search cross-sections
if ( $xs ) {
  for ($i=0;$i<20;$i++) {
    if ( $i===$s ) continue;
    $desc = findInTagsFile('../'.$dir.'tags_'.$lang.'_'.$i.'.csv', $fv, $ci);
    if ( !empty($desc) ) { echo $sep.$desc; exit; }
  }
}
// No result
if ( $na ) {
  $L = []; include '../language/'.$lang.'/app_error.php';
  echo empty($L['No_descr']) ? ' - no description' : $sep.mb_strtolower($L['No_descr']);
}