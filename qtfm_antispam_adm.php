<?php // v4.0 build:20230430

session_start();
require 'bin/init.php';
/**
* @var CHtml $oH
* @var CHtml $oH
* @var array $L
* @var CDatabase $oDB
*/
include translate('lg_adm.php');
include translate(APP.'m_antispam.php');
if ( SUser::role()!=='A' ) die('Access denied');

// INITIALISE

$oH->selfurl = 'qtfm_antispam_adm.php';
$oH->selfname = $L['Antispam']['Admin'];
$oH->selfparent = L('Module');
$oH->exiturl = $oH->selfurl;
$oH->exitname = $oH->selfname;
$oH->selfversion = $L['Antispam']['Version'].' 4.0<br>';

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // read settings
  $strAz    = isset($_POST['az']) ? '1' : '0';
  $strVowel = isset($_POST['vowel']) ? '1' : '0';
  $strChars = isset($_POST['chars']) ? '1' : '0';
  $strChar_n = isset($_POST['char_n']) ? $_POST['char_n'] : '2';
  $strWords = isset($_POST['words']) ? '1' : '0';
  $strWord_n = isset($_POST['word_n']) ? $_POST['word_n'] : '1';
  $strGood = isset($_POST['good']) ? '1' : '0';
  $strGood_n = isset($_POST['good_n']) ?  $_POST['good_n'] : '0';
  $strInsane = isset($_POST['insane']) ? '1' : '0';
  $strInsane_n = isset($_POST['insane_n']) ?  $_POST['insane_n'] : '0';
  $strRepeat = isset($_POST['repeat']) ? '1' : '0';
  $strIp = isset($_POST['ip']) ? '1' : '0';

  $str = $strAz.$strVowel.$strChars.$strChar_n.$strWords.$strWord_n.$strGood.$strGood_n.$strInsane.$strInsane_n.$strRepeat.$strIp;
  $i = $strAz+$strVowel+$strChars+$strWords+$strGood+$strInsane+$strRepeat+$strIp;

  // save settings
  $oDB->exec( 'UPDATE TABSETTING SET setting="'.$str.'" WHERE param="m_antispam_conf"');
  $oDB->exec( 'UPDATE TABSETTING SET setting="'.$i.'" WHERE param="m_antispam"');
  SMem::set('settingsage',time());
  $_SESSION[QT]['m_antispam_conf'] = $str;
  $_SESSION[QT]['m_antispam'] = $i;
  $_SESSION[QT.'splash'] = empty($oH->error) ? L('S_save') : 'E|'.$oH->error;
}

if ( isset($_GET['a']) )
{
  if ( $_GET['a']==='default' ) $_SESSION[QT]['m_antispam_conf'] = '1112110000';
}

// --------
// HTML BEGIN
// --------

include 'qtf_adm_inc_hd.php';

// read values
if ( !isset($_SESSION[QT]['m_antispam_conf']) )
{
  $arr = $oDB->getSettings('param="m_antispam_conf"',true);
  if ( empty($arr) ) die('Parameters not found. The module is probably not installed properly.');
}

$file = QT_DIR_DOC.'qtfm_spamip.txt';
$arrIp = array();
if ( file_exists($file) ) {
  $arrIp = explode("\n", file_get_contents($file));
}

$strAz       = substr($_SESSION[QT]['m_antispam_conf'],0,1);
$strVowel    = substr($_SESSION[QT]['m_antispam_conf'],1,1);
$strChars    = substr($_SESSION[QT]['m_antispam_conf'],2,1);
$strChar_n   = substr($_SESSION[QT]['m_antispam_conf'],3,1);
$strWords    = substr($_SESSION[QT]['m_antispam_conf'],4,1);
$strWord_n   = substr($_SESSION[QT]['m_antispam_conf'],5,1);
$strGood     = substr($_SESSION[QT]['m_antispam_conf'],6,1);
$strGood_n   = substr($_SESSION[QT]['m_antispam_conf'],7,1);
$strInsane   = substr($_SESSION[QT]['m_antispam_conf'],8,1);
$strInsane_n = substr($_SESSION[QT]['m_antispam_conf'],9,1);
$strRepeat   = substr($_SESSION[QT]['m_antispam_conf'],10,1);
$strIp       = substr($_SESSION[QT]['m_antispam_conf'],11,1);

// FORM

