<?php

/**
 * Content : class CMenu
 * Version : 1.1 (requires php 7.3 or next)
 * Date    : 2022-11-03
 * Author  : qt-cute.org
 * Abstract:
 *   The class CMenu allows defining menu-items using compacted-string(s).
 *   The output method allows rendering all menu items, while one "active" menu can have attributes modified
 *   (adding highligh class, not-clickable, changing the html tag, ...)
 *   More info in the file class.qt.menu.php.txt
 */

class CMenu
{
  public $menu = [];       // array of menu definitions
  public $separator = ' '; // added between rendered menu items
  private static $format = '|';    // definition format: '|' or 'json' (compacted-string or json)
  private static $activeConf = []; // settings for active menu (decoded)

  /**
   * @param null|array|string $def menu definition
   * @param string $separator separator added between rendered menu items
   * @param string $format format compacted-string '|' or 'json'
   * @return void
   */
  public function __construct($def=null, string $separator=' ', string $format='|')
  {
    // configurations
    $this->separator = $separator;
    if ( empty($format) ) die(__METHOD__.' format is "|" or "json"' );
    self::$format = $format;
    // definitions {null|string|array}
    if ( $def===null ) return;
    if ( is_string($def) || is_array($def) ) return $this->add($def);
    die( __METHOD__.' invalid argument (def)' );
  }

  /**
   * @param string $activeid active menu (menu-index or id/href value included in the definition)
   * @param string $activeattr attributes to apply to the active menu ('default' or your own definition)
   * @param array $skip list of menu (index) to be skipped
   */
  public function build(string $activeid='', string $activeattr='default', array $skip=[])
  {
    // if using activeid and activeattr, prepares activeConf
    if ( $activeid!=='' && $activeattr!=='' ) {
      if ( substr($activeattr,0,7)==='default' ) {
        self::$activeConf = ['href'=>'javascript:void(0)', 'addclass'=>'active', 'onclick'=>'return false'];
        $activeattr = substr($activeattr,7); // supports more definitions after 'default'
        if ( !empty($activeattr) ) self::$activeConf = array_merge(self::$activeConf, self::attrDecode($activeattr, self::$format));
      } else {
        self::$activeConf = self::attrDecode($activeattr, self::$format);
      }
    }
    // render the menus
    $res = [];
    foreach($this->menu as $k=>$attr) {
      if ( !empty($skip) && in_array($k, $skip, true) ) continue;
      $res[] = self::render($attr, (string)$k, $activeid);
    }
    return implode($this->separator,$res);
  }

  /**
   * @param mixed $index index of the menu (argument can be skipped)
   * @param mixed $def menu definition (compacted-string or json format)
   * @return void
   */
  public function add($index=null, $def=null)
  {
    // Support one parameter call: use $index instead of $def
    if ( $def===null && $index!==null ) return $this->add(null,$index);
    // Works recursively with [array]: each definition is added to the menus stack.
    if ( is_array($def) ) { foreach($def as $index=>$value) $this->add($index,$value); return; }
    // Store the string definition
    if ( !is_string($def) ) die(__METHOD__.' invalid argument (def)' );
    if ( $index===null || is_int($index) ) return array_push($this->menu,$def);
    if ( is_string($index) ) return $this->menu[$index] = $def;
    die(__METHOD__.' invalid argument (index)' );
    // Attention: integer do not provide thrusted index (order may be wrong, array within array duplicates index)
    // That's why array_push() is used with interger/null index
    // [string]index as menu-identifier are safe, and ensure get() and update() methods pointing to the correct menu
  }

  /**
   * @param string $index index of the menu
   * @param string $key attribute to read
   * @return string value (or null if not found)
   */
  public function get(string $index='', string $key='text')
  {
    if ( empty($index) || empty($key) || !isset($this->menu[$index]) ) return; // Only works with [string]index menus
    $attr = self::attrDecode($this->menu[$index], self::$format);
    return isset($attr[$key]) ? $attr[$key] : null;
  }

