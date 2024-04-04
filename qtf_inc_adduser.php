<?php // v4.0 build:20240210
/**
 * Part included in qtf_users and qtf_adm_users
 * @var CHtml $oH
 * @var CSection $oS
 * @var CDatabase $oDB
 * @var string $s
 * @var CHtml $oH
 * @var string $formAddUser
 * @var string $rowCommands
 */

$rowCommands = false;
$certificate = makeFormCertificate('2b174f48ab4d9704934dda56c6997b3a'); //register public key

// SUBMITTED for add
if ( isset($_POST['add']) && $_POST['add']===$certificate ) try {

  // check user grants
  if ( !SUser::isStaff() ) die('Access denied');
  if ( SUser::role()==='M' && $_POST['role']!=='U' ) die('Access denied'); // moderator can't create staff (A|M)
  // check format
  $str = qtAttr(trim($_POST['title']));
  if ( !qtIsPwd($str) ) throw new Exception( L('Username').' '.L('invalid') );
  $strTitle = $str;
  // check password
  $str = qtAttr($_POST['pass']);
  if ( !qtIsPwd($str) ) throw new Exception( L('Password').' '.L('invalid') );
  $strNewpwd = $str;
  // check unique
  if ( $oDB->count( TABUSER." WHERE name=?", [qtDb($strTitle)] )!==0 ) throw new Exception( L('Username').' '.L('already_used') );
  // check mail (required, no multiple)
  if ( empty($_POST['email']) ) throw new Exception( L('Email').' '.L('invalid') );
  $strMail = $_POST['email'];
  // check role
  $str = substr($_POST['role'],0,1);
  if ( !in_array($str,['U','M','A']) ) throw new Exception( L('Role').' '.L('invalid') );
  $strRole = $str;

  // save
  $result = SUser::addUser($strTitle,$strNewpwd,$strMail,$strRole); // return [int]user id or [string]error message
  if ( is_string($result) ) throw new Exception( $result );
  // send email
  if ( isset($_POST['notify']) ) {
    $strSubject='Welcome';
    $strMessage='Please find here after your login and password to access the board '.$_SESSION[QT]['site_name'].PHP_EOL.'Login: %s\nPassword: %s';
    $strFile = qtDirLang().'mail_registred.php';
    if ( file_exists($strFile) ) include $strFile;
    $strMessage = sprintf($strMessage,$strTitle,$strNewpwd);
    qtMail($_POST['email'], $strSubject, $strMessage, QT_HTML_CHAR);
  }
  // exit
  unset($_POST['pass']);
  $_SESSION[QT.'splash'] = L('S_registration');

} catch (Exception $e) {

  // Splash short message and send error to ...inc_hd.php
  $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
  $oH->error = $e->getMessage();

}

// Security: only administrator can create roles A|M
$oH->scripts['newname'] = '<script type="text/javascript" src="bin/js/qt_user_rename.js" data-used="'.L('Already_used').'"></script>';
$formAddUser = '
<div id="tgl-container" class="strongbox add-user article"'.(isset($_POST['title']) ? '' : ' style="display:none"').'>
<form method="post" action="'.$oH->self().'">
<p>'.L('Role').'&nbsp;<select name="role" size="1">'.(SUser::role()==='A' ? '<option value="A">'.L('Role_A').'</option><option value="M">'.L('Role_M').'</option>' : '').'<option value="U" selected>'.L('Role_U').'</option></select></p>
<p>'.qtSVG('user').'&nbsp;<input required id="newname" name="title" type="text" minlength="3" maxlength="24" value="'.(isset($_POST['title']) ? $_POST['title'] : '').'" onfocus="document.getElementById(`newname-error`).innerHTML=``;" placeholder="'.L('Username').'"/></p>
<p id="newname-error" class="error"></p>
<p>'.qtSVG('lock').'&nbsp;<input required name="pass" type="text" maxlength="32" value="'.(empty($_POST['pass']) ? '' : $_POST['pass']).'" placeholder="'.L('Password').'"/></p>
<p>'.qtSVG('envelope').'&nbsp;<input required name="mail" type="email" maxlength="255" value="'.(empty($_POST['email']) ? '' : $_POST['email']).'" placeholder="'.L('Email').'"/></p>
<p><input id="notify" type="checkbox" name="notify"/> <label for="notify">'.L('Send').' '.L('email').'</label>&nbsp; <button type="submit" id="newname-submit" name="add" value="'.$certificate.'">'.L('Add').'</button></p>
</form>
</div>
';