<?php // v4.0 build:20230205

/**
 * CHtml class allows managing html metadata
 * @author qt-cute.org
 */

class CHtml
{
  public $html = '<html>'; // can be use to include xml attributes
  public $title = '';
  public $metas = []; // List of meta declarations. Recommandation: Use the meta 'name' as array key to void double metas when adding a new meta
  public $links = [];
  public $scripts_top = [];
  public $scripts = [];
  public $log = []; // Attention if not empty, is VISIBLE at the bottom of the page.
  public $selfurl = APP.'_index.php'; // page filename
  public $selfname = '';
  public $selfparent = ''; // parent name
  public $selfversion= '';
  public $selfuri = '';
  public $exiturl = APP.'_index.php';
  public $exitname = 'Back';
  public $exituri = '';
  public $items = 0; // number of items in the page, visible (can be in several pages)
  public $itemsHidden = 0; // number of items in the page, hidden (by users preferences)
  public $error = '';
  public $warning = '';

  public function head()
  {
    //push cssContrast as last link
    if ( array_key_exists('cssContrast', $this->links) ) {
      $this->links[] = $this->links['cssContrast']; unset($this->links['cssContrast']);
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
    $this->log = array(); // clear log
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

  public static function getPage(string $attr='id=site')
  {
    if ( $attr==='/' ) {
      echo PHP_EOL.'</div><!-- end site -->'.PHP_EOL.PHP_EOL.'<div id="splash"><i id="splash-ico"></i><span id="splash-txt"></span></div>'.PHP_EOL;
      return;
    }
    echo PHP_EOL.'<div'.attrRender($attr).'>'.PHP_EOL.PHP_EOL;
  }

  /**
   * Redirect to the url $u, and rewrites $u if QT_URLREWRITE
   * @param string $u
   * @param string $s
   */
  public function redirect(string $u, string $s='Continue')
  {
    if ( empty($u) ) die(__METHOD__.' arg must be string');
    $u = Href($u);
    if ( headers_sent() ) {
      echo '<a href="'.$u.'">',$s,'</a><meta http-equiv="REFRESH" content="0;url='.$u.'">';
    } else {
      header('Location: '.str_replace('&amp;','&',$u));
    }
    exit;
  }
  public function backButton(string $class='button btn-back', string $symbol='chevron-left.svg')
  {
    if ( substr($symbol,-4)==='.svg' ) $symbol = file_get_contents('bin/css/svg/'.$symbol); // on failed returns false
    if ( empty($symbol) ) $symbol = '&lt;';
    return '<a class="'.$class.'" href="'.Href($this->exiturl).'">'.$symbol.'</a>';
  }
  public static function msgBox(string $title='', string $attr='class=msgbox', string $attrTitle='class=msgboxtitle', string $attrBody='class=msgboxbody')
  {
    // End msgbox
    if ( $title==='/' ) { echo '</div>'.PHP_EOL.'</div>'.PHP_EOL; return; }
    // Start msgbox
    echo '<div'.attrRender($attr).'>'.PHP_EOL;
    echo '<div'.attrRender($attrTitle).'>'.$title.'</div>'.PHP_EOL;
    echo '<div'.attrRender($attrBody).'>'.PHP_EOL;
  }
  public function pageMessage(string $title='', string $message='Access denied', string $skin='', int $second=0, string $root='', string $attr='')
  {
    if ( empty($title) ) $title = $this->selfname;
    if ( empty($skin) && defined(QT_SKIN) ) $skin = QT_SKIN.APP.'_styles.css';
    if ( empty($skin) || $skin==='admin' ) $skin = $root.'bin/css/'.APP.'_styles.css';
    $this->links['css'] = '<link rel="stylesheet" type="text/css" href="'.$skin.'"/>';
    $this->links['prev'] = '<link rel="prev" id="exiturl" href="'.Href($this->exiturl).'"/>';
    $this->head();
    $this->body($attr);
    self::getPage('id=site|class=pagemsg');
    // in case of error code
    if ( is_int($title) ) {
      if ( $title===99 ) {
        $file = $root.translate('app_offline.txt');
        if ( file_exists($file) ) $message = file_get_contents($file);
      } else {
        $message = L('E_'.$title);
      }
      $title = '!';
    }
    // display message box
    self::msgBox($title);
    echo $message;
    if ( !empty($this->exiturl) ) {
      echo '<p><a id="exiturl" href="'.Href($this->exiturl).'">'.$this->exitname.'</a></p>';
      if ( $second>0 ) echo '<script type="text/javascript">const d = document.getElementById("exiturl"); if ( d ) setTimeout(()=>{window.location=d.href;}, '.($second*1000).');</script>';
    }
    self::msgBox('/');
    self::getPage('/');
    $this->end();
    exit;
  }
}