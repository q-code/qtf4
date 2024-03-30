<?php

/**
* PHP version 7
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license. If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QuickTalk
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2012 The PHP Group
* @license    http://www.php.net/license PHP License 3.0
* @version    4.0 build:20240210
*/

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
require 'bin/init.php';
include translate('lg_adm.php');

if ( SUser::role()!=='A' ) die('Access denied');

// INITIALISE

$pan = 'en';
$dest = '';
qtArgs('pan dest');
if ( empty($dest) ) $oH->error = 'Missing file name';

$intSize = 100;

$oH->selfurl = 'qtf_adm_tags_upload.php';
$oH->selfname = L('Tags');
$oH->exiturl = 'qtf_adm_tags.php';
$oH->exitname = L('Tags');
$oH->selfparent = L('Board_content');

// ------
// SUBMITTED FOR UPLOAD
// ------
if ( isset($_POST['ok']) )
{
  // Check uploaded document

  $oH->error = validateFile($_FILES['title'],'csv,txt,text','',500);

  // Save

  if ( empty($oH->error) )
  {
    copy($_FILES['title']['tmp_name'],'upload/'.$v);
    unlink($_FILES['title']['tmp_name']);
    $oH->voidPage('', L('S_update').'<script type="text/javascript">setTimeout(()=>{window.location="'.url($oH->exiturl).'";}, 2000);</script>', 'admin');
  }
}

// ------
// HTML BEGIN
// ------
include 'qtf_adm_inc_hd.php';

CHtml::msgBox(L('Add').' CSV '.L('file'));

echo '<form class="formsafe" method="post" action="'.$oH->self().'" enctype="multipart/form-data">'.PHP_EOL;
echo '<p style="text-align:right">'.PHP_EOL;
echo L('File').': <input type="hidden" name="max_file_size" value="'.($intSize*1024).'"/>'.PHP_EOL;
echo '<input required type="file" id="title" name="title" size="32"/><br><br><br><br>'.PHP_EOL;
echo L('Destination').': upload/<input type="text" id="dest" name="dest" size="20" maxlength="20" value="'.$dest.'" onkeyup="validateWarning(this.value);"/><br><br>'.PHP_EOL;
echo '<span id="write-info" class="warning">'.(file_exists('upload/'.$dest) ? L('Overwrite_file').' ['.$dest.']' : '').'</span> ';
echo '<input type="hidden" name="pan" value="'.$pan.'"/>'.PHP_EOL;
echo '<a class="button" href="'.$oH->exiturl.'?pan='.$pan.'">'.L('Cancel').'</a> <button type="submit" name="ok" value="ok">'.L('Ok').'</button></p>'.PHP_EOL;
echo '</form>'.PHP_EOL;

CHtml::msgBox('/');

$oH->scripts[] = 'function validateWarning(str){
document.getElementById("write-info").style.display= str==="'.$dest.'" ? "inline" : "none";
}';

// HTML END

include 'qtf_adm_inc_ft.php';