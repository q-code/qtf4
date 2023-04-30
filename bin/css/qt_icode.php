<?php // v4.0 build:20230430 allows app impersonation [qt f|i ]

session_start();
$intBack = rand(1, 2);
$imgPng = imagecreatefrompng('qt_icode_'.$intBack.'.png');
$font = getcwd().'/qt_icode.ttf';

// Generate the random string
$strText = 'QT'.rand(0,9).rand(0,9).rand(0,9).rand(0,9);
$_SESSION['textcolor'] = sha1($strText);

// Create random size, angle, and dark color
$size = 13;
$angle = rand(-5, 5);
$color = imagecolorallocate($imgPng, rand(0, 100), rand(0, 100), rand(0, 100));

//Determine text size, and use dimensions to generatx & y coordinates
$textsize = imagettfbbox($size, $angle, $font, $strText);
$twidth = abs($textsize[2]-$textsize[0]);
$theight = abs($textsize[5]-$textsize[3]);
$x = (imagesx($imgPng)/2)-($twidth/2)+(rand(-15, 15));
$y = (imagesy($imgPng))-($theight/2);

//Add text to image
imagettftext($imgPng, $size, $angle, $x, $y, $color, $font, $strText);

//Output PNG Image
header('Content-Type: image/png');
imagepng($imgPng);

//Destroy the image to free memory
imagedestroy($imgPng);

//End Output
exit;
?>