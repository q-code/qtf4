<?php // v4.0 build:20240210

session_start();
/**
* @var CHtml $oH
* @var CDatabase $oDB
* @var string $formAddUser
*/
require 'bin/init.php';

if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');
include translate('lg_reg.php');

// ------
// INITIALISE
// ------
$intUsers = $oDB->count( TABUSER.' WHERE id>0' ); // Count all users
$oH->name = L('Users').' ('.$intUsers.')';
$parentname = L('Board_content');
$oH->exiturl = 'qtf_adm_users.php';
$oH->exitname = '&laquo; '.L('Users');
$pn  = 1;
$cat = 'all'; // filter category
$fg = 'all'; // filter group lettres
$po = 'name';
$pd = 'asc';
$sqlStart = 0;
$intChecked = -1; // allow checking an id (-1 means no check)
qtArgs('int+:pn char2:cat fg po char4:pd', true, false);
// items per page
$ipp = isset($_COOKIE[QT.'_admusersipp']) ? $_COOKIE[QT.'_admusersipp'] : 25;
if ( isset($_GET['ipp']) && in_array($_GET['ipp'],['25','50','100']) ) {
  $ipp = $_GET['ipp'];
  if ( PHP_VERSION_ID<70300 ) { setcookie(QT.'_admusersipp', $ipp, time()+3600*24*100, '/'); } else { setcookie(QT.'_admusersipp', $ipp, ['expires'=>time()+3600*24*100,'path'=>'/','samesite'=>'Strict']); }
}
$sqlStart = ($pn-1)*25;

// Defines FORM $formAddUser and handles POST
include APP.'_inc_adduser.php';

// Prepare to check the last created user
if ( isset($_GET['cid']) )  $intChecked = (int)strip_tags($_GET['cid']); // allow checking an id. Note checklast overridres this id
if ( isset($_POST['cid']) ) $intChecked = (int)strip_tags($_POST['cid']);
if ( isset($_POST['checklast']) || isset($_GET['checklast']) ) $intChecked = $oDB->count( "SELECT max(id) as countid FROM TABUSER" ); // Find last id. This overrides the cid value !

// ------
// HTML BEGIN
// ------
include 'qtf_adm_inc_hd.php';

// Global statistics
$intFalse = $oDB->count( TABUSER." WHERE id>1 AND firstdate=lastdate" ); // users without post
$str = addDate(date('Ymd His'),-1,'year');
$intSleeping = $oDB->count( TABUSER." WHERE id>1 AND lastdate<'$str'" ); // users sleeping 1 year
// Coppa
if ( $_SESSION[QT]['register_coppa']=='1' ) {
  $intChild = $oDB->count( TABUSER." WHERE id>1 AND children<>'0'" ); // children
  $intSleepchild = $oDB->count( TABUSER." WHERE id>1 AND children='2'" ); // without agreement only
}

$icon = [' ', qtSvg('chevron-circle-right'), qtSvg('circle', 'class=disabled')];
echo '<div id="users-metadata">
<div id="users-filter">
';
echo '<table class="t-item">
<tr>
<th class="c-info">'.L('Users').'</th>
<td class="c-info">&nbsp;</td>
<td class="bold">'.$intUsers.'</td>
<td>'.($cat==='all' ? $icon[2] : '<a href="qtf_adm_users.php" title="'.L('Show').'">'.$icon[1].'</a>').'</td>
</tr>
';
echo '<tr>
<th class="c-info">'.L('Members_FM').'</th>
<td class="c-info">'.L('H_Members_FM').'</td>
<td class="bold">'.$intFalse.'</td>
<td>'.($intFalse===0 ? $icon[0] : ($cat==='FM' ? $icon[2] : '<a href="qtf_adm_users.php?cat=FM" title="'.L('Show').'">'.$icon[1].'</a>')).'</td>
</tr>
';
echo '<tr>
<th class="c-info">'.L('Members_SM').'</th>
<td class="c-info">'.L('H_Members_SM').'</td>
<td class="bold">'.$intSleeping.'</td>
<td>'.($intSleeping===0 ? $icon[0] : ($cat==='SM' ? $icon[2] : '<a href="qtf_adm_users.php?cat=SM" title="'.L('Show').'">'.$icon[1].'</a>')).'</td>
</tr>
';
if ( $_SESSION[QT]['register_coppa']=='1' ) {
echo '<tr>
<th class="c-info">'.L('Members_CH').'</th>
<td class="c-info">'.L('H_Members_CH').'</td>
<td class="bold">'.$intChild.'</td>
<td>'.($intChild===0 ? $icon[0] : ($cat==='CH' ? $icon[2] : '<a href="qtf_adm_users.php?cat=CH" title="'.L('Show').'">'.$icon[1].'</a>')).'</td>
</tr>
';
echo '<tr>
<th class="c-info">'.L('Members_SC').'</th>
<td class="c-info">'.L('H_Members_SC').'</td>
<td class="bold">'.$intSleepchild.'</td>
<td>'.($intSleepchild===0 ? $icon[0] : ($cat==='SC' ? $icon[2] : '<a href="qtf_adm_users.php?cat=SC" title="'.L('Show').'">'.$icon[1].'</a>')).'</td>
</tr>
';
}
echo '</table>
';

