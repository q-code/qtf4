<?php  // v4.0 build:20230618

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

$oH->selfurl = 'qtf_stats.php';
if ( !SUser::canAccess('show_stats') ) exitPage(11,'user-lock.svg'); //...

// FUNCTION

function renderTables($arrYears,$bt,$arrSeries,$arrD,$arrS,$strTendaysago,$arrC)
{
  global $L;
  foreach($arrYears as $y)
  {
    echo '<table class="t-stat">'.PHP_EOL;
    echo '<tr>'.PHP_EOL;
    echo '<th>'.$y.'</th>'.PHP_EOL;
    echo '<th class="legendcolor"></th>'.PHP_EOL;
    switch($bt)
    {
    case 'q': for ($i=1;$i<=MAXBT;++$i) { echo '<th>Q'.$i.'</td>'; } break;
    case 'm': for ($i=1;$i<=MAXBT;++$i) { echo '<th>'.$L['dateMM'][$i].'</td>'; } break;
    case 'd': for ($i=1;$i<=MAXBT;++$i) { echo '<th>'.str_replace(' ','<br>',qtDate(addDate($strTendaysago,$i,'day'),'d M','')).'</td>'; } break;
    }
    echo '<th><b>'.($bt==='d' ? '10 '.strtolower(L('Days')) : L('Total')).'</b></td>
    </tr>';
    echo '<tr>'.PHP_EOL;
    foreach($arrSeries as $k=>$title)
    {
      echo '<th>',$title,'</th>'.PHP_EOL;
      echo '<td class="legendcolor"><div style="display:inline-block;width:12px;height:12px;border-radius:50%;background-color:rgb('.$arrC[$y][$k].')"></div></td>'.PHP_EOL;
      for ($intBt=1;$intBt<=MAXBT;$intBt++)
      {
      echo '<td>'.(isset($arrD[$y][$k][$intBt]) ? qtK($arrD[$y][$k][$intBt]) : '&middot;').'</td>'.PHP_EOL;
      }
      echo '<td class="bold">'.qtK($arrS[$y][$k]).'</td>'.PHP_EOL;
      echo '</tr>';
      echo '<tr>'.PHP_EOL;
    }
    echo '</table>'.PHP_EOL;
  }
}

// --------
// INITIALISE
// --------

include 'bin/lib_qt_stat.php';
include translate('lg_stat.php');

$oH->selfname = L('Statistics');

$pan = 'g'; // panel: g=global, gt=globaltrend, d=detail, dt=detailtrend
$bt  = 'm';
$s   = -1;
$y   = (int)date('Y'); if ( (int)date('n')<2 ) --$y;
$y0  = $y-1;
$tag = '';
qtArgs('pan bt int:s int:y int:y0 tag');

$sqlSection='';
$sqlTags = '';

// --------
// Check and Initialise
// --------
if ( $s>=0 ) $sqlSection = 'forum='.$s.' AND '; // int to avoid injection
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

$colorFade = ['96,96,255', '241,184,255', '0,231,183', '200,200,200', '255,100,100'];
$colorBase = ['0,0,102', '153,0,153', '0,153,153', '150,150,150', '200,0,0'];

// --------
// HTML BEGIN
// --------

$oH->links[] = '<link rel="stylesheet" type="text/css" href="bin/js/chart.css"/>';
$oH->scripts[] = '<script type="text/javascript" src="bin/js/chart.js"></script>';
$oH->scripts[] = '<script type="text/javascript" src="bin/js/qt_chart.js"></script>';

include 'qtf_inc_hd.php';

