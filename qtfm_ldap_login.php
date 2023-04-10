<?php // v4.0 build:20230205 can be app impersonated {qt f|e|i}
/**
 * @var CDatabase $oDB
 * @var string $username
 * @var string $password
 * @var string $hpassword
 */

// This is included in CVip::logIn function when LDAP module is installed and configured
// This part will validate $username/$password (using a ldap bind)
// and add a new profile (when profile not yet existing)

include APP.'m_ldap_lib.php';
$bLdapLogin = qt_ldap_bind($username,$password);
if ( $bLdapLogin )
{
  $_SESSION[QT.'_usr']['auth'] = true;
  // add profile if new user
  global $oDB;
  $sqlName = 'name=:name'.(QT_LOGIN_WITH_EMAIL ? ' OR mail=:name OR mail LIKE :name2' : '');
  $iProfile = $oDB->count(
    TABUSER." WHERE pwd=:pwd AND ($sqlName)",
    ['pwd'=>$hpassword, ':name'=>$username, ':name2'=>$username.',%']
    );
  if ( $iProfile==0 ) qt_ldap_profile($username,$password); // create new profile (will search email from ldap)
}
else
{
  $_SESSION[QT.'_usr']['auth'] = false;
  // ABORD login when bind failed if authority is configured with ldap ONLY
  if ( isset($_SESSION[QT]['m_ldap_users']) && $_SESSION[QT]['m_ldap_users']=='ldap' ) return false;
}