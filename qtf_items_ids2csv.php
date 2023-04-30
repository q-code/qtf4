<?php // v4.0 build:20230205

session_start();
require 'bin/init.php';
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 * @var string $bt
 * @var string $ft
 */
$oH->selfurl = 'qtf_items_ids2csv.php';
if ( !SUser::canView('V2') ) exitPage(11,'user-lock.svg'); //...

// ---------
// INITIALISE
// ---------

// check search arguments
$q = ''; // type of search (if missing will use $q='s')
$s = ''; // section $s can be '*' or [int] (after argument checking only [int] is allowed)
$st = ''; // status $st can be '*' or [string]
$v = ''; // searched text [string] >> array of strings
$v2 = ''; // timeframe [string]
$ids = '';
qtHttp('q s st v v2 ids',true,false); // $_GET only
if ( empty($ids) ) die('Missing ids');
if ( empty($q)) $q = 's';
if ( $s==='*' || $s==='' || !is_numeric($s) ) $s = '-1';
if ( empty($st)) $st = '*';
$v        = asCleanArray($v); // array of (unique) values trimmed (not empty)
$intCount = count(explode(',',$ids));
$intLimit = 0;
$intLen   = (int)$_SESSION[QT]['items_per_page'];

// initialise section
$s = (int)$s;
if ( $q==='s' && $s<0 ) die('Missing argument $s');
if ( $q==='s' || $s>=0 ) {
  $oS = new CSection($_Sections[$s]); // new CSection($s)
  // exit if user role not granted
  if ( $oS->type==='1' && (SUser::role()==='V' || SUser::role()==='U')) {
    $oH->selfname = L('Section');
    $oH->exitname = SLang::translate();
    exitPage(12, 'user-lock.svg'); //...
  }
  if ( $oS->type==='2' && SUser::role()==='V') {
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
$strLastcol = $oS->getMF('options','last'); if  ($strLastcol=='N' || strtolower($strLastcol)==='none' ) $strLastcol='0';
if ( !isset($_SESSION[QT]['lastcolumn']) || $_SESSION[QT]['lastcolumn']=='none' ) $_SESSION[QT]['lastcolumn'] = 'default';
$csv = '';
// change lastcolumn if a preference exists
if ( $_SESSION[QT]['lastcolumn']!=='default' ) $strLastcol = $_SESSION[QT]['lastcolumn']; // advanced query can override preference

// -----
// QUERY parts definition
// -----

$sqlFields = 'SELECT t.*,p.title,p.icon,p.id as postid,p.type as posttype,p.textmsg,p.issuedate,p.username,p.attach';
$sqlFrom = ' FROM TABTOPIC t INNER JOIN TABPOST p ON t.firstpostid=p.id'; // warning: include only firstpostid (not the replies)
$sqlWhere = ' WHERE t.id IN ('.$ids.')';

// --------
// OUPUT
// --------

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
$oDB->query( $sqlFields.$sqlFrom.$sqlWhere );
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