<?php  //4.0 build:20240210 allows app impersonation [qtf|i|e|n]

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php'; if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');

// INITIALISE

$oH->selfurl = APP.'_adm_site.php';
$oH->selfname = L('Board_general');
$oH->selfparent = L('Board_info');
$strHelper = false;

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) ) try {

  // Check. All $_POST are sanitized into $post. For username and password use original $_POST[...]
  $post = array_map('trim', qtDb($_POST));
  if ( empty($post['site_name']) ) throw new Exception( L('Site_name').' '.L('not_empty') );
  if ( empty($post['site_url']) ) throw new Exception( L('Site_url').' '.L('not_empty') );
  if ( empty($post['index_name']) ) throw new Exception( L('Name_of_index').' '.L('not_empty') );
  if ( substr($post['site_url'],-1,1)==='/' ) $post['site_url'] = substr($post['site_url'],0,-1);
  if ( strlen($post['site_url'])<10 || !preg_match('/^(http:\/\/|https:\/\/)/',$post['site_url']) ) { $_SESSION[QT]['site_url']='https://'; throw new Exception( L('Site_url').' '.L('invalid') ); }

  // Register and save
  $_SESSION[QT]['site_name'] = $post['site_name'];
  $oH->title = $_SESSION[QT]['site_name']; // refresh current title
  $_SESSION[QT]['site_url'] = $post['site_url'];
  $_SESSION[QT]['index_name'] = $post['index_name'];
  $_SESSION[QT]['admin_email'] = $post['admin_mail'];
  $_SESSION[QT]['use_smtp'] = $post['use_smtp']==='1' ? '1' : '0';
  $oDB->updSetting( ['site_name','site_url','index_name','admin_email','use_smtp'] );
  // Save translations (cache unchanged)
  SLang::delete('index','i');
  foreach($_POST as $k=>$val) if ( substr($k,0,3)==='tr-' && !empty($val) ) SLang::add('index', substr($k,3), 'i', $val);

  // Check and save others (optional)
  foreach(['admin_phone','admin_name','admin_addr'] as $k) if ( isset($_SESSION[QT][$k]) ) $_SESSION[QT][$k] = $post[$k];
  $oDB->updSetting(['admin_phone','admin_name','admin_addr']);
  if ( $_SESSION[QT]['use_smtp']==='1' ) {
    if ( empty($post['smtp_host']) ) throw new Exception( 'Smtp host '.L('not_empty') );
    $_SESSION[QT]['smtp_host'] = $post['smtp_host'];
    $_SESSION[QT]['smtp_port'] = $post['smtp_port'];
    $_SESSION[QT]['smtp_username'] = qtDb($_POST['smtp_username'],true,false,false); // no trim and allows <>&
    $_SESSION[QT]['smtp_password'] = qtDb($_POST['smtp_password'],true,false,false); // no trim and allows <>&
    $oDB->updSetting( ['smtp_host','smtp_port','smtp_username','smtp_password'] );
  }

  // Exit
  memFlushLang(); // Clear cache
  SMem::set('settingsage',time());
  $_L['index'] = SLang::get('index',QT_LANG); // Register lang
  $_SESSION[QT.'splash'] = L('S_save');

} catch (Exception $e) {

  // Splash short message and send error to ...inc_hd.php
  $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
  $oH->error = $e->getMessage();

}

// ------
// HTML BEGIN
// ------
// Start helper
if ( strlen($_SESSION[QT]['site_url'])<10 || !preg_match('/^(http:\/\/|https:\/\/)/',$_SESSION[QT]['site_url']) ) $oH->warning .= L('Site_url').': '.L('E_missing_http').'<br>';
$str = parse_url($_SESSION[QT]['site_url']); $str = empty($str['path']) ? '' : $str['path']; // site url
$cur = parse_url($_SERVER['REQUEST_URI']); $cur = empty($cur['path']) ? '' : $cur['path']; // current url
if ( strpos($cur,$str)===false ) $oH->warning .= 'Url do not match with current site url';
if ( $strHelper ) $oH->warning .= $strHelper;
if ( !empty($oH->warning) ) $oH->warning = qtSVG('flag', 'style=font-size:1.4rem;color:#1364B7').' '.$oH->warning;

include APP.'_adm_inc_hd.php';

// FORM
echo '<form class="formsafe" method="post" action="'.$oH->selfurl.'">
<h2 class="config">'.L('General_site').'</h2>
<table class="t-conf input100">
';
$str = $_SESSION[QT]['site_name'];
echo '<tr title="'.L('H_Site_name').'">
<th>'.L('Site_name').'</th>
<td><input required type="text" name="site_name" maxlength="64" value="'.qtAttr($str).'"/></td>
</tr>
';
echo '<tr title="'.L('H_Site_url').'">
<th>'.L('Site_url').'</th>
<td><input required type="text" name="site_url" pattern="^(http://|https://).*" maxlength="255" value="'.$_SESSION[QT]['site_url'].'"/></td>
</tr>
';
echo '<tr title="'.L('H_Name_of_index').'">
<th>'.L('Name_of_index').'</th>
<td><input required type="text" name="index_name" maxlength="64" value="'.qtAttr($_SESSION[QT]['index_name']).'" style="background-color:#dbf4ff"/></td>
</tr>
<tr>
<th>'.L('Name_of_index').'<br>'.L('Translations').' *</th>
<td><div class="languages-scroll">
';
$arrTrans = SLang::get('index','*','i'); // stripslashed
foreach(LANGUAGES as $k=>$values)
{
  $arr = explode(' ',$values,2); if ( empty($arr[1]) ) $arr[1]=$arr[0];
  echo '<p class="iso" title="'.L('Name_of_index').' ('.$arr[1].')">'.$arr[0].'</p><p><input type="text" name="tr-'.$k.'" maxlength="64" value="'.(empty($arrTrans[$k]) ? '' : qtAttr($arrTrans[$k])).'" placeholder="'.qtAttr($_SESSION[QT]['index_name']).'"/></p>'.PHP_EOL;
}
echo '</div></td>
</tr>
<tr>
<td colspan="2" class="asterix">* '.L('E_no_translation').'<strong style="color:#1364B7">'.$_SESSION[QT]['index_name'].'</strong></td>
</tr>
</table>
';

