<?php // v4.0 build:20240210 allows app impersonation [qt*]

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php'; if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');
include translate('lg_zone.php');

// INITIALISE

$oH->selfurl = APP.'_adm_region.php';
$oH->selfname = L('Board_region');
$oH->selfparent = L('Settings');
$oH->exiturl = $oH->selfurl;
$oH->exitname = $oH->selfname;

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) ) try {

  // All $_POST are sanitized into $post
  $post = array_map('trim', qtDb($_POST));

  $_SESSION[QT]['show_time_zone'] = $post['show_time_zone']; // 0=no, 1=time, 2=time+gmt
  $_SESSION[QT]['time_zone'] = substr($post['time_zone'],3); // drop gmt in gmt+i
  $_SESSION[QT]['userlang'] = $post['userlang'];
  $oDB->updSetting(['show_time_zone','time_zone','userlang']);

  if ( !array_key_exists($post['language'],LANGUAGES) ) $post['language']='en';
  $_SESSION[QT]['language'] = $post['language'];
  $oDB->updSetting('language',$_SESSION[QT]['language']);

  // change language
  include translate('lg_main.php');
  include translate('lg_adm.php');
  include translate('lg_zone.php');
  $oH->selfname = L('Board_region');
  $oH->exitname = $oH->selfname;

  // formatdate
  if ( empty($post['formatdate']) ) throw new Exception( L('Date_format').' '.L('invalid') );
  $_SESSION[QT]['formatdate'] = $post['formatdate'];
  $oDB->updSetting('formatdate');

  // formattime
  if ( empty($post['formattime']) ) throw new Exception( L('Time_format').' '.L('invalid') );
  $_SESSION[QT]['formattime'] = $post['formattime'];
  $oDB->updSetting('formattime');

  // Successfull end
  SMem::set('settingsage',time());
  $_SESSION[QT.'splash'] = L('S_save');

} catch (Exception $e) {

  // Splash short message and send error to ...inc_hd.php
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
<form class="formsafe" method="post" action="'.$oH->selfurl.'">
<h2 class="config">'.L('Language').'</h2>
<table class="t-conf">
';
echo '<tr>
<th><label for="language">'.L('Dflt_language').'</label></th>
<td><select id="language" name="language">'.qtTags( $files, $_SESSION[QT]['language'] ).'</select><span class="small indent">'.(file_exists('language/readme.txt') ? '<a href="tool_txt.php?ro=1&exit=qtf_adm_region.php&file=language/readme.txt&title=How to add languages">How to add languages...</a>' :'').'</span></td>
</tr>
';
echo '<tr>
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
<td><input type="text" name="formatdate" size="10" maxlength="24" value="'.qtAttr($_SESSION[QT]['formatdate']).'"/><span class="small indent">'.L('H_Date_format').'</span></td>
</tr>
';
echo '<tr>
<th>'.L('Time_format').'</th>
<td><input type="text" name="formattime" size="10" maxlength="24" value="'.qtAttr($_SESSION[QT]['formattime']).'"/><span class="small indent">'.L('H_Time_format').'</span></td>
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
';
echo '<tr>
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