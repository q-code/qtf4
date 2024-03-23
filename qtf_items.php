<?php // v4.0 build:20240210

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';

$oH->selfurl = 'qtf_items.php';
if ( SUser::role()!=='A' && $_SESSION[QT]['board_offline'] ) exitPage(99,'tools.svg',false); //...
if ( !SUser::canView('V2') ) exitPage(11, 'user-lock.svg'); //...

// ------
// INITIALISE
// ------

// init args
$s = -1; // [int]
$fq = ''; // Search type (not required, use 's' if missing)
$fst = ''; // Status [string] {''|status-key}, caution: can be '0'
$fv = ''; // Searched [string] text (converted to array of strings)
$fw = ''; // timeframe [string] or userid
$pn = 1; $po = 'lastpostdate'; $pd = 'desc'; // page number,order,direction
qtArgs('int:s fq fst fv fw int:pn po pd');

// check args
if ( empty($fq) ) $fq = 's';
if ( $fq==='s' && $s<0 ) die(__FILE__.' Missing argument $s');
$fv = qtCleanArray($fv); // [array]

// initialise section or void-section and check specific access right
if ( $fq==='s' ) {
  $oS = new CSection($_Sections[$s]); // new CSection($s)
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
  $oS = new CSection();
  $oH->selfname = L('Search_results');
}

// initialise others
$oH->selfuri = qtURI('pn|po|pd');
$strLastcol = $oS->getMF('options','last'); if  ($strLastcol=='N' || strtolower($strLastcol)==='none' ) $strLastcol='0';
if ( !isset($_SESSION['EditByRows']) || !SUser::isStaff() ) $_SESSION['EditByRows'] = 0;
if ( !isset($_SESSION[QT]['lastcolumn']) || $_SESSION[QT]['lastcolumn']==='none' ) $_SESSION[QT]['lastcolumn'] = 'default';
$navCommands = '';
$rowCommands = ''; // commands when EditByRows

