<?php
/*
lib_qt_txt.php
------------
version: 5.5 build:20230430
This is a library of public functions
-------------
qtDateClean - v5.4: can truncate the cleaned datetime
qtDatestr - v5.3: arguments order changed (for the 2 last arguments)
*/

/**
 * Quote a string (works recursively in case of array)
 * @param string|int|float|array $txt
 * @param string $q1 openquote (&' &" &< to get curved ' " <<)
 * @param string $q2 closequote (if empty, uses mirror curved symbol or openquote)
 * @param bool $keepnumeric int/float are returned as int/float (not quoted)
 * @return string|array (with array, index and non-string element remain unchanged)
 */
function qtQuoted($txt, string $q1='"', string $q2='', bool $keepNum=false)
{
  // Works recursively on array
  if ( is_array($txt) ) { foreach($txt as $k=>$item) $txt[$k] = qtQuoted($item,$q1,$q2,$keepNum); return $txt; }
  // Returns a quoted string (except int/float with $keepNum=true)
  if ( $keepNum && (is_int($txt) || is_float($txt)) ) return $txt;
  if ( empty($q2) ) {
    // use well known open/closing symbols
    switch(strtolower($q1)) {
      case "&'":
      case '&#8216;':
      case '&#x2018;':
      case '&lsquo;': $q1 = '&lsquo;'; $q2 = '&rsquo;'; break;
      case '&"':
      case '&#8220;':
      case '&#x201c;':
      case '&ldquo;': $q1 = '&ldquo;'; $q2 = '&rdquo;'; break;
      case '&<':
      case '&#171;':
      case '&#xab;':
      case '&laquo;': $q1 = '&laquo;'; $q2 = '&raquo;'; break;
      default: $q2 = $q1;
    }
  }
  if ( empty($q1) && empty($q2) ) throw new Exception( __FUNCTION__.' invalid argument q' );
  if ( is_string($txt) || is_int($txt) || is_float($txt) ) return $q1.$txt.$q2;
  throw new Exception( __FUNCTION__.' invalid argument' );
}

/**
 * Convert apostrophe (and optionally doublequote, &, <, >) to html entity (used for sql statement values insertion)
 * @param string $str
 * @param boolean $double convert doublequote (true by default)
 * @param boolean $amp convert ampersand (default defined by system constant)
 * @param boolean $tag convert < and > (true by default)
 * @return string
 */
function qtDb(string $str, bool $double=true, bool $amp=QT_CONVERT_AMP, bool $tag=true)
{
  // same as CDatabase::sqlEncode (with $amp using config constant)
  if ( empty($str) ) return $str;
  if ( $amp && strpos($str,'&')!==false ) $str = str_replace('&','&#38;',$str);
  if ( $double && strpos($str,'"')!==false ) $str = str_replace('"','&#34;',$str);
  if ( $tag && strpos($str,'<')!==false ) $str = str_replace('<','&#60;',$str);
  if ( $tag && strpos($str,'>')!==false ) $str = str_replace('>','&#62;',$str);
  return strpos($str,"'")===false ? $str : str_replace("'",'&#39;',$str);
}
function qtDbDecode(string $str, bool $double=true, bool $amp=QT_CONVERT_AMP, bool $tag=true)
{
  // same as CDatabase::sqlEncode (with $amp using config constant)
  if ( empty($str) || strpos($str,'&')===false ) return $str;
  if ( strpos($str,'&#39;')!==false ) $str = str_replace('&#39;',"'",$str);
  if ( $double && strpos($str,'&#34;')!==false ) $str = str_replace('&#34;','"',$str);
  if ( $tag && strpos($str,'&#60;')!==false ) $str = str_replace('&#60;','<',$str);
  if ( $tag && strpos($str,'&#62;')!==false ) $str = str_replace('&#62;','>',$str);
  if ( $amp && strpos($str,'&#38;')!==false ) $str = str_replace('&#38;','&',$str);
  return $str;
}

/**
 * Drop diacritics
 * @param string|array $str
 * @return string|array (with array, indexes remain unchanged)
 */
