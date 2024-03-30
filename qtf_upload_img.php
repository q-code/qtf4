<?php // v4.0 build:20240210 allows app impersonation [qt f|i|e ]

// This is included in _adm_section_img.php and _user_image.php

/**
 * @var CHtml $oH
 * @var array $L
 * @var string $upload_path
 * @var int $id
 * @var string $currentImg
 * @var bool $currentExists
 * @var int $max_file
 * @var int $max_width
 * @var int $thumb_max_width
 * @var int $thumb_max_height
 * @var int $thumb_width
 * @var int $thumb_height
 * @var int $output_max
 * @var string $strMimetypes
 */

// check repository
if ( !is_dir($upload_path) ) die('Invalid directory: Administrator must create the repository in '.QT_DIR_DOC);
if ( !is_readable($upload_path) || !is_writable($upload_path) ) die('Invalid directory: Administrator must make the repository '.$upload_path.' writable');
if ( !isset($currentImg) ) die('Invalid setting: currentImg not defined.');
if ( !isset($output_max) ) $output_max = 1000;

// ------
// HTML BEGIN
// ------
$oH->links[] = '<link rel="stylesheet" type="text/css" href="bin/js/imagecrop.min.css"/>';

$oH->head();
$oH->body();

CHtml::msgBox($oH->selfname, 'class=msgbox|style=width:680px');

if ( !empty($oH->error) ) echo '<span class="error">',$oH->error,'</span>'.PHP_EOL;
if ( !empty($oH->warning) ) echo $oH->warning.PHP_EOL;

echo '<div class="flex-sp">
<div>'.$currentImg.'</div>
<div class="right">
<p><input type="file" id="file-upload" size="30"/></p>
';

echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'"><input type="hidden" name="id" value="'.$id.'"/>';
if ( $currentExists ) echo '<p>'.L('Delete_picture').' <button type="submit" name="del" value="del" style="width:80px">'.L('Delete').'</button></p>';
echo '<p><button type="submit" id="exit" name="exit" style="width:80px" >'.L('Exit').'</button></p>';
echo '</form>';

echo '</div>
</div>
';

echo'<div id="cropperinterface" style="display:none">
<hr style="margin:20px 0;border:1px solid #dddddd"/>
<div style="margin-bottom:10px;display:flex;flex-direction:row;justify-content:space-between">
<img id="preview" width="'.$thumb_width.'" height="'.$thumb_height.'"/>
<p id="preview-msg" style="display:none">'.$L['Picture_thumbnail'].'</p>
<p id="preview-btn"><button type="submit" name="upload_thumbnail" id="save_thumb" data-objectid="'.$id.'" style="width:80px">'.$L['Save'].'</button></p>
</div>
';

//echo '<div><img style="border:1px #e5e5e5 solid;margin-top:10px" src="'.$large_image_location.'" id="thumbnail" alt="Create Thumbnail"/></div>
echo '<div id="imagesrc" style="display:none"></div>
</div>
';

CHtml::msgBox('/');

$oH->scripts[] = '<script type="text/javascript" src="bin/js/imagecrop.min.js"></script>';
$oH->scripts[] = 'const thumb_width = '.$thumb_width.';
const thumb_height = '.$thumb_height.';
const thumb_max_width = '.$thumb_max_width.';
const thumb_max_height = '.$thumb_max_height.';
const output_max = '.$output_max.';
let img_c;
const imagesrc = document.getElementById("imagesrc");
const file_upload = document.getElementById("file-upload");
const preview = document.getElementById("preview");
const save_thumb = document.getElementById("save_thumb");
const reader = new FileReader();
reader.onload = function(e) {
  // check size
  let image = new Image();
  image.src = e.target.result;
  image.onload = function() {
    if ( this.width<=thumb_max_width && this.height<=thumb_max_height ) {
      init_interface();
      preview.src = image.src;
      return;
    }
    // initialize interface
    init_interface(["preview-msg","imagesrc"]);
    // initialize cropper
    img_c = new ImageCropper(
      "#imagesrc",
      e.target.result,
      {
        min_crop_width : thumb_max_width,
        min_crop_height : thumb_max_height,
        max_width : 624,
        max_height : 624,
        fixed_size : true,
        mode : "square",
        update_cb:function(p){ let m = img_c.crop("image/jpeg",1); preview.src = m; }
      }
    );
  }
};
function init_interface(ids=[]) {
  document.getElementById("cropperinterface").style.display = "block";
  ids.forEach( (id) => { document.getElementById(id).style.display = "inline-block"; } );
}
file_upload.addEventListener("change", (e) => {
  e.preventDefault;
  imagesrc.innerHTML="";
	const file = file_upload.files[0];
	reader.readAsDataURL(file); // triggers reader.onload
});
save_thumb.addEventListener("click", (e) => {
  e.preventDefault;
  let mime_type = "image/jpg";
  if ( preview.src.split(";")[0].includes("png") ) mime_type = "image/png";
  if ( preview.src.split(";")[0].includes("gif") ) mime_type = "image/gif";
  const img = new Image(); img.src = preview.src;
  if ( img.width>output_max )
  {
    const output = output_max/2;
    const canvas = document.createElement("canvas"); canvas.width=output; canvas.height=output;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(preview, 0, 0, output, output);
    preview.src = canvas.toDataURL();
  }
  let img_b64_str = preview.src;
  let formData = new FormData();
  formData.append("id", '.$id.');
  formData.append("mime", mime_type);
  formData.append("path", "'.$upload_path.'");
  formData.append("data", img_b64_str);
  // upload async
  fetch("bin/srv_imageupload.php", {method:"POST", body:formData})
  .then( response => response.text() )
  .then( data => { console.log(data); document.getElementById("exit").click(); } );
});';

$oH->end();