<?php  //4.0 build:20230430 allows app impersonation [qt f|i ]

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
$strHelper=false;

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) ) try {

  // check sitename
  if ( strpos($_POST['site_name'],'<')!==false ) $strHelper = L('Tags_not_allowed');
  $str = qtDb(trim($_POST['site_name'])); // encode
  if ( empty($str) ) throw new Exception( L('Site_name').' '.L('invalid') );
  $_SESSION[QT]['site_name'] = $str;
  $oH->title = $_SESSION[QT]['site_name']; // refresh current title

  // check siteurl
  if ( strpos($_POST['site_url'],'<')!==false ) $strHelper = L('Tags_not_allowed');
  $str = qtDb(trim($_POST['site_url'])); if ( substr($str,-1,1)==='/' ) $str = substr($str,0,-1); // drop final /
  if ( empty($str) || strlen($str)<10 || !preg_match('/^(http:\/\/|https:\/\/)/',$str) ) { $_SESSION[QT]['site_url']='https://'; throw new Exception( L('Site_url').' '.L('invalid') ); }
  $_SESSION[QT]['site_url'] = $str;

  // check indexname
  if ( strpos($_POST['index_name'],'<')!==false ) $strHelper = L('Tags_not_allowed');
  $str = qtDb(trim($_POST['index_name'])); // encode
  if ( empty($str) ) throw new Exception( L('Name_of_index').' '.L('invalid') );
  $_SESSION[QT]['index_name'] = $str;

  // check adminemail
  $str = qtDb(trim($_POST['admin_mail']));
  if ( !qtIsMail($str) ) throw new Exception( L('Adm_e_mail').' '.L('invalid') );
  $_SESSION[QT]['admin_email'] = $str;

  // check smtp
  $_SESSION[QT]['use_smtp'] = substr($_POST['use_smtp'],0,1);

  // Save values
  $oDB->updSetting( ['site_name','site_url','index_name','admin_email','use_smtp'] );

  // check and save others (optional)
  foreach(['admin_phone','admin_name','admin_addr'] as $key) {
    if ( !isset($_SESSION[QT][$key]) ) continue;
    if ( strpos($_POST[$key],'<')!==false ) $strHelper = L('Tags_not_allowed');
    $_SESSION[QT][$key] = qtDb(trim($_POST[$key]));
    $oDB->updSetting($key);
  }

  if ( $_SESSION[QT]['use_smtp']=='1' ) {
    $_SESSION[QT]['smtp_host'] = qtDb(trim($_POST['smtp_host'])); if ( empty($_SESSION[QT]['smtp_host']) ) throw new Exception( 'Smtp host '.L('invalid') );
    $_SESSION[QT]['smtp_port'] = qtDb(trim($_POST['smtp_port']));
    $_SESSION[QT]['smtp_username'] = qtDb($_POST['smtp_username'],true,false,false); // no trim and allows <>&
    $_SESSION[QT]['smtp_password'] = qtDb($_POST['smtp_password'],true,false,false); // no trim and allows <>&
    $oDB->exec( "DELETE FROM TABSETTING WHERE param='smtp_host' OR param='smtp_port' OR param='smtp_username' OR param='smtp_password'" );
    foreach(['smtp_host','smtp_port','smtp_username','smtp_password'] as $param) $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES (:param,:setting)", [':param'=>$param, ':setting'=>$_SESSION[QT][$param] ] );
  }

  // Save translations (cache unchanged)
  SLang::delete('index','i');
  foreach($_POST as $k=>$posted) {
    if ( substr($k,0,3)==='tr-'  && !empty($posted) ) {
      if ( strpos($posted,'<')!==false ) $strHelper = L('Tags_not_allowed');
      SLang::add('index', substr($k,3), 'i', $posted);
    }
  }
  memFlushLang(); // Clear cache
  SMem::set('settingsage',time());
  $_L['index'] = SLang::get('index',QT_LANG); // Register lang
  $_SESSION[QT.'splash'] = L('S_save');

} catch (Exception $e) {

  $_SESSION[QT.'splash'] = 'E|'.$e->getMessage();

}

