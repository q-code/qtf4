<?php // v4.0 build:20240210

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';

// INITIALISE
$oH->name = L('Search');
$oH->exitname = L('Search');
if ( SUser::role()!=='A' && $_SESSION[QT]['board_offline'] ) $oH->voidPage('tools.svg',99,true,false); //█
if ( !SUser::canAccess('search') ) $oH->voidPage('user-lock.svg',11,true); //█

// Check certificates
$certificate = makeFormCertificate('344cdd26d2c91e14d6fd27ab7e452a6f');
if ( isset($_POST['ok']) && $_POST['ok']===makeFormCertificate('a2038e83fd6618a444a5de51bf2313de') ) $_POST['ok']=$certificate; // certificates forwarding
if ( isset($_POST['ok']) && $_POST['ok']!==$certificate ) die('Unable to check certificate');

$s = -1; // [int]
$q = ''; // query model {ref|qkw|kw|adv|user|userm}
$fv = ''; // keyword(s), tag(s), date1 or username
$fw = ''; // timeframe, date2 or userid
$fs = ''; // status {'0'|'1'}
$to = false; // title only
qtArgs('int:s char5:q fv fw char:fs qkw boo:to');

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) && !empty($q) ) try {

  $arg['s'] = $s<0 ? '' : $s;
  $arg['q'] = $q;
  switch($q) {
    case 'ref':
      if ( empty($fv) ) throw new Exception( L('Ref').' '.L('invalid') );
      // support direct open when #id is used as ref
      if ( $fv[0]==="#" ) {
        $fv = substr($fv,1);
        if ( is_numeric($fv) ) $oH->redirect( APP.'_item.php?t='.$fv );
      }
      $arg['fv'] = urlencode($fv);
      break;
    case 'qkw':
    case 'kw':
      if ( empty($fv) ) throw new Exception( L('Keywords').' '.L('invalid') );
      $arg['fv'] = urlencode($fv);
      $arg['to'] = $to;
      break;
    case 'adv':
      if ( $fw==='' && empty($fv) ) throw new Exception( L('Date').' '.L('Date').' '.L('invalid') );
      if ( $fv===';' ) throw new Exception( 'Tag '.L('invalid') );
      $arg['fv'] = urlencode($fv);
      $arg['fw'] = $fw;
      $arg['fs'] = $fs;
      break;
    case 'user':
    case 'userm':
      $fv = qtDb(trim($fv));
      if ( empty($fw) && !empty($fv) ) $fw = SUser::getUserId($oDB,$fv); // return false if wrong name or empty post
      if ( empty($fw) ) throw new Exception( L('Username').' '.L('unknown') );
      $arg['fv'] = urlencode($fv);
      $arg['fw'] = $fw;
      break;
    default: die( 'Unknown criteria '.$q );
  }
  // redirect
  $oH->redirect( APP.'_items.php?'.qtImplode($arg) );

} catch (Exception $e) {

  $oH->error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;

}

// ------
// HTML BEGIN
// ------
include APP.'_inc_hd.php';
include APP.'_search_ui.php'; // SEARCH SHORTCUTS

// SEARCH OPTIONS
echo '<h2>'.L('Search_option').'</h2>'.PHP_EOL;
echo '<section class="search-box options" id="broadcasted-options">'.PHP_EOL;
echo qtSvg('cog', 'id=opt-icon|class=filigrane'.($s<0 && $fs==='' ? '' : ' spinning'));
echo '<div>'.L('Section').' <select id="opt-s" name="s" size="1" autocomplete="off">'.sectionsAsOption($s,[],[],L('In_all_sections')).'</select></div>';
echo '<div>'.L('Status').'&nbsp;<select id="opt-fs" name="fs" size="1" autocomplete="off"><option value=""'.($fs==='' ? ' selected' : '').'>'.L('Any_status').'</option>'.qtTags(CTopic::getStatuses(),$fs).'</select></div>'.PHP_EOL;
echo '</section>'.PHP_EOL;

// SEARCH CRITERIA
echo '<h2>'.L('Search_criteria').'</h2>'.PHP_EOL;

// Error message
if ( !empty($criteriaError) ) echo '<p class="error">'.$criteriaError.'</p>';

