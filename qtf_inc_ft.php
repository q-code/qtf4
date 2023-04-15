<?php // v4.0 build:20230205

/**
 * @var string $strDetailLegend
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 * @var CSection $oS
 * @var int $s
 */

// END MAIN CONTENT
echo '
</div>
';

// time and moderator
$arr = [];
if ( !empty($_SESSION[QT]['show_time_zone']) ) {
  $arr[0] = gmdate($_SESSION[QT]['formattime'], time() + 3600*($_SESSION[QT]['time_zone']));
  if ( $_SESSION[QT]['show_time_zone']==='2' ) {
    $arr[0] .= ' (gmt';
    if ( $_SESSION[QT]['time_zone']>0 ) $arr[0] .= '+'.$_SESSION[QT]['time_zone'];
    if ( $_SESSION[QT]['time_zone']<0 ) $arr[0] .= $_SESSION[QT]['time_zone'];
    $arr[0] .= ')';
  }
}
// no moderator in case of index page and search results page (where $s=-1)
if ( QT_SHOW_MODERATOR && isset($oS) && is_a($oS,'CSection') && $oS->id>=0 ) {
  if ( !empty($oS->ownerid) && !empty($oS->ownername) ) $arr[1] = L('Role_C').': <a href="'.Href('qtf_user.php?id='.$oS->ownerid).'">'.$oS->ownername.'</a>';
}
echo '<div id="main-ft">
<p>'.implode(' &middot; ',$arr).'</p>
<p>';
if ( QT_SHOW_JUMPTO ) {
  echo '<select id="jumpto" size="1" onchange="window.location=this.value;" accesskey="j">';
  echo '<option disabled selected hidden>'.L('Goto').'...</option>';
  if ( $oH->selfurl=='qtf_search.php' ) echo '<option value="'.Href('qtf_index.php').'">'.SLang::translate().'</option>';
  if ( $oH->selfurl!='qtf_search.php' && SUser::canView('V4') ) echo '<option value="'.Href('qtf_search.php').'">'.L('Advanced_search').'</option>';
  echo sectionsAsOption('',[],[],'',32,100,Href('qtf_items.php').'?s='); // current section is not rejected (allow returning to page 1 or top page)
  echo '</select>';
}
echo '</p>
</div>
';

// END MAIN
echo '
</main>
';

// --------
// ASIDE INFO & LEGEND
// --------

