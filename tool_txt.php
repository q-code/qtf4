<?php // v4.0 build:20240210 allows app impersonation [qt f|i|e|n ]

session_start();
/**
* @var CHtml $oH
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
require 'bin/init.php';
if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');

function saveToFile(string $file, string $txt='', bool $create=true, string $mode='w') {
  if ( empty($file) || empty($mode) ) die('saveToFile: invalid argument');
  if ( !file_exists($file) && !$create ) throw new Exception('Impossible to open the file ['.$file.']');
  if ( !$handle=fopen($file, $mode) ) throw new Exception('Impossible to open the file ['.$file.'] in mode ['.$mode.']');
  if ( fwrite($handle,$txt)===FALSE ) throw new Exception('Impossible to write into the file ['.$file.']');
  fclose($handle);
}

// INITIALISE
$exit = ''; // mandatory
$file = ''; // mandatory
$a = '';
$help = '';
$rows = 15;
$title = '';
$ro = false; // [optional] to force readonly
qtArgs('exit! file! a help int:rows title boo:ro');
$ext = pathinfo($file, PATHINFO_EXTENSION);
if ( !in_array($ext, ['txt','csv','text','css','info']) ) die('Unsupported file extension' );
$readonly = substr($file,0,9)==='language/' || substr($file,0,7)==='upload/' || substr($file,0,5)==='skin/' ? false : true ; // edit only works within languge|skin|upload
if ( $ro ) $readonly = true;
if ( $rows<15 || $rows>40 ) $rows=15;
const HIDE_MENU_TOC = true;
const HIDE_MENU_LANG = true;

$oH->selfurl = 'tool_txt.php';
$oH->selfname = empty($title) ? L('File') : $title;
$oH->exiturl = $exit;
$oH->exitname = L('Exit');

// SUBMITTED delete/rename
if ( $a==='delete' && file_exists($file) ) try {

  if ( $readonly ) throw new Exception('This file type cannot be changed with this interface');
  rename($file,$file.'.bck');
  $_SESSION[QT.'splash'] = L('S_delete');

} catch (Exception $e) {

  $_SESSION[QT.'splash'] = 'E|Unable to rename file';
  $oH->error = $e->getMessage();

}

// SUBMITTED restore
if ( $a==='restore' && file_exists($file.'.bck') ) try {

  if ( $readonly ) throw new Exception('This file type cannot be changed with this interface');
  rename($file.'.bck',$file);
  $_SESSION[QT.'splash'] = L('S_update');

} catch (Exception $e) {

  $_SESSION[QT.'splash'] = 'E|Unable to rename file';
  $oH->error = $e->getMessage();

}

// SUBMITTED create
if ( $a==='new' && !file_exists($file) ) try {

  if ( $readonly ) throw new Exception('This file type cannot be changed with this interface');
  // Create file.
  if ( !$handle=fopen($file, 'w') ) throw new Exception('Impossible to open the file ['.$file.']');
  if ( fwrite($handle, '')===FALSE ) throw new Exception('Impossible to write into the file ['.$file.']');
  fclose($handle);
  $_SESSION[QT.'splash'] = L('S_save');

} catch (Exception $e) {

  $_SESSION[QT.'splash'] = 'E|Unable to create file';
  $oH->error = $e->getMessage();

}

// SUBMITTED save changes
if ( isset($_POST['ok']) && isset($_POST['content']) ) try {

  if ( $readonly ) throw new Exception('This file type cannot be changed with this interface');
  saveToFile($file,$_POST['content']);
  $_SESSION[QT.'splash'] = L('S_save');

} catch (Exception $e) {

  $_SESSION[QT.'splash'] = 'E|Unable to save file';
  $oH->error = $e->getMessage();

}

// HTML begin

include APP.'_adm_inc_hd.php';
echo '<style>
#pg-layout{margin:10px auto;width:660px;padding:20px}
textarea{width:620px;min-width:300px;max-width:750px}
p.helpfile{margin-bottom:0.5rem}
p.filename{padding:4px;text-align:center;color:#888}
</style>';

if ( file_exists($file) ) {

  // help text
  if ( !empty($help) ) {
    $str = translate($help);
    if ( file_exists($str) ) { echo '<p class="helpfile">'; include $str; echo '</p>'; }
  }
  // read file
  $str = file_get_contents($file);
  // editor
  echo '<form method="post" action="'.$oH->selfurl.'">'.PHP_EOL;
  if ( !empty($exit) ) echo '<input type="hidden" name="exit" value="'.$oH->exiturl.'"/>'.PHP_EOL;
  if ( !empty($file) ) echo '<input type="hidden" name="file" value="'.$file.'"/>'.PHP_EOL;
  if ( !empty($help) ) echo '<input type="hidden" name="help" value="'.$help.'"/>'.PHP_EOL;
  echo '<textarea name="content" class="content-'.$ext.'" rows="'.$rows.'"'.($readonly ? ' readonly' : '').'>'.$str.'</textarea>'.PHP_EOL;
  echo '<p class="filename">'.$file.'</p>'.PHP_EOL;
  echo '<p class="submit"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Exit').'</button>'.($readonly ? '' : ' &nbsp; <button type="submit" name="ok" value="save">'.L('Save').'</button>').'</p>'.PHP_EOL;
  echo '</form>'.PHP_EOL;

} else {

  echo '<p>File not found: '.$file.'</p><br>';
  if ( file_exists($file.'.bck') )
  echo '<p>File found: '.$file.'.bck <a class="button" href="'.$oH->selfurl.'?a=restore&exit='.$oH->exiturl.'&file='.$file.(empty($help) ? '' : '&help='.$help).'">Restore deleted file...</a></p><br>';
  echo '<p class="submit"><a class="button" href="'.$oH->exiturl.'">'.L('Exit').'</a>',($readonly ? '' : ' &nbsp; <a class="button" href="'.$oH->selfurl.'?a=new&exit='.$oH->exiturl.'&file='.$file.(empty($help) ? '' : '&help='.$help).'">Create file...</a>') ,'</p>';

}

// HTML END
include APP.'_adm_inc_ft.php';