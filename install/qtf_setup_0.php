<?php // v4.0 build:20230430  allows app impersonation [qt f|i ]
/**
 * @var string $strPrev
 * @var string $strNext
 * @var string $urlPrev
 * @var string $urlNext
 */
session_start();
include 'init.php';
$urlPrev = APP.'_setup_1.php';
$result = '';

// --------
// Submitted
// --------
if ( isset($_POST['ok']) && !empty($_POST['template']) && file_exists('../config/'.$_POST['template'])) {

  $file = '../config/'.$_POST['template'];
  copy('../config/config_db.php', '../config/config_db_backup.php');
  if ( copy($file, '../config/config_db.php') )
  {
    $result = '<div class="setup_ok">Backup created. Template successfully loaded.</div>';
    $urlNext = APP.'_setup_1.php';
  } else {
    $result = '<div class="setup_err">Fail to copy</div>';
  }

}

// --------
// Html start
// --------
$intHandle = opendir('../config');
$arrFiles = array();
while(false!==($strFile=readdir($intHandle))) if ( $strFile!='.' && $strFile!='..' && substr($strFile,0,10)==='config_db_' ) $arrFiles[]=$strFile;
closedir($intHandle);
asort($arrFiles);

include APP.'_setup_hd.php'; // this will show $error

echo '<div style="margin:20px">';
echo $result;
if ( count($arrFiles)>0 ) {
  echo '<form method="post" action="'.APP.'_setup_0.php">';
  echo 'Template <select name="template">';
  foreach($arrFiles as $strFile) echo '<option value="'.qtAttr($strFile).'">'.$strFile.'</option>';
  echo '</select>';
  echo ' <button type="submit" name="ok">'.L('Load').'</button>';
  echo '</form>';
} else {
  echo 'No template in /config/ directory...';
}
echo '<p style="margin-top:20px"><small>Templates are php files starting with "config_db_" in the "/config/" folder.<br>On load, previous parametres are saved as "config_db_backup".</small></p>';
echo '</div>';

// --------
// HTML END
// --------

include APP.'_setup_ft.php';