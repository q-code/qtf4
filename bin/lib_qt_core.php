<?php // v4.0 build:20240210

/**
 * Convert qt-php-like url into html-like url when urlrewrite is active. Works only on the path-part of the url. Ex: 'qtx_login.php?x=y' becomes 'login.html?x=y'
 * @param string $url
 * @param string $ext extension (with dot)
 * @param boolean $hidePrefix file prefix (true for the default)
 * @return string the converted url, or the original url when urlrewrite is not active
 */
function url(string $url='', string $ext='.html', bool $hidePrefix=true)
{
  if ( !QT_URLREWRITE ) return $url;
  if ( empty($url) ) die(__FUNCTION__.' url empty');
  if ( $hidePrefix ) {
    $i = strlen(APP.'_');
    if ( substr($url,0,$i)===APP.'_' ) $url = substr($url,$i);
    // @version str_starts_with requires php8
  }
  $i = strrpos($url,'.php'); // only last .php becomes .html
  if ( $i===false ) return $url;
  return substr($url,0,$i).$ext.substr($url,$i+4);
}
/**
 * Returns the translated word (lowercase or plural version) from a language dictionnary
 * @param string $k key to be searched in the dictonnary (dot in the key allows searching sub-dictionnary)
 * @param int $n number>1 indicates plural. Null skips the plural search (and don't add number)
 * @param string $format format when $n is used ('n w' shows the number+word, '' hides the number)
 * @param array $A dictionnary (global $L by default)
 * @param string $pk parentkey (automatically assigned when accessing sub-dictionnary)
 * @param bool $dropDoublequote (default true)
 * @return string|array (can be an array of all sub-items when 'key.*' is requested)
 */