echo '</div>
<div id="participants" class="strongbox">
<p class="title">'.L('Top_participants').'</p>
<table>
';
// Top 5 participants
$strState = 'name, id, numpost FROM TABUSER WHERE id>0';
$oDB->query( sqlLimit($strState,'numpost DESC',0,5) );
while($row = $oDB->getRow()) {
  echo '<tr><td><a href="'.url('qtf_user.php').'?id='.$row['id'].'">'.$row['name'].'</a></td><td class="right">'.qtK((int)$row['numpost']).'</td></tr>';
}
echo '</table>';

echo '</div>
</div>
';

// Add user(s) form
echo '<p style="margin:12px 0">'.($cat=='all' ? '' : '<a href="qtf_adm_users.php">'.qtSvg('chevron-left').L('Show').' '.L('all').'</a> | ');
if ( !empty($formAddUser) )
echo '<a id="tgl-ctrl" href="javascript:void(0)" class="tgl-ctrl" onclick="qtToggle();qtToggle(`svg`,`nodisplay toggle`,this);">'.L('User_add').qtSvg('angle-down', isset($_POST['title']) ? 'class=nodisplay' : '').qtSvg('angle-up', isset($_POST['title']) ? '' : 'class=nodisplay').'</a> | ';
echo '<a href="qtf_adm_users_imp.php">'.L('Users_import_csv').'...</a> | <a href="qtf_adm_users_exp.php">'.L('Users_export_csv').'...</a></p>';
if ( !empty($formAddUser) ) echo $formAddUser;

// ------
// Category subform
// ------
if ( $cat!='all' ) {
  $intCount = $intFalse;
  if ( $cat=='CH' ) $intCount = $intChild;
  if ( $cat=='SM' ) $intCount = $intSleeping;
  if ( $cat=='SC' ) $intCount = $intSleepchild;
  echo '<br><h1 class="title">'.L('Members_'.$cat).' <span style="font-size:11pt">('.$intCount.' '.L('h_Members_'.$cat).')<span></h1>'.PHP_EOL;
}

// Query by lettre (or input field)
$arrGroup = array_filter(explode('|',$fg)); // filter to remove empty
if ( count($arrGroup)===1 ) {
  switch((string)$fg) {
    case 'all': $sqlWhere = ''; break;
    case '~':   $sqlWhere = ' AND '.sqlFirstChar('name','~'); break;
    default:    $sqlWhere = ' AND '.sqlFirstChar('name','u',strlen($fg)).'="'.strtoupper($fg).'"'; break;
  }
} else {
  $arr = [];
  foreach($arrGroup as $str) $arr[] = sqlFirstChar('name','u').'="'.strtoupper($str).'"';
  $sqlWhere = ' AND ('.implode(' OR ',$arr).')';
}

// Query by category
if ( $cat=='FM' ) $sqlWhere .= ' AND firstdate=lastdate'; //false members
if ( $cat=='SM' ) $sqlWhere .= ' AND lastdate<"'.addDate(date('Ymd His'),-1,'year').'"'; //sleeping members
if ( $cat=='CH' ) $sqlWhere .= ' AND children<>"0"'; //children
if ( $cat=='SC' ) $sqlWhere .= ' AND children="2"'; //sleeping children

// Count query
$intCount = $oDB->count( TABUSER.' WHERE id>0 '.$sqlWhere );

// Lettres bar
if ( $intCount>$ipp || $fg!=='all' )
echo htmlLettres($oH->php.qtURI('fg|pn'), $fg, L('All'), L('Username_starting').' ', $intCount>300 ? 1 : ($intCount>$ipp*2 ? 2 : 3));

