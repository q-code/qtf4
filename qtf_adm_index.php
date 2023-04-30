<?php // v4.0 build:20230430 allows app impersonation [qt f|i|e]

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php'; if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');

// INITIALISE

$oH->selfurl = APP.'_adm_index.php';
$oH->selfname = L('Board_status');
$oH->selfparent = L('Board_info');

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) && isset($_POST['offline']) ) try {

  // check admin email and forum url
  if ( !qtIsMail($_SESSION[QT]['admin_email']) ) throw new Exception('Email not yet defined...');
  if ( strlen($_SESSION[QT]['site_url'])<8 ) throw new Exception('Site url not yet defined...');
  // update
  $_SESSION[QT]['board_offline'] = $_POST['offline']==='0' ? '0' : '1'; // only 0|1
  $oDB->updSetting('board_offline');
  // Successfull end
  SMem::set('settingsage',time());
  $_SESSION[QT.'splash'] = L('S_save');

} catch (Exception $e) {

  $oH->exiturl = APP.'_adm_site.php';
  $oH->exitname = L('Board_general');
  $oH->pageMessage(L('Settings'), '<p>Mandatory setting missing:<br>'.$e->getMessage().'</p>', 'admin');
  $_SESSION[QT.'splash'] = 'E|'.$e->getMessage();

}

// --------
// OTHER SUBMIT
// --------

if ( isset($_GET['cmd']) && $_GET['cmd']==='decrypt' && !empty($_GET['file']) ) {

  if ( is_dir($_GET['file']) && rename($_GET['file'],'install') ) {
    $_SESSION[QT.'splash'] = 'Install decrypted';
  } else {
    $_SESSION[QT.'splash'] = 'E|Unable to rename folder install !';
  }

}

// FUNCTION
function qtEncrypt(string $key=APP, string $str='install')
{
  $key = hash('sha256', $key);
  $iv = substr(hash('sha256','5fgf5HJ5g27'), 0, 16); // sha256 is hash_hmac_algo
  $output = openssl_encrypt($str, 'AES-256-CBC', $key, 0, $iv);
  return base64_encode($output);
  // no need to decrypt (just check that encrypted folder exists)
  // to decrypt use: return openssl_decrypt(base64_decode($str), 'AES-256-CBC', $key, 0, $iv);
}

// --------
// HTML BEGIN
// --------

include APP.'_adm_inc_hd.php';

// Stats
$intDomain = $oDB->count( TABDOMAIN );
$intSection =  $oDB->count( TABSECTION );
$intHidden = $oDB->count( TABSECTION." WHERE type='1'" );
$arrItems = getItemsInfo($oDB);

// Start Helper
if ( $intSection-$intHidden==0 ) echo '<p class="right article">'.getSVG('flag', 'style=font-size:1.4rem;color:#1364B7').' '.L('No_public_section').' <a href="'.APP.'_adm_sections.php?add=1">'.L('Add').' '.L('domain').'/'.L('section').'...</a></p>';
if ( !qtIsMail($_SESSION[QT]['admin_email']) ) echo '<p class="right article">'.getSVG('flag', 'style=font-size:1.4rem;color:#1364B7').' '.L('Contact').' '.L('Adm_e_mail').' '.L('invalid').'. '.L('Edit').': <a href="'.APP.'_adm_site.php">'.L('Board_general').'...</a></p>';
if ( strlen($_SESSION[QT]['site_url'])<10 ) echo '<p class="right article">'.getSVG('flag', 'style=font-size:1.4rem;color:#1364B7').' '.L('Site_url').' '.L('invalid').'. '.L('Edit').': <a href="'.APP.'_adm_site.php">'.L('Board_general').'...</a></p>';
if ( $_SESSION[QT]['home_menu'] && (strlen($_SESSION[QT]['home_url'])<10 || !preg_match('/^(http:\/\/|https:\/\/)/',$_SESSION[QT]['home_url'])) ) echo '<p class="right article">'.getSVG('flag', 'style=font-size:1.4rem;color:#1364B7').' '.L('Home_website_url').' '.L('invalid').'. '.L('Edit').': <a href="'.APP.'_adm_skin.php">'.L('Board_layout').'...</a></p>';
if ( is_dir('install') ) echo '<p class="right article">'.getSVG('flag', 'style=font-size:1.4rem;color:red').' Install folder is accessible: <a href="install/'.APP.'_setup_9.php?lang='.QT_LANG.'">'.L('Change').'...</a></p>';

