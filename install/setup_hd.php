<?php // v4.0 build:20230618

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
<link rel="stylesheet" type="text/css" href="../bin/css/qt_core.css" />
<link rel="stylesheet" type="text/css" href="setup.css" />
</head>
';
echo '<body'.(empty($self) ? '' : ' class="'.$self.'"').'>
<main>
<header class="flex-sp bot">
  <img id="logo" src="'.APP.'_logo.gif" alt="'.APPNAME.'" title="'.APPNAME.'" />
  <p class="small">'.L('Installation').' '.APPNAME.' v'.VERSION.' '.BUILD.'</p>
</header>
'.(empty($tools) ? '' : $tools).'
<div class="main">
<div class="content">
';

if ( !empty($error) ) echo '<p class="is_err">'.$error.'</p>';