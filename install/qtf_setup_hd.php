<?php // v4.0 build:20230205
/**
* @var string $error
 */

echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="en" lang="en">
<head>
<title>'.L('Installation').' '.APPNAME.'</title>
<meta charset="utf-8" />
<meta name="description" content="'.APPNAME.'" />
<meta name="keywords" content="quickalk,users management,qt-cute,OpenSource" />
<meta name="author" content="qt-cute.org" />
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5" />
<link rel="shortcut icon" href="'.APP.'_icon.ico" />
<link rel="stylesheet" type="text/css" href="'.APP.'_setup.css" />
</head>

<body>


<div class="page">


<div class="banner">
<img id="logo" src="'.APP.'_logo.gif" alt="'.APPNAME.'" title="'.APPNAME.'" />
</div>


<div class="body">
<p style="margin:0 0 20px 0;text-align:right">'.L('Installation').' '.APPNAME.' v'.VERSION.' '.BUILD.'</p>
';

if ( !empty($error) ) echo '<div class="setup_err">'.$error.'</div>';