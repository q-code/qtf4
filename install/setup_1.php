<?php // v4.0 build:20230618
/**
 * @var string $strPrev
 * @var string $strNext
 * @var string $urlPrev
 * @var string $urlNext
 */
session_start();
include 'init.php';
$self = 'setup_1';
$tools = '<p class="tools"><a href="setup_1_tpl.php">Load from template...</a></p>';

// manipulate config values through $arr holding const defined in the file
$arr = [];
foreach(array('QDB_SYSTEM','QDB_HOST','QDB_DATABASE','QDB_PREFIX','QDB_USER','QDB_PWD','QDB_INSTALL') as $key) $arr[$key] = defined($key) ? constant($key) : '';
$urlPrev = 'setup.php';
$urlNext = 'setup_2.php';

// --------
// HTML BEGIN
// --------

include 'setup_hd.php'; // this will show $error

// --------
// SUBMITTED create sqlite file
// --------
if ( !empty($_GET['sqlite']) ) try {

  $arr['QDB_DATABASE'] = $_GET['sqlite'];
  if ( strpos($arr['QDB_DATABASE'],'.')===false || strlen($arr['QDB_DATABASE'])<4 ) throw new Exception( 'SQLite file extension missing. Recommanded extension: .db .sqlite3' );
  // create sqlitefile (path return to root)
  $oDB = new CDatabase($arr['QDB_SYSTEM'],'',$arr['QDB_DATABASE'],'','',true); // true to create sqlite file
  if ( !empty($oDB->error) ) throw new Exception( 'Unable to create SQLite database file: '.$arr['QDB_DATABASE'].'<br>'.$oDB->error );
  // end
  echo '<p class="is_ok">'.L('S_connect').'</p>';

} catch (Exception $e) {

  $error = $e->getMessage();
  echo '<p class="is_err">Unable to create SQLite database file: '.$arr['QDB_DATABASE'].'<br>'.$error.'</p>';

}

echo '<form method="post" name="install" action="setup_1.php">
';

// --------
// SUBMITTED connect
// --------
if ( isset($_POST['ok']) ) try {

  $arr['QDB_SYSTEM']   = trim($_POST['db_system']);
  $arr['QDB_HOST']     = trim($_POST['db_host']).(empty($_POST['db_port']) ? '' : ';port='.trim($_POST['db_port']));
  $arr['QDB_DATABASE'] = trim($_POST['db_database']);
  $arr['QDB_PREFIX']   = trim($_POST['db_prefix']);
  $arr['QDB_USER']     = trim($_POST['db_user']);
  $arr['QDB_PWD']      = trim($_POST['db_pwd']);
  if ( $arr['QDB_SYSTEM']=='pdo.sqlite' || $arr['QDB_SYSTEM']=='sqlite' ) {
    $arr['QDB_HOST']=''; // not used with SQLITE
    $arr['QDB_USER']='';
    $arr['QDB_PWD']='';
  }
  $str = trim($_POST['qtf_dbouser']); if ( $str!='') $_SESSION['qtf_dbouser'] = $str;
  $str = trim($_POST['qtf_dbopwd']); if ( $str!='') $_SESSION['qtf_dbopwd'] = $str;

  // Save Connection
  // Note: QDB_INSTALL is used to build the default memory namespace "qtf{n}" where n is the last character of QDB_INSTALL
  $date = date('Y-m-d');
  $str = '<?php
  const QDB_SYSTEM = "'.$arr['QDB_SYSTEM'].'";
  const QDB_HOST = "'.$arr['QDB_HOST'].'";
  const QDB_DATABASE = "'.$arr['QDB_DATABASE'].'";
  const QDB_PREFIX = "'.$arr['QDB_PREFIX'].'";
  const QDB_USER = "'.$arr['QDB_USER'].'";
  const QDB_PWD = "'.$arr['QDB_PWD'].'";
  const QDB_INSTALL = "'.$date.' '.APP.substr($date,-1).'";'; // default memory namespace is "qtf{n}"
  $error = saveToFile('../config/config_db.php',$str); // SAVE TO FILE
  if ( !empty($error) ) throw new Exception( L('E_save').'<br>'.$error );
  echo '<p class="is_ok">'.L('S_save').'</p>';

  // Test Connection
  if ( $arr['QDB_SYSTEM']=='pdo.sqlite' || $arr['QDB_SYSTEM']=='sqlite' )
  {
    // for sqlite, check filename insead of connect()
    if ( !file_exists('../'.$arr['QDB_DATABASE']) ) throw new Exception( '<p>SQLite database file not found: '.$arr['QDB_DATABASE'].'</p><p><a href="setup_1.php?sqlite='.$arr['QDB_DATABASE'].'">Create SQLite file ['.$arr['QDB_DATABASE'].']...</a></p>' );
  }
  else
  {
    if ( isset($_SESSION['qtf_dbologin']) )
    {
    $oDB = new CDatabase($arr['QDB_SYSTEM'],$arr['QDB_HOST'],$arr['QDB_DATABASE'],$_SESSION['qtf_dbologin'],$_SESSION['qtf_dbopwd']);
    }
    else
    {
    $oDB = new CDatabase($arr['QDB_SYSTEM'],$arr['QDB_HOST'],$arr['QDB_DATABASE'],$arr['QDB_USER'],$arr['QDB_PWD']);
    }
    if ( !empty($oDB->error) ) throw new Exception( sprintf(L('E_connect'),$arr['QDB_DATABASE'],$arr['QDB_HOST']).'<br>'.$oDB->error );
  }
  // Test Result
  echo '<p class="is_ok">'.L('S_connect').'</p>';

} catch (Exception $e) {

  $error = $e->getMessage();
  echo '<p class="is_err">'.$error.'</p>';

}

