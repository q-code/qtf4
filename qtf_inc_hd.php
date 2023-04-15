<?php // v4.0 build:20230205

/**
 * @var bool $hideMenuProfile
 * @var CDatabase $oDB
 * @var CHtml $oH
 * @var CSection $oS
 * @var CTopic $oT
 * @var int $s
 */
// Page log
if ( $_SESSION[QT]['board_offline'] ) $oH->log[] = 'Warning: the board is offline. Only administrators can perform actions.'.(SUser::role()=='A' ? ' <a href="qtf_adm_index.php">Administration pages...</a>' : ' <a href="qtf_login.php">Sign in...</a>');

// Menu language (when page includes forms build on $_POST arguments, HIDE_MENU_PROFILE can be used to hide language menu)
if ( (defined('HIDE_MENU_PROFILE') && HIDE_MENU_PROFILE) || (isset($hideMenuProfile) && $hideMenuProfile) )
{
  $strLangMenu = '';
}
else
{
  // create menus
  $m = new CMenu();
  $m->add( '!'.getSVG('user-'.SUser::role(), 'title='.L('Role_'.SUser::role())) );
  $m->add( SUser::id()>0 ? 'text='.SUser::name().'|id=logname|href='.Href('qtf_user.php').'?id='.SUser::id() : 'text='.L('Role_V').'|tag=span|id=logname');
  if ( $_SESSION[QT]['userlang'] ) {
    if ( is_array(LANGUAGES) && count(LANGUAGES)>1 ) {
      $m->add( '!|' );
      foreach (LANGUAGES as $iso=>$language) {
        $arr = explode(' ',$language,2);
        $m->add( 'text='.$arr[0].'|id=lang-'.$iso.'|href='.Href($oH->selfurl).'?'.getURI('lang').'&lang='.$iso.'|title='.(isset($arr[1]) ? $arr[1] : $arr[0]) );
      }
    } else {
      $m->add('!missing file:config/config_lang.php');
    }
  }
  if ( QT_MENU_CONTRAST ) {
    $m->add( 'text='.getSVG('adjust').'|href=javascript:void(0)|id=contrast-ctrl|title=High contrast display|aria-current=false' );
    $oH->links['cssContrast'] = '<link id="contrastcss" rel="stylesheet" type="text/css" href="bin/css/qtf_contrast.css" disabled/>';
    $oH->scripts[] = "document.getElementById('contrast-ctrl').addEventListener('click', toggleContrast);
      qtApplyStoredState('contrast');
      function toggleContrast() {
      const d = document.getElementById('contrastcss');
      if ( !d ) { console.log('toggleContrast: no element with id=contrastcss'); return; }
      const ctrl = document.getElementById('contrast-ctrl');
      if ( !ctrl ) { console.log('toggleContrast: no element with id=contrast-ctrl'); return; }
      d.toggleAttribute('disabled');
      ctrl.setAttribute('aria-current', d.disabled ? 'false' : 'true');
      qtAttrStorage('contrast-ctrl','qt-contrast');
    }";
  }
  // group the menus
  $strLangMenu = '<div id="menulang">'.$m->build('lang-'.QT_LANG, 'tag=span|class=active').'</div>';
}

// Check banner
if ( !isset($_SESSION[QT]['show_banner']) ) $_SESSION[QT]['show_banner']='0';

// --------
// HTML BEGIN
// --------

$oH->title = (empty($oH->selfname) ? '' : $oH->selfname.' - ').$oH->title;
$oH->head();
$oH->body();

CHtml::getPage('id=site|'.($_SESSION[QT]['viewmode']==='C' ? 'class=compact' : ''));

// MENU
$arrMenus = array();
if ( $_SESSION[QT]['home_menu']=='1' && !empty($_SESSION[QT]['home_url']) )
$arrMenus['home']    = 'text='.qtAttr($_SESSION[QT]['home_name']).'|href='.$_SESSION[QT]['home_url'];
$arrMenus['privacy'] = 'text='.L('Legal').'|href=qtf_privacy.php';
$arrMenus['index']   = 'text='. SLang::translate().'|href=qtf_index.php|accesskey=i|class=secondary|activewith=qtf_items.php qtf_item.php qtf_edit.php';
$arrMenus['search']  = 'text='.L('Search').'|id=nav-search|activewith=qtf_search.php';
if ( $oH->selfurl!=='qtf_search.php' )
$arrMenus['search'] .= '|href=qtf_search.php|accesskey=s'.(QT_SIMPLESEARCH ? '|onclick=if (document.getElementById(`searchbar`).style.display===`flex`) return true; qtToggle(`searchbar`,`flex`); qtFocusAfter(`qkw`); return false' : '');
  // SUser::canAccess('search') not included here... We want the searchbar/page shows a message for not granted users
