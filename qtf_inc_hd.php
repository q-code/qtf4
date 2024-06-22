<?php // v4.0 build:20240210

/**
 * @var CHtml $oH
 * @var CSection $oS (always if isset)
 * @var CTopic $oT (always if isset)
 */
// Page log
if ( $_SESSION[QT]['board_offline'] ) $oH->log[] = 'Warning: the board is offline. Only administrators can perform actions.'.(SUser::role()==='A' ? ' <a href="qtf_adm_index.php">Administration pages...</a>' : ' <a href="qtf_login.php">Sign in...</a>');

// Check banner
if ( !isset($_SESSION[QT]['show_banner']) ) $_SESSION[QT]['show_banner'] = '0';

// Menus definition
$navMenu = new CMenu();
if ( $_SESSION[QT]['home_menu']==='1' && !empty($_SESSION[QT]['home_url']) )
$navMenu->add('home', 'text='.qtAttr($_SESSION[QT]['home_name']).'|href='.$_SESSION[QT]['home_url']);
$navMenu->add('privacy', 'text='.L('Legal').'|href=qtf_privacy.php');
$navMenu->add('index', 'text='. SLang::translate().'|href=qtf_index.php|class=secondary|activewith=qtf_items.php qtf_item.php qtf_edit.php');
$navMenu->add('search', 'text='.L('Search').'|id=nav-search|activewith=qtf_search.php');
if ( $oH->php!=='qtf_search.php' )
$navMenu->menu['search'] .= '|href=qtf_search.php'.(QT_SIMPLESEARCH ? '|onclick=if ( document.getElementById(`searchbar`).style.display===`flex`) return true; qtToggle(`#searchbar`,`flex`); qtFocusAfter(`qkw`); return false' : '');
  // SUser::canAccess('search') not included here... We want the searchbar/page shows a message for not granted users
if ( SUser::canAccess('show_memberlist') )
$navMenu->add('users', 'text='.L('Memberlist').'|href=qtf_users.php');
if ( SUser::canAccess('show_stats') )
$navMenu->add('stats', 'text='.L('Statistics').'|href=qtf_stats.php');
if ( SUser::auth() ) {
$navMenu->add('profile', 'text='.L('Profile').'|href=qtf_user.php?id='.SUser::id().'|class=secondary|activewith=qtf_user.php qtf_register.php');
$navMenu->add('sign', 'text='.L('Logout').'|href=qtf_login.php?a=out|class=nav-sign');
} else {
$navMenu->add('profile', 'text='.L('Register').'|href=qtf_register.php?a=rules|class=secondary');
$navMenu->add('sign', 'text='.L('Login').'|href=qtf_login.php|class=nav-sign');
}

// Menu when board offline or urlrewrite
if ( $_SESSION[QT]['board_offline'] && SUser::role()!=='A' ) {
  $navMenu->update('search', 'href', '');
  $navMenu->update('search', 'onclick', '');
  $navMenu->update('profile', 'href', '');
}
if ( QT_URLREWRITE ) {
  foreach(array_keys($navMenu->menu) as $k) {
    $navMenu->update( $k, 'href', url($navMenu->get($k,'href')) );
    $navMenu->update( $k, 'activewith', implode(' ',array_map('url',explode(' ',$navMenu->get($k,'activewith')))) );
  }
}

if ( !isset($hideMenuLang) ) $hideMenuLang = false;
if ( defined('HIDE_MENU_LANG') && HIDE_MENU_LANG ) $hideMenuLang = true;

// menu-lang (user,lang,contrast)
if ( !$hideMenuLang ) {
  $langMenu = new CMenu();
  // user
  $langMenu->add( '!'.qtSvg('user-'.SUser::role(), 'title='.L('Role_'.SUser::role())) );
  $langMenu->add( SUser::id()>0 ? 'text='.SUser::name().'|id=logname|href='.url(APP.'_user.php').'?id='.SUser::id() : 'text='.L('Role_V').'|tag=span|id=logname');
  // lang
  if ( $_SESSION[QT]['userlang'] ) {
    if ( is_array(LANGUAGES) && count(LANGUAGES)>1 ) {
      $langMenu->add( '!|' );
      foreach (LANGUAGES as $iso=>$language) {
        $arr = explode(' ', $language, 2);
        $langMenu->add( 'text='.$arr[0].'|id=lang-'.$iso.'|href='.url($oH->php).qtURI('lang').'&lang='.$iso.'|title='.(isset($arr[1]) ? $arr[1] : $arr[0]) );
      }
    } else {
      $langMenu->add('!missing file:config/config_lang.php');
    }
  }
  // contrast
  if ( QT_MENU_CONTRAST ) {
    $langMenu->add( 'text='.qtSvg('adjust').'|href=javascript:void(0)|id=contrast-ctrl|aria-label=High contrast|title=High contrast display|role=switch|aria-checked=false' );
    $oH->links['cssContrast'] = '<link id="contrastcss" rel="stylesheet" type="text/css" href="bin/css/contrast.css" disabled/>';
    $oH->scripts[] = "document.getElementById('contrast-ctrl').addEventListener('click', tglContrast);
      qtApplyStoredState('contrast');
    function tglContrast(){
      this.setAttribute('aria-checked', this.getAttribute('aria-checked')==='true' ? 'false' : 'true');
      qtAttrStorage('contrast-ctrl','qt-contrast');
      qtApplyStoredState('contrast');
    }";
  }
}

