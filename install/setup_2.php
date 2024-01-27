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
$self = 'setup_2';
$urlPrev = 'setup_1.php';
$urlNext = 'setup_3.php';
$tools = ''; if ( file_exists('tool_tables.php') ) $tools = '<p class="tools"><a href="tool_tables.php">Tool tables...</a></p>';

// --------
// HTML BEGIN
// --------

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
    if ( !empty($oDB->error) ) throw new Exception( $oDB->error );

    // Install the tables
    $out = '';
    $table = TABSETTING;
    $out .= '<p>A) '.L('Installation').' '.$table.'... ';
    include 'qtf_table_setting.php';
    $out .= L('Done').', '.L('Default_setting').'<br>';
    $table = TABDOMAIN;
    $out .= 'B) '.L('Installation').' '.$table.'... ';
    include 'qtf_table_domain.php';
    $out .= L('Done').', '.L('Default_domain').'<br>';
    $table = TABSECTION;
    $out .= 'C) '.L('Installation').' '.$table.'... ';
    include 'qtf_table_section.php';
    $out .= L('Done').', '.L('Default_section').'<br>';
    $table = TABTOPIC;
    $out .= 'D) '.L('Installation').' '.$table.'... ';
    include 'qtf_table_topic.php';
    $out .= L('Done').'<br>';
    $table = TABPOST;
    $out .= 'E) '.L('Installation').' '.$table.'... ';
    include 'qtf_table_post.php';
    $out .= L('Done').'<br>';
    $table = TABUSER;
    $out .= 'F) '.L('Installation').' '.$table.'... ';
    include 'qtf_table_user.php';
    $out .= L('Done').', '.L('Default_user').'<br>';
    $table = TABLANG;
    $out .= 'G) '.L('Installation').' '.$table.'... ';
    include 'qtf_table_lang.php';
    $out .= L('Done').'</p>';
    if ( !empty($oDB->error) ) throw new Exception( $oDB->error );

    echo '<p>'.$out.'</p>
    <p class="is_ok">'.L('S_install').'</p>';

    $_SESSION['qtfInstalled'] = true;
    // save the url
    $strURL = ( empty($_SERVER['SERVER_HTTPS']) ? "http://" : "https://" ).$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    $strURL = substr($strURL,0,-24);
    $oDB->exec( 'UPDATE TABSETTING SET setting="'.$strURL.'" WHERE param="site_url"');

  } catch (Exception $e) {

    $error = $e->getMessage();
    echo '<p class="is_err">Problem to execute a query in database ['.QDB_DATABASE.'] on server ['.QDB_HOST.']<br>See the error message... '.$error.'</p>';

  }

}
else
{

  echo '<form method="post" name="install" action="setup_2.php" onsubmit="showWait()">
  <h1>'.L('Install_db').'</h1>
  <p>'.L('Upgrade2').'</p>
  <br>
  <p><button type="submit" id="btn-create" name="ok" value="ok" onclick="return this.innerHTML!=msgWait">'.sprintf(L('Create_tables'),QDB_DATABASE).'</button></p>
  </form>
  ';

}
$aside = L('Help_2');

// --------
// HTML END
// --------

echo '<script type="text/javascript">
const msgWait = "Installing...";
function showWait(){
  document.body.style.cursor="wait";
  let d = document.getElementById("btn-create");
  if ( d ) d.innerHTML=msgWait;
}
</script>';

include 'setup_ft.php';