function L(string $k, int $n=null, string $format='n w', array $A=[], string $pk='', bool $dropDoublequote=true)
{
  // Initialise
  if ( !$A ) { global $L; $A = $L; } // if no dico, uses global $L
  $res = substr($k,-2)==='.*' ? [] : $pk.$k; // on failed returns the key (or an empty array)
  // On key with '.' works recursively (sub-dictionnary $A[...])
  if ( strpos($k, '.')>0 ) {
    $part = explode('.', $k, 2);
    $pk = $part[0];
    if ( empty($A[$pk]) || !is_array($A[$pk]) || $part[1]==='' ) return $res; // check sub-dictionnary and sub-key exists
    return $part[1]==='*' ? $A[$pk] : L($part[1], $n, $format, $A[$pk], $pk.'.');
  }
  // Format: php-format can be used, but note that this format applies ONLY when a $n exists (not null) and that the word is the first entry in the format
  switch($format) {
    case 'n w': $f = '%2$d %1$s'; break;
    case 'k w': $f = '%2$s %1$s'; break;
    case 'w':
    case '': $f = '$s'; break;
    default: $f = $format;
  }
  // Check if plural form must be searched (i.e. search for key with '+')
  $p = $n===null || $n<2 ? '' : '+';
  // Resolve word
  if ( !empty($A[$k.$p]) ) {
    $res = $A[$k.$p];
  } elseif ( !empty($A[ucfirst($k.$p)]) ) {
    $res = mb_strtolower($A[ucfirst($k.$p)]);
  } elseif ( !empty($A[$k]) ) {
    $res = $A[$k];
  } elseif ( !empty($A[ucfirst($k)]) ) {
    $res = mb_strtolower($A[ucfirst($k)]);
  } else {
    if ( substr($res,0,2)==='E_' ) $res = 'error: '.substr($res,2); // key is an (un-translated) error code, returns the error code
    if ( strpos($res,'_')!==false ) $res = str_replace('_',' ',$res); // When word is missing, returns the key code without _ (with the parentkey if used)
    if ( isset($_SESSION['QTdebuglang']) && $_SESSION['QTdebuglang'] ) $res = '<span style="color:red;text-shadow:0 0 2px black">'.$res.'</span>';
  }
  // Returns the word
  if ( $dropDoublequote && strpos($res,'"')!==false ) $res = str_replace('"','',$res); // recommended to secure html attr
  return $n===null ? $res : sprintf($f, $res, $format==='k w' ? qtK($n) : $n);
  /*
  Samples (using a french dictionnary):
  To access sub-dictionnary, use the '.' as key-separator
    L('DateMMM.Ja') returns 'Janvier' (the word in the dictionnary $L['DateMMM']['Ja'])
    L('DateMMM.*) returns an array of all words in the dictionnary $L['DateMMM'] (months)
  To get the lowercase version of a word, just use a key in lowercase
    L('domain') returns 'domaine' (because $L['Domain'] exists in the dictionnary)
    Tips: By convention, a dictionnary does not contain lowercase version of the words
          The function L() allows creating the lowercase version
          For sub-dictionnary, use the lowercase key only on the last part: L('DateMMM.ja') returns 'janvier'
  To get the plural form a word, use a number as second argument
    L('Domain', 3) returns '3 Domaines'
    L('domain', 2) returns '2 domaines' (lowercase version)
    L('Domain', 1) returns '1 Domaine' (for 0 or 1, no plural form)
    Tips: by default, the number is included in result. To hide the number use format '' as 3d argument.
  Fallback
    When a plural form is not defined in the dictionnary, the function use the singular form.
    If the requested key/subkey is not defined in the dictionnary the function returns the key itself without '_'
    L('Unknown_key') returns 'Unknown key'
  Debug
    If you define a session variable 'QTdebuglang' set to '1', the function shows in red the key not defined.
    A session variable can be set with url 'index.php?debuglang=1'
  */
}
function translate(string $file)
{
  $path = qtDirLang().$file;
  return file_exists($path) ? $path : 'language/en/'.$file;
}
function attrDecode(string $str, string $sep='|', string $required='')
{
  // Explode a compacted-string 'x1=y1|x2=y2|x3' into an array [x1=>y1,...]
  // Values are un-quoted. Attributes are lowercase. An attribute without value is allowed (value is null)
  // Note: For an unformatted $str, the array [0=>$str] is returned
  // $required allow adding some default attributes if not declared in $str
  if ( empty($str) && empty($required) ) return [];
  if ( !empty($required) ) $str = $required.$sep.$str;
  if ( substr_count($str,$sep)===0 && substr_count($str,'=')===0 ) return [$str]; // $str not compacted
  $attr = [];
  foreach(qtCleanArray($str,$sep) as $str) {
    $a = array_map('trim',explode('=',$str,2)); // cut on first '=' only
    if ( !isset($a[1]) || $a[1]==='' || $a[1]==='"' || $a[1]==='""' ) $a[1] = null; // support for attribute without value
    if ( isset($a[1]) ) {
      // remove first and last quote
      if ( substr($a[1],0,1)==='"' ) $a[1] = substr($a[1],1);
      if ( substr($a[1],-1,1)==='"' ) $a[1] = substr($a[1],0,-1);
    }
    $attr[$a[0]] = $a[1];
  }
  return array_change_key_case($attr); // W3C recommends attribute-names in lowercase, strict XHTML requires lowercase
}
function attrRender($attr=[], array $skip=[])
{
  // Supports 'addclass' attribute (appends the value to the class-list)
  if ( empty($attr) ) return '';
  if ( is_string($attr) ) $attr = attrDecode($attr);
  if ( !is_array($attr) ) die(__FUNCTION__.' invalid argument');
  if ( isset($attr['addclass']) ) { attrAddClass($attr,$attr['addclass']); unset($attr['addclass']); }
  $str = '';
  foreach ($attr as $k=>$value) {
    if ( !empty($skip) && in_array($k,$skip) ) continue;
    $str .= ' '.$k.'="'.str_replace('"','&quot;',$value).'"';
  }
  return $str;
}
function attrAddClass(array &$arr, string $value='')
{
  if ( empty($arr) || empty($value) ) return;
  if ( empty($arr['class']) ) { $arr['class'] = $value; return; }
  if ( strpos($arr['class'],$value)===false ) $arr['class'] .= ' '.$value;
}
function qtExt($file, bool $lowercase=true)
{
  $ext = strrpos($file,'.');
  if ( $ext===false || $ext===0 ) die(__FUNCTION__.' file extension not found');
  return $lowercase ? strtolower(substr($file,$ext+1)) : substr($file,$ext+1);
}
function qtDropExt($file)
{
  $i = strrpos($file,'.');
  if ( $i===false || $i===0 ) die(__FUNCTION__.' file extension not found');
  return substr($file,0,$i);
}
/**
 * Returns a [string] n and for big-int nK nM
 */
