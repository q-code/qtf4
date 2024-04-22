<?php // v4.0 build:20240210 allows app impersonation [qt f|e|i]

session_start();
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 * @var int $thumb_image_location
 * @var int $max_file
 * @var int $max_width
 * @var string $currentImg
 * @var bool $currentExists
 * @var string $upload_path
 * @var int $thumb_max_width
 * @var int $thumb_max_height
 * @var int $thumb_width
 * @var int $thumb_height
 * @var string $strMimetypes
 */
require 'bin/init.php';
if ( SUser::role()!=='A' ) die('Access denied');
$id = -1;
qtArgs('int:id!'); if ( $id<0 ) die('Missing parameter id...');

// ------
// INITIALISE
// ------
include translate('lg_reg.php');

$oH->selfurl = APP.'_adm_section_img.php';
$oH->selfname = L('Change_picture');
$oH->exiturl = APP.'_adm_section.php?pan=2&s='.$id.'&up='.(empty($_POST['up']) ? '0' : '1'); // extra arg to indicate image is updated

// ------
// SUBMITTED for Exit
// ------
if ( isset($_POST['exit']) ) $oH->redirect(); //█

// ------
// INITIALISE image and repository
// ------
$currentImg = CSection::getImage($id , 'id=userimg', ''); // returns '' if image doesn't exist
$currentExists = $currentImg!=='';
$upload_path = QT_DIR_DOC.'section/';
$max_file        = defined('QT_SECTIONLOGO_SIZE') ? QT_SECTIONLOGO_SIZE : 2; // Maximum file size in MB
$max_width       = 650; // Display width for the large image (image can be larger)
$thumb_max_width = defined('QT_SECTIONLOGO_WIDTH') ? QT_SECTIONLOGO_WIDTH+25 : 75; // Above this value, the crop tool will start
$thumb_max_height= defined('QT_SECTIONLOGO_HEIGHT') ? QT_SECTIONLOGO_HEIGHT+25 : 75; // Above this value, the crop tool will start
$thumb_width     = defined('QT_SECTIONLOGO_WIDTH') ? QT_SECTIONLOGO_WIDTH : 50;  // Width of thumbnail image (75px)
$thumb_height    = defined('QT_SECTIONLOGO_HEIGHT') ? QT_SECTIONLOGO_HEIGHT : 50; // Height of thumbnail image (75px)
$strMimetypes    = 'image/pjpeg,image/jpeg,image/jpg,image/gif,image/png,image/x-png';
//Check to see if any images with the same name already exist

// ------
// SUBMITTED FOR DELETE
// ------
if ( isset($_POST['del']) && $_POST['del']=='del' )
{
  CSection::deleteImage($id);
  $oH->redirect($oH->selfurl.'?id='.$id);
}

// ------
// PAGE
// ------
$oH->links = [];
$oH->links[] = '<link rel="shortcut icon" href="bin/css/qt.ico"/>';
$oH->links[] = '<link rel="stylesheet" type="text/css" href="bin/css/qt_core.css"/>';
$oH->links[] = '<link rel="stylesheet" type="text/css" href="bin/css/admin.css"/>';

include APP.'_upload_img.php';