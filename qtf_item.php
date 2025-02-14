<?php // v4.0 build:20240210

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';
if ( !SUser::canView('V3') ) $oH->voidPage('user-lock.svg',11,true); //█

// ------
// PRE-INITIALISE
// ------
$t = -1; qtArgs('int+:t!');
$oT = new CTopic($t); //provide userid to update stats, after access granted, does not increment views
$s = $oT->pid;
$oH->exiturl = 'qtf_items.php?s='.$s; // used in next voidPage()

// ------
// SUBMITTED
// ------
if ( isset($_POST['Maction']) ) {

  $oH->exitname = L('Section');
  if ( empty($_POST['Maction']) ) $oH->redirect( 'qtf_item.php'.'?t='.$t ); //█
  if ( substr($_POST['Maction'],0,7)==='status_' ) $oT->setStatus(substr($_POST['Maction'],-1,1));
  if ( substr($_POST['Maction'],0,5)==='type_' ) $oT->setType(substr($_POST['Maction'],-1,1));
  if ( $_POST['Maction']==='reply' ) $oH->redirect( 'qtf_edit.php?a=re&t='.$t, L('Reply') ); //█
  if ( $_POST['Maction']==='move' ) $oH->redirect( 'qtf_dlg.php?a=itemsMove&s='.$s.'&ids='.$t, L('Move') ); //█
  if ( $_POST['Maction']==='delete' ) $oH->redirect( 'qtf_dlg.php?a=itemsDelete&s='.$s.'&ids='.$t, L('Delete') ); //█

}

// ------
// INITIALISE and check grant access
// ------
$oS = new CSection($s);
// access denied
if ( $oS->type==='1' && (SUser::role()==='V' || SUser::role()==='U') ) {
  $oH->name = L('Section');
  $oH->exitname = SLang::translate();
  $oH->voidPage('', L('R_staff')); //█
}
if ( $oS->type==='2' && SUser::role()==='V' && $oT->type!=='A' ) {
  $oH->name = L('Section');
  $oH->exitname = SLang::translate();
  $oH->voidPage('', L('R_member')); //█
}
if ( $oS->type==='2' && SUser::role()==='U' && $oT->firstpostuser != SUser::id() && $oT->type!=='A' ) {
  $oH->name = L('Section');
  $oH->exitname = SLang::translate();
  $oH->voidPage('', L('R_member').'<br>'.L('E_item_private')); //█
}

// access granted
$oT->viewsIncrement(SUser::id()); // increment views (only when access is granted)
$tagEditor = SUser::canEditTags($oT);
$navCommands= '';
$limit = 0;
$currentPage = 1;
if ( isset($_GET['page']) ) { $limit = ($_GET['page']-1)*$_SESSION[QT]['replies_per_page']; $currentPage = (int)$_GET['page']; }
if ( isset($_GET['view']) ) { $_SESSION[QT]['viewmode'] = $_GET['view']; }
$oH->name = L('Messages');

// SUBMITTED CHANGE TAGS (tag-edit can be empty to delete all tags)
if ( isset($_POST['tag-ok']) && isset($_POST['tag-edit']) ) {
  $oT->descr = strip_tags(trim($_POST['tag-edit']));
  $oT->tagsUpdate();
}

// ------
// HTML BEGIN
// ------
include 'qtf_inc_hd.php';

// -- Title and staff commands --
echo '<div id="title-top" class="flex-sp top">
<div id="title-top-l">';
if ( QT_SHOW_PARENT_DESCR && $oT->numid>=0 && $oS->numfield!='N' )
echo '<p class="pg-title">'.sprintf($oS->numfield,$oT->numid).'</p>';
echo '</div>
<div id="title-top-r" class="right">';
if ( SUser::isStaff() ) include 'qtf_item_ui.php';
echo '</div>
</div>
';

// CONTENT

// Navigation buttons
$def = 'href='.url('qtf_edit.php').'?t='.$oT->id.'&a=re|class=button';
if ( $oS->status==='1' || $oT->status==='1' || (SUser::role()==='V' && $_SESSION[QT]['visitor_right']<6) ) {
  $def .= ' disabled|href=javascript:void(0)|tabindex=-1'; // class=button disabled
  if ( $oS->status==='1' )     { $def .= '|title='.L('E_section_closed'); }
  elseif ( $oT->status==='1' ) { $def .= '|title='.L('Closed_item'); }
  else                         { $def .= '|title='.L('R_member'); }
}
$navCommands = $oH->backButton().'<a'.attrRender($def).'>'.L('Reply').'</a>';
echo '<div class="nav nav-top">'.$navCommands.'</div>
';