if ( SUser::canAccess('show_memberlist') )
$arrMenus['users']   = 'text='.L('Memberlist').'|href=qtf_users.php';
if ( SUser::canAccess('show_stats') )
$arrMenus['stats']   = 'text='.L('Statistics').'|href=qtf_stats.php';
if ( SUser::auth() ) {
$arrMenus['profile'] = 'text='.L('Profile').'|href=qtf_user.php?id='.SUser::id().'|class=secondary|activewith=qtf_user.php qtf_register.php';
$arrMenus['sign']    = 'text='.L('Logout').'|href=qtf_login.php?a=out|class=nav-sign';
} else {
$arrMenus['profile'] = 'text='.L('Register').'|href=qtf_register.php?a=rules|class=secondary';
$arrMenus['sign']    = 'text='.L('Login').'|href=qtf_login.php|class=nav-sign';
}

// Menu when board offline or urlrewrite
if ( $_SESSION[QT]['board_offline'] && SUser::role()!=='A' ) {
  $m = new CMenu($arrMenus);
  $m->update('search','href', '');
  $m->update('search','onclick', '');
  $m->update('profile','href', '');
  $arrMenus = $m->menu;
}
if ( QT_URLREWRITE ) {
  $m = new CMenu($arrMenus);
  foreach(array_keys($m->menu) as $k) {
    $m->update( $k, 'href', Href($m->get($k,'href')) );
    $m->update( $k, 'activewith', implode(' ',array_map('Href',explode(' ',$m->get($k,'activewith')))) );
  }
  $arrMenus = $m->menu;
}

// Use only some menu (head menus)
$m = new CMenu( array_intersect_key($arrMenus, array_flip(['home','index','search','users','profile','sign'])) );
$strNav = '<nav>'.$m->build(Href($oH->selfurl)).'</nav>';

// HEADER shows BANNER, MENU and WELCOME
echo '<header id="banner" data-layout="'.$_SESSION[QT]['show_banner'].'">'.PHP_EOL; // id banner|nobanner
if ( $_SESSION[QT]['show_banner']!=='0' ) echo '<div id="logo"><img src="'.QT_SKIN.'img/qtf_logo.gif" alt="'.qtAttr($_SESSION[QT]['site_name'],24).'" title="'.qtAttr($_SESSION[QT]['site_name']).'"/></div>'.PHP_EOL;
if ( !empty($strLangMenu) ) echo $strLangMenu.PHP_EOL;
echo $strNav.PHP_EOL;
echo '</header>'.PHP_EOL.PHP_EOL;

