<?php // v4.0 build:20230205

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';
if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');

// INITIALISE

$s = -1; // Section id
$pan = 1; // TAB 1:definition, 2:display or 3:translation
qtHttp('int:s! int:pan'); if ( $s<0 ) die('Missing parameters');
if ( $pan<1 || $pan>3) $pan=1;

$oH->selfurl = 'qtf_adm_section.php';
$oH->selfname = L('Section_upd');
$oH->selfparent = L('Board_content');
$oH->exiturl = 'qtf_adm_sections.php';
$oH->exitname = L('Section+');

$arrDomains = CDomain::getTitles();
$arrStaff = getUsers('S');
$oS = new CSection($s);

// --------
// SUBMITTED pan 1
// --------

if ( isset($_POST['ok']) && $pan===1 ) try {

  // CHECK MANDATORY VALUE
  $_POST['title'] = trim($_POST['title']); if ( empty($_POST['title']) ) throw new Exception( L('Title').' '.L('invalid') );
  $_POST['title'] = qtDb($_POST['title']);
  // Check if name is already used (in destination domain) note: same name for same id is allowed
  if ( $oDB->count( TABSECTION." WHERE id<>$s AND domainid=? AND title=?", [(int)$_POST['domain'],$_POST['title']] )>0 ) throw new Exception( L('Name').' '.L('already_used') );
  $oS->pid = (int)$_POST['domain'];
  $oS->title = $_POST['title'];
  $oS->type = $_POST['type'];
  $oS->status = $_POST['status'];
  if ( isset($_POST['ownername']) && $_POST['ownername']!=$_POST['ownernameold'] ) {
    $oS->ownername = $_POST['ownername'];
    $oS->ownerid = array_search($_POST['ownername'],$arrStaff);
    if ( $oS->ownerid===FALSE || empty($oS->ownerid) ) {
      $oS->ownerid = 1;
      $oS->ownername = $arrStaff[1];
      $oH->warning = L('Role_C').' '.L('invalid');
    }
  }
  if ( isset($_POST['ownerid']) && $_POST['ownerid']!=$_POST['owneridold'] ) {
    $oS->ownername = $arrStaff[$_POST['ownerid']];
    $oS->ownerid = $_POST['ownerid'];
  }
  $oS->titlefield = (int)$_POST['titlefield'];
  $oS->numfield = trim($_POST['numfield']); if ( strlen($oS->numfield)===0 ) $oS->numfield = 'N';
  $oS->prefix = $_POST['prefix'];
  // Update
  $oDB->query(
    "UPDATE TABSECTION SET domainid=?,title=?,type=?,status=?,moderator=?,moderatorname=?,titlefield=?,numfield=?,prefix=? WHERE id=".$oS->id,
    [$oS->pid,$oS->title,$oS->type,$oS->status,$oS->ownerid,$oS->ownername,$oS->titlefield,$oS->numfield,$oS->prefix]
    );
  SMem::clear('_Sections'); // Clear cache
  $_SESSION[QT.'splash'] = L('S_save');

} catch (Exception $e) {

  $oH->error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;

}

// --------
// SUBMITTED pan 2
// --------

if ( isset($_POST['ok']) && $pan===2 )
{
  $oS->setMF('options', 'order', $_POST['dfltorder'], false);
  $oS->setMF('options', 'last', $_POST['lastcolumn'], false);
  $oS->setMF('options', 'logo', $_POST['sectionlogo'], false);
  $oS->updateMF('options');
  SMem::clear('_Sections'); // clear only _Sections
  $_SESSION[QT.'splash'] = L('S_save');
}

// --------
// SUBMITTED pan 3
// --------

if ( isset($_POST['ok']) && $pan===3 )
{
  // Translations (cache unchanged)
  SLang::delete('sec,secdesc','s'.$oS->id);
  foreach($_POST as $k=>$posted) {
    $posted = qtDb(trim($posted)); // encode simple+doublequote
    if ( substr($k,0,3)==='tr-' && !empty($posted) ) SLang::add('sec', substr($k,3), 's'.$oS->id, $posted);
    if ( substr($k,0,5)==='desc-' && !empty($posted) ) SLang::add('secdesc', substr($k,5), 's'.$oS->id, $posted);
  }
  memFlushLang(); // Clear cache
  $_SESSION[QT.'splash'] = L('S_save');
}

