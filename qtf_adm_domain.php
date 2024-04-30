<?php // v4.0 build:20240210 allows app impersonation [qtf|i|e|n]

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
require 'bin/init.php';
if ( SUser::role()!=='A' ) die('Access denied');
$id = -1; qtArgs('int:id!'); if ( $id<0 ) die('Missing argument id');

include translate('lg_adm.php');

$oH->selfurl = APP.'_adm_domain.php';
$oH->selfname = L('Domain_upd');
$oH->selfparent = L('Board_content');
$oH->exiturl = APP.'_adm_sections.php';
$oH->exitname = qtSVG('angle-left').' '.L('Section+');

// ------
// INITIALISE (no cache)
// ------
$oDB->query( "SELECT title FROM TABDOMAIN WHERE id=".$id);
$row = $oDB->getRow();
$arrTrans = SLang::get('domain','*','d'.$id);

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) ) try {

  // Check. All $_POST are sanitized into $post
  $post = array_map('trim', qtDb($_POST));
  if ( empty($post['title']) ) throw new Exception( L('Title').' '.L('not_empty') );
  // Update
  if ( $post['title']!==$row['title'] ) CDomain::rename($id, $post['title']); // encode, check unique title, clears _Domains cache
  SLang::delete('domain', 'd'.$id, '*');
  foreach($post as $k=>$val) if ( substr($k,0,3)==='tr-' && !empty($val) ) SLang::add('domain', substr($k,3), 'd'.$id, $val);
	// Successful exit
  memFlushLang();
	$_SESSION[QT.'splash'] = L('S_update');
  $oH->redirect($oH->exiturl,$oH->exitname); //█

} catch (Exception $e) {

  $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
  $oH->error = $e->getMessage();

}

// ------
// HTML BEGIN
// ------
include APP.'_adm_inc_hd.php';

echo '
<form class="formsafe" method="post" action="'.$oH->selfurl.'">
<h2 class="config">'.L('Domain').'</h2>
<table class="t-conf input100">
';
echo '<tr>
<th style="width:100px;"><label for="title">'.L('Title').'</label></th>
<td><input required type="text" id="title" name="title" size="32" maxlength="64" value="'.qtAttr($row['title']).'"/></td>
</tr>
';
echo '<tr>
<th>'.L('Translations').' *</th>
<td><div class="languages-scroll">
';
foreach(LANGUAGES as $k=>$values) {
  $arr = explode(' ',$values,2); if ( empty($arr[1]) ) $arr[1]=$arr[0];
  echo '<p class="iso" title="'.L('Domain').' ('.$arr[1].')">'.$arr[0].'</p><p><input type="text" name="tr-'.$k.'" size="45" maxlength="64" value="'.(empty($arrTrans[$k]) ? '' : qtAttr($arrTrans[$k])).'" placeholder="'.qtAttr($row['title']).'"/></p>'.PHP_EOL;
}
echo '</div></td>
</tr>
<tr>
<td colspan="2" class="asterix">* '.L('E_no_translation').'<strong style="color:#1364B7">'.$row['title'].'</strong></td>
</tr>
</table>
<p class="submit">
<input type="hidden" name="id" value="'.$id.'"/>
<button type="button" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button>
<button type="submit" name="ok" value="ok">'.L('Save').'</button>
</p>
</form>
';

// HTML END

include APP.'_adm_inc_ft.php';