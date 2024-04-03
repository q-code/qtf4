<?php // v4.0 build:20240210

session_start();
require 'bin/init.php';
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 * @var string $bt
 * @var string $ft
 */
$oH->selfurl = 'qtf_items_csv.php';
if ( !SUser::canView('V2') ) $oH->voidPage('user-lock.svg',11,true); //...

// ------
// INITIALISE
// ------
// Check size arguments
$size     = isset($_GET['size']) ? strip_tags($_GET['size']) : 'all'; // all | pages p{n} | thousands m{1|2|5|10}
$intCount = (int)$_GET['n'];
$sqlStart = 0;
$intLen   = (int)$_SESSION[QT]['items_per_page'];
if ( empty($size) || $intCount <= $intLen ) $size='all';
if ( strlen($size)>6 ) die('Invalid argument');
if ( substr($size,0,1)!=='p' && substr($size,0,1)!=='m' && $size!=='all') die('Invalid argument');
if ( substr($size,0,1)==='p' ) {
  $i = (int)substr($size,1);
  if ( empty($i) || $i<0 ) die('Invalid argument');
  if ( ($i-1) > $intCount/$intLen ) die('Invalid argument');
}
if ( substr($size,0,1)==='m' ) {
  if ( $size!='m1' && $size!='m2' && $size!='m5' && $size!='m10' ) die('Invalid argument');
}
if ( $intCount>1000 && $size=='all' ) die('Invalid argument');
if ( $intCount<=1000 && substr($size,0,1)==='m' ) die('Invalid argument');
if ( $intCount>1000 && substr($size,0,1)==='p' ) die('Invalid argument');

// apply size arguments
if ( $size=='all') { $sqlStart=0; $intLen=$intCount; }
if ( $size=='m1' ) { $sqlStart=0; $intLen=999; }
if ( $size=='m2' ) { $sqlStart=1000; $intLen=1000; }
if ( $size=='m5' ) { $sqlStart=0; $intLen=4999; }
if ( $size=='m10') { $sqlStart=5000; $intLen=5000; }
if ( substr($size,0,1)==='p' ) { $i = (int)substr($size,1); $sqlStart = ($i-1)*$intLen; }

// init args
$s = -1; // [int]
$q = ''; // Search type (not required, use 's' if missing)
$fs = ''; // Status [string] {'*'|status-key}, caution: can be '0'
$fv = ''; // Searched [string] text (converted to array of strings)
$fw = ''; // timeframe [string] or userid
$pn = 1; $po = 'lastpostdate'; $pd = 'desc'; // page number,order,direction
qtArgs('q int:s fs fv fw int:pn po pd');

// check args
if ( empty($q) ) $q = '';
if ( $q==='' && $s<0 ) die(__FILE__.' Missing argument $s');
$fv = qtCleanArray($fv); // [array]

// initialise section
if ( $q==='' && $s<0 ) die('Missing argument $s');
if ( $q==='' || $s>=0 ) {
  $oS = new CSection($_Sections[$s]); // new CSection($s)
  // exit if user role not granted
  if ( $oS->type==='1' && (SUser::role()==='V' || SUser::role()==='U')) {
    $oH->selfname = L('Section');
    $oH->exitname = SLang::translate();
    $oH->voidPage('user-lock.svg',12,true); //...
  }
  if ( $oS->type==='2' && SUser::role()==='V') {
    $oH->selfname = L('Section');
    $oH->exitname = SLang::translate();
    $oH->voidPage('user-lock.svg',11,true); //...
  }
  $oH->selfname = L('Section').': '.$oS->title;
} else {
  $oS = new CSection(); // void-section in case of search query
  $oH->selfname = L('Search_results');
}

// initialise others
$oH->selfuri = qtURI('pn|po|pd');
$strLastcol = $oS->getMF('options','last'); if  ($strLastcol=='N' || strtolower($strLastcol)==='none' ) $strLastcol='0';
if ( isset($_GET['cid']) ) $intChecked = (int)strip_tags($_GET['cid']); // allow checking an id in edit mode
if ( isset($_POST['cid']) ) $intChecked = (int)strip_tags($_POST['cid']);
if ( !isset($_SESSION['EditByRows']) || !SUser::isStaff() ) $_SESSION['EditByRows'] = 0;
if ( !isset($_SESSION[QT]['lastcolumn']) || $_SESSION[QT]['lastcolumn']==='none' ) $_SESSION[QT]['lastcolumn'] = 'default';
$intChecked = -1; // allows checking an id when EditByRows (-1 means no check)
$csv = '';

// change lastcolumn if a preference exists
if ( $_SESSION[QT]['lastcolumn']!=='default' ) $strLastcol = $_SESSION[QT]['lastcolumn']; // advanced query can override preference

