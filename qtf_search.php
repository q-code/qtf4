<?php // v4.0 build:20230618

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';

// INITIALISE
$oH->selfurl = 'qtf_search.php';
$oH->selfname = L('Search');
$oH->exitname = L('Search');
if ( SUser::role()!=='A' && $_SESSION[QT]['board_offline'] ) exitPage(99,'tools.svg',false); //...
if ( !SUser::canAccess('search') ) exitPage(11,'user-lock.svg'); //...

// Check certificates
$certificate = makeFormCertificate('344cdd26d2c91e14d6fd27ab7e452a6f');
if ( isset($_POST['ok']) && $_POST['ok']===makeFormCertificate('a2038e83fd6618a444a5de51bf2313de') ) $_POST['ok']=$certificate; // certificates forwarding
if ( isset($_POST['ok']) && $_POST['ok']!==$certificate ) die('Unable to check certificate');

$q = ''; // query model
$v = ''; // keyword(s), tag(s), date1 or username
$v2 = ''; // timeframe, date2 or userid
$to = false; // title only
$s = -1; // [int]
$st = '*';
qtArgs('q v v2 boo:to int:s st qkw');
if ( $st==='' || $st==='-1' || !is_numeric($st) ) $st='*';

// --------
// SUBMITTED
// --------
if ( isset($_POST['ok']) && !empty($q) ) {

  $arg=''; // criterias (other than filters)
  switch($q) {
  case 'ref':
    if ( empty($v) ) $criteriaError = L('Ref').' '.L('invalid');
    // support direct open when #id is used as ref
    if ( $v[0]==="#" ) {
      $v = substr($v,1);
      if ( is_numeric($v) ) $oH->redirect('qtf_item.php?t='.$v);
    }
    $arg = '&v='.urlencode($v);
    break;
  case 'qkw':
  case 'kw':
    if ( empty($v) ) $criteriaError = L('Keywords').' '.L('invalid');
    $arg = '&v='.urlencode($v).'&to='.$to;
    break;
  case 'adv':
    if ( $v2==='*' && empty($v) ) $criteriaError = L('Date').' '.L('Date').' '.L('invalid');
    if ( $v===';' ) $criteriaError = 'Tag '.L('invalid');
    $arg = '&st='.$st.'&v2='.$v2.'&v='.urlencode($v);
    break;
  case 'user':
  case 'userm':
    if ( empty($v2) && !empty($v) ) $v2 = SUser::getUserId($oDB,$v); // return false if wrong name or empty post
    if ( empty($v2) ) $criteriaError = L('Username').' '.L('unknown');
    $arg = '&v='.urlencode($v).'&v2='.$v2;
    break;
  default: die('Unknown criteria '.$q);
  }
  // redirect
  if ( empty($criteriaError) ) {
    $oH->redirect('qtf_items.php?q='.$q.'&s='.$s.'&st='.$st.$arg);
    exit;
  } else {
    $_SESSION[QT.'splash'] = 'E|'.L('Search_criteria').' '.L('invalid');
  }
}

// --------
// HTML BEGIN
// --------

include APP.'_inc_hd.php';

// SEARCH SHORTCUTS
include APP.'_search_ui.php';

// SEARCH OPTIONS
echo '<h2>'.L('Search_option').'</h2>'.PHP_EOL;
echo '<section class="search-box options" id="broadcasted-options">'.PHP_EOL;
echo qtSVG('cog', 'id=opt-icon|class=filigrane'.($s<0 && $st==='*' ? '' : ' spinning'));
echo '<div>'.L('Section').' <select id="opt-s" name="s" size="1" autocomplete="off">'.sectionsAsOption($s,[],[],L('In_all_sections')).'</select></div>';
echo '<div>'.L('Status').'&nbsp;<select id="opt-st" name="st" size="1" autocomplete="off"><option value="*"'.($st==='*' ? ' selected' : '').'>'.L('Any_status').'</option>'.qtTags(CTopic::getStatuses(),$st).'</select></div>'.PHP_EOL;
echo '</section>'.PHP_EOL;

// SEARCH CRITERIA
echo '<h2>'.L('Search_criteria').'</h2>'.PHP_EOL;

// Error message
if ( !empty($criteriaError) ) echo '<p class="error">'.$criteriaError.'</p>';