// --------
// HTML BEGIN
// --------

// Start helper
if ( strlen($_SESSION[QT]['site_url'])<10 || !preg_match('/^(http:\/\/|https:\/\/)/',$_SESSION[QT]['site_url']) ) $oH->warning .= L('Site_url').': '.L('E_missing_http').'<br>';
$str = parse_url($_SESSION[QT]['site_url']); $str = empty($str['path']) ? '' : $str['path']; // site url
$cur = parse_url($_SERVER['REQUEST_URI']); $cur = empty($cur['path']) ? '' : $cur['path']; // current url
if ( strpos($cur,$str)===false ) $oH->warning .= 'Url do not match with current site url';
if ( !qtIsMail($_SESSION[QT]['admin_email']) ) $oH->warning .= L('Adm_e_mail').' '.L('invalid').'<br>';
if ( $strHelper ) $oH->warning .= $strHelper;
if ( !empty($oH->warning) ) $oH->warning = getSVG('flag', 'style=font-size:1.4rem;color:#1364B7').' '.$oH->warning;

include APP.'_adm_inc_hd.php';

// FORM
echo '<form method="post" action="'.$oH->selfurl.'">
<h2 class="config">'.L('General_site').'</h2>
<table class="t-conf input100">
';
$str = $_SESSION[QT]['site_name'];
echo '<tr title="'.L('H_Site_name').'">
<th>'.L('Site_name').'</th>
<td><input required type="text" name="site_name" maxlength="64" value="'.qtAttr($str).'" onchange="qtFormSafe.not();"/></td>
</tr>
';
echo '<tr title="'.L('H_Site_url').'">
<th>'.L('Site_url').'</th>
<td><input required type="text" name="site_url" pattern="^(http://|https://).*" maxlength="255" value="'.$_SESSION[QT]['site_url'].'" onchange="qtFormSafe.not();"/></td>
</tr>
';
echo '<tr title="'.L('H_Name_of_index').'">
<th>'.L('Name_of_index').'</th>
<td><input required type="text" name="index_name" maxlength="64" value="'.qtAttr($_SESSION[QT]['index_name']).'" style="background-color:#dbf4ff" onchange="qtFormSafe.not();"/></td>
</tr>
<tr>
<th>'.L('Name_of_index').'<br>'.L('Translations').' *</th>
<td><div class="languages-scroll">
';
$arrTrans = SLang::get('index','*','i'); // stripslashed
foreach(LANGUAGES as $k=>$values)
{
  $arr = explode(' ',$values,2); if ( empty($arr[1]) ) $arr[1]=$arr[0];
  echo '<p class="iso" title="'.L('Name_of_index').' ('.$arr[1].')">'.$arr[0].'</p><p><input type="text" name="tr-'.$k.'" maxlength="64" value="'.(empty($arrTrans[$k]) ? '' : qtAttr($arrTrans[$k])).'" placeholder="'.qtAttr($_SESSION[QT]['index_name']).'" onchange="qtFormSafe.not();"/></p>'.PHP_EOL;
}
echo '</div></td>
</tr>
<tr>
<td colspan="2" class="void asterix">* '.L('E_no_translation').'<strong style="color:#1364B7">'.$_SESSION[QT]['index_name'].'</strong></td>
</tr>
</table>
';

