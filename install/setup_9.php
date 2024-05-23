<?php // v4.0 build:20240210 allows app impersonation [qt f|i|e|n]
/**
 * @var string $strPrev
 * @var string $strNext
 * @var string $urlPrev
 * @var string $urlNext
 */
session_start();
include 'init.php';
$error = '';
$strPrev = L('Back');
$strNext = APPNAME;
$urlPrev = 'setup_4.php';
$urlNext = '../'.APP.'_login.php?dfltname=Admin';
$php = 'setup_9.php';
$tools = '';
if ( file_exists('tool_tables.php') ) $tools .= '<a href="tool_tables.php">Tool tables...</a>';
if ( file_exists('tool_check.php') ) $tools .= ' | <a href="tool_check.php">Check installation...</a>';

function redirect(string $u='exit', string $s='Continue')
{
  if ( empty($u) ) die('CHtml::redirect arg must be string');
  if ( headers_sent() ) {
    echo '<a href="'.$u.'">'.$s.'</a><meta http-equiv="REFRESH" content="0;url='.$u.'">';
  } else {
    header('Location: '.$u);
  }
  exit;
}
function qtEncrypt(string $key=APP, string $str='install')
{
  $key = hash('sha256', $key);
  $iv = substr(hash('sha256','5fgf5HJ5g27'), 0, 16); // sha256 is hash_hmac_algo
  $output = openssl_encrypt($str, 'AES-256-CBC', $key, 0, $iv);
  return base64_encode($output);
  // no need to decrypt (just check that encrypted folder exists)
}
function deleteDir(string $dirPath) {
  if ( ! is_dir($dirPath)) {
    throw new Exception( "$dirPath must be a directory" );
  }
  if ( substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
    $dirPath .= '/';
  }
  $files = glob($dirPath . '*', GLOB_MARK);
  foreach ($files as $file) {
    if ( is_dir($file)) {
      deleteDir($file);
    } else {
      unlink($file);
    }
  }
  rmdir($dirPath);
}

// ------
// HTML BEGIN
// ------
include 'setup_hd.php'; // this will show $error

// SUBMITTED

if ( isset($_POST['method']) )
{
  switch($_POST['method']) {
    case 'm0':
      redirect($urlNext);
      break;
    case 'm1':
      if ( rename('../install','../'.qtEncrypt()) ) {
        $_SESSION[QT.'splash'] = 'Install encrypted';
        redirect('../'.APP.'_index.php');
      } else {
        echo 'Unable to rename folder install !';
      }
      break;
    case 'm2':
      try {
        deleteDir('../install');
        $_SESSION[QT.'splash'] = 'Install deleted';
        redirect('../'.APP.'_index.php');
      } catch (Exception $e) {
        echo '<p class="result err">'.$e->getMessage().'</p>';
      }
      break;
  }
}

// Tables do drop

echo '<h1>'.L('Prevent_install').'</h1>';

echo '<p>'.L('Disable_install').'</p>';

echo '<form action="'.$php.'" method="post">';
echo '<p style="margin:10px 0"><input type="radio" id="m0" name="method" value="m0" checked>&nbsp;<label for="m0">'.L('Disable.0').'</label></p>';
echo '<p style="margin:10px 0"><input type="radio" id="m1" name="method" value="m1">&nbsp;<label for="m1">'.L('Disable.1').'</label></p>';
echo '<p style="margin:10px 0"><input type="radio" id="m2" name="method" value="m2">&nbsp;<label for="m2">'.L('Disable.2').'</label></p>';
echo '<p style="margin:10px 0"><button type="submit">'.L('Ok').'</button></p>';
echo '</form>';


// ------
// HTML END
// ------
include 'setup_ft.php'; // this will show $error

// DISCONNECT to reload new variables (keep same language)
$str = $_SESSION['setup_lang'];
$_SESSION = [];
$_SESSION['setup_lang'] = $str;