function qtK(int $n, string $unit='k', string $unit2='M')
{
  if ( $n<1000 ) return (string)$n;
  if ( $n<1000000 ) return round(floor($n/100)/10, 1).$unit; // Thousands: 1 decimal no round-up (9999 is 9.9k not 10k)
  return round($n/1000000,2).$unit2; // Millions: 2 decimals
}
function qtSVG(string $ref='info', string $attr='', string $wrapper='', bool $addSvgClass=false)
{
  if ( !file_exists('bin/svg/'.$ref.'.svg') ) return '#';
  $svg = file_get_contents('bin/svg/'.$ref.'.svg');
  if ( $addSvgClass) $svg = '<svg class="svg-'.$ref.'" '.substr($svg,4);
  if ( !empty($attr) && empty($wrapper) ) $wrapper = 'span'; // force span when attribute exists
  if ( !empty($wrapper) ) $svg = '<'.$wrapper.attrRender($attr).'>'. $svg.'</'.$wrapper.'>';
  return $svg;
}
/**
 * Returns the language path (with final /)
 */
function qtDirLang(string $iso='')
{
  if ( !empty($iso) ) return 'language/'.$iso.'/';
  if ( defined('QT_LANG') ) return 'language/'.QT_LANG.'/';
  if ( !empty($_SESSION[QT]['language']) ) return 'language/'.$_SESSION[QT]['language'].'/';
  global $oDB; return 'language/'.$oDB->getSetting('language','en').'/';
}
function qtDirData(string $root='', int $id=0, bool $check=false)
{
  // Get directory/subdirectory for Id (with final /).
  $i1 = $id>0 ? floor($id/1000) : 0;
  $i2 = $id-($i1*1000);
  $i2 = $i2>0 ? floor($i2/100) : 0;
  $path = $root.$i1.'000/'.$i1.$i2.'00';
  if ( !$check ) return $path.'/';
  return is_dir($path) ? $path.'/' : ''; // returns '' if directory not existing
}
function qtModule(string $name)
{
  return isset($_SESSION[QT]['module_'.$name]);
}
function qtCtype_digit(string $str)
{
  // Same as ctype_digit (in case of ctype library is disabled on the server)
  if ( function_exists('ctype_digit') ) return ctype_digit($str);
  if ( $str!=='' && preg_match('/^[0-9]+$/',$str) ) return true;
  return false;
}
/**
 * Assign GET/POST values into typed-variables listed in $defs. Suffix ! means a value is required
 * @param string $defs list of type:variable, space separated. Ex. "a! char2:b int:c boo:d"
 * @param boolean $inGet read value from $_GET
 * @param boolean $inPost read value from $_POST
 * @param boolean $trim trim value
 * @param boolean $striptags remove tags
 * @param boolean $dieOnCharSize die when value length is above charN, otherwhise returns substr
 * @return void global variables listed in $defs are re-assigned if the value exists in GET/POST
 */
