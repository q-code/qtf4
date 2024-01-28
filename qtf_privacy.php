<?php // v4.0 build:20230618 allows app impersonation [qt f|i|e|m]

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';

// INITIALISE

$oH->selfurl = APP.'_privacy.php';
$oH->selfname = L('Legal');
if ( !isset($_SESSION[QT]['admin_phone']) ) $_SESSION[QT]['admin_phone'] = '';

// --------
// HTML BEGIN
// --------

include APP.'_inc_hd.php';

CHtml::msgBox($oH->selfname);

include translate('app_rules.txt');
if ( isset($_SESSION[QT]['register_coppa']) && $_SESSION[QT]['register_coppa']==='1' ) include translate('app_rules_coppa.txt');

CHtml::msgBox('/');

CHtml::msgBox(L('About'));

$strFile = translate('app_about.php');
if ( file_exists($strFile) ) { include $strFile; } else { echo 'Missing file:<br>'.$strFile; }

echo '<p>';
if ( file_exists('bin/css/vhtml5.png') ) echo '<img src="bin/css/vhtml5.png" alt="HTML 5" height="64" width="64" title="HTML5"/>&nbsp;';
if ( file_exists('bin/css/vcss3.png') ) echo '<img src="bin/css/vcss3.png" alt="HTML 5" height="64" width="64" title="CSS3"/>&nbsp;';
echo '<a href="http://www.w3.org/WAI/WCAG1AAA-Conformance" title="Explanation of Level Triple-A Conformance"><img height="31" width="88" src="bin/css/wcag1aaa.png" alt="Level Triple-A conformance icon, W3C-WAI Web Content Accessibility Guidelines 1.0"/></a>
';
echo '</p>
';

// Get module about files
$files = glob(APP.'m_*_about.php');
foreach($files as $file){
  echo '<br><p class="bold">Module '.substr($file,5,-10).'</p>';
  include($file);
}

CHtml::msgBox('/');

// --------
// HTML END
// --------

include APP.'_inc_ft.php';