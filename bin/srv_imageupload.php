<?php // v4.0 build:20240210

// SERVEUR SCRIPT
// Perform async queries on request from web pages (ex: using ajax) with POST method
// Ouput (echo) results as string


// SERVICE ARGUMENTS
if ( !isset($_POST['id']) || $_POST['id']<0 ) { echo 'error: invalid id'; exit; }
if ( empty($_POST['mime']) ) { echo 'error: invalid mime'; exit; } // image/gif | image/jpeg | image/png
if ( empty($_POST['path']) ) { echo 'error: invalid output path'; exit; }

// INITIALIZE

/* Get the name of the uploaded file */
$fullname = $_POST['path'].$_POST['id'].'.'.substr($_POST['mime'],6);
$data = substr($_POST['data'], strpos($_POST['data'], ",") + 1);
$decodedData = base64_decode($data);

// write the data out to the file
$fp = fopen('../'.$fullname, 'wb');
fwrite($fp, $decodedData);
fclose($fp);

echo 'saved! '.$fullname;