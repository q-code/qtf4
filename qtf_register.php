<?php // v4.0 build:20230430 allows app impersonation [qt f|i|e]

/*
Handles all ui and bi regarding user's profile
Actions GET/POST['a'] are
'rules':    agreed to rules for registration
'in':       registration form
'pwd':      change password
'id':       forgotten password, search id by username
'out':      unregister
'adm-reset':reset password (access admin only)
'reset':    forgotten password
'role':     change user role (access admin only)
'qa':       change secret question/answer
'name':     change username
'sign':     change signature
'ban':      change ban duration (access admin only)
'delete':   delete user (access admin only)
*/

session_start();
/**
* @var CHtml $oH
* @var CDatabase $oDB
*/
require 'bin/init.php';

if ( $_SESSION[QT]['board_offline'] && SUser::role()!=='A' ) die('Board is under maintenance, please wait...');
if ( !isset($_SESSION[QT]['register_coppa']) ) $_SESSION[QT]['register_coppa']='0'; // if coppa disabled

$a = empty($_GET['a']) ? 'rules' : $_GET['a']; if ( !empty($_POST['a']) ) $a = $_POST['a']; // a and id  can come from get or post
if ( empty($a) ) die('Missing argument');
$id = empty($_GET['id']) ? 0 : (int)$_GET['id']; if ( !empty($_POST['id']) ) $id = (int)$_POST['id'];

include translate('lg_reg.php');
$oH->selfurl = APP.'_register.php';
$oH->selfuri = $oH->selfurl.'?a='.$a;
$oH->exiturl = APP.'_index.php';
$oH->exitname = L('Exit');
$frm_attr = 'class=msgbox';
$frm_hd = '';
$frm = array();
$frm_ft = '';
$name = (string)$id;

