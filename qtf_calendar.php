<?php // v4.0 build:20230430 allows app impersonation [qt f|i|e]

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 * @var string $s
 */
require 'bin/init.php';
$oH->selfurl = APP.'_calendar.php';
if ( !SUser::canView('V2') || !SUser::canAccess('show_calendar') ) die('Access denied');

// ---------
// FUNCTIONS
// ---------

function FirstDayDisplay($intYear,$intMonth,$intWeekstart=1)
{
  // search date of the first 'monday' (or weekstart if not 1)
  // before the beginning of the month (to display gey-out in the calendar)
  if ( $intWeekstart<1 || $intWeekstart>7 ) die ('FirstDayDisplay: Arg #3 must be an int (1-7)');

  $arr = array(1=>'monday','tuesday','wednesday','thursday','friday','saturday','sunday'); // system weekdays reference
  $strWeekstart = $arr[$intWeekstart];
  $d = mktime(0,0,0,$intMonth,1,$intYear); // first day of the month
  if ( strtolower(date('l',$d))==$strWeekstart ) return $d;

  for($i=1;$i<8;$i++)
  {
    $d = strtotime('-1 day',$d);
    if ( strtolower(date('l',$d))==$strWeekstart ) return $d;
  }
  return $d;
}

function ArraySwap($arr,$n=1)
{
  // Move the first value to the end of the array. Action is repeated $n times. Keys are not moved.
  if ( $n>0)
  {
    $arrK = array_keys($arr);
    while($n>0) { array_push($arr,array_shift($arr)); $n--; }
    $arrV = array_values($arr);
    $arr = array();
    for($i=0;$i<count($arrK);$i++) $arr[$arrK[$i]] = $arrV[$i];
  }
  return $arr;
}

// ---------
// INITIALISE
// ---------

$s = -1;
$v = 'birthday';
qtArgs('int:s');

$intYear   = date('Y');  if ( isset($_GET['y']) ) $intYear = intval($_GET['y']);
$intYearN  = $intYear; // year next month is this year except in december
$intMonth  = date('n'); if ( isset($_GET['m']) ) $intMonth = intval($_GET['m']);
$intMonthN = $intMonth+1; if ( $intMonthN>12 ) { $intMonthN=1; $intYearN++; }
$strMonth  = '0'.$intMonth; $strMonth = substr($strMonth,-2,2);
$strMonthN = '0'.$intMonthN; $strMonthN = substr($strMonthN,-2,2);
$arrWeekCss = array(1=>'monday','tuesday','wednesday','thursday','friday','saturday','sunday'); // system weekdays reference

$dToday = mktime(0,0,0,date('n'),date('j'),date('Y'));

if ( $intYear>2100 ) die('Invalid year');
if ( $intYear<1900 ) die('Invalid year');
if ( $intMonth>12 ) die('Invalid month');
if ( $intMonth<1 ) die('Invalid month');

$oH->selfname = L('Birthdays_calendar');

// Shift language names and cssWeek to match with weekstart setting, if not 1 (monday)

if ( QT_WEEKSTART>1 )
{
  $L['dateDDD'] = ArraySwap($L['dateDDD'],intval(QT_WEEKSTART)-1);
  $L['dateDD'] = ArraySwap($L['dateDD'],intval(QT_WEEKSTART)-1);
  $L['dateD'] = ArraySwap($L['dateD'],intval(QT_WEEKSTART)-1);
  $arrWeekCss = ArraySwap($arrWeekCss,intval(QT_WEEKSTART)-1);
}

// --------
// LIST OF ITEMS
// --------

$arrEvents = array();
$arrEventsN = array();
$intEvents = 0;
$intEventsN = 0;

switch($oDB->type)
{
// Select 2 months
case 'pdo.mysql':
case 'mysql':
case 'pdo.sqlsrv':
case 'sqlsrv': $oDB->query( "SELECT id,name,role,$v FROM TABUSER WHERE SUBSTRING($v,5,2)=? OR SUBSTRING($v,5,2)=?", [$strMonth,$strMonthN] ); break;
case 'pdo.pg':
case 'pg': $oDB->query( "SELECT id,name,role,$v FROM TABUSER WHERE SUBSTRING($v FROM 5 FOR 2)=? OR SUBSTRING($v FROM 5 FOR 2)=?", [$strMonth,$strMonthN] ); break;
case 'pdo.sqlite':
case 'sqlite': $oDB->query( "SELECT id,name,role,$v FROM TABUSER WHERE SUBSTR($v,5,2)=? OR SUBSTR($v,5,2)=?", [$strMonth,$strMonthN] ); break;
case 'pdo.oci':
case 'oci':$oDB->query( "SELECT id,name,role,$v FROM TABUSER WHERE SUBSTR($v,5,2)=? OR SUBSTR($v,5,2)=?", [$strMonth,$strMonthN] ); break;
default: die('Unknown db type '.$oDB->type);
}
$i=0;
while($row=$oDB->getRow())
{
  $i++;
  if ( strlen($row[$v])==8 )
  {
    $strM = substr($row[$v],4,2);
    $strD = substr($row[$v],6,2);
    if ( $strM==$strMonth )  { $arrEvents[(int)$strD][]=$row; $intEvents++; }
    if ( $strM==$strMonthN ) { $arrEventsN[(int)$strD]=1; $intEventsN++; }
  }
  if ( $i>8 ) break;
}

