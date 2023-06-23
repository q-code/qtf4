<?php // v4.0 build:20230618
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
$urlPrev = APP.'_setup_4.php';
$urlNext = '../qtf_login.php?dfltname=Admin';
$selfurl = APP.'_setup_9.php';

function redirect(string $u, string $s='Continue')
{
  if ( empty($u) ) die('CHtml::redirect arg must be string');
  if ( headers_sent() )
  {
    echo '<a href="'.$u.'">',$s,'</a><meta http-equiv="REFRESH" content="0;url='.$u.'">';
  }
  else
  {
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
  // to decrypt use; return openssl_decrypt(base64_decode($str), 'AES-256-CBC', $key, 0, $iv);
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

// --------
// HTML BEGIN
// --------

include APP.'_setup_hd.php'; // this will show $error

echo '<p style="text-align:right">';
if ( file_exists('tool_tables.php') ) echo '<a href="tool_tables.php">Tool tables...</a>';
if ( file_exists('tool_check.php') ) echo ' | <a href="tool_check.php">Check installation...</a>';
echo '</p>';

// SUBMITTED

if ( isset($_POST['method']) )
{
  switch($_POST['method'])
  {
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
        echo '<div class="setup_err">'.$e->getMessage().'</div>';
      }
      break;
  }
}

// Tables do drop

echo '<h2>'.L('Prevent_install').'</h2>';

echo '<p>'.L('Disable_install').'</p>';

echo '<div style="margin:20px"><form action="'.$selfurl.'" method="post">';
echo '<p style="margin:10px 0"><input type="radio" id="m0" name="method" value="m0" checked>&nbsp;<label for="m0">'.L('Disable.0').'</label></p>';
echo '<p style="margin:10px 0"><input type="radio" id="m1" name="method" value="m1">&nbsp;<label for="m1">'.L('Disable.1').'</label></p>';
echo '<p style="margin:10px 0"><input type="radio" id="m2" name="method" value="m2">&nbsp;<label for="m2">'.L('Disable.2').'</label></p>';
echo '<p style="margin:10px 0"><button type="submit">'.L('Ok').'</button></p>';
echo '</form>
</div>';


// --------
// HTML END
// --------
include APP.'_setup_ft.php'; // this will show $error

// DISCONNECT to reload new variables (keep same language)
$str = $_SESSION[APP.'_setup_lang'];
$_SESSION = [];
$_SESSION[APP.'_setup_lang']=$str;