function qtArgs(string $defs, bool $inGet=true, bool $inPost=true, bool $trim=true, bool $striptags=true, bool $dieOnCharSize=true)
{
  foreach(array_filter(explode(' ',$defs)) as $def) {
    if ( strpos($def,':')===false ) $def = 'str:'.$def; // no type is str
    $def = explode(':',$def); if ( count($def)!==2 ) die(__FUNCTION__.' unknown definition ['.$def.']');
    // reset
    $type = strtolower(substr($def[0],0,3)); if ( !in_array($type,['str','int','boo','flo','cha']) ) die(__FUNCTION__.' unknown type ['.$type.']');
    $required = false;
    $var = $def[1]; if ( substr($var,-1)==='!' ) { $var = substr($var,0,-1); $required = true; }
    $post = null;
    if ( $inGet && isset($_GET[$var]) ) $post = $_GET[$var];
    if ( $inPost && isset($_POST[$var]) ) $post = $_POST[$var]; // POST overwrites GET (ie. a variable well assigned by GET can become missing if set to '' here)
    // check and cast
    if ( $required && ($post==='' || $post===null) ) die(__FUNCTION__.' required argument ['.$var.'] is missing or without value');
    if ( is_null($post) ) continue;
    if ( $trim ) $post = trim($post);
    if ( $striptags ) $post = strip_tags($post);
    if ( ($type==='int' || $type==='flo') && !is_numeric($post) ) die(__FUNCTION__.' value ['.$var.'] cannot be casted as ['.$type.']');
    global $$var;
    switch($type) {
      case 'str': $$var = $post; break;
      case 'int': $$var = (int)$post; break;
      case 'boo': $$var = $post==='1' || strtolower($post)==='true' ? true : false; break;
      case 'flo': $$var = (float)$post; break;
      case 'cha':
        $size = substr($def[0],-1); // [optional] last character can be the length, between 1..9 (if other, uses 1)
        $size = !is_numeric($size) ? 1 : (int)$size; if ( $size===0 ) $size = 1;
        if ( $dieOnCharSize && isset($post[$size]) ) die(__FUNCTION__.' maximum '.$size.' char allowed for argument ['.$var.']');
        $$var = substr($post,0,$size);
        break;
    }
  }
  // Initializing the variables before using qtArgs is recommended. GET/POST values are urldecoded (build-in php)
  // Only variables in $defs are parsed and assigned. Other (url-injected) variables are skipped.
  // Supported types are [integer,float,boolean,string,char,charN] can be noted 'int:', 'flo:', 'boo:', 'str:', 'char:', 'charN:' in the $defs
  // For type 'boo', TRUE is assigned when GET/POST is '1' or 'true', FALSE is assigned for all other values.
  // For type 'char' (N=1) or 'charN' (N=2..9), $dieOnCharSize TRUE will stop the script when the length exceed N while FALSE assigns the N-first char.
  // Type 'char' is the same as 'char1'. Wrong type like 'char0' or 'charAZ' is casted as 'char1'.
  // For required variable (suffix !), script stops if the variable is not in GET/POST or is an empty string.
  // When a value is not in the GET/POST and not required, the initial variable remains unchanged (if not initialized, new variable is not created)
  // When both GET and POST are used, the POST-values overwrite GET-values (or are added to those already defined by GET).
}
/**
 * Generates a string of html-tags (option, checkbox, span or input-hidden) based on an array
 */
function qtTags(array $arr, $current='', string $attr='', string $attrCurrent='', array $disabled=[])
{
  // Validate arguments: $arr keys and $current can be [int|string] (will be converted to [string]). $disabled is list of keys [int|string]
  if ( is_int($current) ) $current = (string)$current;
  if ( !is_string($current) ) die(__FUNCTION__.' arg #2 must be int or string');
  $attr = attrDecode($attr,'|','tag=option'); // use 'option' if tag is not defined
  $tag = $attr['tag'];
  unset($attr['tag']);
  // Build result
  $str = '';
  foreach($arr as $k=>$value) {
    // format the value: $k and $value are converted to [string]
    $k = qtAttr($k); // $k is alway used as [string] attribute hereafter
    if ( is_array($value) ) $value = reset($value);
    if ( !empty($attrCurrent) && strlen($current)>0 && $current==$k ) $attr = attrDecode($attrCurrent);
    switch($tag) {
      // Attributes must use qtAttr(). $k is already converted with qtAttr()
      // Attention for checkbox value is used as id. $attrCurrent can mark one as checked
      case 'option':
        $str .= '<option value="'.$k.'"'.attrRender($attr).($current===$k ? ' selected' : '').(in_array($k,$disabled,true) ? ' disabled' : '').'>'.$value.'</option>';
        break;
      case 'checkbox':
        $str .= '<input type="checkbox" id="'.$k.'" value="'.$k.'"'.attrRender($attr).($current===$k || $current==='*' ? ' checked' : '').(in_array($k,$disabled) ? ' disabled ' : '').'/><label for="'.$k.'">'.$value.'</label>';
        break;
      case 'hidden':
        $str .= '<input type="hidden" name="'.$k.'" value="'.qtAttr($value).'"'.attrRender($attr).'/>';
        break;
      case 'span':
        $str .= '<span'.attrRender($attr).'>'.$value.'</span>';
        break;
      default:
        die(__FUNCTION__.' invalid tag');
    }
  }
  return $str;
}
/**
 * Convert string to compliant Html-attribute/sql-value (trim and drop doublequote)
 * @param string $str
 * @param int $size maximum size (0 unchanged, can be negatif)
 * @param string $unquote doublequote replacement
 * @return string
 */