// End if no result
if ( $intCount==0 ) {
  echo L('None');
  include 'qtf_adm_inc_ft.php';
  exit;
}

// Build paging
$paging = makePager("qtf_adm_users.php?cat=$cat&fg=$fg&po=$po&pd=$pd",$intCount,$ipp,$pn);
if ( !empty($paging) ) $paging = L('Page').$paging;
if ( $intCount<$intUsers ) $paging = '<small>'.L('user',$intCount).' '.L('from').' '.$intUsers.'</small>'.(empty($paging) ? '' : ' | '.$paging);

// ------
// Memberlist
// ------
$m = new CMenu([
L('role').'|class=cmd-cb|data-action=usersrole',
L('delete').'|class=cmd-cb|data-action=usersdel',
strtolower(L('Ban')).'|class=cmd-cb|data-action=usersban',
L('picture').'|class=cmd-cb|data-action=userspic'
], ' &middot; ');
$rowCommands = L('selection').': '.$m->build();

echo PHP_EOL.'<form id="form-items" method="post" action="'.APP.'_adm_register.php"><input type="hidden" id="form-items-action" name="a" />'.PHP_EOL;
echo '<div class="table-ui top">';
echo '<div id="t1-edits-top" class="cmds-cb" data-table="t1">'.qtSvg('corner-up-right','class=arrow-icon').$rowCommands.'</div>';
echo '<div class="right">'.$paging.'</div></div>'.PHP_EOL;

// Table definition
$t = new TabTable('id=t1|class=t-item table-cb|data-content=users',$intCount);
$t->activecol = $po;
$t->activelink = '<a href="'.$oH->php.'?cat='.$cat.'&fg='.$fg.'&po='.$po.'&pd='.($pd=='asc' ? 'desc' : 'asc').'">%s</a>&nbsp;'.qtSvg('caret-'.($pd==='asc' ? 'up' : 'down'));
// TH
$t->arrTh['checkbox'] = new TabHead($t->countDataRows<2 ? '&nbsp;' : '<input type="checkbox" data-target="t1-cb[]"/>', 'class=c-checkbox');
$t->arrTh['name'] = new TabHead(L('User'), 'class=c-name', '<a href="'.$oH->php.'?cat='.$cat.'&fg='.$fg.'&po=name&pd=asc">%s</a>');
$t->arrTh['pic'] = new TabHead(qtSvg('camera'), 'class=c-pic|title='.L('Picture'));
$t->arrTh['role'] = new TabHead(L('Role'), 'class=c-role ellipsis', '<a href="'.$oH->php.'?cat='.$cat.'&fg='.$fg.'&po=role&pd=asc">%s</a>');
$t->arrTh['numpost'] = new TabHead(qtSvg('comments'), 'class=c-numpost|title='.L('Messages'), '<a href="'.$oH->php.'?cat='.$cat.'&fg='.$fg.'&po=numpost&pd=desc">%s</a>');
if ( $cat=='FM' || $cat=='SC' ) {
$t->arrTh['firstdate'] = new TabHead(L('Joined'), 'class=c-joined ellipsis', '<a href="'.$oH->php.'?cat='.$cat.'&fg='.$fg.'&po=firstdate&pd=desc">%s</a>');
} else {
$t->arrTh['lastdate'] = new TabHead(L('Last_message').' (ip)', 'class=c-lastdate ellipsis', '<a href="'.$oH->php.'?cat='.$cat.'&fg='.$fg.'&po=lastdate&pd=desc">%s</a>');
}
$t->arrTh['closed'] = new TabHead(qtSvg('ban'), 'class=c-ban', '<a href="'.$oH->php.'?cat='.$cat.'&fg='.$fg.'&po=closed&pd=desc" title="'.L('Banned').'">%s</a>');
$t->arrTh['id'] = new TabHead('Id', 'class=c-id', '<a href="'.$oH->php.'?cat='.$cat.'&fg='.$fg.'&po=id&pd=asc">%s</a>');
// TD
$t->cloneThTd();

// === TABLE START DISPLAY ===

echo PHP_EOL;
echo $t->start().PHP_EOL;
echo '<thead>'.PHP_EOL;
echo $t->getTHrow().PHP_EOL;
echo '</thead>'.PHP_EOL;
echo '<tbody>'.PHP_EOL;