// --------
// HTML BEGIN
// --------

$oH->links[] = '<link rel="stylesheet" type="text/css" href="'.QT_SKIN.APP.'_calendar.css"/>';

include APP.'_inc_hd.php';

// PREPARE MAIN CALENDAR
$dCurrentDate = mktime(0,0,0,$intMonth,1,$intYear);
$dMainDate = $dCurrentDate;
$dFirstDay = mktime(0,0,0,$intMonth,1,$intYear);
if ( date('l',$dFirstDay)!=='Monday' )
{
  $dFirstDay = strtotime('-1 week',$dFirstDay);
  $dFirstMonday = strtotime('next monday',$dFirstDay);
  // correction for php 4.2
  // find last monday
  for ($i=date('j',$dFirstDay);$i<32;$i++)
  {
    $dI = mktime(0,0,0,date('n',$dFirstDay),$i,date('Y',$dFirstDay));
    if ( !$dI )
    {
    if ( date('N',$dI)==1 ) $dFirstMonday = $dI;
    }
  }
  $dFirstDay = $dFirstMonday;
}

// DISPLAY MAIN CALENDAR MENU
echo '<div id="ct-title" class="flex-sp">';
echo '<h1>'.$oH->selfname.': '.$L['dateMMM'][date('n',$dCurrentDate)].' '.date('Y',$dCurrentDate).'</h1>';
echo '<form method="get" action="'.url($oH->selfurl).'" id="cal_month">';
echo '<input type="hidden" name="y" id="y" value="'.$intYear.'"/> '.PHP_EOL;
echo L('Month').' <select name="m" onchange="document.getElementById(`cal_month`).submit();">';
for ($i=1;$i<13;$i++) echo '<option'.($i==date('n') ? ' class="bold"' : '').' value="'.$i.'"'.($i==$intMonth ? ' selected' : '').'>'.$L['dateMMM'][$i].'</option>'.PHP_EOL;
echo '</select>&nbsp;';
if ( date('n',$dCurrentDate)>1 )
  echo '<a class="button" href="'.$oH->selfurl.'?m='.(date('n',$dCurrentDate)-1).'">'.getSVG('chevron-left').'</a>&thinsp;';
  else
  echo '<a class="button disabled">'.getSVG('chevron-left').'</a>&thinsp;';
if ( date('n',$dCurrentDate)<12 )
  echo '<a class="button" href="'.$oH->selfurl.'?m='.(date('n',$dCurrentDate)+1).'">'.getSVG('chevron-right').'</a>';
  else
  echo '<a class="button disabled">'.getSVG('chevron-right').'</a>';
echo '</form>'.PHP_EOL;
echo '</div>'.PHP_EOL;

// DISPLAY MAIN CALENDAR

echo '<table id="calendar">'.PHP_EOL;
echo '<tr>';
echo '<th class="week date_first">&nbsp;</th>';
for ($i=1;$i<8;$i++)
{
  echo '<th class="date'.($i==7 ? ' date_last' : '').'">'.$L['dateDDD'][$i].'</th>'.PHP_EOL;
}
echo '</tr>'.PHP_EOL;

  $iShift=0;
  $d = 0;
  $nWeek = (int)date('W',$dFirstDay);
  for ($intWeek=0;$intWeek<6;$intWeek++)
  {
    echo '<tr>';
    echo '<th class="week">'.$nWeek.'</th>'; $nWeek++; if ( $nWeek>52 ) $nWeek=1;
    for ($intDay=1;$intDay<8;$intDay++)
    {
      $d = strtotime("+$iShift days",$dFirstDay);
      $iShift++;
      $intShiftYear = (int)date('Y',$d);
      $intShiftDay = (int)date('j',$d);
      $idxEvent = $intShiftDay;
      // date number
      if ( date('n',$dCurrentDate)==date('n',$d) )
      {
        echo '<td class="date '.$arrWeekCss[$intDay].'"'.(date('z',$dToday)==date('z',$d) ? ' id="today">' : '>');
        echo '<p class="datenumber">'.$intShiftDay.'</p><p class="dateicon">&nbsp;';
        // date info topic
        if ( isset($arrEvents[$idxEvent]) )
        {
          $intDayEvents = 0;
          foreach($arrEvents[$idxEvent] as $arrValues)
          {
            $intDayEvents++;
            $intAge = $intShiftYear - intval(substr($arrValues[$v],0,4));
            if ( $intDayEvents<4 )
            {
              echo '<a class="ajaxmouseover" id="u'.$arrValues['id'].'" href="'.url(APP.'_user.php').'?id='.$arrValues['id'].'">'.$arrValues['name'].'</a> ('.$intAge.')<br>';
            }
            else
            {
              echo '<a class="ajaxmouseover" id="u'.$arrValues['id'].'" href="'.url(APP.'_user.php').'?id='.$arrValues['id'].'" title="'.$arrValues['name'].' ('.$intAge.')">'.getSVG('user').'</a> ';
            }
            if ( $intDayEvents>7 ) break;
          }
        }
      }
      else
      {
        echo '<td class="outdate">';
        echo '<p class="datenumber">'.$intShiftDay.'</p><p class="dateicon">&nbsp;';
      }
      echo '</p></td>';
    }
    echo '</tr>'.PHP_EOL;
    // limit
    if ( $intWeek>3 && date('j',$d)<7) break;
  }