echo '<h2 class="config">'.L('Contact').'</h2>
<table class="t-conf input100">
';
echo '<tr title="'.L('H_Admin_e_mail').'">
<th>'.L('Adm_e_mail').'</th><td><input required type="email" name="admin_mail" maxlength="255" value="'.qtAttr($_SESSION[QT]['admin_email']).'"/></td></tr>
';
if ( isset($_SESSION[QT]['admin_phone']) ) echo '<tr><th>'.L('Adm_phone').'</th><td><input type="text" name="admin_phone" maxlength="255" value="'.qtAttr($_SESSION[QT]['admin_phone']).'"/></td></tr>'.PHP_EOL;
if ( isset($_SESSION[QT]['admin_name']) ) echo '<tr><th>'.L('Adm_name').'</th><td><input type="text" name="admin_name" maxlength="255" value="'.qtAttr($_SESSION[QT]['admin_name']).'"/></td></tr>'.PHP_EOL;
if ( isset($_SESSION[QT]['admin_addr']) ) echo '<tr><th>'.L('Adm_addr').'</th><td><input type="text" name="admin_addr" maxlength="255" value="'.qtAttr($_SESSION[QT]['admin_addr']).'"/></td></tr>'.PHP_EOL;
echo '</table>
';
echo '<h2 class="config">'.L('Email_settings').'</h2>
<table class="t-conf">
';
if ( empty($_SESSION[QT]['smtp_host']) ) $_SESSION[QT]['smtp_host'] = '';
if ( !isset($_SESSION[QT]['smtp_port']) ) $_SESSION[QT]['smtp_port'] = '25';
if ( empty($_SESSION[QT]['smtp_username']) ) $_SESSION[QT]['smtp_username'] = '';
if ( empty($_SESSION[QT]['smtp_password']) ) $_SESSION[QT]['smtp_password'] = '';
echo '<tr title="'.L('H_Use_smtp').'">
<th>'.L('Use_smtp').'</th>
<td><select name="use_smtp" onchange="toggleSmtp(this.form,this.value);">'.qtTags([L('N'),L('Y')],(int)$_SESSION[QT]['use_smtp']).'</select></td>
</tr>
<tr>
<th>Smtp host</th>
<td><input type="text" id="smtp_host" name="smtp_host" size="28" maxlength="64" value="'.qtAttr($_SESSION[QT]['smtp_host']).'"'.($_SESSION[QT]['use_smtp']==='0' ? ' disabled' : '').'/> port <input type="text" id="smtp_port" name="smtp_port" size="4" maxlength="6" value="'.qtAttr($_SESSION[QT]['smtp_port']).'"'.($_SESSION[QT]['use_smtp']==='0' ? ' disabled' : '').'/></td>
</tr>
<tr>
<th>Smtp username</th>
<td><input type="text" id="smtp_username" name="smtp_username" size="28" maxlength="64" value="'.qtAttr($_SESSION[QT]['smtp_username']).'"'.($_SESSION[QT]['use_smtp']==='0' ? ' disabled' : '').'/></td>
</tr>
<tr>
<th>Smtp password</th>
<td><input type="text" id="smtp_password" name="smtp_password" size="28" maxlength="64" value="'.qtAttr($_SESSION[QT]['smtp_password']).'"'.($_SESSION[QT]['use_smtp']==='0' ? ' disabled' : '').'/> <a href="javascript:void(0)" onclick="addArgs(this);" target="_blank">test smtp</a></td>
</tr>
</table>
'; // use void <a> to bypass formsafe
echo '
<p class="submit"><button type="submit" name="ok" value="save">'.L('Save').'</button></p>
</form>';

// HTML END

$oH->scripts[] = 'function toggleSmtp(form,state) {
  form.smtp_host.disabled = state==="0";
  form.smtp_port.disabled = state==="0";
  form.smtp_username.disabled = state==="0";
  form.smtp_password.disabled = state==="0";
}
function addArgs(ancor) {
  ancor.href = "'.APP.'_adm_smtp.php?h=" + document.getElementById("smtp_host").value + "&p=" + document.getElementById("smtp_port").value + "&u=" + encodeURI(document.getElementById("smtp_username").value) + "&fw=" + encodeURI(document.getElementById("smtp_password").value);
}';

include APP.'_adm_inc_ft.php';