// First message
$oP = new CPost($oT->firstpostid,1);
echo $oP->render($oS,$oT,true,true,QT_SKIN,'r1');

// First message tags
if ( $_SESSION[QT]['tags']!='0' && ($tagEditor || !empty($oT->descr)) ) {

  $tags = qtCleanArray($oT->descr);
  echo '<div id="tags-handler" class="tags right" style="padding:4px 0">'.qtSvg('tag'.(count($tags)>1 ? 's' : ''), 'title='.L('Tags')).' ';
  if ( $tagEditor ) {

    echo '<div id="tag-shown" style="display:inline-block">';
    foreach($tags as $tag)
    echo '<span class="tag clickable" onclick="tagClick(this.innerHTML)" title="" data-tagdesc="'.$tag.'">'.$tag.'</span>';
    echo '</div>';
    echo ' &nbsp; <a href="javascript:void(0)" id="tag-ctrl" class="tgl-ctrl" onclick="qtToggle(`#tag-container`,``,`#tags-handler`);" title="'.L('Edit').'">'.qtSvg('pen').qtSvg('angle-down').qtSvg('angle-up','class=nodisplay').'</a>'.PHP_EOL;
    echo '<div id="tag-container" style="display:none">';
    echo '<div id="ac-wrapper-tag-edit">';
    echo '<span id="tag-edit-srv-error" style="font-size:0.9rem;padding:0.1rem;margin-right:0.5rem:background-color:white;color:#e30000"></span>';
    echo '<input required type="text" id="tag-edit" size="12" maxlength="255" placeholder="'.L('Tags').'..." title="'.L('Edit_tags').'" data-multi="1" autocomplete="off"/>';
    echo '<button type="button" class="tag-btn" title="'.L('Reset').'" onclick="qtFocusAfter(`tag-edit`,true); return false;">'.qtSvg('backspace').'</button>&nbsp;';
    echo '<button type="button" class="tag-btn" title="'.L('Add').'" onclick="tagAdd(); asyncSaveTag('.$t.'); return false;">'.qtSvg('plus').'</button>';
    echo '<button type="button" class="tag-btn" title="'.L('Delete_tags').'" onclick="tagDel(); asyncSaveTag('.$t.'); return false;">'.qtSvg('minus').'</button>';
    echo '<input type="hidden" id="tag-saved" value="'.qtAttr($oT->descr).'"/>';
    echo '<input type="hidden" id="tag-new" name="tag-new" maxlength="255" value="'.qtAttr($oT->descr).'"/>';
    echo '</div></div>';

  } else {

    foreach($tags as $tag) echo '<span class="tag" title="...">'.$tag.'</span> ';

  }
  echo '</div>'.PHP_EOL;
}

// REPLIES
if ( $oT->items>0 ) {
  // Pager
  $paging = makePager( url('qtf_item.php?t='.$oT->id), $oT->items, (int)$_SESSION[QT]['replies_per_page'], $currentPage);
  if ( !empty($paging) ) echo '<p class="paging">'.L('Page').$paging.'</p>'.PHP_EOL;

  // ========
  $state = "p.*, u.role, u.location, u.signature FROM TABPOST p, TABUSER u WHERE p.id<>$oT->firstpostid AND p.userid=u.id AND p.topic=$oT->id";
  $oDB->query( sqlLimit($state,'p.id ASC',$limit,$_SESSION[QT]['replies_per_page']) );
  // ========
  $iMsgNum = $limit+1;
  $intWhile= 0;
  // ========
  while ( $row=$oDB->getRow() ) {
    $iMsgNum = $iMsgNum+1;
    $oP = new CPost($row,$iMsgNum); // when compact view $oP->text is qtInline
    // SHOW MESSAGE
    echo $oP->render($oS,$oT,true,true,QT_SKIN);
    $intWhile++;
  }
  // Pager
  if ( !empty($paging) ) echo '<p class="paging">'.L('Page').$paging.'</p>'.PHP_EOL;
}
// ========

