<?php // v4.0 build:20240210

session_start();
/**
* @var CHtml $oH
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php'; if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');

function return_bytes($val) {
    $val = strtolower(trim($val));
    if ( qtCtype_digit($val) ) return (int)$val;
    $unit = substr($val,-1);
    $val = (int)substr($val,0,-1);
    switch($unit)
    {
    case 'g': $val *= 1024;
    case 'm': $val *= 1024;
    case 'k': $val *= 1024;
    }
    return $val;
}
function max_fileuploadbytes() {
    //select maximum upload size
    $max_upload = return_bytes(ini_get('upload_max_filesize'));
    //select post limit
    $max_post = return_bytes(ini_get('post_max_size'));
    //select memory limit
    $memory_limit = return_bytes(ini_get('memory_limit'));
    // return the smallest of them, this defines the real limit
    return min($max_upload, $max_post, $memory_limit)/1024/1024;
}

// INITIALISE

$_SESSION[QT]['visitor_right'] = (int)$_SESSION[QT]['visitor_right'];
if ( !isset($_SESSION[QT]['show_memberlist']) ) $_SESSION[QT]['show_memberlist'] = 'U';
if ( !isset($_SESSION[QT]['recaptcha2pk']) ) $_SESSION[QT]['recaptcha2pk']=''; //public key (site key)
if ( !isset($_SESSION[QT]['recaptcha2sk']) ) $_SESSION[QT]['recaptcha2sk']=''; //secret key (validation api access key)
if ( !isset($_SESSION[QT]['recaptcha3pk']) ) $_SESSION[QT]['recaptcha3pk']=''; //public key (site key)
if ( !isset($_SESSION[QT]['recaptcha3sk']) ) $_SESSION[QT]['recaptcha3sk']=''; //secret key (validation api access key)

$oH->name = L('Board_security');
$parentname = L('Settings');
switch(QDB_SYSTEM) {
  //Note utf-8 coding may consume 4bytes/character, that's why MAXCHAR < varchar limit of the database
  case 'pdo.sqlsrv' :
  case 'sqlsrv' :
  case 'pdo.pg' :
  case 'pg' : define('MAX_K',6); break; // sqlsrv and postgesql use varchar(8000)
  case 'pdo.oci' :
  case 'oci' : define('MAX_K',3); break; // oracle uses varchar(4000)
  default : define('MAX_K',10); break; // mysql text(64K), sqlite text(>1MB)
}

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) ) try {

  // Public access level
  if ( $oDB->getSetting('show_memberlist','missing')==='missing' ) $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('show_memberlist','U')" );
  $_SESSION[QT]['visitor_right'] = $_POST['pal']; // 0=view nothing...6=create subject
  $_SESSION[QT]['show_memberlist'] = $_POST['memberlist']; // A|M|U|V
  $oDB->updSetting(['visitor_right','show_memberlist']);

  // Login authority
  if ( !empty($_POST['login_addon']) ) {
    if ( $oDB->getSetting('login_addon','missing')==='missing' ) $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('login_addon','0')" );
    if ( empty($_SESSION[QT][$_POST['login_addon']]) ) throw new Exception( 'Use the module administration page to configure your settings' );
    $_SESSION[QT]['login_addon'] = $_POST['login_addon'];
    $oDB->updSetting('login_addon');
  }
  // Registration
  $_SESSION[QT]['register_mode'] = $_POST['regmode'];
  $_SESSION[QT]['register_safe'] = $_POST['regsafe'];
  $_SESSION[QT]['register_coppa'] = $_POST['regcoppa'];
  $oDB->updSetting(['register_mode','register_safe','register_coppa']);
  if ( $_POST['regsafe']==='recaptcha2' ) {
    if ( empty($_POST['api2pk']) || empty($_POST['api2sk']) ) throw new Exception( 'reCaptcha keys '.L('not_empty') );
    if ( $oDB->getSetting('recaptcha2pk','missing')==='missing' ) $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('recaptcha2pk','')" );
    if ( $oDB->getSetting('recaptcha2sk','missing')==='missing' ) $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('recaptcha2sk','')" );
    $_SESSION[QT]['recaptcha2pk'] = trim(qtDb($_POST['api2pk'])); $oDB->updSetting('recaptcha2pk');
    $_SESSION[QT]['recaptcha2sk'] = trim(qtDb($_POST['api2sk'])); $oDB->updSetting('recaptcha2sk');
  }
  if ( $_POST['regsafe']=='recaptcha3' ) {
    if ( empty($_POST['api3pk']) || empty($_POST['api3sk']) ) throw new Exception( 'reCaptcha keys '.L('not_empty') );
    if ( $oDB->getSetting('recaptcha3pk','missing')==='missing' ) $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('recaptcha3pk','')" );
    if ( $oDB->getSetting('recaptcha3sk','missing')==='missing' ) $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('recaptcha3sk','')" );
    $_SESSION[QT]['recaptcha3pk'] = trim(qtDb($_POST['api3pk'])); $oDB->updSetting('recaptcha3pk');
    $_SESSION[QT]['recaptcha3sk'] = trim(qtDb($_POST['api3sk'])); $oDB->updSetting('recaptcha3sk');
  }

  $format = qtExplode('mime=0;width=100;height=100'); //default values
  if ( !empty($_POST['mime']) ) $format['mime']   = $_POST['mime'];
  if ( !empty($_POST['maxw']) ) $format['width']  = $_POST['maxw'];
  if ( !empty($_POST['maxh']) ) $format['height'] = $_POST['maxh'];
  $_SESSION[QT]['formatpicture'] = qtImplode($format,';');
  $oDB->updSetting('formatpicture');

  // upload
  $_SESSION[QT]['upload'] = $_POST['upload'];
  $oDB->updSetting('upload');
  if ( $_SESSION[QT]['upload']!=='0' && isset($_POST['uploadsize']) ) {
    $i = (int)trim($_POST['uploadsize']);
    if ( !qtIsBetween($i,1,QT_UPLOAD_MAXSIZE) ) throw new Exception( L('Allow_upload').' '.L('invalid').' (1-'.QT_UPLOAD_MAXSIZE.' Mb)' );
    $_SESSION[QT]['upload_size'] = (string)($i*1024);
    $oDB->updSetting('upload_size');
  }

  $_SESSION[QT]['show_calendar'] = $_POST['show_calendar'];
  $_SESSION[QT]['show_stats'] = $_POST['show_stats'];
  $_SESSION[QT]['tags'] = $_POST['tags'];
  $oDB->updSetting(['show_calendar','show_stats','tags']);

  $i = (int)trim($_POST['ppt']); if ( !qtIsBetween($i,50,1000) ) throw new Exception( L('Max_replies_per_items').' '.L('invalid').' (10-999)' );
  $_SESSION[QT]['posts_per_item'] = (string)$i;
  $i = (int)trim($_POST['cpp']); if ( !qtIsBetween($i,1,MAX_K) ) throw new Exception( L('Max_char_per_post').' '.L('invalid').' (1-'.MAX_K.')' );
  $_SESSION[QT]['chars_per_post'] = (string)($i*1000);
  $i = (int)trim($_POST['lpp']); if ( !qtIsBetween($i,50,1000) ) throw new Exception( L('Max_line_per_post').' '.L('invalid').' (10-999)' );
  $_SESSION[QT]['lines_per_post'] = (string)$i;
  $i = (int)trim($_POST['ppd']); if ( !qtIsBetween($i,50,1000) ) throw new Exception( L('Max_post_per_user').' '.L('invalid').' (1-999)' );
  $_SESSION[QT]['posts_per_day'] = (string)$i;
  $oDB->updSetting(['posts_per_item','chars_per_post','lines_per_post','posts_per_day']);

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
include 'qtf_adm_inc_hd.php';

echo '
<form class="formsafe" method="post" action="'.$oH->php.'">
<h2 class="config">'.L('Public_access_level').'</h2>
<table class="t-conf">
<tr title="'.L('H_Visitors_can').'">
<th>'.L('Visitors_can').'</th>
<td><select name="pal">'.qtTags(L('Pal.*'), (int)$_SESSION[QT]['visitor_right']).'</select></td>
</tr>
<tr>
<th>'.L('View_memberlist').'</th>
<td><select name="memberlist">'.qtTags(['A'=>L('Role_A').' '.L('only'),'M'=>L('Role_M'),'U'=>L('Role_U'),'V'=>L('Role_V')], $_SESSION[QT]['show_memberlist']).'</select></td>
</tr>
</table>
';

if ( !isset($_SESSION[QT]['login_addon']) ) $_SESSION[QT]['login_addon'] = '0'; // By default, no addon
// List of possible authorities: modules registered with param "m_{modulename}:login"
// index is "m_{modulename}", value is the alias
// index '0' means no addon and is called the internal authority
$arrAddons = [0=>'Internal authority (default)'];
foreach($oDB->getSettings('param LIKE "m_%:login"') as $param=>$alias) {
  $addon = substr($param,0,-6); // drop the ":login"
  if ( isset($_SESSION[QT][$addon]) && $_SESSION[QT][$addon]!=='0' ) $arrAddons[$addon] = 'Module '.$alias;
}
echo '<h2 class="config">'.L('Registration').'</h2>
<table class="t-conf">
<tr>
<th>'.L('Authority').'</th>
<td>'.(count($arrAddons)===1 ? $arrAddons[0] : '<select id="login_addon" name="login_addon">'.qtTags($arrAddons, $_SESSION[QT]['login_addon']).'</select>').'</td>
</tr>
<tr title="'.L('Reg_mode').'">
<th>'.L('Reg_mode').'</th>
<td>
<select id="regmode" name="regmode">
'.qtTags(['direct'=>'Online (direct)','email'=>'Online (with e-mail checking)','backoffice'=>'Back-office request'],$_SESSION[QT]['register_mode']).'
</select>
</tr>
';
$use_gd = extension_loaded('gd') && function_exists('gd_info');
echo '<tr>
<th>'.L('Reg_security').'</th>
<td>
<select id="regsafe" name="regsafe" onchange="regsafeChanged(this.value);">
<optgroup label="'.L('Internal').'">
<option value="none"'.($_SESSION[QT]['register_safe']=='none' ? ' selected' : '').'>'.L('None').'</option>
<option value="text"'.($_SESSION[QT]['register_safe']=='text' ? ' selected' : '').'>'.L('Text_code').'</option>
<option value="image"'.($_SESSION[QT]['register_safe']=='image' ? ' selected' : '').($use_gd ? ' ': ' disabled').'>'.L('Image_code').'</option>
</optgroup>
<optgroup label="'.L('Online_services').'">
<option value="recaptcha2"'.($_SESSION[QT]['register_safe']==='recaptcha2' ? ' selected' : '').'>reCAPTCHA v2</option>
<option value="recaptcha3"'.($_SESSION[QT]['register_safe']==='recaptcha3' ? ' selected' : '').'>reCAPTCHA v3</option>
</optgroup>
</select> *
</tr>
';
echo '<tr id="recaptcha2" style="display:'.($_SESSION[QT]['register_safe']==='recaptcha2' ? 'table-row' : 'none').'">
<th>reCAPTCHA api keys</th>
<td>
<input type="text" id="api2pk" name="api2pk" size="24" maxlength="255" value="'.$_SESSION[QT]['recaptcha2pk'].'" placeholder="reCAPTCHA v2 site key" title="reCAPTCHA v2 site key"/>
<input type="text" id="api2sk" name="api2sk" size="24" maxlength="255" value="'.$_SESSION[QT]['recaptcha2sk'].'" placeholder="reCAPTCHA v2 secret key" title="reCAPTCHA v2 secret key"/>
</td>
</tr>
';
echo '<tr id="recaptcha3" style="display:'.($_SESSION[QT]['register_safe']==='recaptcha3' ? 'table-row' : 'none').'">
<th>reCAPTCHA api keys</th>
<td>
<input type="text" id="api3pk" name="api3pk" size="24" maxlength="255" value="'.$_SESSION[QT]['recaptcha3pk'].'" placeholder="reCAPTCHA v3 site key" title="reCAPTCHA v3 site key"/>
<input type="text" id="api3sk" name="api3sk" size="24" maxlength="255" value="'.$_SESSION[QT]['recaptcha3sk'].'" placeholder="reCAPTCHA v3 secret key" title="reCAPTCHA v3 secret key"/>
</td>
</tr>
';
echo '<tr title="'.L('H_Register_coppa').'">
<th>'.L('Register_coppa').'</th>
<td>
<select id="regcoppa" name="regcoppa">
<option value="0"'.($_SESSION[QT]['register_coppa']==='0' ? ' selected' : '').'>'.L('N').'</option>
<option value="1"'.($_SESSION[QT]['register_coppa']==='1' ? ' selected' : '').'>'.L('Y').'</option>
</select>
</tr>
';
echo '<tr>
<td colspan="2" class="asterix">* '.L('H_Reg_security').'</td>
</tr>
</table>
';

echo '<h2 class="config">'.L('Security_rules').'</h2>
<table class="t-conf">
';
echo '<tr title="'.L('H_Max_replies_per_items').'">
<th>'.L('Max_replies_per_items').'</th>
<td><input required type="number" id="ppt" name="ppt" min="50" max="1000" step="50" value="'.$_SESSION[QT]['posts_per_item'].'"/> / '.strtolower(L('Item')).'</td>
</tr>
';
echo '<tr title="'.L('H_hacking_day').'">
<th>'.L('Max_post_per_user').'</th>
<td><input required type="number" id="ppd" name="ppd" min="50" max="1000" step="50" value="'.$_SESSION[QT]['posts_per_day'].'"/> / '.strtolower(L('Day')).'</td>
</tr>
';
echo '<tr title="'.L('H_Max_char_per_post').'">
<th>'.L('Max_char_per_post').'</th>
<td><input required type="number" id="cpp" name="cpp" min="1" max="'.MAX_K.'" value="'.($_SESSION[QT]['chars_per_post']/1000).'"/> x 1000</td>
</tr>
';
echo '<tr title="'.L('H_Max_line_per_post').'">
<th>'.L('Max_line_per_post').'</th>
<td><input required type="number" id="lpp" name="lpp" min="50" max="1000" step="50" value="'.$_SESSION[QT]['lines_per_post'].'"/></td>
</tr>
</table>
';
if ( empty($_SESSION[QT]['formatpicture']) ) $_SESSION[QT]['formatpicture']='mime=0;width=100;height=100';
$format = qtExplode($_SESSION[QT]['formatpicture']); // mime;width;height;kb (mime 0 means avatar not allowed)
echo '<h2 class="config">'.L('User_interface').'</h2>
<table class="t-conf">
<tr>
<th>'.L('Allow_picture').'</th>
<td>
<select id="avatar" name="mime" onchange="toggleParams(this.id,this.value);">
<option value="0"'.(empty($format['mime']) ? ' selected' : '').'>'.L('N').'</option>
<option value="jpg jpeg"'.($format['mime']=='jpg jpeg' ? ' selected' : '').'>'.L('Y').' ('.L('Jpg_only').')</option>
<option value="gif jpg jpeg png"'.($format['mime']=='gif jpg jpeg png' ? ' selected' : '').'>'.L('Y').' ('.L('Gif_jpg_png').')</option>
</select>
<div id="avatar-params" style="display:'.(empty($format['mime']) ? 'none' : 'inline-block').'">
Max.<input required type="number" id="avatarwidth" name="maxw" min="20" max="200" step="10" value="'.$format['width'].'" title="'.L('width').'"/>x<input required type="number" id="avatarheight" name="maxh" min="20" max="200" step="10" value="'.$format['height'].'" title="'.L('height').'"/>px
</div>
</td>
</tr>
';
$arr = array(
  '0'=>L('N'),
  'M'=>L('Y').' ('.L('Role_M').')',
  'U'=>L('Y').' ('.L('Role_U').')',
  'V'=>L('Y').' ('.L('Role_V').')');
$i = round((int)$_SESSION[QT]['upload_size']/1024);
echo '<tr title="'.L('H_Allow_upload').'">
<th>'.L('Allow_upload').'</th>
<td>
<select id="upload" name="upload" onchange="toggleParams(this.id,this.value);">
'.qtTags($arr,$_SESSION[QT]['upload']).'
</select> <div id="upload-params" style="display:'.($_SESSION[QT]['upload']=='0' ? 'none' : 'inline-block').'">Max.<input required type="number" id="uploadsize" name="uploadsize" min="1" max="'.QT_UPLOAD_MAXSIZE.'" value="'.$i.'"/>Mb<small> (server limit '.(function_exists('ini_get') ? max_fileuploadbytes().'Mb' : 'unknown').')</small></div>
</td>
</tr>
';
echo '<tr title="'.L('H_Show_calendar').'">
<th>'.L('Show_calendar').'</th>
<td>
<select id="show_calendar" name="show_calendar">'.qtTags($arr,$_SESSION[QT]['show_calendar']).'</select>
</td>
</tr>
';
$arr = array(
  'A'=>L('Role_A'),
  'M'=>L('Role_M'),
  'U'=>L('Role_U'),
  'V'=>L('Role_V'));
echo '<tr title="'.L('H_Show_statistics').'">
<th>'.L('Show_statistics').'</th>
<td>
<select name="show_stats">'.qtTags($arr,$_SESSION[QT]['show_stats']).'</select>
</td>
</tr>
';

echo '<tr>
<th>'.L('Allow_tags').'</th>
<td><select name="tags">
'.qtTags($arr,$_SESSION[QT]['tags']).'
</select> *</td>
</tr>
<tr>
<td colspan="2" class="asterix">* '.L('H_Allow_tags').'</td>
</tr>
</table>
';
echo '<p class="submit"><button type="submit" name="ok" value="save">'.L('Save').'</button></p>
</form>';

// HTML END

include 'qtf_adm_inc_ft.php';