echo '<h2 class="config">'.L('Contact').'</h2>
<table class="t-conf input100">
';
echo '<tr title="'.L('H_Admin_e_mail').'">
<th>'.L('Adm_e_mail').'</th><td><input required type="email" name="admin_mail" maxlength="255" value="'.qtAttr($_SESSION[QT]['admin_email']).'" onchange="qtFormSafe.not();"/></td></tr>
';
if ( isset($_SESSION[QT]['admin_phone']) ) echo '<tr><th>'.L('Adm_phone').'</th><td><input type="text" name="admin_phone" maxlength="255" value="'.qtAttr($_SESSION[QT]['admin_phone']).'" onchange="qtFormSafe.not();"/></td></tr>'.PHP_EOL;
if ( isset($_SESSION[QT]['admin_name']) ) echo '<tr><th>'.L('Adm_name').'</th><td><input type="text" name="admin_name" maxlength="255" value="'.qtAttr($_SESSION[QT]['admin_name']).'" onchange="qtFormSafe.not();"/></td></tr>'.PHP_EOL;
if ( isset($_SESSION[QT]['admin_addr']) ) echo '<tr><th>'.L('Adm_addr').'</th><td><input type="text" name="admin_addr" maxlength="255" value="'.qtAttr($_SESSION[QT]['admin_addr']).'" onchange="qtFormSafe.not();"/></td></tr>'.PHP_EOL;
echo '</table>
';
echo '<h2 class="config">'.L('Email_settings').'</h2>
<table class="t-conf">
';
echo '<tr title="'.L('H_Use_smtp').'">
<th>'.L('Use_smtp').'</th>
<td><select name="use_smtp" onchange="toggleSmtp(this.value); qtFormSafe.not();">'.asTags([L('N'),L('Y')],(int)$_SESSION[QT]['use_smtp']).'</select></td>
</tr>
';
echo '<tr>
<th>Smtp host</th>
<td>
<input type="text" id="smtp_host" name="smtp_host" size="28" maxlength="64" value="'.qtAttr($_SESSION[QT]['smtp_host']).'"'.($_SESSION[QT]['use_smtp']=='0' ? 'disabled' : '').' onchange="qtFormSafe.not();"/>
 port <input type="text" id="smtp_port" name="smtp_port" size="4" maxlength="6" value="'.(isset($_SESSION[QT]['smtp_port']) ? qtAttr($_SESSION[QT]['smtp_port']) : '25').'"'.($_SESSION[QT]['use_smtp']=='0' ? 'disabled' : '').' onchange="qtFormSafe.not();"/>
</td>
</tr>
';
echo '<tr>
<th>Smtp username</th>
<td><input type="text" id="smtp_username" name="smtp_username" size="28" maxlength="64" value="'.qtAttr($_SESSION[QT]['smtp_username']).'"'.($_SESSION[QT]['use_smtp']=='0' ? 'disabled' : '').' onchange="qtFormSafe.not();"/></td>
</tr>
';
echo '<tr>
<th>Smtp password</th>
<td><input type="text" id="smtp_password" name="smtp_password" size="28" maxlength="64" value="'.qtAttr($_SESSION[QT]['smtp_password']).'"'.($_SESSION[QT]['use_smtp']=='0' ? 'disabled' : '').' onchange="qtFormSafe.not();"/> <a href="javascript:void(0)" onclick="addArgs(this);" target="_blank">test smtp</a></td>
</tr>
</table>
';
echo '
<p class="submit"><button type="submit" name="ok" value="save">'.L('Save').'</button></p>
</form>';

// HTML END

$oH->scripts[] = 'function toggleSmtp(status){
  document.getElementById("smtp_host").disabled = status==="0";
  document.getElementById("smtp_port").disabled = status==="0";
  document.getElementById("smtp_username").disabled = status==="0";
  document.getElementById("smtp_password").disabled = status==="0";
}
function addArgs(ancor){
  ancor.href = "'.APP.'_adm_smtp.php?h=" + document.getElementById("smtp_host").value + "&p=" + document.getElementById("smtp_port").value + "&u=" + encodeURI(document.getElementById("smtp_username").value) + "&w=" + encodeURI(document.getElementById("smtp_password").value);
}';

include APP.'_adm_inc_ft.php';