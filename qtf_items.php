<?php // v4.0 build:20230205

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';

$oH->selfurl = 'qtf_items.php';
if ( SUser::role()!=='A' && $_SESSION[QT]['board_offline'] ) exitPage(99,'tools.svg',false); //...
if ( !SUser::canView('V2') ) exitPage(11, 'user-lock.svg'); //...

// ---------
// INITIALISE
// ---------

// check arguments
$q = ''; // type of search (if missing will use $q='s')
$s = ''; // section $s can be '*' or [int] (after argument checking only [int] is allowed)
$st = ''; // status $st can be '*' or [string]
$v = ''; // searched text [string] >> array of strings
$v2 = ''; // timeframe [string] or userid
qtHttp('q s st v v2'); // as [string]
if ( empty($q) ) $q = 's';
if ( $s==='*' || $s==='' || !is_numeric($s) ) $s = '-1';
if ( $st==='' ) $st = '*';
$v = asCleanArray($v);

// initialise section
$s = (int)$s;
if ( $q==='s' && $s<0 ) die(__FILE__.' Missing argument $s');
if ( $q==='s' || $s>=0 ) {
  $oS = new CSection($_Sections[$s]); // new CSection($s)
  // exit if user role not granted
  if ( $oS->type==='1' && (SUser::role()==='V' || SUser::role()==='U') ) {
    $oH->selfname = L('Section');
    $oH->exitname = SLang::translate();
    exitPage(12, 'user-lock.svg'); //...
  }
  if ( $oS->type==='2' && SUser::role()==='V' ) {
    $oH->selfname = L('Section');
    $oH->exitname = SLang::translate();
    exitPage(11, 'user-lock.svg'); //...
  }
  $oH->selfname = L('Section').': '.$oS->title;
} else {
  $oS = new CSection(); // void-section in case of search query
  $oH->selfname = L('Search_results');
}

// initialise others
$oH->selfuri = getURI('order,dir');
$strOrder = 'lastpostdate';
$strDirec = 'desc';
$strLastcol = $oS->getMF('options','last'); if  ($strLastcol=='N' || strtolower($strLastcol)==='none' ) $strLastcol='0';
$intPage = 1;
$intLimit = 0;
if ( isset($_GET['page']) ) { $intPage = (int)$_GET['page']; $intLimit = ($intPage-1)*$_SESSION[QT]['items_per_page']; }
if ( isset($_GET['order']) ) $strOrder = $_GET['order'];
if ( isset($_GET['dir']) ) $strDirec = strtolower(substr($_GET['dir'], 0, 4));
if ( isset($_GET['cid']) ) $intChecked = (int)strip_tags($_GET['cid']); // allow checking an id in edit mode
if ( isset($_POST['cid']) ) $intChecked = (int)strip_tags($_POST['cid']);
if ( !isset($_SESSION['EditByRows']) || !SUser::isStaff() ) $_SESSION['EditByRows'] = 0;
if ( !isset($_SESSION[QT]['lastcolumn']) || $_SESSION[QT]['lastcolumn']==='none' ) $_SESSION[QT]['lastcolumn'] = 'default';
$intChecked = -1; // allows checking an id when EditByRows (-1 means no check)
$navCommands = '';
$rowCommands = ''; // commands when EditByRows

// ---------
// SUBMITTED preferences and staff action
// ---------

