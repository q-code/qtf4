<?php // V4.0 build:20240210

$root = '../';
define('THISAPPNAME', 'QuickTalk forum');

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" xml:lang="en" lang="en">
<head>
<title>'.THISAPPNAME.' installation checker</title>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
<link rel="shortcut icon" href="src/qt.ico" />
<style type="text/css">*{box-sizing:border-box;margin:0}
header,main{margin:1rem auto;width:650px;font-family:system-ui,sans-serif}
header{padding:0.5rem;color:inherit;background:linear-gradient(to bottom,#0C4C8C 20%,#156AC2)}
footer{padding-top:0.75rem;text-align:center}
h1{margin:0.5rem 0 0.4rem 0;font-size:1.4rem}
a{color:#0000FF}
.end{margin:5px;padding:4px;border:solid 1px #aaa;border-radius:5px}
.ok{color:#00aa00; background-color:inherit}
.nok{color:#ff0000; background-color:inherit}
</style>
</head>
<body>
';
echo '<header>
<img id="logo" src="src/qtf_logo.gif" width="175" height="50" style="border-width:0" alt="'.THISAPPNAME.'" title="'.THISAPPNAME.'"/>
</header>
<main>
';

//======
try {
//======

// ------
// 1 CONFIG
// ------
echo '
<h1>Checking your configuration</h1>
';
echo '<p>'.THISAPPNAME.' 4.0 build:20240210</p>';
$result = true;

// 1 file exist
$error = '';
echo '<p>Checking installed files... ';
if ( !file_exists($root.'config/config_db.php') ) $error .= 'File config_db.php is not in the config directory. Communication with database is impossible. ';
if ( !file_exists($root.'bin/init.php') ) $error .= 'File init.php is not in the bin directory. Application cannot start. ';
if ( !file_exists($root.'bin/lib_qt_core.php') ) $error .= 'File lib_qt_core.php is not in the bin directory. Application cannot start. ';
if ( !file_exists($root.'bin/lib_qtf_base.php') ) $error .= 'File lib_qtf_base.php is not in the bin directory. Application cannot start. ';
if ( !file_exists($root.'bin/class/class.qt.db.php') ) $error .= 'File class.qt.db.php is not in the bin/class directory. Application cannot start. ';
if ( !file_exists($root.'bin/class/class.qt.core.php') ) $error .= 'File class.qt.core.php is not in the bin/class directory. Application cannot start. ';
if ( !file_exists($root.'bin/class_qtf_section.php') ) $error .= 'File class_qtf_section.php is not in the bin/class directory. Application cannot start. ';
if ( !file_exists($root.'bin/class_qtf_topic.php') ) $error .= 'File class_qtf_topic.php is not in the bin/class directory. Application cannot start. ';
if ( !file_exists($root.'bin/class_qtf_post.php') ) $error .= 'File class_qtf_post.php is not in the bin/class directory. Application cannot start. ';
if ( empty($error) ) {
  echo '<span class="ok">Main files found.</span></p>';
} else {
  echo '</p>';
  throw new Exception( $error );
}

// 2 config is correct
echo '<p>Checking config folder... ';
include $root.'config/config_db.php'; $database = strpos(QDB_SYSTEM,'sqlite') ? $root.QDB_DATABASE : QDB_DATABASE; // using SQLite, database file is in the root directory
include $root.'config/config_cst.php';
include $root.'bin/lib_qtf_base.php';
if ( !defined('QDB_SYSTEM') )   throw new Exception( 'Variable QDB_SYSTEM is not defined in the file config/config_db.php. Communication with database is impossible.');
if ( !defined('QDB_HOST') )     throw new Exception( 'Variable QDB_HOST is not defined in the file config/config_db.php. Communication with database is impossible.');
if ( !defined('QDB_DATABASE') ) throw new Exception( 'Variable QDB_DATABASE is not defined in the file config/config_db.php. Communication with database is impossible.');
if ( !defined('QDB_PREFIX') )   throw new Exception( 'Variable QDB_PREFIX is not defined in the file config/config_db.php. Communication with database is impossible.');
if ( !defined('QDB_USER') )     throw new Exception( 'Variable QDB_USER is not defined in the file config/config_db.php. Communication with database is impossible.');
if ( !defined('QDB_PWD') )      throw new Exception( 'Variable QDB_PWD is not defined in the file config/config_db.php. Communication with database is impossible.');
if ( !in_array(QDB_SYSTEM, ['pdo.mysql','mysql','pdo.sqlsrv','sqlsrv','pdo.pg','pg','pdo.sqlite','sqlite','pdo.oci','oci']) ) throw new Exception('Unknown db type '.QDB_SYSTEM);
if ( empty(QDB_DATABASE) )  throw new Exception( 'Variable QDB_DATABASE is not defined in the file config/config_db.php. Communication with database is impossible.');
echo '<span class="ok">Done.</span></p>';

// 3 test db connection
echo '<p>Connecting to database... ';
include $root.'bin/class/class.qt.db.php';
$oDB = new CDatabase(QDB_SYSTEM, QDB_HOST, $database, QDB_USER, QDB_PWD);
echo '<span class="ok">Done.</span></p>';

// end CONFIG tests
echo '<p class="end">Configuration tests completed successfully.</p>';

// ------
// 2 DATABASE
// ------
echo '
<h1>Checking your database</h1>
';

// 0 setting table
echo '<p>Checking setting table... ';
$oDB->query( 'SELECT setting FROM '.QDB_PREFIX.'qtasetting WHERE param="version"');
$row = $oDB->getRow();
$version = $row['setting'];
echo '<span class="ok">Table ['.QDB_PREFIX.'qtasetting] exists. Version: '.$version.'.</span>';
if ( substr($version,0,3)!=='4.0' ) throw new Exception('Database version is incompatible (must be version 4.x)');
echo '</p>';

// 1 lang table
echo '<p>Checking lang table... ';
$intCount = $oDB->count( QDB_PREFIX.'qtalang' );
echo '<span class="ok">Table ['.QDB_PREFIX.'qtalang] exists. '.$intCount.' translation(s) found.</span></p>';

// 2 domain table
echo '<p>Checking domain table... ';
$intCount = $oDB->count( QDB_PREFIX.'qtadomain' );
echo '<span class="ok">Table ['.QDB_PREFIX.'qtadomain] exists. '.$intCount.' domain(s) found.</span></p>';

// 3 section table
echo '<p>Checking forum table...';
$intCount = $oDB->count( QDB_PREFIX.'qtaforum' );
echo '<span class="ok">Table ['.QDB_PREFIX.'qtaforum] exists. '.$intCount.' section(s) found.</span></p>';

// 4 topic table
echo '<p>Checking topic table...';
$intCount = $oDB->count( QDB_PREFIX.'qtatopic' );
echo '<span class="ok">Table ['.QDB_PREFIX.'qtatopic] exists. '.$intCount.' topic(s) found.</span></p>';

// 5 post table
echo '<p>Checking post table...';
$intCount = $oDB->count( QDB_PREFIX.'qtapost' );
echo '<span class="ok">Table ['.QDB_PREFIX.'qtapost] exists. '.$intCount.' post(s) found.</span></p>';

// 6 user table
echo '<p>Checking user table... ';
$intCount = $oDB->count( QDB_PREFIX.'qtauser' );
echo '<span class="ok">Table ['.QDB_PREFIX.'qtauser] exists. '.$intCount.' user(s) found.</span></p>';

// end DATABASE tests
echo '<p class="end">Database tests completed successfully.</p>';

// ------
// 3 LANGUAGE AND SKIN
// ------
echo '
<h1>Checking language and skin options</h1>
';
echo '<p>Files... ';
$error = '';

// languages
$oDB->query( 'SELECT setting FROM '.QDB_PREFIX.'qtasetting WHERE param="language"');
$row = $oDB->getRow();
$str = $row['setting'];
if ( empty($str) ) $error .= 'Setting language is not defined in the setting table. Application can only work with english. ';
if ( !file_exists($root."language/$str/lg_main.php") ) $error .= "File lg_main.php is not in the language/xx directory. ";
if ( !file_exists($root."language/$str/lg_adm.php") )  $error .= "File lg_adm.php is not in the language/xx directory. ";
if ( !file_exists($root."language/$str/lg_icon.php") ) $error .= "File lg_icon.php is not in the language/xx directory. ";
if ( !file_exists($root."language/$str/lg_reg.php") )  $error .= "File lg_reg.php is not in the language/xx directory. ";
if ( !file_exists($root."language/$str/lg_zone.php") ) $error .= "File lg_zone.php is not in the language/xx directory. ";
if ( $str!='english' ) {
  if ( !file_exists($root."language/en/lg_main.php") ) $error .= "File lg_main.php is not in the language/en directory. English language is mandatory. ";
  if ( !file_exists($root."language/en/lg_adm.php") )  $error .= "File lg_adm.php is not in the language/en directory. English language is mandatory. ";
  if ( !file_exists($root."language/en/lg_icon.php") ) $error .= "File lg_icon.php is not in the language/en directory. English language is mandatory. ";
  if ( !file_exists($root."language/en/lg_reg.php") )  $error .= "File lg_reg.php is not in the language/en directory. English language is mandatory. ";
  if ( !file_exists($root."language/en/lg_zone.php") ) $error .= "File lg_zone.php is not in the language/en directory. English language is mandatory. ";
}

// skins
$oDB->query( 'SELECT setting FROM '.QDB_PREFIX.'qtasetting WHERE param="skin_dir"');
$row = $oDB->getRow();
$str = $row['setting']; if ( substr($str,0,5)!=='skin/' ) $str = 'skin/'.$str;
if ( empty($str) ) $error .= 'Setting skin< is not defined in the setting table. Application will not display correctly.<br>';
if ( !file_exists($root."$str/qtf_styles.css") ) $error .= "File qtf_styles.css< is not in the $str< directory.<br>";
if ( !file_exists($root."skin/default/qtf_styles.css") ) $error .= 'File qtf_styles.css< is not in the skin/default< directory. Default skin is mandatory.<br>';
if ( empty($error) ) {
  echo '<span class="ok">Done.</span>';
} else {
  echo '<span class="nok">'.$error.'</span>';
}
echo '</p>';

// end LANGUAGE AND SKIN
echo '<p class="end">Language and skin files tested.</p>';

// ------
// 4 ADMINISTRATION TIPS
// ------
echo '
<h1>Administration tips</h1>
';

// 1 admin email
echo '<p>Email setting... ';
$error = '';
$oDB->query( 'SELECT setting FROM '.QDB_PREFIX.'qtasetting WHERE param="admin_email"');
$row = $oDB->getRow();
$strMail = $row['setting'];
if ( empty($strMail) ) {
  $error .= 'Administrator e-mail is not yet defined. It\'s mandatory to define it.';
} else {
  if ( !preg_match("/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i",$strMail) ) $error .= 'Administrator e-mail format seams incorrect. Please tool_check it';
}
if ( empty($error) ) {
  echo '<span class="ok">Done.</span></p>';
} else {
  echo '<span class="nok">'.$error.'</span></p>';
}

// 2 admin password
echo '<p>Security check... <span class="ok">Done.</span><br>';
$oDB->query( 'SELECT pwd FROM '.QDB_PREFIX.'qtauser WHERE id=1');
$row = $oDB->getRow();
$strPwd = $row['pwd'];
if ( $strPwd===sha1('Admin') ) echo '<span class="nok">Administrator password is still the initial password. It\'s recommended to change it.</span><br>';
if ( is_dir($root.'install') ) echo '<span class="nok">Install folder must be encrypted or removed.</span><br>';
echo '</p>';

// 3 site url
echo '<p>Site url... ';
$error = '';
$oDB->query( 'SELECT setting FROM '.QDB_PREFIX.'qtasetting WHERE param="site_url"');
$row = $oDB->getRow();
$strText = trim($row['setting']);
echo '<span class="ok">'.$strText.'</span><br>';
if ( substr($strText,0,7)!=='http://' && substr($strText,0,8)!=='https://' ) {
  $error .= 'Site url is not yet defined (or not starting by http://). It\'s mandatory to define it!<br>';
} else {
  $strURL = 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 's' : '').'://'.$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
  if ( strpos($strURL,$strText)===false ) {
    if ( substr($strURL,0,5)!==substr($strText,0,5) ) $error .= 'Site is registered with '.substr($strText,0,5).' while current protocol is '.substr($strURL,0,5).'... ';
    $error .= 'Site url seams to be different that the current url. Please check it<br>';
  }
}
if ( !empty($error) ) echo '<span class="nok">'.$error.'</span>';
echo '</p>';

// 4 avatar folder permission
echo '<p>Folder permissions... ';
$error = '';
if ( !is_dir($root.'avatar') ) {
  $error .= 'Directory avatar not found. Please create this directory and make it writeable (chmod 777) if you want to allow avatars. ';
} else {
  if ( !is_readable($root.'avatar') ) $error .= 'Directory avatar is not readable.</font> Change permissions (chmod 777) if you want to allow avatars. ';
  if ( !is_writable($root.'avatar') ) $error .= 'Directory avatar is not writable.</font> Change permissions (chmod 777) if you want to allow avatars. ';
}
if ( empty($error) ) {
  echo '<span class="ok">Done.</span></p>';
} else {
  echo '<span class="nok">'.$error.'</span></p>';
}

// end ADMINSTRATION
echo '<p class="end">Administration tips completed.</p>';

// board status warning
$oDB->query( 'SELECT setting FROM '.QDB_PREFIX.'qtasetting WHERE param="board_offline"');
$row = $oDB->getRow();
if ( $row['setting']==='1' ) $warning = 'Your board is currently <font color="red">off-line</font>. Log as Administrator and go to the Administration panel to turn your board on-line.';

//======
} catch (Exception $e) {
//======

echo '<p class="nok">'.$e->getMessage().'</p>';
$result = false;

//======
}
//======

echo '
<h1>Result</h1>
';
echo '<p>The checker did '.($result ? 'not ' : '').'found blocking issues in your configuration.</p>';
if ( !empty($warning) ) echo '<p>'.$warning.'</p>';

// ------
// HTML END
// ------
$menu = [];
if ( $result && is_dir($root.'install') ) $menu[] = '<a href="setup_9.php">Secure your installation</a>';
if ( $result && file_exists('tool_tables.php') ) $menu[] = '<a href="tool_tables.php">Tool tables</a>';
$menu[] = '<a href="setup.php">Install</a>';
$menu[] = '<a href="'.$root.'qtf_index.php">Go to '.THISAPPNAME.'</a>';
echo '<footer>'.implode(' | ', $menu).'</footer>';
echo '
</main>
</body>
</html>';