//=======
switch($a) {
//=======

//=======
case 'rules':

$oH->selfname = L('Register');
$intY = empty($_GET['y']) ? 1970 : (int)$_GET['y'];
$intM = empty($_GET['m']) ? 1 : (int)$_GET['m'];
$intD = empty($_GET['d']) ? 1 : (int)$_GET['d'];
if ( !empty($_POST['y']) ) $intY = (int)trim($_POST['y']);
if ( !empty($_POST['m']) ) $intM = (int)trim($_POST['m']);
if ( !empty($_POST['d']) ) $intD = (int)trim($_POST['d']);

// SUBMIT

if ( isset($_POST['ok']) ) {
  if ( !isset($_POST['agreed']) ) $oH->pageMessage($oH->selfname, L('Rules_not_agreed')); //...
  if ( $_SESSION[QT]['register_coppa'] && !qtIsValiddate($intY*10000+$intM*100+$intD,true,false) ) $oH->error = L('Birthday').' '.L('invalid');
  if ( empty($oH->error) ) $oH->redirect(APP.'_register.php?a=in'.($_SESSION[QT]['register_coppa'] ? '&y='.$intY.'&m='.$intM.'&d='.$intD : '')); //... registration form
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;
}

// FORM

$frm_hd = file_get_contents(getLangDir().'app_rules.txt'); if ( $frm_hd===false ) $frm_hd = 'Unable to read file app_rules.txt';
$frm_hd = '<div id="rules">'.$frm_hd.'</div>';
$frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
$frm[] = '<p class="bold"><span class="cblabel"><input required type="checkbox" id="agreed" name="agreed"'.(isset($_POST['agreed']) ? 'checked' : '').'/><label for="agreed">&nbsp;'.L('Agree').'</label><span></p>';
if ( $_SESSION[QT]['register_coppa'] )
{
$frm[] = '<p>'.L('Birthday').'</p>';
$frm[] = '<p><select name="d" size="1">'.asTags(array_combine(range(1,31),range(1,31)),$intD).'</select>';
$frm[] = '<select name="m" size="1">'.asTags(L('dateMMM.*'),$intM).'</select>';
$frm[] = '<input type="text" id="y" name="y" size="4" maxlength="4" pattern="(19|20)[0-9]{2}" value="'.(empty($intY) ? '' : $intY).'" required/></p>';
}
$frm[] = '<p class="submit right"><button type="submit" name="ok" value="ok">'.L('Proceed').'...</button></p>';
$frm[] = '</form>';

break;

//=======
case 'in':

$oH->selfname = L('Register').($_SESSION[QT]['register_mode']=='backoffice' ? ' ('.L('request').')' : '');
$birthday = '';
$intY = isset($_GET['y']) ? (int)$_GET['y'] : 0;
$intM = isset($_GET['m']) ? (int)$_GET['m'] : 0;
$intD = isset($_GET['d']) ? (int)$_GET['d'] : 0;
if ( !empty($_POST['birthday']) ) {
  $intY = (int)substr($_POST['birthday'],0,4); // post overwrites get
  $intM = (int)substr($_POST['birthday'],4,2);
  $intD = (int)substr($_POST['birthday'],6,2);
}
$strChild = (int)date('Ymd',strtotime('now')) > ($intY+13)*10000+$intM*100+$intD ? '0' : '2';
$birthday = (string)($intY*10000+$intM*100+$intD);

$certificate = makeFormCertificate('2b174f48ab4d9704934dda56c6997b3a');
if ( isset($_POST['ok']) && $_POST['ok']!==$certificate ) die('Unable to check certificate');

// SUBMITTED

if ( isset($_POST['ok']) ) try {

  // pre-checks
  if ( empty($_POST['mail']) ) throw new Exception( L('Email').' '.L('invalid') );
  if ( empty($_POST['username']) ) throw new Exception( L('Username').' '.L('invalid') );
  if ( $_SESSION[QT]['register_safe']==='text' || $_SESSION[QT]['register_safe']==='image' )
  {
    if ( trim($_POST['code'])==='' || strlen($_POST['code'])!=6 ) throw new Exception( L('Type_code') );
  }
  // check name & unique
  if ( $is = SUser::isUsedName($oDB,$_POST['username']) ) throw new Exception($is); // use = (not compare)
  // check mail
  $_POST['mail'] = trim($_POST['mail']);
  if ( !qtIsMail($_POST['mail'])) throw new Exception( L('Email').' '.L('invalid') );
  // check password
  if ( $_SESSION[QT]['register_mode']=='direct' ) {
    if ( !qtIsPwd($_POST['pwd']) || !qtIsPwd($_POST['conpwd']) || $_POST['conpwd']!=$_POST['pwd'] ) throw new Exception( L('Password').' '.L('invalid') );
  }
  // check role
  if ( isset($_POST['role']) ) { $_POST['role']=substr(strtoupper($_POST['role']),0,1); } else { $_POST['role']='U'; }
  if ( !in_array($_POST['role'],array('A','M','U')) ) $_POST['role']='U';
  // check code text/image/reCAPTCHA
  switch($_SESSION[QT]['register_safe']) {
  case 'text':
  case 'image':
    $strCode = strtoupper(strip_tags(trim($_POST['code'])));
    if ( $strCode=='') $oH->error = L('Type_code');
    if ( $_SESSION['textcolor']!=sha1($strCode) ) throw new Exception( L('Type_code') );
    break;
  case 'recaptcha2':
  case 'recaptcha3':
    $strResp = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : false; // error if recaptcha was not used!
    if ( $strResp===false) {
      throw new Exception( L('reCAPTCHA_failed') );
    } else {
      $strResp = file_get_contents( 'https://www.google.com/recaptcha/api/siteverify?secret='.urlencode($_SESSION[QT]['register_safe']==='recaptcha3' ? $_SESSION[QT]['recaptcha3sk'] : $_SESSION[QT]['recaptcha2sk']).'&response='.urlencode($strResp) );
      $arrResp = json_decode($strResp,true);
      $bRes = true;
      if ( empty($arrResp['success']) || $arrResp['success']==='false' ) $bRes=false;
      if ( $bRes ) {
        // if v3, uses score
        if ( $_SESSION[QT]['register_safe']==='recaptcha3' && $arrResp['score']<0.5 ) throw new Exception( L('reCAPTCHA_failed') ); // error if 'score<0.5' is not in the report
      } else {
        throw new Exception( L('reCAPTCHA_failed') ); // error if 'success' is not in the report
      }
    }
  }
  // check secret_a
  $_POST['secret_q'] = qtAttr($_POST['secret_q']);
  $_POST['secret_a'] = qtAttr($_POST['secret_a']);
  if ( empty($_POST['secret_a']) ) throw new Exception( L('Secret_question').' '.L('invalid') );
  // check parentmail
  if ( $_SESSION[QT]['register_coppa']=='1' && $strChild!='0' ) {
    $_POST['parentmail'] = trim($_POST['parentmail']);
    if ( !qtIsMail($_POST['parentmail']) ) throw new Exception( L('Parent_mail').' '.L('Invalid') );
  }
  if ( !isset($_POST['parentmail']) ) $_POST['parentmail'] = '';

  // Register user

  if ( $_SESSION[QT]['register_mode']==='backoffice' ) {
    // Send email
    $strSubject = $_SESSION[QT]['site_name'].' - Registration request';
    $strMessage = "This user request access to the board {$_SESSION[QT]['site_name']}.\nUsername: %s\nEmail: %s";
    $strFile = getLangDir().'mail_request.php';
    if ( file_exists($strFile) ) include $strFile;
    $strMessage = sprintf($strMessage,$_POST['username'],$_POST['mail']);
    qtMail($_SESSION[QT]['admin_email'],$strSubject,$strMessage,QT_HTML_CHAR);
    $oH->pageMessage('', '<h2>'.L('Request_completed').'</h2><p>'.L('Reg_mail').'</p><p><a href="'.Href($oH->exiturl).'">'.$oH->exitname.'</a></p>');
  } else {
    // email code
    if ( $_SESSION[QT]['register_mode']==='email' ) $_POST['pwd'] = 'QT'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);

    $id = SUser::addUser($_POST['username'],$_POST['pwd'],$_POST['mail'],$_POST['role'],$strChild,$_POST['parentmail'],$_POST['secret_q'],$_POST['secret_a'],$birthday); // unset _NewUser
    // note: username is db-encoded, pwd is sha, while sql query

    // send email
    $strSubject = $_SESSION[QT]['site_name'].' - Welcome';
    $strMessage = "Please find here after your login and password to access the board {$_SESSION[QT]['site_name']}.\nLogin: %s\nPassword: %s";
    $strFile = getLangDir().'mail_registred.php';
    if ( file_exists($strFile) ) include $strFile;
    $strMessage = sprintf($strMessage,$_POST['username'],$_POST['pwd']);
    qtMail($_POST['mail'],$strSubject,$strMessage,QT_HTML_CHAR);

    // parent mail
    if ( $_SESSION[QT]['register_coppa']=='1' && $strChild!='0' ) {
      $strSubject = $_SESSION[QT]['site_name'].' - Welcome';
      $strFile = getLangDir().'mail_registred_coppa.php';
      if ( file_exists($strFile) ) include $strFile;
      if ( empty($strMessage) ) $strMessage = "We inform you that your children has registered on the team {$_SESSION[QT]['site_name']}.\nLogin: %s\nPassword: %s\nYour agreement is required to activte this account.";
      $strMessage = sprintf($strMessage,$_POST['username'],$_POST['pwd']);
      $strMessage = wordwrap($strMessage,70,"\r\n");
      qtMail($_POST['parentmail'],$strSubject,$strMessage);
    }

    // END MESSAGE
    if ( $_SESSION[QT]['register_mode']==='email' ) {
      $oH->exiturl = APP.'_index.php';
      $oH->exitname = SLang::translate();
    } else {
      $L['Reg_mail'] = '&nbsp;';
      $oH->exiturl = APP.'_login.php?dfltname='.urlencode($_POST['username']);
      $oH->exitname = L('Login');
    }
    $oH->pageMessage('', '<h2>'.L('S_registration').'</h2><p>'.L('Reg_mail').'</p>');
  }

} catch (Exception $e) {

  $oH->error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;

}

