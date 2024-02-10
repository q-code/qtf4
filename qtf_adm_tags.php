<?php // v4.0 build:20240210 allows app impersonation [qt f|i]

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php'; if ( SUser::role()!=='A' ) die('Access denied');
require 'bin/lib_qt_tags.php';
include translate('lg_adm.php');

// INITIALISE

$pan = isset($_GET['pan']) ? $_GET['pan'] : 'en';

$oH->selfurl = APP.'_adm_tags.php';
$oH->selfname = L('Tags');
$oH->selfparent = L('Board_content');

// ------
// HTML BEGIN
// ------
include APP.'_adm_inc_hd.php';

if ( $_SESSION[QT]['tags']==='0' )
{
  CHtml::msgBox('!','class=msgbox message');
  echo L('R_security');
  echo '<p><a href="'.APP.'_adm_secu.php">&laquo; '.L('Security').'</a></p>';
  CHtml::msgBox('/');
  include APP.'_adm_inc_ft.php';
  exit;
}

$arrDomains = CDomain::getTitles(); // titles translated
$arrSections = CSection::getSections('A',-2); // titles not translated, optimisation: get all sections at once (grouped by domain)

// DISPLAY TABS

$arrM = [];
foreach (LANGUAGES as $iso=>$lang)
{
  $lang = explode(' ',$lang);
  $lang = empty($lang[1]) ? strtoupper($iso) : $lang[1]; // uppercase iso code if no description
  $arrM['pan-'.$iso] = $lang.'|href='.$oH->selfurl.'?pan='.$iso.'|id=pan-'.$iso.'|class=pan-tab|title='.L('Edit').' '.$lang;
}
$m = new CMenu($arrM,'');
echo '<div class="pan-tabs">'.$m->build( 'pan-'.$pan ).'</div>';

// DISPLAY TAB PANEL

echo '<div class="pan">
<p class="pan-title">'.$m->get('pan-'.$pan, 'title').'</p>
';

echo '<table class="t-sec">
<thead>
<tr>
<th class="c-section" colspan="2">'.L('Domain').'/'.L('Section').'</th>
<th>'.L('File').'</th>
<th class="c-action">'.L('Action').'</th>
</tr>
</thead>
<tbody>
';

// common tags
$file = 'tags_'.$pan.'.csv';
$bFile = file_exists(QT_DIR_DOC.$file);

echo '<tr style="height:35px">';
echo '<td class="c-section" colspan="2">'.L('Common_all_sections').'</td>';
echo '<td class="c-file">'.($bFile ? $file.' <a href="'.$oH->selfurl.'?pan='.$pan.'&a=view&file='.QT_DIR_DOC.$file.'" title="'.L('Preview').'">'.qtSVG('search').'</a> <a href="'.QT_DIR_DOC.$file.'" title="'.L('download').'">'.qtSVG('download').'</a>' : '<a href="tool_txt.php?exit='.urlencode($oH->selfurl.'?pan='.$pan).'&file='.QT_DIR_DOC.$file.'" title="'.L('Add').'...">'.qtSVG('magic').'</a>').'</td>';
echo '<td class="c-action">';

echo $bFile ? '<a href="tool_txt.php?exit='.urlencode($oH->selfurl.'?pan='.$pan).'&file='.QT_DIR_DOC.$file.'">'.L('Edit').'</a>' : '<span class="disabled">'.L('Edit').'</span>';
echo '<br>';
echo '<a href="'.APP.'_adm_tags_upload.php?pan='.$pan.'&v='.$file.'">'.L('Upload').'</a>';
echo '<br>';
echo $bFile ? '<a href="tool_txt.php?exit='.urlencode($oH->selfurl.'?pan='.$pan).'&a=delete&file='.QT_DIR_DOC.$file.'">'.L('Delete').'</a>' : '<span class="disabled">'.L('Delete').'</span>';

echo '</td></tr>'.PHP_EOL;