// OPTIONS
$arrY = []; // all possible years
for ($i=$intStartyear;$i<=$intEndyear;$i++) $arrY[$i]=$i;
echo '<div class="flex-sp top">
<h1>'.L('Options').'</h1>
<div class="search-box options">
<form method="get" action="'.url($oH->selfurl).'">
<table>
<tr>
<td>'.L('Year').'</td>
<td>'.(count($_Sections)>0 ? L('Section') : '&nbsp;').'</td>
<td>'.( $_SESSION[QT]['tags']=='0' ? '&nbsp;' : L('Tag')).'</td>
<td>&nbsp;</td>
</tr>
<tr>
<td><input type="hidden" name="pan" value="'.$pan.'"/><select name="y" id="y" onchange="checkY0(this.value);">'.qtTags($arrY,(int)$y).'</select></td>
<td>'.(count($_Sections)>0 ? '<select name="s" id="s">'.sectionsAsOption($s,[],[],L('In_all_sections')).'</select>' : '&nbsp;').'</td>
<td>'.( $_SESSION[QT]['tags']=='0' ? '&nbsp;' : '<div id="ac-wrapper-tag-edit" class="ac-wrapper"><input type="text" id="tag-edit" name="tag" size="18" value="'.$tag.'"/></div>').'</td>
<td><button type="submit" name="ok" value="ok">'.L('Ok').'</button></td>
</tr>
</table>
</form>
</div>
</div>
';

echo '<h1>'.L('Statistics').'</h1>'.PHP_EOL;

// DISPLAY TABS
$href = $oH->selfurl.'?'.qtURI('pan');
$arrM = [];
$arrM['pan-g']  = L('Global')        .'|href='.$href.'&pan=g |id=pan-g |class=pan-tab|title='.L('H_Global').' '.$y;
$arrM['pan-gt'] = L('Global_trends') .'|href='.$href.'&pan=gt|id=pan-gt|class=pan-tab|title='.L('H_Global_trends').' '.$y0.'-'.$y;
$arrM['pan-d']  = L('Details')       .'|href='.$href.'&pan=d |id=pan-d |class=pan-tab|title='.L('H_Details').' '.$y;
$arrM['pan-dt'] = L('Details_trends').'|href='.$href.'&pan=dt|id=pan-dt|class=pan-tab|title='.L('H_Details_trends').' '.$y0.'-'.$y;
$m = new CMenu($arrM, '');
echo '<div class=pan-tabs>'.$m->build('pan-'.$pan).'</div>';
echo '<div class="pan">
<p class="pan-title">'.$m->get('pan-'.$pan,'title');
if ( $s>=0 ) echo '<br>'.L('section').' '.qtQuote(isset($_Sections[$s]['title']) ? $_Sections[$s]['title'] : $s, "&'");
if ( !empty($tag) & $tag!=='*' ) echo '<br> '.L('tag').' '.qtQuote($tag, "&'");
echo '</p>
';

// Statistic computation
//----------------------
$arrSeries = $pan==='d' || $pan==='dt' ? array('T','N','C','Z','ATT') : array('T','R','U');
include 'qtf_stats_inc.php';
//----------------------

// DISPLAY title & option
$href = $oH->selfurl.'?'.qtURI('bt'); // remove argument &bt=
$arrM = [];
$arrM['bt-q'] = L('Per_q').'|id=bt-q|href='.$href.'&bt=q';
$arrM['bt-m'] = L('Per_m').'|id=bt-m|href='.$href.'&bt=m';
$arrM['bt-d'] = L('Per_d').'|id=bt-d|href='.$href.'&bt=d';
// add comparaison year selector
if ( $pan=='gt' || $pan=='dt' ) {
  $href = $oH->selfurl.'?'.qtURI('y0'); // remove argument '&y0='
  $arrM[] = '!<span>'.L('Compare_year').'&nbsp;<select id="y0" name="y0" value="'.$y0.'" onchange="window.location=`'.$href.'`+this.value;">'.qtTags([$y-4=>$y-4,$y-3=>$y-3,$y-2=>$y-2,$y-1=>$y-1], $y0).'</select></span>';
}
$m = new CMenu($arrM, ' &middot; ');
echo '<div id="nav-blocktime">'.$m->build('bt-'.$bt, 'tag=span|addclass=actif').'</div>';

