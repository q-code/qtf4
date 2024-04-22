<?php // v4.0 build:20240210

/**
 * CHtml class allows managing html metadata
 * @author qt-cute.org
 */

class CHtml
{

public $html = '<html>'; // can be use to include xml attributes
public $title = '';
public $metas = [];
public $links = [];
public $scripts_top = [];
public $scripts = [];
public $log = []; // Attention if not empty, is VISIBLE at the bottom of the page.
public $selfname = '';
public $selfurl = APP.'_index.php'; // page filename
public $selfuri = '';
public $selfparent = ''; // parent name
public $selfversion= '';
public $exitname = 'Back';
public $exiturl = APP.'_index.php';
public $items = 0; // number of items in the page, visible (can be in several pages)
public $itemsHidden = 0; // number of items in the page, hidden (by users preferences)
public $error = '';
public $warning = '';
public function head()
{
  //push cssContrast as last link
  if ( !empty($this->links['cssContrast']) ) {
    $arr = array_keys($this->links);
    if ( end($arr)!=='cssContrast' ) { $this->links[] = $this->links['cssContrast']; unset($this->links['cssContrast']); }
  }
  // check/add <script> enclosing tag
  foreach($this->scripts_top as $k=>$src) {
    if ( substr($src,0,8)==='<script ' ) continue;
    $this->scripts_top[$k] = '<script type="text/javascript">'.$src.'</script>';
  }
  // build links
  echo $this->html.PHP_EOL.'<head>'.PHP_EOL.'<title>'.$this->title.'</title>'.PHP_EOL.implode(PHP_EOL,$this->metas).PHP_EOL.implode(PHP_EOL,$this->links).PHP_EOL.implode(PHP_EOL,$this->scripts_top).PHP_EOL.'</head>'.PHP_EOL;
}
public function body(string $attr='')
{
  echo '<body'.attrRender($attr).'>'.PHP_EOL;
}
public function end(bool $allowSplash=true)
{
  $log = empty($this->log) ? '' : '<p id="pagelog">'.implode('<br>',$this->log).(SUser::auth() ? '<br><small>Sign out to stop debugging</small>' : '').'</p>'.PHP_EOL;
  $this->log = []; // clear log
  $splash = '';
  if ( $allowSplash && !empty($_SESSION[QT.'splash']) ) $splash .= Splash::getSplash();
  // check/add <script> enclosing tag
  foreach($this->scripts as $k=>$src) {
    if ( substr($src,0,8)==='<script ' ) continue;
    $this->scripts[$k] = '<script type="text/javascript">'.$src.'</script>';
  }
  // output
  echo $log.PHP_EOL.implode(PHP_EOL,$this->scripts).$splash.PHP_EOL.'</body>'.PHP_EOL.'</html>';
}
public static function pageEntity(string $attr='id=site', string $info='id site', string $entity='div')
{
  if ( $attr==='/' ) return PHP_EOL.'</'.$entity.'>'.PHP_EOL.'<!-- end '.$info.' -->'.PHP_EOL;
  return PHP_EOL.'<!-- start '.$info.' -->'.PHP_EOL.'<'.$entity.''.attrRender($attr).'>'.PHP_EOL;
}
/**
 * Redirect to the url($u)
 * @param string $u 'self|exit|url' selfurl?selfuri
 * @param string $s
 */
public function redirect(string $u='exit', string $s='Continue')
{
  if ( empty($u) ) die(__METHOD__.' arg must be string');
  if ( $u==='self' ) $u = $this->selfurl.($this->selfuri ?: '');
  if ( $u==='exit' ) $u = $this->exiturl;
  $u = url($u);
  if ( headers_sent() ) {
    echo '<a href="'.$u.'">'.$s.'</a><meta http-equiv="REFRESH" content="0;url='.$u.'">';
  } else {
    header('Location: '.str_replace('&amp;','&',$u));
  }
  exit;
}
public function backButton(string $class='button btn-back', string $symbol='chevron-left.svg')
{
  if ( substr($symbol,-4)==='.svg' ) $symbol = file_get_contents('bin/svg/'.$symbol); // on failed returns false
  if ( empty($symbol) ) $symbol = '&lt;';
  return '<a class="'.$class.'" href="'.url($this->exiturl).'">'.$symbol.'</a>';
}
public static function msgBox(string $title='', string $attr='class=msgbox', string $attrTitle='class=msgboxtitle', string $attrBody='class=msgboxbody')
{
  // End msgbox
  if ( $title==='/' ) { echo '</div>'.PHP_EOL.'</div>'.PHP_EOL; return; }
  // Start msgbox.
  echo '<div'.attrRender($attr).'>'.PHP_EOL;
  echo '<div'.attrRender($attrTitle).'>'.$title.'</div>'.PHP_EOL;
  echo '<div'.attrRender($attrBody).'>'.PHP_EOL;
}
public function voidPage(string $title='!', string $content='Access denied', bool $appHeader=false, bool $hideMenuLang=true, string $msgboxAttr='class=msgbox')
{
  if ( empty($content) ) die(__METHOD__.' invalid argument [content]');
  if ( empty($title) ) $title = $this->selfname;
  if ( empty($this->exiturl) ) $this->exiturl = APP.'_index.php';
  if ( substr($title,-4)==='.svg' ) $title = qtSVG(substr($title,0,-4)); // title can be a svg
  $inAdm = strpos($this->selfurl, APP.'_adm')===0; // detect if in admin pages

  // Start app page or blanko page
  if ( $appHeader ) {
    $oH = $this; include APP.($inAdm ? '_adm' : '').'_inc_hd.php'; // uses $hideMenuLang=true for error/exit pages
  } else {
    $this->links['css'] = '<link rel="stylesheet" type="text/css" href="'.($inAdm ? 'bin/css/admin.css' : QT_SKIN.APP.'_styles.css').'"/>';
    $this->head();
    $this->body();
  }

  // Page content
  self::msgBox($title, $msgboxAttr);
  if ( is_numeric($content) ) {
    if ( (int)$content===99 ) {
      $content = translate('app_offline.txt',false);
      if ( file_exists($content) ) { include $content; } else { echo L('E_99'); }
    } else {
      echo L('E_'.$content);
      if ( !SUser::auth() ) echo '<p><a href="'.APP.'_login.php">'.L('Login').'...</a></p>';
    }
  } else {
    echo $content;
  }
  self::msgBox('/');

  // End page
  if ( $appHeader ) {
    include APP.($inAdm ? '_adm' : '').'_inc_ft.php'; // uses $hideMenuLang=true for error/exit pages
  } else {
    $this->end();
  }
  exit;
}

}