 <?php // v4.0 build:20230430

session_start();
/**
 * @var string $oH->warning
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';
if ( !SUser::canView('V6') ) die(L('E_11'));

// --------
// Check posted certificates
// --------

$certificate = makeFormCertificate('db6a94aa9b95da757a97f96ab4ce4ca5');
// Forwarding certificate. Note: 'dopreview' is ajax-transfered to edit_preview.php
if ( isset($_POST['dosend']) && $_POST['dosend']===makeFormCertificate('ec8a0d9ab2cae03d0c7314491eb60d0b') ) $_POST['dosend']=$certificate;
// Check certificate
if ( isset($_POST['dosend']) && $_POST['dosend']!==$certificate ) die('Unable to check certificate');

// --------
// INITIALISE
// --------

$a = '';
$s = -1;
$t = -1;
$p = -1;
qtArgs('a! int:s int:t int:p'); // a,s,t,p can be send by GET or POST, others only by POST from the form. Only a is required

// Initialise containers and check $s
$oT = new CTopic($t>=0 ? $t : null); // can be -1 (new topic)
if ( $oT->pid>=0 ) $s = $oT->pid; // when editing a topic, $s is that topic pid, otherwise uses the GET/POST value
if ( $s<0 ) die('Missing parameters: s');
$oS = new CSection($s);
$oP = new CPost($p>=0 ? $p : null);
if ( $a==='qu' && !empty($oP->text) ) {
  $str = $oP->text; $oP = new CPost(null); $oP->text = $str; // quote must be a void-post with only text
}
if ( isset($_POST['text']) ) $oP->text = trim($_POST['text']); // Note: text can be forwarded (not yet submitted) when user changes from quickreply to advanced-reply form

// Initialise $oH and check $a
$oH->selfurl = APP.'_edit.php';
switch($a) {
  case 'nt': $oH->selfname = L('New_item'); break;
  case 're': $oH->selfname = L('Reply'); break;
  case 'qu': $oH->selfname = L('Reply'); break;
  case 'ed': $oH->selfname = L('Edit_message'); break;
  default: die('Missing parameters a');
}
$oH->exiturl = APP.'_item.php?t='.$t; if ( $t<0 ) $oH->exiturl = 'qtf_items.php?s='.$s;
$oH->exitname = L('Item+');

// Initialise others
$now = date('Ymd His');
$withDoc = false;
$tagEditor = false;

// Check initial type and parent ('quote' become 'reply')
switch($a) {
case 'nt': $oP->type = 'P'; break;
case 're': $oP->type = 'R'; if ( $t<0 ) die('Missing parameters: t'); break;
case 'qu': $oP->type = 'R'; if ( $t<0 || $p<0 ) die('Missing parameters: t or p');
  $oP->text = '[quote='.$oP->username.']'.$oP->text.'[/quote]';
  $a = 're';
  break;
case 'ed': if ( $t<0 || $p<0 ) die('Missing parameters: t or p'); break;
}

if ( isset($_GET['debug']) ) var_dump($oP);

// --------
// SUBMITTED
// --------

if ( isset($_POST['dosend']) ) try {

  // Current editor/creator (modifuser), can be the onbehalf
  $oP->modifuser = (int)$_POST['userid'];
  $oP->modifname = qtDb(trim($_POST['username']));
  if ( !empty($_POST['behalf']) ){
    $strBehalf = qtDb(trim($_POST['behalf']));
    $intBehalf = (int)$_POST['behalfid']; if ( $intBehalf<0 ) $intBehalf = SUser::getUserId($oDB,$strBehalf,-1);
    if ( $intBehalf<0 ) throw new Exception( L('Send_on_behalf').' '.L('invalid') );
    $oP->modifuser = $intBehalf;
    $oP->modifname = $strBehalf;
  }
  // For New post (or Reply or Quote) creator=modifuser, while creator don't change when editing existing post
  if ( $a!='ed' ) {
    $oP->userid = $oP->modifuser;
    $oP->username = $oP->modifname;
  }

  // Read submitted form values
  if ( isset($_POST['icon']) )   $oP->icon = substr($_POST['icon'],0,2);
  if ( isset($_POST['title']) )  $oP->title = qtInline(trim($_POST['title']),64);
  if ( isset($_POST['attach']) ) $oP->attach = $_POST['attach']; // old attachment
  if ( isset($_POST['tag-edit']) ) $oT->descr = trim($_POST['tag-edit']);
  if ( strlen($oP->text)>$_SESSION[QT]['chars_per_post'] ) throw new Exception( L('E_too_long').' '.sprintf(L('E_char_max'), $_SESSION[QT]['chars_per_post']) );
  if ( substr_count($oP->text,"\n")>$_SESSION[QT]['lines_per_post'] ) throw new Exception( L('E_too_long').' '.sprintf(L('E_line_max'), $_SESSION[QT]['lines_per_post']) );
  $oT->preview = qtInline($oP->text);

  // Detect basic errors
  if ( $oP->text=='' ) throw new Exception( L('Message').' '.L('invalid') ); //...
  if ( $a=='nt' && $oP->title=='' && $oS->titlefield==2 ) throw new Exception( L('E_no_title') ); //...
  if ( $a=='nt' && $oP->title=='' ) CPost::makeTitle($oP);

  // Check flood limit (_usr_lastpost is set in CPost::insert)
  if ( !empty($_SESSION[QT.'_usr']['lastpost']) && $_SESSION[QT.'_usr']['lastpost']+QT_FLOOD >= time() ) throw new Exception( L('E_wait') ); //...

  // check maximum post per day (not for moderators)
  if ( !SUser::isStaff() && !postsTodayAcceptable((int)$_SESSION[QT]['posts_per_day']) ) {
    $oH->exiturl = 'qtf_items.php?s='.$s;
    $oH->pageMessage('', L('E_too_much')); //###
  }

  // Module antispam
  if ( useModule('antispam') ) include 'qtfm_antispam.php';

  // check upload
  if ( $_SESSION[QT]['upload']!=='0' && !empty($_FILES['newdoc']['name']) ) {
    include 'config/config_upload.php';
    $info = validateFile($_FILES['newdoc'],ALLOWED_FILE_EXT,ALLOWED_MIME_TYPE,intval($_SESSION[QT]['upload_size'])*1024+16);
    if ( !empty($info) ) throw new Exception( $info ); //...
    $withDoc = true;
    // remove old attach
    if ( !empty($_POST['attach']) && file_exists(QT_DIR_DOC.$_POST['attach']) ) { unlink(QT_DIR_DOC.$_POST['attach']); $oP->attach = ''; }
  }

  // PROCESS $a
  switch($a)
  {
  case 'nt': // new topic
    $oDB->beginTransac();
    $oT->id = $oDB->nextId(TABTOPIC);
    $oT->numid = $oDB->nextId(TABTOPIC,'numid','WHERE forum='.$s);
    $oP->id = $oDB->nextId(TABPOST);
    $oP->topic = $oT->id;
    $oT->pid = $s;
    $oP->section = $s;
      // if moderator post
      if ( isset($_POST['topictype']) ) $oT->type = $_POST['topictype'];
      if ( isset($_POST['topicstatus']) ) $oT->status = $_POST['topicstatus'];
    $oT->firstpostid = $oP->id;
    $oT->lastpostid = $oP->id;
    $oT->firstpostuser = $oP->userid; // not SUser::id() as it can be onbehalf;
    $oT->firstpostname = $oP->username;
    $oT->lastpostuser = $oP->userid; // not SUser::id() as it can be onbehalf;
    $oT->lastpostname = $oP->username;
    $oT->firstpostdate = $now;
    $oT->lastpostdate = $now;
    $oP->issuedate = $now;
    if ( $withDoc )
    {
      $strDir = getRepository('',$oP->id);
      $oP->attach = $strDir.$oP->id.'_'.$_FILES['newdoc']['name'];
      copy($_FILES['newdoc']['tmp_name'],QT_DIR_DOC.$oP->attach);
      unlink($_FILES['newdoc']['tmp_name']);
    }
    // Insert
    $oP->insertPost(false);
    $oT->insertTopic(true);
    $oDB->commitTransac();
    if ( isset($_SESSION[QT.'_usr']['numpost']) ) $_SESSION[QT.'_usr']['numpost']++;
    // ----------
    // module rss
    if ( useModule('rss') ) { if ( $_SESSION[QT]['m_rss']=='1' ) include 'qtfm_rss_inc.php'; }
    // ----------
    break;

  case 're':
  case 'qu': // SEND a reply
    $oP->id = $oDB->nextId(TABPOST);
    $oP->topic = $t;
    $oP->section = $s;
    $oP->issuedate = $now;
    if ( $withDoc )
    {
      $strDir = getRepository('',$oP->id);
      $oP->attach = $strDir.$oP->id.'_'.$_FILES['newdoc']['name'];
      copy($_FILES['newdoc']['tmp_name'],QT_DIR_DOC.$oP->attach);
      unlink($_FILES['newdoc']['tmp_name']);
    }

    $oP->insertPost(true);
    if ( isset($_SESSION[QT.'_usr']['numpost']) ) $_SESSION[QT.'_usr']['numpost']++;

    // update topic stats and close topic if full (and lastpost topic info)
    $oT->updMetadata((int)$_SESSION[QT]['posts_per_item']);

    // topic status/type (from staff)
    if ( isset($_POST['topictype']) ) $oT->setType($_POST['topictype']);
    if ( isset($_POST['topicstatus']) )$oT->setStatus($_POST['topicstatus']);

    // topic status (from user)
    if ( isset($_POST['topicstatususer']) ) { if ( $_POST['topicstatususer'][0]=='1' ) $oT->setStatus('1'); }
    break;

  case 'ed': // SEND a edit
    if ( $oP->type==='P' && ($oS->titlefield===0 || empty($oP->title)) ) CPost::makeTitle($oP);

    $strModif = '';
    // modifdate+modifuser if editor is not the creator
    if ( $oP->modifuser!=$oP->userid ) $strModif=', modifdate="'.date('Ymd His').'", modifuser='.$oP->modifuser.', modifname="'.$oP->modifname.'"';
    // modifdate+modifuser if not the last message
    if ( $oT->lastpostid!=$oP->id ) $strModif=', modifdate="'.date('Ymd His').'", modifuser='.$oP->modifuser.', modifname="'.$oP->modifname.'"';

    // Add attach
    if ( $withDoc ) {
      $strDir = getRepository('',$oP->id);
      $oP->attach = $strDir.$oP->id.'_'.$_FILES['newdoc']['name'];
      copy($_FILES['newdoc']['tmp_name'],QT_DIR_DOC.$oP->attach);
      unlink($_FILES['newdoc']['tmp_name']);
    }

    // Drop attach
    if ( isset($_POST['dropattach']) ) { $oP->attach=''; CPost::dropAttachs($oP->id,false); }
    // save edits
    $oDB->exec( "UPDATE TABPOST SET title='".qtDb($oP->title)."', icon='".$oP->icon."',textmsg='".qtDb($oP->text)."',attach='".$oP->attach."' ".$strModif." WHERE id=".$oP->id );
    // topic type (from staff)
    if ( isset($_POST['topictype']) ) $oT->setType($_POST['topictype']);
    // topic status (from staff)
    if ( isset($_POST['topicstatus']) ) $oT->setStatus($_POST['topicstatus']);
    // topic status (from user)
    if ( isset($_POST['topicstatususer']) && $_POST['topicstatususer'][0]=='1' ) $oT->setStatus('1');
    break;

  default: die('Invalid edit type');
  }

  // clear caches (SectionsStats, Sections, StatsGDS)
  memFlush(); memFlushStats();

  // EXIT
  $str = ''; if ( $oS->numfield!='N' ) $str=sprintf($oS->numfield,$oT->numid);
  $str .= (empty($oH->warning) ? '' : ' '.$oH->warning).' ';
  $_SESSION[QT.'splash'] = $str.L('S_message_saved');
  $oH->redirect('qtf_item.php?t='.$oP->topic.'#'.$oP->id);

} catch (Exception $e) {

  $_SESSION[QT.'splash'] = 'E|'.$e->getMessage();

}

// --------
// HTML BEGIN
// --------

$canUpload = SUser::canAccess('upload');
$intBbc = $canUpload ? 3 : 2;

include 'qtf_inc_hd.php';

// PREVIEW

echo '<div id="message-preview"></div>
';

// FORM START

echo '<form id="form-edit" method="post" action="'.url($oH->selfurl).'" enctype="multipart/form-data">
<div class="flex-sp">
<h2>'.$oH->selfname.'</h2>
';

if ( SUser::isStaff() )
{
  echo '<div id="optionsbar" title="'.L('Staff').' '.L('commands').'">'.getSVG('user-M');
  if ( $oP->type==='P' )
  {
  echo '<span>'.L('Type').' <select name="topictype" size="1" id="newtopictype" onchange="changeIcon()">';
  echo asTags(CTopic::getTypes(),$oT->type);
  echo '</select></span>';
  echo '<span>'.L('Status').' <select name="topicstatus" size="1" id="newtopicstatus" onchange="changeIcon()">';
  echo asTags(CTopic::getStatuses(),$oT->status);
  echo '</select></span>';
  }
  echo '<span id="ac-wrapper-behalf" class="ac-wrapper">'.L('Send_on_behalf').'&nbsp;<input type="text" name="behalf" id="behalf" size="14" maxlength="24" value="'.$oP->username.'" autocomplete="off"/><input type="hidden" id="behalfid" name="behalfid" value="-1"></span></div>';
}
echo '
</div>
';

echo '<div class="edit-post">
<div class="edit-type"><p class="i-container">'.CPost::getIconType($oP->type,$oT->type,$oT->status,QT_SKIN).'</p></div>
<div class="edit-form">
<input type="hidden" name="s" value="'.$s.'"/>
<input type="hidden" name="t" value="'.$t.'"/>
<input type="hidden" name="a" value="'.$a.'"/>
<input type="hidden" name="p" value="'.$oP->id.'"/>
<input type="hidden" id="userid" name="userid" value="'.SUser::id().'"/>
<input type="hidden" id="username" name="username" value="'.SUser::name().'"/>
';
echo '<table>'.PHP_EOL;
if ( !empty($oS->prefix) )
{
  echo '<tr>';
  echo '<th>'.L('Prefix').'</th>';
  echo '<td><span class="cblabel">'.PHP_EOL;
  for ($i=1;$i<10;$i++)
  {
    $str = icoPrefix($oS->prefix,$i);
    if ( !empty($str) ) echo '<input type="radio" name="icon" id="i0'.$i.'" value="0'.$i.'"'.($oP->icon=='0'.$i ? 'checked' : '').'/><label for="i0'.$i.'">'.$str.'</label> &nbsp;'.PHP_EOL;
  }
  echo '<input type="radio" name="icon" id="00" value="00"'.($oP->icon=='00' ? 'checked' : '').'/><label for="00">'.L('None').'</label></td>';
  echo '</span></tr>'.PHP_EOL;
}
// title
if ( $oS->titlefield!=0 )
{
echo '<tr>'.PHP_EOL;
echo '<th>'.L('Title').'</th>'.PHP_EOL;
echo '<td><input'.($oS->titlefield==2 ? ' required' : '').' id="edit-form-title" tabindex="1" type="text" name="title" maxlength="64" value="'.qtAttr($oP->title).'"/></td>'.PHP_EOL;
echo '</tr>'.PHP_EOL;
}
// message
echo '<tr>';
echo '<th><label for="text">'.L('Message').'</label></th>';
echo '<td>';
if ( QT_BBC ) echo '<div class="bbc-bar">'.bbcButtons($intBbc).'</div>';
echo '<textarea required tabindex="2" id="text" name="text" '.(strlen($oP->text)>500 ? 'rows="30"' : 'rows="15"' ).' maxlength="'.$_SESSION[QT]['chars_per_post'].'">'.$oP->text.'</textarea>'.PHP_EOL;

if ( $canUpload ) echo '<p style="margin:0"><a id="tgl-ctrl" class="tgl-ctrl" href="javascript:void(0)" onclick="qtToggle(`tgl-container`,`table-row`,`tgl-ctrl`); return false;">'.L('Attachment').getSVG('angle-down','','',true).getSVG('angle-up','','',true).'</a></p>';

echo '</td></tr>'.PHP_EOL;
// attachment
if ( $canUpload )
{
  $intMax = (int)$_SESSION[QT]['upload_size']*1024;
  echo '<tr id="tgl-container" style="display:'.(empty($oP->attach) ? 'none' : 'table-row').'">';
  echo '<th>'.getSVG('paperclip', 'title='.L('Attachment')).'</th>';
  echo '<td>';
  if ( !empty($oP->attach) )
  {
    if ( strpos($oP->attach,'/') ) { $str = substr(strrchr($oP->attach,'/'),1); } else { $str=$oP->attach; }
    if ( substr($str,0,strlen($oP->id.'_'))==($oP->id).'_' ) $str = substr($str,strlen($oP->id.'_'));
    echo $str.'<input type="hidden" id="attach" name="attach" value="'.$oP->attach.'"/>';
    echo ' &middot; <input type="checkbox" id="dropattach" name="dropattach" value="1"/><label for="dropattach">&nbsp;'.L('Drop_attachment').'</label>';
  }
  else
  {
    echo '<input type="hidden" name="MAX_FILE_SIZE" value="'.$intMax.'"/>';
    echo '<input tabindex="3" type="file" id="newdoc" name="newdoc" size="42"/>';
  }
  echo '</td></tr>'.PHP_EOL;
}
// topic status (from user)
if ( $oT->status==='0' && $oT->firstpostuser==SUser::id() )
{
  $bChecked = false;
  if ( isset($_POST['topicstatususer']) ) { if ( $_POST['topicstatususer'][0]=='1' ) $bChecked=true; }
  echo '<tr>'.PHP_EOL;
  echo '<th>'.L('Status').'</th>'.PHP_EOL;
  echo '<td><span class="cblabel"><input tabindex="4" type="checkbox" id="topicstatususer" name="topicstatususer[]" value="1"'.($bChecked ? 'checked' : '').'/><label for="topicstatususer">&nbsp;'.L('Close_my_item').'</label>&nbsp;</span></td>'.PHP_EOL;
  echo '</tr>'.PHP_EOL;
}
echo '</table>'.PHP_EOL;
// form end
echo '</div>
</div>
';

// ADD TAGS
if ( $_SESSION[QT]['tags']!=='0' && ($a==='nt' || ($a==='ed' && $oP->type==='P') ) )
{
  $arrTags=explode(';',$oT->descr);
  if ( $oT->status!=='1' )
  {
    if ( SUser::isStaff() ) $tagEditor=true;
    if ( $_SESSION[QT]['tags']=='U' && SUser::id()===$oT->firstpostuser ) $tagEditor=true; // 'U'=members can edit in his own ticket
    if ( $_SESSION[QT]['tags']=='U+' && SUser::role()==='U' ) $tagEditor=true; // 'U+'=members can edit any tickets
    if ( $_SESSION[QT]['tags']=='V' ) $tagEditor=true; // 'V'=Visitor can edit any tickets
  }
  if ( $tagEditor )
  {
    $tags = '';
    foreach(explode(';',$oT->descr) as $k=>$item) $tags .= empty($item) ? '' : '<span class="tag" onclick="tagClick(this.innerHTML)">'.$item.'</span>';
    echo '<div class="tags right" style="padding:4px 0">'.getSVG('tag'.(count($arrTags)>1 ? 's' : ''), 'title='.L('Tags'));
    echo ' <div id="tag-container" style="display:inline-block">';
    echo '<span id="tag-shown" style="display:inline-block">'.$tags.'</span>';
    echo '<input type="hidden" id="tag-saved" value="'.qtAttr($oT->descr).'"/>';
    echo '<input type="hidden" id="tag-new" name="tag-new" maxlength="255" value="'.qtAttr($oT->descr).'"/>';
    echo '<input type="hidden" id="tag-dir" value="'.QT_DIR_DOC.'"/><input type="hidden" id="tag-lang" value="'.QT_LANG.'"/>';
    echo '<div id="ac-wrapper-tag-edit" class="ac-wrapper">';
    echo '<input type="text" id="tag-edit" size="12" maxlength="255" placeholder="'.L('Tags').'..." title="'.L('Edit_tags').'" autocomplete="off" data-multi="1"/><button type="reset" class="tag-btn" title="'.L('Reset').'" onclick="document.getElementById(`tag-edit`).value=``;qtFocus(`tag-edit`)">'.getSVG('backspace').'</button>&nbsp;<button type="button" name="tag-btn" class="tag-btn" value="addtag" title="'.L('Add').'" onclick="tagAdd()">'.getSVG('plus').'</button><button type="button" name="tag-btn" class="tag-btn" value="deltag" title="'.L('Delete_tags').'" onclick="tagDel()">'.getSVG('minus').'</button>';
    echo '</div>';
    echo '</div></div>'.PHP_EOL;
  }
}

echo '<p class="submit">
<button type="button" tabindex="5" onclick="window.location=`'.url($oH->exiturl).'`">'.L('Cancel').'</button>&nbsp;
<button type="submit" tabindex="6" id="dopreview" value="'.$certificate.'" onclick="this.form.dataset.state=0">'.L('Preview').'...</button>&nbsp;
<button type="submit" tabindex="7" name="dosend" value="'.$certificate.'" onclick="this.form.dataset.state=1">'.($a=='ed' ? L('Save') : L('Send')).'</button>
</p>
</form>
';

// PREVIOUS POSTS

if ( $a=='re' || $a=='qu' )
{
  echo '<h2>'.L('Previous_replies').'</h2>'.PHP_EOL;
  $strState = 'p.*, u.role, u.location, u.signature FROM TABPOST p, TABUSER u WHERE p.userid = u.id AND p.topic='.$oT->id.' ';
  $oDB->query( sqlLimit($strState,'p.id DESC',0,5) );
  $iMsgNum = $oT->items + 2;
  $intWhile= 0;
  while($row=$oDB->getRow())
  {
    $iMsgNum = $iMsgNum-1;
    $oP = new CPost($row,$iMsgNum);
    echo $oP->render($oS,$oT,false,true,QT_SKIN);
    $intWhile++;
  }
}

// HTML END

if ( QT_BBC ) $oH->scripts[] = '<script type="text/javascript" src="bin/js/qt_bbc.js"></script>';
if ( $tagEditor || SUser::isStaff() ) {
  $oH->scripts['ac'] = '<script type="text/javascript" src="bin/js/qt_ac.js"></script>
  <script type="text/javascript" src="bin/js/qtf_config_ac.js"></script>';
  $oH->scripts[] = '<script type="text/javascript" src="bin/js/qt_tags.js"></script>';
  $oH->scripts[] = 'acOnClicks["behalf"] = function(focusInput,btn) {
  if ( focusInput.id=="behalf" ) document.getElementById("behalfid").value = btn.dataset.id;
}
function changeIcon(){
  const type = document.getElementById("newtopictype").value.toLowerCase();
  const status = document.getElementById("newtopicstatus").value.toLowerCase();
  const d = document.querySelector(".i-container img");
  if ( d ){
    let src = d.getAttribute("src");
    if ( src ) {
      src = src.replace(/topic_._./, "topic_"+type+"_"+status);
      d.setAttribute("src",src);
      d.setAttribute("data-type",type);
      d.setAttribute("data-status",status);
    }
  }
}';
}

$oH->scripts[] = 'const btnPreview = document.getElementById("dopreview");
if  ( btnPreview ) {
  btnPreview.addEventListener("click", (e) => {
    e.preventDefault();
    if ( document.getElementById("text").value.length==0 ) { alert("No message..."); return; }
    let formData = new FormData(document.getElementById("form-edit"));
    fetch("qtf_edit_preview.php", {method:"POST", body:formData})
    .then( response => response.text() )
    .then( data => {
      document.getElementById("message-preview").innerHTML = data;
      document.querySelectorAll("#message-preview a").forEach( anchor => {anchor.href="javascript:void(0)"; anchor.target="";} ); } )
    .catch( err => console.log(err) );
  });
}';

include 'qtf_inc_ft.php';