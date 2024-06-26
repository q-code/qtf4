<?php // v4.0 build:20240210 allows app impersonation [qtf|i|e|n]

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php'; if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');
include translate('lg_zone.php');

// INITIALISE

$oH->name = L('Board_region');
$parentname = L('Settings');
$oH->exiturl = $oH->php;
$oH->exitname = $oH->name;

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) ) try {

  // All $_POST are sanitized
  $_POST = array_map('trim', qtDb($_POST));
  if ( empty($_POST['formatdate']) ) throw new Exception( L('Date_format').' '.L('not_empty') );
  if ( empty($_POST['formattime']) ) throw new Exception( L('Time_format').' '.L('not_empty') );
  $_SESSION[QT]['formatdate'] = $_POST['formatdate'];
  $_SESSION[QT]['formattime'] = $_POST['formattime'];
  $_SESSION[QT]['show_time_zone'] = $_POST['show_time_zone']; // 0=no, 1=time, 2=time+gmt
  $_SESSION[QT]['time_zone'] = substr($_POST['time_zone'],3); // drop gmt in gmt+i
  $_SESSION[QT]['userlang'] = $_POST['userlang']; // 0|1
  $oDB->updSetting(['formatdate','formattime','show_time_zone','time_zone','userlang']);

  // Change language
  if ( !array_key_exists($_POST['language'],LANGUAGES) ) $_POST['language'] = 'en';
  $_SESSION[QT]['language'] = $_POST['language'];
  $oDB->updSetting('language',$_SESSION[QT]['language']);
  include translate('lg_main.php');
  include translate('lg_adm.php');
  include translate('lg_zone.php');
  $oH->name = L('Board_region');
  $oH->exitname = $oH->name;

  // Successfull end
  SMem::set('settingsage',time());
  $_SESSION[QT.'splash'] = L('S_save');

} catch (Exception $e) {

  $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
  $oH->error = $e->getMessage();

}

// ------
// HTML BEGIN
// ------
include APP.'_adm_inc_hd.php';

// Check language subdirectories
$files = [];
foreach(LANGUAGES as $k=>$values) if ( is_dir('language/'.$k) ) $files[$k] = $values;

// FORM
echo '
<form class="formsafe" method="post" action="'.$oH->php.'">
<h2 class="config">'.L('Language').'</h2>
<table class="t-conf">
<tr>
<th><label for="language">'.L('Dflt_language').'</label></th>
<td><select id="language" name="language">'.qtTags( $files, $_SESSION[QT]['language'] ).'</select><span class="small indent">'.(file_exists('language/readme.txt') ? '<a href="tool_txt.php?ro=1&exit=qtf_adm_region.php&file=language/readme.txt&title=How to add languages">How to add languages...</a>' :'').'</span></td>
</tr>
<tr>
<th><label for="userlang">'.L('User_language').'</label></th>
<td><select id="userlang" name="userlang">'.qtTags( [L('N'),L('Y')], (int)$_SESSION[QT]['userlang'] ).'</select><span class="small indent">'.L('H_User_language').'</span></td>
</tr>
</table>
';
echo '<h2 class="config">'.L('Date_time').'</h2>
<table class="t-conf">
';
if ( PHP_VERSION_ID>=50200 ) {
echo '<tr>
<th>Server time</th>
<td><span>'.date('H:i').' (gmt '.gmdate('H:i').')</span><span class="small indent"><a href="'.APP.'_adm_time.php">'.L('Change_time').'...</a></span></td>
</tr>
';
}
echo '<tr>
<th>'.L('Date_format').'</th>
<td><input required type="text" name="formatdate" size="10" maxlength="24" value="'.qtAttr($_SESSION[QT]['formatdate']).'"/><span class="small indent">'.L('H_Date_format').'</span></td>
</tr>
<tr>
<th>'.L('Time_format').'</th>
<td><input required type="text" name="formattime" size="10" maxlength="24" value="'.qtAttr($_SESSION[QT]['formattime']).'"/><span class="small indent">'.L('H_Time_format').'</span></td>
</tr>
</table>
';
echo '<h2 class="config">'.L('Clock').'</h2>
<table class="t-conf">
<tr>
<th>'.L('Show_time_zone').'</th>
<td><select name="show_time_zone">'.qtTags( [L('N'),L('Y'),L('Y').' (+gmt)'], (int)$_SESSION[QT]['show_time_zone'] ).'</select><span class="small indent">'.L('H_Show_time_zone').'</span></td>
</tr>
<tr>
<th>'.L('Clock_setting').'</th>
<td><select name="time_zone">'.qtTags( L('tz.*'),'gmt'.$_SESSION[QT]['time_zone'] ).'</select><small>&nbsp;</small></td>
</tr>
</table>
';
echo '<p class="submit"><button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</form>
';
echo '<h2 class="config">'.L('Preview').'</h2>
<table class="t-conf" style="width:250px;">
<tr>
<td class="right">'.L('Date').'</td><td>'.qtDate('now',$_SESSION[QT]['formatdate'],'',false).'</td>
</tr>
<tr>
<td class="right">'.L('Clock').'</td><td>';
echo gmdate($_SESSION[QT]['formattime'],time()+(3600*$_SESSION[QT]['time_zone']));
if ( $_SESSION[QT]['show_time_zone']=='2' ) echo ' (gmt'.($_SESSION[QT]['time_zone']>0 ? '+' : '').($_SESSION[QT]['time_zone']==0 ? '' : $_SESSION[QT]['time_zone']).')';
echo '</td>
</tr>
</table>
';

// HTML END

include APP.'_adm_inc_ft.php';