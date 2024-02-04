<?php /// v4.0 build:20230618 allows app impersonation [qt f|i|e ]

session_start();
/**
  * @var CDatabase $oDB
* @var CHtml $oH
 * @var array $L
 * @var int $id
 * @var string $currentImg
 * @var bool $currentExists
 * @var string $upload_path
 * @var int $max_file
 * @var int $max_width
 * @var int $thumb_max_width
 * @var int $thumb_max_height
 * @var int $thumb_width
 * @var int $thumb_height
 * @var string $strMimetypes
 */
require 'bin/init.php';
if ( !isset($_SESSION[QT]['formatpicture']) ) $_SESSION[QT]['formatpicture'] = 'mime=0;width=100;height=100';
if ( empty(qtExplodeGet($_SESSION[QT]['formatpicture'], 'mime')) ) die('This board do not use profile picture.');
$id = -1; qtArgs('int:id'); if ( $id<0 ) die('Missing parameter id...');
if ( SUser::id()!=$id && !SUser::isStaff() ) die('Access denied');

include 'bin/class/class.phpmailer.php';
include translate('lg_reg.php');

// ------
// INITIALISE
// ------
$oH->selfurl = APP.'_user_img.php';
$oH->selfname = L('Change_picture');
$oH->exiturl = APP.'_user.php?id='.$id;
$oH->exitname = '&laquo; '.L('Profile');

$oDB->query( "SELECT name,picture,children,role,parentmail FROM TABUSER WHERE id=$id" );
$row = $oDB->getRow();
// check staff edit grants
if ( SUser::id()!==$id && $row['role']==='A' && SUser::role()==='M' ) {
  if ( !defined('QT_STAFFEDITADMIN') ) define('QT_STAFFEDITADMIN',false);
  if ( !QT_STAFFEDITADMIN ) die('Access denied (system coordinator cannot edit system administrator)' );
}

// check folder
if ( !is_dir(QT_DIR_PIC) ) die('Invalid directory: Administrator must create the repository '.QT_DIR_PIC);
if ( !is_readable(QT_DIR_PIC) || !is_writable(QT_DIR_PIC) ) die('Invalid directory: Administrator must make the repository '.QT_DIR_PIC.' writable');

// ------
// SUBMITTED for Exit
// ------
if ( isset($_POST['exit']) ) $oH->redirect('exit');

// ------
// INITIALISE image and repository
// ------
$currentImg = SUser::getPicture($id , 'id=userimg'); // img tag (with user.gif if image does not exist)
$currentExists = strpos($currentImg,'user.gif')===false;
$upload_path = qtDirData(QT_DIR_PIC,$id); // The path to where the image will be saved (is checked in qtx_upload_img.php)
$max_file = 3;       // Maximum file size in MB
$max_width = 650;    // Max width allowed for the large image
$thumb_max_width = qtExplodeGet($_SESSION[QT]['formatpicture'],'width',150); // Above this value, the crop tool will start
$thumb_max_height = qtExplodeGet($_SESSION[QT]['formatpicture'],'height',150); // Above this value, the crop tool will start
$thumb_width = 100;  // Width of thumbnail image
$thumb_height = 100; // Height of thumbnail image
$output_max = 280; // maximum output thumbnail width (triggers 0.5x0.5 resize)
$strMimetypes = 'image/jpeg,image/jpg';
if ( strpos($_SESSION[QT]['formatpicture'],'gif')!==FALSE) $strMimetypes.=',image/gif';
if ( strpos($_SESSION[QT]['formatpicture'],'png')!==FALSE) $strMimetypes.=',image/png,image/x-png';

// ------
// SUBMITTED for Delete
// ------
if ( isset($_POST['del']) && $_POST['del']=='del' )
{
  unset($_SESSION['temp_key']);
  SUser::deletePicture($id);
  $oH->redirect($oH->selfurl.'?id='.$id);
}

// ------
// HTML BEGIN
// ------
if ( SUser::id()!==$id ) $oH->warning = '<p>'.qtSVG('exclamation-triangle', 'style=color:orange').' '.L('Not_your_account').'</p>';

include APP.'_upload_img.php';