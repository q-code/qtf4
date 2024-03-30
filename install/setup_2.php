<?php // v4.0 build:20240210
/**
 * @var string $strPrev
 * @var string $strNext
 * @var string $urlPrev
 * @var string $urlNext
 */
session_start();
include 'init.php';
$error = '';
$main = 'setup_2'; // main class (setup_hd)
$urlPrev = 'setup_1.php';
$urlNext = 'setup_3.php';
$tools = ''; if ( file_exists('tool_tables.php') ) $tools = '<a href="tool_tables.php">Tool tables...</a>';

// ------
// HTML BEGIN
// ------
include 'setup_hd.php';

if ( isset($_POST['ok']) ) {

  try {

    include '../bin/lib_qtf_base.php';
    if ( isset($_SESSION['qtf_dbopwd']) ) {
      $user = $_SESSION['qtf_dbologin'];
      $pwd = $_SESSION['qtf_dbopwd'];
    } else {
      $user = QDB_USER;
      $pwd = QDB_PWD;
    }
    $oDB = new CDatabase(QDB_SYSTEM,QDB_HOST,QDB_DATABASE,$user,$pwd);

    // Install the tables
    $out = 'A) '.L('Installation').' '.TABSETTING.'... ';
    include 'qtf_table_setting.php';
    $out .= L('Done').', '.L('Default_setting').'<br>';
    $out .= 'B) '.L('Installation').' '.TABDOMAIN.'... ';
    include 'qtf_table_domain.php';
    $out .= L('Done').', '.L('Default_domain').'<br>';
    $out .= 'C) '.L('Installation').' '.TABSECTION.'... ';
    include 'qtf_table_section.php';
    $out .= L('Done').', '.L('Default_section').'<br>';
    $out .= 'D) '.L('Installation').' '.TABTOPIC.'... ';
    include 'qtf_table_topic.php';
    $out .= L('Done').'<br>';
    $out .= 'E) '.L('Installation').' '.TABPOST.'... ';
    include 'qtf_table_post.php';
    $out .= L('Done').'<br>';
    $out .= 'F) '.L('Installation').' '.TABUSER.'... ';
    include 'qtf_table_user.php';
    $out .= L('Done').', '.L('Default_user').'<br>';
    $out .= 'G) '.L('Installation').' '.TABLANG.'... ';
    include 'qtf_table_lang.php';
    $out .= L('Done');
    echo '<p class="result">'.$out.'</p><p class="result ok">'.L('S_install').'</p>';
    $_SESSION['qtfInstalled'] = true;

    // save the url
    $strURL = ( empty($_SERVER['SERVER_HTTPS']) ? "http://" : "https://" ).$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    $strURL = substr($strURL,0,-20);
    $oDB->updSetting('site_url',$strURL,true);

  } catch (Exception $e) {

    $error = $e->getMessage();
    echo '<p class="result err">Problem to execute a query in database ['.QDB_DATABASE.'] on server ['.QDB_HOST.']<br>'.$error.'</p>';

  }

} else {

  echo '<form  method="post" name="install" action="setup_2.php" onsubmit="showWait()">
  <h1>'.L('Install_db').'</h1>
  <p class="italic">'.L('Not_install_on_upgrade').'</p>
  <br>
  <p><button type="submit" id="btn-create" name="ok" value="ok" onclick="return this.innerHTML!=msgWait">'.sprintf(L('Create_tables'),QDB_DATABASE).'</button></p>
  </form>
  ';

}
$aside = L('Help_2');

// ------
// HTML END
// ------
echo '<script type="text/javascript">
const msgWait = "Installing...";
function showWait(){
  document.body.style.cursor="wait";
  let d = document.getElementById("btn-create");
  if ( d ) d.innerHTML=msgWait;
}
</script>';

include 'setup_ft.php';