// ------
// HTML BEGIN
// ------
if ( QT_TITLE_WITH_PAGENAME && !empty($oH->name) ) $oH->metas[0] = '<title>'.$_SESSION[QT]['site_name'].' '.$oH->name.'</title>';
$oH->head();
$oH->body();
echo CHtml::pageEntity('id=site|'.($_SESSION[QT]['viewmode']==='C' ? 'class=compact' : ''), 'site');

// ------
// HEADER shows BANNER LANG-MENU NAV
// ------
// header layout
echo '<header id="banner" data-layout="'.$_SESSION[QT]['show_banner'].'">'.PHP_EOL; // css data-layout (0=no-banner|1=nav-after|2=nav-inside)
// logo
if ( $_SESSION[QT]['show_banner']!=='0' ) echo '<div id="logo"><img src="'.QT_SKIN.'img/'.APP.'_logo.gif" alt="'.qtAttr($_SESSION[QT]['site_name'],24).'" title="'.qtAttr($_SESSION[QT]['site_name']).'"/></div>'.PHP_EOL;
// menu-lang (user,lang,contrast)
if ( !$hideMenuLang ) echo '<div id="menulang">'.$langMenu->build('lang-'.QT_LANG, 'tag=span|class=active').'</div>'.PHP_EOL;
// header nav (intersect to use only some head menus)
$skip = array_diff(array_keys($navMenu->menu), ['home','index','search','users','profile','sign']);
echo '<nav>'.$navMenu->build(url($oH->php), 'default', $skip).'</nav>'.PHP_EOL;
echo '</header>'.PHP_EOL;