// -----
// SUBMITTED preferences
// -----
if ( isset($_POST['pref']) ) {
  if ( in_array((int)$_POST['pref'],PAGE_SIZES) ) $_SESSION[QT]['items_per_page'] = $_POST['pref'];
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
// Change lastcolumn if a preference exists. Advanced query can override preference (but not change preference)
if ( $_SESSION[QT]['lastcolumn']!=='default' ) $strLastcol = $_SESSION[QT]['lastcolumn'];

// -----
// QUERY
// -----
$sqlStart = ($pn-1)*$_SESSION[QT]['items_per_page'];
$sqlFields = ($_SESSION[QT]['news_on_top'] ? "CASE WHEN t.type='A' AND t.status='0' THEN 'A' ELSE 'Z' END as typea," : '');
$sqlFields .= 't.*,p.title,p.icon,p.id as postid,p.type as posttype,p.textmsg,p.issuedate,p.username,p.attach';
$sqlFrom = ' FROM TABTOPIC t INNER JOIN TABPOST p ON t.firstpostid=p.id'; // warning: include only firstpostid (not the replies)
$sqlWhere = ' WHERE t.forum'.($fq==='s' ? '='.$s : '>=0');
  // In private section, show topics created by user himself
  if ( $fq==='s' && $oS->type==='2' && !SUser::isStaff() ) $sqlWhere .= " AND (t.firstpostuser=".SUser::id()." OR (t.type='A' AND t.status='0'))";
$sqlValues = []; // list of values for the prepared-statements
$sqlCount = "SELECT count(*) as countid FROM TABTOPIC t ".$sqlWhere;
$sqlCountAlt='';
if ( $fq!=='s' ) {
  include 'bin/lib_qtf_query.php';  // warning: this changes $sqlFrom to include any post (also replies)
  $oH->warning = sqlQueryParts($sqlFrom,$sqlWhere,$sqlValues,$sqlCount,$sqlCountAlt,$oH->selfuri); //selfuri is not urldecoded
  if ( $fq==='adv' && !empty($fv) ) $strLastcol = 'tags'; // forces display column tags
}
$forceShowClosed = $_SESSION[QT]['show_closed']==='0' && $fst==='1';
$sqlHideClosed = $_SESSION[QT]['show_closed']==='0' && !$forceShowClosed ? " AND t.status<>'1'" : ''; // User preference, hide closed items (not for advanced query having status specified)
// Count topics & visible for current user ONLY
if ( ($fq=='s' && $oS->type!==2) || ( $fq==='s' && SUser::isStaff()) ) {
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

// -----
// BUILD HTML COMPONENTS
// -----

// PREFERENCES
include APP.'_items_ui.php'; // $ui

// BUTTON LINE AND PAGER
if ( $fq==='s' ) {
  $def = 'href="'.url('qtf_edit.php').'?s='.$oS->id.'&a=nt|class=button btn-cmd';
  if ( $oS->status==='1' || (SUser::role()==='V' && $_SESSION[QT]['visitor_right']<7) ) {
    $def .= ' disabled|href=javascript:void(0)|tabindex=-1|title='.($oS->status==='1' ? L('E_section_closed') : L('R_member')); // class=button btn-cmd disabled
  }
  $navCommands .= '<a'.attrRender($def).'>'.L('New_item').'</a>';
}
$navCommands .= '<a class="button btn-search" href="'.url('qtf_search.php').'?'.$oH->selfuri.'" title="'.L('Search').'">'.qtSVG('search').'</a>';

$strPaging = makePager( url($oH->selfurl).'?'.$oH->selfuri, $intCount, (int)$_SESSION[QT]['items_per_page'], $pn);
if ( $strPaging!='' ) $strPaging = L('Page').$strPaging;

// MAP
$bMap = false; // map is only used for user's location

// Page title or description
$pageTitle ='';
$navCommandsRefine = '';

switch($fq) {
  case 's': if ( QT_SHOW_PARENT_DESCR ) $pageTitle = CSection::translate($s,'secdesc'); break;
  case 'ref': $pageTitle .= sprintf( L('Search_results_ref'), $fv[0] ); break;
  case 'qkw':
  case 'kw':
    $arrVlbl = qtQuote($fv,"&'");
    $to = isset($_GET['to']) ? $_GET['to'] : '0';
    $pageTitle .= sprintf( L('Search_results_keyword'), strtolower(implode(' '.L('or').' ',$arrVlbl)) );
    // for refine search detection: trim and remove quote on $fv to avoid trailing quote be interpreted as a 2d word
    if ( count($fv)==1 && strpos(qtAttr($fv[0]),' ')!==false ) $navCommandsRefine = '<a class="button" href="'.$oH->selfurl.'?fq=kw&to='.$to.'&fv='.urlencode(str_replace(' ',QSEPARATOR,$fv[0])).'"><small>'.L('Search_by_words').'</small></a>';
    if ( count($fv)==1 && strpos($fv[0],QSEPARATOR)!==false ) $navCommandsRefine = '<a class="button" href="'.$oH->selfurl.'?fq=kw&to='.$to.'&fv='.urlencode(str_replace(QSEPARATOR,' ',$fv[0])).'"><small>'.L('Search_exact_words').' &lsquo;'.str_replace(QSEPARATOR,' ',$fv).'&rsquo;</small></a>';
    if ( $to=='1' ) $pageTitle .= ' '. L('in_title_only');
    break;
  case 'user':
    $pageTitle .= sprintf(L('Search_results_user'), implode(' '.L('or').' ',$fv));
    $navCommandsRefine = '<a class="button" href="'.url('qtf_items.php').'?fq=userm&'.qtUri('q').'"><small>'.L('Search').': '.L('item+').' '.L('and').' '.L('reply+').'</small></a>';
   break;
  case 'userm':
    $pageTitle .= sprintf(L('Search_results_user_m'), implode(' '.L('or').' ',$fv));
    $navCommandsRefine = '<a class="button" href="'.url('qtf_items.php').'?fq=user&'.qtUri('q').'"><small>'.L('Search').': '.L('item+').' '.L('only').'</small></a>';
     break;
  case 'actor': $pageTitle .= sprintf(L('Search_results_actor'), implode(' '.L('or').' ',$fv) ); break;
  case 'last': $pageTitle .= L('Search_results_last'); break;
  case 'news': $pageTitle .= L('Search_results_news'); break;
  case 'adv':
    $arrVlbl = qtQuote($fv,"&'");
    $pageTitle .= sprintf( L(empty($arrVlbl) ? 'Search_results' : 'Search_results_tags'), strtolower(implode(' '.L('or').' ',$arrVlbl)) );
    if ( $fw!=='*' ) {
      switch($fw){
        case 'y': $pageTitle .= ' '.L('this_year'); break;
        case 'm': $pageTitle .= ' '.L('this_month'); break;
        case 'w': $pageTitle .= ' '.L('this_week'); break;
        default: $pageTitle .= ', '.L('dateMMM.'.$fw);
      }
    }
    break;
  default:
    $arrVlbl = $fv;
    $pageTitle .= empty($arrVlbl) ? L('Item+',$oS->items) : sprintf( L('Search_results'), $oS->items, implode(' '.L('or').' ',$arrVlbl) );
}

// search options subtitle
$pageSubtitle = '';
if ( $fq!=='s' ) {
  if ( $s>=0 ) $pageSubtitle = L('only_in_section').' &lsquo;'.CSection::translate($s).'&rsquo;';
  if ( $fst!=='' ) $pageSubtitle .= (empty($pageSubtitle) ? '' : ', ').L('status').' '.CTopic::getStatus($fst);
}
// full title
if ( !empty($pageTitle) ) $pageTitle = '<p class="pg-title">'.$pageTitle.'</p>'.(empty($pageSubtitle) ? '' : '<p class="pg-title pg-subtitle">'.$pageSubtitle.'</p>');

// ------
// HTML BEGIN
// ------
include APP.'_inc_hd.php';

// PAGE title and UI
if ( !empty($pageTitle) || !empty($ui) ) {
  echo '<div id="title-top" class="flex-sp top">'.PHP_EOL;
  echo '<div id="title-top-l">'.$pageTitle.'</div>'.PHP_EOL;
  echo '<div id="title-top-r" class="optionsbar-container">'.$ui.'</div>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
}

// Notes
if  ( !empty($oH->warning) ) echo '<p class="warning">'.qtSVG('exclamation-triangle').' '.$oH->warning.'</p>';

$navCommands = $oH->backButton().$navCommands.$navCommandsRefine;

// End if no results
if ( $intCount===0 ) {
  // if no result with sqlHideClosed, re-count without
  if ( !empty($sqlHideClosed) ) $intCount = $oDB->count($sqlCount, $sqlValues);
  echo '<div class="nav-top">'.$navCommands.'</div>'.PHP_EOL;
  echo '<p class="center" style="margin:1rem 0">'.L('No_result').'...</p>';
  if ( $oS->type==='2' && !SUser::isStaff() ) echo '<p class="center">'.L('Only_your_items').'</p>';
  if ( $intCount ) echo '<p class="center">'.qtSVG('exclamation-triangle').' '.L('Closed_item', $intCount).'. '.L('Closed_hidden_by_pref').' (<a href="javascript:void(0)" onclick="let d=document.getElementById(`pref`); if ( d) {d.value=`toggleclosed`;doSubmit(`formPref`);}">'.L('show').' '.L('closed_items').'</a>).</p>';
  // alternate query
  if ( $s>=0 || $fst!=='' ) {
    $arg = 'q='.$fq;
    if ( $fq==='user' || $fq==='kw' || $fq==='adv' ) $arg .= '&fv='.implode(';',$fv).'&fw='.urlencode($fw);
    echo '<p class="center"><a href="'.url('qtf_items.php').'?'.$arg.'">'.L('Try_without_options').'</a></p>';
  }
  include 'qtf_inc_ft.php';
  exit;
}

// Table definition
$useNewsOnTop = $_SESSION[QT]['news_on_top'];
// selfuri contains arguments WITHOUT order,dir
$t = new TabTable('id=t1|class=t-item|data-cbe', $intCount);
  $t->activecol = $po;
  $t->activelink = '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&po='.$po.'&pd='.($pd==='asc' ? 'desc' : 'asc').'">%s</a> '.qtSVG('caret-'.($pd==='asc' ? 'up' : 'down'));
  $t->thead();
  $t->tbody('data-dataset='.($useNewsOnTop ? 'newsontop' : 'items'));
// TH (note: class are defined after)
if ( $_SESSION['EditByRows'] )
$t->arrTh['checkbox'] = new TabHead($t->countDataRows<2 ? '&nbsp;' : '<input type="checkbox" data-target="t1-cb[]"/>');
$t->arrTh['icon'] = new TabHead('&bull;', '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&po=icon&pd=asc">%s</a>');
if ( $fq!=='s' || ( $fq==='s' && $oS->numfield!=='N' && $oS->numfield!=='' ) )
$t->arrTh['numid'] = new TabHead(L('Ref'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&po=numid&pd=desc">%s</a>');
$t->arrTh['title'] = new TabHead(L('Item+'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&po=title&pd=asc">%s</a>');
if ( !empty($fq) && $s<0 )
$t->arrTh['section'] = new TabHead(L('Section'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&po=section&pd=asc">%s</a>');
$t->arrTh['firstpostname'] = new TabHead(L('Author'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&po=firstpostname&pd=asc">%s</a>');
$t->arrTh['lastpostdate'] = new TabHead(L('Last_message'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&po=lastpostdate&pd=desc">%s</a>');
$t->arrTh['replies'] = new TabHead(L('Reply+'), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&po=replies&pd=desc">%s</a>');
if ( in_array($strLastcol,['id','views','status','tags']) )
$t->arrTh[$strLastcol] = new TabHead(L(ucfirst($strLastcol)), '', '<a href="'.$oH->selfurl.'?'.$oH->selfuri.'&po='.$strLastcol.'&pd=desc">%s</a>');
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

  $rowCommands = '<a class="rowcmd" href="javascript:void(0)" data-action="itemsType">'.L('Type').'/'.L('Status').'</a>';
  $rowCommands .= ' &middot; <a class="rowcmd" href="javascript:void(0)" data-action="itemsTags">'.L('Tags').'</a>';
  $rowCommands .= ' &middot; <a class="rowcmd" href="javascript:void(0)" data-action="itemsMove">'.L('Move').'</a>';
  $rowCommands .= ' &middot; <a class="rowcmd" href="javascript:void(0)" data-action="itemsDelete">'.L('Delete').'</a>'.PHP_EOL;
  $oH->scripts[] = '<script type="text/javascript" src="bin/js/qt_table_cb.js"></script>';
  $oH->scripts[] = 'const cmds = document.getElementsByClassName("checkboxcmds");
  for (const el of cmds){ el.addEventListener("click", (e)=>{ if ( e.target.tagName==="A" ) clickRowCmd("t1-cb[]", e.target.dataset.action); }); }
  function clickRowCmd(checkboxname,action)
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
echo $rowCommands ? '<div id="t1-edits-top" class="left checkboxcmds">'.qtSVG('corner-up-right','class=arrow-icon').$rowCommands.'</div>' : '<div></div>';
echo '<div class="right">'.$strPaging.'</div></div>'.PHP_EOL;

// TABLE START DISPLAY
if ( $_SESSION['EditByRows']) {
  echo '<form id="form-items" method="post" action="'.url('qtf_dlg.php').'">
<input type="hidden" id="form-items-action" name="a"/>
<input type="hidden" name="s" value="'.$s.'"/>
<input type="hidden" name="uri" value="'.$oH->selfuri.'"/>
';
}

echo $t->start();
echo $t->thead->start();
echo $t->getTHrow('');
echo $t->thead->end();
echo $t->tbody->start();

// ========
$sqlOrder = $po==='title' ? 'p.title' : 't.'.$po;
if ( $sqlOrder==='t.section' ) $sqlOrder = 't.forum';
if ( $sqlOrder==='t.icon' ) $sqlOrder='t.status';
$oDB->query(sqlLimit(
  $sqlFields.$sqlFrom.$sqlWhere.$sqlHideClosed,
  ($_SESSION[QT]['news_on_top'] ? 'typea ASC, ' : '').$sqlOrder.' '.strtoupper($pd),
  $sqlStart,
  $_SESSION[QT]['items_per_page'],
  $intCount
  ), $sqlValues);
// ========

$intRow=0; // count row displayed
$arrRe = []; // topic id having replies (use in post-processing)
$arrTags = [];
$arrOptions = [];
$arrOptions['bmap'] = $bMap;
if ( $_SESSION[QT]['item_firstline']==='0' ) {
  $arrOptions['firstline'] = false;
} elseif ( $_SESSION[QT]['item_firstline']==='2' ) {
  $arrOptions['firstline'] = $oS->getMF('options','if','0')==='1';
} else {
  $arrOptions['firstline'] = true;
}

while( $row = $oDB->getRow() ) {

  // check if end of a tbody (dataset group)
  if ( $useNewsOnTop && !empty($row['typea']) && $row['typea']==='Z' ) {
    $useNewsOnTop = false; // end of news on top
    echo $t->tbody->end();
    $t->tbody->add('data-dataset', 'items');
    echo $t->tbody->start();
  }

  // FORMAT the data
  $t->setTDcontent(formatItemRow('t1', $t->getTHnames(), $row, $oS, $arrOptions), false); // adding extra columns not allowed
  // dynamic style (class c-status open|closed)
  if ( isset($t->arrTd['status']) )
  $t->arrTd['status']->add('class', 'c-status '.($row['status'] ? 'closed' : 'opened'));
  // add checkbox if edit mode
  if ( $_SESSION['EditByRows'] && $row['posttype']==='P' )
  $t->arrTd['checkbox']->content = '<input type="checkbox" name="t1-cb[]" id="t1-cb-'.$row['id'].'" value="'.$row['id'].'" data-row="'.$intRow.'"/>';

  // OUPUT the row
  echo $t->getTDrow('id=t1-tr-'.$row['id'].'|class=t-item hover rowlight');

  // COLLECT
  if ( $row['replies']>0 ) $arrRe[] = (int)$row['id'];
  if ( QT_LIST_TAG && !empty($_SESSION[QT]['tags']) && !empty($row['tags']) && count($arrTags)<32 ) $arrTags = qtCleanArray($row['tags'], ';', $arrTags);
  // security limit break
  ++$intRow; if ( $intRow>=$_SESSION[QT]['items_per_page'] ) break;

}

// === TABLE END DISPLAY ===

echo $t->tbody->end();
echo $t->end();

if ( SUser::isStaff() && !empty($_SESSION['EditByRows']) ) echo '</form>'.PHP_EOL;

// BUTTON LINE AND PAGER
$strCsv = '';
if ( SUser::isStaff() && !empty($_SESSION['EditByRows']) ) $strCsv .= '<a id="cmd-export-selected" class="csv" href="javascript:void(0)" title="'.L('H_Csv').' ('.L('selected').')">'.L('Export').qtSVG('check-square').'</a> &middot; ';
$strCsv .= SUser::role()==='V' ? '' : htmlCsvLink(url('qtf_items_csv.php').'?'.$oH->selfuri, $intCount, $pn);
echo '<div id="tablebot" class="table-ui bot">';
echo $rowCommands ? '<div id="t1-edits-bot" class="left checkboxcmds">'.qtSVG('corner-down-right','class=arrow-icon').$rowCommands.'</div>' : '<div></div>';
echo '<div class="right">'.$strPaging.'</div></div>'.PHP_EOL;
echo '<p class="right table-ui-export">'.$strCsv.'</p>'.PHP_EOL;
echo '<div id="t1-nav-bot" class="nav-bot">'.$navCommands.'</div>'.PHP_EOL;

// TAGS FILTRING
if ( QT_LIST_TAG && !empty($_SESSION[QT]['tags']) && count($arrTags)>0 ) {
  sort($arrTags);
  echo '<div class="tag-box"><p><svg class="svg-symbol svg-125"><use href="#symbol-tags" xlink:href="#symbol-tags"/></svg> '.L('Show_only_tag').'</p>';

  foreach($arrTags as $strTag)
  echo '<a class="tag" href="'.url('qtf_items.php').'?s='.$s.'&fq=adv&fw=*&fv='.urlencode($strTag).'" title="..." data-tagdesc="'.$strTag.'">'.$strTag.'</a>';
  echo qtSVG('search','','',true).'</div>';
  $oH->scripts['tagdesc'] = '<script type="text/javascript" src="bin/js/qt_tagdesc.js" id="tagdesc" data-dir="'.QT_DIR_DOC.'" data-lang="'.QT_LANG.'"></script>';
}

// Post-compute user's replied items (for topics having replies). Result is added using js.
if ( QT_LIST_ME && count($arrRe)>0 && (int)SUser::getInfo('numpost',0)>0 ) {
  $arr = [];
  $oDB->query( "SELECT topic,issuedate FROM TABPOST WHERE type='R' AND userid=".SUser::id()." AND topic IN (".implode(',', $arrRe).")" );
  while($row = $oDB->getRow())
    $arr[(int)$row['topic']] = '"'.qtDate($row['issuedate'], 'j M', 'H:i', true, true).'"';
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

// ------
// HTML END
// ------
// hide href column if empty
if ( $fq!=='s' ) $oH->scripts[] = 'qtHideEmptyColumn();';
// hide table-ui-bottom-controls if less than 5 table rows
$oH->scripts[] = 'qtHideAfterTable("t1-nav-bot");qtHideAfterTable("tablebot");';

// Symbols
echo '<svg xmlns="http://www.w3.org/2000/svg" style="display:none">'.PHP_EOL;
echo qtSVG('symbol-caret-square-right').PHP_EOL;
if ( QT_LIST_ME ) echo qtSVG('symbol-ireplied').PHP_EOL;
if ( $_SESSION[QT]['upload']!=='0' ) echo qtSVG('symbol-paperclip').PHP_EOL;
if ( !empty($_SESSION[QT]['tags']) ) {
  echo qtSVG('symbol-tag').PHP_EOL;
  echo qtSVG('symbol-tags').PHP_EOL;
}
echo '</svg>'.PHP_EOL;

include APP.'_inc_ft.php';