function qtDropDiacritics($str) {
  // Works recursively on array
  if ( is_array($str) ) { foreach($str as $k=>$item) $str[$k] = qtDropDiacritics($item); return $str; }
  // String only
  if ( !is_string($str) ) throw new Exception(__FUNCTION__.' invalid argument');
  $tl = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', Transliterator::FORWARD);
  $res = $tl->transliterate($str);
  return $res===false ? $str : $res;
}
/**
 * Truncate and add the trailing $end (works also on an array)
 * @param string|array $txt
 * @param integer $max maximum size (including trailing characters)
 * @param string $end trailing characters
 * @return string|array (with array, index and non-string element remain unchanged)
 */
function qtTrunc($txt, int $max=255, string $end='...')
{
  if ( $max<1 ) die('qtTrunc arg #2 must be positif');
  if ( is_string($txt) ) {
    if ( $max<=strlen($end) ) $txt = $end; // truncate too short
    if ( isset($txt[$max]) ) $txt = substr($txt,0,$max-strlen($end)).$end;
    return $txt;
  }
  if ( is_array($txt) ) {
    foreach($txt as $k=>$item) if ( is_string($item) || is_array($item) ) $txt[$k] = qtTrunc($item,$max,$end);
    return $txt;
  }
  throw new Exception( 'invalid argument txt' );
}
/**
 * Convert multiline text into one line (truncate and unbbc)
 * @param string $str
 * @param integer $max when >0 uses qtTrunc
 * @param string $end end characters used by qtTrunc
 * @param boolean $unbbc remove bbc tags
 * @param string|array $in text to be replaced
 * @param string|array $out text replacement
 * @return string
 */