function qtAttr(string $str, int $size=0, string $unquote='')
{
  if ( strpos($str,'"')!==false ) $str = str_replace('"',$unquote,$str);
  $str = trim($str);
  return $size && isset($str[$size]) ? substr($str,0,$size) : $str;
}
/**
 * Returns an array (key=>value) from a multfield string 'key1=value1;key2=value2'
 * @param string $str
 * @param string $sep separator
 * @param string $skip key to remove (can be a list with | separator)
 */
function qtExplode(string $str, string $sep=';', string $skip='')
{
  if ( empty($str) ) return [];
  $arr = [];
  $skip = explode('|', $skip);
  foreach(explode($sep, $str) as $str) {
    if ( strpos($str,'=')===false ) continue; // skip parts without =
    $parts = explode('=', $str, 2); $parts[0] = trim($parts[0]); // trim the keys
    if ( $parts[0]==='' || ($skip && in_array($parts[0], $skip, true)) ) continue;
    $arr[$parts[0]] = $parts[1];
  }
  return $arr;
  // $skip should be [string] key(s), nevertheless php convert integer-like-key to integer
}
/**
 * Explode the uri $str (or the current uri) to return an array of the query parts (not urldecoded)
 * @param string $str an uri like 'page.html?a=1&b=2#1' (if $str is empty, use the current URI)
 * @param string $skip key (argument) to exclude (can be a list with | separator)
 * @return array (empty array when the uri does not contains query parts)
 */
function qtExplodeUri(string $str='', string $skip='')
{
  if ( empty($str) ) $str = $_SERVER['REQUEST_URI'];
  $str = parse_url($str, PHP_URL_QUERY); // null if no url query part
  if ( empty($str) ) return [];
  if ( strpos($str,'&amp;')!==false ) $str = str_replace('&amp;','&',$str);
  return qtExplode($str, '&', $skip);
}
/**
 * Return the URI-part of the current REQUEST_URI
 * @param string $skip argument to be removed  (can be a list with | separator)
 * @param string $prefix is usualy '?'
 * @return string uri-arguments
 */
function qtURI(string $skip='', string $prefix='?')
{
  return $prefix.qtImplode(qtExplodeUri('',$skip));
}
/**
 * Search a specific key value in a multifield string 'a=1;b=2;c=3'
 * @param string $str
 * @param string $key
 * @param mixed $alt (value if key not found)
 * @param string $sep
 * @return string can be also $alt [mixed]
 */
function qtExplodeGet(string $str, string $key, $alt='', string $sep=';')
{
  // qtExplodeGet('a=1;b=2', 'a') returns '1'
  if ( empty($str) ) die(__FUNCTION__.' Invalid multifield string');
  if ( empty($key) ) die(__FUNCTION__.' Invalid key');
  $arr = qtExplode($str, $sep);
  return isset($arr[$key]) ? $arr[$key] : $alt;
  // Note on alt:
  // for wrong format, $alt is returned. Ex: in 'a;b=2', a is not a declaration string, thus $alt is returned
  // a key without value is valid (set to ''). Ex: in 'a=;b=2', a is declared, thus '' is returned
}
/**
 * Implode an array[key=>values] into a multifield string "key1=value1&key2=value2"
 */
function qtImplode(array $arr, string $sep='&', bool $skipNull=true, bool $skipVoid=true)
{
  $str = '';
  foreach($arr as $k=>$value) {
    if ( $skipNull && is_null($value) ) continue;
    if ( $skipVoid && $value==='' ) continue;
    $str .= (isset($str[0]) ? $sep : '').$k.'='.$value;
  }
  return $str;
  // TIPS: default separator is '&' because this function is MAINLY use to handle uri-arguments
}
/**
 * Explode, trim, remove empty values (also 0) and return unique values
 * @param string $str
 * @param string $sep cannot be space
 * @param array $append optional array to append (before trimming and filtering)
 * @return array array of string
 */
function qtCleanArray(string $str, string $sep=';', array $append=[])
{
  if ( empty($str) ) return $append ? array_unique(array_filter(array_map('trim',$append))) : [];
  if ( trim($sep)==='' ) die(__FUNCTION__.' invalid separator. For space use explode()');
  $arr = explode($sep,$str); if ( $append ) $arr = array_merge($arr,$append);
  return array_unique(array_filter(array_map('trim',$arr)));
  // NOTE: if $append contains sub-array, they are skipped and php throws a warning
}
/**
 * Switch between mail() and external-PHPMailer (unsing class.pop3.php and class.smtp.php)
 * @param string $strTo
 * @param string $strSubject
 * @param string $strMessage
 * @param string $strCharset
 * @param string $engine SMTP-engine (''=as configured, '1'=external-SMTP, '0'=php-mail)
 */
