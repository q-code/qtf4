<?php // v4.0 build:20230205 can be app impersonated {qt f|e|i}

// Returns true/false, in case of troubles $oH->error will include the ldap error message

function qt_ldap_bind($username,$password)
{
  $b = false;
  global $oH;
  if ( !function_exists('ldap_connect') ) { $oH->error='Ldap functions not available from the current webserver configuration.'; return false; }
  $c = @ldap_connect($_SESSION[QT]['m_ldap_host']) or $oH->error=ldap_err2str(ldap_errno($c));
  // bind
  if ( empty($oH->error) )
  {
    ldap_set_option($c, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($c, LDAP_OPT_REFERRALS, 0);
    $login_dn = str_replace('$username',$username,$_SESSION[QT]['m_ldap_login_dn']);
    $b = @ldap_bind($c,$login_dn,$password);
    if ( !$b ) $oH->error=ldap_err2str(ldap_errno($c));
  }
  @ldap_close($c);
  return $b;
}

// Return the mail
// $email may be empty in case of wrong ldap search settings

function qt_ldap_search($username)
{
  global $oH;
  $oH->error = '';
  $mail = '';
  $c = @ldap_connect($_SESSION[QT]['m_ldap_host']) or $oH->error=ldap_err2str(ldap_errno($c));
  // admin or anonymous bind
  if ( empty($oH->error) )
  {
    ldap_set_option($c, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($c, LDAP_OPT_REFERRALS, 0);
    if ( $_SESSION[QT]['m_ldap_bind']==='n' )
    {
      @ldap_bind($c,$_SESSION[QT]['m_ldap_bind_rdn'],$_SESSION[QT]['m_ldap_bind_pwd']) or $oH->error='Connection result: '.ldap_err2str(ldap_errno($c)); // bind (anonymous by default)
    }
    else
    {
      @ldap_bind($c) or $oH->error='Connection result: '.ldap_err2str(ldap_errno($c)); // bind (anonymous by default)
    }
  }
  // search username
  if ( empty($oH->error) )
  {
    $filter = str_replace('$username',$username,$_SESSION[QT]['m_ldap_s_filter']);
    $s = @ldap_search($c,$_SESSION[QT]['m_ldap_s_rdn'],$filter,explode(',',$_SESSION[QT]['m_ldap_s_info'])) or $oH->error='Search result: '.ldap_err2str(ldap_errno($c));
  }
  // analyse search results
  if ( empty($oH->error) )
  {
    $users = ldap_get_entries($c, $s);
    $intEntries = ldap_count_entries($c,$s);
    for($i=0;$i<$intEntries;$i++)
    {
      $infos = $users[$i];
      $mail = isset($infos['mail'][0]) ? $infos['mail'][0] : '';
      if ( empty($mail) && isset($infos['email'][0]) ) $mail = $infos['email'][0];
      if ( empty($mail) )
      {
        foreach($infos as $info)
        {
          if ( isset($info[0]) && QTismail($info[0]) ) { $mail = $info[0]; break; }
        }
      }
      if ( !empty($mail) ) break;
    }
  }
  return $mail;
}

function qt_ldap_profile($username,$password,$mail='')
{
  if ( empty($mail) ) $mail = qt_ldap_search($username); // seach the user's mail from ldap (may be empty)
  return SUser::addUser($username,$password,$mail);
}