echo '<form method="post" action="'.$oH->selfurl.'">
<h2 class="config">'.$L['Antispam']['Basic_rules'].'</h2>
<table class="t-conf">
<tr>
<th style="width:30px"><input type="checkbox" id="az" name="az"'.($strAz=='1' ? 'checked' : '').'/></th>
<td><label for="az">'.$L['Antispam']['Az'].'</label></td>
</tr>
<tr>
<th style="width:30px"><input type="checkbox" id="vowel" name="vowel"'.($strVowel=='1' ? 'checked' : '').'/></th>
<td><label for="vowel">'.$L['Antispam']['Voyelles'].'</label></td>
</tr>
<tr>
<th style="width:30px"><input type="checkbox" id="chars" name="chars"'.($strChars=='1' ? 'checked' : '').'/></th>
<td><label for="chars">'.$L['Antispam']['Chars'].'</label>&nbsp;
<select name="char_n" size="1">
<option value="1"'.($strChar_n=='1' ? ' selected' : '').'>'.$L['Antispam']['Char_1'].'</option>
<option value="2"'.($strChar_n=='2' ? ' selected' : '').'>'.$L['Antispam']['Char_2'].'</option>
<option value="3"'.($strChar_n=='3' ? ' selected' : '').'>'.$L['Antispam']['Char_3'].'</option>
<option value="4"'.($strChar_n=='4' ? ' selected' : '').'>'.$L['Antispam']['Char_4'].'</option>
</select></td>
</tr>
<tr>
<th style="width:30px"><input type="checkbox" id="words" name="words"'.($strWords=='1' ? 'checked' : '').'/></th>
<td><label for="words">'.$L['Antispam']['Words'].'</label>
<select name="word_n" size="1">
<option value="1"'.($strWord_n=='1' ? ' selected' : '').'>'.$L['Antispam']['Word_1'].'</option>
<option value="2"'.($strWord_n=='2' ? ' selected' : '').'>'.$L['Antispam']['Word_2'].'</option>
<option value="3"'.($strWord_n=='3' ? ' selected' : '').'>'.$L['Antispam']['Word_3'].'</option>
</select></td>
</tr>
</table>
';
echo '<h2 class="config">'.$L['Antispam']['Content_checking_rules'].'</h2>
<table class="t-conf">
<tr>
<th style="width:30px"><input type="checkbox" id="good" name="good"'.($strGood=='1' ? 'checked' : '').'/></th>
<td><label for="good">'.$L['Antispam']['Langue'].'</label>
<select name="good_n" size="1">
<option value="0"'.($strGood_n=='0' ? ' selected' : '').'>'.$L['Antispam']['Langue_0'].'</option>
<option value="1"'.($strGood_n=='1' ? ' selected' : '').'>'.$L['Antispam']['Langue_1'].'</option>
</select>*</td>
</tr>
<tr>
<th style="width:30px"><input type="checkbox" id="insane" name="insane"'.($strInsane=='1' ? 'checked' : '').'/></th>
<td><label for="insane">'.$L['Antispam']['Insane'].'</label>
<select name="insane_n" size="1">
<option value="1"'.($strInsane_n=='1' ? ' selected' : '').'>'.$L['Antispam']['Insane_1'].'</option>
<option value="2"'.($strInsane_n=='2' ? ' selected' : '').'>'.$L['Antispam']['Insane_2'].'</option>
</select>*</td>
</tr>
';
echo '<tr>
<td colspan="2" class="asterix">* '.L('Antispam.Langue_other').'</td>
</tr>
</table>
';

echo '
<h2 class="config">'.$L['Antispam']['Activity_check'].'</h2>
<table class="t-conf">
<tr>
<th style="width:30px"><input type="checkbox" id="repeat" name="repeat"'.($strRepeat=='1' ? 'checked' : '').'/></th>
<td><label for="repeat">'.$L['Antispam']['Repeat'].'</label></td>
</tr>
<tr>
<th style="width:30px"><input type="checkbox" id="ip" name="ip"'.($strIp=='1' ? 'checked' : '').'/></th>
<td><label for="ip">'.$L['Antispam']['Reject_ip'].'</label>';
if ( isset($arrIp) )
{
  $i = count($arrIp); if ( $i==1 && empty($arrIp[0]) ) $i=0; // one line but empty
  echo ' ['.sprintf($L['Antispam']['Banned_ip'],$i).'] <a href="tool_txt.php?exit='.$oH->selfurl.'&file='.$file.'&help=qtfm_antispam_ip.txt">'.L('Edit').'...</a>';
  if ( $i ) echo ' | <a href="tool_txt.php?a=delete&exit='.$oH->selfurl.'&file='.$file.'&help=qtfm_antispam_ip.txt">'.L('Delete').'</a>';

}
echo '</td>
</tr>
</table>
';
echo '<p class="submit">
<a href="'.$oH->selfurl.'?a=default" style="font-weight:normal;">'.$L['Antispam']['Default'].'</a>&nbsp;&middot;&nbsp;<button type="submit" name="ok" value="ok">'.L('Save').'</button>
</p>
</form>
';

// HTML END

include 'qtf_adm_inc_ft.php';