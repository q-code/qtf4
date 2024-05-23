<?php

session_start();
require 'bin/init.php';
if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');

// INITIALISE
$pan = 'en';
$dest = '';
qtArgs('pan! dest!');
$intSize = 100;
$oH->name = L('Tags');
$oH->exiturl = APP.'_adm_tags.php';
$oH->exitname = L('Tags');
$parentname = L('Board_content');

// ------
// SUBMITTED FOR UPLOAD
// ------
if ( isset($_POST['ok']) ) try {

  // Check uploaded document
  fileValidate($_FILES['title'], ['csv','txt','text'], [], 500);
  // Save
  copy($_FILES['title']['tmp_name'], 'upload/'.$v);
  unlink($_FILES['title']['tmp_name']);
  $oH->voidPage('', L('S_update').'<script type="text/javascript">setTimeout(()=>{window.location="'.url($oH->exiturl).'";}, 2000);</script>', 'admin');

} catch (Exception $e) {

  $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
  $oH->error = $e->getMessage();

}

// ------
// HTML BEGIN
// ------
include APP.'_adm_inc_hd.php';

CHtml::msgBox(L('Add').' CSV '.L('file'));

echo '<form class="formsafe" method="post" action="'.$oH->php.'" enctype="multipart/form-data">
<p style="text-align:right">
';
echo L('File').': <input type="hidden" name="max_file_size" value="'.($intSize*1024).'"/>';
echo '<input required type="file" id="title" name="title" size="32"/><br><br>';
echo L('Destination').': upload/<input type="text" id="dest" name="dest" size="20" maxlength="20" value="'.$dest.'" onkeyup="document.getElementById(`write-info`).style.visibility=this.value===`'.$dest.'` ? `visible` : `hidden`;"/><br><br>';
echo '<span id="write-info" class="warning">'.(file_exists('upload/'.$dest) ? L('Overwrite_file').' ['.$dest.']' : '').'<br><br></span>';
echo '<input type="hidden" name="pan" value="'.$pan.'"/><a class="button" href="'.$oH->exiturl.'?pan='.$pan.'">'.L('Cancel').'</a> <button type="submit" name="ok" value="ok">'.L('Ok').'</button>';
echo '
</p>
</form>
';

CHtml::msgBox('/');

// HTML END

include APP.'_adm_inc_ft.php';