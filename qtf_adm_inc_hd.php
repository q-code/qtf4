<?php // v4.0 build:20230430
/**
* @var CHtml $oH
* @var array $L
* @var CDatabase $oDB
*/
ob_start();

// Change default header settings
$oH->links['ico'] = '<link rel="shortcut icon" href="bin/css/qtf_icon.ico"/>';
unset($oH->links['cssContrast']);
$oH->links['css'] = '<link rel="stylesheet" type="text/css" href="bin/css/admin.css"/>';
if ( !isset($oH->scripts['e0']) ) $oH->scripts['e0'] = 'var e0 = '.(empty(L('E_editing')) ? 'Data not yet saved. Quit without saving?' : '"'.L('E_editing').'"').';';
$oH->links['cssCustom'] = null;

// Render
$oH->head();
$oH->body();

echo PHP_EOL.'<div class="pg-admin">'.PHP_EOL;

if ( file_exists(translate($oH->selfurl.'.txt'))  )
{
  echo '<div class="hlp-box">';
  echo '<div class="hlp-head">'.L('Help').'</div>';
  echo '<div class="hlp-body">';
  include translate($oH->selfurl.'.txt');
  echo '</div></div>';
}

echo '
<div id="banner"><img id="logo" src="bin/css/'.APP.'_logo.gif" style="border-width:0" alt="'.APPNAME.'" title="'.APPNAME.'"/></div>
';

if ( !defined('HIDE_MENU_LANG') || !HIDE_MENU_LANG )
{
  $langMenu = new CMenu();
  $langMenu->add('text='.getSVG('caret-square-left').'|id=lang-exit|href='.APP.'_index.php|title='.L('Exit').'|onclick=return qtFormSafe.exit(e0);');
  // lang
  if ( $_SESSION[QT]['userlang'] ) {
    if ( is_array(LANGUAGES) && count(LANGUAGES)>1 ) {
      foreach (LANGUAGES as $iso=>$lang) {
        $lang = explode(' ',$lang);
        $lang = empty($lang[1]) ? strtoupper($iso) : $lang[1]; // uppercase iso code if no description
        $langMenu->add('lang-'.$iso, strtoupper($iso).'|href='.$oH->selfurl.'?'.qtURI('lang').'&lang='.$iso.'||title='.$lang.'|onclick=return qtFormSafe.exit(e0);');
      }
    } else {
      $langMenu->add('!missing file:config/config_lang.php');
    }
  }
  $langMenu->separator = '&nbsp;';
  echo '<div id="menulang">'.$langMenu->build('lang-'.QT_LANG, 'tag=span|onclick=return false').'</div>';
}

echo '<div id="pg-layout">
';

if ( !defined('HIDE_MENU_TOC') || !HIDE_MENU_TOC )
{
  $navMenu = new CMenu(null,'');
  echo '<div id="toc">'.PHP_EOL;
  $navMenu->add(L('Info').          '|tag=p|class=group');
  $navMenu->add(L('Board_status').  '|href=qtf_adm_index.php|class=item|onclick=return qtFormSafe.exit(e0);');
  $navMenu->add(L('Board_general'). '|href=qtf_adm_site.php|class=item|onclick=return qtFormSafe.exit(e0);');
  echo '<div class="group">'.$navMenu->build($oH->selfurl).'</div>';
  // group settings
  $navMenu->menu = [];
  $navMenu->add(L('Settings').      '|tag=p|class=group');
  $navMenu->add(L('Board_region').  '|href=qtf_adm_region.php|class=item|onclick=return qtFormSafe.exit(e0);|activewith=qtf_adm_time.php');
  $navMenu->add(L('Board_layout').  '|href=qtf_adm_skin.php|class=item|onclick=return qtFormSafe.exit(e0);');
  $navMenu->add(L('Board_security').'|href=qtf_adm_secu.php|class=item|onclick=return qtFormSafe.exit(e0);');
  echo '<div class="group">'.$navMenu->build($oH->selfurl).'</div>';
  // group Content
  $navMenu->menu = [];
  $navMenu->add(L('Board_content'). '|tag=p|class=group');
  $navMenu->add(L('Section+').      '|href=qtf_adm_sections.php|class=item|onclick=return qtFormSafe.exit(e0);|activewith=qtf_adm_section.php qtf_adm_domain.php');
  $navMenu->add(L('Item+').         '|href=qtf_adm_items.php|class=item|onclick=return qtFormSafe.exit(e0);');
  $navMenu->add(L('Tags').          '|href=qtf_adm_tags.php|class=item|onclick=return qtFormSafe.exit(e0);');
  $navMenu->add(L('Users').         '|href=qtf_adm_users.php|class=item|onclick=return qtFormSafe.exit(e0);|activewith=qtf_adm_users_exp.php qtf_adm_users_imp.php');
  echo '<div class="group">'.$navMenu->build($oH->selfurl).'</div>';
  // group modules
  $navMenu->menu = [];
  $navMenu->add(L('Board_modules'). '|tag=p|class=group');
  if ( !isset($_SESSION[QT]['mModules']) ) $_SESSION[QT]['mModules'] = $oDB->getSettings('param LIKE "module%"'); // store list of modules in memory if not yet done
  foreach($_SESSION[QT]['mModules'] as $k=>$module)
  {
    $k = str_replace('module_','',$k);
    $navMenu->add($module.'|href=qtfm_'.$k.'_adm.php|class=item|onclick=return qtFormSafe.exit(e0);');
  }
  echo '<div class="group">'.$navMenu->build($oH->selfurl).'<p class="item"><a href="qtf_adm_module.php?a=add" onclick="return qtFormSafe.exit(e0);">['.L('Add').']</a> &middot; <a href="qtf_adm_module.php?a=rem" onclick="return qtFormSafe.exit(e0);">['.L('Remove').']</a></p></div>';
  echo '<a style="display:block;margin:8px 0" class="button center" href="'.APP.'_index.php" onclick="return qtFormSafe.exit(e0);">'.L('Exit').'</a>';
  echo getSVG('user-A', 'class=filigrane');
  echo '</div>'.PHP_EOL;
}

CHtml::getPage();

// Title (and error)
echo '<h1 class="title"'.(isset($oH->selfparent) ? ' data-parent="'.$oH->selfparent.'"' : '').'>'.$oH->selfname.'</h1>';
if ( !empty($oH->selfversion) ) echo '<p class="pageversion">'.$oH->selfversion.'</p>';
if ( !empty($oH->error) ) echo '<p class="error center">'.$oH->error.'</p>';
if ( !empty($oH->warning) ) echo '<p class="warning center">'.$oH->warning.'</p>';
