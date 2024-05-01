<?php // v4.0 build:20240210 allows app impersonation [qtf|i|n]

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php'; if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');
$oH->selfurl = APP.'_adm_tags_help.php';

const HIDE_MENU_TOC = true;
const HIDE_MENU_LANG = true;

include APP.'_adm_inc_hd.php';

echo '<div style="padding:2rem;border:#ccc solid 1px">';
include translate(APP.'_adm_tags_help.php');
echo '</div>';

include APP.'_adm_inc_ft.php';