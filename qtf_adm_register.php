<?php // v4.0 build:20240210 allows app impersonation

// Actions GET['a'] are
// role:     change users role (access admin only)
// ban:      change ban (access admin only)
// delete:   delete users (access admin only)

session_start();
/**
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
$ids = [];
if ( isset($_GET['ids']) ) $ids = array_map( 'intval', explode(',',$_GET['ids']) );
if ( isset($_POST['ids']) ) $ids = array_map( 'intval', explode(',',$_POST['ids']) );
if ( isset($_POST['t1-cb']) ) $ids = getPostedValues('t1-cb');

$oH->links['css'] = '<link rel="stylesheet" type="text/css" href="bin/css/admin.css"/>';
include translate('lg_adm.php');
include translate('lg_reg.php');
$oH->selfname = L('Users');
$oH->selfparent = L('Board_content');
$oH->selfurl = APP.'_adm_register.php';
$oH->exiturl = APP.'_adm_users.php';
$oH->exitname = L('Exit');
$frm_title = 'Multiple edit';
$frm_action = $oH->selfurl.'?a='.$a; // when confirmed, ids must be in POST
$frm_hd = '';
$frm = [];
$frm_ft = '';

function renderUsers(array $ids, array $fields=['name','role','closed']) {
  // process ids [array of int]
  $str = '';
  $days = BAN_DAYS;
  $arrUsers = getUsersInfo( array_slice($ids,0,5), implode(',',$fields) );
  if ( count($arrUsers)===5 ) { array_pop($arrUsers); $last='<p>...</p>'; } else { $last=''; }
  foreach($arrUsers as $row) {
    $str .= '<p class="list"><span class="magnifier center">'.SUser::getPicture((int)$row['id'], 'data-magnify=0|onclick=this.dataset.magnify=this.dataset.magnify==1?0:1;', 'bin/css/user.gif').'</span> '.qtTrunc($row['name'],24);
    $str .= empty($row['closed']) ? '' : ' '.qtSVG( 'ban', 'title='.L('Banned').' '.L('day',$days[(int)$row['closed']]) );
    $str .= ' ('.L('Role_'.$row['role']).')</p>';
  }
  return $str.$last;
}

switch($a) {
case 'usersrole':

  if ( empty($ids) || in_array(0,$ids,true) || in_array(1,$ids,true) ) die('Invalid argument'); // [strict] 0 and 1 cannot be delete/upgraded/ban

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['role']) && $_POST['role']!=='' ) try {
    $role = strtoupper($_POST['role']);
    $list = implode(',',$ids);
    $oDB->exec( "UPDATE TABUSER SET role='$role' WHERE id IN ($list)" );
    // change section coordinator if required
    if ( $role==='U' ) {
      $oDB->exec( "UPDATE TABSECTION SET moderator=1, moderatorname='Admin' WHERE moderator IN ($list)" );
      SMem::clear('_Sections');
    }
    $_SESSION[QT.'splash'] = L('S_update');
    $oH->redirect(); //█

  } catch (Exception $e) {

    $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
    $oH->error = $e->getMessage();

  }

  // FORM
  $frm_title = L('Change_role');
  $frm[] = '<form method="post" action="'.$frm_action.'">';
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
  break;

case 'usersdel':

  if ( empty($ids) || in_array(0,$ids,true) || in_array(1,$ids,true) ) die('Invalid argument'); // [strict] 0 and 1 cannot be delete/upgraded/ban

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['confirm']) ) try {
    foreach($ids as $id) SUser::delete($oDB,$id);
    $_SESSION[QT.'splash'] = L('S_delete');
    $oH->redirect(); //█

  } catch (Exception $e) {

    $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
    $oH->error = $e->getMessage();

  }

  // FORM
  $frm_title = L('Delete').' '.L('users');
  $frm[] = '<form method="post" action="'.$frm_action.'">';
  $frm[] = '<p>'.L('Users').':</p>';
  $frm[] = renderUsers($ids).'<br>';
  $frm[] = '<p class="row-confirm"><input required type="checkbox" id="confirm" name="confirm"/> <label for="confirm">'.L('Confirm').': '.L('Delete').' '.L('member',count($ids)).'<label></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Delete').' ('.count($ids).')</button></p>';
  $frm[] = '<input type="hidden" name="ids" value="'.implode(',',$ids).'"/>';
  $frm[] = '</form>';
  break;

case 'catdel':

  if ( empty($cat) ) die('Invalid argument');

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['confirm']) ) try {

    // Delete
    $where = ' id>1 AND ';
    switch($cat) {
    case 'CH': $where .= 'children="2"'; break;
    case 'FM': $where .= 'firstdate=lastdate'; break;
    case 'SC': $where .= 'children="2"'; break;
    case 'SM':
      switch($oDB->type) {
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
    $oDB->exec( 'DELETE FROM TABUSER WHERE '.$where );
    $_SESSION[QT.'splash'] = L('S_update');
    $oH->redirect(); //█

  } catch (Exception $e) {

    $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
    $oH->error = $e->getMessage();

  }

  // FORM
  $frm_title = L('Delete').' '.L('users');
  $str = isset($_GET['n']) ? $_GET['n'] : '!';
  $frm[] = '<form method="post" action="'.$frm_action.'">';
  $frm[] = '<p><input required type="checkbox" id="confirm" name="confirm"/> <label for="confirm">'.L('Confirm').': '.L('Delete').' '.$str.' '.L('members_'.$cat).'<label></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> &nbsp; <button type="submit" name="ok" value="delete">'.L('Delete').' ('.$str.')</button></p>';
  $frm[] = '<input type="hidden" name="cat" value="'.$cat.'"/>';
  $frm[] = '</form>';
  break;

case 'usersban':

  if ( SUser::role()!=='A' ) die('Access denied');
  if ( empty($ids) || in_array(0,$ids,true) || in_array(1,$ids,true) ) die('Invalid argument'); // [strict] 0 and 1 cannot be delete/upgraded/ban

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['ban']) && $_POST['ban']!=='' ) try {

    $sqlIds = implode(',',$ids);
    // check other admins
    if ( $oDB->count( TABUSER." WHERE role='A' AND id IN ($sqlIds)" )>0 ) throw new Exception( L('Admin_protected') );
    $oDB->exec( "UPDATE TABUSER SET closed='".substr($_POST['ban'],0,1)."' WHERE id IN ($sqlIds)" );
    $_SESSION[QT.'splash'] = L('S_update');
    $oH->redirect(); //█

  } catch (Exception $e) {

    $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
    $oH->error = $e->getMessage();

  }

  // FORM
  $frm_title = L('Ban');
  $frm[] = '<form method="post" action="'.$frm_action.'">';
  $frm[] = '<p>'.L('Users').':</p>';
  $frm[] = renderUsers($ids).'<br>';
  $frm[] = '<p>'.L('H_ban').':</p>';
  $frm[] = '<p><select required name="ban" size="1"><option value="" disabled selected hidden></option>';
  foreach(BAN_DAYS as $k=>$days) $frm[] = '<option value="'.$k.'">'.L('day',$days).'</option>';
  $frm[] = '</select></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Ok').' ('.count($ids).')</button></p>';
  $frm[] = '<input type="hidden" name="ids" value="'.implode(',',$ids).'"/>';
  $frm[] = '</form>';
  break;

case 'userspic':

  if ( SUser::role()!=='A' ) die('Access denied');
  if ( empty($ids) || in_array(0,$ids,true) || in_array(1,$ids,true) ) die('Invalid argument'); // [strict] 0 and 1 cannot be delete/upgraded/ban

  // SUBMITTED
  if ( isset($_POST['ok']) ) try {

    // check if admins
    if ( $oDB->count( TABUSER." WHERE role='A' AND id IN (".implode(',',$ids).")" )>0 ) throw new Exception( L('Admin_protected') );
    SUser::deletePicture($ids);
    $_SESSION[QT.'splash'] = L('S_delete');
    $oH->redirect(); //█

  } catch (Exception $e) {

    $_SESSION[QT.'splash'] = 'E|'.L('E_failed');
    $oH->error = $e->getMessage();

  }

  // FORM
  $frm_title = L('Pictures');
  $frm[] = '<form method="post" action="'.$frm_action.'">';
  $frm[] = '<p>'.L('Users').':</p>';
  $frm[] = renderUsers($ids).'<br>';
  $frm[] = '<p class="row-confirm"><input required type="checkbox" id="confirm" name="confirm"/> <label for="confirm">'.L('Confirm').': '.L('Delete').' '.L('picture',count($ids)).'<label></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=`'.$oH->exiturl.'`;">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Delete').' ('.count($ids).')</button></p>';
  $frm[] = '<input type="hidden" name="ids" value="'.implode(',',$ids).'"/>';
  $frm[] = '</form>';
  break;

default: die('Unknown command');
}

// DISPLAY PAGE

const HIDE_MENU_TOC = true;
const HIDE_MENU_LANG = true;
include APP.'_adm_inc_hd.php';

unset($oH->scripts['formsafe']);

if ( $frm_hd ) echo $frm_hd.PHP_EOL;
CHtml::msgBox($frm_title, 'class=msgbox|style=margin-bottom:2rem');
echo implode(PHP_EOL,$frm);
CHtml::msgBox('/');
if ( $frm_ft ) echo $frm_ft.PHP_EOL;

include APP.'_adm_inc_ft.php';