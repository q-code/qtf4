<?php // v4.0 build:20240210 allows app impersonation [qt f|i]

session_start();
require 'bin/init.php';
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
include translate('lg_adm.php');

if ( SUser::role()!=='A' ) die(L('E_13'));

// INITIALISE

$oH->selfurl = APP.'_adm_prefixicon.php';
$oH->exiturl = APP.'_adm_sections.php';
$oH->selfname = L('Item_prefix');
$oH->exitname = L('Section+');

// ------
// HTML BEGIN
// ------
const HIDE_MENU_TOC = true;
const HIDE_MENU_LANG=true;

include APP.'_adm_inc_hd.php';

echo '<div style="display:flex;justify-content:space-between;align-items:flex-start">
<p style="margin:0;padding:0.5rem;width:35%;background-color:#fff;border-radius:5px;">To create a new serie, create a new file named serie-{id}.php in the config/prefix/ subfolder.<br><br>
In this file, the array prefixIcon can reference svg or image files.<br>
Example: "prefix_e_01.gif".<br><br>
Optionally, you can add css style to each prefixIcon.<br><br>
Note: the index 0 (no icon) is automatically available in each serie and must not be declared.<br><br>
To give names to your serie and to your icons, edit the file lg_icon.php in each language subfolders.</p>
<div style="margin:0;padding:0.5rem;background-color:#fff;border-radius:5px;">
';

// Browse image files
foreach(L('PrefixSerie.*') as $k=>$strName)
{
echo '<table style="border-bottom:solid 1px #bbb">
<tr>
<td style="width:200px;vertical-align:top"><h2>'.$strName.'</h2></td>
<td>
';
echo '<table>
<tr><td style="width:50px;">#</td><td style="width:80px;">Icon</td><td>Name</td></tr>
';
for ($i=1;$i<10;++$i)
{
  $str = icoPrefix($k,$i);
  if ( !empty($str) ) echo '<tr><td>'.$k.' 0'.$i.'</td><td>'.$str.'</td><td>'.L('PrefixIcon.'.$k.'0'.$i).'</td></tr>'.PHP_EOL;
}
echo '</table>
</td>
</tr>
</table>
';
}

echo '</div>
</div>';

// HTML END

include APP.'_adm_inc_ft.php';