if ( !$_SESSION[QT]['board_offline'] ) {
if ( $_SESSION[QT]['show_legend']==='1' ) {
if ( in_array($oH->selfurl,array('index.php','qtf_index.php','qtf_items.php','qtf_calendar.php','qtf_item.php')) ) {

// Using stats ($_SectionsStats)
$stats = isset($_SectionsStats) ? $_SectionsStats : SMem::get('_SectionsStats');
$strStatusText = '';

echo '<aside>'.PHP_EOL;
echo '<a id="aside-ctrl" class="tgl-ctrl" href="javascript:void(0)" onclick="toggleAside(); return false;" title="'.L('Showhide_legend').'" aria-current="false">'.getSVG('info').getSVG('angle-down','','',true).getSVG('angle-up','','',true).'</a>'.PHP_EOL;
echo '<div id="aside__info" class="article" style="display:none">'.PHP_EOL;
  echo '<h1>'.L('Information').'</h1>'.PHP_EOL;
  // section info
  echo '<p>';
  if ( isset($oS) && is_a($oS,'CSection') && $oS->id>=0 )
  {
    $strStatusText = SLang::translate('sec', 's'.$s, $oS->title).': ';
    echo $strStatusText.'<br>';
    echo '&nbsp; '.L('item',$stats[$s]['items']).', '.L('reply',$stats[$s]['replies']).'<br>';
    if ( !$_SESSION[QT]['show_closed'] ) {
      $intTopicsZ = isset($stats[$s]['itemsZ']) ? $stats[$s]['itemsZ'] : $oDB->count( CSection::sqlCountItems($s,'items','1') );
      echo '&nbsp; '.L('closed_item',$intTopicsZ).'<br>';
    }
    $strStatusText .= L('item',$stats[$s]['items']).(empty($intTopicsZ) ? '' : ' ('.L('closed_item',$intTopicsZ).')').', '.L('reply',$stats[$s]['replies']);
  }
  echo '</p>';
  // application info
  echo '<p>';
  echo L('Total').' '.SLang::translate().':<br>';
  if ( isset($stats['all']) ) echo '&nbsp; ',L('item', $stats['all']['items']),', ',L('reply',$stats['all']['replies']);
  if ( empty($strStatusText) ) $strStatusText = L('Total').' '.SLang::translate().': '.L('item',$stats['all']['items']).', '.L('reply',$stats['all']['replies']);
  echo '</p>';
  // new user info (from memcache)
  $newuser = SMem::get('_NewUser',false,0); // no default, no expire, regenerated if missing
  if ( $newuser!==false && !empty($newuser['id']) && !empty($newuser['firstdate']) && !empty($newuser['name']) ) {
    if ( addDate($newuser['firstdate'],30,'day')>Date('Ymd') ) {
      echo '<p>'.L('Welcome_to').'<a href="'.Href('qtf_user.php?id='.$newuser['id']).'">'.$newuser['name'].'</a></p>';
    }
  }
echo '</div>'.PHP_EOL;

echo '<div id="aside__detail" class="secondary" style="display:none">'.PHP_EOL;
if ( isset($strDetailLegend) ) echo '<h1>'.L('Details').'</h1>'.PHP_EOL.$strDetailLegend.PHP_EOL;
echo '</div>'.PHP_EOL;

echo '<div id="aside__legend" style="display:none">'.PHP_EOL;
echo '<h1>'.L('Legend').'</h1>'.PHP_EOL;
echo '<p>';
if ( $oH->selfurl==='qtf_index.php' )
{
  echo asImg( QT_SKIN.'img/section_0_0.gif', 'title='.L('Ico_section_0_0') ) . ' ' . L('Ico_section_0_0') . '<br>';
  echo asImg( QT_SKIN.'img/section_2_0.gif', 'title='.L('Ico_section_2_0') ) . ' ' . L('Ico_section_2_0') . '<br>';
  echo asImg( QT_SKIN.'img/section_0_1.gif', 'title='.L('Ico_section_0_1') ) . ' ' . L('Ico_section_0_1') . '<br>';
}
else
{
  echo asImg( QT_SKIN.'img/topic_a_0.gif', 'alt=N|class=i-item' ) . ' '.L('Ico_item_a_0');
  if ( QT_LIST_ME && $oH->selfurl!=='qtf_item.php' ) echo ' &nbsp;<svg class="svg-symbol symbol-ireplied"><use href="#symbol-ireplied" xlink:href="#symbol-ireplied"></use></svg>'.' '.L('You_reply');
  echo '<br>';
  echo asImg( QT_SKIN.'img/topic_t_0.gif', 'alt=T|class=i-item' ) . ' '.L('Ico_item_t_0').' &nbsp;';
  echo asImg( QT_SKIN.'img/topic_t_0_h.gif', 'alt=T|class=i-item' ) . ' '.L('Ico_item_t_0_h');
  echo '<br>';
  echo asImg( QT_SKIN.'img/topic_t_1.gif', 'alt=T|class=i-item' ) . ' '.L('Ico_item_t_1').' ';
  echo '<br>';
  if ( $oH->selfurl==='qtf_item.php' ) echo getSVG('comment-dots').' '.L('Ico_post_r').'<br>';
}
echo '</p></div>'.PHP_EOL;
echo '<div id="aside__status">'.$strStatusText.'</div>'.PHP_EOL;
echo '</aside>'.PHP_EOL.PHP_EOL;

$oH->scripts[] = 'function toggleAside(){
  const d = document.getElementById("aside-ctrl"); if ( !d ) return;
  d.setAttribute("aria-current", d.getAttribute("aria-current")==="false" ? "true" : "false" );
  qtToggle("aside__status");
  qtToggle("aside__legend");
  qtToggle("aside__detail");
  qtToggle("aside__info",null,"aside-ctrl");
  qtAttrStorage("aside-ctrl","qt-aside");
}
qtApplyStoredState("aside");';

}}}

// END PAGE SITE
echo '
</div>
';

// --------
// FOOTER
// --------

echo '<footer class="flex-sp">
';

// MODULE RSS
if ( !$_SESSION[QT]['board_offline'] && useModule('rss') && $_SESSION[QT]['m_rss']=='1' ) {
if ( SUser::role()!=='V' || SUser::role().substr($_SESSION[QT]['m_rss_conf'],0,1)=='VV' ) {
  $arrMenus['rss'] = 'text='.getSVG('rss-square').'|id=menu-rss|href=qtfm_rss.php';
}}

// footer menu extra definition
if ( SUser::role()==='A' ) {
  $arrMenus['admin'] = '['.L('Administration').']|id=menu-admin|href=qtf_adm_index.php|accesskey=a';
}

$m = new CMenu(array_intersect_key($arrMenus, array_flip(['home','privacy','stats','rss','sign','admin'])), ' &middot; ');

echo '<p id="footer-menu">'.$m->build($oH->selfurl, 'tag=span|onclick=return false').'</p>'.PHP_EOL;
echo '<p id="footer-credit">powered by <a href="http://www.qt-cute.org">QT-cute</a> <span title="'.VERSION.' '.BUILD.'">v'.VERSION.'</span></p>
</footer>
';

// --------
// HTML END
// --------

if ( isset($oDB->stats) )
{
  $end = (float)vsprintf('%d.%06d', gettimeofday());
  $str = '';
  if ( isset($oDB->stats['num']) ) $str .= $oDB->stats['num'].' query. ';
  if ( isset($oDB->stats['rows']) ) $str .= $oDB->stats['rows'].' rows fetched. ';
  if ( isset($oDB->stats['start']) ) $str .= 'End queries in '.round($end-$oDB->stats['start'],4).' sec. ';
  if ( isset($oDB->stats['pagestart']) ) $str .= 'End page in '.round($end-$oDB->stats['pagestart'],4).' sec. ';
  $oH->log[] = $str;
}

// Automatic add script {file.php.js} if existing
if ( file_exists($oH->selfurl.'.js') ) $oH->scripts[] = '<script type="text/javascript" src="'.$oH->selfurl.'.js"></script>';

$oH->end();