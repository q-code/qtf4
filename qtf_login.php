<?php // v4.0 build:20230430

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';

include translate('lg_reg.php');

// --------
// SUBMITTED for loggout. To redirect to login page use url '?a=out&r=in'
// --------

if ( isset($_GET['a']) && $_GET['a']==='out' ) {
  $str = L('Goodbye');
  SUser::logOut(); // destroy session
  // REBOOT
  session_start();
  $_SESSION[QT.'splash'] = 'i|'.$str;
  $oH->redirect( APP.'_'.(isset($_GET['r']) && $_GET['r']==='in' ? 'login' : 'index').'.php' );
}

// --------
// INITIALISE
// --------

$oH->selfurl = 'qtf_login.php';
$oH->selfname = L('Login');
$strName = isset($_GET['dfltname']) ? qtAttr($_GET['dfltname']) : '';
$certificate = makeFormCertificate('9db03580c02d0ac85d9d2d6611098fac');

// --------
// SUBMITTED for login
// --------

if ( isset($_POST['ok']) ) try {

  // check certificate
  if ( $_POST['ok']!==$certificate ) die('Unable to check certificate');
  // check values
  $strName = trim($_POST['usr']); // Trim required. qtDb encode is performed while sql query
  if ( !qtIsPwd($strName) ) throw new Exception( L('Username').' '.L('invalid') );
  $strPwd = $_POST['pwd']; // Trim required. Sha is performed while sql query
  if ( !qtIsPwd($strPwd) ) throw new Exception( L('Password').' '.L('invalid') );

  // LOGIN
  SUser::logIn($strName,$strPwd,isset($_POST['remember'])); //name and pwd qtDb-encode is performed while sql query
  if ( !SUser::auth() ) throw new Exception( L('E_login') );

  // check registered if children and coppa active (0=Adult, 1=Kid aggreed, 2=Kid not aggreed)
  if ( $_SESSION[QT]['register_coppa']=='1' )
  {
    $arrCoppa = SUser::coppa();
    if ( isset($arrCoppa['children']) && $arrCoppa['children']=='2' ) {
      $oH->exitname = SLang::translate();
      SUser::unsetSession();
      $oH->pageMessage('', '<h2>'.L('Welcome').' '.$strName.'</h2>'.L('E_10').'<br>'.L('No_parental_confirm')); //...
    }
  }

  // check ban, unban and secret question
  SUser::loginPostProc($oDB); //... can exit to register specific page

  // end message
  $_SESSION[QT.'splash']=L('Welcome').' '.$strName;
  $oH->redirect('qtf_index.php');

} catch (Exception $e) {

  $oH->error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;

}

// --------
// HTML BEGIN
// --------

include 'qtf_inc_hd.php';

CHtml::msgBox($oH->selfname, 'class=msgbox formLogin');

$str = L('Username').(QT_LOGIN_WITH_EMAIL ? ' '.L('or').' '.L('email') : '');
echo '<form method="post" action="'.Href($oH->selfurl).'">'.PHP_EOL;
echo '<p><a href="'.Href('qtf_register.php?a=id').'">'.L('Forgotten_pwd').'</a></p>';
echo '<p title="'.$str.'">'.getSVG('user','class=svg-label').' <input required type="text" id="usr" name="usr" size="24" minlength="4" maxlength="50" value="'.qtAttr($strName).'" placeholder="'.$str.'"/></p>';
echo '<p class="input-pwd" title="'.L('Password').'">'.getSVG('lock','class=svg-label').' <input required type="password" id="pwd-1" name="pwd" size="24" minlength="4" maxlength="50" placeholder="'.L('Password').'" />'.getSVG('eye', 'class=toggle-pwd clickable|onclick=togglePwd(1)|title='.L('Show')).'</p>';
echo '<p class="submit">';
if ( QT_REMEMBER_ME ) echo '<span class="cblabel"><input type="checkbox" id="remember" name="remember"/>&nbsp;<label for="remember">'.L('Remember'),'</label></span>&nbsp;&nbsp;';
echo '<button type="submit" name="ok" value="'.$certificate.'">'.L('Ok').'</button></p>';
echo '</form>';

CHtml::msgBox('/');

// HTML END

$oH->scripts[] = 'let doc = document.getElementById("usr"); doc.focus(); if ( doc.value.length>1 ) document.getElementById("pwd-1").focus();
function togglePwd(id) {
  let d = document.getElementById("pwd-"+id);
  if ( d.type==="password" ) { d.type="text"; } else { d.type="password"; }
}';

include 'qtf_inc_ft.php';