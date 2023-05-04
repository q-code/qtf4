<?php // v4.0 build:20230205

/**
 * Convert qt-php-like url into html-like url when urlrewrite is active<br>Works only on the path-part (and next parts) of the url<br>Ex: 'qtx_login.php' becomes 'login.html'
 * @param string $url
 * @param string $ext extension (with dot)
 * @param boolean|string $prefix file prefix (true for the default)
 * @return string the converted url, or the original url when urlrewrite is not active
 */
function Href(string $url='', string $ext='.html', bool $prefix=true)
{
  if ( !QT_URLREWRITE ) return $url;
  if ( $prefix ) $prefix = APP.'_';
  if ( empty($url) ) die('Href: empty argument');
  // Ex: 'qtx_login.php?a=1' becomes 'login.html?a=1'
  $i = strlen($prefix);
  if ( $i && substr($url,0,$i)===$prefix ) $url = substr($url,$i); // $i>0
  if ( strpos($url,'.php') ) $url = str_replace('.php',$ext,$url);
  return $url;
}
function getSVG(string $id='info', string $attr='', string $wrapper='', bool $addSvgClass=false)
{
  if ( !file_exists('bin/css/svg/'.$id.'.svg') ) return '#';
  $svg = file_get_contents('bin/css/svg/'.$id.'.svg');
  if ( $addSvgClass) $svg = '<svg class="svg-'.$id.'" '.substr($svg,4);
  if ( !empty($attr) && empty($wrapper) ) $wrapper = 'span'; // force span when attribute exists
  if ( !empty($wrapper) ) $svg = '<'.$wrapper.attrRender($attr).'>'. $svg.'</'.$wrapper.'>';
  return $svg;
}

/**
 * Returns the language path (with final /)
 * @param string $str
 * @return string
 */
