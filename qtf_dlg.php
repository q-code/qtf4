<?php // v4.0 build:20230205

// Actions GET['a'] are (with access rights)
// [staff] itemsType: change type (A|T) or status (opened|closed)
// [staff] itemsTags: add remove tags
// [staff] itemsMove: move to a section
// [staff] itemsDelete: delete items
// [owner] itemDelete: delete 1 item
// [owner] replyDelete: delete 1 reply

session_start();
/**
* @var CHtml $oH
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
require 'bin/init.php';
if ( SUser::role()==='V' ) die('Access denied'); // minimum access rights

$a = empty($_GET['a']) ? '' : $_GET['a']; if ( !empty($_POST['a']) ) $a = $_POST['a']; // a come as get or post
if ( empty($a) ) die('Missing argument'); // a is mandatory
$s = isset($_GET['s']) ? (int)$_GET['s'] : -1; if ( isset($_POST['s']) ) $s = (int)$_POST['s'];
$parentUri = isset($_POST['uri']) ? $_POST['uri'] : 's='.$s;

// ids [array-of-int] from GET, POST, or Checkboxes
$ids = array();
if ( isset($_GET['ids']) ) $ids = array_map( 'intval', explode(',',$_GET['ids']) );
if ( isset($_POST['ids']) ) $ids = array_map( 'intval', explode(',',$_POST['ids']) );
if ( isset($_POST['t1-cb']) ) $ids = getCheckedIds('t1-cb');
$strIds = implode(',',$ids);

$oH->selfname = L('Item+');
$oH->selfurl = APP.'_dlg.php';
$oH->selfuri = $oH->selfurl.'?a='.$a; // when confirmed, ids must be in POST
$oH->exiturl = APP.'_items.php?'.$parentUri;
$oH->exitname = L('Exit');
$frm_title = 'Multiple edit';
$frm_hd = '';
$frm = array();
$frm_ft = '';

function renderItems(array $ids, bool $tags=false, bool $replies=false, bool $attach=false, bool $typeIcon=true)
{
  $topIds = array_slice($ids,0,5);
  // process ids [array of int]
  $str = '';
  global $oDB;
  $oDB->query( "SELECT p.title,p.attach,t.status,t.type,t.firstpostname,t.firstpostdate,t.tags,t.replies FROM TABTOPIC t INNER JOIN TABPOST p ON t.firstpostid=p.id WHERE t.id IN (".implode(',',$topIds).")" );
  while( $row=$oDB->getRow() ){
    $oT = new CTopic($row);
    $str .= '<p class="list ellipsis">';
    if ( $typeIcon ) $str .= $oT->getIcon(QT_SKIN).' ';
    $str .= QTquoted(QTtrunc($oT->title,30), '&"');
    if ( $replies && $oT->items ) $str .= ' '.getSVG('comments', 'title='.L('reply',$oT->items));
    if ( $attach && !empty($oT->attachinfo) ) $str .= ' '.getSVG('paperclip', 'title='.L('Attachment'));
    if ( $tags ) $str .= ' '.$oT->getTagIcon();
    $str .= ' <span class="minor">'.L('by').' '.QTtrunc($oT->firstpostname,20).' ('.QTdatestr($oT->firstpostdate,'j M').')</span>';
    $str .= '</p>';
  }
  return $str.(count($ids)>5 ? '<p>...</p>' : '');
}
function renderReply(int $id, string $parentType='T', string $parentStatus='1')
{
  global $oDB;
  $oDB->query( "SELECT * FROM TABPOST WHERE id=$id" );
  while( $row=$oDB->getRow() ) {
    $str = '<p class="indent" class="list ellipsis">'.CPost::getIconType($row['type'],$parentType,$parentStatus,QT_SKIN);
    $str .= ' "'.QTtrunc($row['textmsg'],100).'"';
    $str .= ' <span class="minor">'.L('by').' '.QTtrunc($row['username'],20).' ('.strtolower(QTdatestr($row['issuedate'],'j M')).')</span></p>';
  }
  return $str;
}
function ListTags(array $ids, bool $sort=true, bool $format=true, int $max=32)
{
  $arr = array();
  global $oDB;
  $oDB->query( "SELECT tags FROM TABTOPIC WHERE id IN (".implode(',',$ids).")" );
  while( $row=$oDB->getRow() ) {
    if ( count($arr)>$max ) break;
    if ( !empty($row['tags']) ) foreach(explode(';',$row['tags']) as $tag) if ( !in_array($tag,$arr) ) $arr[]=$tag;
  }
  if ( count($arr)==0 ) return array('('.L('none').')');
  if ( $sort ) sort($arr);
  if ( count($arr)>$max ) { $arr = array_slice($arr,0,$max-1); $arr[]='...'; }
  if ( $format ) foreach($arr as $k=>$str) $arr[$k]='<span class="tag clickable" onclick="tagClick(this.innerHTML)" data-tagdesc="'.qtAttr($str).'">'.$str.'</span>';
  return $arr;
}

// PROCESS $a
switch($a) {

case 'itemsType':

  // ACCESS RIGHTS
  if ( !SUser::isStaff() ) die('Access denied');

  // SUBMITTED
  if ( isset($_POST['ok']) )
  {
    // update status
    if ( isset($_POST['status']) && $_POST['status']!=='U' ) $oDB->exec( "UPDATE TABTOPIC SET status=?,statusdate=? WHERE id IN ($strIds)", [$_POST['status'],date('Ymd His')] );
    // update type
    if ( isset($_POST['type']) && $_POST['type']!=='U' ) $oDB->exec( "UPDATE TABTOPIC SET type=? WHERE id IN ($strIds)", [$_POST['type']] );
    memFlush(); memFlushStats(); // clear cache
    $_SESSION[QT.'splash'] = L('S_update');
    $oH->redirect($oH->exiturl);
  }

  // FORM (default type/status is U=unchanged)
  $frm_title = L('Change').' '.L('type').'/'.L('status');
  $frm[] = '<form method="post" action="'.Href($oH->selfuri).'" onsubmit="return validateForm(this)">';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Item+').':</p>';
  $frm[] = renderItems($ids,false,true);
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Type').' <select name="type" size="1">
  <option value="U" selected disabled hidden>('.L('unchanged').')</option>
  <option value="T">'.L('Topic').'</option>
  <option value="A">'.L('News').'</option>
  </select> '.L('Status').' <select name="status" size="1">
  <option value="U" selected disabled hidden>('.L('unchanged').')</option>
  <option value="0">'.L('Opened').'</option>
  <option value="1">'.L('Closed').'</option>
  </select></p>';
  $frm[] = '</article>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=\''.Href($oH->exiturl).'\';">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Ok').' ('.count($ids).')</button></p>';
  $frm[] = '<input type="hidden" name="ids" value="'.$strIds.'"/>';
  $frm[] = '<input type="hidden" name="uri" value="'.$parentUri.'"/>';
  $frm[] = '</form>';
  $oH->scripts[] = 'function validateForm(f) {
    if (f.elements[0].value=="U" && f.elements[1].value=="U") { alert("'.L('Nothing_selected').'"); return false; }
    document.body.style.cursor = "wait";
    return true;
  }';

  break; //=======

case 'itemsTags':

  // ACCESS RIGHTS
  if ( !SUser::isStaff() ) die('Access denied');

  // SUBMITTED
  if ( isset($_POST['tag-btn']) && !empty($_POST['tag-edit']) )
  {
    // update status
    foreach($ids as $id) {
      $oT = new CTopic($id);
      if ( $_POST['tag-btn']==='addtag' ) $oT->tagsAdd($_POST['tag-edit']);
      if ( $_POST['tag-btn']==='deltag' ) $oT->tagsDel($_POST['tag-edit']);
    }
    // exit
    $_SESSION[QT.'splash'] = L('S_update');
  }

  // FORM (default type/status is U=unchanged)
  $frm_title = L('Change').' '.L('tags');
  $frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Item+').':</p>';
  $frm[] = renderItems($ids,true);
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Used_tags').':</p>';
  $frm[] = '<p>'.implode('',ListTags($ids)).'</p>';
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Change').' '.L('item',count($ids)).':</p>
  <div id="ac-wrapper-tag-edit" class="ac-wrapper">
  <div style="display:flex;align-items:center">
  <input type="hidden" id="tag-dir" value="'.QT_DIR_DOC.'"/>
  <input type="hidden" id="tag-lang" value="'.QT_LANG.'"/>
  <input required type="text" id="tag-edit" name="tag-edit" size="15" maxlength="255" placeholder="'.L('Tags').'..." title="'.L('Edit_tags').'" data-multi="1" autocomplete="off"/>
  <button class="tag-btn" title="'.L('Reset').'" onclick="qtFocusAfter(`tag-edit`,true); return false;">'.getSVG('backspace').'</button>&nbsp;
  <button type="submit" name="tag-btn" class="tag-btn" value="addtag" title="'.L('Add').'">'.getSVG('plus').'</button>
  <button type="submit" name="tag-btn" class="tag-btn" value="deltag" title="'.L('Delete_tags').'">'.getSVG('minus').'</button>
  </div>
  </div>';
  $frm[] = '</article>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=\''.Href($oH->exiturl).'\';">'.L('Cancel').'</button></p>';
  $frm[] = '<input type="hidden" name="ids" value="'.$strIds.'"/><input type="hidden" name="uri" value="'.$parentUri.'"/>';
  $frm[] = '</form>';
  $oH->scripts['tagdesc'] = '<script type="text/javascript" src="bin/js/qt_tagdesc.js" id="tagdesc" data-dir="'.QT_DIR_DOC.'" data-lang="'.QT_LANG.'"></script>';
  $oH->scripts['tags'] = '<script type="text/javascript" src="bin/js/qt_tags.js"></script>';
  $oH->scripts['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js"></script>
  <script type="text/javascript" src="bin/js/qtf_config_ac.js"></script>';

  break; //=======

case 'itemsMove':

  // ACCESS RIGHTS
  if ( !SUser::isStaff() ) die('Access denied');

  // SUBMITTED
  if ( isset($_POST['ok']) && isset($_POST['destination']) && $_POST['destination']!=='' )
  {
    CSection::moveItems($ids, (int)$_POST['destination'], (int)$_POST['ref'], isset($_POST['dropprefix']) ? true : false);
    // exit
    $_SESSION[QT.'splash'] = L('S_update');
    $oH->redirect($oH->exiturl);
  }

  // FORM (default type/status is U=unchanged)
  $frm_title = L('Move').' '.L('item+');
  $frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Item+').':</p>';
  $frm[] = renderItems($ids,false,true,true);
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Destination').' <select name="destination" size="1" required>
  <option value="" disabled selected hidden></option>
  '.sectionsAsOption().'
  </select></p>';
  $frm[] = '</article>';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Options').':</p>';
  $frm[] = '<p>'.L('Ref').': <select name="ref" size="1">
  <option value="1">'.L('Move_keep').'</option>
  <option value="0">'.L('Move_reset').'</option>
  <option value="2">'.L('Move_follow').'</option>
  </select></p>';
  $frm[] = '<p><span class="cblabel"><input type="checkbox" id="dropprefix" name="dropprefix" checked/> <label for="dropprefix">'.L('Remove').' '.L('item').' '.L('prefix').'</label></span></p>';
  $frm[] = '</article>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=\''.Href($oH->exiturl).'\';">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Ok').' ('.count($ids).')</button></p>';
  $frm[] = '<input type="hidden" name="ids" value="'.$strIds.'"/><input type="hidden" name="uri" value="'.$parentUri.'"/>';
  $frm[] = '</form>';

  break; //=======

case 'itemDelete':
case 'itemsDelete':

  // ACCESS RIGHTS (staff or owner), for multiple edit, only staff
  if ( !SUser::isStaff() ) {
    if ( $a==='itemsDelete' ) die('Access denied');
    if ( SUser::id()!==CTopic::getOwner($ids[0]) ) die('Access denied');
  }

  // SUBMITTED
  if ( isset($_POST['ok']) ) try {

    if ( isset($_POST['deleteT']) ) {
      if ( count($ids)===0 ) throw new Exception( L('Delete').' '.L('item+').': 0 '.L('found') );
      CTopic::delete($ids,true);
      memFlush();memFlushStats(); // clear cache
    } elseif ( isset($_POST['deleteR']) ) {
      if ( count($ids)===0 || $oDB->count( "TABPOST WHERE type<>'P' AND topic IN ($strIds)" )===0 ) throw new Exception( L('Delete').' '.L('replies').': 0 '.L('found') );
      CTopic::deleteReplies($ids,true);
      memFlush();memFlushStats(); // clear cache
    } elseif ( isset($_POST['dropattach']) ) {
      if ( count($ids)===0 || $oDB->count( "TABPOST WHERE attach<>'' AND topic IN ($strIds)" )===0 ) throw new Exception( L('Drop_attachments').': 0 '.L('found') );
      CPost::dropAttachs($ids,true,true); // use a list of topics
    } else {
      throw new Exception( L('Nothing_selected') );
    }
    $_SESSION[QT.'splash'] = L('S_delete');
    $oH->redirect($oH->exiturl);

  } catch (Exception $e) {

    $oH->error = $e->getMessage();

  }

  // FORM (default type/status is U=unchanged)
  $frm_title = L('Delete');
  $frm[] = '<form method="post" action="'.Href($oH->selfuri).'" onsubmit="return validateForm()">';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Item+').':</p>';
  $frm[] = renderItems($ids,false,true,true);
  $frm[] = '</article>';
  $frm[] = '<p class="row-confirm">'.L('Confirm').':</p>';
  $frm[] = '<p class="cblabel"><input type="checkbox" id="deleteT" name="deleteT"/> <label for="deleteT">'.L('Delete').' '.L('item+').'</label></p>';
  $frm[] = '<p class="cblabel"><input type="checkbox" id="deleteR" name="deleteR"/> <label for="deleteR">'.L('Delete').' '.L('reply+').'</label></p>';
  $frm[] = '<p class="cblabel"><input type="checkbox" id="deleteA" name="dropattach"/> <label for="deleteA">'.L('Drop_attachments').'<span class="small" id="attachoption"></span></label></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=\''.Href($oH->exiturl).'\';">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Ok').' (<span id="submit-sum">...</span>)</button></p>';
  $frm[] = '<input type="hidden" id="ids" name="ids" value="'.$strIds.'"/>';
  $frm[] = '<input type="hidden" name="uri" value="'.$parentUri.'"/>';
  $frm[] = '</form>';
  $oH->scripts[] = 'const deleteT = document.getElementById("deleteT");
const deleteR = document.getElementById("deleteR");
const deleteA = document.getElementById("deleteA");
const optionA = document.getElementById("attachoption");
deleteT.addEventListener("change", () => {
  submitSum();
  optionA.innerHTML = "";
  if ( deleteT.checked ) {
    deleteR.checked = true;
    deleteR.disabled = true;
    deleteA.checked = true;
    deleteA.disabled = true;
    updateCounts("T");
  } else {
    deleteR.checked = false;
    deleteR.disabled = false;
    deleteA.checked = false;
    deleteA.disabled = false;
  }
});
deleteR.addEventListener("change", () => {
  submitSum();
  if ( deleteR.checked ) {
    deleteA.checked = true;
    deleteA.disabled = true;
    optionA.innerHTML = " ('.L('reply+').' '.L('only').')";
    updateCounts("R");
  } else {
    deleteA.checked = false;
    deleteA.disabled = false;
    optionA.innerHTML = "";
  }
});
deleteA.addEventListener("change", () => {
  submitSum();
  if ( deleteA.checked ) updateCounts("attach");
});
function validateForm() {
  if ( deleteT.checked || deleteR.checked || deleteA.checked ) return true;
  alert("'.L('Nothing_selected').'");
  return false;
}
function unConfirm() {
  deleteT.checked=false;
  deleteR.checked=false;
  deleteR.disabled=false;
  deleteA.checked=false;
  deleteA.disabled=false;
  optionA.innerHTML = "";
  document.getElementById("submit-sum").innerHTML = "...";
}
function updateCounts(q) {
  fetch( `bin/srv_count.php?q=${q}&ids='.$strIds.'` )
  .then( response => response.json() )
  .then( data => { submitSum(data); } )
  .catch( err => console.log(err) );
}
function submitSum(n="...") { document.getElementById("submit-sum").innerHTML = n; }';

  break; //=======

case 'replyDelete':

  $t = isset($_GET['t']) ? (int)$_GET['t'] : -1; if ( isset($_POST['t']) ) $t = (int)$_POST['t'];
  $p = isset($_GET['p']) ? (int)$_GET['p'] : -1; if ( isset($_POST['p']) ) $p = (int)$_POST['p'];
  if ( $t<0 || $p<0 ) die('replyDelete: missing argument');
  $oH->exiturl = APP.'_item.php?t='.$t;

  // ACCESS RIGHTS (user can be staff or post creator)
  if ( !SUser::isStaff() && SUser::id()!==CPost::getOwner($p) ) die('Access denied');

  // SUBMITTED
  if ( isset($_POST['ok']) )
  {
    // delete only reply posts
    if ( isset($_POST['deletereply']) )
    {
      CPost::delete($p);
      // find the new topic lastpost and count posts
      $voidTopic = new CTopic();
      $voidTopic->id = $t;
      $voidTopic->updMetadata((int)$_SESSION[QT]['posts_per_item']);
    }
    memFlush();memFlushStats(); // clear cache
    $_SESSION[QT.'splash'] = L('S_delete');
    $oH->redirect($oH->exiturl);
  }

  // FORM (default type/status is U=unchanged)
  $frm_title = L('Delete');
  $frm[] = '<form method="post" action="'.Href($oH->selfuri).'">';
  $frm[] = '<article>';
  $frm[] = '<p>'.L('Reply').':</p>';
  $frm[] = renderReply($p);
  $frm[] = '</article>';
  $frm[] = '<p class="row-confirm">'.L('Confirm').':</p>';
  $frm[] = '<p><span class="cblabel"><input required type="checkbox" id="deletereply" name="deletereply"/> <label for="deletereply">'.L('Delete').' '.L('reply').'</label></span></p>';
  $frm[] = '<p class="submit right"><button type="button" name="cancel" value="cancel" onclick="window.location=\''.Href($oH->exiturl).'\';">'.L('Cancel').'</button> <button type="submit" name="ok" value="ok">'.L('Ok').'</button></p>';
  $frm[] = '<input type="hidden" name="t" value="'.$t.'"/>';
  $frm[] = '<input type="hidden" name="p" value="'.$p.'"/>';
  $frm[] = '<input type="hidden" name="uri" value="'.$parentUri.'"/>';
  $frm[] = '</form>';

  break; //=======

default: die('Unknown command '.$a);

}

// DISPLAY PAGE
const HIDE_MENU_TOC=true;
const HIDE_MENU_LANG=true;
include APP.'_inc_hd.php';

if ( !empty($frm_hd) ) echo $frm_hd.PHP_EOL;

CHtml::msgBox($frm_title);
echo implode(PHP_EOL,$frm);
CHtml::msgBox('/');

if ( !empty($frm_ft) ) echo $frm_ft.PHP_EOL;

include APP.'_inc_ft.php';