if ( isset($_POST['pref']) ) {
  if ( in_array($_POST['pref'], array( 'n10', 'n25', 'n50', 'n100'))) $_SESSION[QT]['items_per_page'] = substr($_POST['pref'], 1, 3);
  if ( $_POST['pref']==='togglenewsontop') $_SESSION[QT]['news_on_top'] = $_SESSION[QT]['news_on_top'] ? '0' : '1';
  if ( $_POST['pref']==='toggleclosed') $_SESSION[QT]['show_closed'] = $_SESSION[QT]['show_closed'] ? '0' : '1';
}
if ( isset($_POST['modaction']) && SUser::isStaff() ) {
  if ( $_POST['modaction']==='nt') $oH->redirect('qtf_edit.php?s='.$s.'&a=nt', L('New_item')); //...
  $_SESSION[QT]['lastcolumn'] = $_POST['modaction'];
}
if ( isset($_POST['toggleedit']) && $_POST['toggleedit']==='1' && SUser::isStaff() ) {
  $_SESSION['EditByRows'] = $_SESSION['EditByRows'] ? '0' : '1';
}
// change lastcolumn if a preference exists
if ( $_SESSION[QT]['lastcolumn']!=='default' ) $strLastcol = $_SESSION[QT]['lastcolumn']; // advanced query can override preference

// -----
// QUERY parts definition
// -----

$sqlFields = ($_SESSION[QT]['news_on_top'] ? "CASE WHEN t.type='A' AND t.status='0' THEN 'A' ELSE 'Z' END as typea," : '');
$sqlFields .= 't.*,p.title,p.icon,p.id as postid,p.type as posttype,p.textmsg,p.issuedate,p.username,p.attach';
$sqlFrom = ' FROM TABTOPIC t INNER JOIN TABPOST p ON t.firstpostid=p.id'; // warning: include only firstpostid (not the replies)
$sqlWhere = ' WHERE t.forum'.($q==='s' ? '='.$s : '>=0');
  // In private section, show topics created by user himself
  if ( $q==='s' && $oS->type==='2' && !SUser::isStaff() ) $sqlWhere .= " AND (t.firstpostuser=".SUser::id()." OR (t.type='A' AND t.status='0'))";
$sqlValues = array(); // list of values for the prepared-statements
$sqlCount = "SELECT count(*) as countid FROM TABTOPIC t ".$sqlWhere;
$sqlCountAlt='';
if ( $q!=='s' ) {
  include 'bin/lib_qtf_query.php';  // warning: this changes $sqlFrom to include any post (also replies)
  $oH->warning = sqlQueryParts($sqlFrom,$sqlWhere,$sqlValues,$sqlCount,$sqlCountAlt,$oH->selfuri); //selfuri is not urldecoded
  if ( $q==='adv' && !empty($v) ) $strLastcol = 'tags'; // forces display column tags
}

$forceShowClosed = $_SESSION[QT]['show_closed']==='0' && $st==='1';
$sqlHideClosed = $_SESSION[QT]['show_closed']==='0' && !$forceShowClosed ? " AND t.status<>'1'" : ''; // User preference, hide closed items (not for advanced query having status specified)

// Count topics & visible for current user ONLY
if ( ($q=='s' && $oS->type!==2) || ( $q==='s' && SUser::isStaff()) ) {
  // Using stats info ($_SectionsStats)
  $info = isset($_SectionsStats) ? $_SectionsStats : SMem::get('_SectionsStats');
  if ( !$forceShowClosed && !isset($info[$s]['itemsZ']) ) $info[$s]['itemsZ'] = $oDB->count(CSection::sqlCountItems($s,'items','1'));
  $oH->items = empty($info[$s]['items']) ? 0 : (int)$info[$s]['items'];
  if ( !empty($sqlHideClosed) ) $oH->itemsHidden = (int)$info[$s]['itemsZ'];
} else {
  $oH->items = $oDB->count($sqlCount, $sqlValues);
  if ( !empty($sqlHideClosed) ) $oH->itemsHidden = $oH->items - $oDB->count($sqlCount.$sqlHideClosed, $sqlValues);
}
$intCount = $oH->items - $oH->itemsHidden;

// BUTTON LINE AND PAGER
if ( $q==='s' ) {
  $def = 'href="'.Href('qtf_edit.php').'?s='.$oS->id.'&a=nt|class=button btn-cmd';
  if ( $oS->status==='1' || (SUser::role()==='V' && $_SESSION[QT]['visitor_right']<7) ) {
    $def .= ' disabled|href=javascript:void(0)|tabindex=-1|title='.($oS->status==='1' ? L('E_section_closed') : L('R_member')); // class=button btn-cmd disabled
  }
  $navCommands .= '<a'.attrRender($def).'>'.L('New_item').'</a>';
}
$navCommands .= '<a class="button btn-search" href="'.Href('qtf_search.php').'?'.$oH->selfuri.'" title="'.L('Search').'">'.getSVG('search').'</a>';

