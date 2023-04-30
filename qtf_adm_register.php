<?php // v4.0 build:20230430

// Actions GET['a'] are
// role:     change users role (access admin only)
// ban:      change ban (access admin only)
// delete:   delete users (access admin only)

session_start();
/**
* @var CHtml $oH
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';
if ( SUser::role()!=='A' ) die('Access denied');

$a = empty($_GET['a']) ? '' : $_GET['a']; if ( !empty($_POST['a']) ) $a = $_POST['a']; // a come as get or post
if ( empty($a) ) die('Missing argument');
$cat = empty($_GET['cat']) ? '' : $_GET['cat']; if ( !empty($_POST['cat']) ) $cat = $_POST['cat']; // category² come as get or post
if ( !empty($_GET['cat']) ) $cat = substr($_GET['cat'],0,2); // in case working by category
if ( !empty($_POST['cat']) ) $cat = substr($_POST['cat'],0,2);
// ids [array-of-int] from GET, POST, or Checkboxes
$ids = array();
if ( isset($_GET['ids']) ) $ids = array_map( 'intval', explode(',',$_GET['ids']) );
if ( isset($_POST['ids']) ) $ids = array_map( 'intval', explode(',',$_POST['ids']) );
if ( isset($_POST['t1-cb']) ) $ids = getCheckedIds('t1-cb');

$oH->links['css'] = '<link rel="stylesheet" type="text/css" href="bin/css/'.APP.'_styles.css"/>';
include translate('lg_adm.php');
include translate('lg_reg.php');
$oH->selfname = L('Users');
$oH->selfparent = L('Board_content');
$oH->selfurl = APP.'_adm_register.php';
$oH->selfuri = $oH->selfurl.'?a='.$a; // when confirmed, ids must be in POST
$oH->exiturl = APP.'_adm_users.php';
$oH->exitname = L('Exit');
$frm_title = 'Multiple edit';
$frm_hd = '';
$frm = array();
$frm_ft = '';

function renderUsers(array $ids, array $fields=['name','role','closed'])
{
  // process ids [array of int]
  $str = '';
  $days = BAN_DAYS;
  $arrUsers = getUsersInfo( array_slice($ids,0,5), implode(',',$fields) );
  if ( count($arrUsers)===5 ) { array_pop($arrUsers); $last='<p>...</p>'; } else { $last=''; }
  foreach($arrUsers as $row) {
    $str .= '<p class="list"><span class="magnifier center">'.SUser::getPicture((int)$row['id'], 'data-magnify=0|onclick=this.dataset.magnify=this.dataset.magnify==1?0:1;', 'bin/css/user.gif').'</span> '.qtTrunc($row['name'],24);
    $str .= empty($row['closed']) ? '' : ' '.getSVG( 'ban', 'title='.L('Banned').' '.L('day',$days[(int)$row['closed']]) );
    $str .= ' ('.L('Role_'.$row['role']).')</p>';
  }
  return $str.$last;
}

switch($a) {
case 'usersrole':

  if ( empty($ids) || in_array(0,$ids,true) || in_array(1,$ids,true) ) die('Invalid argument'); // [strict] 0 and 1 cannot be delete/upgraded/ban

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['role']) && $_POST['role']!=='' )
  {
    $role = strtoupper($_POST['role']);
    $list = implode(',',$ids);
    // update role
    $oDB->exec( "UPDATE TABUSER SET role='$role' WHERE id IN ($list)" );
    // change section coordinator if required
    if ( $role==='U' ) {
      $oDB->exec( "UPDATE TABSECTION SET moderator=1, moderatorname='Admin' WHERE moderator IN ($list)" );
      SMem::clear('_Sections');
    }
    // exit
    $_SESSION[QT.'splash'] = L('S_update');
    $oH->redirect($oH->exiturl);
  }

  // FORM
  $frm_title = L('Change_role');
  $frm[] = '<form method="post" action="'.$oH->selfuri.'">';
  $frm[] = '<p>'.L('Users').':</p>';
  $frm[] = renderUsers($ids).'<br>';
  $frm[] = '<p>'.L('Role').' <select required name="role" size="1">
  <option value="" disabled selected hidden></option>
  <option value="U">'.L('Role_U').'</option>
  <option value="M">'.L('Role_M').'</option>
  <option value="A"'.(SUser::role()!=='A' ? 'disabled' :'').'>'.L('Role_A').'</option>
  </select></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Ok').' ('.count($ids).')</button></p>';
  $frm[] = '<input type="hidden" name="ids" value="'.implode(',',$ids).'"/>';
  $frm[] = '</form>';
  break; // =====

case 'usersdel':

  if ( empty($ids) || in_array(0,$ids,true) || in_array(1,$ids,true) ) die('Invalid argument'); // [strict] 0 and 1 cannot be delete/upgraded/ban

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['confirm']) )
  {
    // Delete
    foreach($ids as $id) SUser::delete($oDB,$id);
    // Exit
    $_SESSION[QT.'splash'] = L('S_delete');
    $oH->redirect($oH->exiturl);
  }

  // FORM
  $frm_title = L('Delete').' '.L('users');
  $frm[] = '<form method="post" action="'.$oH->selfuri.'">';
  $frm[] = '<p>'.L('Users').':</p>';
  $frm[] = renderUsers($ids).'<br>';
  $frm[] = '<p class="row-confirm"><input required type="checkbox" id="confirm" name="confirm"/> <label for="confirm">'.L('Confirm').': '.L('Delete').' '.L('member',count($ids)).'<label></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Delete').' ('.count($ids).')</button></p>';
  $frm[] = '<input type="hidden" name="ids" value="'.implode(',',$ids).'"/>';
  $frm[] = '</form>';
  break; // =====

case 'catdel':

  if ( empty($cat) ) die('Invalid argument');

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['confirm']) )
  {
    // Delete
    $where = ' id>1 AND ';
    switch($cat)
    {
    case 'CH': $where .= 'children="2"'; break;
    case 'FM': $where .= 'firstdate=lastdate'; break;
    case 'SC': $where .= 'children="2"'; break;
    case 'SM':
      switch($oDB->type)
      {
      case 'pdo.mysql':
      case 'mysql':
      case 'pdo.sqlsrv':
      case 'sqlsrv': $where .= 'LEFT(lastdate,8)<'.addDate(date('Ymd'),-1,'year'); break;
      case 'pdo.pg':
      case 'pg': $where .= 'SUBSTRING(lastdate FROM 1 FOR 8)<'.addDate(date('Ymd'),-1,'year'); break;
      case 'pdo.sqlite':
      case 'sqlite':
      case 'pdo.oci':
      case 'oci': $where .= 'SUBSTR(lastdate,1,8)<'.addDate(date('Ymd'),-1,'year'); break;
      default: die('Unknown db type '.$oDB->type);
      }
      break;
    }
    $b = $oDB->exec( 'DELETE FROM TABUSER WHERE '.$where );
    // Exit
    $_SESSION[QT.'splash'] = $b ? L('S_update') : 'E|'.L('E_failed');
    $oH->redirect($oH->exiturl);
  }

  // FORM
  $frm_title = L('Delete').' '.L('users');
  $str = isset($_GET['n']) ? $_GET['n'] : '!';
  $frm[] = '<form method="post" action="'.$oH->selfuri.'">';
  $frm[] = '<p><input required type="checkbox" id="confirm" name="confirm"/> <label for="confirm">'.L('Confirm').': '.L('Delete').' '.$str.' '.L('members_'.$cat).'<label></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> &nbsp; <button type="submit" name="ok" value="delete">'.L('Delete').' ('.$str.')</button></p>';
  $frm[] = '<input type="hidden" name="cat" value="'.$cat.'"/>';
  $frm[] = '</form>';
  break; // =====

case 'usersban':

  if ( SUser::role()!=='A' ) die('Access denied');
  if ( empty($ids) || in_array(0,$ids,true) || in_array(1,$ids,true) ) die('Invalid argument'); // [strict] 0 and 1 cannot be delete/upgraded/ban

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['ban']) && $_POST['ban']!=='' ) try {

    $ban = substr($_POST['ban'],0,1);
    $list = implode(',',$ids);
    // check if admins
    if ( $oDB->count( TABUSER." WHERE role='A' AND id IN ($list)" )>0 ) throw new Exception( L('Admin_protected') );
    // ban
    $oDB->exec( "UPDATE TABUSER SET closed='$ban' WHERE id IN ($list)" );
    // exit
    $_SESSION[QT.'splash'] = L('S_update');
    $oH->redirect($oH->exiturl);

  } catch (Exception $e) {

    $oH->error = $e->getMessage();

  }

  // FORM
  $frm_title = L('Ban');
  $frm[] = '<form method="post" action="'.$oH->selfuri.'">';
  $frm[] = '<p>'.L('Users').':</p>';
  $frm[] = renderUsers($ids).'<br>';
  $frm[] = '<p>'.L('H_ban').':</p>';
  $frm[] = '<p><select required name="ban" size="1"><option value="" disabled selected hidden></option>';
  foreach(BAN_DAYS as $k=>$days) $frm[] = '<option value="'.$k.'">'.L('day',$days).'</option>';
  $frm[] = '</select></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Ok').' ('.count($ids).')</button></p>';
  $frm[] = '<input type="hidden" name="ids" value="'.implode(',',$ids).'"/>';
  $frm[] = '</form>';
  break; // =====

case 'userspic':

  if ( SUser::role()!=='A' ) die('Access denied');
  if ( empty($ids) || in_array(0,$ids,true) || in_array(1,$ids,true) ) die('Invalid argument'); // [strict] 0 and 1 cannot be delete/upgraded/ban

  // SUBMITTED
  if ( isset($_POST['ok']) ) try {

    // check if admins
    if ( $oDB->count( TABUSER." WHERE role='A' AND id IN (".implode(',',$ids).")" )>0 ) throw new Exception( L('Admin_protected') );
    // delete user's pic
    SUser::deletePicture($ids);
    // exit
    $_SESSION[QT.'splash'] = L('S_delete');
    $oH->redirect($oH->exiturl);

  } catch (Exception $e) {

    $oH->error = $e->getMessage();

  }

  // FORM
  $frm_title = L('Pictures');
  $frm[] = '<form method="post" action="'.$oH->selfuri.'">';
  $frm[] = '<p>'.L('Users').':</p>';
  $frm[] = renderUsers($ids).'<br>';
  $frm[] = '<p class="row-confirm"><input required type="checkbox" id="confirm" name="confirm"/> <label for="confirm">'.L('Confirm').': '.L('Delete').' '.L('picture',count($ids)).'<label></p>';  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Delete').' ('.count($ids).')</button></p>';
  $frm[] = '<input type="hidden" name="ids" value="'.implode(',',$ids).'"/>';
  $frm[] = '</form>';
  break; // =====

default: die('Unknown command');

}

// DISPLAY PAGE

const HIDE_MENU_TOC=true;
const HIDE_MENU_LANG=true;
include APP.'_adm_inc_hd.php';

if ( !empty($frm_hd) ) echo $frm_hd.PHP_EOL;

CHtml::msgBox($frm_title);
echo implode(PHP_EOL,$frm);
CHtml::msgBox('/');

if ( !empty($frm_ft) ) echo $frm_ft.PHP_EOL;

include APP.'_adm_inc_ft.php';