// BUTTON LINE AND PAGER
if ( $oT->items>2 ) echo '<div class="nav nav-bot">'.$navCommands.'</div>';

// QUICK REPLY
if ( $_SESSION[QT]['show_quick_reply']==='1' ) {
if ( $oS->status==='0' && $oT->status==='0' ) {
if ( SUser::role()==='V' && $_SESSION[QT]['visitor_right']<7 ) {} else {

$certificate = makeFormCertificate('ec8a0d9ab2cae03d0c7314491eb60d0b');
echo '
<div id="message-preview"></div>
<form id="form-qr" method="post" action="'.url('qtf_edit.php').'?s='.$s.'&t='.$oT->id.'&a=re">
<div id="quickreply">
';
echo '<div class="g-qr-icon"><p class="i-container" title="'.L('Reply').'">'.qtSvg('comment-dots').'</p></div>
<div class="g-qr-title">'.L('Quick_reply').'</div>
<div class="g-qr-bbc">'.(QT_BBC ? '<div class="bbc-bar">'.bbcButtons(1).'</div>' : '').'</div>
<div class="g-qr-text">
<textarea required id="text-area" name="text" rows="5"></textarea>
<p id="quickreply-footer"><a href="javascript:void(0)" onclick="document.getElementById(`form-qr`).submit();">'.L('More').'...</a></p>
</div>
';
echo '<div class="g-qr-btn">
<input type="hidden" name="s" value="'.$s.'"/>
<input type="hidden" name="t" value="'.$oT->id.'"/>
<input type="hidden" name="a" value="re"/>
<input type="hidden" name="ref" value="'.$oT->numid.'"/>
<input type="hidden" name="userid" value="'.SUser::id().'"/>
<input type="hidden" name="username" value="'.SUser::name().'"/>
<input type="hidden" name="icon" value="00"/>
<input type="hidden" name="title" />
<button type="submit" id="form-qr-preview" name="preview" value="'.$certificate.'">'.L('Preview').'...</button><button type="submit" id="send" name="send" value="'.$certificate.'">'.L('Send').'</button>
</div>
';
echo '</div>
</form>
';

$oH->scripts[] = 'document.getElementById("form-qr-preview").addEventListener("click", (e) => {
  if ( document.getElementById("text-area").value.length===0 ) return false;
  e.preventDefault();
  let formData = new FormData(document.getElementById("form-qr"));
  fetch("qtf_edit_preview.php", {method:"POST", body:formData})
  .then( response => response.text() )
  .then( data => {
    document.getElementById("message-preview").innerHTML = data;
    document.querySelectorAll("#message-preview a").forEach( anchor => {anchor.href="javascript:void(0)"; anchor.target="";} ); } )
  .catch( err => console.log(err) );
});
';

}}}

// ------
// HTML END
// ------
if ( $_SESSION[QT]['tags']!='0' ) {

  $oH->scripts_end['tagdesc'] = '<script type="text/javascript" src="bin/js/qt_tagdesc.js" data-dir="'.QT_DIR_DOC.'" data-lang="'.QT_LANG.'"></script>';
  if ( $tagEditor) {
    $oH->scripts_end['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js" data-lang="'.QT_LANG.'"></script><script type="text/javascript" src="bin/js/qtf_config_ac.js"></script>';
    $oH->scripts['tags'] = '<script type="text/javascript" src="bin/js/qt_tags.js"></script>';
    $oH->scripts[] = 'function asyncSaveTag(item){
      const tag = document.getElementById("tag-new");
      fetch( `bin/srv_tagupdate.php?ref='.MD5(QT.session_id()).'&nid=${item}&max='.(defined('QT_MAX_TAGS') ? QT_MAX_TAGS : 24).'&tag=${tag.value}` )
      .then( response => response.json() )
      .then( data => asyncSaveTagResponse(data) )
      .catch( err => console.log(err) );
      }
      function asyncSaveTagResponse(data, target="tag-edit-srv-error") {
        if (data.length===0 ) { console.log("void response"); return; }
        if (data.status==="error") {
          let d = document.getElementById(target);
          if (d) { d.innerText = data?.info; return; }
        }
        console.log(data);
      }';
    }

}

include 'qtf_inc_ft.php';