// --------
// FORM
// --------

echo '<h1>'.L('Connection_db').'</h1>
<table class="t-conn">
';
echo '<tr>
<td>',L('Database_type'),'</td>
<td><select name="db_system" onchange="toggleHostLogin(this.value)">
<optgroup label="PDO connectors">
<option value="pdo.mysql"',($arr['QDB_SYSTEM']==='pdo.mysql' ? ' selected' : ''),'>MariaDb/MySQL (5 or next)</option>
<option value="pdo.sqlsrv"',($arr['QDB_SYSTEM']==='pdo.sqlsrv' ? ' selected' : ''),'>SQL sever (or Express)</option>
<option value="pdo.pg"',($arr['QDB_SYSTEM']==='pdo.pg' ? ' selected' : ''),'>PostgreSQL</option>
<option value="pdo.sqlite"',($arr['QDB_SYSTEM']==='pdo.sqlite' ? ' selected' : ''),'>SQLite</option>
<option value="pdo.oci"',($arr['QDB_SYSTEM']==='pdo.oci' ? ' selected' : ''),'>Oracle</option>
</optgroup>
<optgroup label="Legacy connectors">
<option value="mysql"',($arr['QDB_SYSTEM']==='mysql' ? ' selected' : ''),'>MySQL 5 or next</option>
<option value="sqlsrv"',($arr['QDB_SYSTEM']==='sqlsrv' ? ' selected' : ''),'>SQL server (or Express)</option>
<option value="pg"'.($arr['QDB_SYSTEM']==='pg' ? ' selected' : ''),'>PostgreSQL</option>
<option value="sqlite"'.($arr['QDB_SYSTEM']==='sqlite' ? ' selected' : ''),'>SQLite</option>
<option value="oci"',($arr['QDB_SYSTEM']==='oci' ? ' selected' : ''),'>Oracle</option>
</optgroup>
</select></td>
</tr>
';
// explode host and port if port exists
if ( strpos($arr['QDB_HOST'],';port=')!==false ) { $parts = explode(';port=',$arr['QDB_HOST'],2); $arr['QDB_HOST']=$parts[0]; $arr['QDB_PORT']=$parts[1];}
echo '<tr id="db-host"',($arr['QDB_SYSTEM']==='pdo.sqlite' || $arr['QDB_SYSTEM']==='sqlite' ? ' style="display:none"' : ''),'>
<td>',L('Database_host'),'</td>
<td>
<input type="text" name="db_host" value="',$arr['QDB_HOST'],'" size="26" maxlength="255" placeholder="localhost"/>
<input type="text" name="db_port" value="',(empty($arr['QDB_PORT']) ? '' : $arr['QDB_PORT']),'" size="4" maxlength="255" placeholder="port"/>
</td>
</tr>
<tr>
<td>',L('Database_name'),'</td>
<td><input type="text" name="db_database" value="',$arr['QDB_DATABASE'],'" size="15" maxlength="255"/></td>
</tr>
<tr>
<td>',L('Table_prefix'),'</td>
<td><input type="text" name="db_prefix" value="',$arr['QDB_PREFIX'],'" size="15" maxlength="100"/></td>
</tr>
<tr id="user-login"',($arr['QDB_SYSTEM']==='pdo.sqlite' || $arr['QDB_SYSTEM']==='sqlite' ? ' style="display:none"' : ''),'>
<td>Database user/password</td>
<td>
<input type="text" name="db_user" value="',$arr['QDB_USER'],'" size="15" maxlength="255" placeholder="username"/>
<input type="text" name="db_pwd" value="',$arr['QDB_PWD'],'" size="15" maxlength="255" placeholder="password"/>
</td>
</tr>
<tr id="dbo-login-info"',($arr['QDB_SYSTEM']==='pdo.sqlite' || $arr['QDB_SYSTEM']==='sqlite' ? ' style="display:none"' : ''),'>
<td colspan="2" style="background-color:#ddd">',L('Htablecreator'),'</td>
</tr>
<tr id="dbo-login"',($arr['QDB_SYSTEM']==='pdo.sqlite' || $arr['QDB_SYSTEM']==='sqlite' ? ' style="display:none"' : ''),'>
<td style="background-color:#ddd">Table creator/password</td>
<td style="background-color:#ddd">
<input type="text" name="qtf_dbouser" value="',(isset($_SESSION['qtf_dbouser']) ? $_SESSION['qtf_dbouser'] : ''),'" size="15" maxlength="255" placeholder="username"/>
<input type="text" name="qtf_dbopwd" value="',(isset($_SESSION['qtf_dbopwd']) ? $_SESSION['qtf_dbopwd'] : ''),'" size="15" maxlength="255" placeholder="password"/>
</td>
</tr>
<tr>
<td colspan="2">&nbsp;</td>
</tr>
<tr>
<td colspan="2" style="text-align:center"><button type="submit" name="ok" value="save">',L('Save'),'</button></td>
</tr>
</table>
</form>
';

$aside = L('Help_1');

// --------
// HTML END
// --------

echo '<script type="text/javascript">
function toggleHostLogin(str) {
let d = document.getElementById("db-host");
if ( d ) d.style.display = str=="sqlite" || str=="pdo.sqlite" ? "none" : "table-row";
d = document.getElementById("user-login");
if ( d ) d.style.display = str=="sqlite" || str=="pdo.sqlite" ? "none" : "table-row";
d = document.getElementById("dbo-login-info");
if ( d ) d.style.display = str=="sqlite" || str=="pdo.sqlite" ? "none" : "table-row";
d = document.getElementById("dbo-login");
if ( d ) d.style.display = str=="sqlite" || str=="pdo.sqlite" ? "none" : "table-row";
}
</script>';

include 'setup_ft.php';