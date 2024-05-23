<?php // v4.0 build:20240210 allows app impersonation [qt f|i|e|n]

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';
if ( SUser::role()!=='A' ) die('Access denied');

include translate('lg_adm.php');
include translate('lg_zone.php');

// INITIALISE

$oH->name = 'Server time';
$parentname = L('Settings');
$oH->exiturl = APP.'_adm_region.php';
$oH->exitname = qtSVG('angle-left').' '.L('Board_region');

// Default time zone setting

if ( !isset($_SESSION[QT]['defaulttimezone']) ) $_SESSION[QT]['defaulttimezone']=date_default_timezone_get();

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) ) try {

  $tzi = qtAttr($_POST['tzi']);
  if ( !in_array($tzi,DateTimeZone::listIdentifiers()) ) throw new Exception( 'Unknown time zone identifier ['.$tzi.']' );

  // Save change. Attention, it can be a empty string (i.e. No change in the timezone)
  $_SESSION[QT]['defaulttimezone'] = $tzi;
  $oDB->exec( "DELETE FROM TABSETTING WHERE param='defaulttimezone'" ); // delete-insert REQUIRED as parametre can be missing
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES (?,?)", ['defaulttimezone',$_SESSION[QT]['defaulttimezone']] );

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
$arrTZI = [];
$groups = ['africa','america','asia','atlantic','australia','europe','indian','pacific','others'];
$fg = 'europe';
qtArgs('fg',true,false);
if ( !in_array($fg,$groups) && $fg!=='all' ) $fg = 'europe';

include APP.'_adm_inc_hd.php';

if ( $_SESSION[QT]['defaulttimezone']!=='' ) date_default_timezone_set($_SESSION[QT]['defaulttimezone']); // restore application timezone
$oDT = new DateTime();

echo '<form class="formsafe" method="post" action="'.$oH->php.'">
<h2 class="config">Server time zone</h2>
<table class="t-conf">
<tr>
<th style="width:150px;">Time</th>
<td style="width:225px;">'.$oDT->format('H:i:s').'</td>
<td class="small">'.$oDT->format(DATE_ATOM).'</td>
</tr>
<tr>
<th style="width:150px;">Identifier</th>
<td style="width:225px;"><input type="text" id="tzi" name="tzi" size="32" value="'.$oDT->getTimezone()->getName().'"/></td>
<td class="small">Time zone identifier</td>
</tr>
</table>
<p class="submit"><button type="submit" name="ok" value="ok">'.L('Save').'</button></p>
</form>
';

switch($fg) {
  case 'all':
    $arrTZI = DateTimeZone::listIdentifiers();
    break;
  case 'others':
    $arrTZI = DateTimeZone::listIdentifiers();
    foreach ($arrTZI as $i=>$str)
    foreach ($groups as $s) if ( $s===strtolower(substr($str,0,strlen($s))) ) unset($arrTZI[$i]);
    arsort($arrTZI);
    break;
  default:
    foreach (DateTimeZone::listIdentifiers() as $str) if ( $fg==strtolower(substr($str,0,strlen($fg))) ) $arrTZI[] = $str;
    break;
}

echo '
<h2 class="config">Identifiers</h2>
<table class="t-conf" style="border-spacing:10px">
<tr>
<td colspan="2">Find here after the list of possible time zone identifiers available on your server.<br>Copy one identifier and click Save to change your server time setting.</td>
</tr>
<tr>
<td class="right bold" style="width:40%">Search by zone</td>
<td class="bold">Time zone identifiers</td>
</tr>
<tr>
<td class="right" style="vertical-align:top">
';
foreach ($groups as $k) echo '<a href="'.APP.'_adm_time.php?fg='.$k.'">'.ucfirst($k).'</a><br>';
echo '<br><a href="'.APP.'_adm_time.php?fg=all">Show all</a>';
echo '</td>
<td style="vertical-align:top"><div class="scroll">'.implode('<br>',$arrTZI).'</div></td>
</tr>
</table>
<p><a href="'.$oH->exiturl.'">'.$oH->exitname.'</a></p>';

// HTML END

include APP.'_adm_inc_ft.php';