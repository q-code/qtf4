<?php // v4.0 build:20230618 allows app impersonation [qt f|i|e|m]

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

  $oDB->debug = true;//!!!

  $_SESSION[QT]['show_time_zone'] = qtDb($_POST['show_time_zone']); // 0=no, 1=time, 2=time+gmt
  $oDB->updSetting('show_time_zone');

  $_SESSION[QT]['time_zone'] = qtDb(substr($_POST['time_zone'],3)); // drop gmt in gmt+i
  $oDB->updSetting('time_zone');

  $_SESSION[QT]['userlang'] = qtDb($_POST['userlang']);
  $oDB->updSetting('userlang');

  if ( !array_key_exists($_POST['language'],LANGUAGES) ) $_POST['language']='en';
  $_SESSION[QT]['language'] = qtDb($_POST['language']);
  $oDB->updSetting('language',$_SESSION[QT]['language']);

  // change language
  include translate('lg_main.php');
  include translate('lg_adm.php');
  include translate('lg_zone.php');
  $oH->selfname = L('Board_region');
  $oH->exitname = $oH->selfname;

  // formatdate
  $str = qtDb(trim($_POST['formatdate'])); if ( empty($str) ) throw new Exception( L('Date_format').' '.L('invalid') );
  $_SESSION[QT]['formatdate'] = $str;
  $oDB->updSetting('formatdate');

  // formattime
  $str = qtDb(trim($_POST['formattime'])); if ( empty($str) ) throw new Exception( L('Time_format').' '.L('invalid') );
  $_SESSION[QT]['formattime'] = $str;
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
$arrFiles = [];
foreach(LANGUAGES as $k=>$values) if ( is_dir('language/'.$k) ) $arrFiles[$k] = $values;

// FORM
echo '
<form method="post" action="'.$oH->self().'">
<h2 class="config">'.L('Language').'</h2>
<table class="t-conf">
';
echo '<tr>
<th><label for="language">'.L('Dflt_language').'</label></th>
<td><select id="language" name="language" onchange="qtFormSafe.not();">'.qtTags( $arrFiles, $_SESSION[QT]['language'] ).'</select><span class="small indent">'.(file_exists('language/readme.txt') ? '<a href="tool_txt.php?ro=1&exit=qtf_adm_region.php&file=language/readme.txt&title=How to add languages" onclick="return qtFormSafe.exit(e0);">How to add languages...</a>' :'').'</span></td>
</tr>
';
echo '<tr>
<th><label for="userlang">'.L('User_language').'</label></th>
<td><select id="userlang" name="userlang" onchange="qtFormSafe.not();">'.qtTags( [L('N'),L('Y')], (int)$_SESSION[QT]['userlang'] ).'</select><span class="small indent">'.L('H_User_language').'</span></td>
</tr>
</table>
';
echo '<h2 class="config">'.L('Date_time').'</h2>
<table class="t-conf">
';
if ( PHP_VERSION_ID>=50200 ) {
echo '<tr>
<th>Server time</th>
<td><span>'.date('H:i').' (gmt '.gmdate('H:i').')</span><span class="small indent"><a href="'.APP.'_adm_time.php" onclick="return qtFormSafe.exit(e0);">'.L('Change_time').'...</a></span></td>
</tr>
';
}
echo '<tr>
<th>'.L('Date_format').'</th>
<td><input type="text" name="formatdate" size="10" maxlength="24" value="'.qtAttr($_SESSION[QT]['formatdate']).'" onchange="qtFormSafe.not();"/><span class="small indent">'.L('H_Date_format').'</span></td>
</tr>
';
echo '<tr>
<th>'.L('Time_format').'</th>
<td><input type="text" name="formattime" size="10" maxlength="24" value="'.qtAttr($_SESSION[QT]['formattime']).'" onchange="qtFormSafe.not();"/><span class="small indent">'.L('H_Time_format').'</span></td>
</tr>
</table>
';
echo '<h2 class="config">'.L('Clock').'</h2>
<table class="t-conf">
<tr>
<th>'.L('Show_time_zone').'</th>
<td><select name="show_time_zone" onchange="qtFormSafe.not();">'.qtTags( [L('N'),L('Y'),L('Y').' (+gmt)'], (int)$_SESSION[QT]['show_time_zone'] ).'</select><span class="small indent">'.L('H_Show_time_zone').'</span></td>
</tr>
<tr>
<th>'.L('Clock_setting').'</th>
<td><select name="time_zone" onchange="qtFormSafe.not();">'.qtTags( L('tz.*'),'gmt'.$_SESSION[QT]['time_zone'] ).'</select><span class="small">&nbsp;</span></td>
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
<td class="void right">'.L('Date').'</td><td class="void">'.qtDate('now',$_SESSION[QT]['formatdate'],'',false).'</td>
</tr>
<tr>
<td class="void right">'.L('Clock').'</td><td class="void">';
echo gmdate($_SESSION[QT]['formattime'],time()+(3600*$_SESSION[QT]['time_zone']));
if ( $_SESSION[QT]['show_time_zone']=='2' ) echo ' (gmt'.($_SESSION[QT]['time_zone']>0 ? '+' : '').($_SESSION[QT]['time_zone']==0 ? '' : $_SESSION[QT]['time_zone']).')';
echo '</td>
</tr>
</table>
';

// HTML END

include APP.'_adm_inc_ft.php';