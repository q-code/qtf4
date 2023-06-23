<?php // v4.0 build:20230618 allows app impersonation [qt f|i ]

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
require 'bin/init.php';
if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');

// FUNCTION
function arrShift(array $arr, $item, int $step=1)
{
  // To shift up/down we swap the item with previous/next (step -1 or 1). WARNING: array index changes
  $arr = array_values($arr); // new index (0..n)
  $i = array_search($item,$arr);
  if ( $i===false || $i+$step<0 || $i+$step>=count($arr) ) return $arr;
  [$arr[$i],$arr[$i+$step]] = [$arr[$i+$step],$arr[$i]];
  return $arr;
}

// INITIALISE
$a = '';
$d = -1;
$s = -1;
$add = false; // shows Add-Form
qtArgs('a int:d int:s bool:add');

$oH->selfurl = APP.'_adm_sections.php';
$oH->selfname = L('Section+');
$oH->selfparent = L('Board_content');

// --------
// SUBMITTED
// --------

// REODER DOMAINS/SECTIONS 'neworder' contains a csv list of s{id} or d{id} created by java drag and drop events
if ( isset($_POST['neworder']) ) {
  if ( substr($_POST['neworder'],0,1)==='d' ) {
    foreach(explode(';',$_POST['neworder']) as $k=>$id) {
      $oDB->exec( 'UPDATE TABDOMAIN SET titleorder='.$k.' WHERE id='.substr($id,1) );
    }
    SMem::clear('_Domains');
  }
  if ( substr($_POST['neworder'],0,1)==='s' ) {
    foreach(explode(';',$_POST['neworder']) as $k=>$id) {
      $oDB->exec( 'UPDATE TABSECTION SET titleorder='.$k.' WHERE id='.substr($id,1) );
    }
    SMem::clear('_Sections');
  }
}

// ADD DOMAIN
if ( isset($_POST['add_dom']) ) try {

  $_POST['title'] = trim($_POST['title']); if ( empty($_POST['title']) ) throw new Exception( L('Domain').' '.L('invalid') );
  CDomain::create($_POST['title']); // check unique title. Cache is cleared
  // Successfull end
  $_SESSION[QT.'splash'] = L('S_insert');

} catch (Exception $e) {

  $oH->error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;

}

// ADD SECTION
if ( isset($_POST['add_sec']) ) try {

  $_POST['title'] = qtDb(trim($_POST['title']));
  if ( empty($_POST['title']) ) throw new Exception( L('Section').' '.L('invalid') );
  CSection::create($_POST['title'],(int)$_POST['indomain']); // check unique title. Clear cache
  // Successfull end
  $_SESSION[QT.'splash'] = L('S_insert');

} catch (Exception $e) {

  $oH->error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;

}

// Move domain/section
if ( !empty($a) )
{
  $arr = [];
  if ( $a==='d_up' || $a==='d_down' )
  {
    $oDB->query( "SELECT id FROM TABDOMAIN ORDER BY titleorder" );
    while($row=$oDB->getRow()) $arr[] = (int)$row['id'];
    $arr = arrShift($arr, $d, $a==='d_up' ? -1 : 1);
    foreach($arr as $k=>$id) $oDB->exec( "UPDATE TABDOMAIN SET titleorder=$k WHERE id=$id" );
    // Clear cache
    SMem::clear('_Domains');
  }
  if ( $a==='s_up' || $a==='s_down' )
  {
    $oDB->query( "SELECT id FROM TABSECTION WHERE domainid=$d ORDER BY titleorder" );
    while($row=$oDB->getRow()) $arr[] = (int)$row['id'];
    $arr = arrShift($arr, $s, $a==='s_up' ? -1 : 1);
    foreach($arr as $k=>$id) $oDB->exec( "UPDATE TABSECTION SET titleorder=$k WHERE id=$id" );
    // Clear cache
    SMem::clear('_Sections');
  }
}

// --------
// INITIALISE (no cache)
// --------