// SEARCH BY KEY
echo '<form method="post" action="'.url($oH->selfurl).'" autocomplete="off">
<section class="search-box criteria">
'.qtSVG('search', 'class=filigrane').'
<div>'.L('Keywords').' <div id="ac-wrapper-kw" class="ac-wrapper"><input required type="text" id="kw" name="v" size="40" maxlength="64" value="'.($q=='kw' ? qtAttr($v,0,'&quot;') : '').'" data-multi="1"/></div>*</div>
<div><span class="cblabel"><input type="checkbox" id="to" name="to"'.($to ? ' checked' : '').' value="1"/> <label for="to">'.L('In_title_only').'</label></span></div>
<div style="flex-grow:1;text-align:right">
<input type="hidden" name="q" value="kw"/>
<input type="hidden" id="kw-s" name="s" value="'.$s.'"/>
<input type="hidden" id="kw-st" name="st" value="'.$st.'"/>
<button type="submit" name="ok" value="'.$certificate.'">'.L('Search').'</button>
</div>
</section>
</form>
';

// SEARCH BY REF
$refExists=false;
foreach($_Sections as $mSec)
{
  if ( $mSec['type']=='1' && !SUser::isStaff() ) continue;
  if ( $mSec['numfield']!=='N' ) { $refExists=true; break; }
}
if ( $refExists )
{
echo '<form method="post" action="'.url($oH->selfurl).'" autocomplete="off">
<section class="search-box criteria">
'.qtSVG('search', 'class=filigrane').'
<div>'.L('Ref').' <div id="ac-wrapper-ref" class="ac-wrapper"><input required type="text" id="ref" name="v" size="5" minlength="1" maxlength="10" value="'.($q=='ref' ? qtAttr($v,0,'&quot;') : '').'"/>&nbsp;'.L('H_Reference').'</div></div>
<div style="flex-grow:1;text-align:right">
<input type="hidden" name="q" value="ref"/>
<input type="hidden" id="ref-s" name="s" value="'.$s.'"/>
<input type="hidden" id="ref-st" name="st" value="'.$st.'"/>
<button type="submit" name="ok" value="'.$certificate.'">'.L('Search').'</button>
</div>
</section>
</form>
';
}

// SEARCH BY DATE & TAGS
echo '<form method="post" action="'.url($oH->selfurl).'" autocomplete="off">
<section class="search-box criteria">
'.qtSVG('search', 'class=filigrane').'
<div>'.L('Date').' <select id="tf" name="v2" size="1">
<option value="*"'.($v2==='*' || $v2==='' ? ' selected' : '').'>'.L('Any_time').'</option>
<option value="w"'.($v2==='w' ? ' selected' : '').'>&nbsp; '.L('This_week').'</option>
<option value="m"'.($v2==='m' ? ' selected' : '').'>&nbsp; '.L('This_month').'</option>
<option value="y"'.($v2==='y' ? ' selected' : '').'>&nbsp; '.L('This_year').'</option>
'.qtTags(L('dateMMM.*'),(int)$v2).'
</select><input type="hidden" id="y" name="y" value="'.date('Y').'"/>
</div>';
if ( $_SESSION[QT]['tags']!='0' ) echo '<div>'.L('With_tag').' <div id="ac-wrapper-tag-edit" class="ac-wrapper"><input type="text" id="tag-edit" name="v" size="30" value="'.($q==='adv' ? qtAttr($v) : '').'" data-multi="1"/></div>*</div>';
echo '<div style="flex-grow:1;text-align:right">
<input type="hidden" name="q" value="adv"/>
<input type="hidden" id="tag-s" name="s" value="'.$s.'"/>
<input type="hidden" id="tag-st" name="st" value="'.$st.'"/>
<button type="submit" name="ok" value="'.$certificate.'">'.L('Search').'</button>
</div>
</section>
</form>
';

// SEARCH NAME
echo '<form method="post" action="'.url($oH->selfurl).'" autocomplete="off">
<section class="search-box criteria">
'.qtSVG('search', 'class=filigrane').'
<div><select name="q" size="1">'.qtTags( ['user'=>L('Item').' '.L('author'),'userm'=>L('Item').'/'.L('reply').' '.L('author')], $q ).'
</select> <div id="ac-wrapper-user" class="ac-wrapper"><input type="hidden" id="userid" type="text" name="v2" value="'.$v2.'"/><input required id="user" type="text" name="v" value="'.(empty($v) || substr($q,0,4)!=='user' ? '' : qtAttr($v,0,'&quot;')).'" size="32" maxlenght="64"/></div></div>
<div style="flex-grow:1;text-align:right">
<input type="hidden" id="user-s" name="s" value="'.$s.'"/>
<button type="submit" name="ok" value="'.$certificate.'">'.L('Search').'</button>
</div>
</section>
</form>
';

echo '* <small>'.sprintf(L('Multiple_input'),QSEPARATOR).'</small>';

// HTML END

$oH->scripts['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js"></script>
<script type="text/javascript" src="bin/js/qtf_config_ac.js"></script>';

include APP.'_inc_ft.php';