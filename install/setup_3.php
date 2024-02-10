<?php // v4.0 build:20240210

session_start();
/**
 * @var string $strPrev
 * @var string $strNext
 * @var string $urlPrev
 * @var string $urlNext
 */
include 'init.php';
$error='';
$self = 'setup_3';
$urlPrev = 'setup_2.php';
$urlNext = 'setup_4.php';

// Read admin_email setting
try {
  $oDB = new CDatabase();
  $oDB->query( "SELECT param,setting FROM TABSETTING WHERE param='admin_email'" );
} catch (Exception $e) {
  $error = $e->getMessage(); //...
}
while ($row = $oDB->getRow()) $_SESSION[QT][$row['param']] = (string)$row['setting'];
if ( !isset($_SESSION[QT]['admin_email']) ) $_SESSION[QT]['admin_email']='';

// ------
// HTML BEGIN
// ------
include 'setup_hd.php';

// Submitted
if ( !empty($_POST['admin_email']) ) try {

  $_SESSION[QT]['admin_email'] = $_POST['admin_email'];
  $oDB->updSetting('admin_email',null,true);
  // save the url
  $strURL = 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 's' : '').'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
  $strURL = substr($strURL,0,-24);
  $oDB->updSetting('site_url',$strURL,true);
  echo '<p class="result ok">'.L('S_save').'</p>';

} catch (Exception $e) {

  echo '<p class="result err">'.$e->getMessage().'</p>';

}

// Form

echo '<form method="post" name="install" action="setup_3.php">
<h1>'.L('Board_email').'</h1>
<p><input required type="email" name="admin_email" value="'.$_SESSION[QT]['admin_email'].'" size="30" maxlength="100"/> <button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</form>
';

$aside = L('Help_3');

include 'setup_ft.php';