// FORM

$frm_attr = 'class=msgbox formRegister';
if ( !isset($_POST['username']) ) $_POST['username']='';
if ( !isset($_POST['pwd']) ) $_POST['pwd']='';
if ( !isset($_POST['conpwd']) ) $_POST['conpwd']='';
if ( !isset($_POST['mail']) ) $_POST['mail']='';
if ( !isset($_POST['parentmail']) ) $_POST['parentmail']='';
if ( !isset($_POST['secret_q']) ) $_POST['secret_q']='';
if ( !isset($_POST['secret_a']) ) $_POST['secret_a']='';
if ( !isset($_SESSION[QT]['register_mode']) ) $_SESSION[QT]['register_mode']='direct';
if ( !isset($_SESSION[QT]['register_safe']) ) $_SESSION[QT]['register_safe']='text';

if ( $_SESSION[QT]['register_safe']=='text' ) {
  $keycode = 'QT'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
  $_SESSION['textcolor'] = sha1($keycode);
}

// Using reCAPTCHA v2 (sitekey is the public key)
if ( $_SESSION[QT]['register_safe']==='recaptcha2' ) {
  $oH->scripts[] = 'var onloadCallback = function() { grecaptcha.render("recaptcha2",{"sitekey" : "'.$_SESSION[QT]['recaptcha2pk'].'"}); };';
}
// Using reCAPTCHA v3 (sitekey is the public key)
if ( $_SESSION[QT]['register_safe']==='recaptcha3' ) {
  $oH->scripts[] = '<script src="https://www.google.com/recaptcha/api.js?render='.$_SESSION[QT]['recaptcha3pk'].'"></script>';
  $oH->scripts[] = 'grecaptcha.ready(function() {
  grecaptcha.execute("'.$_SESSION[QT]['recaptcha3pk'].'", {action: "registration"}).then(function(token) {
  // pass the token to the backend script for verification
  document.getElementById("g-recaptcha-response").value = token;
  });
});';
}

if ( $_SESSION[QT]['register_coppa']=='1' &&  $strChild!='0' ) {
  $frm_hd = file_get_contents(getLangDir().'app_rules_coppa.txt'); if ( $frm_hd===false ) $frm_hd = 'Unable to read file app_rules_coppa.txt';
  $frm_hd = '<div id="rules">'.$frm_hd.'</div>';
}

$frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
$frm[] = '<div class="flex-sp top">';
$frm[] = '<div style="min-width:65%;padding:0 20px 0 0">';
$frm[] = '<fieldset class="register"><legend>'.L('Username').' '.L('and').' '.L('password').'</legend>';
$frm[] = '<p>'.getSVG('user','class=svg-label|title='.L('Username')).' <input required type="text" id="newname" name="username" size="25" minlength="3" maxlength="64" value="'.$_POST['username'].'" placeholder="'.L('Username').'"/></p><p id="newname-error" class="error"></p>';
if ( $_SESSION[QT]['register_mode']==='direct' ) {
  $frm[] = '<p class="input-pwd">'.getSVG('lock','class=svg-label|title='.L('Password')).' <input required type="password" id="pwd-1" name="pwd" size="25" minlength="4" maxlength="50" value="'.$_POST['pwd'].'" placeholder="'.L('Password').'"/>'.getSVG('eye', 'class=toggle-pwd clickable|onclick=togglePwd(1)|title='.L('Show')).'</p>';
  $frm[] = '<p class="input-pwd">'.getSVG('lock','class=svg-label|title='.L('Confirm_password')).' <input required type="password" id="pwd-2" name="conpwd" size="25" minlength="4" maxlength="50" value="'.$_POST['conpwd'].'" placeholder="'.L('Confirm_password').'"/>'.getSVG('eye', 'class=toggle-pwd clickable|onclick=togglePwd(2)|title='.L('Show')).'</p>';
  $oH->scripts[] = 'function togglePwd(id) {
    var d = document.getElementById("pwd-"+id);
    if ( d.type==="password" ) { d.type="text"; } else { d.type="password"; }
  }';
  $oH->scripts['newname-w'] = 'let w_already_used = "'.L('Already_used').'";';
  $oH->scripts['newname'] = '<script type="text/javascript" src="bin/js/qt_user_rename.js"></script>';
} else {
  $frm[] = L('Password_by_mail').'<br>';
}
$frm[] = '</fieldset>';
$frm[] = '<fieldset class="register"><legend>'.L('Email').'</legend>';
$frm[] = getSVG('envelope','class=svg-label').'&nbsp;<input type="email" name="mail" size="25" maxlength="64" value="'.$_POST['mail'].'" placeholder="'.L('Your_mail').'"/><span id="mail_err" class="error"></span><br>';
if ( $_SESSION[QT]['register_coppa']=='1' && $strChild!='0' )
$frm[] = getSVG('envelope','class=svg-label').'&nbsp;<input type="email" name="parentmail" size="32" maxlength="64" value="'.$_POST['parentmail'].'" placeholder="'.L('Parent_mail').'"/><br>';
$frm[] = '</fieldset>';
$frm[] = '<fieldset class="register"><legend>'.L('Secret_question').'</legend>'.L('H_Secret_question').'<br>';
$frm[] = '<select name="secret_q">'.asTags($L['Secret_q'],$_POST['secret_q']).'</select><br><input required type="text" name="secret_a" size="25" maxlength="255" value="'.qtAttr($_POST['secret_a'],255).'"/>';
$frm[] = '</fieldset>';
if ( !empty($_SESSION[QT]['register_safe']) && $_SESSION[QT]['register_safe']!=='none'  ) {
  $frm[] = '<fieldset class="register captcha"><legend>'.L('Security').'</legend>';
  switch($_SESSION[QT]['register_safe']) {
  case 'image':       $frm[] = '<img id="secu-img-code" width="100" height="35" src="bin/css/qt_icode.php" alt="security" style="text-align:right"/> <input required type="text" name="code" size="8" minlength="6" maxlength="8" value="QT"/><br>'.L('Type_code'); break;
  case 'text':        $frm[] = '<span id="secu-txt-code">'.$keycode.'</span> <input required type="text" id="code" name="code" size="8" minlength="6" maxlength="8" /><br>'.L('Type_code'); break;
  case 'recaptcha2': $frm[] = '<div id="recaptcha2"></div><script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>'; break;
  case 'recaptcha3': $frm[] = '<span class="minor">reCAPTCHA v3<span><input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response"/><input type="hidden" name="registration" value="registration"/>'; break;
  }
  $frm[] = '</fieldset>';
}
$frm[] = '</div>';
$frm[] = '<div class="formHelp article">';
$frm[] = L('Reg_help');
$frm[] = '</div>';
$frm[] = '</div>';
$frm[] = '<p class="submit right"><input type="hidden" name="birthday" value="'.$birthday.'"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.Href($oH->exiturl).'`;">'.L('Cancel').'</button>&nbsp;<button id="newname-submit" type="submit" name="ok" value="'.$certificate.'">'.L('Register').'</button></p>';
$frm[] = '</form>';

break;

//=======
case 'out':

if ( $id<2 ) die('Admin and Visitor cannot be removed');
if ( SUser::id()!==$id && SUser::role()!=='A' ) die('Access denied');
$oH->selfname = L('Unregister');
$oH->selfuri .= '&id='.$id;
$oH->exiturl = APP.'_user.php?id='.$id;

// SUBMITTED

if ( isset($_POST['ok']) ) try {

  // check password
  $str = sha1($_POST['pwd']);
  if ( $oDB->count( TABUSER." WHERE id=$id AND pwd='$str'" )!==1 ) throw new Exception( L('Password').' '.L('invalid') );
  // execute and exit
  if ( !SUser::delete($oDB,$id) ) throw new Exception( 'Unable to delete user' ); // unregister and delete pic
  // exit
  $_SESSION[QT.'splash'] = L('S_delete');
  $oH->redirect(APP.'_login.php?a=out'); // sign out then return to index

} catch (Exception $e) {

  $oH->error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;

}

// FORM

$oDB->query( "SELECT * FROM TABUSER WHERE id=".$id);
if ( $row=$oDB->getRow() ) $name = $row['name'];

$frm_hd = '<div class="user-dlg">
<div class="aside">'.SUser::getPicture($id,'id=userimg').'<p class="ellipsis">'.$name.'</p></div>
';
if ( $row['role']!='U' ) {
$frm[] = '<p>'.$name.L('Unregister_staff').'</p>';
$frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.Href($oH->exiturl).'`;">'.L('Cancel').'</button></p>';
} else {
if ( SUser::id()!==$id ) $frm[] = '<p class="right">'.getSVG('exclamation-triangle', 'style=color:orange').' '.L('Not_your_account').'</p>';
$frm[] = '<p>'.L('H_Unregister').'</p>';
$frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
$frm[] = '<p>'.getSVG('lock','class=svg-label').'&nbsp;<input required type="password" name="pwd" size="20" minlength="4" maxlength="50" placeholder="'.L('Password').'" /></p>';
$frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.Href($oH->exiturl).'`;">'.L('Cancel').'</button>&nbsp;<button type="submit" name="ok" value="ok">'.L('Unregister').'</button></p>';
$frm[] = '</form>';
}
$frm_ft = '</div>';

break;

//=======
case 'pwd':

if ( $id<1 ) die('Invalid id');
if ( SUser::id()!==$id && SUser::role()!=='A' ) die('Access denied');

include 'bin/class/class.phpmailer.php';
$oH->selfname = L('Change_password');
$oH->selfuri .= '&id='.$id;
$oH->exiturl = APP.'_user.php?id='.$id;
$oH->exitname = L('Profile');

// SUBMITTED

if ( isset($_POST['ok']) ) try {

  if ( empty($_POST['oldpwd']) || empty($_POST['newpwd']) || empty($_POST['conpwd']) )  die('Missing data');
  if ( !qtIsPwd($_POST['oldpwd']) ) throw new Exception( L('Old_password').' '.L('invalid') );
  if ( !qtIsPwd($_POST['newpwd']) ) throw new Exception( L('New_password').' '.L('invalid') );
  if ( !qtIsPwd($_POST['conpwd']) ) throw new Exception( L('Confirm_password').' '.L('invalid') );
  if ( $_POST['oldpwd']===$_POST['newpwd'] ) throw new Exception( L('New_password').' '.L('invalid') );
  if ( $_POST['conpwd']!==$_POST['newpwd'] ) throw new Exception( L('Confirm_password').' '.L('invalid') );
  // CHECK OLD PWD
  if ( $oDB->count( TABUSER." WHERE id=? AND pwd=?", [$id,sha1($_POST['oldpwd'])] )!==1 ) throw new Exception( L('Old_password').' '.L('invalid') );
  // EXECUTE
  // save new password
  $oDB->exec( "UPDATE TABUSER SET pwd=? WHERE id=$id", [sha1($_POST['newpwd'])] );
  // send parent email (if coppa)
  if ( $_POST['child']!='0' && $_SESSION[QT]['register_coppa']=='1') {
    $strSubject="New password";
    $strMessage="We inform you that your children has changed his/her password on the board {$_SESSION[QT]['site_name']}.\nLogin: %s\nPassword: %s";
    $strFile = getLangDir().'mail_pwd_coppa.php';
    if ( file_exists($strFile) ) include $strFile;
    $strMessage = sprintf($strMessage,$_POST['name'],$_POST['newpwd']);
    qtMail($_POST['parentmail'],$strSubject,$strMessage,QT_HTML_CHAR);
  }
  // exit
  $_SESSION[QT.'splash'] = L('S_update');
  $oH->redirect($oH->exiturl);

} catch (Exception $e) {

  $oH->error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;

}

