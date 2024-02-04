<?php // v4.0 build:20230618  allows app impersonation [qt f|i ]
/**
 * @var string $strPrev
 * @var string $strNext
 * @var string $urlPrev
 * @var string $urlNext
 */
session_start();
include 'init.php';
$urlPrev = 'setup_1.php';
$urlNext = 'setup_1.php';
$result = '';

// ------
// Submitted
// ------
if ( isset($_POST['ok']) && !empty($_POST['template']) && file_exists('../config/'.$_POST['template'])) {

  $copied = true;
  if ( $_POST['template']!=='config_db_backup.php' ) $copied = copy('../config/config_db.php', '../config/config_db_backup.php');
  $copied = copy('../config/'.$_POST['template'], '../config/config_db.php');
  $result = $copied ? '<p class="is_ok">Backup created. Template successfully loaded.</p>' : '<p class="is_err">Fail to copy</p>';
}

// ------
// Html start
// ------
$intHandle = opendir('../config');
$arrFiles = [];
while(false!==($strFile=readdir($intHandle))) if ( $strFile!='.' && $strFile!='..' && substr($strFile,0,10)==='config_db_' ) $arrFiles[]=$strFile;
closedir($intHandle);
asort($arrFiles);

include 'setup_hd.php'; // this will show $error

echo $result;
if ( count($arrFiles)>0 ) {
  echo '<form method="post" action="setup_1_tpl.php">';
  echo 'Template <select name="template">';
  foreach($arrFiles as $strFile) echo '<option value="'.qtAttr($strFile).'">'.$strFile.'</option>';
  echo '</select>';
  echo ' <button type="submit" name="ok">'.L('Load').'</button>';
  echo '</form>';
} else {
  echo 'No template in /config/ directory...';
}
echo '<p style="margin-top:20px;font-size:0.8rem">On load, previous parametres are saved as "config_db_backup".<br>
Templates are in the "/config/" folder.</p>';

// ------
// HTML END
// ------
include 'setup_ft.php';