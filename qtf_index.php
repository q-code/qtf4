<?php

/**
* PHP version 7
*
* LICENSE: This source file is subject to version 3.0 of the PHP license
* that is available through the world-wide-web at the following URI:
* http://www.php.net/license.  If you did not receive a copy of
* the PHP License and are unable to obtain it through the web, please
* send a note to license@php.net so we can mail you a copy immediately.
*
* @package    QuickTalk forum
* @author     Philippe Vandenberghe <info@qt-cute.org>
* @copyright  2012 The PHP Group
* @version    4.0 build:20240210
*/

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 * @var CSection $oS
 */
require 'bin/init.php';

// ------
// SECURITY
// ------
if ( $_SESSION[QT]['board_offline'] ) $oH->voidPage('tools.svg',99,true,false);
if ( $_SESSION[QT]['visitor_right']<1 && SUser::role()==='V' ) $oH->voidPage('user-lock.svg',11,true,false);

// ------
// HTML BEGIN
// ------
include 'qtf_inc_hd.php';

// Table definition
$t = new TabTable('class=t-sec');
$t->thead();
$t->tbody();
// TH
$t->arrTh[0] = new TabHead('&nbsp;', 'class=c-icon');
$t->arrTh[1] = new TabHead('&nbsp;', 'class=c-section ellipsis');
$t->arrTh[2] = new TabHead(L('Last_message'), 'class=c-issue ellipsis');
$t->arrTh[3] = new TabHead(L('Item+'), 'class=c-items ellipsis');
$t->arrTh[4] = new TabHead(L('Reply+'), 'class=c-replies secondary ellipsis');
// TD
$t->cloneThTd();

$intSec = 0;
foreach(array_keys($_Domains) as $idDom) {

  // output
  $arrSections = CDomain::get_pSectionsVisible($idDom); // Sections visible for user-role
  if ( count($arrSections)===0 ) continue; // Skip domain without section
  $t->arrTh[1]->content = CDomain::translate($idDom);

  // Render domain/sections
  echo $t->start();
  echo $t->thead->start();
  echo $t->getTHrow();
  echo $t->thead->end();
  echo $t->tbody->start();

  foreach($arrSections as $idSec=>$mSec) {

    // translations
    $mSec['title'] = CSection::translate($idSec);
    $mSec['descr'] = CSection::translate($idSec,'secdesc');
    // output
    $intSec++;
    $strLastpost = '&nbsp;';
    $logofile = empty($mSec['options']) ? '' : qtExplodeGet($mSec['options'],'logo',''); // specific logo, or '' for default logo
    if ( $mSec['items']>0 ) {
      $strLastpost = qtDate($mSec['lastpostdate'],'$','$',true,true,true);
      $strLastpost .= '<a class="lastitem" href="'.url('qtf_item.php').'?t='.$mSec['lastpostpid'].'#p'.$mSec['lastpostid'].'" title="'.L('Goto_message').'"><svg class="svg-symbol symbol-caret-square-right"><use href="#symbol-caret-square-right" xlink:href="#symbol-caret-square-right"/></svg></a><br><small>'.L('by').' <a href="'.url('qtf_user.php').'?id='.$mSec['lastpostuser'].'">'.$mSec['lastpostname'].'</a></small>';
    }
    $t->arrTd[0]->content = asImg( CSection::makeLogo($logofile,$mSec['type'],$mSec['status']), 'title='.L('Ico_section_'.$mSec['type'].'_'.$mSec['status']), url('qtf_items.php?s='.$idSec) );
    $t->arrTd[1]->content = '<p><a class="section" href="'.url('qtf_items.php?s='.$idSec).'">'.$mSec['title'].'</a></p>'.(empty($mSec['descr']) ? '' : '<p class="sectiondesc">'.$mSec['descr'].'</p>');
    $t->arrTd[2]->content = $strLastpost;
    $t->arrTd[3]->content = qtK($mSec['items']);
    $t->arrTd[4]->content = qtK($mSec['replies']);
    echo $t->getTDrow('class=hover');

  }

  echo $t->tbody->end();
  echo $t->end();

}

// No public section

if ( $intSec===0 ) echo '<p>'.(SUser::role()==='V' ? L('E_sign_in_required') : L('E_no_visible_section')).'</p>';

// HTML END

if ( isset($oS) ) unset($oS);

// DEBUG SSE
if ( isset($_SESSION['QTdebugsse']) && $_SESSION['QTdebugsse'] ) echo '<div id="serverData"></div>';

// Symbols
$oH->symbols[] = qtSVG('symbol-caret-square-right');

include 'qtf_inc_ft.php';