// --------
// HTML BEGIN
// --------

include 'qtf_adm_inc_hd.php';

$arrDest = $arrDomains;
Unset($arrDest[$oS->pid]);
$arrDest = array_map(function($str){ return L('Move_to').': '.$str;}, $arrDest);

// DISPLAY TABS
$arrM = array(); $str = $oH->selfurl.'?s='.$s.'&pan=';
$arrM['pan-1'] = L('Settings').       '|href='.$str.'1|id=pan-1|class=pan-tab|onclick=return qtFormSafe.exit(e0);';
$arrM['pan-2'] = L('Display_options').'|href='.$str.'2|id=pan-2|class=pan-tab|onclick=return qtFormSafe.exit(e0);';
$arrM['pan-3'] = L('Translations').   '|href='.$str.'3|id=pan-3|class=pan-tab|onclick=return qtFormSafe.exit(e0);';
$m = new CMenu($arrM,'');
echo '<div class="pan-tabs">'.$m->build('pan-'.$pan).'</div>';

// DISPLAY TAB PANEL
echo '<div class="pan">
<p class="pan-title">'.$oS->title.' &middot; '.$m->get('pan-'.$pan).'</p>
';

// FORM 1

if ( $pan==1 )
{

echo '<form method="post" action="'.$oH->selfurl.'">
<h2 class="subconfig">'.L('Definition').'</h2>
<table class="t-conf">
<tr>
<th style="width:150px; text-align:right"><span class="texthead"><label for="title">'.L('Title').'</label></span></th>
<td><input required type="text" id="title" name="title" size="32" maxlength="64" value="'.qtAttr($oS->title).'" style="background-color:#dbf4ff;" onchange="qtFormSafe.not();"/></td>
</tr>
<tr>
<th style="width:150px; text-align:right"><span class="texthead">'.L('Domain').'</span></th>
<td><select name="domain" onchange="qtFormSafe.not();">
<option value="'.$oS->pid.'" selected>'.$arrDomains[$oS->pid].'</option>'.asTags($arrDest).'</select></td>
</tr>
</table>
';
echo '<h2 class="subconfig">'.L('Properties').'</h2>
<table class="t-conf">
<tr>
<th style="text-align: right; width:150px"><span class="texthead"><label for="type">'.L('Type').'</label></span></th>
<td>
<select id="type" name="type" onchange="qtFormSafe.not();">
<option value="1"'.($oS->type==='1' ? ' selected' : '').'>'.L('Section_type.1').'</option>
<option value="0"'.($oS->type==='0' ? ' selected' : '').'>'.L('Section_type.0').'</option>
<option value="2"'.($oS->type==='2' ? ' selected' : '').'>'.L('Section_type.2').'</option>
</select>
 '.L('Status').' <select id="status" name="status" onchange="qtFormSafe.not();">
<option value="0"'.($oS->status==='0' ? ' selected' : '').'>'.L('Section_status.0').'</option>
<option value="1"'.($oS->status==='1' ? ' selected' : '').'>'.L('Section_status.1').'</option>
</select>
';
echo '</td>
</tr>
<tr>
<th style="width:150px; text-align:right">'.L('Role_C').'</th>
<td>';
if ( count($arrStaff)>15 )
{
echo 'input type="hidden" id="usr-t" value="M"/>
<input type="hidden" name="ownernameold" value="'.$oS->ownername.'" onchange="qtFormSafe.not();"/>
<div id="ac-wrapper-user" class="ac-wrapper">
<input name="ownername" id="user" maxlength="24" value="'.$oS->ownername.'" onchange="qtFormSafe.not();" size="32"/>
</div>';
}
else
{
echo '<input type="hidden" name="owneridold" value="'.$oS->ownerid.'" onchange="qtFormSafe.not();"/>
<select id="ownerid" class="stamprole" name="ownerid" onchange="qtFormSafe.not();">'.asTags($arrStaff, $oS->ownerid).'</select>';
}
echo '</td>
</tr>
</table>
';
echo '<h2 class="subconfig">'.L('Specific_fields').'</h2>
<table class="t-conf">
';
echo '<tr>
<th style="text-align: right; width:150px"><span class="texthead"><label for="numfield">'.L('Show_item_id').'</label></span></th>
<td><input type="text" id="numfield" size="10" maxlength="24" name="numfield" value="'.($oS->numfield=='N' ? '' : qtAttr($oS->numfield)).'" onchange="qtFormSafe.not();"/>&nbsp;<span class="small">'.L('H_Show_item_id').'</span></td>
</tr>
';
echo '<tr>
<th style="text-align: right; width:150px"><span class="texthead"><label for="titlefield">'.L('Show_item_title').'</label></span></th>
<td><select id="titlefield" name="titlefield" onchange="qtFormSafe.not();">'.asTags(L('Item_title.*'),$oS->titlefield).'</select>&nbsp;<span class="small">'.L('H_Show_item_title').'</span></td>
</tr>
';
echo '<tr title="'.L('H_Item_prefix').'">
<th style="text-align: right; width:150px"><span class="texthead"><label for="prefix">'.L('Item_prefix').'</label></span></th>
<td>
<select id="prefix" name="prefix" onchange="qtFormSafe.not();">
'.asTags(L('PrefixSerie.*'),$oS->prefix).'
<option value="0"'.($oS->prefix=='0' ? ' selected' : '').'>'.L('None').'</option>
</select>&nbsp;<a class="small" href="qtf_adm_prefixicon.php" target="_blank">'.L('Item_prefix_demo').'</a>
</td>
</tr>
</table>
';
echo '<p class="submit">
<input type="hidden" name="s" value="'.$oS->id.'"/>
<input type="hidden" name="pan" value="'.$pan.'"/>
<button type="submit" name="ok" value="ok">'.L('Save').'</button>
</p>
</form>
';

}