function qtMail(string $strTo, string $strSubject, string $strMessage, string $strCharset='utf-8', string $engine='')
{
  if ( $engine==='' ) $engine = $_SESSION[QT]['use_smtp'];
  switch($engine) {
  case '1':
    require 'bin/class/class.phpmailer.php';
    if ( substr($_SESSION[QT]['smtp_host'],0,4)==='pop3' ) {
      require 'bin/class/class.pop3.php';
      $pop = new POP3();
      $pop->Authorise($_SESSION[QT]['smtp_host'], $_SESSION[QT]['smtp_port'], 30, $_SESSION[QT]['smtp_username'], $_SESSION[QT]['smtp_password'], 1);
    }
    $mail = new PHPMailer();
    $mail->IsSMTP(); // set mailer to use SMTP
    $mail->Host = $_SESSION[QT]['smtp_host'];  // specify main and backup server
    $mail->Port = $_SESSION[QT]['smtp_port'];  // set the SMTP port for the GMAIL server
    $mail->SMTPAuth = true;     // turn on SMTP authentication
    $mail->Username = $_SESSION[QT]['smtp_username']; // SMTP username
    $mail->Password = $_SESSION[QT]['smtp_password']; // SMTP password
    $mail->From = $_SESSION[QT]['admin_email'];
    $mail->FromName = '';
    $mail->AddAddress($strTo);
    $mail->AddReplyTo($_SESSION[QT]['admin_email']);
    $mail->IsHTML(false);  // set email format to HTML
    $mail->Subject = $strSubject;
    $mail->Body    = $strMessage;
    $mail->AltBody = $strMessage;
    if ( !$mail->Send() ) throw new Exception( 'Message could not be sent: '.$mail->ErrorInfo.'<br>Subject: '.$mail->Subject.'<br>Message: '.$mail->Body );
    break;
  default:
    $strHeaders = 'Content-Type: text/plain; charset='.$strCharset;
    mail($strTo,$strSubject,$strMessage,'From:'.$_SESSION[QT]['admin_email']."\r\n".$strHeaders);
    break;
  }
}
function qtBasename(string $file, bool $chgUnderscore=true)
{
  // Returns the basename: filename in lowercase without prefix or extension
  $str = strtolower(str_replace(APP.'_','',$file));
  if ( $chgUnderscore && strpos($str,'_')!==false ) $str = str_replace('_','-',$str);
  $i = strrpos($str,'.');
  return  $i===false ? $str : substr($str,0,$i);
}
/**
 * Quote a string (works recursively in case of array)
 * @param string|int|float|array $txt
 * @param string $beg openquote (&' &" &< to get curved ' " <<)
 * @param string $end closequote (if empty, uses mirror curved symbol or openquote)
 * @param bool $keepNum int/float are returned as int/float (not quoted)
 * @return string|array (with array, index and non-string element remain unchanged)
 */
function qtQuote($txt, string $beg='"', string $end='', bool $keepNum=false)
{
  // Works recursively on array
  if ( is_array($txt) ) { foreach($txt as $k=>$item) $txt[$k] = qtQuote($item,$beg,$end,$keepNum); return $txt; }
  // Returns a quoted string (except int/float with $keepNum=true)
  if ( $keepNum && (is_int($txt) || is_float($txt)) ) return $txt;
  if ( empty($end) ) {
    // use well known open/closing symbols
    switch(strtolower($beg)) {
      case "&'":
      case '&#8216;':
      case '&#x2018;':
      case '&lsquo;': $beg = '&lsquo;'; $end = '&rsquo;'; break;
      case '&"':
      case '&#8220;':
      case '&#x201c;':
      case '&ldquo;': $beg = '&ldquo;'; $end = '&rdquo;'; break;
      case '&<':
      case '&#171;':
      case '&#xab;':
      case '&laquo;': $beg = '&laquo;'; $end = '&raquo;'; break;
      default: $end = $beg;
    }
  }
  if ( empty($beg) && empty($end) ) throw new Exception(__FUNCTION__.' invalid argument q');
  if ( is_string($txt) || is_int($txt) || is_float($txt) ) return $beg.$txt.$end;
  throw new Exception(__FUNCTION__.' invalid argument');
}
/**
 * Convert apostrophe and optionally "<>& to html entity (used for sql statement values insertion)
 * @param array|string $str (string or array of strings)
 * @param boolean $double convert doublequote (true by default)
 * @param boolean $tag convert < and > (true by default)
 * @return array|string
 */
