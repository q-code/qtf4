<?php // v4.0 build:20230205 allows app impersonation [qt f|i|e ]

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

function saveToFile(string $file, string $txt='', bool $create=true, string $mode='w')
{
  if ( empty($file) || empty($mode) ) die('saveToFile: invalid argument');
  global $oH;
  $oH->error = '';
  // Stop of no file and creation not allowed
  if ( !file_exists($file) && !$create ) return 'Impossible to open the file ['.$file.'].';
  // Update file (or create file)
  if ( !$handle=fopen($file, $mode) ) $oH->error='Impossible to open the file ['.$file.'].';
  if ( empty($oH->error) )
  {
    if ( fwrite($handle,$txt)===FALSE )
    {
      $oH->error = 'Impossible to write into the file ['.$file.'].';
    }
    else
    {
      fclose($handle);
    }
  }
  return $oH->error;
}

$exit = ''; // mandatory
$file = ''; // mandatory
$a = '';
$help = '';
$rows = 15;
$title = '';
$ro = ''; // [optional] use "1" to force readonly
qtHttp('exit! file! a help int:rows title ro');
$ext = pathinfo($file, PATHINFO_EXTENSION);
if ( !in_array($ext, ['txt','csv','text','css','info']) ) die( 'Unsupported file extension' );
$readonly = substr($file,0,9)==='language/' || substr($file,0,7)==='upload/' || substr($file,0,5)==='skin/' ? false : true ; // edit only works within languge|skin|upload
if ( $ro==='1' || strtolower($ro)==='true' ) $readonly = true;
if ( $rows<15 || $rows>40 ) $rows=15;

// INITIALISE

const HIDE_MENU_TOC=true;
const HIDE_MENU_LANG=true;

$oH->selfurl = 'tool_txt.php';
$oH->selfname = empty($title) ? L('File') : $title;
$oH->exiturl = $exit;
$oH->exitname = L('Exit');

// SUBMITTED delete/rename

if ( $a==='delete' && file_exists($file) )
{
  if ( $readonly ) die('This file type cannot be changed with this interface');
  // Rename
  $b = rename($file,$file.'.bck');
  // Exit
  $_SESSION[QT.'splash'] = ($b ? L('S_delete') : 'E|Unable to rename file');
}

// SUBMITTED restore

if ($a==='restore' && file_exists($file.'.bck') )
{
  if ( $readonly ) die('This file type cannot be changed with this interface');
  // Rename
  $b = rename($file.'.bck',$file);
  // Exit
  $_SESSION[QT.'splash'] = ($b ? L('S_update') : 'E|Unable to rename file');
}

// SUBMITTED create

if ( $a==='new' && !file_exists($file) )
{
  if ( $readonly ) die('This file type cannot be changed with this interface');
  // Create file.
  if ( empty($oH->error) )
  {
     if ( !$handle=fopen($file, 'w') ) $oH->error='Impossible to open the file ['.$file.'].';
  }
  if ( empty($oH->error) )
  {
     if (fwrite($handle, '') === FALSE)
     {
       $oH->error = 'Impossible to write into the file ['.$file.'].';
     }
     else
     {
       fclose($handle);
     }
  }
  // exit
  $_SESSION[QT.'splash'] = empty($oH->error) ? L('S_save') : 'E|'.$oH->error;
}

// SUBMITTED save changes

if ( isset($_POST['ok']) && isset($_POST['content']) )
{
  if ( $readonly ) die('This file type cannot be changed with this interface');
  $oH->error = saveToFile($file,$_POST['content']);
  // exit
  $_SESSION[QT.'splash'] = empty($oH->error) ? L('S_save') : 'E|'.$oH->error;
}

// HTML begin

include APP.'_adm_inc_hd.php';
echo '<style>
#pg-layout{margin:10px auto;width:660px;padding:20px}
textarea{width:620px;min-width:300px;max-width:750px}
p.helpfile{margin-bottom:0.5rem}
p.filename{padding:4px;text-align:center;color:#888}
</style>';

if ( file_exists($file) )
{
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
  echo '<p class="submit"><button type="button" name="cancel" value="cancel" onclick="window.location=\''.$oH->exiturl.'\';">'.L('Exit').'</button>'.($readonly ? '' : ' &nbsp; <button type="submit" name="ok" value="save">'.L('Save').'</button>').'</p>'.PHP_EOL;
  echo '</form>'.PHP_EOL;
}
else
{
  echo '<p>File not found: ',$file,'</p><br>';
  if ( file_exists($file.'.bck') )
  {
    echo '<p>File found: ',$file.'.bck',' <a class="button" href="'.$oH->selfurl.'?a=restore&exit='.$oH->exiturl.'&file='.$file.(empty($help) ? '' : '&help='.$help).'">Restore deleted file...</a></p><br>';
  }
  echo '<p class="submit"><a class="button" href="'.$oH->exiturl.'">'.L('Exit').'</a>',($readonly ? '' : ' &nbsp; <a class="button" href="'.$oH->selfurl.'?a=new&exit='.$oH->exiturl.'&file='.$file.(empty($help) ? '' : '&help='.$help).'">Create file...</a>') ,'</p>';
}

// HTML end

include APP.'_adm_inc_ft.php';