// FORM 2

if ( $pan==2 ) {

$addOption='';
$strFile='';
if ( file_exists(QT_DIR_DOC.'section/'.$s.'.gif') ) $strFile = $s.'.gif';
if ( file_exists(QT_DIR_DOC.'section/'.$s.'.jpg') ) $strFile = $s.'.jpg';
if ( file_exists(QT_DIR_DOC.'section/'.$s.'.png') ) $strFile = $s.'.png';
if ( file_exists(QT_DIR_DOC.'section/'.$s.'.jpeg') ) $strFile = $s.'.jpeg';
if ( !empty($strFile) ) {
  if ( !empty($_GET['up']) ) $oS->setMF('options','logo',$strFile,true); // save if uploaded
  $addOption = '<option value="'.$strFile.'"'.(empty($oS->getMF('options','logo')) ? '' : 'selected').'>'.L('Specific_image').'</option>';
}
echo '<form method="post" action="'.$oH->selfurl.'">
<table class="t-conf">
<tr>
<th><span class="texthead">Logo</span></th>
<td><select name="sectionlogo" onchange="qtFormSafe.not(); switchpreview(this.value);">
<option value="">'.L('Default').'</option>
'.$addOption.'
</select> '.asImg($oS->logo(), 'id=previewlogo|title='.L('Ico_section_'.$oS->type.'_'.$oS->status)).' <a class="small" href="qtf_adm_section_img.php?id='.$s.'">'.L('Add').'/'.L('Remove').'</a>
</td>
</tr>
';

$arr = array('lastpostdate'=>L('Lastpost_date'),'numid'=>L('Ref_number'),'title'=>L('Title'));
$strOrder = $oS->getMF('options','order','lastpostdate');
echo '<tr>
<th><span class="texthead">'.L('Default_items_order').'</span></th>
<td>
<select name="dfltorder" onchange="qtFormSafe.not();">'.asTags($arr,$strOrder).'</select>
</td>
</tr>
';

$arr = array('none'=>L('None'),'views'=>L('Views'),'status'=>L('Status'),'id'=>'Id'); if ( !empty($_SESSION[QT]['tags']) ) $arr['tags']=L('Tags');
$dflt_lastcol = $oS->getMF('options','last'); if  (strtolower($dflt_lastcol)==='n' || empty($dflt_lastcol) ) $dflt_lastcol='none';
echo '<tr>
<th><span class="texthead">'.L('Infofield').'</span></th>
<td><select name="lastcolumn" onchange="qtFormSafe.not();">'.asTags($arr,$dflt_lastcol).'</select></td>
</tr>
</table>
';
echo '<p class="submit">
<input type="hidden" name="s" value="'.$oS->id.'"/>
<input type="hidden" name="pan" value="'.$pan.'"/>
<button type="submit" name="ok" value="ok">'.L('Save').'</button>
</p>
</form>
';

$oH->scripts[] = 'function switchpreview(img){
  const src = img=="" ? "'.QT_SKIN.'img/section_'.$oS->type.'_'.$oS->status.'.gif" : "'.QT_DIR_DOC.'section/"+img;
  document.getElementById("previewlogo").src = src;
  return null;
}';

}