// BOARD OFFLINE
echo '<h2 class="config">'.L('Board_status').'</h2>
<table class="t-conf">
<tr>
<th>'.L('Board_status').'</th>
<td class="flex-sp">
<p><span style="display:inline-block;width:16px;background-color:'.($_SESSION[QT]['board_offline'] ? 'red' : 'green').';border-radius:3px">&nbsp;</span> '.L(($_SESSION[QT]['board_offline']?'Off' : 'On').'_line').'</p>
<form method="post" action="'.$oH->selfurl.'">
'.L('Change').' <select id="offline" name="offline" onchange="qtFormSafe.not();">
'.asTags([L('On_line'),L('Off_line')], $_SESSION[QT]['board_offline']).'
</select> <button type="submit" name="ok" value="save">'.L('Ok').'</button>
</form>
</td>
</tr>
</table>
';

// INFO
echo '<h2 class="config">'.L('Info').'</h2>'.PHP_EOL;
echo '<table class="t-conf">'.PHP_EOL;
echo '<tr><th>'.L('Domains').'/'.L('Section+').'</th><td>'.L('Domain',$intDomain).', '.L('Section',$intSection).' <span  class="small">('.L('hidden',$intHidden).')</span>, <a href="'.APP.'_adm_sections.php?add=1">'.L('Add').' '.L('domain').'/'.L('section').'...</a></td></tr>'.PHP_EOL;
if ( !empty($arrItems['startdate']) ) echo '<tr><th>'.L('Board_start_date').'</th><td>'.$arrItems['startdate'].', <a href="'.APP.'_stats.php">'.L('Statistics').'...</a></td></tr>'.PHP_EOL;

$intUser = $oDB->count( TABUSER );
$intAdmin = $oDB->count( TABUSER." WHERE role='A'" );
$intMod = $oDB->count( TABUSER." WHERE role='M'" );

echo '<tr><th>'.L('Users').'</th><td>'.L('User',$intUser).' <span  class="small">('.L('Role_A',$intAdmin).', '.L('Role_M',$intMod).', '.L('Role_U',$intUser-$intAdmin-$intMod).')</span></td></tr>'.PHP_EOL;
if ( !empty($arrItems['content']) ) echo '<tr><th>'.L('Content').'</th><td>'.$arrItems['content'].'</td></tr>';
echo '</table>
';

// PUBLIC ACCESS LEVEL

echo '<h2 class="config">'.L('Public_access_level').'</h2>'.PHP_EOL;
echo '<table class="t-conf">'.PHP_EOL;
echo '<tr>';
echo '<th>'.L('Visitors_can').'</th>';
echo '<td>'.L('Pal.'.$_SESSION[QT]['visitor_right']).' &middot; <a href="'.APP.'_adm_secu.php">'.L('Change').'...</a></td>';
echo '</tr>'.PHP_EOL.'</table>'.PHP_EOL;

// VERSIONS

echo '
<h2 class="config">'.L('Version').'</h2>
<table class="t-conf">
<tr>
<th>'.APPNAME.'</th>
<td>'.VERSION.' '.BUILD.', database '.$_SESSION[QT]['version'].(file_exists(APP.'_about.txt') ? ', <a target="_blank" href="'.APP.'_about.txt">release notes</a>' : '').'</td>
</tr>
';

$arr=[];
if ( file_exists('tool_phpinfo.php') ) $arr[] = ' <a href="tool_phpinfo.php" target="_blank">php info</a>';
if ( file_exists(APP.'_adm_const.php') ) $arr[] = '<a href="'.APP.'_adm_const.php">php constants</a>';
if ( file_exists('tool_sql.php') ) $arr[] = '<a href="tool_sql.php">test sql</a>';
$file = qtEncrypt(); if ( is_dir($file) ) $arr[] = '<a href="'.APP.'_adm_index.php?cmd=decrypt&file='.$file.'">decrypt install folder</a>';

echo '<tr>
<th>Memory</th>
<td>Cache library: '.SMem::getLibraryName().', namespace '.QT.'</td>
</tr>
<th>PHP</th>
<td>'.implode(' &middot; ',$arr).'</td>
</tr>
<th>DB</th>
<td>Connector: '.QDB_SYSTEM.' &middot;  Host: '.$oDB->getHost().'</td>
</tr>
</table>
';

// HTML END

include APP.'_adm_inc_ft.php';