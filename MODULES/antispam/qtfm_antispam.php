<?php // v4.0 build:20221111
// This is included in qtf_edit.php in a try{}catch{}
/**
 * Convert $ip string into IPv6 or IPv4 canonincal format (IPv4-over-IPv6 becomes IPv4)<br>A range selector * is possible on the last byte.
 * @param string $ip the IP-adress
 * @return string (or false if address not valid)
 */
function getCanonIP(string $ip) {
  if ( empty($ip) ) die('getCanonIP: ip is empty');
  // convert IPv4 into IPv4-over-IPv6 (this allows trailing 0 also in IPv4)
  if ( strpos($ip,':')===false ) $ip = '::ffff:'.$ip;
  // detect if last byte is '*', use 0-byte instead
  $mask = false;
  if ( substr($ip,-2)==='.*' ) { $mask = '.'; $ip = substr($ip,0,-2).$mask.'0'; }
  if ( substr($ip,-2)===':*' ) { $mask = ':'; $ip = substr($ip,0,-2).$mask.'0'; }
  // Parse
  $ip_bin = inet_pton($ip); if ( $ip_bin===false ) return false;
  // Check prefix and strip
  $prefix_bin = hex2bin('00000000000000000000ffff');
  if ( substr($ip_bin,0,strlen($prefix_bin))==$prefix_bin ) $ip_bin = substr($ip_bin, strlen($prefix_bin));
  // Convert back to printable address in canonical format IPv6, or IPv4 (for IPv4 and IPv4-over-IPv6 inputs)
  $ip = inet_ntop($ip_bin); if ( $ip_bin===false ) return false;
  if ( $mask ) $ip = substr($ip,0,-2).$mask.'*'; // restore the mask on the last byte
  return $ip;
}
/**
 * @var string $error
 * @var string $warning
 * @var CVip $oV
 * @var cHtml $oHtml
 * @var array $L
 * @var CDatabase $oDB
 * @var CPost $oP
 */
if ( $_SESSION[QT]['m_antispam']>0 ) // this parametre can be >1
{
  include translate('qtfm_antispam_error.php');
  if ( !isset($_SESSION[QT]['m_antispam_conf']) ) $oDB->getSettings('param="m_antispam_conf"',true);
  if ( !isset($_SESSION[QT]['m_antispam_count']) ) $_SESSION[QT]['m_antispam_count']=0;

  if ( $_SESSION[QT]['m_antispam_count']>3 ) throw new Exception( L('Antispam.E1') );

  $strMAz     = substr($_SESSION[QT]['m_antispam_conf'],0,1);
  $strMVowel  = substr($_SESSION[QT]['m_antispam_conf'],1,1);
  $strMChars  = substr($_SESSION[QT]['m_antispam_conf'],2,1);
  $intMChars  = (int)substr($_SESSION[QT]['m_antispam_conf'],3,1);
  $strMWords  = substr($_SESSION[QT]['m_antispam_conf'],4,1);
  $intMWords  = (int)substr($_SESSION[QT]['m_antispam_conf'],5,1);
  $strMGood   = substr($_SESSION[QT]['m_antispam_conf'],6,1);
  $intMGoods  = (int)substr($_SESSION[QT]['m_antispam_conf'],7,1);
  $strMInsane = substr($_SESSION[QT]['m_antispam_conf'],8,1);
  $intMInsane = (int)substr($_SESSION[QT]['m_antispam_conf'],9,1);
  $strMRepeat = substr($_SESSION[QT]['m_antispam_conf'],10,1);
  $strMIp     = substr($_SESSION[QT]['m_antispam_conf'],11,1);

  // minimum size
  if ( $strMChars=='1' )
  {
    if ( strlen($oP->text)<=$intMChars ) throw new Exception( L('Antispam.E2') );
  }

  $strM = strtolower(substr($oP->text,0,500));
  if ( $strMAz=='1' )
  {
    if ( !preg_match("/[a-z]/",$strM) ) throw new Exception( L('Antispam.E3') );
  }
  $strM = QTdropaccent($strM);

  // vowels
  if ( $strMVowel=='1' )
  {
    if ( !preg_match("/[aeiouy]/",$strM) ) throw new Exception( L('Antispam.E4') );
  }

  // check IPs
  if ($strMIp=='1' )
  {
    $file = QT_DIR_DOC.'qtfm_spamip.txt';
    $arrIP = file_exists($file) ? explode("\n", file_get_contents($file)) : array();
    if ( count($arrIP)>0 ) {
      $userIP = getCanonIP($_SERVER['REMOTE_ADDR']); // canonical format (can be false for not valid ip)
      if ( $userIP!==false) foreach($arrIP as $ip) {
        $ip = trim($ip);
        $ip = explode(' ',$ip)[0]; // first part of the line (the ip)
        $ip = getCanonIP($ip); if ( $ip===false ) continue;
        // compare
        if ( $ip===$userIP ) throw new Exception( L('Antispam.E5') );
        if ( substr($ip,-1)==='*' ) {
          $i = strlen($ip)-2;
          if ( substr($ip,0,$i)==substr($userIP,0,$i) ) throw new Exception( L('Antispam.E5') );
        }
      }
    }
  }

  if ( $strMWords=='1' )
  {
    $arrMWords = str_word_count($strM,1); // php 4.3.0
    if ( count($arrMWords)<=$intMWords ) throw new Exception( L('Antispam.E6') );
  }

  // language
  if ( $strMGood=='1' || $strMInsane=='1' ) {

    include translate('qtfm_antispam_goodbad.php'); /** @var array $voc */
    if ( $strMGood=='1' )
    {
      if ( !isset($arrMWords) ) $arrMWords = str_word_count($strM,1); // php 4.3.0
      $interMWords = array_intersect($voc['good'],$arrMWords);
      if ( count($interMWords)<=$intMGoods ) throw new Exception( L('Antispam.E7') );
    }
    if ( $strMInsane=='1' )
    {
      if ( !isset($arrMWords) ) $arrMWords = str_word_count($strM,1); // php 4.3.0
      $interMWords = array_intersect($voc['bad'],$arrMWords);
      if ( count($interMWords)>=$intMInsane )
      {
        $error = 'message suspect.';
        foreach($interMWords as $strMValue)
        {
        $error .= "- $strMValue";
        }
      }
    }

  }

  // random keyboard
  if ( $strMVowel=='1' )
  {
    $iM=0;
    if ( !isset($arrMWords) ) $arrMWords = str_word_count($strM,1); // php 4.3.0
    foreach($arrMWords as $strMWord) {
    foreach(array('qwe','wqe','qsd','sdf','dfg','fgh','jkl','klm','ghg','fjf','jfj','fgf','dfd','fdj','dfj') as $aM) {
      if ( strpos($strMWord,$aM)===false ) continue;
      $iM++;
    }}
    if ( $iM>1 ) throw new Exception( L('Antispam.E0') );
  }

  // END ANTISPAM

  // no error means: consecutive error reset to 0
  $_SESSION[QT]['m_antispam_count']=0;

  // watch for consecutive errors

  if ( $strMRepeat=='1' ) {
    $_SESSION[QT]['m_antispam_count']++;
    if ( $_SESSION[QT]['m_antispam_count']>3 )
    {
      // ban user 1 day
      if ( SUser::id()!=1 ) $oDB->exec( 'UPDATE TABUSER SET closed="1" WHERE id='.SUser::id());
      throw new Exception( L('Antispam.E1') );
    }
  }

}