function qtDb($str, bool $double=true, bool $tag=true)
{
  // Work recursievely on array, and [] returns [].
  if ( is_array($str) ) { foreach($str as $k=>$val) $str[$k] = qtDb($val,$double,$tag); return $str; }
  if ( !is_string($str) ) die(__FUNCTION__.' invalid argument #1');
  // same as CDatabase::sqlEncode (with $amp using config constant)
  if ( empty($str) ) return $str;
  if ( !defined('QT_CONVERT_AMP') ) define('QT_CONVERT_AMP', false);
  if ( QT_CONVERT_AMP && strpos($str,'&')!==false ) $str = str_replace('&','&#38;',$str); // must be first
  if ( $double && strpos($str,'"')!==false ) $str = str_replace('"','&#34;',$str);
  if ( $tag ) {
    if ( strpos($str,'<')!==false ) $str = str_replace('<','&#60;',$str);
    if ( strpos($str,'>')!==false ) $str = str_replace('>','&#62;',$str);
  }
  return strpos($str,"'")===false ? $str : str_replace("'",'&#39;',$str);
}
function qtDbDecode($str, bool $double=true, bool $tag=true)
{
  // Work recursievely on array, and [] returns [].
  if ( is_array($str) ) { foreach($str as $k=>$val) $str[$k] = qtDbDecode($val,$double,$tag); return $str; }
  if ( !is_string($str) ) die(__FUNCTION__.' invalid argument #1');
  // same as CDatabase::sqlEncode (with $amp using config constant)
  if ( empty($str) || strpos($str,'&')===false ) return $str;
  if ( strpos($str,'&#39;')!==false ) $str = str_replace('&#39;',"'",$str);
  if ( $double && strpos($str,'&#34;')!==false ) $str = str_replace('&#34;','"',$str);
  if ( $tag && strpos($str,'&#60;')!==false ) $str = str_replace('&#60;','<',$str);
  if ( $tag && strpos($str,'&#62;')!==false ) $str = str_replace('&#62;','>',$str);
  if ( !defined('QT_CONVERT_AMP') ) define('QT_CONVERT_AMP', false);
  if ( QT_CONVERT_AMP && strpos($str,'&#38;')!==false ) $str = str_replace('&#38;','&',$str); // must be last
  return $str;
}
function qtDropDiacritics($str)
{
  // Works recursively on array
  if ( is_array($str) ) { foreach($str as $k=>$item) $str[$k] = qtDropDiacritics($item); return $str; }
  // Process string (returns $str if failed)
  if ( !is_string($str) ) throw new Exception(__FUNCTION__.' invalid argument');
  $tl = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', Transliterator::FORWARD);
  $res = $tl->transliterate($str);
  return $res===false ? $str : $res;
}
function qtTrunc($txt, int $max=255, string $end='...')
{
  // Works recursively on array
  if ( is_array($txt) ) { foreach($txt as $k=>$item) $txt[$k] = qtTrunc($item,$max,$end); return $txt; }
  // Truncate and add the trailing $end
  if ( !is_string($txt) || $max<1 ) throw new Exception(__FUNCTION__.' invalid argument');
  if ( $max<=strlen($end) ) return $end; // truncate too short
  if ( isset($txt[$max]) ) $txt = substr($txt,0,$max-strlen($end)).$end;
  return $txt;
}
/**
 * Convert multiline text into one line (truncate and unbbc)
 * @param string $str
 * @param integer $max when >0 uses qtTrunc
 * @param string $end end characters used by qtTrunc
 * @param boolean $unbbc remove bbc tags
 * @param array $in texts to be replaced
 * @param array $out replacement texts
 * @return string
 */