// FORM

$oDB->query( "SELECT * FROM TABUSER WHERE id=".$id);
if ( $row=$oDB->getRow() ) {
  $name = $row['name'];
}
$frm_hd = '<div class="user-dlg">
<div class="aside">'.SUser::getPicture($id,'id=userimg').'<p class="ellipsis">'.$name.'</p></div>
';
$frm_attr = 'class=msgbox formPwd';
if ( SUser::id()!==$id ) $frm[] = '<p>'.getSVG('exclamation-triangle', 'style=color:orange').' '.L('Not_your_account').'</p>';
$frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
$frm[] = '<p class="right input-pwd">'.L('Old_password').'&nbsp;<input required id="pwd-1" type="password" name="oldpwd" pattern="^.{4}.*" size="22" maxlength="24" />'.getSVG('eye', 'class=toggle-pwd clickable|onclick=togglePwd(1)|title='.L('Show')).'</p>';
$frm[] = '<p class="right input-pwd">'.L('New_password').'&nbsp;<input required id="pwd-2" type="password" name="newpwd" pattern="^.{4}.*" size="22" maxlength="24" />'.getSVG('eye', 'class=toggle-pwd clickable|onclick=togglePwd(2)|title='.L('Show')).'</p>';
$frm[] = '<p class="right input-pwd">'.L('Confirm_password').'&nbsp;<input required id="pwd-3" type="password" name="conpwd" pattern="^.{4}.*" size="22" maxlength="24" />'.getSVG('eye', 'class=toggle-pwd clickable|onclick=togglePwd(3)|title='.L('Show')).'</p>';
$frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.Href($oH->exiturl).'`;">'.L('Cancel').'</button>&nbsp;<button type="submit" name="ok" value="save">'.L('Save').'</button></p>';
$frm[] = '<input type="hidden" name="name" value="'.$row['name'].'"/>';
$frm[] = '<input type="hidden" name="mail" value="'.$row['mail'].'"/>';
$frm[] = '<input type="hidden" name="child" value="'.$row['children'].'"/>';
$frm[] = '<input type="hidden" name="parentmail" value="'.$row['parentmail'].'"/>';
$frm[] = '</form>';
$oH->scripts[] = 'function togglePwd(id) {
  var d = document.getElementById("pwd-"+id);
  if ( d.type==="password" ) { d.type="text"; } else { d.type="password"; }
}';
$frm_ft = '
</div>';

break;

//=======
case 'id':

$oH->selfname = L('Forgotten_pwd');

// SUBMITTED

if ( isset($_POST['ok']) ) try {

  $_POST['username'] = trim($_POST['username']);
  if ( !qtIsPwd($_POST['username']) ) throw new Exception( L('Username').' '.L('invalid') );
  $oDB->query( "SELECT id FROM TABUSER WHERE name=?", [qtDb($_POST['username'])] );
  if ( $row=$oDB->getRow() ) $oH->redirect( $oH->selfurl.'?a=reset&id='.$row['id'] ); //... reset pwd
  throw new Exception( L('Username').' '.L('invalid') );

} catch (Exception $e) {

  $oH->error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;

}

// FORM username

$frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
$frm[] = '<p>'.L('Reg_pass').'</p>';
$frm[] = '<p>'.getSVG('user','class=svg-label').'&nbsp;<input required type="text" name="username" pattern="^.{2}.*" size="24" maxlength="24" placeholder="'.L('Username').'" /></p>';
$frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.Href($oH->exiturl).'`;">'.L('Cancel').'</button>&nbsp;<button type="submit" name="ok">'.L('Ok').'</button></p>';
$frm[] = '</form>';

break;

//=======
case 'role':

if ( $id<2 ) die('Guest and first administrator are protected');
if ( SUser::role()!=='A' ) die('Access denied');
$oH->selfname = L('Change_role');
$oH->selfuri .= '&id='.$id;
$oH->exiturl = APP.'_user.php?id='.$id;

// SUBMITTED

if ( isset($_POST['ok']) ) {
  //update role
  if ( SUser::role()!=='A' && $_POST['role']=='A' ) die('Access is restricted to administrators only');
  $oDB->exec( "UPDATE TABUSER SET role='" . $_POST['role'] . "' WHERE id=" . $id );
  if ( $_POST['role']==='U' ) $oDB->exec( "UPDATE TABSECTION SET moderator=1, moderatorname='Admin' WHERE moderator=" . $id );
  // exit
  $_SESSION[QT.'splash'] = L('S_update');
  $oH->redirect($oH->exiturl);
}

// FORM

$oDB->query( "SELECT * FROM TABUSER WHERE id=".$id );
if ( $row=$oDB->getRow() ) $name = $row['name'];

$frm_hd = '<div class="user-dlg">
<div class="aside">'.SUser::getPicture($id,'id=userimg').'<p class="ellipsis">'.$name.'</p></div>
';
$frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
$frm[] = '<p>'.$name.' <select name="role" size="1">
<option value="A"'.($row['role']=='A' ? ' selected' : '').(SUser::role()!=='A' ? ' disabled' : '').'>'.L('Role_A').'</option>
<option value="M"'.($row['role']=='M' ? ' selected' : '').'>'.L('Role_M').'</option>
<option value="U"'.($row['role']=='U' ? ' selected' : '').'>'.L('Role_U').'</option>
</select></p>';
$frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.Href($oH->exiturl).'`;">'.L('Cancel').'</button>&nbsp;<button type="submit" name="ok" value="ok">'.L('Ok').'</button></p>';
$frm[] = '</form>';
$frm_ft = '
</div>';

break;

//=======
case 'ban':