$arrDomains = CDomain::getTitles(); // titles translated
$arrSections = CSection::getSections('A',-2); // titles not translated, optimisation: get all sections at once (grouped by domain)
if ( count($arrDomains)>12 ) { $oH->warning .= 'You have a lot of domains. Try to remove unused domains. '; $_SESSION[QT.'splash'] = 'W|'.$oH->warning; }
if ( count($arrSections,COUNT_RECURSIVE)>120 ) { $oH->warning .= 'You have a lot of sections. Try to remove unused sections. '; $_SESSION[QT.'splash'] = 'W|'.$oH->warning; }
if ( !empty($oH->warning) ) $oH->warning = qtSVG('flag', 'style=font-size:1.4rem;color:#1364B7').' '.$oH->warning;

// --------
// HTML BEGIN
// --------

include APP.'_adm_inc_hd.php';

// Add domain/section
echo '
<div style="position:relative">
<p class="right">
<a id="tgl-ctrl" class="tgl-ctrl'.($add ? ' expanded' : '' ).'" href="javascript:void(0)" onclick="qtToggle(); return false;">'.L('Add').' '.L('domain').'/'.L('section').qtSVG('angle-down','','',true).qtSVG('angle-up','','',true).'</a>
</p>
';
echo '<div id="tgl-container" class="add-dom-sec" style="display:'.($add ? 'block' : 'none' ).'">
<form method="post" action="'.$oH->self().'">
<div class="add-dom">
<div class="flex-sp">
<p>'.L('Domain').' <input required id="domain" name="title" type="text" size="24" maxlength="64" /></p>
<p><input type="hidden" name="add" value="1"/><button type="submit" name="add_dom" value="add_dom">'.L('Add').'</button></p>
</div>
</div>
</form>
<form method="post" action="'.$oH->self().'">
<div class="add-sec">
<div class="flex-sp">
<p>'.L('Section').' <input required id="section" name="title" type="text" size="24" maxlength="64" /> '.L('in_domain').' <select name="indomain" size="1">'.qtTags($arrDomains).'</select></p>
<p><input type="hidden" name="add" value="1"/><button type="submit" name="add_sec" value="add_sec">'.L('Add').'</button></p>
</div>
</div>
</form>
</div>
';

// Dialog reorder (is used to submit domains or sections neworder)
// Tips: Drag and drop eventlisteners are added on elements having attribute draggable="true" and data-dragid (see .js file)
echo '<div id="dlg-reorder" style="display:none">
<p>'.L('Reorder_domains').'</p>
<table>
';
foreach($arrDomains as $id=>$domain)
echo '<tr data-dragid="d'.$id.'" draggable="true"><td class="ellipsis">'.qtSVG('arrows-v').'<span class="indent">'.$domain.'</span></td></tr>'.PHP_EOL;
echo '</table>
<form method="post" action="'.$oH->self().'">
<p class="submit">
<input type="hidden" id="neworder" name="neworder" />
<button type="button" onclick="qtToggle(`dlg-reorder`)">'.L('Cancel').'</button> <button type="submit" id="neworder-save" name="save" value="save">'.L('Save').'</button>
</p>
</form>
</div>
';

// Table domains/sections
// Previous dlg-reorder form is used in this table, after a drag and drop (using input neworder value and neworder-save click event)
echo '
<table class="t-sec">
<thead>
<tr>
<th class="c-handler">&nbsp;</th>
<th class="c-section" colspan="2">'.L('Domain').'/'.L('Section').'</th>
<th class="c-data ellipsis">'.L('Ref').'</th>
<th class="c-moderator ellipsis">'.L('Role_C').'</th>
<th class="c-action ellipsis">'.L('Action').'</th>
</tr>
</thead>
';