echo '</table>'.PHP_EOL;

// DISPLAY SUBDATA

echo '<div class="table-ui bot">'.PHP_EOL;
echo '<div class="cal_info left">'.PHP_EOL;

  // PREPARE NEXT MONTH

  $dCurrentDate = mktime(0,0,0,$intMonthN,1,$intYearN);
  $dFirstDay = mktime(0,0,0,$intMonthN,1,$intYearN);
  if ( date("l",$dFirstDay)!=='Monday' )
  {
    $dFirstDay = strtotime('-1 week',$dFirstDay);
    $dFirstMonday = strtotime('next monday',$dFirstDay);
    // correction for php 4.2
    // find last monday
    for ($i=date('j',$dFirstDay);$i<32;$i++)
    {
      $dI = mktime(0,0,0,date('n',$dFirstDay),$i,date('Y',$dFirstDay));
      if ( !$dI )
      {
      if ( date('N',$dI)==1 ) $dFirstMonday = $dI;
      }
    }
    $dFirstDay = $dFirstMonday;
  }

  // DISPLAY NEXT MONTH

  echo '<h2>'.$L['dateMMM'][date('n',$dCurrentDate)].'</h2>';
  echo '<table id="calendarnext">'.PHP_EOL;
  echo '<tr>'.PHP_EOL;
  for ($intDay=1;$intDay<8;$intDay++)
  {
  echo '<th class="date_next">'.$L['dateD'][$intDay].'</th>'.PHP_EOL;
  }
  echo '</tr>'.PHP_EOL;

    $iShift=0;
    $d=0;
    for ($intWeek=0;$intWeek<6;$intWeek++)
    {
      echo '<tr>'.PHP_EOL;
      for ($intDay=1;$intDay<8;$intDay++)
      {
        $d = strtotime("+$iShift days",$dFirstDay);
        $iShift++;
        $intShiftDay = (int)date('j',$d);
        $idxEvent = $intShiftDay;
        // date number
        if ( date('n',$dCurrentDate)==date('n',$d) )
        {
          echo '<td class="date_next '.$arrWeekCss[$intDay].'"'.(date('z',$dToday)==date('z',$d) ? ' id="todaynext"' : '').'>';
          echo isset($arrEventsN[$idxEvent]) ? '<a class="date_next" href="'.url($oH->selfurl).'?m='.$intMonthN.'">'.$intShiftDay.'</a>' : $intShiftDay;
        }
        else
        {
          echo '<td class="outdate">'.$intShiftDay;
        }
        echo '</td>'.PHP_EOL;
      }
      echo '</tr>'.PHP_EOL;
      // limit (based on next calendar day)
      if ( $intWeek>3 && date('j',$d)<7) break;
    }

  echo '</table>'.PHP_EOL;

echo '</div>'.PHP_EOL;
echo '<div class="cal_info center secondary article">'.PHP_EOL;

  // DISPLAY STATS
  echo '<h2>'.L('Total').'</h2>'.PHP_EOL;
  echo '<p>'.L('dateMMM.'.date('n',$dMainDate)).'<br>';
  echo L('User',$intEvents).'</p>';
  echo '<p>'.L('dateMMM.'.date('n',$dCurrentDate)).'<br>';
  echo L('User',$intEventsN).'</p>';

echo '</div>'.PHP_EOL;
echo '<div class="cal_info center">'.PHP_EOL;

  // DISPLAY Preview
  echo '<h2>'.L('Information').'</h2>';
  echo '<div id="previewcontainer"></div>'.PHP_EOL;

echo '</div>'.PHP_EOL;
echo '</div>'.PHP_EOL;

// HTML END
$oH->scripts[] = 'const dir ="'.QT_DIR_PIC.'";
const titleRole = function(d) {
  if ( d.dataset.role=="A" ) d.title = "'.L('Role_A').'";
  if ( d.dataset.role=="M" ) d.title = "'.L('Role_M').'";
  if ( d.dataset.role=="U" ) d.title = "'.L('Role_U').'";
  if ( d.dataset.role=="V" ) d.title = "Visitor";
}
const elements = document.querySelectorAll(".ajaxmouseover");
elements.forEach( el => el.addEventListener("mouseover", (e) => {
  fetch( `bin/srv_user.php?q=u&id=${el.id.substring(1)}&dir=${dir}` )
  .then( response => {
    return response.text()
    .then( text => { document.getElementById("previewcontainer").innerHTML = text; } )
    } )
  .catch( err => console.log(err) );
  })
);';

include APP.'_inc_ft.php';