function qtInline(string $str, int $max=255, string $end='...', bool $unbbc=true, $in=array("\r\n",'<br>','  '), $out=array(' ',' ',' '))
{
  if ( empty($str) ) return $str;
  if ( !is_string($in) && !is_array($in) ) die('qtInline: arg #5 must be a string or array');
  if ( !is_string($out) && !is_array($out) ) die('qtInline: arg #6 must be a string or array');
  // unbbc
  if ( $max>0 ) $str = substr($str,0,$max+64); // optimize for qtTrunc and qtUnbbc
  if ( $unbbc ) $str = qtUnbbc($str);
  // inline
  $str = str_replace($in, $out, $str);
  // truncate
  return $max>0 ? qtTrunc($str,$max,$end) : $str;
}
function qtDateClean($d='now', int $size=14, string $failed='')
{
  // Works recursively on array
  if ( is_array($d) ) { foreach($d as $k=>$item) $d[$k] = qtDateClean($item,$size); return $d; }
  // Returns datetime [string] 'YYYYMMDD[HHMM[SS]]' from 'now', integer or a string like 'YYYY-MM-DD HH:MM:SS'
  if ( is_int($d) ) $d = (string)$d; // support int
  if ( !is_string($d) ) die(__FUNCTION__.' invalid arg #1');
  if ( $size<4 || $size>14) die(__FUNCTION__.' invalid arg #2');
  if ( empty($d) ) return $failed;
  // Sanitize
  if ( $d==='now' ) return substr(date('YmdHis'),0,$size);
  if ( $d===(string)abs((int)$d) ) return substr($d,0,$size); // unsignedint [string] is sanitized
  $d = str_replace([' ','-','.','/',':'], '', $d);
  if ( $d===(string)(int)$d ) return substr($d,0,$size);
  return $failed;
}
function qtDatestr($sDate='now', string $sOutDate='$', string $sOutTime='$', bool $friendly=true, bool $dropOldTime=true, $title=false, $titleid=false, $e='?')
{
  // Converts a date [string|int|'now'] to a formatted date [string] and translate it.
  // $sDate - The date string, can be 'YYYYMMDD[HH][MM][SS]' or 'now'. It can include [.][/][-][ ]
  // $sOutDate - The output format for the date (or '$' to use constant FORMATDATE). Also accept 'RFC-3339' (this will ignore other parametres)
  // $sOutTime - The output format for the time (or '$' to use constant FORMATTIME). If not empty, time is appended to the date (or friendlydate)
  // $friendly - Replace date by 'Today','Yesterday'
  // $dropOldTime - Don't show time for date > 2 days.
  // $e - When $sDate is '0' or empty, or when the input date format is unsupported the function returns $e ('?')
  // The translation uses $L['dateSQL']. If not existing, the php words remains (english).

  $sDate = qtDateClean($sDate); if ( empty($sDate) ) return $e; // Clean $sDate to a [string] YYYYMMDD[HHMMSS] (max 14 char, supports 'now')
  if ( strlen($sDate)===4 ) return $sDate; // Stop if input is a year

  // Analyse date time: returns $e when input is a invalid date otherwhise detect if recent date
  $intDate = false;
  switch(strlen($sDate)) {
    case 6:  $intDate = mktime(0,0,0,substr($sDate,4,2),1,substr($sDate,0,4)); break;
    case 8:  $intDate = mktime(0,0,0,substr($sDate,4,2),substr($sDate,6,2),substr($sDate,0,4)); break;
    case 10: $intDate = mktime(substr($sDate,-2,2),0,0,substr($sDate,4,2),substr($sDate,6,2),substr($sDate,0,4)); break;
    case 12: $intDate = mktime(substr($sDate,-4,2),substr($sDate,-2,2),0,substr($sDate,4,2),substr($sDate,6,2),substr($sDate,0,4)); break;
    case 14: $intDate = mktime(substr($sDate,-6,2),substr($sDate,-4,2),substr($sDate,-2,2),substr($sDate,4,2),substr($sDate,6,2),substr($sDate,0,4)); break;
    default: return $e;
  }
  if ( $intDate===FALSE ) return $e;

  // Exceptions (used by rss xml)
  if ( $sOutDate==='RFC-3339' ) {
    $sDate = date('Y-m-d\TH:i:s',$intDate);
    $sGMT = date('O',$intDate);
    $sGMT = substr($sGMT,0,3).':'.substr($sGMT,-2,2);
    return $sDate.$sGMT;
  }

  // Check requested formats (and if recent)
  $bRecent = ( date('Y-m-d')==date('Y-m-d',$intDate) || date('Y-m-d')==date('Y-m-d',$intDate+86400) );
  if ( $sOutDate==='$' ) $sOutDate = defined('FORMATDATE') ? FORMATDATE : 'Y-m-d';
  if ( $sOutTime==='$' ) $sOutTime = defined('FORMATTIME') ? FORMATTIME : ''; // drop hh:mm if format not specified
  if ( !$bRecent && $dropOldTime ) $sOutTime='';

  // Apply output format. In case of friendly date, Today/Yesterday will replace the date (and time can be added)
  $stamp = '';
  if ( $bRecent && $friendly ) {
    if ( date('Y-m-d')==date('Y-m-d',$intDate) )       { $stamp = 'Today '; $sOutDate=''; }
    if ( date('Y-m-d')==date('Y-m-d',$intDate+86400) ) { $stamp = 'Yesterday '; $sOutDate=''; }
  }
  $format = trim($sOutDate.' '.$sOutTime);
  $sDate = trim($stamp.(empty($format) ?  '' : date($format,$intDate)));
  if ( empty($sDate) )  return $e;
  $sDateFull = date('j F Y, '.(empty($sOutTime) ? 'G:i' : $sOutTime),$intDate);

  // Translating
  global $L;
  if ( isset($L['dateSQL']) && is_array($L['dateSQL']) ) {
    $sDate = qtDateTranslate($sDate, $L['dateSQL']);
    $sDateFull = qtDateTranslate($sDateFull, $L['dateSQL']);
  }

  // Exit
  if ( $title===false ) return $sDate;
  return '<span'.(empty($titleid) ? '' : ' id="'.$titleid.'" ').' title="'.qtAttr($sDateFull).'">'.$sDate.'</span>';
}
function qtDateTranslate(string $str, array $translations)
{
  if ( empty($translations) ) return $str;
  // to avoid recursive replacement, we build a dictionnary containing only the words used in $str
  $words = array_filter(explode(' ',preg_replace('/[^a-z]+/i', ' ', $str)));
  $dico = array();
  foreach($words as $word) {
    if ( array_key_exists($word, $translations) ) {
      $dico[$word] = $translations[$word];
    } elseif ( array_key_exists($word, array_change_key_case($translations,CASE_LOWER)) ) {
      $dico[$word] = strtolower($translations[$word]);
    } else {
      $dico[$word] = $word;
    }
  }
  return str_replace(array_keys($dico),array_values($dico),$str);
}