if ( QT_SIMPLESEARCH && $oH->selfurl!==APP.'_search.php' ) {

echo '<div id="searchbar" style="display:none">
';

if ( !SUser::canAccess('search') ) {

  echo L('E_11');

} else {

  echo '<a href="'.Href('qtf_search.php').'">'.L('Advanced_search').'...</a>';
  echo asImg( QT_SKIN.'img/topic_t_0.gif', 'alt=T|class=img|title='.L('Recent_items'), Href('qtf_items.php').'?q=last' );
  echo asImg( QT_SKIN.'img/topic_a_0.gif', 'alt=T|class=img|title='.L('All_news'), Href('qtf_items.php').'?q=news' );
  if ( SUser::role()!=='V' ) echo '<a href="'.Href('qtf_items.php').'?q=user&v2='.SUser::id().'&v='.urlencode(SUser::name()).'" title="'.L('All_my_items').'">'.getSVG('user').'</a>';
  echo '<form method="post" action="'.Href('qtf_search.php').'" style="display:inline">
<button id="searchSubmit" type="submit" style="display:none" name="ok" value="'.makeFormCertificate('a2038e83fd6618a444a5de51bf2313de').'">ok</button>
<input type="hidden" name="q" value="qkw"><div id="ac-wrapper-qkw" class="ac-wrapper"><input required id="qkw" name="v" type="text" size="25" placeholder="'.L('Number_or_keyword').'" autocomplete="off" /></div> <a class="btn-search" href="javascript:void(0)" title="'.L('Search').' '.L('in_all_sections').'" onclick="document.getElementById(`searchSubmit`).click();">'.getSVG('search').'</a></form>';
$oH->scripts['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js"></script>
<script type="text/javascript" src="bin/js/qtf_config_ac.js"></script>';
$oH->scripts[] = 'acOnClicks["qkw"] = function(focusInput,btn){ if ( focusInput.id=="qkw" && focusInput.value.substring(0,1)=="#" ) window.location="qtf_item.php?t="+focusInput.value.substring(1); }';
}

echo '<a class="button button-x" href="javascript:void(0)" onclick="qtToggle(`searchbar`);" title="'.L('Close').'">'.getSVG('times').'</a>
</div>
';

}

$bWelcome = false;
if ( ( $_SESSION[QT]['show_welcome']=='2' || ($_SESSION[QT]['show_welcome']=='1' && !SUser::auth()) ) && file_exists(translate('app_welcome.txt')) && !$_SESSION[QT]['board_offline'] ) $bWelcome = true;

if ( $bWelcome && $oH->selfurl!=='qtf_register.php' )
{
echo '
<div id="intro">';
include translate('app_welcome.txt');
echo '</div>
';
}

// MAIN
echo '
<main>
';

echo '
<div id="main-hd">
<p id="crumbtrail"><a href="',Href('qtf_index.php'),'"',($oH->selfurl==='qtf_index.php' ? ' onclick="return false;"' : ''),'>',SLang::translate(),'</a>';
if ( isset($s) && is_int($s) && $s>=0 ) {
  if ( isset($oS) ) {
    if ( QT_SHOW_DOMAIN ) echo QT_CRUMBTRAIL.CDomain::translate($oS->pid);
    echo QT_CRUMBTRAIL.'<a href="'.Href('qtf_items.php').'?s='.$s.'">'.CSection::translate($s).'</a>';
    if ( $oS->type==='2' && !SUser::isStaff() ) echo QT_CRUMBTRAIL.'<span class="small">'.L('all_my_items').'</span>';
    if ( $oH->selfurl===APP.'_item.php' && $oS->numfield!=='N' && $oS->numfield!=='' ) echo QT_CRUMBTRAIL.'<span class="small">'.sprintf($oS->numfield,$oT->numid).'</span>';
  }
}
if ( $oH->selfurl===APP.'_user.php' ) echo QT_CRUMBTRAIL.L('Profile');
if ( $oH->selfurl===APP.'_stats.php' ) echo QT_CRUMBTRAIL.L('Statistics');
if ( $oH->selfurl===APP.'_search.php' ) echo QT_CRUMBTRAIL.L('Search');
echo '</p>
<p id="page-ui">';

switch($oH->selfurl)
{
case 'qtf_stats.php':
  $strURI = qtImplode(qtArradd(qtExplodeUri(),'view',null)); // drop the 'view'-part. Arguments remain urlencoded
  break;
case 'qtf_item.php':
  $strURI = qtImplode(qtArradd(qtExplodeUri(),'view',null)); // drop the 'view'-part. Arguments remain urlencoded
  if ( $_SESSION[QT]['viewmode']=='C' ) {
    echo '<a id="viewmode" href="'.Href($oH->selfurl).'?'.$strURI.'&view=N" title="'.L('View_n').'">'.getSVG('window-maximize').' '.getSVG('long-arrow-alt-down').'</a>';
  } else {
    echo '<a id="viewmode" href="'.Href($oH->selfurl).'?'.$strURI.'&view=C" title="'.L('View_c').'">'.getSVG('window-maximize').' '.getSVG('long-arrow-alt-up').'</a>';
  }
  break;
case 'qtf_items.php':
  if ( !empty($oH->items) ) {
    if ( !isset($q) ) $q = 's';
    $strCrumbtrail = $q!=='s' ? getSVG('search').' ' : '';
    $strCrumbtrail .= L( in_array($q,['qkw','kw','userm']) ? 'message' : 'item', $oH->items);
    if ( !empty($oH->itemsHidden) ) $strCrumbtrail .= ' ('.L('hidden',$oH->itemsHidden).')';
    echo '<span id="crumbtrail-info">'.$strCrumbtrail.'</span>';
  }
  break;
case 'qtf_users.php':
  if ( !empty(qtExplodeGet($_SESSION[QT]['formatpicture'], 'mime')) ) {
    if ( $_SESSION[QT]['viewmode']=='C' ) {
      echo '<a id="viewmode" href="'.Href($oH->selfurl).'?view=N" title="'.L('View_n').'">'.getSVG('window-maximize').' '.getSVG('long-arrow-alt-down').'</a>';
    } else {
      echo '<a id="viewmode" href="'.Href($oH->selfurl).'?view=C" title="'.L('View_c').'">'.getSVG('window-maximize').' '.getSVG('long-arrow-alt-up').'</a>';
    }
  }
  break;
}

echo '</p>
</div>
';

// MAIN CONTENT
echo '<div id="main-ct" class="pg-'.baseFile($oH->selfurl).'">
';
if ( !empty($oH->error) ) echo '<p id="infomessage" class="error">'.$oH->error.'</p>';