$strPaging = makePager( Href($oH->selfurl).'?'.$oH->selfuri, $intCount, (int)$_SESSION[QT]['items_per_page'], $intPage);
if ( $strPaging!='' ) $strPaging = L('Page').$strPaging;

// MAP
$bMap = false; // map is only used for user's location

// Page title or description
$pageTitle ='';
$navCommandsRefine = '';

switch($q) {

  case 's': if ( QT_SHOW_PARENT_DESCR ) $pageTitle = CSection::translate($s,'secdesc'); break;
  case 'ref': $pageTitle .= sprintf( L('Search_results_ref'), $v[0] ); break;
  case 'qkw':
  case 'kw':
    $arrVlbl = qtQuoted($v,"&'");
    $to = isset($_GET['to']) ? $_GET['to'] : '0';
    $pageTitle .= sprintf( L('Search_results_keyword'), strtolower(implode(' '.L('or').' ',$arrVlbl)) );
    // for refine search detection: trim and remove quote on $v to avoid trailing quote be interpreted as a 2d word
    if ( count($v)==1 && strpos(qtAttr($v[0]),' ')!==false ) $navCommandsRefine = '<a class="button" href="'.$oH->selfurl.'?q=kw&to='.$to.'&v='.urlencode(str_replace(' ',QSEPARATOR,$v[0])).'"><small>'.L('Search_by_words').'</small></a>';
    if ( count($v)==1 && strpos($v[0],QSEPARATOR)!==false ) $navCommandsRefine = '<a class="button" href="'.$oH->selfurl.'?q=kw&to='.$to.'&v='.urlencode(str_replace(QSEPARATOR,' ',$v[0])).'"><small>'.L('Search_exact_words').' &lsquo;'.str_replace(QSEPARATOR,' ',$v).'&rsquo;</small></a>';
    if ( $to=='1' ) $pageTitle .= ' '. L('in_title_only');
    break;
  case 'user':
    $pageTitle .= sprintf(L('Search_results_user'), implode(' '.L('or').' ',$v));
    $navCommandsRefine = '<a class="button" href="'.Href('qtf_items.php').'?q=userm&'.getUri('q').'"><small>'.L('Search').': '.L('item+').' '.L('and').' '.L('reply+').'</small></a>';
   break;
  case 'userm':
    $pageTitle .= sprintf(L('Search_results_user_m'), implode(' '.L('or').' ',$v));
    $navCommandsRefine = '<a class="button" href="'.Href('qtf_items.php').'?q=user&'.getUri('q').'"><small>'.L('Search').': '.L('item+').' '.L('only').'</small></a>';
     break;
  case 'actor': $pageTitle .= sprintf(L('Search_results_actor'), implode(' '.L('or').' ',$v) ); break;
  case 'last': $pageTitle .= L('Search_results_last'); break;
  case 'news': $pageTitle .= L('Search_results_news'); break;
  case 'adv':
    $arrVlbl = qtQuoted($v,"&'");
    $pageTitle .= sprintf( L(empty($arrVlbl) ? 'Search_results' : 'Search_results_tags'), strtolower(implode(' '.L('or').' ',$arrVlbl)) );
    if ( $v2!=='*' ) {
      switch($v2){
        case 'y': $pageTitle .= ' '.L('this_year'); break;
        case 'm': $pageTitle .= ' '.L('this_month'); break;
        case 'w': $pageTitle .= ' '.L('this_week'); break;
        default: $pageTitle .= ', '.L('dateMMM.'.$v2);
      }
    }
    break;
  default:
    $arrVlbl = $v;
    $pageTitle .= empty($arrVlbl) ? L('Item+',$oS->items) : sprintf( L('Search_results'), $oS->items, implode(' '.L('or').' ',$arrVlbl) );

}