  /**
   * @param string $index index of the menu
   * @param string $key attribute to change (or to insert if not yet existing)
   * @param string $value new value (null or '' removes the attribute)
   */
  public function update(string $index='', string $key='', string $value='')
  {
    // Only works if menus are declared with [string]index
    if ( empty($index) || empty($key) || !isset($this->menu[$index]) ) return false;
    // Update
    $attr = self::attrDecode($this->menu[$index], self::$format);
    if ( $value===null || $value==='' ) { unset($attr[$key]); } else { $attr[$key] = str_replace('"','',$value); }
    // Store result
    $arr = [];
    foreach($attr as $k=>$v) $arr[] = $k.'='.$v;
    $this->menu[$index] = strtolower(self::$format)==='json' ? json_encode($arr) : implode('|',$arr);
    return true;
  }

  public function remove($index)
  {
    unset($this->menu[$index]); // caution: unset('1') same as unset(1)
  }

  private static function render(string $def, string $currentid='', string $activeid='')
  {
    $attr = self::attrDecode($def, self::$format); // [array]
    if ( isset($attr['!']) ) return $attr['!']; // handled escaped (array contains only the source-definition)
    if ( !isset($attr['text']) ) $attr['text'] = 'menu';
    if ( !isset($attr['tag'])) $attr['tag'] = 'a';
    if ( $attr['tag']==='a' && empty($attr['href']) ) $attr['href'] = 'javascript:void(0)';
    if ( !empty($attr['addclass']) ) self::appendClass($attr, $attr['addclass']);
    // check if active
    if ( $activeid!=='') {
      $b = false;
      if ( $currentid===$activeid ) $b=true;
      if ( !$b && !empty($attr['id']) && $attr['id']===$activeid ) $b=true;
      if ( !$b && !empty($attr['href']) && substr($attr['href'],0,strlen($activeid))===$activeid ) $b=true;
      if ( !$b && !empty($attr['activewith']) && strpos($attr['activewith'],$activeid)!==false ) $b=true;
      if ( $b ) {
        foreach(self::$activeConf as $k=>$value)
        {
          if ( ($k==='tag' && empty($value)) || $k==='addclass' || $k==='activewith' ) continue; // don't change tag if active-tag is not specified
          $attr[$k] = $value;
        }
        // in case of 'addclass' append value
        if ( !empty(self::$activeConf['addclass']) ) self::appendClass($attr, self::$activeConf['addclass']);
      }
    }
    // render
    $str = $attr['tag'];
    // include href attribute
    $str .= $attr['tag']==='a' ? ' href="'.$attr['href'].'"' : '';
    // include other attributes
    foreach(array_keys($attr) as $k) $str .= in_array($k,['text','tag','href','addclass','activewith']) || empty($k) ? '' : ' '.$k.'="'.$attr[$k].'"';
    // exit
    return '<'.$str.'>'.$attr['text'].'</'.$attr['tag'].'>';
  }

  private static function attrDecode(string $str, string $sep='|', string $esc='!')
  {
    if ( empty($str) ) return [];
    if ( strlen($esc)===1 && $str[0]===$esc) return ['!'=>substr($str,1)]; // string start with the escape character: decoding is skipped and source string is returned
    if ( strtolower($sep)==='json' ) return self::attrDecodeJson($str); //...
    $attr = array();
    foreach(self::asCleanArray($str,$sep) as $str)
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
    // on missing text, check if first value can be used
    if ( !isset($attr['text']) ) {
      $key = array_key_first($attr);
      if ( $attr[$key]===null ) { $attr['text']=$key; unset($attr[$key]); }
    }
    // exit (W3C recommends attribute-names in lowercase, strict XHTML requires lowercase)
    return array_change_key_case($attr);
  }

  private static function attrDecodeJson(string $str)
  {
    if ( $str==='{}' ) return [];
    $attr = json_decode($str,true);
    if ( empty($attr) || !is_array($attr) ) $attr = array('tag'=>'span','id'=>'CMenu::error','title'=>'definition not readable');
    return array_change_key_case($attr);
  }

  private static function appendClass(array &$arr, string $value='')
  {
    if ( empty($arr) || empty($value) ) return;
    if ( empty($arr['class']) ) { $arr['class'] = $value; return; }
    if ( strpos($arr['class'],$value)===false ) $arr['class'] .= ' '.$value;
  }

  private static function asCleanArray(string $str, string $sep=';')
  {
    if ( empty($str) ) return [];
    if ( trim($sep)==='' ) die(__METHOD__.' invalid separator (use explode with space separator)' );
    return array_unique(array_filter(array_map('trim',explode($sep,$str))));
  }
}