if ( $id<2 ) die('Guest and first administrator are protected');
if ( SUser::role()!=='A' ) die('Access denied');
$oH->selfname = L('Ban');
$oH->selfuri .= '&id='.$id;
$oH->exiturl = APP.'_user.php?id='.$id;
$currentban=0;

// SUBMITTED

if ( isset($_POST['ok']) && isset($_POST['t']) ) {
  // ban user
  if ( (int)$_POST['t']<0 ) die('Wrong parameters: delay');
  $b = $oDB->exec( "UPDATE TABUSER SET closed='" . $_POST['t'] . "' WHERE id=".$id);
  // exit
  $_SESSION[QT.'splash'] = $b ? L('S_update') : 'E|'.L('E_failed');
  $oH->redirect($oH->exiturl);
}

// FORM

$oDB->query( "SELECT * FROM TABUSER WHERE id=".$id);
if ( $row=$oDB->getRow() ) {
  $name = $row['name'];
  $role = ' <small>('.L('Role_'.$row['role']).')</small>';
  if ( !empty($row['closed']) && array_key_exists((int)$row['closed'],BAN_DAYS) ) $currentban = (int)$row['closed'];
}

$frm_hd = '<div class="user-dlg">
<div class="aside">'.SUser::getPicture($id,'id=userimg').'<p class="ellipsis">'.$name.'</p></div>';
$frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
$frm[] = '<p>'.$name.$role.'</p>';
$frm[] = '<p>'.L('H_ban').'</p>';
$frm[] = '<p><select name="t" size="1">';
foreach(array_keys(BAN_DAYS) as $k)
$frm[] = '<option value="'.$k.'"'.($k==$currentban ? ' selected' : '').'>'.L('day',BAN_DAYS[$k]).'</option>';
$frm[] = '</select></p>';
$frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.Href($oH->exiturl).'`;">'.L('Cancel').'</button>&nbsp;<button type="submit" name="ok" value="ok">'.L('Ok').'</button></p>';
$frm[] = '</form>';
$frm_ft = '</div>';

break;

//=======
case 'delete':

if ( $id<2 ) die('Wrong argument (guest and first administrator are protected)');
if ( SUser::role()!=='A' ) die('Access denied');
$oH->selfname = L('User_del');
$oH->selfuri .= '&id='.$id;
$oH->exiturl = APP.'_user.php?id='.$id;

// SUBMITTED

if ( isset($_POST['ok']) && isset($_POST['confirm']) ) {
  $b = SUser::delete($oDB,$id);
  // exit
  $_SESSION[QT.'splash'] = $b ? L('S_delete') : 'E|'.L('E_failed');
  $oH->redirect(APP.'_adm_users.php');
}

// FORM

$oDB->query( "SELECT * FROM TABUSER WHERE id=".$id);
if ( $row=$oDB->getRow() ) {
  $name = $row['name'];
  $role = ' <small>('.L('Role_'.$row['role']).')</small>';
}
$frm_hd = '<div class="user-dlg"><div class="aside">'.SUser::getPicture($id,'id=userimg').'<p class="ellipsis">'.$name.'</p></div>';
$frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
$frm[] = '<p><input required type="checkbox" name="confirm"/> '.$name.$role.'</p>';
$frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.Href($oH->exiturl).'`;">'.L('Cancel').'</button>&nbsp;<button type="submit" name="ok" value="delete">'.L('Delete').'</button></p>';
$frm[] = '</form>';
$frm_ft = '</div>';

break;

//=======
case 'adm-reset':

if ( $id<1 ) die('Missing argument');
if ( SUser::role()!=='A' ) die('Access denied');
$oH->selfname = L('Reset_pwd');
$oH->selfuri .= '&id='.$id;
$oH->exiturl = APP.'_user.php?id='.$id;

// SUBMITTED

if ( isset($_POST['ok']) ) {
  // set new password
  $newpwd = 'T'.rand(0,9).rand(0,9).'Q'.rand(0,9).rand(0,9); // T..Q.. allows detecting when a pwd was resetted
  $oDB->exec( 'UPDATE TABUSER SET pwd="'.sha1($newpwd).'" WHERE id="'.$id.'"');
  // send email
  $strSubject='New password';
  $strMessage="Please find here after a new password to access the board {$_SESSION[QT]['site_name']}.\nLogin: %s\nPassword: %s";
  $file = getLangDir().'mail_pwd.php';
  if ( file_exists($file) ) include $file;
  $strMessage = sprintf($strMessage, SUser::name(), $newpwd);
  $row = getUserInfo($id,'children,mail,parentmail'); // can be NULL if user not found
  if ( !empty($row['mail']) ) qtMail($row['mail'],$strSubject,$strMessage,QT_HTML_CHAR);
  // send parent email (if coppa)
  if ( $_SESSION[QT]['register_coppa']=='1' && $row['children']!='0' ) {
    $strSubject='New password';
    $strMessage="Here is then new password of your children.\nLogin: %s\nPassword: %s";
    $file = getLangDir().'mail_pwd_coppa.php';
    if ( file_exists($file) ) { include $file; }
    $strMessage = sprintf($strMessage, SUser::name(), $newpwd);
    if ( !empty($row['parentmail']) ) qtMail($row['parentmail'],$strSubject,$strMessage,QT_HTML_CHAR);
  }
  // exit
  $_SESSION[QT.'splash'] = L('S_update');
  if ( SUser::role()==='A' ) $oH->pageMessage('','<p>'.$strMessage.'</p>'); //...
  $oH->redirect($oH->exiturl);
}

// FORM

$oDB->query( "SELECT * FROM TABUSER WHERE id=".$id);
if ( $row=$oDB->getRow() ) {
  $name = $row['name'];
}
$frm_hd = '<div class="user-dlg"><div class="aside">'.SUser::getPicture($id,'id=userimg').'<p class="ellipsis">'.$name.'</p></div>';
$frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
$frm[] = '<p>'.L('Reset_pwd').' - '.$name.'</p>';
$frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.Href($oH->exiturl).'`;">'.L('Cancel').'</button>&nbsp;<button type="submit" name="ok" value="ok">'.L('Ok').'</button></p>';
$frm[] = '</form>';
$frm_ft = '</div>';

