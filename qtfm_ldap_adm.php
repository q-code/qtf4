<?php // v4.0 build:20230205 can be app impersonated {qt f|e|i}

/**
 * @package    QuickTalk
 * @author     Philippe Vandenberghe <info@qt-cute.org>
 * @copyright  2012 The PHP Group
 * @version    4.0 build:20230205
 */

session_start();
require 'bin/init.php';
/**
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 */
if ( SUser::role()!=='A' ) die('Access is restricted to administrators only');
include translate('lg_adm.php');
include APP.'m_ldap_lib.php';

// INITIALISE

$pan=0;
$oH->selfurl = APP.'m_ldap_adm.php';
$oH->selfname = 'LDAP/AD stettings';
$oH->selfparent = L('Module');
$oH->exiturl = APP.'_adm_secu.php';

qtHttp('int:pan');
if ( $pan<0 || $pan>2) $pan=0;

if ( !isset($_SESSION[QT]['m_ldap']) ) $_SESSION[QT]['m_ldap']='0';
if ( !isset($_SESSION[QT]['login_addon']) ) $_SESSION[QT]['login_addon']='0';

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) && $pan==0 )
{
  $oDB->exec( "DELETE FROM TABSETTING WHERE param='m_ldap' OR param='m_ldap_users'" );

  $_SESSION[QT]['m_ldap_users'] = $_POST['m_ldap_users']; if ( $_SESSION[QT]['m_ldap_users']!=='ldap' ) $_SESSION[QT]['m_ldap_users']='all';
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_ldap_users','".$_SESSION[QT]['m_ldap_users']."')" );

  // Enable module (m_ldap=0 means disable, m_ldap=qtfm_ldap_login.php means enabled
  $_SESSION[QT]['m_ldap']='0';
  if ( $_POST['m_ldap']=='1' )
  {
    if ( empty($_SESSION[QT]['m_ldap_host']) ) $oH->error = 'First defined Ldap settings';
    if ( !function_exists('ldap_connect') ) $oH->error = 'Ldap function not found, unable to start the module.';
    if ( empty($oH->error) ) $_SESSION[QT]['m_ldap']='1';

  }
  $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_ldap','".$_SESSION[QT]['m_ldap']."')" );

  // exit
  $_SESSION[QT.'splash'] = empty($oH->error) ? L('S_save') : 'E|'.$oH->error;
}