function qtBbc(string $str, string $nl='<br>', array $tip=array(), string $bold='<b>$1</b>', string $italic='<i>$1</i>')
{
  // Converts bbc to html
  // $str - [mandatory] a string than can contains bbc tags
  // $nl - convert \r\n, \r or \n to $nl. Use '' to not convert.
  // $tip - block info tips
  // $bold - (optional) format ex: <b>$1</b> or <span class="b">$1</span>
  // $italic - (optional) format ex: <b>$1</b> or <span class="i">$1</span>
  // Example qtBbc('[b]Text[/b]') returns <b>Text</b>
  // Example qtBbc('[i]<b>Text<b>[/i]') returns <i>&lt;b&gt;Text&lt;/b&gt;</i>

  // check
  if ( strpos($str,'<')!==false) $str = str_replace('<','&#60;',$str);
  if ( strpos($str,'>')!==false) $str = str_replace('>','&#62;',$str);

  // process for []
  if ( strpos($str,'[')!==false)
  {
    if ( strpos($str,'[*]')!==false) $str = str_replace('[*]','&bull;',$str);
    if ( !isset($tip['Quotation']) ) $tip['Quotation']='Quotation';
    if ( !isset($tip['Quotation_from']) ) $tip['Quotation_from']='Quotation from';
    if ( !isset($tip['Code']) ) $tip['Code']='Code';
    $arrSearch = [
    '/\[b\](.*?)\[\/b\]/',
    '/\[i\](.*?)\[\/i\]/',
    '/\[u\](.*?)\[\/u\]/',
    '/\[img\](.*?)\[\/img\]/',
    '/\[url\](.*?)\[\/url\]/',
    '/\[url\=(.*?)\](.*?)\[\/url\]/',
    '/\[mail\](.*?)\[\/mail\]/',
    '/\[mail\=(.*?)\](.*?)\[\/mail\]/',
    '/\[quote\]/',
    '/\[quote\=(.*?)\]/',
    '/\[\/quote\]/',
    '/\[code\]/',
    '/\[\/code\]/'
    ];
    $arrReplace = [
    $bold,
    $italic,
    '<span class="u">$1</span>',
    '<img class="post-img" src="$1" alt="(missing file)"/>',
    '<a class="msgbody" href="http://$1" target="_blank">$1</a>',
    '<a class="msgbody" href="http://$2" target="_blank">$1</a>',
    '<a class="msgbody" href="mailto:$1">$1</a>',
    '<a class="msgbody" href="mailto:$2">$1</a>',
    '<p class="quotetitle">'.$tip['Quotation'].':</p><section class="quote">',
    '<p class="quotetitle">'.$tip['Quotation_from'].' $1:</p><section class="quote">',
    '</section>',
    '<p class="codetitle">'.$tip['Code'].':</p><section class="code">',
    '</section>'
    ];
    $str = preg_replace( $arrSearch, $arrReplace, $str );
    // check for missing end-tags
    foreach(['span','a','p','section','img'] as $tag) {
      $a = substr_count($str,'<'.$tag);
      $b = substr_count($str,'</'.$tag);
      if ( $b<$a ) for($i=$b;$i<$a;++$i) $str .= '</'.$tag.'>';
    }
    $str = str_replace( array('http://http','http://ftp:','http://mailto:','mailto:mailto:'), array('http','ftp:','mailto:','mailto'), $str ); // special check for the href error
  }

  if ( $nl!=='' ) $str = str_replace( array("\r\n","\r","\n"), $nl, $str );
  if ( $nl!=='' ) $str = str_replace( '<p>'.$nl, '<p>', $str );
  return $str;
}
function qtUnbbc(string $str, bool $deep=true, array $tip=array())
{
  if ( empty($str) || strpos($str,'[')===false ) return $str;
  if ( !isset($tip['Quotation']) ) $tip['Quotation']='Quotation';
  if ( !isset($tip['Quotation_from']) ) $tip['Quotation_from']='Quotation from';
  if ( !isset($tip['Code']) ) $tip['Code']='Code';
  return preg_replace(
      array('/\[b\](.*?)\[\/b\]/','/\[i\](.*?)\[\/i\]/', '/\[u\](.*?)\[\/u\]/', '/\[\*\]/', '/\[img\](.*?)\[\/img\]/', '/\[url\](.*?)\[\/url\]/', '/\[url\=(.*?)\](.*?)\[\/url\]/', '/\[mail\](.*?)\[\/mail\]/', '/\[mail\=(.*?)\](.*?)\[\/mail\]/', '/\[color\=(.*?)\](.*?)\[\/color\]/', '/\[size=(.*?)\](.*?)\[\/size\]/', '/\[quote\]/', '/\[quote\=(.*?)\]/', '/\[\/quote\]/', '/\[code\]/', '/\[\/code\]/') ,
      array('$1','$1','$1','',($deep ? '' : '$1'),($deep ? '' : '$1'),($deep ? '' : '$1'),'$1','$1','$1','$1',($deep ? '' : $tip['Quotation'].': '),($deep ? '' : $tip['Quotation_from'].' $1: '),'',($deep ? '' : $tip['Code'].': '),''),
      $str );
}