// -----
// QUERY parts definition
// -----
$sqlStart = ($pn-1)*$_SESSION[QT]['items_per_page'];
$sqlFields = ($_SESSION[QT]['news_on_top'] ? "CASE WHEN t.type='A' AND t.status='0' THEN 'A' ELSE 'Z' END as typea," : '');
$sqlFields .= 't.*,p.title,p.icon,p.id as postid,p.type as posttype,p.textmsg,p.issuedate,p.username,p.attach';
$sqlFrom = ' FROM TABTOPIC t INNER JOIN TABPOST p ON t.firstpostid=p.id'; // warning: include only firstpostid (not the replies)
$sqlWhere = ' WHERE t.forum'.($q==='' ? '='.$s : '>=0');
  // In private section, show topics created by user himself
  if ( $q==='' && $oS->type==='2' && !SUser::isStaff()) $sqlWhere .= " AND (t.firstpostuser=".SUser::id()." OR (t.type='A' AND t.status='0'))";
$sqlValues = []; // list of values for the prepared-statements
$sqlCount = "SELECT count(*) as countid FROM TABTOPIC t".$sqlWhere;
$sqlCountAlt='';
if ( $q!=='' ) {
  include 'bin/lib_qtf_query.php';
  $oH->error = sqlQueryParts($sqlFrom,$sqlWhere,$sqlValues,$sqlCount,$sqlCountAlt,$oH->selfuri); //selfuri is not urldecoded
  if ( !empty($oH->error) ) die($oH->error);
  if ( $q==='adv' && !empty($fv) ) $strLastcol = 'tags'; // forces display column tags
}

$forceShowClosed = $_SESSION[QT]['show_closed']==='0' && $fs==='1';
$sqlHideClosed = $_SESSION[QT]['show_closed']==='0' && !$forceShowClosed ? " AND t.status<>'1'" : ''; // User preference, hide closed items (not for advanced query having status specified)

// Count items & visible for current user ONLY
if ( ($q==='' && $oS->type!=='2') || ( $q==='' && SUser::isStaff()) ) {
  // Using stats ($_SectionsStats)
  $stats = isset($_SectionsStats) ? $_SectionsStats : SMem::get('_SectionsStats');
  if ( !$forceShowClosed && !isset($stats[$s]['itemsZ']) ) $stats[$s]['itemsZ'] = $oDB->count(CSection::sqlCountItems($s,'items','1'));
  $oH->items = empty($stats[$s]['items']) ? 0 : (int)$stats[$s]['items'];
  if ( !empty($sqlHideClosed) ) $oH->itemsHidden = (int)$stats[$s]['itemsZ'];
} else {
  $oH->items = $oDB->count($sqlCount, $sqlValues);
  if ( !empty($sqlHideClosed) ) $oH->itemsHidden = $oH->items - $oDB->count($sqlCount.$sqlHideClosed, $sqlValues);
}
$intCount = $oH->items - $oH->itemsHidden;

// ------
// OUPUT
// ------
$t = new TabTable();
$t->arrTh['type'] = new TabHead(L('Type'));
$t->arrTh['numid'] = new TabHead(L('Ref'));
$t->arrTh['title'] = new TabHead(L('Item'));
if ( !empty($q) && $s<0 )
$t->arrTh['sectiontitle'] = new TabHead(L('Section'));
$t->arrTh['firstpostname'] = new TabHead(L('Author'));
$t->arrTh['firstpostdate'] = new TabHead(L('First_message'));
$t->arrTh['lastpostdate'] = new TabHead(L('Last_message'));
$t->arrTh['replies'] = new TabHead(L('Reply+'));
if ( !empty($strLastcol) )
$t->arrTh[$strLastcol] = new TabHead(L(ucfirst($strLastcol)));

$csv = toCsv($t->getTHnames()).PHP_EOL;

// ========
$oDB->query(sqlLimit(
  $sqlFields.$sqlFrom.$sqlWhere.$sqlHideClosed,
  ($_SESSION[QT]['news_on_top'] ? 'typea ASC, ' : '').($strOrder=='title' ? 'p.title' : 't.'.$strOrder).' '.strtoupper($strDirec),
  $sqlStart,
  $_SESSION[QT]['items_per_page'],
  $intCount
  ), $sqlValues);
// ========
$intWhile=0;
while($row=$oDB->getRow())
{
  $csv .= formatCsvRow($t->getTHnames(),$row,$oS).PHP_EOL;
  $intWhile++; if ( $intWhile>=$intCount ) break;//odbcbreak
}
// ========

if ( isset($_GET['debug']) ) { echo $csv; exit; }

// Header sould not have been sent yet. Define a download header. Otherwise file or messages are displayed as a new html page.
if ( !headers_sent() )
{
  header('Content-Type: text/csv; charset='.QT_HTML_CHAR);
  header('Content-Disposition: attachment; filename="'.APP.'_'.date('YmdHi').'.csv"');
}
echo $csv;