//-- LIMIT QUERY --
$strState = 'id,name,closed,role,numpost,firstdate,lastdate,ip,picture FROM TABUSER WHERE id>0'.$sqlWhere;
$oDB->query( sqlLimit($strState,$po.' '.strtoupper($pd).($po==='name' ? '' : ',name asc'),$sqlStart,$ipp) );
// ------
$arrRow=array(); // rendered row. To remove duplicate in seach result
$intRow=0; // count row displayed
$days = BAN_DAYS;
while($row=$oDB->getRow())
{
  if ( in_array((int)$row['id'], $arrRow) ) continue; // this remove duplicate users in case of search result

  $arrRow[] = (int)$row['id'];
  if ( empty($row['name']) ) $row['name']='('.L('unknown').')';
  $bChecked = $row['id']==$intChecked;

  $intLock = (int)$row['closed']; if ( !array_key_exists($intLock,BAN_DAYS) ) $intLock=0;
  $strLock = $intLock ? '<span class="ban" title="'.L('Banned').' '.L('day',$days[$intLock]).'">'.$days[$intLock].'<span>' : L('n');

  // prepare row
  $t->arrTd['checkbox']->content = '<input type="checkbox" name="t1-cb[]" id="t1-cb-'.$row['id'].'" value="'.$row['id'].'"'.($bChecked ? ' checked' : '').' data-row="'.$intRow.'"/>'; if ( $row['id']<2) $t->arrTd['checkbox']->content = '&nbsp;';
  $t->arrTd['name']->content = '<a href="'.url('qtf_user.php').'?id='.$row['id'].'" title="'.qtAttr($row['name'],24).'">'.qtTrunc($row['name'],24).'</a>';
  $img = SUser::getPicture((int)$row['id'], 'data-magnify=0|onclick=this.dataset.magnify=this.dataset.magnify==1?0:1;', '');
  $t->arrTd['pic']->content = empty($img) ? '' : '<p class="magnifier center">'.$img.'</p>';
  $t->arrTd['role']->content = L('Role_'.strtoupper($row['role']));
  $t->arrTd['numpost']->content = qtK((int)$row['numpost']);
  if ( $cat=='FM' || $cat=='SC' ) {
  $t->arrTd['firstdate']->content = empty($row['firstdate']) ? '' : qtDate($row['firstdate'],'$','',true);
  } else {
  $t->arrTd['lastdate']->content = (empty($row['lastdate']) ? '' : qtDate($row['lastdate'],'$','',true)) . (empty($row['ip']) ? '' : '<br><small>('.$row['ip'].')</small>');
  }
  $t->arrTd['closed']->content = $strLock;
  $t->arrTd['id']->content = $row['id'];

  echo $t->getTDrow('id=t1-tr-'.$row['id'].'|class=t-item hover rowlight');
  ++$intRow; if ( $intRow>=$ipp ) break;

}

// === TABLE END DISPLAY ===

echo '</tbody>'.PHP_EOL;
echo '</table>'.PHP_EOL;
echo '<div class="table-ui bot">';
echo $rowCommands ? '<div id="t1-edits-bot" class="cmds-cb" data-table="t1">'.qtSvg('corner-down-right','class=arrow-icon').$rowCommands.'</div>' : '<div></div>';
echo '<div class="right">'.$paging.'</div></div>'.PHP_EOL;
echo '</form>'.PHP_EOL;

// Extra command
if ( $cat!=='all' ) {
  echo '<p class="submit"><a href="'.APP.'_adm_register.php?a=catdel&cat='.$cat.'&n='.$intCount.'">'.L('Delete').' '.L('members_'.$cat).'...</a></p>';
}

// Extra user preference ipp
$m = new CMenu(['25|id=u25|href='.$oH->php.'?ipp=25', '50|id=u50|href='.$oH->php.'?ipp=50', '100|id=u100|href='.$oH->php.'?ipp=100']);
echo '<p class="right" style="padding:0.3rem 0">'.L('Show').': '.$m->build('u'.$ipp, 'default|style=color:#444;text-decoration:underline').' / '.L('page').'</p>';

// HTML END

$oH->scripts[] = '<script type="text/javascript" src="bin/js/qt_table_cb.js" data-noselect="'.L('Nothing_selected').'..."></script>';
$oH->scripts[] = 'qtHideAfterTable(".table-ui.bot");';

include 'qtf_adm_inc_ft.php';