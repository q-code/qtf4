<?php // v4.0 build:20230205 can be app impersonated {qt f|i}

/**
* PHP version 7
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license. If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QuickTalk
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2012 The PHP Group
*/

session_start();
require 'bin/init.php';
/**
* @var CHtml $oH
* @var array $L
* @var CDatabase $oDB
*/
if ( !isset($_SESSION[QT]['m_rss']) ) die('Access denied');

$arrConf = explode(' ',$_SESSION[QT]['m_rss_conf']);
$strUser = $arrConf[0];
$strForm = $arrConf[1];

if ( !SUser::canView(($strUser=='V' ? 'V' : 'U')) ) die('Access denied');

// INITIALISE

include translate(APP.'m_rss.php');

$oH->selfurl = APP.'m_rss.php';
$oH->selfname = $L['rss']['Rss'];

$strRssUrl = $_SESSION[QT]['site_url'].'/rss/';

$arrDS = array(); // only public sections (type '0')
foreach(SMem::get('_Sections') as $id=>$mSec)
{
  if ( $mSec['type'] ) continue;
  $arrDS[$mSec['pid']][$id] = $mSec;
}
if ( count($arrDS)==0 )
{
  // end if no section
  include APP.'_inc_hd.php';
  echo '<h2>'.$oH->selfname.'</h2><p>Format: '.($strForm==='atom' ? 'Atom' : 'Rss 2.0').'</p>'.PHP_EOL;
  echo '<p>'.$L['rss']['E_nosection'].'</p>';
  include APP.'_inc_ft.php';
  exit;
}

// Prepare rss-link header
foreach($arrDS as $arrSections) {
foreach($arrSections as $id=>$mSec) {
  $oH->links[] = '<link rel="alternate" type="application/rss+xml" title="'.qtAttr($mSec['title']).'" href="'.$strRssUrl.'/qtf_'.$strForm.'_'.$id.'.xml"/>';
}}

// --------
// HTML BEGIN
// --------

include APP.'_inc_hd.php';

// TITLE & version
echo '<h2>'.$oH->selfname.'</h2><p>Format: '.($strForm=='atom' ? 'Atom' : 'Rss 2.0').'</p>'.PHP_EOL;

foreach($arrDS as $domId=>$arrSections)
{
//  $arrSections = getSections('V',$intDomain);
    echo '<table class="t-sec">'.PHP_EOL;
    echo '<tr class="t-sec">';
    echo '<th style="width:50px">&nbsp;</th>';
    echo '<th style="width:35%" class="c-section">'.SLang::translate('domain', 'd'.$domId, empty($_Domains[$domId]['title']) ? '(domain-'.$domId.')' : $_Domains[$domId]['title'] ).'</th>';
    echo '<th>'.$L['rss']['Url'].'</th>';
    echo '</tr>';

    // SHOW SECTIONS

    foreach($arrSections as $id=>$mSec)
    {
      echo '<tr class="t-sec hover">';
      echo '<td class="c-icon">'.asImg( QT_SKIN.'img/section_'.$mSec['type'].'_'.$mSec['status'].'.gif', 'title='.L('Ico_section_'.$mSec['type'].'_'.$mSec['status']) ).'</td>';
      echo '<td class="c-section"><span class="section">'.$mSec['title'].'</span><br><span class="sectiondesc">'.$mSec['descr'].'</span></td><td>';
      echo '<a href="'.$strRssUrl.APP.'_'.$strForm.'_'.$id.'.xml" title="syndication">'.getSVG('rss-square').' rss</a> &middot; '.$strRssUrl.APP.'_'.$strForm.'_'.$id.'.xml';
      if ( !file_exists('rss/'.APP.'_'.$strForm.'_'.$id.'.xml') ) echo '<p class="minor">'.getSVG('exclamation-triangle').' '.L('rss.File_not_available').'</p>';
      echo '</td></tr>';
    }
    echo '</table>'.PHP_EOL;
}

// HTML END

include APP.'_inc_ft.php';