function getLangDir(string $iso='')
{
  $dir = 'language/';
  if ( empty($iso) ) {
    if ( defined('QT_LANG') ) return $dir.QT_LANG.'/';
    if ( !empty($_SESSION[QT]['language']) ) return $dir.$_SESSION[QT]['language'].'/';
    global $oDB; $iso = $oDB->getSetting('language','en'); // fallback
  }
  return $dir.$iso.'/';
}
function getRepository(string $root='', int $id=0, bool $check=false)
{
  // Get directory/subdirectory for Id (with final /).
  $i1 = $id>0 ? floor($id/1000) : 0;
  $i2 = $id-($i1*1000);
  $i2 = $i2>0 ? floor($i2/100) : 0;
  $path = $root.$i1.'000/'.$i1.$i2.'00';
  if ( !$check ) return $path.'/';
  return is_dir($path) ? $path.'/' : ''; // returns '' if directory not existing
}
function translate(string $file)
{
  if ( empty($file) ) die(__FUNCTION__.' invalid argument');
  if ( file_exists(getLangDir().$file) ) return getLangDir().$file;
  return 'language/en/'.$file;
}
function useModule(string $name)
{
  return isset($_SESSION[QT]['module_'.$name]);
}
function attrDecode(string $str, string $sep='|', string $required='')
{
  // Explode a compacted-string 'x1=y1|x2=y2|x3' into an array [x1=>y1,...]
  // Values are un-quoted. Attributes are lowercase. An attribute without value is allowed (value is null)
  // Note: For an unformatted $str, the array [0=>$str] is returned
  // $required allow adding some default attributes if not declared in $str
  if ( empty($str) && empty($required) ) return [];
  $str = $required.$sep.$str;
  if ( substr_count($str,$sep)===0 && substr_count($str,'=')===0 ) return [$str]; // $str not compacted
  $attr = array();
  foreach(asCleanArray($str,$sep) as $str)
  {
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

/**
 * Returns the translated word, or the lowercase or plural version, from the language dictionnary
 * @param string $k key to be searched in the dictonnary (dot in the key allows accessing sub-dictionnary)
 * @param int $n number >1 indicates plural. Null skips the plural search (and don't add number)
 * @param string $format format when $n is used ('n w' shows the number+word, '' hides the number and shows only the word)
 * @param array $A dictionnary (automatically assigned to the global dictionnary $L by default)
 * @param string $pk parentkey (automatically assigned when accessing sub-dictionnary)
 * @param bool $dropDoublequote (default true) removes " to secure html attr value insertion
 * @return string|array (can be an array of all sub-items when 'key.*' is requested)
 */
function L(string $k, int $n=null, string $format='n w', array $A=[], string $pk='', bool $dropDoublequote=true)
{
  // Initialise
  if ( empty($A) ) { global $L; $A = $L; } // if no dictionnary, uses global dictionnary $L
  $str = substr($k,-2)==='.*' ? [] : $pk.$k; // default result is the key (if dico entry not found) or an empty array (if dico array not found)
  // Format (formula shortcut)
  // Note: php format can also be used, but pay attention that $format is only used when a $n exists (not null) and that the word will be the 2nd input in the formula
  switch($format){
    case 'n w': $f = '%1$d %2$s'; break;
    case 'k w': $f = '%1$s %2$s'; break;
    case 'w':
    case '': $f = '%2$s'; break;
    default: $f = $format;
  }
  // Check subarray request (use recursive call)
  if ( strpos($k, '.')>0 ) {
    $part = explode('.', $k, 2);
    if ( empty($A[$part[0]]) || !is_array($A[$part[0]]) ) return $str;
    if ( $part[1]==='*' ) return $A[$part[0]];
    return L($part[1], $n, $format, $A[$part[0]], $part[0].'.');
  }
  // Check if plural form must be searched (i.e. search for key with '+')
  $p = $n===null || $n<2 ? '' : '+';
  // Resolve word
  if ( !empty($A[$k.$p]) ) {
    $str = $A[$k.$p];
  } elseif ( !empty($A[ucfirst($k.$p)]) ) {
    $str = mb_strtolower($A[ucfirst($k.$p)]);
  } elseif ( !empty($A[$k]) ) {
    $str = $A[$k];
  } elseif ( !empty($A[ucfirst($k)]) ) {
    $str = mb_strtolower($A[ucfirst($k)]);
  } else {
    if ( substr($str,0,2)==='E_' ) $str = 'error: '.substr($str,2); // key is an (un-translated) error code, returns the error code
    if ( strpos($str,'_')!==false ) $str = str_replace('_',' ',$str); // When word is missing, returns the key code without _ (with the parentkey if used)
    if ( isset($_SESSION['QTdebuglang']) && $_SESSION['QTdebuglang'] ) $str = '<span style="color:red;text-shadow:0 0 2px black">'.$str.'</span>';
  }
  // Return the word (with $n if not null)
  if ( $dropDoublequote && strpos($str,'"')!==false ) $str = str_replace('"','',$str);
  return $n===null ? $str : sprintf($f, $format==='k w' ? intK($n) : $n, $str);
/*
ABOUT KEYS:
A dot in the key indicates a sub-dictionnary entry (when words are stored in array of array)
Dictionnary have case-sensitive keys (and first character is uppercase) and can have language specific plural form (key+)
Using lowercase-key will lowercase the translation
  Example in french:
  L('Save') returns 'Sauver',
  L('save') returns 'sauver'
You can add a number $n as second argument to request the plural form of a word. When $n>1 the plural form is returned (if the plural form is not defined in the dictionnary, the singular translation is returned)
By default when a number is defined, it is added before the word. You can hide this number using $format=''
  Example in french:
  L('Domain',0) returns '0 Domaine' - here no plural (the number is inserted by default)
  L('domain',1) returns '1 domaine' - same but lowercase version
  L('Domain',2) returns '2 Domaines' - returns the plural version
  L('Domain',2,'') returns 'Domaines' - returns the plural version, while the number is hidden
Sub-array level are accessible with the dot-key (and can be returned as array with key.*)
  Example:
  L('DateMMM.Ja') returns 'January'
  L('DateMMM.*') returns ['January',...,'Decembre']
  Tips: When using sub-array, only the last key can be used to switch to the lowercase version
  L('DateMMM.ja') returns 'january'
  L('datemmm.ja') will fail (and the key is returned)
Fallback - If the requested key (or subkey) is not defined in the language file (or the sub-array is not defined) the function returns the key itself without '_'
  Example:
  L('Unknown_key') returns 'Unknown key'
  L('Error.404') returns 'Error.404'
  L('E_101') [key used as error code] returns 'error: 101' when not defined in the language file.
Debug - If you define a session variable 'QTdebuglang' set to '1', the function shows in red the key not defined in the language file. A session variable can be set with url 'index.php?debuglang=1'
*/
}

/**
 * Same as ctype_digit (in case of ctype library is disabled on the server)
 * @param string $str
 * @return boolean
 */
function qtCtype_digit(string $str)
{
  if ( function_exists('ctype_digit') ) return ctype_digit($str);
  if ( $str!=='' && preg_match('/^[0-9]+$/',$str) ) return true;
  return false;
}

/**
 * Assign GET/POST values into typed-variables listed in $def with prefix str: int: boo: or flo: defining the type. Suffix ! means a GET/POST is required
 * @param string $def list of type:variable, space separated. Ex. "a! b int:c boo:d"
 * @param boolean $inGet read value from $_GET
 * @param boolean $inPost read value from $_POST
 * @param boolean $trim trim value
 * @param boolean $striptags remove tags
 * @return void global variables listed in $args are re-assigned if the value exists in GET/POST
 */
function qtArgs(string $def, bool $inGet=true, bool $inPost=true, bool $trim=true, bool $striptags=true)
{
  // NOTES:
  // Initializing the variables before using qtArgs is recommended.
  // When a user try to url-inject other variables, they are skipped (only variables in $def are parsed).
  // Supported types are [integer,float,boolean,string]. Can be noted 'int:', 'flo:', 'boo:', 'str:'
  // With type 'boo' (boolean), the variable is set to TRUE when GET/POST is '1' or 'true'. For ALL OTHER values, FALSE is assigned.
  // For required variable (suffix !), script stops if the variable is not in GET/POST (or if get/post as an empty string).
  // When values are not in the Http GET/POST (and not marked as required), the initial variable remains unchanged (can be a new variable with NULL value if the variable was not initialised).
  // When values are red from both GET and POST, the POST values are assigned after. POST-values overwrite GET-values, and POST can include other variables (in addition to those already assigned from GET)
  // For required variable, the script stops if the variable is declared in GET but as an empty string (even if the variable is also declared in POST)
  // GET/POST values are urldecoded (build-in php)
  $def = array_filter(explode(' ',$def));
  foreach($def as $typedvar) {
    if ( strpos($typedvar,':')===false ) $typedvar = 'str:'.$typedvar; // default str: when no type
    $arr = explode(':',$typedvar); if ( count($arr)!==2 ) die(__FUNCTION__.'invalid format');
    $type = substr(trim($arr[0]),0,3); // first 3-lettres defines the type ('boolean', 'bool', 'boo' are valid. 'bol' throws a data type error)
    $var = trim($arr[1]); // global variable name
    if ( substr($var,-1)==='!' ) { $var = substr($var,0,-1); $required = true; } else { $required = false; } // required becomes FALSE when a value exists in GET or POST
    global $$var;
    if ( $inGet && isset($_GET[$var]) ) {
      if ( $required && $_GET[$var]==='' ) die(__FUNCTION__.'Required argument ['.$var.'] is without value'); // initially empty (before type check, trim or strip_tags)
      $required = false;
      if ( $trim ) $_GET[$var] = trim($_GET[$var]);
      if ( $striptags ) $_GET[$var] = strip_tags($_GET[$var]);
      if ( ($type==='int' || $type==='flo') && !is_numeric($_GET[$var]) ) die(__FUNCTION__.'Invalid type for argument '.$var);
      switch($type) {
        case 'str': $$var = $_GET[$var]; break;
        case 'int': $$var = (int)$_GET[$var]; break;
        case 'boo': $$var = $_GET[$var]==='1' || strtolower($_GET[$var])==='true' ? true : false; break;
        case 'flo': $$var = (float)$_GET[$var]; break;
        default: die(__FUNCTION__.'Invalid data type ['.$type.']');
      }
    }
    if ( $inPost && isset($_POST[$var]) ) {
      if ( $required && $_POST[$var]==='' ) die(__FUNCTION__.'Required argument ['.$var.'] is without value'); // initially empty (before type check, trim or strip_tags)
      $required = false;
      if ( $trim ) $_POST[$var] = trim($_POST[$var]);
      if ( $striptags ) $_POST[$var] = strip_tags($_POST[$var]);
      if ( ($type==='int' || $type==='flo') && !is_numeric($_POST[$var]) ) die(__FUNCTION__.'Invalid type for argument '.$var);
      switch($type) {
        case 'str': $$var = $_POST[$var]; break;
        case 'int': $$var = (int)$_POST[$var]; break;
        case 'boo': $$var = $_POST[$var]==='1' || strtolower($_POST[$var])==='true' ? true : false; break;
        case 'flo': $$var = (float)$_POST[$var]; break;
        default: die(__FUNCTION__.'Invalid data type ['.$type.']');
      }
    }
    // Still required, if required but is not in GET nor in POST
    if ( $required ) die(__FUNCTION__.'Required argument ['.$var.'] is missing');
  }
}

function asTags(array $arr, $current='', string $attr='', string $attrCurrent='', array $arrDisabled=[], string $fx='', array $reject=[], string $eol='')
{
  if ( !empty($fx) && !function_exists($fx) ) die(__FUNCTION__.' requested function ['.$fx.'] is unknown' );

  // $current and $arr indexes can be [int] but will be converted to [string]
  // When $arrDisabled is included, it must be an array of trimmed-strings

  if ( is_int($current) ) $current = (string)$current;
  if ( !is_string($current) ) die(__FUNCTION__.' arg #2 must be int or string');
  $attr = attrDecode($attr,'|','tag=option');
  $tag = $attr['tag'];
  unset($attr['tag']);

  $str = '';
  foreach($arr as $k=>$value)
  {
    if ( in_array($k,$reject) ) continue; // works when mixing [int|string]
    // format the value: $k and $value are converted to [string]
    $k = qtAttr($k); // $k is alway used as [string] attribute hereafter
    if ( is_array($value) ) $value = reset($value);
    if ( !empty($fx) ) $value = $fx($value);

    $itemAttr = $attr;
    if  ( !empty($attrCurrent) && strlen($current)>0 && $current==$k ) $itemAttr = attrDecode($attrCurrent);

    switch($tag)
    {
    // Attributes must use qtAttr(). $k is already converted with qtAttr()
    // Attention for checkbox value is used as id. $attrCurrentnt can mark one as checked
    case 'option'  : $str .= '<option value="'.$k.'"'.attrRender($itemAttr).($current===$k ? ' selected' : '').(in_array($k,$arrDisabled,true) ? ' disabled' : '').'>'.$value.'</option>'; break;
    case 'checkbox': $str .= '<input type="checkbox" id="'.$k.'" value="'.$k.'"'.attrRender($itemAttr).($current===$k || $current==='*' ? ' checked' : '').(in_array($k,$arrDisabled,true) ? ' disabled ' : '').'/><label for="'.$k.'">'.$value.'</label>'; break;
    case 'hidden'  : $str .= '<input type="hidden" name="'.$k.'" value="'.qtAttr($value).'"'.attrRender($itemAttr).'/>'; break;
    case 'span'    : $str .= '<span'.attrRender($itemAttr).'>'.$value.'</span>'; break;
    default: die(__FUNCTION__.' Invalid tag' );
    }
    if ( !empty($eol) ) $str .= $eol;
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
 * Add or change (or remove) a key-value in array. When value is NULL the key is removed
 * @param array $arr
 * @param integer|string $key
 * @param mixed $val
 * @return array
 */
function qtArrAdd(array $arr, $key, $val)
{
  if ( !is_string($key) && !is_int($key) ) die('qtArrAdd: invalid argument(s)');
  unset($arr[$key]); // remove the key
  if ( is_null($val) ) return $arr;
  $arr[$key] = $val; // add the key
  return $arr;
}

/**
 * Returns an array (key=>value) from a multfield string 'key1=value1;key2=value2'
 * @param string $str
 * @param string $sep separator
 * @param string $fx a function to apply to each value ex. 'trim', 'strtolower', 'urldecode'...
 * @return array (of string), keys are trimmed, unformated parts are skipped
 * <p>Returns an empty array when $str is empty (or no "=" or no keys)<br>
 * When duplicate keys exist, the last value overwrites previous values<br>
 * Values without keys are skipped, but a key without a value is valid i.e. 'key1=;key2=value2' returns ['key1'=>'','key2'=>'value2']</p>
 */
function qtExplode(string $str, string $sep=';', string $fx='')
{
  if ( empty($str) ) return [];
  if ( !empty($fx) && !function_exists($fx) ) die('qtExplode: '.$fx.' is unknown function');
  $arr = explode($sep,$str);
  $arrArgs = [];
  foreach($arr as $str) {
    if ( empty($str) || strpos($str,'=')===false ) continue; // skip parts without =
    $arrPart = explode('=',$str); $arrPart[0] = trim($arrPart[0]); // trim the keys
    if ( $arrPart[0]==='' ) continue; // skip when no-key
    $arrArgs[$arrPart[0]] = empty($fx) ? $arrPart[1] : $fx($arrPart[1]);
  }
  return $arrArgs;
}

/**
 * Explode, trim, remove empty values (also 0) and return unique values
 * @param string $str
 * @param string $sep cannot be space
 * @param array $append optional array to append (before trimming and filtering)
 * @return array array of string
 */
function asCleanArray(string $str, string $sep=';', array $append=[])
{
  if ( empty($str) ) return empty($append) ? [] : array_unique(array_filter(array_map('trim',$append)));
  if ( trim($sep)==='' ) die(__FUNCTION__.' invalid separator (use explode with space separator)' );
  $arr = explode($sep,$str); if ( !empty($append) ) $arr = array_merge($arr,$append);
  return array_unique(array_filter(array_map('trim',$arr)));
  // NOTE: if $append contains sub-array, they are skipped and php generates a warning
}

/**
 * Explode the uri $str (or the current uri) to return an array of the query parts (not urldecoded by default)
 * @param string $str an uri like 'page.html?a=1&b=2#1' (if $str is empty, use the current URI)
 * @param string $fx ex: 'urldecode' each value (key remains unchanged)
 * @return array (empty array when the uri does not contains query parts)
 */
function qtExplodeUri(string $str='', string $fx='')
{
  if ( empty($str) ) $str = $_SERVER['REQUEST_URI'];
  $str = parse_url($str,PHP_URL_QUERY); // null if no url query part
  if ( empty($str) ) return array();
  if ( strpos($str,'&amp;')!==false ) $str = str_replace('&amp;','&',$str);
  return qtExplode($str,'&',$fx);
}

/**
 * Search a specific key-value in a multifield string 'a=1;b=2;c=3'
 * @param string $str
 * @param string $key
 * @param mixed $alt (value if key not found)
 * @param string $sep
 * @param string $fx
 * @return string can be also $alt [mixed]
 */
function qtExplodeGet(string $str, string $key, $alt='', string $sep=';', string $fx='')
{
  // qtExplodeGet('a=1;b=2', 'a') returns '1'
  if ( empty($str) ) die(__FUNCTION__.' Invalid multifield string');
  if ( empty($key) ) die(__FUNCTION__.' Invalid key');
  $arr = qtExplode($str,$sep,$fx); // can be [] when str is empty
  return isset($arr[$key]) ? $arr[$key] : $alt;
  // Note on alt:
  // for wrong format, $alt is returned. Ex: in 'a;b=2', a is not a declaration string, thus $alt is returned
  // a key without value is valid (set to ''). Ex: in 'a=;b=2', a is declared, thus '' is returned
}

/**
 * Implode an array of key-values into a ini-string "key1=value1&key2=value2"
 * @param array $arr
 * @param string $sep separator character
 * @param string $fx function to apply to each value (ex: urlencode or strtolower)
 * @param boolean $skipNull do not include key=value when value is null
 * @return string (or '' if $arr is empty)
 */
function qtImplode(array $arr, string $sep='&', string $fx='', bool $skipNull=true)
{
  if ( !empty($fx) && !function_exists($fx) ) die(__FUNCTION__.' requested function ['.$fx.'] is unknown');
  $str = '';
  foreach($arr as $key=>$value)
  {
    if ( $skipNull && is_null($value) ) continue;
    $str .= (isset($str[0]) ? $sep : '').$key.'='.(empty($fx) ? $value : $fx($value));
  }
  return $str;
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
  $strHeaders = 'Content-Type: text/plain; charset='.$strCharset;
  if ( $engine==='' ) $engine = $_SESSION[QT]['use_smtp'];
  switch($engine)
  {
  case '1':
    require 'bin/class/class.phpmailer.php';
    if ( substr($_SESSION[QT]['smtp_host'],0,4)==='pop3' )
    {
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
    if ( !$mail->Send() )
    {
      echo '<br>Message could not be sent.';
      echo '<br>Mailer Error: ' . $mail->ErrorInfo;
      echo '<br>Subject: '.$mail->Subject;
      echo '<br>Message: '.$mail->Body;
      exit;
    }
    //echo "Message has been sent";
    break;
  default:
    mail($strTo,$strSubject,$strMessage,'From:'.$_SESSION[QT]['admin_email']."\r\n".$strHeaders);
    break;
  }
}
function baseFile(string $file, bool $minusUnderscore=true)
{
  // Returns the selfurl basename: filename without prefix or extension and in lowercase.
  // Option: converts '_' to '-'
  $str = strtolower(str_replace(APP.'_','',$file));
  if ( $minusUnderscore ) $str = str_replace('_','-',$str);
  $i = strrpos($str,'.'); if ( $i===false ) return $str;
  return substr($str,0,$i);
}
function intK(int $n, string $unit='k', string $unit2='M')
{
  if ( $n<1000 ) return $n;
  if ( $n<1000000 ) return round(floor($n/100)/10, 1).$unit;
  return round($n/1000000,2).$unit2;
}
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