function qtInline(string $str, int $max=255, string $end='...', bool $unbbc=true, array $in=["\r\n",'<br>','  '], array $out=[' ',' ',' '])
{
  if ( empty($str) ) return $str;
  if ( $max>0 ) $str = substr($str,0,$max+64); // reduce length if truncating
  // unbbc and inline
  if ( $unbbc ) $str = qtBBclean($str);
  $str = str_replace($in, $out, $str);
  // truncate
  return $max>0 ? qtTrunc($str,$max,$end) : $str;
}
function qtDateClean($d='now', int $size=14, string $e='?')
{
  if ( $d==='now' ) return substr(date('YmdHis'),0,$size);
  // Works recursively on array
  if ( is_array($d) ) { foreach($d as $k=>$item) $d[$k] = qtDateClean($item,$size,$e); return $d; }
  // Returns datetime [string] 'YYYYMMDD[HHMM[SS]]' from 'now'|integer|'YYYY-MM-DD HH:MM:SS'
  if ( is_int($d) && $d>0 ) $d = (string)$d;
  if ( !is_string($d) || $size<4 || $size>14 || strlen($d)<4 ) return $e;
  // Sanitize
  if ( $d===(string)(int)$d ) return substr($d,0,$size);
  $d = str_replace([' ','-','.','/',':'], '', $d);
  if ( $d===(string)(int)$d ) return substr($d,0,$size);
  return $e;
}
function qtDate($sDate='now', string $sOutDate='$', string $sOutTime='$', bool $friendly=true, bool $dropOldTime=true, bool $title=false, $e='?')
{
  // Converts a date [string|int|'now'] to a formatted date [string] and translate it.
  // $sDate - The date string, can be 'YYYYMMDD[HH][MM][SS]' or 'now'. It can include [.][/][-][ ]
  // $sOutDate - The output format for the date (or '$' to use constant FORMATDATE). Also accept 'RFC-3339' (this will ignore other parametres)
  // $sOutTime - The output format for the time (or '$' to use constant FORMATTIME). If not empty, time is appended to the date (or friendlydate)
  // $friendly - Replace date by 'Today','Yesterday'
  // $dropOldTime - Don't show time for date > 2 days.
  // $e - When $sDate is '0' or empty, or when the input date format is unsupported
  // The translation uses $L['dateSQL']. If not existing, the php engliqhe remains.
  $sDate = qtDateClean($sDate); // Clean $sDate to a [string] YYYYMMDD[HHMMSS] (max 14 char, supports 'now')
  if ( strlen($sDate)<4 || $sDate===$e ) return $e;
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
  if ( $intDate===false ) return $e;
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
  // Translating
  global $L;
  if ( isset($L['dateSQL']) && is_array($L['dateSQL']) ) $sDate = qtDateTranslate($sDate, $L['dateSQL']);
  // Exit
  if ( !$title ) return $sDate;
  return '<span'.(empty($titleid) ? '' : ' id="'.$titleid.'" ').' title="'.date('Y-m-d H:i:s',$intDate).'">'.$sDate.'</span>';
}
function qtDateTranslate(string $str, array $translations)
{
  if ( !$translations ) return $str;
  // to avoid recursive replacement, we build a dictionnary containing only the words used in $str
  $words = array_filter(explode(' ',preg_replace('/[^a-z]+/i', ' ', $str)));
  $dico = [];
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
function qtBBcode(string $str, string $nl='<br>', array $tip=array(), string $bold='<b>$1</b>', string $italic='<i>$1</i>')
{
  // Converts bbc to html
  // $str - [mandatory] a string than can contains bbc tags
  // $nl - convert \r\n, \r or \n to $nl. Use '' to not convert.
  // $tip - block info tips
  // $bold - (optional) format ex: <b>$1</b> or <span class="b">$1</span>
  // $italic - (optional) format ex: <b>$1</b> or <span class="i">$1</span>
  // Example qtBBcode('[b]Text[/b]') returns <b>Text</b>
  // Example qtBBcode('[i]<b>Text<b>[/i]') returns <i>&lt;b&gt;Text&lt;/b&gt;</i>

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
function qtBBclean(string $str, bool $deep=true, array $tip=array())
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
function qtIsBetween($n, $min=0, $max=99999)
{
  // Works recursively on array
  if ( is_array($n) ) { foreach($n as $k=>$item) if ( !qtIsBetween($item,$min,$max) ) return false; return true; }
  // Only numeric
  if ( !is_numeric($n) || !is_numeric($min) || !is_numeric($max) ) die(__FUNCTION__.' arguments must be a numeric');
  if ( $min>=$max ) die(__FUNCTION__.' invalid min > max');
  if ( $n<$min || $n>$max ) return false;
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