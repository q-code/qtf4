<?php // v4.0 build:20240210

// This generates a preview message where all href are DISABLED
// Data come from qtf_edit.php by a ajax POST request. The preview is included in the form
// Attachment cannot be rendered (only filename is displayed)

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';

// Security and compliance

if ( !SUser::canView('V5') ) $oH->voidPage('user-lock.svg',11,true); //█

// INITIALISE
$a = '';
$s = -1;
$t = -1;
qtArgs('char2:a! int+:s! int+:t!',false); // in POST only
if ( !in_array($a,['nt','re','ed','qu','de']) ) die('Invalid parameter a');

$oH->error = '';
$oH->name = L('Message');
$oS = new CSection($s);
$oT = new CTopic($t);
$oP = new CPost(); if ( isset($_POST['p']) ) $oP->id = (int)$_POST['p']; // id = -1 while preview
$oP->type = 'R';
$oP->issuedate = date('Y-m-d H:i');
$oP->text = rtrim($_POST['text']); // right-trim
$oP->userid = (int)$_POST['userid'];
$oP->username = $_POST['username'];
$oP->modifuser = (int)$_POST['userid']; // can be onbehalf
$oP->modifname = $_POST['username'];
if ( empty($oP->modifname) ) { $oP->modifuser = SUser::id(); $oP->modifname = SUser::id(); }

// ------
// SUBMITTED
// ------
try {

  // For New post (or Reply or Quote) creator=modifuser, while creator don't change when editing existing post
  if ( $a!='ed' ) {
    $oP->userid = $oP->modifuser;
    $oP->username = $oP->modifname;
  }

  // Read submitted form values
  if ( isset($_POST['icon']) )   $oP->icon = substr($_POST['icon'],0,2);
  if ( isset($_POST['title']) )  $oP->title = qtInline(trim($_POST['title']),64);
  if ( isset($_POST['attach']) ) $oP->attach = $_POST['attach'];
  if ( isset($_POST['tag-edit']) ) $oT->descr = trim($_POST['tag-edit']);
  if ( strlen($oP->text)>$_SESSION[QT]['chars_per_post'] ) { $oP->text = substr($oP->text,0,255).' [...]'; throw new Exception( L('E_too_long').' '.sprintf(L('E_char_max'), $_SESSION[QT]['chars_per_post']) ); }
  if ( substr_count($oP->text,"\n")>$_SESSION[QT]['lines_per_post'] ) { $oP->text = substr($oP->text,0,255).' [...]'; throw new Exception( L('E_too_long').' '.sprintf(L('E_line_max'), $_SESSION[QT]['lines_per_post']) ); }
  $oT->preview = qtInline($oP->text);

  // Detect basic errors
  if ( $oP->text=='' ) throw new Exception( L('Message').' '.L('invalid') );
  if ( $a=='nt' && $oP->title=='' && $oS->titlefield==2 ) throw new Exception( L('E_no_title') );
  if ( $a=='nt' && $oP->title=='' ) CPost::makeTitle($oP);

  // Check flood limit
  if ( !empty($_SESSION[QT.'_usr']['lastpost']) && $_SESSION[QT.'_usr']['lastpost']+QT_FLOOD >= time() ) throw new Exception( L('E_wait') );

  // check maximum post per day (not for moderators)
  if ( !SUser::isStaff() && !postsTodayAcceptable((int)$_SESSION[QT]['posts_per_day']) ) {
    $oH->exiturl = 'qtf_items.php?s='.$s;
    $oH->voidPage('', L('E_too_much')); //█
  }

  // Module antispam
  if ( qtModule('antispam') ) include 'qtfm_antispam.php';

  // check upload
  if ( $_SESSION[QT]['upload']!=='0' && !empty($_FILES['newdoc']['name']) ) {
    include 'config/config_upload.php';
    fileValidate($_FILES['newdoc'], ALLOWED_FILE_EXT, ALLOWED_MIME_TYPE, $_SESSION[QT]['upload_size']*1024+16);
  }

} catch (Exception $e) {

  $oH->error = $e->getMessage();

}

// PREPARE DISPLAY

if ( $_POST['a']=='nt' ) { $oH->name = L('New_item'); $oP->type = 'P'; }
if ( $_POST['a']=='ed' ) $oH->name = L('Edit_message');
if ( $_POST['a']=='qu' || $_POST['a']=='re' ) $oH->name = L('Reply');

// get user info
if ( empty($oP->userid) ) $oP->userid=$oP->modifuser;
$oDB->query( "SELECT signature,location,role FROM TABUSER WHERE id=$oP->userid" );
$row = $oDB->getRow();
$oP->userloca = $row['location'];
$oP->usersign = $row['signature'];
$oP->userrole = $row['role'];

// ------
// HTML PART
// ------
echo '<h2>'.L('Preview').'</h2>
';
if ( !empty($oH->error) ) echo '<p><span class="error">'.$oH->error.'</span></p>';

echo $oP->render($oS, $oT, true, '', QT_SKIN, '1');

echo '<p class="right small">'.( empty($oP->attach) ? '' : qtSVG('info').' '.L('No_attachment_preview') ).'</p><br>';