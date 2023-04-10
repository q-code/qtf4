<?php // v4.0 build:20230205
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 * @var string $tag
 * @var string $strTendaysago
 * @var string $bt
 * @var string $sqlSection
 * @var string $sqlType
 * @var string $sqlTags
 * @var int $s
 * @var int $y
 * @var int $intStartyear
 * @var int $intEndyear
 * @var int $intStartmonth
 * @var int $intEndmonth
 * @var array $arrYears
 * @var array $arrSeries
 * @var array $arrA
 * @var array $arrD
 * @var array $arrS
 */

// Initialise array values. When a value is missing the display will show &middot;
// MAXBT must be declare before

$intCurrentYear = $y;
$strCurrentTendaysago = $strTendaysago;

$arrA = getAbscissa($bt,MAXBT,$strTendaysago);
$arrD = array(); // Datasets
$arrS = array(); // Datasets_sum

// -----
foreach($arrYears as $year) {
foreach($arrSeries as $serie) {
// -----

// check memcache (only when no options)
if ( $s==='*' && empty($tag) ) {
  $mD = SMem::get('statD'.$year.$serie.$bt);
  if ( $mD!==false ) {
    $mS = SMem::get('statS'.$year.$serie.$bt);
    if ( $mS!==false ) {
      $arrD[$year][$serie] = $mD;
      $arrS[$year][$serie] = $mS;
      if ( isset($_SESSION['QTdebugmem']) ) {
        $oH->log[]='arrD['.$year.']['.$serie.'] loaded from memcache';
        $oH->log[]='arrS['.$year.']['.$serie.'] loaded from memcache';
      }
      continue;
    }
  }
}

// initialize
$arrS[$year][$serie] = 0; //sum set to 0
$arrD[$year][$serie] = array_fill(1,MAXBT,null); //values (index Q:1-4,M:1-12,D:1-10) set to null
if ( $intCurrentYear==$year ) { $strTendaysago = $strCurrentTendaysago; } else { $strTendaysago = addDate(substr($strTendaysago,0,8),-1,'year'); }

// count
for ($intBt=1;$intBt<=MAXBT;$intBt++)
{
  // check limits (startdate/enddate)
  if ( $year<$intStartyear ) continue;
  if ( $year==$intStartyear )
  {
    if ( $bt=='m' && $intBt<$intStartmonth ) continue;
    if ( $bt=='q' && (($intBt==1 && $intStartmonth>3) || ($intBt==2 && $intStartmonth>6) || ($intBt==3 && $intStartmonth>9)) ) continue;
  }
  if ( $year>=$intEndyear )
  {
    if ( $bt=='m' && $intBt>$intEndmonth ) continue;
    if ( $bt=='q' && (($intBt==2 && $intEndmonth<4) || ($intBt==3 && $intEndmonth<7) || ($intBt==4 && $intEndmonth<10)) ) continue;
  }

  switch($serie.$bt)
  {
    // [serie|bt] T=topics, R=replies, Z=unreplied and opened, U=users having post, N=type news, C=status closed, ATT=attachments, m=per month, q=per quarter, d=last 10 days
    case 'Tm': $sql='SELECT count(id) as countid FROM TABTOPIC WHERE '.$sqlSection.$sqlTags.sqlDateCondition((string)($year*100+$intBt),'firstpostdate',6); break;
    case 'Tq': $sql='SELECT count(id) as countid FROM TABTOPIC WHERE '.$sqlSection.$sqlTags.sqlDateCondition((string)($year*100+($intBt-1)*3),'firstpostdate',6,'>').' AND '.sqlDateCondition(($year*100+($intBt*3)),'firstpostdate',6,'<='); break;
    case 'Td': $sql='SELECT count(id) as countid FROM TABTOPIC WHERE '.$sqlSection.$sqlTags.sqlDateCondition(addDate($strTendaysago,$intBt,'day'),'firstpostdate',8); break;
    case 'Rm': $sql='SELECT sum(replies) as countid FROM TABTOPIC WHERE '.$sqlSection.$sqlTags.sqlDateCondition((string)($year*100+$intBt),'lastpostdate',6); break;
    case 'Rq': $sql='SELECT sum(replies) as countid FROM TABTOPIC WHERE '.$sqlSection.$sqlTags.sqlDateCondition((string)($year*100+($intBt-1)*3),'lastpostdate',6,'>').' AND '.sqlDateCondition(($year*100+($intBt*3)),'lastpostdate',6,'<='); break;
    case 'Rd': $sql='SELECT sum(replies) as countid FROM TABTOPIC WHERE '.$sqlSection.$sqlTags.sqlDateCondition(addDate($strTendaysago,$intBt,'day'),'lastpostdate',8); break;
    case 'Zm': $sql='SELECT count(id) as countid FROM TABTOPIC WHERE '.$sqlSection.'status="0" AND replies=0 AND '.$sqlTags.sqlDateCondition((string)($year*100+$intBt),'firstpostdate',6); break;
    case 'Zq': $sql='SELECT count(id) as countid FROM TABTOPIC WHERE '.$sqlSection.'status="0" AND replies=0 AND '.$sqlTags.sqlDateCondition((string)($year*100+($intBt-1)*3),'firstpostdate',6,'>').' AND '.sqlDateCondition(($year*100+($intBt*3)),'firstpostdate',6,'<='); break;
    case 'Zd': $sql='SELECT count(id) as countid FROM TABTOPIC WHERE '.$sqlSection.'status="0" AND replies=0 AND '.$sqlTags.sqlDateCondition((addDate($strTendaysago,$intBt,'day')),'firstpostdate',8); break;
    case 'Um': $sql='SELECT count(DISTINCT userid) as countid FROM TABPOST WHERE '.$sqlSection.sqlDateCondition((string)($year*100+$intBt),'issuedate',6); break;
    case 'Uq': $sql='SELECT count(DISTINCT userid) as countid FROM TABPOST WHERE '.$sqlSection.sqlDateCondition((string)($year*100+($intBt-1)*3),'issuedate',6,'>').' AND '.sqlDateCondition(($year*100+($intBt*3)),'issuedate',6,'<='); break;
    case 'Ud': $sql='SELECT count(DISTINCT userid) as countid FROM TABPOST WHERE '.$sqlSection.sqlDateCondition(addDate($strTendaysago,$intBt,'day'),'issuedate',8); break;
    case 'Nm': $sql='SELECT count(id) as countid FROM TABTOPIC WHERE '.$sqlSection.'type="A" AND '.$sqlTags.sqlDateCondition((string)($year*100+$intBt),'firstpostdate',6); break;
    case 'Nq': $sql='SELECT count(id) as countid FROM TABTOPIC WHERE '.$sqlSection.'type="A" AND '.$sqlTags.sqlDateCondition((string)($year*100+($intBt-1)*3),'firstpostdate',6,'>').' AND '.sqlDateCondition(($year*100+($intBt*3)),'firstpostdate',6,'<='); break;
    case 'Nd': $sql='SELECT count(id) as countid FROM TABTOPIC WHERE '.$sqlSection.'type="A" AND '.$sqlTags.sqlDateCondition(addDate($strTendaysago,$intBt,'day'),'firstpostdate',8); break;
    case 'Cm': $sql='SELECT count(id) as countid FROM TABTOPIC WHERE '.$sqlSection.'status="1" AND '.$sqlTags.sqlDateCondition((string)($year*100+$intBt),'firstpostdate',6); break;
    case 'Cq': $sql='SELECT count(id) as countid FROM TABTOPIC WHERE '.$sqlSection.'status="1" AND '.$sqlTags.sqlDateCondition((string)($year*100+($intBt-1)*3),'firstpostdate',6,'>').' AND '.sqlDateCondition(($year*100+($intBt*3)),'firstpostdate',6,'<='); break;
    case 'Cd': $sql='SELECT count(id) as countid FROM TABTOPIC WHERE '.$sqlSection.'status="1" AND '.$sqlTags.sqlDateCondition(addDate($strTendaysago,$intBt,'day'),'firstpostdate',8); break;
    case 'ATTm': $sql='SELECT count(id) as countid FROM TABPOST WHERE attach<>"" AND '.$sqlSection.sqlDateCondition((string)($year*100+$intBt),'issuedate',6); break;
    case 'ATTq': $sql='SELECT count(id) as countid FROM TABPOST WHERE attach<>"" AND '.$sqlSection.sqlDateCondition((string)($year*100+($intBt-1)*3),'issuedate',6,'>').' AND '.sqlDateCondition(($year*100+($intBt*3)),'issuedate',6,'<='); break;
    case 'ATTd': $sql='SELECT count(id) as countid FROM TABPOST WHERE attach<>"" AND '.$sqlSection.sqlDateCondition(addDate($strTendaysago,$intBt,'day'),'issuedate',8); break;
  }
  $x = $oDB->count($sql); if ( $x===false ) $x=0;
  $arrD[$year][$serie][$intBt] = $x;
  $arrS[$year][$serie] += $arrD[$year][$serie][$intBt]; // total
}

// store in memcache
if ( $s==='*' && empty($tag) ) {
  if ( $mD===false ) {
    SMem::set('statD'.$year.$serie.$bt, $arrD[$year][$serie], 0);
    SMem::set('statS'.$year.$serie.$bt, $arrS[$year][$serie], 0);
    if ( isset($_SESSION['QTdebugmem']) ) {
      $oH->log[]='arrD['.$year.']['.$serie.'] stored in memcache statD'.$year.$serie.$bt;
      $oH->log[]='arrS['.$year.']['.$serie.'] stored in memcache statS'.$year.$serie.$bt;
    }
  }
}

// -----
}}
// -----