function qtIsPwd(string $str, int $intMin=4, int $intMax=50, bool $trim=false)
{
  if ( empty($str) ) return false;
  if ( $trim && $str!=trim($str) ) return false;
  if ( isset($str[$intMax]) ) return false; //length > $intMax
  if ( !isset($str[$intMin-1]) ) return false; //length < $intMin
  return true;
}
function qtIsMail(string $str, bool $multiple=true)
{
  if ( empty($str) || $str!=trim($str) ) return false;
  $arr = $multiple && strpos($str,',')!==false ? asCleanArray($str,',') : [$str];
  foreach ($arr as $str) if ( !preg_match("/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i",$str) ) return false;
  return true;
}
function qtIsBetween($n, $min=0, $max=99999)
{
  // Works recursively on array
  if ( is_array($n) ) { foreach($n as $k=>$item) if ( !qtIsBetween($item,$min,$max) ) return false; return true; }
  // Only numeric
  if ( !is_numeric($n) || !is_numeric($min) || !is_numeric($max) ) die(__FUNCTION__.' arguments must be a numeric');
  if ( $min>=$max ) die(__FUNCTION__.' invalid min > max');
  if ( $n<$min ) return false;
  if ( $n>$max ) return false;
  return true;
}
function qtIsValiddate($d, bool $pastYear=true, bool $futurYear=false)
{
  // Works recursively on array
  if ( is_array($d) ) { foreach($d as $k=>$item) if ( !qtIsValiddate($item,$pastYear,$futurYear) ) return false; return true; }
  // Only YYYYMMDD
  // Valid within [1900-2200] and (default) allow past year, disallow futur year
  $d = (string)$d;
  if ( strlen($d)!=8 ) return false;
  if ( $d!==(string)abs((int)$d) ) return false; // only unsignedint [string]
  $y = (int)substr($d,0,4); if ( $y<1900 || $y>2200 ) return false;
  $m = (int)substr($d,4,2); if ( $m<1 || $m>12 ) return false;
  $d = (int)substr($d,-2,2); if ( $d<1 || $d>31 ) return false;
  if ( !$pastYear && $y<date('Y') ) return false;
  if ( !$futurYear && $y>date('Y') ) return false;
  return true;
}
function qtIsValidtime($d)
{
  // Works recursively on array
  if ( is_array($d) ) { foreach($d as $k=>$item) if ( !qtIsValidtime($item) ) return false; return true; }
  // Only HHMM or HHMMSS
  $d = (string)$d;
  if ( strlen($d)!==4 && strlen($d)!==6 ) return false;
  if ( !preg_match('/^[0-9]*$/', $d) ) return false;
  $i = (int)substr($d,0,2); if ( $i<0 || $i>23 ) return false;
  $i = (int)substr($d,2,2); if ( $i<0 || $i>59 ) return false;
  $i = (int)substr($d,4,2); if ( $i<0 || $i>59 ) return false;
  return true;
}