foreach($arrDomains as $idDom=>$strDomtitle)
{
  if ( !isset($arrSections[$idDom]) || count($arrSections[$idDom])===0 ) continue;

  // DISPLAY

  echo '<tr>'.PHP_EOL;
  echo '<td class="c-section group" colspan="2">'.$strDomtitle.'</td>'.PHP_EOL;
  echo '<td class="c-file group">&nbsp;</td>'.PHP_EOL;
  echo '<td class="c-action group">&nbsp;</td>'.PHP_EOL;
  echo '</tr>';

  foreach($arrSections[$idDom] as $intSecid=>$arrSection)
  {
    $oS = new CSection($arrSection, true);
    $file = 'tags_'.$pan.'_'.$intSecid.'.csv';
    $bFile = file_exists(QT_DIR_DOC.$file);
    echo '<tr class="hover">';
    echo '<td class="c-icon">'.asImg( CSection::makeLogo(qtExplodeGet($oS->options,'logo',''),$oS->type,$oS->status),
      'title='.L('Ico_section_'.$oS->type.'_'.$oS->status), APP.'_adm_section.php?d='.$idDom.'&s='.$oS->id ).'</td>';
    echo '<td class="c-section"><span class="bold">'.$oS->title.'</span><br><span class="small">id '.$intSecid;
    if ( $oDB->count(CSection::sqlCountItems($intSecid,'tags')) ) {
    echo ' &middot; <a href="'.APP.'_adm_tags.php?pan='.$pan.'&s='.$intSecid.'&a=used">'.L('Find_used_tags').'</a>';
    } else {
    echo ' &middot; '.L('E_no_tag');
    }
    echo '</span></td>';
    echo '<td class="c-file">'.($bFile ? $file.' <a href="'.$oH->selfurl.'?pan='.$pan.'&s='.$oS->id.'&a=view&file='.QT_DIR_DOC.$file.'" title="'.L('Preview').'">'.qtSVG('search').'</a> <a href="'.QT_DIR_DOC.$file.'" title="'.L('download').'">'.qtSVG('download').'</a>' : '<a href="tool_txt.php?exit='.urlencode($oH->selfurl.'?pan='.$pan).'&file='.QT_DIR_DOC.$file.'" title="'.L('Add').'...">'.qtSVG('magic').'</a>').'</td>';
    echo '<td class="c-action">';

    echo $bFile ? '<a href="tool_txt.php?exit='.urlencode($oH->selfurl.'?pan='.$pan).'&file='.QT_DIR_DOC.$file.'">'.L('Edit').'</a>' : '<span class="disabled">'.L('Edit').'</span>';
    echo '<br>';
    echo '<a href="'.APP.'_adm_tags_upload.php?pan='.$pan.'&v='.$file.'">'.L('Upload').'</a>';
    echo '<br>';
    echo $bFile ? '<a href="tool_txt.php?exit='.urlencode($oH->selfurl.'?pan='.$pan).'&a=delete&file='.QT_DIR_DOC.$file.'">'.L('Delete').'</a>' : '<span class="disabled">'.L('Delete').'</span>';

    echo '</td></tr>'.PHP_EOL;
  }
}

echo '</tbody>
</table>
';

// END TABS

echo '</div>
';

// PREVIEW FILE

$file = isset($_GET['file']) ? $_GET['file'] : '';
echo '<h2 style="margin:20px 0 10px 0">'.qtSVG('search').' '.L('Preview').'</h2>';

if ( empty($_GET['a']) && empty($file) ) echo '<p class="disabled">'.L('Nothing_selected').'</p>';

if ( !empty($_GET['a']) && $_GET['a']==='view' )
{
  if ( !file_exists($file) )
  {
    echo '<p class="disabled">'.$file.' File not found...</p>';
  }
  else
  {
    $s = isset($_GET['s']) ? $_GET['s'] : '*';
    $arrTags = readTagsFile($file);// read csv
    // display
    echo '<h2 class="ellipsis" style="margin:20px 0 10px 0">'.L('Proposed_tags').' &middot; '.$file.' &middot; '.($s==='*' ? L('Common_all_sections') : L('Section').' '.qtQuote(CSection::translate((int)$s), "&'")).'</h2>';
    echo '<div class="scroll">';
    echo '<table class="tags">'.PHP_EOL;
    foreach($arrTags as $strKey=>$strValue)
    {
    echo '<tr class="hover">'.PHP_EOL;
    echo '<td>'.$strKey.'</td>'.PHP_EOL;
    echo '<td>'.$strValue.'</td>'.PHP_EOL;
    echo '<td><a class="small" href="'.APP.'_items.php?q=adv&s='.$s.'&v2=*&v='.urlencode($strKey).'" title="'.L('Find_item_tag').'">'.L('Search').'</a></td>'.PHP_EOL;
    echo '</tr>';
    }
    echo '</table>'.PHP_EOL;
    echo '</div>';
  }
}

// PREVIEW FIND

if ( !empty($_GET['a']) && $_GET['a']==='used' )
{
  $s = (int)$_GET['s'];

  // search used tags
  $arrUsed = CSection::getTagsUsed($s,100);
  if ( count($arrUsed)>=100 ) $arrUsed[]='...';

  // display
  echo '<h2 class="ellipsis" style="margin:20px 0 10px 0">'.L('Used_tags').' &middot; '.L('Section').' '.qtQuote(CSection::translate($s),"&'").'</h2>
  ';

  if ( count($arrUsed)===0 )
  {
    echo '<p class="disabled">'.L('No_result').'</p>';
  }
  else
  {
    // search proposed tags
    $arrTags = readTagsFile(QT_DIR_DOC.'tags_'.$pan.'.csv');
    $arrTags2 = readTagsFile(QT_DIR_DOC.'tags_'.$pan.'_'.$s.'.csv');
    foreach($arrTags2 as $strKey=>$strValue)
    {
      if ( !isset($arrTags[$strKey]) ) $arrTags[$strKey]=$strValue;
    }

    // display

    echo '<div class="scroll">';
    echo '<table class="tags">'.PHP_EOL;
    foreach($arrUsed as $strValue)
    {
    echo '<tr class="hover">'.PHP_EOL;
    echo '<td>'.$strValue.'</td>'.PHP_EOL;
    echo '<td>'.(isset($arrTags[$strValue]) ? $arrTags[$strValue] : '&nbsp;').'</td>'.PHP_EOL;
    echo '<td><a class="small" href="'.APP.'_items.php?q=adv&s='.$s.'&v2=*&v='.$strValue.'" title="'.L('Find_item_tag').'">'.L('Search').'</a></td>'.PHP_EOL;
    echo '</tr>';
    }
    echo '</table>'.PHP_EOL;
    echo '</div>';
  }
}

// HTML END

include APP.'_adm_inc_ft.php';