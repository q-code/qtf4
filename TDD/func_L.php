<?php

function L(string $k='', $n=false, bool $show_n=true, string $sep_n=' ', array $A=[], string $pk='')
{
  if ( $n!==false && !is_int($n) ) throw new Exception('Invalid argument');
  // if ( empty(trim($k)) ) throw new Exception('Invalid argument');
  // initialise
  if ( empty($A) ) { global $L; $A=$L; } // sub-dictionnary (or full dictionnary $L)
  $str = $pk.$k; // default is the key (if the key cannot match a dictionnary entry)
  // check subarray
  if ( strpos($k,'.')>0 )
  {
    $part = explode('.', $k,2);
    if ( empty($A[$part[0]]) || !is_array($A[$part[0]]) ) return $str;
    if ( $part[1]==='*' ) return $A[$part[0]];
    return L($part[1], $n, $show_n, $sep_n, $A[$part[0]], $part[0].'.');
  }
  // check if plural can be used
  $s = $n==false || $n<2 ? '' : 's';
  // search word
  if ( !empty($A[$k.$s]) ) {
    $str = $A[$k.$s];
  } elseif ( !empty($A[ucfirst($k.$s)]) ) {
    $str = strtolower($A[ucfirst($k.$s)]);
  } elseif ( !empty($A[$k]) ) {
    $str = $A[$k];
  } elseif ( !empty($A[ucfirst($k)]) ) {
    $str = strtolower($A[ucfirst($k)]);
  } elseif ( trim($k)=='' ) {
    throw new Exception('Invalid argument');
  } else {
    if ( substr($str,0,2)==='E_' ) $str = 'error: '.substr($str,2); // key is an (un-translated) error code, returns the error code
    if ( strpos($str,'_')!==false ) $str = str_replace('_',' ',$str); // When word is missing, returns the key code without _ (with the parentkey if used)
    if ( isset($_SESSION['QTdebuglang']) && $_SESSION['QTdebuglang'] ) $str = '<span style="color:red">'.$str.'</span>';
  }
  // if show number
  return ($n!==false && $show_n ? $n.$sep_n : '').$str;
  // ABOUT KEYS:
  // A dot in the key indicates a sub-dictionnary entry (when words are stored in array of array)
  // Dictionnary have case-sensitive keys (and first character is uppercase) and can have language specific plural form (key+s)
  // Using lowercase-key will lowercase the translation
  // Example in french:
  //   L('Save') returns 'Sauver',
  //   L('save') returns 'sauver'
  // You can add a number $n as second argument to request the plural form of a word. When $n>1 the plural form is returned (if the plural form is not defined in the dictionnary, the singular translation is returned)
  // Example in french:
  //   L('Domain',0) returns '0 Domaine' - here no plural but value is inserted
  //   L('domain',1) returns '1 domaine' - same but lowercase version
  //   L('Domain',2) returns '2 Domaines' - returns the plural version (if plural version exists in the dictionnary)
  //   L('Domain',2,false) returns 'Domaines' - returns the plural version, while the number is not displayed
  // Sub-array level are accessible with the dot-key and can be returned as array with the '*':
  //   Example:
  //   L('DateMMM.1') returns 'January'
  //   L('DateMMM.*') returns ['January',...,'Decembre']
  // Fallback - If the requested key (or subkey) is not defined in the language file (or the sub-array is not defined) the function returns the key itself without '_'
  //   Example:
  //   L('Unknown_key') returns 'Unknown key'
  //   L('Error.404') returns 'Error.404'
  //   L('E_101') [key used as error code] returns 'error: 101' when not defined in the language file.
  // Debug - If you define a session variable 'QTdebuglang' set to '1', the function shows in red the key not defined in the language file. A session variable can be set with url 'index.php?debuglang=1'
}