break;

//=======
case 'reset':

if ( $id<1 ) die('Visitor password can not be reset');
$oH->selfname = L('Forgotten_pwd');
$oH->selfuri .= '&id='.$id;

$oDB->query( "SELECT * FROM TABUSER WHERE id=".$id);
$row = $oDB->getRow();

// FORM no secret
if ( empty($row['secret_q']) || empty($row['secret_a']) ) {
  $frm[] = '<p>Secret question not defined.<br>Please contact the webmaster ('.$_SESSION[QT]['admin_email'].') to reset your password.</p>';
  break;
}

// SUBMITTED
if ( isset($_POST['ok']) && !empty($_POST['s']) ) {

  if ( !isset($_SESSION['try']) ) $_SESSION['try']=0;
  $_SESSION['try']++;
  $_POST['s'] = strtolower(trim($_POST['s']));

  // Valid if secret_a or sha1 matches
  if ( $row['secret_a']===sha1($_POST['s']) || strtolower(trim($row['secret_a']))===$_POST['s']  ) {
    include 'bin/class/class.phpmailer.php';
    // send new password
    $newpwd = 'T'.rand(0,9).rand(0,9).'Q'.rand(0,9).rand(0,9);
    $oDB->exec( 'UPDATE TABUSER SET pwd="'.sha1($newpwd).'" WHERE id="'.$id.'"');
    // send email
    $strSubject='New password';
    $strMessage="Please find here after a new password to access the board {$_SESSION[QT]['site_name']}.\nLogin: %s\nPassword: %s";
    $strFile = getLangDir().'mail_pwd.php';
    if ( file_exists($strFile) ) include $strFile;
    $strMessage = sprintf($strMessage, SUser::name(), $newpwd);
    if ( !empty($row['mail']) ) qtMail($row['mail'],$strSubject,$strMessage,QT_HTML_CHAR);
    // send parent email (if coppa)
    if ( $row['children']!='0' ) {
      if ( $_SESSION[QT]['register_coppa']=='1') {
        $strSubject='New password';
        $strMessage="Here is then new password of your children.\nLogin: %s\nPassword: %s";
        $strFile = getLangDir().'mail_pwd_coppa.php';
        if ( file_exists($strFile) ) include $strFile;
        $strMessage = sprintf($strMessage, SUser::name(), $newpwd);
        if ( !empty($row['parentmail']) ) qtMail($row['parentmail'],$strSubject,$strMessage,QT_HTML_CHAR);
      }
    }
    // exit
    if ( SUser::role()==='A' ) $oH->pageMessage('', '<p>'.$strMessage.'</p>');
    $oH->pageMessage('', L('Password_updated').'<br><br>');
  }
  $oH->error = L('E_2');
  if ( $_SESSION['try']>4 ) $oH->pageMessage('', 'Impossible to reset your password. Contact the administrator.');

}

// FORM secret

$frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
$frm[] = '<p>'.L('Reg_pass_reset').'</p>';
$frm[] = '<p>'.$row['secret_q'].'</p>';
$frm[] = '<p><input required type="text" id="secret_a" name="s" size="24" maxlength="255" /></p>';
$frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.Href($oH->exiturl).'`;">'.L('Cancel').'</button>&nbsp;<button type="submit" name="ok" value="ok">'.L('Ok').'</button></p>';
$frm[] = '</form>';
$oH->scripts[] = 'document.getElementById("secret_a").focus();';

break;

//=======
case 'qa':

if ( $id<1 ) die('Missing argument');
$oH->selfname = L('Secret_question');
$oH->selfuri .= '&id='.$id;
$oH->exiturl = APP.'_user.php?id='.$id;
$oH->exitname = L('Profile');

// SUBIMITTED

if ( isset($_POST['ok']) ) {

  $_POST['secret_a'] = strtolower(trim($_POST['secret_a']));
  $_POST['secret_a'] = empty($_POST['secret_a']) ? '' : sha1($_POST['secret_a']);
  // save new password
  $oDB->exec( "UPDATE TABUSER SET secret_q=:sq,secret_a=:sa WHERE id=".$id, [':sq'=>$_POST['secret_q'],':sa'=>$_POST['secret_a']]);

  // exit
  $_SESSION[QT.'splash'] = L('S_update');
  $oH->redirect($oH->exiturl);

}

// FORM

$oDB->query( "SELECT * FROM TABUSER WHERE id=".$id);
$row=$oDB->getRow();
$name = $row['name'];
$secret_q = empty($row['secret_q']) ? '' : $row['secret_q'];