// SEARCH BY KEY
echo '<form method="post" action="'.url($oH->php).'" autocomplete="off">
<section class="search-box criteria">
'.qtSvg('search', 'class=filigrane').'
<div>'.L('Keywords').' <div id="ac-wrapper-kw"><input required type="text" id="kw" name="fv" size="40" maxlength="64" value="'.($q=='kw' ? qtAttr($fv,0,'&quot;') : '').'" data-multi="1"/></div>*</div>
<div><span class="cblabel"><input type="checkbox" id="to" name="to"'.($to ? ' checked' : '').' value="1"/> <label for="to">'.L('In_title_only').'</label></span></div>
<div style="flex-grow:1;text-align:right">
<input type="hidden" name="q" value="kw"/>
<input type="hidden" id="kw-s" name="s" value="'.$s.'"/>
<input type="hidden" id="kw-fs" name="fs" value="'.$fs.'"/>
<button type="submit" name="ok" value="'.$certificate.'">'.L('Search').'</button>
</div>
</section>
</form>
';

// SEARCH BY REF
$refExists=false;
foreach($_Sections as $mSec) {
  if ( $mSec['type']=='1' && !SUser::isStaff() ) continue;
  if ( $mSec['numfield']!=='N' ) { $refExists=true; break; }
}
if ( $refExists ) {
echo '<form method="post" action="'.url($oH->php).'" autocomplete="off">
<section class="search-box criteria">
'.qtSvg('search', 'class=filigrane').'
<div>'.L('Ref').' <div id="ac-wrapper-ref"><input required type="text" id="ref" name="fv" size="5" minlength="1" maxlength="10" value="'.($q=='ref' ? qtAttr($fv,0,'&quot;') : '').'"/>&nbsp;'.L('H_Reference').'</div></div>
<div style="flex-grow:1;text-align:right">
<input type="hidden" name="q" value="ref"/>
<input type="hidden" id="ref-s" name="s" value="'.$s.'"/>
<input type="hidden" id="ref-fs" name="fs" value="'.$fs.'"/>
<button type="submit" name="ok" value="'.$certificate.'">'.L('Search').'</button>
</div>
</section>
</form>
';
}

// SEARCH BY DATE & TAGS
echo '<form method="post" action="'.url($oH->php).'" autocomplete="off">
<section class="search-box criteria">
'.qtSvg('search', 'class=filigrane').'
<div>'.L('Date').' <select id="tf" name="fw" size="1">
<option value=""'.($fw==='' ? ' selected' : '').'>'.L('Any_time').'</option>
<option value="w"'.($fw==='w' ? ' selected' : '').'>&nbsp; '.L('This_week').'</option>
<option value="m"'.($fw==='m' ? ' selected' : '').'>&nbsp; '.L('This_month').'</option>
<option value="y"'.($fw==='y' ? ' selected' : '').'>&nbsp; '.L('This_year').'</option>
'.qtTags(L('dateMMM.*'),(int)$fw).'
</select><input type="hidden" id="y" name="y" value="'.date('Y').'"/>
</div>';
if ( $_SESSION[QT]['tags']!='0' ) echo '<div>'.L('With_tag').' <div id="ac-wrapper-tag-edit"><input type="text" id="tag-edit" name="fv" size="30" value="'.($q==='adv' ? qtAttr($fv) : '').'" data-multi="1"/></div>*</div>';
echo '<div style="flex-grow:1;text-align:right">
<input type="hidden" name="q" value="adv"/>
<input type="hidden" name="s" value="'.$s.'" id="adv-s"/>
<input type="hidden" name="fs" value="'.$fs.'" id="adv-fs"/>
<button type="submit" name="ok" value="'.$certificate.'">'.L('Search').'</button>
</div>
</section>
</form>
';

// SEARCH NAME
echo '<form method="post" action="'.url($oH->php).'" autocomplete="off">
<section class="search-box criteria">
'.qtSvg('search', 'class=filigrane').'
<div><select name="q" size="1">'.qtTags( ['user'=>L('Item').' '.L('author'),'userm'=>L('Item').'/'.L('reply').' '.L('author')], $q ).'
</select> <div id="ac-wrapper-user"><input type="hidden" id="userid" type="text" name="fw" value="'.$fw.'"/><input required id="user" type="text" name="fv" value="'.(empty($fv) || substr($q,0,4)!=='user' ? '' : qtAttr($fv,0,'&quot;')).'" size="32" maxlenght="64"/></div></div>
<div style="flex-grow:1;text-align:right">
<input type="hidden" name="s" value="'.$s.'" id="user-s"/>
<button type="submit" name="ok" value="'.$certificate.'">'.L('Search').'</button>
</div>
</section>
</form>
';

echo '* <small>'.sprintf(L('Multiple_input'),QSEPARATOR).'</small>';

// HTML END

$oH->scripts_end['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js"></script><script type="text/javascript" src="bin/js/qtf_config_ac.js"></script>';

include APP.'_inc_ft.php';