switch($pan)
{

//--------
case 'g':
//--------

// Table
$arrC[$y] = array_combine($arrSeries,array_slice($colorBase,0,count($arrSeries)));
$titles = array('T'=>L('Item+'),'R'=>L('Reply+'),'U'=>L('Users').'*');
renderTables($arrYears,$bt,$titles,$arrD,$arrS,$strTendaysago,$arrC);
echo '<p>* <span class="small">'.L('Distinct_users').'</span></p>';

// Add cumulative to series
$arrSeries[] = 'CT';
$arrD[$y]['CT']=qtCumul($arrD[$y]['T']);
$titles['CT']=L('Cumulative').' '.L('item+');
$titles['U']=L('Users');

//change the null values to zero to be able to make charts and change title
foreach($arrSeries as $serie)
{
  $titles[$serie]=$titles[$serie].' '.L('per_'.$bt);
  foreach($arrYears as $year) $arrD[$year][$serie]=qtArrayzero($arrD[$year][$serie]);
}

// display graphs.
if ( !file_exists('bin/js/chart.css') || !file_exists('bin/js/chart.js') ) { echo '<p>Missing chart library...</p>'; exit; }
$arrA = array_values(getAbscissa($bt,MAXBT,$strTendaysago)); // abscisse (not indexed)
echo '<p id="nav-charts">
'.L('Charts_options').'&nbsp; <select id="chartsType" onchange="resetCharts([1,2,3,4]);storeChartsOptions();">
<option value="b">'.L('Bar').'</option>
<option value="l">'.L('Line').'</option>
</select> &nbsp; <span class="cblabel"><input type="checkbox" id="chartsPercent" onclick="chartY([1,2,3,4]);storeChartsOptions();"> <label for="chartsPercent">'.L('Percent').'</label></span>
</p>
';
// Tips: canvas needs a div block to limit chart size (otherwise it would be 100% width, because parent "charts" is displayed as grid)
echo '<div class="charts">
<div class="chart"><canvas id="chart1" width="350px" height="250px"></canvas></div>
<div class="chart"><canvas id="chart2" width="350px" height="250px"></canvas></div>
<div class="chart"><canvas id="chart3" width="350px" height="250px"></canvas></div>
<div class="chart"><canvas id="chart4" width="350px" height="250px"></canvas></div>
</div>
';
$oH->scripts[] = 'var labels = '.json_encode($arrA).';
var chartconf1 = {
  title: "'.html_entity_decode($titles['T'], ENT_NOQUOTES).'",
  label: ["'.$y.'"],
  data: ['.json_encode($arrD[$y]['T']).'],
  maxy: '.qtRoof($arrD[$y]['T']).',
  stacktotal: false,
  color: ["'.$arrC[$y]['T'].'"]
  };
var chartconf2 = {
  title: "'.html_entity_decode($titles['CT'], ENT_NOQUOTES).'",
  label: ["'.$y.'"],
  data: ['.json_encode($arrD[$y]['CT']).'],
  maxy: '.qtRoof($arrD[$y]['CT']).',
  stacktotal: false,
  color: ["'.$arrC[$y]['T'].'"]
  };
var chartconf3 = {
  title: "'.html_entity_decode($titles['R'], ENT_NOQUOTES).'",
  label: ["'.$y.'"],
  data: ['.json_encode($arrD[$y]['R']).'],
  maxy: '.qtRoof($arrD[$y]['R']).',
  stacktotal: false,
  color: ["'.$arrC[$y]['R'].'"]
  };
var chartconf4 = {
  title: "'.html_entity_decode($titles['U'], ENT_NOQUOTES).'",
  label: ["'.$y.'"],
  data: ['.json_encode($arrD[$y]['U']).'],
  maxy: '.qtRoof($arrD[$y]['U']).',
  stacktotal: false,
  color: ["'.$arrC[$y]['U'].'"]
  };';
$oH->scripts[] = 'iniChartsOptions();
makeChart([1,2,3,4]);';

break;

//--------
case 'gt':
//--------

// Table
$arrC[$y0] = array_combine($arrSeries,array_slice($colorFade,0,count($arrSeries)));
$arrC[$y] = array_combine($arrSeries,array_slice($colorBase,0,count($arrSeries)));
$titles = array('T'=>L('Item+'),'R'=>L('Reply+'),'U'=>L('Users').'*');
renderTables($arrYears,$bt,$titles,$arrD,$arrS,$strTendaysago,$arrC);
echo '* <small>'.L('Distinct_users').'</small>';

// Add cumulative to series
$arrSeries[] = 'CT';
$arrD[$y0]['CT']=qtCumul($arrD[$y0]['T']);
$arrD[$y]['CT']=qtCumul($arrD[$y]['T']);
$titles['CT']=L('Cumulative').' '.L('item+');
$titles['U']=L('Users');

//change the null values to zero to be able to make charts and change title
foreach($arrSeries as $serie)
{
  $titles[$serie]=$titles[$serie].' '.L('per_'.$bt);
  foreach($arrYears as $year) $arrD[$year][$serie]=qtArrayzero($arrD[$year][$serie]);
}

// display graphs
if ( !file_exists('bin/js/chart.css') || !file_exists('bin/js/chart.js') ) { echo '<p>Missing chart library...</p>'; exit; }
$arrA = array_values(getAbscissa($bt,MAXBT,$strTendaysago)); // abscisse (not indexed)
echo '<div id="nav-charts">
'.L('Charts_options').'&nbsp; <select id="chartsType" onchange="resetCharts([1,2,3,4]);storeChartsOptions();">
<option value="b">'.L('Bar').'</option>
<option value="l">'.L('Line').'</option>
</select> &nbsp; <span class="cblabel"><input type="checkbox" id="chartsPercent" onclick="chartY([1,2,3,4]);storeChartsOptions();"> <label for="chartsPercent">'.L('Percent').'</label></span>
</div>
';
// Tips: canvas needs a div block to limit chart size (otherwise it would be 100% width, because parent "charts" is displayed as grid)
echo '<div class="charts">
<div class="chart"><canvas id="chart1" width="350px" height="250px"></canvas></div>
<div class="chart"><canvas id="chart2" width="350px" height="250px"></canvas></div>
<div class="chart"><canvas id="chart3" width="350px" height="250px"></canvas></div>
<div class="chart"><canvas id="chart4" width="350px" height="250px"></canvas></div>
</div>
';
$oH->scripts[] = 'var labels = '.json_encode($arrA).';
var chartconf1 = {
  title: "'.html_entity_decode($titles['T'], ENT_NOQUOTES).'",
  label: ["'.$y0.'","'.$y.'"],
  data: ['.json_encode($arrD[$y0]['T']).','.json_encode($arrD[$y]['T']).'],
  stacktotal: false,
  maxy: '.qtRoof(array_merge($arrD[$y0]['T'],$arrD[$y]['T'])).',
  color: ["'.$arrC[$y0]['T'].'","'.$arrC[$y]['T'].'"]
  };
var chartconf2 = {
  title: "'.html_entity_decode($titles['CT'], ENT_NOQUOTES).'",
  label: ["'.$y0.'","'.$y.'"],
  data: ['.json_encode($arrD[$y0]['CT']).','.json_encode($arrD[$y]['CT']).'],
  maxy: '.qtRoof(array_merge($arrD[$y0]['CT'],$arrD[$y]['CT'])).',
  stacktotal: false,
  color: ["'.$arrC[$y0]['T'].'","'.$arrC[$y]['T'].'"]
  };
var chartconf3 = {
  title: "'.html_entity_decode($titles['R'], ENT_NOQUOTES).'",
  label: ["'.$y0.'","'.$y.'"],
  data: ['.json_encode($arrD[$y0]['R']).','.json_encode($arrD[$y]['R']).'],
  maxy: '.qtRoof(array_merge($arrD[$y0]['R'],$arrD[$y]['R'])).',
  stacktotal: false,
  color: ["'.$arrC[$y0]['R'].'", "'.$arrC[$y]['R'].'"]
  };
var chartconf4 = {
  title: "'.html_entity_decode($titles['U'], ENT_NOQUOTES).'",
  label: ["'.$y0.'","'.$y.'"],
  data: ['.json_encode($arrD[$y0]['U']).','.json_encode($arrD[$y]['U']).'],
  maxy: '.qtRoof(array_merge($arrD[$y0]['U'],$arrD[$y]['U'])).',
  stacktotal: false,
  color: ["'.$arrC[$y0]['U'].'", "'.$arrC[$y]['U'].'"]
  };';
$oH->scripts[] = 'iniChartsOptions();
makeChart([1,2,3,4]);';

break;

//--------
case 'd':
//--------

// Table
$arrSeries=array('N','C','Z','ATT');
$arrC[$y] = array_combine($arrSeries,array_slice($colorBase,0,count($arrSeries)));
$arrC[$y]['ATT']=$colorBase[4];
$titles = array('N'=>L('Type').' '.L('news'),'C'=>L('Status').' '.L('closed'),'Z'=>L('Pending').'*','ATT'=>L('Attachments').'*');
renderTables($arrYears,$bt,$titles,$arrD,$arrS,$strTendaysago,$arrC);
echo '* <small>'.L('Pending_items').'</small>';

// Add Differences to series
$titles = array('N'=>L('Type').' '.L('news'),'C'=>L('Status').' '.L('closed'),'Z'=>L('Pending'),'ATT'=>L('Attachments'));
$arrD[$y]['NN']=getArrDiff($arrD[$y]['N'],$arrD[$y]['T']);
$arrC[$y]['NN']=$colorFade[3];
$arrD[$y]['NC']=getArrDiff($arrD[$y]['C'],$arrD[$y]['T']);
$arrC[$y]['NC']=$colorFade[3];

//change the null values to zero to be able to make charts and change title
foreach($arrSeries as $serie)
{
  $titles[$serie]=$titles[$serie].' '.L('per_'.$bt);
  foreach($arrYears as $year) $arrD[$year][$serie]=qtArrayzero($arrD[$year][$serie]);
}

// display graphs
if ( !file_exists('bin/js/chart.css') || !file_exists('bin/js/chart.js') ) { echo '<p>Missing chart library...</p>'; exit; }
$arrA = array_values(getAbscissa($bt,MAXBT,$strTendaysago)); // abscisse (not indexed)

echo '<div id="nav-charts">
'.L('Charts_options').'&nbsp; <select id="chartsType" onchange="resetCharts([1,2],[`stack0`,`stack0`]);resetCharts([3,4]);storeChartsOptions();">
<option value="b">'.L('Bar').'</option>
<option value="l">'.L('Line').'</option>
</select> &nbsp; <span class="cblabel"><input type="checkbox" id="chartsPercent" onclick="chartY([1,2],[`stack0`,`stack0`]);chartY([3,4]);storeChartsOptions();" > <label for="chartsPercent">'.L('Percent').'</label></span>
</div>
';
// Tips: canvas needs a div block to limit chart size (otherwise it would be 100% width, because parent "charts" is displayed as grid)
echo '<div class="charts">
<div class="chart"><canvas id="chart1" width="350px" height="250px"></canvas></div>
<div class="chart"><canvas id="chart2" width="350px" height="250px"></canvas></div>
<div class="chart"><canvas id="chart3" width="350px" height="250px"></canvas></div>
<div class="chart"><canvas id="chart4" width="350px" height="250px"></canvas></div>
</div>
';

$stackT = array_sum($arrD[$y]['T']); // used in percent computation with stacked series
$oH->scripts[] = 'var labels = '.json_encode($arrA).';
var chartconf1 = {
  title: "'.html_entity_decode($titles['N'], ENT_NOQUOTES).'",
  label: ["'.html_entity_decode(L('News'), ENT_NOQUOTES).'","'.html_entity_decode(L('Item+'), ENT_NOQUOTES).'"],
  data: ['.json_encode($arrD[$y]['N']).','.json_encode($arrD[$y]['NN']).'],
  maxy: '.qtRoof($arrD[$y]['T']).',
  stacktotal: ['.$stackT.','.$stackT.'],
  color: ["'.$arrC[$y]['N'].'","'.$arrC[$y]['NN'].'"]
  };
var chartconf2 = {
  title: "'.html_entity_decode($titles['C'], ENT_NOQUOTES).'",
  label: ["'.html_entity_decode(L('Closed'), ENT_NOQUOTES).'","'.html_entity_decode(L('Opened'), ENT_NOQUOTES).'"],
  data: ['.json_encode($arrD[$y]['C']).','.json_encode($arrD[$y]['NC']).'],
  maxy: '.qtRoof($arrD[$y]['T']).',
  stacktotal: ['.$stackT.','.$stackT.'],
  color: ["'.$arrC[$y]['C'].'","'.$arrC[$y]['NC'].'"]
  };
var chartconf3 = {
  title: "'.html_entity_decode($titles['Z'], ENT_NOQUOTES).'",
  label: ["'.$y.'"],
  data: ['.json_encode($arrD[$y]['Z']).'],
  maxy: '.qtRoof($arrD[$y]['Z']).',
  stacktotal: false,
  color: ["'.$arrC[$y]['Z'].'"]
  };
var chartconf4 = {
  title: "'.html_entity_decode($titles['ATT'], ENT_NOQUOTES).'",
  label: ["'.$y.'"],
  data: ['.json_encode($arrD[$y]['ATT']).'],
  maxy: '.qtRoof($arrD[$y]['ATT']).',
  stacktotal: false,
  color: ["'.$arrC[$y]['ATT'].'"]
  };';
$oH->scripts[] = 'iniChartsOptions();
makeChart([1,2],["stack0","stack0"]);
makeChart([3,4]);';

break;

//--------
case 'dt':
//--------

// Table
$arrSeries=array('N','C','Z','ATT');
$arrC[$y0] = array_combine($arrSeries,array_slice($colorFade,0,count($arrSeries)));
$arrC[$y0]['ATT']=$colorFade[4];
$arrC[$y] = array_combine($arrSeries,array_slice($colorBase,0,count($arrSeries)));
$arrC[$y]['ATT']=$colorBase[4];
$titles = array('N'=>L('Type').' '.L('news'),'C'=>L('Status').' '.L('closed'),'Z'=>L('Pending').'*','ATT'=>L('Attachments').'*');
renderTables($arrYears,$bt,$titles,$arrD,$arrS,$strTendaysago,$arrC);
echo '* <small>'.L('Pending_items').'</small>';

// Add Differences to series
$titles = array('N'=>L('Type').' '.L('news'),'C'=>L('Status').' '.L('closed'),'Z'=>L('Pending'),'ATT'=>L('Attachments'));
$arrD[$y0]['NN']=getArrDiff($arrD[$y0]['N'],$arrD[$y0]['T']);
$arrC[$y0]['NN']=$colorFade[3];
$arrD[$y0]['NC']=getArrDiff($arrD[$y0]['C'],$arrD[$y0]['T']);
$arrC[$y0]['NC']=$colorFade[3];
$arrD[$y]['NN']=getArrDiff($arrD[$y]['N'],$arrD[$y]['T']);
$arrC[$y]['NN']=$colorBase[3];
$arrD[$y]['NC']=getArrDiff($arrD[$y]['C'],$arrD[$y]['T']);
$arrC[$y]['NC']=$colorBase[3];

//change the null values to zero to be able to make charts and change title
foreach($arrSeries as $serie)
{
  $titles[$serie]=$titles[$serie].' '.L('per_'.$bt);
  foreach($arrYears as $year) $arrD[$year][$serie]=qtArrayzero($arrD[$year][$serie]);
}

// display graphs
if ( !file_exists('bin/js/chart.css') || !file_exists('bin/js/chart.js') ) { echo '<p>Missing chart library...</p>'; exit; }

// display graphs
$arrA = array_values(getAbscissa($bt,MAXBT,$strTendaysago)); // abscisse (not indexed)
$titles['CT'] = L('Cumul').' '.L('item+');

echo '<div id="nav-charts">
'.L('Charts_options').'&nbsp; <select id="chartsType" onchange="resetCharts([1,2],[`stack0`,`stack0`,`stack1`,`stack1`]);resetCharts([3,4]);storeChartsOptions();">
<option value="b">'.L('Bar').'</option>
<option value="l">'.L('Line').'</option>
</select> &nbsp; <span class="cblabel"><input type="checkbox" id="chartsPercent" onclick="chartY([1,2],[`stack0`,`stack0`,`stack1`,`stack1`]);chartY([3,4]);storeChartsOptions();"> <label for="chartsPercent">'.L('Percent').'</label></span>
</div>
';
// Tips: canvas needs a div block to limit chart size (otherwise it would be 100% width, because parent "charts" is displayed as grid)
echo '<div class="charts">
<div class="chart"><canvas id="chart1" width="350px" height="250px"></canvas></div>
<div class="chart"><canvas id="chart2" width="350px" height="250px"></canvas></div>
<div class="chart"><canvas id="chart3" width="350px" height="250px"></canvas></div>
<div class="chart"><canvas id="chart4" width="350px" height="250px"></canvas></div>
</div>
';

$stackT = array_sum($arrD[$y]['T']); // used in percent computation with stacked series
$stackT0 = array_sum($arrD[$y0]['T']);
$oH->scripts[] = 'var labels = '.json_encode($arrA).';
var chartconf1 = {
  title: "'.html_entity_decode($titles['N'], ENT_NOQUOTES).'",
  label: ["'.$y0.' '.L('news').'","'.L('item+').'","'.$y.' '.L('news').'","'.L('item+').'"],
  data: ['.json_encode($arrD[$y0]['N']).','.json_encode($arrD[$y0]['NN']).','.json_encode($arrD[$y]['N']).','.json_encode($arrD[$y]['NN']).'],
  maxy: '.qtRoof(array_merge($arrD[$y0]['T'],$arrD[$y0]['T'])).',
  stacktotal: ['.$stackT0.','.$stackT0.','.$stackT.','.$stackT.'],
  color: ["'.$arrC[$y0]['N'].'","'.$arrC[$y0]['NN'].'","'.$arrC[$y]['N'].'","'.$arrC[$y]['NN'].'"]
  };
var chartconf2 = {
  title: "'.html_entity_decode($titles['C'], ENT_NOQUOTES).'",
  label: ["'.$y0.' '.L('closed').'","'.L('opened').'","'.$y.' '.L('closed').'","'.L('opened').'"],
  data: ['.json_encode($arrD[$y0]['C']).','.json_encode($arrD[$y0]['NC']).','.json_encode($arrD[$y]['C']).','.json_encode($arrD[$y]['NC']).'],
  maxy: '.qtRoof(array_merge($arrD[$y0]['T'],$arrD[$y0]['T'])).',
  stacktotal: ['.$stackT0.','.$stackT0.','.$stackT.','.$stackT.'],
  color: ["'.$arrC[$y0]['C'].'","'.$arrC[$y0]['NC'].'","'.$arrC[$y]['C'].'","'.$arrC[$y]['NC'].'"]
  };
var chartconf3 = {
  title: "'.html_entity_decode($titles['Z'], ENT_NOQUOTES).'",
  label: ["'.$y0.'","'.$y.'"],
  data: ['.json_encode($arrD[$y0]['Z']).','.json_encode($arrD[$y]['Z']).'],
  maxy: '.qtRoof(array_merge($arrD[$y0]['Z'],$arrD[$y]['Z'])).',
  stacktotal: false,
  color: ["'.$arrC[$y0]['Z'].'","'.$arrC[$y]['Z'].'"]
  };
var chartconf4 = {
  title: "'.html_entity_decode($titles['ATT'], ENT_NOQUOTES).'",
  label: ["'.$y0.'","'.$y.'"],
  data: ['.json_encode($arrD[$y0]['ATT']).','.json_encode($arrD[$y]['ATT']).'],
  maxy: '.qtRoof(array_merge($arrD[$y0]['ATT'],$arrD[$y]['ATT'])).',
  stacktotal: false,
  color: ["'.$arrC[$y0]['ATT'].'","'.$arrC[$y]['ATT'].'"]
  };';
$oH->scripts[] = 'iniChartsOptions();
makeChart([1,2],["stack0","stack0","stack1","stack1"]);
makeChart([3,4]);';

break;

//--------
default: die('Invalid tab');
//--------
}

echo '
</div>
';

// CSV
if ( file_exists('qtf_stats_csv.php') ) {
  echo '<p class="right table-ui-export"><a class="csv" href="qtf_stats_csv.php?'.parse_url($_SERVER['REQUEST_URI'],PHP_URL_QUERY).'" title="'.L('H_Csv').'">'.L('Csv').'</a></p>';
}

// --------
// HTML END
// --------

if ( $_SESSION[QT]['tags']!='0' )
$oH->scripts['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js"></script>
<script type="text/javascript" src="bin/js/qtf_config_ac.js"></script>';

include 'qtf_inc_ft.php';