// search options subtitle
$pageSubtitle = '';
if ( $q!=='s' ) {
  if ( $s>=0 ) $pageSubtitle = L('only_in_section').' &lsquo;'.CSection::translate($s).'&rsquo;';
  if ( $st!=='*' ) $pageSubtitle .= (empty($pageSubtitle) ? '' : ', ').L('status').' '.CTopic::getStatus($st);
}
// full title
if ( !empty($pageTitle) ) $pageTitle = '<p class="pg-title">'.$pageTitle.'</p>'.(empty($pageSubtitle) ? '' : '<p class="pg-title pg-subtitle">'.$pageSubtitle.'</p>');

// --------
// HTML BEGIN
// --------

include APP.'_inc_hd.php';

// PAGE title and UI
include APP.'_items_ui.php'; // $ui
if ( !empty($pageTitle) || !empty($ui) ) {
  echo '<div id="title-top" class="flex-sp top">'.PHP_EOL;
  echo '<div id="title-top-l">'.$pageTitle.'</div>'.PHP_EOL;
  echo '<div id="title-top-r" class="optionsbar-container">'.$ui.'</div>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
}

// Notes
if  ( !empty($oH->warning) ) echo '<p class="warning">'.getSVG('exclamation-triangle').' '.$oH->warning.'</p>';

$navCommands = $oH->backButton().$navCommands.$navCommandsRefine;

// End if no results
if ( $intCount===0 ) {

  // if no result with sqlHideClosed, re-count without
  if ( !empty($sqlHideClosed) ) $intCount = $oDB->count($sqlCount.$sqlHideClosed, $sqlValues);
  echo '<div class="nav-top">'.$navCommands.'</div>'.PHP_EOL;
  echo '<p class="center" style="margin:1rem 0">'.L('No_result').'...</p>';
  if ( $oS->type==='2' && !SUser::isStaff() ) echo '<p class="center">'.L('Only_your_items').'</p>';
  if ( $intCount ) echo '<p class="center">'.getSVG('exclamation-triangle').' '.L('Closed_item', $intCount).'. '.L('Closed_hidden_by_pref').' (<a href="javascript:void(0)" onclick="let d=document.getElementById(`pref`); if ( d) {d.value=`toggleclosed`;doSubmit(`formPref`);}">'.L('show').' '.L('closed_items').'</a>).</p>';
  // alternate query
  $arg = 'q='.$q;
  if ( $q==='user' || $q==='kw' || $q==='adv' ) $arg .= '&v='.implode(';',$v).'&v2='.urlencode($v2);
  echo '<p class="center"><a href="'.Href('qtf_items.php').'?'.$arg.'">'.L('Try_without_options').'</a></p>';
  include 'qtf_inc_ft.php';
  exit;

}

// Table definition
// selfuri contains arguments WITHOUT order,dir
$t = new TabTable('id=t1|class=t-item', $intCount);
  $t->activecol = $strOrder;
  $t->activelink = '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order='.$strOrder.'&dir='.($strDirec==='asc' ? 'desc' : 'asc').'">%s</a> '.getSVG('caret-'.($strDirec==='asc' ? 'up' : 'down'));
  $t->thead();
  $t->tbody();
