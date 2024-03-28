<?php // v4.0 build:20240210 allows app impersonation [qt f|i|e ]

session_start();
/**
* @var CHtml $oH
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php'; if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');

// INITIALISE

$a = isset($_GET['a']) ? $_GET['a'] : 'add';
$oH->selfurl = APP.'_adm_module.php';
$oH->selfname = L('Board_modules');

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) ) {

  if ( isset($_SESSION[QT]['mModules']) ) unset($_SESSION[QT]['mModules']); // clear memory
  if ( isset($_POST['a']) ) $a = $_POST['a'];

  // check form
  $name = strtolower(qtAttr(trim($_POST['name'])));
  $name = str_replace(' ','_',$name);
  $strFile = APP.'m_'.$name.'_'.($a=='rem' ? 'un' : '').'install.php';

  if ( file_exists($strFile) ) {
    $oH->exiturl = '';
    $oH->voidPage('', '<p>'.L('Module').': '.$name.'</p><p>Script: '.$strFile.'</p><p class="submit"><a class="button" href="'.APP.'_adm_module.php">'.L('Cancel').'</a> <a class="button" href="'.$strFile.'">'.($a==='rem' ? L('Remove') : L('Add')).'</a></p>', 'admin');
  } else {
    $oH->error = 'Module not found... ('.$strFile.')<br><br>Possible cause: components of this module are not uploaded.';
    if ( $a==='rem' ) {
      $oDB->exec( "DELETE FROM TABSETTING WHERE param LIKE ? OR param LIKE ?", ['module_'.$name,'m_'.$name] );
      SMem::set('settingsage',time());
    }
  }
}

// ------
// HTML BEGIN
// ------
include APP.'_adm_inc_hd.php';

$arr = [];
foreach(glob(APP.'m_*_install.php') as $name) $arr[] = '<a href="javascript:void(0)" onclick="addValue(this)">'.strtolower(substr($name,5,-12)).'</a>';

echo '<form method="post" action="'.$oH->self().'">
<h2 class="config">'.L($a==="rem" ? 'Remove' : 'Add').'</h2>
<table class="t-conf">
<tr>
<th style="width:200px;"><label for="name">'.L('Module').'</label></th>
<td>'.L('name').' <input required id="name" name="name" size="14" maxlength="24" /><input type="hidden" name="a" value="'.$a.'"/> <button type="submit" name="ok" value="search">'.L('Search').'</button></td>
</tr>
</table>
</form>
';
echo '<p>'.L('Module_detected_names').': '.(count($arr)>0 ? implode(', ',$arr) : '(nothing available)').'</p>';

// HTML END

$oH->scripts[] = 'qtFocusAfter("name");
function addValue(ancor) { document.getElementById("name").value=ancor.innerHTML; }';

include APP.'_adm_inc_ft.php';