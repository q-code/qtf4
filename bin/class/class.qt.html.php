<?php // v4.0 build:20240210

/**
 * CHtml class allows managing html metadata
 * @author qt-cute.org
 */

class CHtml
{

public $html = '<html>';  // can be use to include xml attributes
public $metas = [];       // can contains <title> as $metas[0]
public $links = [];
public $scripts_top = []; // scripts in the <head>
public $scripts = [];     // scripts in the <body>
public $scripts_end = []; // if scripts require to be after other scripts
public $symbols = [];     // list of svg <symbol> (not displayed) can be served with <use>
public $log = [];         // Attention if not empty, is VISIBLE at the bottom of the page
public $php = '';         // script name (without path)
public $arg = '';         // url arguments (with '?' if not empty)
public $name = '';
public $exitname = 'Back';
public $exiturl = APP.'_index.php';
public $items = 0;        // number of items in the page, visible (can be in several pages)
public $itemsHidden = 0;  // number of items in the page, hidden (by users preferences)
public $error = '';
public $warning = '';

public function __construct()
{
  $this->php = substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'],'/')+1);
  if ( !empty($_SERVER['QUERY_STRING']) ) $this->arg = '?'.$_SERVER['QUERY_STRING'];
}
public function head()
{
  //push cssContrast as last link
  if ( !empty($this->links['cssContrast']) ) {
    $arr = array_keys($this->links);
    if ( end($arr)!=='cssContrast' ) { $this->links[] = $this->links['cssContrast']; unset($this->links['cssContrast']); }
  }
  // check/add <script> enclosing tag
  self::formatScripts($this->scripts_top);
  // build links
  echo $this->html.PHP_EOL.'<head>'.PHP_EOL.implode(PHP_EOL,$this->metas).PHP_EOL.implode(PHP_EOL,$this->links).PHP_EOL.implode(PHP_EOL,$this->scripts_top).PHP_EOL.'</head>'.PHP_EOL;
}
public function body(string $attr='')
{
  echo '<body'.attrRender($attr).'>'.PHP_EOL;
}
public function end(bool $allowSplash=true)
{
  $log = empty($this->log) ? '' : '<p id="pagelog">'.implode('<br>',$this->log).(SUser::auth() ? '<br><small>Sign out to stop debugging</small>' : '').'</p>'.PHP_EOL;
  $this->log = []; // clear log
  // check/add <script> enclosing tag
  if ( $this->scripts ) self::formatScripts($this->scripts);
  if ( $this->scripts_end ) self::formatScripts($this->scripts_end);
  // output
  echo $log.PHP_EOL;
  if ( $this->symbols )
  echo '<svg xmlns="http://www.w3.org/2000/svg" style="display:none">'.PHP_EOL.implode(PHP_EOL,$this->symbols).PHP_EOL.'</svg>'.PHP_EOL;
  if ( $this->scripts )
  echo implode(PHP_EOL,$this->scripts).PHP_EOL;
  if ( $this->scripts_end )
  echo implode(PHP_EOL,$this->scripts_end).PHP_EOL;
  if ( $allowSplash && !empty($_SESSION[QT.'splash']) )
  echo Splash::getSplash().PHP_EOL;
  echo '</body>'.PHP_EOL.'</html>';
}
/** Add enclosing script if missing */
private static function formatScripts(array &$codes) {
  foreach($codes as $k=>$code) {
    if ( substr($code,0,8)==='<script ' ) continue;
    $codes[$k] = '<script type="text/javascript">'.$code.'</script>';
  }
}
/** Open/close a div (or other) tag with (optionally) an html-comment before/after */
public static function pageEntity(string $attr='', string $com='', string $entity='div')
{
  if ( $attr==='/' ) return '</'.$entity.'>'.PHP_EOL.($com ? '<!-- end '.$com.' -->'.PHP_EOL : '');
  return ($com ? PHP_EOL.'<!-- start '.$com.' -->' : '').PHP_EOL.'<'.$entity.''.attrRender($attr).'>'.PHP_EOL;
}
/**
 * Redirect
 * @param string $dest 'self', 'exit' or url
 * @param string $s
 */
public function redirect(string $dest='exit', string $s='Continue')
{
  if ( $dest==='self' ) $dest = $this->php.($this->arg ?: '');
  if ( $dest==='exit' ) $dest = $this->exiturl;
  $dest = url($dest); // can use urlrewrite
  if ( headers_sent() ) {
    echo '<a href="'.$dest.'">'.$s.'</a><meta http-equiv="REFRESH" content="0;url='.$dest.'">';
  } else {
    header('Location: '.str_replace('&amp;','&',$dest));
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
  if ( empty($title) ) $title = $this->name;
  if ( empty($this->exiturl) ) $this->exiturl = APP.'_index.php';
  if ( substr($title,-4)==='.svg' ) $title = qtSVG(substr($title,0,-4)); // title can be a svg
  $inAdm = strpos($this->php, APP.'_adm')===0; // detect if in admin pages

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