<?php  // v4.0 build:20230430 allows app impersonation [qt f|i]

session_start();
/**
* @var CHtml $oH
 * @var string $sqlSection
 * @var string $sqlTags
 * @var CHtml $oH
 * @var CDatabase $oDB
 * @var int $intStartmonth
 * @var int $intEndmonth
 * @var array $arrD
 * @var array $arrS
 */
require 'bin/init.php';

$oH->selfurl = APP.'_stats.php';
if ( !SUser::canAccess('show_stats') ) exitPage(11,'user-lock.svg'); //...

include translate('lg_stat.php');
include 'bin/lib_qt_stat.php';

function renderTables($arrYears,$bt,$arrSeries,$arrD,$arrS,$strTendaysago,$arrC)
{
  global $L;
  $csv = '';
  foreach($arrYears as $y)
  {
    //header
    $csv .= qtQuote($y).';';
    switch($bt)
    {
    case 'q': for ($i=1;$i<=MAXBT;++$i) { $csv .= qtQuote('Q'.$i).';'; } break;
    case 'm': for ($i=1;$i<=MAXBT;++$i) { $csv .= qtQuote($L['dateMM'][$i]).';'; } break;
    case 'd': for ($i=1;$i<=MAXBT;++$i) { $csv .= qtQuote(qtDatestr(addDate($strTendaysago,$i,'day'),'d M','')).';'; } break;
    }
    $csv .= qtQuote(($bt==='d' ? '10 '.strtolower(L('Days')) : L('Total'))).'<br>';
    // data series
    foreach($arrSeries as $k=>$title)
    {
      $csv .= qtQuote($title).';';
      for ($intBt=1;$intBt<=MAXBT;$intBt++)
      {
      $csv .= (isset($arrD[$y][$k][$intBt]) ? $arrD[$y][$k][$intBt] : 0).';';
      }
      $csv .= $arrS[$y][$k].'<br>';
    }
  }
  return $csv;
}

// --------
// INITIALISE
// --------

include translate('lg_stat.php');

$csv = '';
$pan = 'g'; // panel: g=global, gt=globaltrend, d=detail, dt=detailtrend
$bt  = 'm';
$s   = '*';
$y   = (int)date('Y'); if ( (int)date('n')<2 ) --$y;
$y0  = $y-1;
$tag = '';
qtArgs('pan bt s int:y int:y0 tag');

$sqlSection='';
$sqlTags = '';

// --------
// Check and Initialise
// --------
if ( $s!=='*' ) $sqlSection = 'forum='.$s.' AND '; // int to avoid injection
if ( $y0>=$y ) $y0=$y-1;
if ( !empty($tag) )
{
  $tag = urldecode($tag); if ( substr($tag,-1,1)===';' ) $tag = substr($tag,0,-1);
  $arrTags = explode(';',$tag);
  $str = '';
  foreach($arrTags as $strTag)
  {
  if ( !empty($str) ) $str .= ' OR ';
  $str .= 'UPPER(tags) LIKE "%'.strtoupper($strTag).'%"';
  }
  if ( !empty($str) ) $sqlTags = ' ('.$str.') AND ';
}
$arrYears = $pan=='gt' || $pan=='dt' ? array($y0,$y) : array($y);
switch($bt)
{
case 'q': define('MAXBT',4); break; // quarters
case 'd': define('MAXBT',10); break; // 10 days
case 'm': define('MAXBT',12); break; // months
default: die('Invalid blocktime');
}

$row = SMem::get('statG');
if ( $row===false ) {
  $oDB->query( "SELECT count(id) as countid, min(firstpostdate) as startdate, max(firstpostdate) as lastdate FROM ".TABTOPIC );
  $row = $oDB->getRow();
  SMem::set('statG',$row,0);
}
if ( empty($row['startdate']) ) $row['startdate']=strval($y0).'0101';
if ( empty($row['lastdate']) ) $row['lastdate']=strval($y).'1231';
$strLastdaysago = substr($row['lastdate'],0,8);
$strTendaysago = addDate($strLastdaysago,-10,'day');
$intStartyear = intval(substr($row['startdate'],0,4));
$intStartmonth = intval(substr($row['startdate'],4,2));
$intEndyear = intval(date('Y'));
$intEndmonth = intval(date('n'));

$colorFade = array('96,96,255','241,184,255','0,231,183','200,200,200', '255,100,100');
$colorBase = array('0,0,102',  '153,0,153',  '0,153,153','150,150,150', '200,0,0');

// Statistic computation
//----------------------
$arrSeries = $pan=='d' || $pan=='dt' ? array('T','N','C','Z','ATT') : array('T','R','U');
include APP.'_stats_inc.php';
//----------------------

switch($pan)
{
  case 'g':
    $arrC[$y] = array_combine($arrSeries,array_slice($colorBase,0,count($arrSeries)));
    $titles = array('T'=>L('Item+'),'R'=>L('Reply+'),'U'=>L('Users'));
    $csv = renderTables($arrYears,$bt,$titles,$arrD,$arrS,$strTendaysago,$arrC);
    break;

  case 'gt':
    $arrC[$y0] = array_combine($arrSeries,array_slice($colorFade,0,count($arrSeries)));
    $arrC[$y] = array_combine($arrSeries,array_slice($colorBase,0,count($arrSeries)));
    $titles = array('T'=>L('Item+'),'R'=>L('Reply+'),'U'=>L('Users'));
    $csv = renderTables($arrYears,$bt,$titles,$arrD,$arrS,$strTendaysago,$arrC);
    break;

  case 'd':
    $arrSeries=array('N','C','Z','ATT');
    $arrC[$y] = array_combine($arrSeries,array_slice($colorBase,0,count($arrSeries)));
    $arrC[$y]['ATT']=$colorBase[4];
    $titles = array('N'=>L('Type').' '.L('news'),'C'=>L('Status').' '.L('closed'),'Z'=>L('Pending'),'ATT'=>L('Attachments'));
    $csv = renderTables($arrYears,$bt,$titles,$arrD,$arrS,$strTendaysago,$arrC);
    break;

  case 'dt':
    $arrSeries=array('N','C','Z','ATT');
    $arrC[$y0] = array_combine($arrSeries,array_slice($colorFade,0,count($arrSeries)));
    $arrC[$y0]['ATT']=$colorFade[4];
    $arrC[$y] = array_combine($arrSeries,array_slice($colorBase,0,count($arrSeries)));
    $arrC[$y]['ATT']=$colorBase[4];
    $titles = array('N'=>L('Type').' '.L('news'),'C'=>L('Status').' '.L('closed'),'Z'=>L('Pending'),'ATT'=>L('Attachments'));
    $csv = renderTables($arrYears,$bt,$titles,$arrD,$arrS,$strTendaysago,$arrC);
    break;
}

// ------
// Export
// ------

if ( !headers_sent() )
{
  $csv = str_replace('<br>',"\r\n",$csv);
  header('Content-Type: text/csv; charset='.QT_HTML_CHAR);
  header('Content-Disposition: attachment; filename="global_stat_'.$y.'.csv"');
}

echo $csv;