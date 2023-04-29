<?php // v4.0 build:20230205
// This is included in qtx_edit.php in a try{}catch{}
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
function antispamThrow(CHtml $oH, string $err='E0')
{
  $oH->error = L('Antispam.'.$err);
  $_SESSION[QT]['m_antispam_count']++;
  throw new Exception( $oH->error );
}
/**
* @var CHtml $oH
 * @var string $oH->warning
 * @var CHtml $oH
 * @var array $L
 * @var CDatabase $oDB
 * @var CPost $oP
 */
if ( $_SESSION[QT]['m_antispam']>0 ) // this parametre can be >1
{
  include translate('qtfm_antispam_error.php');
  if ( !isset($_SESSION[QT]['m_antispam_conf']) ) $oDB->getSettings('param="m_antispam_conf"',true);
  if ( !isset($_SESSION[QT]['m_antispam_count']) ) $_SESSION[QT]['m_antispam_count'] = 0;
  $checkAZ    = substr($_SESSION[QT]['m_antispam_conf'],0,1)==='1';
  $checkVowel = substr($_SESSION[QT]['m_antispam_conf'],1,1)==='1';
  $checkSize  = substr($_SESSION[QT]['m_antispam_conf'],2,1)==='1';
  $minSize    = (int)substr($_SESSION[QT]['m_antispam_conf'],3,1);
  $checkWord  = substr($_SESSION[QT]['m_antispam_conf'],4,1)==='1';
  $minWord    = (int)substr($_SESSION[QT]['m_antispam_conf'],5,1);
  $checkGood  = substr($_SESSION[QT]['m_antispam_conf'],6,1)==='1';
  $minGood    = (int)substr($_SESSION[QT]['m_antispam_conf'],7,1);
  $checkBad   = substr($_SESSION[QT]['m_antispam_conf'],8,1)==='1';
  $minBad     = (int)substr($_SESSION[QT]['m_antispam_conf'],9,1);
  $checkBan   = substr($_SESSION[QT]['m_antispam_conf'],10,1)==='1';
  $checkIP    = substr($_SESSION[QT]['m_antispam_conf'],11,1)==='1';
  // watch for consecutive errors
  if ( $checkBan && $_SESSION[QT]['m_antispam_count']>3 && SUser::id()>1 ) {
    $oDB->exec( "UPDATE TABUSER SET closed='1' WHERE id=".SUser::id()); // ban user 1 day
    antispamThrow($oH, 'E1');
  }
  // minimum size
  if ( $checkSize && strlen($oP->text)<=$minSize ) antispamThrow($oH, 'E2'); //!!!throw new Exception( L('Antispam.E2') );
  // sampling text
  $strM = mb_strtolower(qtDropDiacritics(substr($oP->text,0,500)));
  // simple characters exists
  if ( $checkAZ && !preg_match("/[a-z]/",$strM) ) antispamThrow($oH, 'E3');
  // vowels exists (i.e. only second line of the keyboard)
  if ( $checkVowel && !preg_match("/[aeiouy]/",$strM) ) antispamThrow($oH, 'E4');
  // check IPs
  if ( $checkIP ) {
    $file = QT_DIR_DOC.APP.'m_spamip.txt';
    $arrIP = file_exists($file) ? explode("\n", file_get_contents($file)) : [];
    if ( count($arrIP)>0 ) {
      $userIP = getCanonIP($_SERVER['REMOTE_ADDR']); // canonical format (can be false for not valid ip)
      if ( $userIP!==false) foreach($arrIP as $ip) {
        $ip = trim($ip);
        $ip = explode(' ',$ip)[0]; // first part of the line (the ip)
        $ip = getCanonIP($ip); if ( $ip===false ) continue;
        // compare
        if ( $ip===$userIP ) antispamThrow($oH, 'E5');
        if ( substr($ip,-1)==='*' ) {
          $i = strlen($ip)-2;
          if ( substr($ip,0,$i)==substr($userIP,0,$i) ) antispamThrow($oH, 'E5');
        }
      }
    }
  }
  // sampling words
  $words = str_word_count($strM,1); // [array] of words
  // number of words
  if ( $checkWord && count($words)<=$minWord ) antispamThrow($oH, 'E6');
  // language
  if ( $checkGood || $checkBad ) {
    include translate('qtfm_antispam_goodbad.php'); /** @var array $voc */
    if ( $checkGood ) {
      $matchWords = array_intersect($voc['good'],$words);
      if ( count($matchWords)<=$minGood ) antispamThrow($oH, 'E7');
    }
    if ( $checkBad ) {
      $matchWords = array_intersect($voc['bad'],$words);
      if ( count($matchWords)>=$minBad ) antispamThrow($oH, 'Message suspect (may contain too many rudnesses)');
    }
  }
  // random keyboard
  if ( $checkVowel ) {
    $iM=0;
    if ( !isset($words) ) $words = str_word_count($strM,1); // php 4.3.0
    foreach($words as $word) {
    foreach(array('qwe','wqe','qsd','sdf','dfg','fgh','jkl','klm','ghg','fjf','jfj','fgf','dfd','fdj','dfj') as $aM) {
      if ( strpos($word,$aM)===false ) continue;
      $iM++;
    }}
    if ( $iM>1 ) antispamThrow($oH);
  }
  // no error means: consecutive error reset to 0
  $_SESSION[QT]['m_antispam_count'] = 0;

}