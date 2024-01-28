<?php // v4.0 build:20230618

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

function qtIsEmail($str)
{
  if ( !is_string($str) ) die('qtIsEmail: arg #1 must be a string');
  if ( $str!=trim($str) ) return false;
  if ( $str!=strip_tags($str) ) return false;
  if ( !preg_match("/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i",$str) ) return false;
  return true;
}

// Read admin_email setting
$oDB = new CDatabase();
$oDB->query( "SELECT param,setting FROM TABSETTING WHERE param='admin_email'" );
while ($row = $oDB->getRow())
{
$_SESSION[QT][$row['param']]=strval($row['setting']);
}
if ( !isset($_SESSION[QT]['admin_email']) ) $_SESSION[QT]['admin_email']='';

// --------
// HTML BEGIN
// --------

include 'setup_hd.php';

// Submitted

if ( !empty($_POST['admin_email']) ) {
  if ( qtIsEmail($_POST['admin_email']) ) {
    $_SESSION[QT]['admin_email'] = $_POST['admin_email'];
    $oDB->exec( "UPDATE TABSETTING SET setting='".$_POST['admin_email']."' WHERE param='admin_email'" );
    if ( empty($oDB->error) ) {
    echo '<p class="is_ok">'.L('S_save').'</p>';
    } else {
      echo '<p class="is_err">'.sprintf (L('E_connect'),QDB_DATABASE,QDB_HOST).'</p>';
    }
  } else {
    echo '<p class="is_err">Invalid e-mail</p>';
  }
  // save the url
  $strURL = 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 's' : '').'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
  $strURL = substr($strURL,0,-24);
  $oDB->exec( "UPDATE TABSETTING SET setting='$strURL' WHERE param='site_url'" );
}

// Form

echo '<form method="post" name="install" action="setup_3.php">
<h1>'.L('Board_email').'</h1>
<p><input required type="email" name="admin_email" value="',$_SESSION[QT]['admin_email'],'" size="30" maxlength="100"/> <button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</form>
';

$aside = L('Help_3');

include 'setup_ft.php';