if ( isset($_POST['ok']) && $pan>0 )
{
  // register value used
  $_SESSION[QT]['m_ldap_host'] = $_POST['m_ldap_host'];
  $_SESSION[QT]['m_ldap_login_dn'] = $_POST['m_ldap_login_dn'];
  $_SESSION[QT]['m_ldap_bind'] = (isset($_POST['m_ldap_bind']) ? 'a' : 'n');
  $_SESSION[QT]['m_ldap_bind_rdn'] = '';
  $_SESSION[QT]['m_ldap_bind_pwd'] = '';
  if ( $_SESSION[QT]['m_ldap_bind']==='n' && !empty($_POST['m_ldap_bind_rdn']) ) $_SESSION[QT]['m_ldap_bind_rdn'] = $_POST['m_ldap_bind_rdn'];
  if ( $_SESSION[QT]['m_ldap_bind']==='n' && !empty($_POST['m_ldap_bind_pwd']) ) $_SESSION[QT]['m_ldap_bind_pwd'] = $_POST['m_ldap_bind_pwd'];
  $_SESSION[QT]['m_ldap_s_rdn'] = (empty($_POST['m_ldap_s_rdn']) ? '' : $_POST['m_ldap_s_rdn']);
  $_SESSION[QT]['m_ldap_s_filter'] = (empty($_POST['m_ldap_s_filter']) ? '' : $_POST['m_ldap_s_filter']);
  $_SESSION[QT]['m_ldap_s_info'] = (empty($_POST['m_ldap_s_info']) ? '' : $_POST['m_ldap_s_info']);

  // Save
  if ( $pan>0 )
  {
    $oDB->exec( "DELETE FROM TABSETTING WHERE param='m_ldap_host' OR param='m_ldap_login_dn' OR param='m_ldap_bind' OR param='m_ldap_bind_rdn' OR param='m_ldap_bind_pwd' OR param='m_ldap_s_rdn' OR param='m_ldap_s_filter' OR param='m_ldap_s_info'" );
    $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_ldap_host','".$_SESSION[QT]['m_ldap_host']."')" );
    $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_ldap_login_dn','".$_SESSION[QT]['m_ldap_login_dn']."')" );
    $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_ldap_bind','".$_SESSION[QT]['m_ldap_bind']."')" );
    $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_ldap_bind_rdn','".$_SESSION[QT]['m_ldap_bind_rdn']."')" );
    $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_ldap_bind_pwd','".$_SESSION[QT]['m_ldap_bind_pwd']."')" );
    $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_ldap_s_rdn','".$_SESSION[QT]['m_ldap_s_rdn']."')" );
    $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_ldap_s_filter','".$_SESSION[QT]['m_ldap_s_filter']."')" );
    $oDB->exec( "INSERT INTO TABSETTING (param,setting) VALUES ('m_ldap_s_info','".$_SESSION[QT]['m_ldap_s_info']."')" );
    // exit
    $_SESSION[QT.'splash'] = empty($oH->error) ? L('S_save') : 'E|'.$oH->error;
  }

  // TEST

  if ( $pan==2 ) try {

    $test_conn='<span style="color:red">pending</span>';
    $test_find='<span style="color:red">pending</span>';
    $test_login='<span style="color:red">pending</span>';

    if ( empty($_SESSION[QT]['m_ldap_host']) ) throw new Exception( 'Missing host' );
    if ( empty($_SESSION[QT]['m_ldap_login_dn']) )throw new Exception( 'Missing login dn' );

    $username = $_POST['username'];
    $password = $_POST['password'];
    if ( empty($username) ) throw new Exception( 'Missing username' );

    $intEntries=0;

    // open connection
    $c = @ldap_connect($_SESSION[QT]['m_ldap_host']);
    if ( $c===false) throw new Exception( 'Unable to connect ldap service' );

    // admin(anonymous) bind
    $test_conn='<span style="color:green">started</span>';
    $test_find='<span style="color:red">failed</span>';
    $test_login='<span style="color:red">access denied</span>';
    ldap_set_option($c, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($c, LDAP_OPT_REFERRALS, 0);
    if ( $_SESSION[QT]['m_ldap_bind']==='n' ) {
      $b = @ldap_bind($c,$_SESSION[QT]['m_ldap_bind_rdn'],$_SESSION[QT]['m_ldap_bind_pwd']); // bind (anonymous by default)
    } else {
      $b = @ldap_bind($c); // bind (anonymous by default)
    }
    if ( $b===false) throw new Exception( 'Connection result: '.ldap_err2str(ldap_errno($c)) );

    // search test user
    $filter = str_replace('$username',$username,$_SESSION[QT]['m_ldap_s_filter']);
    $s = @ldap_search($c,$_SESSION[QT]['m_ldap_s_rdn'],$filter,explode(',',$_SESSION[QT]['m_ldap_s_info']));
    if ( $s===false) throw new Exception( 'Search result: '.ldap_err2str(ldap_errno($c)) );

    // analyse search results
    $intEntries = ldap_count_entries($c,$s);
    $test_find='<span style="color:green">'.$intEntries.' matching entry</span>';
    $users = ldap_get_entries($c, $s);
    $infos = explode(',',$_SESSION[QT]['m_ldap_s_info']);
    $results = array();
    for($i=0;$i<$intEntries;$i++)
    {
      $results[$i] = '';
      foreach($infos as $info)
      {
        if ( isset($users[$i][$info]) ) { $results[$i] .= $info.'='.$users[$i][$info][0].','; } else { $results[$i] .= '(missing '.$info.'),'; }
      }
      if ( $i>=1 ) { $results[]='...'; break; }
    }
    if ( $intEntries>0 ) $test_find .= '<br><span class="small">&gt; '.implode('<br>&gt; ',$results).'</span>';

    // bind test user
    $b = qt_ldap_bind($username,$password);
    if ( $b )
    {
      $test_login='<span style="color:green">successfull</span>';
    }
    else
    {
      $test_login='<span style="color:red">denied</span>';
      $test_login.='<br><span class="small" style="color:red">&gt; '.$oH->error.'<br>&gt; Possible cause: '.($intEntries==0 ? 'the username does not exists (or is not in the group specied by the login DN)' : 'username exists but the password is invalid') .'</span>';
    }

    // Successfull end
    ldap_close($c);
    $_SESSION[QT.'splash'] = 'Test completed';

  } catch (Exception $e) {

    $oH->error = $e->getMessage();
    $_SESSION[QT.'splash'] = 'E|'.$e->getMessage();

  }

}

// --------
// HTML BEGIN
// --------

if ( !isset($_SESSION[QT]['m_ldap_host']) ) $_SESSION[QT]['m_ldap_host']='';
if ( !isset($_SESSION[QT]['m_ldap_login_dn']) ) $_SESSION[QT]['m_ldap_login_dn']='';
if ( !isset($_SESSION[QT]['m_ldap_bind']) ) $_SESSION[QT]['m_ldap_bind']='a';
if ( !isset($_SESSION[QT]['m_ldap_bind_rdn']) ) $_SESSION[QT]['m_ldap_bind_rdn']='';
if ( !isset($_SESSION[QT]['m_ldap_bind_pwd']) ) $_SESSION[QT]['m_ldap_bind_pwd']='';
if ( !isset($_SESSION[QT]['m_ldap_s_rdn']) ) $_SESSION[QT]['m_ldap_s_rdn']='';
if ( !isset($_SESSION[QT]['m_ldap_s_filter']) ) $_SESSION[QT]['m_ldap_s_filter']='';
if ( !isset($_SESSION[QT]['m_ldap_s_info']) ) $_SESSION[QT]['m_ldap_s_info']='mail';
if ( !isset($_SESSION[QT]['m_ldap_users']) ) $_SESSION[QT]['m_ldap_users']='all';

include APP.'_adm_inc_hd.php';

// DISPLAY TABS
$arrM = array();
foreach(['Authority','Settings','Test'] as $k=>$str)
$arrM['pan-'.$k] = $str.'|href='.$oH->selfurl.'?pan='.$k.'|id=pan-'.$k.'|class=pan-tab';
$m = new CMenu($arrM, '');
echo '<div class="pan-tabs">'.$m->build('pan-'.$pan).'</div>';

// DISPLAY TAB PANEL
echo '<div class="pan">
<style>.config.ldap th{width:110px}</style>
<p class="pan-title">'.$m->get('pan-'.$pan).'</p>
';

if ( !function_exists('ldap_connect') ) echo '<p class="error">LDAP function not found. It seems that module LDAP is not activated on your webserver.</p>';

// Use java qtFormSafe only on pannel 0
if ( $pan==0 )
{

if ( $_SESSION[QT]['login_addon']==='0')
{
echo '<p>Current authority is <span class="bold italic">Internal authority (default)</span>.<br>When module is on-line, change the authority in the page <a href="qtf_adm_secu.php" onclick="return qtFormSafe.exit(e0);">'.L('Board_security').'</a>.</p><br>';
}
echo '<form method="post" action="'.Href($oH->selfurl).'">
<h2 class="subconfig">Module status</h2>

<table class="t-conf ldap">
<tr>
<th>Status</th>
<td style="width:100px"><span style="display:inline-block;width:16px;background-color:'.( $_SESSION[QT]['m_ldap']==='1' ? 'green' : 'red').';border-radius:3px">&nbsp;</span>&nbsp;'.L(($_SESSION[QT]['m_ldap']==='1' ? 'On' : 'Off').'_line').'</td>
';
echo '<td style="text-align:right">'.L('Change').'&nbsp;
<select id="m_ldap" name="m_ldap" onchange="qtFormSafe.not();">
<option value="1"'.($_SESSION[QT]['m_ldap']=='1' ? ' selected' : '').'>'.$L['On_line'].'</option>
<option value="0"'.($_SESSION[QT]['m_ldap']=='0' ? ' selected' : '').'>'.$L['Off_line'].'</option>
</select>
</td>
</tr>
</table>

<h2 class="subconfig">User authentication</h2>
<table class="t-conf ldap">
<tr>
<th>Login users</th>
<td>
<p class="cblabel"><input type="radio" id="log_all" name="m_ldap_users" value="all"'.($_SESSION[QT]['m_ldap_users']=='all' ? 'checked' : '').' onchange="qtFormSafe.not();"/><label for="log_all">Accept locally registered users AND ldap users</label></p>
<p class="cblabel"><input type="radio" id="log_ldap" name="m_ldap_users" value="ldap"'.($_SESSION[QT]['m_ldap_users']=='ldap' ? 'checked' : '').' onchange="qtFormSafe.not();"/><label for="log_ldap">Accept ONLY valid ldap users</label></p>
</td>
</tr>
';
echo '<tr>
<th>'.L('Information').'</th>
<td class="article">
<p class="bold italic">Accept locally registered users AND ldap account</p>
<p style="font-size:0.9rem">With this option, users without ldap entry must first register before using the application. Users having a valid ldap account don\'t need to register.</p>
<p class="bold italic">Accept ONLY valid ldap accounts</p>
<p style="font-size:0.9rem">On first login, a local profile is created for the user having a valid ldap account. For users without ldap account, the register page allows sending a request to the Administrator in order to create a new ldap entry. With this option, it\'s recommended to turn the registration mode to "backoffice" (see security page).</p>
</td>
</tr>
</table>
';
}

if ( $pan==1 || $pan==2 )
{
echo '<form method="post" action="'.Href($oH->selfurl).'">
<h2 class="subconfig">Connection and authentication</h2>
<table class="t-conf ldap">
<tr>
<th>Host</th>
<td><input type="text" id="m_ldap_host" name="m_ldap_host" size="30" maxlength="64" value="'.$_SESSION[QT]['m_ldap_host'].'"/><br>
<span class="small">Host and port. Ex.: </span><span class="small" style="color:#4444ff">ldap://localhost:10389</span></td>
</tr>
<tr>
<th>Login DN</th>
<td><input type="text" id="m_ldap_login_dn" name="m_ldap_login_dn" size="30" maxlength="64" value="'.$_SESSION[QT]['m_ldap_login_dn'].'"/><br>
<span class="small">Use $username as placeholder. Ex.: </span><span class="small" style="color:#4444ff">cn=$username,ou=users,o=mycompany</span></td>
</tr>
</table>
';
echo '<h2 class="subconfig">Search configuration (to create new user)</h2>
<table class="t-conf ldap">
<tr>
<th style="vertical-align:top">When searching</th>
<td style="vertical-align:top" class="article">
<p class="cblabel"><input type="checkbox" id="m_ldap_bind" name="m_ldap_bind" value="a"'.($_SESSION[QT]['m_ldap_bind']==='a' ? 'checked' : '').' onclick="toggleAnonymous(this.checked);"/> <label for="m_ldap_bind">Server supports anonymous bind</label></p>
<p id="bind_input" style="display:'.($_SESSION[QT]['m_ldap_bind']==='a' ? 'none' : 'block').'">
<input type="text" id="m_ldap_bind_rdn" name="m_ldap_bind_rdn" size="20" maxlength="34" value="'.$_SESSION[QT]['m_ldap_bind_rdn'].'" placeholder="System DN"/><br>
<input type="text" id="m_ldap_bind_pwd" name="m_ldap_bind_pwd" size="20" maxlength="64" value="'.$_SESSION[QT]['m_ldap_bind_pwd'].'" placeholder="Password"/>
</p>
</td>
</tr>
<tr>
<th>Search RDN</th>
<td><input type="text" id="m_ldap_s_rdn" name="m_ldap_s_rdn" size="30" maxlength="64" value="'.$_SESSION[QT]['m_ldap_s_rdn'].'"/><br>
<span class="small">dn or rdn (search basis)</span></td>
</tr>
<tr>
<th>Search filter</th>
<td><input type="text" id="m_ldap_s_filter" name="m_ldap_s_filter" size="30" maxlength="64" value="'.$_SESSION[QT]['m_ldap_s_filter'].'"/><br>
<span class="small">Use $username as placeholder. Ex.: </span><span class="small" style="color:#4444ff">(cn=$username)</span><span class="small"> allows searching the username specified in the login panel</span></td>
</tr>
<tr>
<th>Requested info</th>
<td><input type="text" id="m_ldap_s_info" name="m_ldap_s_info" size="30" maxlength="64" value="'.$_SESSION[QT]['m_ldap_s_info'].'"/><br>
<span class="small">At least the mail is recommended. This is usefull when a user performs his very first login (the application will create a new profile with the same e-mail as in ldap).</span></td>
</tr>
</table>
';
$oH->scripts[] = 'function toggleAnonymous(checked){ document.getElementById("bind_input").style.display = checked ? "none" : "block"; }';
}

if ( $pan===2  )
{

if ( !isset($test_conn) ) $test_conn='<span class="disabled">(none)</span>';
if ( !isset($test_find) ) $test_find='<span class="disabled">(none)</span>';
if ( !isset($test_login) ) $test_login='<span class="disabled">(none)</span>';

echo '<h2 class="subconfig">Test</h2>
<table class="t-conf ldap">
<tr>
<th>Username</th>
<td><input type="text" id="username" name="username" size="30" maxlength="64" /></td>
</tr>
<tr>
<th>Password</th>
<td><input type="text" id="password" name="password" size="30" maxlength="64" /></td>
</tr>
<tr>
<th>Test result</th>
<td>Connection: '.$test_conn.'<br>Search user: '.$test_find.'<br>Login: '.$test_login.'<br></td>
</tr>
</table>
';
}

echo '<p class="submit">'.PHP_EOL;
if ( $pan===2 )
{
  if ( function_exists('ldap_connect') )
  {
  echo '<input type="hidden" name="pan" value="'.$pan.'"/><button type="submit" name="ok" value="Test">Test</button></p>'.PHP_EOL;
  }
  else
  {
  echo 'LDAP function not found. It seems that module LDAP is not activated on your webserver.
  ';
  }
}
else
{
echo '<p class="submit">
<input type="hidden" name="pan" value="'.$pan.'"/>
<button type="submit" name="ok" value="ok">'.L('Save').'</button>

';
}

echo '</p>
</form>
';

// END TABS

echo '
</div>
';

// INFO
if ( $pan>0 )
{
echo '<br>
<h2 class="subconfig">Setting examples</h2>
<table class="t-conf">
<tr>
<td>
<div class="scroll article">
<p>
Host <span style="color:#4444ff">ldap://localhost:10389</span><br>
Login DN <span style="color:#4444ff">uid=$username,dc=example,dc=com</span><br>
Search RDN <span style="color:#4444ff">dc=example,dc=com</span><br>
Search filter <span style="color:#4444ff">(cn=$username)</span><br>
Requested info <span style="color:#4444ff">cn,sn,mail,uid</span><br>
</p>
';
echo '<p class="bold">Example with url and ssl</p>
<p class="small">If you are using OpenLDAP 2.x.x you can specify a URL instead of the hostname. To use LDAP with SSL, compile OpenLDAP 2.x.x with SSL support, configure PHP with SSL, and set this parameter as ldaps://hostname/.</p>
<p class="small">
Host <span style="color:#4444ff">ldaps://ldap.example.com</span> (port not used when using URLs)<br>
Login DN <span style="color:#4444ff">cn=$username,ou=users,o=mycompany</span><br>
Search RDN <span style="color:#4444ff">ou=users,o=mycompany</span><br>
Search filter <span style="color:#4444ff">(uid=$username)</span><br>
Requested info <span style="color:#4444ff">cn,sn,mail,uid</span><br>
</p>
</div>
</td>
</tr>
</table>
';
}

// HTML END

include APP.'_adm_inc_ft.php';