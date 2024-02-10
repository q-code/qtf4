<?php

/**
* PHP version 7
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license.  If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QuickTalk forum
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2012 The PHP Group
* @version    4.0 build:20240210
*/

session_start();
require 'bin/init.php';
/**
* @var CHtml $oH
* @var array $L
* @var CDatabase $oDB
*/
include translate('lg_coppa.php');
$oH->selfurl = 'qtf_form_coppa.php';

// ------
// HTML BEGIN
// ------
const HIDE_MENU_TOC=true;

include 'qtf_adm_inc_hd.php';

echo <<<END
<h2>{$_SESSION[QT]['site_name']}: {$L['Register']} / {$L['Coppa_form']} </h2>
<br>
<p>{$L['Coppa']['Form_info']}</p>
<p>{$_SESSION[QT]['admin_email']}<br>{$_SESSION[QT]['admin_phone']}<br>{$_SESSION[QT]['admin_name']}<br>{$_SESSION[QT]['admin_addr']}</p>
<table style="border-spacing:0 15px">
<tr>
<td style="width:175px"><b>{$L['Coppa']['Permission']}</b></td>
<td style="padding: 4px;border-style: solid;border-width: 1px; border-color: black">&nbsp;</td>
<td style="padding: 4px;border-style: solid;border-width: 0px; border-color: black">{$L['Y']}</td>
<td style="padding: 4px;border-style: solid;border-width: 1px; border-color: black">&nbsp;</td>
<td style="padding: 4px;border-style: solid;border-width: 0px; border-color: black">{$L['N']}</td>
</tr>
<tr>
<td style="width:175px">{$L['Coppa']['Child_name']}</td>
<td colspan="4" style="padding: 4px;border-style: solid;border-width: 1px; border-color: black">&nbsp;</td>
</tr>
<tr>
<td style="width:175px">{$L['Coppa']['Child_login']}</td>
<td colspan="4" style="padding: 4px;border-style: solid;border-width: 1px; border-color: black">&nbsp;</td>
</tr>
<tr>
<td style="width:175px">{$L['Coppa']['Child_email']}</td>
<td colspan="4" style="padding: 4px;border-style: solid;border-width: 1px; border-color: black">&nbsp;</td>
</tr>
<tr>
<td style="width:175px">{$L['Coppa']['Child_privacy']}</td>
<td style="padding: 4px;border-style: solid;border-width: 1px; border-color: black">&nbsp;</td>
<td style="padding: 4px;border-style: solid;border-width: 0px; border-color: black">{$L['Coppa']['Members']}</td>
<td style="padding: 4px;border-style: solid;border-width: 1px; border-color: black">&nbsp;</td>
<td style="padding: 4px;border-style: solid;border-width: 0px; border-color: black">{$L['Coppa']['Nobody']}</td>
</tr>
</table>
<p>{$L['Coppa']["Agreement"]}</p>
<table style="border-spacing:0 15px">
<tr>
<td style="width:175px">{$L['Coppa']['Parent_name']}</td>
<td style="padding: 4px;border-style: solid;border-width: 1px; border-color: black">&nbsp;</td>
</tr>
<tr>
<td style="width:175px">{$L['Coppa']['Parent_relation']}</td>
<td style="padding: 4px;border-style: solid;border-width: 1px; border-color: black">&nbsp;</td>
</tr>
<tr>
<td style="width:175px">{$L['Coppa']['Parent_email']}</td>
<td style="padding: 4px;border-style: solid;border-width: 1px; border-color: black">&nbsp;</td>
</tr>
<tr>
<td style="width:175px">{$L['Coppa']['Parent_phone']}</td>
<td style="padding: 4px;border-style: solid;border-width: 1px; border-color: black">&nbsp;</td>
</tr>
<tr>
<td style="width:175px">{$L['Coppa']['Parent_sign']}</td>
<td style="padding: 4px;border-style: solid;border-width: 1px; border-color: black"><br><br><br><br></td>
</tr>
</table>

<p>{$L['Coppa']['End']}</p>\n
END;

// HTML END

include 'qtf_adm_inc_ft.php';