<?php // v4.0 build:20230205

session_start();
/**
 * @var string $error
 * @var string $strPrev
 * @var string $strNext
 * @var string $urlPrev
 * @var string $urlNext
 */
include 'init.php';
$error='';
$urlPrev = APP.'_setup_2.php';
$urlNext = APP.'_setup_4.php';

function QTisEmail($str)
{
  if ( !is_string($str) ) die('QTisEmail: arg #1 must be a string');
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

include APP.'_setup_hd.php';

// Submitted

if ( !empty($_POST['admin_email']) )
{
  if ( QTisEmail($_POST['admin_email']) )
  {
    $_SESSION[QT]['admin_email'] = $_POST['admin_email'];
    $oDB->updSetting('admin_email');
    if ( empty($oDB->error) )
    {
    echo '<div class="setup_ok">',L('S_save'),'</div>';
    }
    else
    {
      echo '<div class="setup_err">',sprintf (L('E_connect'),QDB_DATABASE,QDB_HOST),'</div>';
    }
  }
  else
  {
  echo '<div class="setup_err">Invalid e-mail</div>';
  }
  // save the url
  $strURL = 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 's' : '').'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
  $strURL = substr($strURL,0,-24);
  $oDB->updSetting('site_url', $strURL);
}

// Form

echo '<h2>',L('Board_email'),'</h2>
<form method="post" name="install" action="',APP,'_setup_3.php">
<table>
<tr>
<td>
<p><input required type="email" name="admin_email" value="',$_SESSION[QT]['admin_email'],'" size="30" maxlength="100"/> <button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</td>
<td style="width:40%"><div class="setup_help">',L('Help_3'),'</div></td>
</tr>
</table>
</form>
';

include APP.'_setup_ft.php';