$i=0;
foreach($arrDomains as $idDomain=>$domain) {

  echo '<tbody>'.PHP_EOL;
  echo '<tr data-dragid="d'.$idDomain.'">'.PHP_EOL;
  echo '<td class="group handler">'.(count($arrDomains)<2 ? '' : '<span class="draghandler" title="'.L('Move').'" onclick="qtToggle(`dlg-reorder`)">'.qtSVG('arrows-v').'</span>').'</td>'.PHP_EOL;
  echo '<td class="group c-section" colspan="2">'.$domain.'</td>'.PHP_EOL;
  echo '<td class="group">&nbsp;</td>'.PHP_EOL;
  echo '<td class="group">&nbsp;</td>'.PHP_EOL;
  echo '<td class="group c-action"><a href="'.APP.'_adm_domain.php?id='.$idDomain.'" title="'.L('Edit').'">'.qtSVG('pen-square').'</a>';
  echo ' &middot; '.($idDomain===0 ? '<span class="disabled" title="'.L('Delete').'">'.qtSVG('trash').'</span>' : '<a href="'.APP.'_dlg_adm.php?a=deldom&s='.$idDomain.'" title="'.L('Delete').'">'.qtSVG('trash').'</a>');
  echo ' &middot; ';
  $strUp = qtSVG('caret-up', 'class=disabled');
  $strDw = qtSVG('caret-down', 'class=disabled');
  if ( count($arrDomains)>1 ) {
    if ( $i>0 ) $strUp = '<a class="popup_ctrl" href="'.$oH->selfurl.'?d='.$idDomain.'&a=d_up" title="'.L('Up').'">'.qtSVG('caret-up').'</a>';
    if ( $i<count($arrDomains)-1 ) $strDw = '<a class="popup_ctrl" href="'.$oH->selfurl.'?d='.$idDomain.'&a=d_down" title="'.L('Down').'">'.qtSVG('caret-down').'</a>';
  }
  echo $strUp.'&nbsp;&thinsp;'.$strDw;
  echo '</td>'.PHP_EOL;
  echo '</tr>'.PHP_EOL;
  echo '</tbody>'.PHP_EOL;

  $j = 0;
  if ( isset($arrSections[$idDomain]) && count($arrSections[$idDomain])>0 ) {

    $isSortable = count($arrSections[$idDomain])>1;
    echo '<tbody '.($isSortable ? ' class="sortable"' : '').'>'.PHP_EOL;
    foreach($arrSections[$idDomain] as $idSection=>$arrSection) {
      $oS = new CSection($arrSection,true);
      $strUp = qtSVG('caret-up', 'class=disabled');
      $strDw = qtSVG('caret-down', 'class=disabled');
      echo '<tr class="hover"'.($isSortable ? ' data-dragid="s'.$oS->id.'"' : '').'>';
      echo '<td class="handler">'.($isSortable ? '<span class="draghandler" title="'.L('Move').'" draggable="true">'.qtSVG('arrows-v').'</span>' : '').'</td>'.PHP_EOL;
      echo '<td class="c-icon">'.asImg( CSection::makeLogo(qtExplodeGet($oS->options,'logo',''),$oS->type,$oS->status), 'title='.L('Ico_section_'.$oS->type.'_'.$oS->status) ).'</td>';
      echo '<td class="c-section"><a class="sectionname" href="'.APP.'_adm_section.php?s='.$oS->id.'">'.$oS->title.'</a><br><span class="small">'.L('Section_type.'.$oS->type).($oS->status==='1' ? ', '.L('Section_status.1') : '').'</span></td>';
      echo '<td class="c-data ellipsis">'.( $oS->numfield==='N' ? '<span class="disabled">'.L('N').'</span>' : sprintf($oS->numfield,1) ).'</td>';
      echo '<td class="c-moderator ellipsis">'.$oS->ownername.'</td>';
      echo '<td class="c-action"><a href="'.APP.'_adm_section.php?s='.$oS->id.'" title="'.L('Edit').'">'.qtSVG('pen-square').'</a>';
      echo ' &middot; '.($idSection===0 ? '<span class="disabled" title="'.L('Delete').'">'.qtSVG('trash').'</span>' : '<a href="'.APP.'_dlg_adm.php?a=delsec&s='.$idSection.'" title="'.L('Delete').'">'.qtSVG('trash').'</a>');
      echo ' &middot; ';
      if ( count($arrSections[$idDomain])>1 ) {
        if ( $j>0 ) $strUp = '<a href="'.$oH->selfurl.'?d='.$idDomain.'&s='.$idSection.'&a=s_up" title="'.L('Up').'">'.qtSVG('caret-up').'</a>';
        if ( $j<count($arrSections[$idDomain])-1 ) $strDw = '<a href="'.$oH->selfurl.'?d='.$idDomain.'&s='.$idSection.'&a=s_down" title="'.L('Down').'">'.qtSVG('caret-down').'</a>';
      }
      echo $strUp.'&nbsp;&thinsp;'.$strDw;
      $j++;
      echo '</td></tr>'.PHP_EOL;
    }
  }
  echo '</tbody>'.PHP_EOL;
  $i++;
}

echo '</table>
';

// HTML END

echo '
</div>
';

include APP.'_adm_inc_ft.php';