$frm_hd = '<div class="user-dlg"><div class="aside">'.SUser::getPicture($id,'id=userimg').'<p class="ellipsis">'.$name.'</p></div>';
$frm_attr = 'class=msgbox formQa';
if ( SUser::id()!==$id )
$frm[] = '<p>'.getSVG('exclamation-triangle', 'style=color:orange').' '.L('Not_your_account').'</p><br>';
$frm[] = '<p class="center">'.L('H_Secret_question').'</p>';
$frm[] = '<form method="post" action="'.Href($oH->selfuri).'" autocomplete="off">';
$frm[] = '<p class="center"><select name="secret_q">'.asTags($L['Secret_q'],$secret_q).'</select></p>';
$frm[] = '<p class="center"><input required type="text" name="secret_a" size="32" maxlength="255" placeholder="'.(empty($row['secret_a']) ? '' : '*********').'"/></p>';
$frm[] = '<p class="submit"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.Href($oH->exiturl).'`;">'.L('Cancel').'</button>&nbsp;<button type="submit" name="ok" value="save">'.L('Save').'</button></p>';
$frm[] = '</form>';
$frm_ft = '</div>';

break;

//=======
case 'name':

if ( $id<1 ) die('Missing parameters');
if ( SUser::id()!==$id && SUser::role()!=='A' ) die('Access denied');

$oH->selfname = L('Change_name');
$oH->selfuri .= '&id='.$id;
$oH->exiturl = APP.'_user.php?id='.$id;
$oH->exitname = L('Profile');

// SUBMITTED

if ( isset($_POST['ok']) ) try {

  // pre-checks
  if ( empty($_POST['username']) ) die('Missing data');
  // check name & unique
  if ( $result = SUser::isUsedName($oDB,$_POST['username']) ) throw new Exception($result); // use = (not compare)
  // Execute and exit
  if ( !SUser::rename($oDB,$id,$_POST['username']) ) throw new Exception( 'Unable to perform some queries. Rollback done.' );
  $_SESSION[QT.'splash'] = L('S_save');
	$oH->redirect($oH->exiturl);

} catch (Exception $e) {

  $oH->error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;

}

// FORM
$oDB->query( "SELECT * FROM TABUSER WHERE id=".$id);
if ( $row=$oDB->getRow() ) $name = $row['name'];
$frm_hd = '<div class="user-dlg"><div class="aside">'.SUser::getPicture($id,'id=userimg').'<p class="ellipsis">'.$name.'</p></div>';
$frm_attr = 'class=msgbox formName';
if ( SUser::id()!==$id )
$frm[] = '<p>'.getSVG('exclamation-triangle', 'style=color:orange').' '.L('Not_your_account').'</p>';
$frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
$frm[] = '<p class="center">'.getSVG('user','class=svg-label').'&nbsp;<input required type="text" id="newname" name="username" size="20" minlength="3" maxlength="32" placeholder="'.L('Username').'" /></p>';
$frm[] = '<p id="newname-error" class="error center"></p><p class="submit"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.Href($oH->exiturl).'`;">'.L('Cancel').'</button>&nbsp;<button type="submit" id="newname-submit" name="ok" value="ok">'.L('Save').'</button></p>';
$frm[] = '</form>';
$frm_ft = '</div>';

$oH->scripts['newname-w'] = 'let w_already_used = "'.L('Already_used').'";';
$oH->scripts['newname'] = '<script type="text/javascript" src="bin/js/qt_user_rename.js"></script>';

break;

//=======
case 'sign':

if ( $id<1 ) die('Visitor cannot be edited');
if ( SUser::id()!==$id && !SUser::isStaff() ) die('Access denied.');

$oH->selfname = L('Change_signature');
$oH->selfuri .= '&id='.$id;
$oH->exiturl = APP.'_user.php?id='.$id;

// SUBMITTED

if ( isset($_POST['ok']) )
{
  // update user
  if ( empty($oH->error) )
  {
    $oDB->exec( "UPDATE TABUSER SET signature=:sign WHERE id=".$id, [':sign'=>qtAttr($_POST['text'],255)] );
    // exit
    $_SESSION[QT.'splash'] = L('S_update');
    $oH->redirect($oH->exiturl);
  }
  else
  {
    $_SESSION[QT.'splash'] = 'E|'.$oH->error;
  }
}

// FORM

$oDB->query( "SELECT * FROM TABUSER WHERE id=".$id);
if ( $row=$oDB->getRow() )
{
  $name = $row['name'];
}

// check staff edit grants
if ( SUser::id()!==$id && $row['role']==='A' && SUser::role()==='M' ) {
  if ( !defined('QT_STAFFEDITADMIN') ) define('QT_STAFFEDITADMIN',false);
  if ( !QT_STAFFEDITADMIN ) die('Access denied (system coordinator cannot edit system administrator)' );
}

if ( empty($row['signature']) ) $row['signature']='';
$strSign = qtBbc($row['signature']); if ( empty($strSign) ) $strSign='&nbsp;';
if ( QT_BBC ) $oH->scripts[] = '<script type="text/javascript" src="bin/js/qt_bbc.js"></script>';

$frm_hd = '<div class="user-dlg"><div class="aside">'.SUser::getPicture($id,'id=userimg').'<p class="ellipsis">'.$name.'</p></div>';
$frm_attr = 'class=msgbox formSign';
if ( SUser::id()!==$id ) $frm[] = '<p>'.getSVG('exclamation-triangle', 'style=color:orange').' '.L('Not_your_account').'</p>';
$frm[] = '<p>'.L('H_no_signature').'</p>';
$frm[] = '<h2>'.L('Signature').'</h2>';
$frm[] = '<div id="signature-preview">'.$strSign.'</div>';
$frm[] = '<h2>'.$oH->selfname.'</h2>'.( !empty($oH->error) ? '<p class="error">'.$oH->error.'</p>' : '');
$frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
$frm[] = '<div id="signature">';
$frm[] = '<div class="bbc-bar">'.bbcButtons(3).'</div>';
$frm[] = '<textarea id="text" name="text" rows="5">'.$row['signature'].'</textarea>';
$frm[] = '<p class="submit"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.Href($oH->exiturl).'`;">'.L('Cancel').'</button>&nbsp;<button type="submit" name="ok" value="save">'.L('Save').'</button></p>';
$frm[] = '</div>';
$frm[] = '</form>';
$frm_ft = '</div>';
$oH->scripts[] = 'document.querySelectorAll("#signature-preview a").forEach( anchor => {anchor.href="javascript:void(0)"; anchor.target="";} );';

break;

//=======
default: die('Unknown command');

//=======
}
//=======


include APP.'_inc_hd.php';

if ( !empty($frm_hd) ) echo $frm_hd.PHP_EOL;

CHtml::msgBox($oH->selfname,$frm_attr);
echo PHP_EOL.implode(PHP_EOL,$frm).PHP_EOL;
CHtml::msgBox('/');

if ( !empty($frm_ft) ) echo $frm_ft.PHP_EOL;

include APP.'_inc_ft.php';