// ------
// SEARCH BAR
// ------
if ( QT_SIMPLESEARCH && $oH->php!==APP.'_search.php' ) {
  echo '<div id="searchbar" style="display:none">'.PHP_EOL;
  if ( !SUser::canAccess('search') ) {
    echo L('E_11');
  } else {
    echo '<a href="'.url(APP.'_search.php').'">'.L('Advanced_search').'...</a>';
    echo asImg( QT_SKIN.'img/topic_t_0.gif', 'alt=T|class=img|title='.L('Recent_items'), url(APP.'_items.php').'?q=last' );
    echo asImg( QT_SKIN.'img/topic_a_0.gif', 'alt=T|class=img|title='.L('All_news'), url(APP.'_items.php').'?q=news' );
    if ( SUser::role()!=='V' ) echo '<a href="'.url(APP.'_items.php').'?q=user&fw='.SUser::id().'&fv='.urlencode(SUser::name()).'" title="'.L('All_my_items').'">'.qtSvg('user').'</a>';
    echo '<form method="post" action="'.url(APP.'_search.php').'" style="display:inline">';
    echo '<button id="searchSubmit" type="submit" style="display:none" name="ok" value="'.makeFormCertificate('a2038e83fd6618a444a5de51bf2313de').'">ok</button>';
    echo '<input type="hidden" name="q" value="qkw">';
    echo '<div id="ac-wrapper-qkw"><input required id="qkw" name="fv" type="text" size="25" placeholder="'.L('Number_or_keyword').'" autocomplete="off" /></div> <a class="btn-search" href="javascript:void(0)" title="'.L('Search').' '.L('in_all_sections').'" onclick="document.getElementById(`searchSubmit`).click();">'.qtSvg('search').'</a>';
    echo '</form>';
    $oH->scripts['ac'] = 'if ( typeof acOnClicks==="undefined" ) { var acOnClicks = []; }
    acOnClicks["qkw"] = function(focusInput,btn){ if ( focusInput.id=="qkw" && focusInput.value.substring(0,1)==="#" ) window.location="'.APP.'_item.php?t="+focusInput.value.substring(1); }';
    $oH->scripts_end['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js"></script><script type="text/javascript" src="bin/js/'.APP.'_config_ac.js"></script>';
  }
  echo '<a class="button button-x" href="javascript:void(0)" onclick="qtToggle(`#searchbar`,`none`);" title="'.L('Close').'">'.qtSvg('times').'</a>'.PHP_EOL;
  echo '</div>'.PHP_EOL;
}

// ------
// WELCOME
// ------
$showWelcome = false;
if ( ( $_SESSION[QT]['show_welcome']==='2' || ($_SESSION[QT]['show_welcome']==='1' && !SUser::auth()) ) && file_exists(translate('app_welcome.txt')) && !$_SESSION[QT]['board_offline'] ) $showWelcome = true;
if ( $showWelcome && $oH->php!==APP.'_register.php' ) {
  echo '<div id="intro">';
  include translate('app_welcome.txt');
  echo '</div>';
}

// ------
// MAIN
// ------
echo '
<main>
<div id="main-hd">
';
// CRUMBTRAIL
$crumb[] = '<a href="'.url('qtf_index.php').'"'.($oH->php==='qtf_index.php' ? ' onclick="return false;"' : '').'>'.SLang::translate().'</a>';
if ( (isset($oS) && $oS->id>=0) ) {
  $crumb[] = (QT_SHOW_DOMAIN && $oS->pid>=0 ? CDomain::translate($oS->pid) : '').'<a href="'.url('qtf_items.php').'?s='.$oS->id.'">'.CSection::translate($oS->id).'</a>';
  switch($oH->php) {
  case APP.'_user.php': $crumb[] = L('Profile'); break;
  case APP.'_stats.php': $crumb[] = L('Statistics'); break;
  case APP.'_search.php': $crumb[] = L('Search'); break;
  case APP.'_items.php': if ( $oS->type==='2' && !SUser::isStaff() ) $crumb[] = '<small>'.L('all_my_items').'</small>' ; break;
  case APP.'_item.php': if ( $oS->numfield!=='N' && $oS->numfield!=='' && isset($oT) ) $crumb[] = '<small>'.sprintf($oS->numfield,$oT->numid).'</small>'; break;
  }
}
echo '<p id="crumbtrail">'.implode(QT_CRUMBTRAIL,$crumb).'</p>';

echo '<p id="page-ui">';
switch($oH->php) {
case 'qtf_item.php':
  if ( $_SESSION[QT]['viewmode']=='C' ) {
    echo '<a id="viewmode" href="'.url($oH->php).qtURI('view').'&view=N" title="'.L('View_n').'">'.qtSvg('window-maximize').' '.qtSvg('long-arrow-alt-down').'</a>';
  } else {
    echo '<a id="viewmode" href="'.url($oH->php).qtURI('view').'&view=C" title="'.L('View_c').'">'.qtSvg('window-maximize').' '.qtSvg('long-arrow-alt-up').'</a>';
  }
  break;
case 'qtf_items.php':
  if ( !empty($oH->items) ) {
    $crumbtrail = empty($q) ? '' : qtSvg('search').' ';
    $crumbtrail .= L( in_array($q,['qkw','kw','userm']) ? 'message' : 'item', $oH->items);
    if ( !empty($oH->itemsHidden) ) $crumbtrail .= ' ('.L('hidden',$oH->itemsHidden).')';
    echo '<span id="crumbtrail-info">'.$crumbtrail.'</span>';
  }
  break;
case 'qtf_users.php':
  if ( empty($_SESSION[QT]['formatpicture']) ) $_SESSION[QT]['formatpicture']='mime=0;width=100;height=100';
  if ( !empty(qtExplodeGet($_SESSION[QT]['formatpicture'], 'mime')) ) {
    if ( $_SESSION[QT]['viewmode']==='C' ) {
      echo '<a id="viewmode" href="'.url($oH->php).'?view=N" title="'.L('View_n').'">'.qtSvg('window-maximize').' '.qtSvg('long-arrow-alt-down').'</a>';
    } else {
      echo '<a id="viewmode" href="'.url($oH->php).'?view=C" title="'.L('View_c').'">'.qtSvg('window-maximize').' '.qtSvg('long-arrow-alt-up').'</a>';
    }
  }
  break;
}

echo '</p>
</div>
';

// MAIN CONTENT / $oS->id=-1 in case of 'void'-section
$str =  isset($oS) && $oS->id>=0 ? ' data-section-type="'.$oS->type.'" data-section-status="'.$oS->status.'"' : '';
echo '<div id="main-ct" class="pg-'.qtBasename($oH->php).'"'.$str.'>
';
if ( !empty($oH->error) ) echo '<p class="error center">'.$oH->error.'</p>';