// FORM 3

if ( $pan==3 )
{

echo '<form method="post" action="'.$oH->selfurl.'">
<table class="t-conf input100">
<tr>
<th>'.L('Title').' *</th>
<td><div class="languages-scroll">
';
$arrTrans = SLang::get('sec','*','s'.$oS->id);
$arrDescTrans = SLang::get('secdesc','*','s'.$oS->id);
foreach(LANGUAGES as $k=>$values)
{
  $arr = explode(' ',$values,2); if ( empty($arr[1]) ) $arr[1]=$arr[0];
  $str = empty($arrTrans[$k]) ? '' : $arrTrans[$k];
  echo '<p class="iso" title="'.L('Name_of_index').' ('.$arr[1].')">'.$arr[0].'</p><p><input type="text" name="tr-'.$k.'" maxlength="64" value="'.$str.'" placeholder="'.$oS->title.'" onchange="qtFormSafe.not();"/>'.(strpos($str,'&amp;') ?  ' <span class="small">'.$arrTrans[$k].'</span>' : '').'</p>'.PHP_EOL;
}
echo '</div></td>
</tr>
<tr>
<th>'.L('Description').'</th>
<td><div class="languages-scroll">
';
foreach(LANGUAGES as $k=>$values)
{
  $arr = explode(' ',$values,2); if ( empty($arr[1]) ) $arr[1]=$arr[0];
  $str = empty($arrDescTrans[$k]) ? '' : $arrDescTrans[$k];
  echo '<p class="iso" title="'.L('Description').' ('.$arr[1].')">'.$arr[0].'</p><p><input type="text" name="desc-'.$k.'" maxlength="254" onchange="qtFormSafe.not();" value="'.$str.'"/></p>'.PHP_EOL;
}
echo '</div></td>
</tr>
<tr>
<td colspan="2" class="void asterix">* '.L('E_no_translation').'<strong style="color:#1364B7">'.$oS->title.'</strong></td>
</tr>
</table>
';
echo '<p class="submit">
<input type="hidden" name="s" value="'.$oS->id.'"/>
<input type="hidden" name="pan" value="'.$pan.'"/>
<button type="submit" name="ok" value="save">'.L('Save').'</button>
</p>
</form>
';

}

// END TABS

echo '
</div>
<p>'.getSVG('angle-left').' <a href="'.$oH->exiturl.'" onclick="return qtFormSafe.exit(e0);">'.$oH->exitname.'</a></p>
';

// HTML END

if ( $pan==1 && count($arrStaff)>15 )
$oH->scripts['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js"></script>
<script type="text/javascript" src="bin/js/qtf_config_ac.js"></script>';

include 'qtf_adm_inc_ft.php';