// TH (note: class are defined after)
if ( $_SESSION['EditByRows'])
$t->arrTh['checkbox'] = new TabHead($t->countDataRows<2 ? '&nbsp;' : '<input type="checkbox" name="t1-cb-all" id="t1-cb-all"/>');
$t->arrTh['icon'] = new TabHead('&bull;', '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order=icon&dir=asc">%s</a>');
if ( $q!=='s' || ( $q==='s' && $oS->numfield!=='N' && $oS->numfield!=='' ) )
$t->arrTh['numid'] = new TabHead(L('Ref'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order=numid&dir=desc">%s</a>');
$t->arrTh['title'] = new TabHead(L('Item+'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order=title&dir=asc">%s</a>');
if ( !empty($q) && $s<0)
$t->arrTh['section'] = new TabHead(L('Section'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order=section&dir=asc">%s</a>');
$t->arrTh['firstpostname'] = new TabHead(L('Author'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order=firstpostname&dir=asc">%s</a>');
$t->arrTh['lastpostdate'] = new TabHead(L('Last_message'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order=lastpostdate&dir=desc">%s</a>');
$t->arrTh['replies'] = new TabHead(L('Reply+'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order=replies&dir=desc">%s</a>');
if ( in_array($strLastcol,['id','views','status','tags']) )
$t->arrTh[$strLastcol] = new TabHead(L(ucfirst($strLastcol)), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&order='.$strLastcol.'&dir=desc">%s</a>');
// add default class {c-$k}
foreach(array_keys($t->arrTh) as $k) $t->arrTh[$k]->add('class', 'c-'.$k);
// append class secondary
foreach(['firstpostname','tags','views'] as $k) if ( isset($t->arrTh[$k])) $t->arrTh[$k]->append('class', 'secondary');
// append class ellipsis
foreach(['firstpostname','lastpostdate','replies','views','id','status','section'] as $k) if ( isset($t->arrTh[$k])) $t->arrTh[$k]->append('class', 'ellipsis');
// TD
$t->cloneThTd();

// Edit mode
if ( $_SESSION['EditByRows']) {

  $rowCommands = '<a class="rowcommands" href="javascript:void(0)" data-action="itemsType">'.L('Type').'/'.L('Status').'</a>';
  $rowCommands .= ' &middot; <a class="rowcommands" href="javascript:void(0)" data-action="itemsTags">'.L('Tags').'</a>';
  $rowCommands .= ' &middot; <a class="rowcommands" href="javascript:void(0)" data-action="itemsMove">'.L('Move').'</a>';
  $rowCommands .= ' &middot; <a class="rowcommands" href="javascript:void(0)" data-action="itemsDelete">'.L('Delete').'</a>'.PHP_EOL;
  $oH->scripts[] = '<script id="cbe" type="text/javascript" src="bin/js/qt_table_cb.js" data-tableid="t1"></script>';
  $oH->scripts[] = 'const cmds = document.getElementsByClassName("checkboxcmds");
  for (const el of cmds){ el.addEventListener("click", (e)=>{ if ( e.target.tagName==="A" ) datasetcontrol_click("t1-cb[]", e.target.dataset.action); }); }
  function datasetcontrol_click(checkboxname,action)
  {
    const checkboxes = document.getElementsByName(checkboxname);
    let n = 0;
    for (let i=0; i<checkboxes.length; ++i) if ( checkboxes[i].checked ) ++n;
    if ( n>0 ) {
      document.getElementById("form-items-action").value=action;
      document.getElementById("form-items").submit();
    } else {
      alert("'.L('Nothing_selected').'");
    }
    return false;
  }';
  $oH->scripts[] = 'const cmdExport = document.getElementById("cmd-export-selected");
if ( cmdExport ) {
  cmdExport.addEventListener("click", ()=>{
    const checkboxes = document.getElementsByName("t1-cb[]");
    let ids = new Array();
    for (let i=0; i<checkboxes.length; ++i) if ( checkboxes[i].checked ) ids.push(checkboxes[i].value);
    if ( ids.length===0 ) return alert("'.L('Nothing_selected').'");
    cmdExport.href = "qtf_items_ids2csv.php?'.$oH->selfuri.'&ids=" + ids.join(",");
  });
}';
}

// Buttons and paging
echo '<div id="t1-nav-top" class="nav-top">'.$navCommands.'</div>'.PHP_EOL;
echo '<div id="tabletop" class="table-ui top">';
echo $rowCommands ? '<div id="t1-edits-top" class="left checkboxcmds">'.getSVG('corner-up-right','class=arrow-icon').$rowCommands.'</div>' : '<div></div>';
echo '<div class="right">'.$strPaging.'</div></div>'.PHP_EOL;

// TABLE START DISPLAY
if ( $_SESSION['EditByRows']) {
  echo '<form id="form-items" method="post" action="'.Href('qtf_dlg.php').'">
<input type="hidden" id="form-items-action" name="a"/>
<input type="hidden" name="uri" value="'.$oH->selfuri.'"/>
';
}

echo $t->start();
echo $t->thead->start();
echo $t->getTHrow('');
echo $t->thead->end();
echo $t->tbody->start();

// ========
$sqlOrder = $strOrder==='title' ? 'p.title' : 't.'.$strOrder;
if ( $sqlOrder==='t.section' ) $sqlOrder = 't.forum';
if ( $sqlOrder==='t.icon' ) $sqlOrder='t.status';
$oDB->query(sqlLimit(
  $sqlFields.$sqlFrom.$sqlWhere.$sqlHideClosed,
  ($_SESSION[QT]['news_on_top'] ? 'typea ASC, ' : '').$sqlOrder.' '.strtoupper($strDirec),
  $intLimit,
  $_SESSION[QT]['items_per_page'],
  $intCount
  ), $sqlValues);
// ========

$intRow=0; // count row displayed
$arrRe = array(); // topic id having replies (use in post-processing)
$arrTags = array();
$arrOptions = [];
$arrOptions['bmap'] = $bMap;
if ( $_SESSION[QT]['item_firstline']==='0' ) {
  $arrOptions['firstline'] = false;
} elseif ( $_SESSION[QT]['item_firstline']==='2' ) {
  $arrOptions['firstline'] = $oS->getMF('options','if','0')==='1';
} else {
  $arrOptions['firstline'] = true;
}

while($row = $oDB->getRow()) {

  if ( $row['replies']>0 ) $arrRe[] = (int)$row['id'];

  // prepare values, and insert value into the cells
  $t->setTDcontent(formatItemRow('t1', $t->getTHnames(), $row, $oS, $arrOptions), false); // adding extra columns not allowed

  // dynamic style (reset class c-status open|closed
  if ( isset($t->arrTd['status']) ) $t->arrTd['status']->add('class', 'c-status '.($row['status'] ? 'closed' : 'opened'));

  // prepare checkbox (edit mode)
  if ( $_SESSION['EditByRows'] ) {
    $bChecked = $row['id']==$intChecked;
    if ( $row['posttype']==='P') $t->arrTd['checkbox']->content = '<input type="checkbox" name="t1-cb[]" id="t1-cb-'.$row['id'].'" value="'.$row['id'].'"'.($bChecked ? 'checked' : '').' data-row="'.$intRow.'"/>';
  }
  // show row content
  echo $t->getTDrow('id=t1-tr-'.$row['id'].'|class=t-item hover rowlight');
  // collects and appends unique tags (32+)
  if ( QT_LIST_TAG && !empty($_SESSION[QT]['tags']) && !empty($row['tags']) && count($arrTags)<32 ) $arrTags = asCleanArray($row['tags'], ';', $arrTags);
  // odbcbreak
  ++$intRow; if ( $intRow>=$_SESSION[QT]['items_per_page'] ) break;
}

// === TABLE END DISPLAY ===

echo $t->tbody->end();
echo $t->end();

if ( SUser::isStaff() && !empty($_SESSION['EditByRows']) ) echo '</form>'.PHP_EOL;

// BUTTON LINE AND PAGER
$strCsv = '';
if ( SUser::isStaff() && !empty($_SESSION['EditByRows']) ) $strCsv .= '<a id="cmd-export-selected" class="csv" href="javascript:void(0)" title="'.L('H_Csv').' ('.L('selected').')">'.L('Export').getSVG('check-square').'</a> &middot; ';
$strCsv .= SUser::role()==='V' ? '' : htmlCsvLink(Href('qtf_items_csv.php').'?'.$oH->selfuri, $intCount, $intPage);
echo '<div id="tablebot" class="table-ui bot">';
echo $rowCommands ? '<div id="t1-edits-bot" class="left checkboxcmds">'.getSVG('corner-down-right','class=arrow-icon').$rowCommands.'</div>' : '<div></div>';
echo '<div class="right">'.$strPaging.'</div></div>'.PHP_EOL;
echo '<p class="right table-ui-export">'.$strCsv.'</p>'.PHP_EOL;
echo '<div id="t1-nav-bot" class="nav-bot">'.$navCommands.'</div>'.PHP_EOL;

// TAGS FILTRING
if ( QT_LIST_TAG && !empty($_SESSION[QT]['tags']) && count($arrTags)>0 ) {
  sort($arrTags);
  echo '<div class="tag-box"><p><svg class="svg-symbol svg-125"><use href="#symbol-tags" xlink:href="#symbol-tags"></use></svg> '.L('Show_only_tag').'</p>';
  foreach($arrTags as $strTag)
    echo '<a class="tag" href="'.Href('qtf_items.php').'?s='.$s.'&q=adv&v2=*&v='.urlencode($strTag).'" title="..." data-tagdesc="'.$strTag.'">'.$strTag.'</a>';
  echo getSVG('search','','',true).'</div>';
  $oH->scripts['tagdesc'] = '<script type="text/javascript" src="bin/js/qt_tagdesc.js" id="tagdesc" data-dir="'.QT_DIR_DOC.'" data-lang="'.QT_LANG.'"></script>';
}

// Post-compute user's replied items (for topics having replies). Result is added using js.
if ( QT_LIST_ME && count($arrRe)>0 && (int)SUser::getInfo('numpost',0)>0 ) {
  $arr = array();
  $oDB->query( "SELECT topic,issuedate FROM TABPOST WHERE type='R' AND userid=".SUser::id()." AND topic IN (".implode(',', $arrRe).")" );
  while($row = $oDB->getRow())
    $arr[(int)$row['topic']] = '"'.QTdatestr($row['issuedate'], 'j M', 'H:i', true, true).'"';
  if ( count($arr)>0 ) {
    $oH->scripts[] = 'function addIRe(table,tids,ttitles,title="I replied") {
  for (let i=0;i<tids.length;++i) {
    const el = document.getElementById(table+"re"+tids[i]);
    if ( el ) el.setAttribute("title", ttitles[i]+", "+title);
  }
}
addIRe("t1",['.implode(',', array_keys($arr)).'],['.implode(',', $arr).'],"'.L('You_reply').'");';
  }
}

// --------
// HTML END
// --------

// hide href column if empty
if ( $q!=='s' ) $oH->scripts[] = 'function hideEmptyColumn(id="t1",col="c-numid"){
  const cols = document.querySelectorAll(`#${id} td.${col}`);
  if ( cols.length===0 ) return;
  for(i=0;i<cols.length;++i) if ( cols.item(i).innerHTML!=="" ) return;
  document.querySelector(`#${id} th.${col}`).style.display="none";
  cols.forEach( el => { el.style.display="none"; } );
}
hideEmptyColumn();';

// hide table-ui-bottom-controls if less than 5 table rows
$oH->scripts[] = 'qtHideAfterTable("t1-nav-bot");qtHideAfterTable("tablebot");';

// Symbols
echo '<svg xmlns="http://www.w3.org/2000/svg" style="display:none">'.PHP_EOL;
echo getSVG('symbol-caret-square-right').PHP_EOL;
if ( QT_LIST_ME ) echo getSVG('symbol-ireplied').PHP_EOL;
if ( $_SESSION[QT]['upload']!=='0' ) echo getSVG('symbol-paperclip').PHP_EOL;
if ( !empty($_SESSION[QT]['tags']) ) {
  echo getSVG('symbol-tag').PHP_EOL;
  echo getSVG('symbol-tags').PHP_EOL;
}
echo '</svg>'.PHP_EOL;

include APP.'_inc_ft.php';