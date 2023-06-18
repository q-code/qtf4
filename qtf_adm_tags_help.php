<?php // v4.0 build:20230618 allows app impersonation [qt f|i ]

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php'; if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');
$oH->selfurl = APP.'_adm_tags_help.php';

const HIDE_MENU_TOC=true;

$oH->links['cssIcons']=''; // remove webicons

include APP.'_adm_inc_hd.php';

echo '<div style="margin:25px;padding:20px;border:#ccc solid 1px">';
include translate(APP.'_adm_tags_help.php');
echo '</div>';

include APP.'_adm_inc_ft.php';