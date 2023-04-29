<?php // v4.0 build:20230205

// REQUIREMENTS:
// Methods use typed-arguments (basic types)
// Methods DONT use typed-return and typed-properties
// Compatible with php 7.1 (typed-properties requires php 7.4, typed-return method requires php 8, mixed or pseudo-types requires php 8)

/**
 * AContainer is a (abstract)class of generic properties: id, pid, title, descr, type, status, items
 * IContainer interface includes generic methods: create, delete, rename, setFrom
 * SMem class manages shared-memory (memcache/memcached)
 * SLang class manages application objects translations
 * CStat class provide basic statistics
 * Splash class to parse (then clear) a message coming from the previous page
 * @author qt-cute.org
 */

abstract class AContainer
{
  public $id = -1;            // [int] unique id
  public $title = 'untitled'; // [string] (mandatory) name
  public $pid = -1;           // [int] parent unique id (-1 means no-parent)
  public $ptitle = '';        // [string] (optional) parent name
  public $ownerid = 1;        // [int] (optional) owner id (moderator)
  public $ownername = 'Admin';// [string] (optional) owner name (moderator)
  public $descr = '';         // [string] (optional) description (can also be a ini-string)
  public $type = '';          // [string] ex: '0'=visible, '1'=hidden, '2'=hidden by user
  public $status ='';         // [string] ex: '0'=default, '1'=closed
  public $items = 0;          // [int] number of child items
}

interface IContainer
{
  public function setFrom($ref=null); // initialise
  static function create(string $title='untitled',int $pid=-1, bool $uniquetitle=true); // Add a new container in the storage-db
  static function delete(int $id);
  static function rename(int $id, string $title='untitled');
  static function getOwner(int $id);
}

class SMem
{
  // Attention: Uses 'memcached' library (php>7.2) but automatically tries to use legacy 'memcache' when memcached is not available.
  // This class uses memcache/ed (no session variables) and REQUIERES
  // - a function memInitialise allowing to re-generate specific keys-values you want to store
  // - constants QT, MEMCACHE_TIMEOUT, MEMCACHE_HOST, MEMCACHE_PORT and MEMCACHE_FAILOVER
  // These public methods use a 'simple' key to store the data.
  // Nevertheless, the actual key (private methods) has the namespace as prefix (the namespace QT)
  // Note #1: php will issue a fatal error if memInitialise is not defined or constants are missing
  // Note #2: Storing FALSE is not recommanded (key-not-found or server-failed can also returns false, causing a re-set of the key)
  // Note #3: Flush memory is not in this class: define your specific memFlush function (with the keys you need to flush)

  private static $library = 'memcached';
  private static $memory;
  public static function create(string &$warning)
  {
    if ( MEMCACHE_HOST ) {
      if  ( self::$library==='memcached' && !class_exists('Memcached') ) self::$library='memcache';
      if  ( self::$library==='memcache' && !class_exists('Memcache') ) self::$library='none';
      switch(self::$library) {
      case 'memcached':
        self::$memory = new Memcached();
        if ( !self::$memory->addServer(MEMCACHE_HOST,MEMCACHE_PORT) ) { $warning = 'Unable to contact memcache daemon ['.MEMCACHE_HOST.' port '.MEMCACHE_PORT.']. Turn this option to false in config/config_cst.php'; self::$memory = null; }
        break;
      case 'memcache':
        self::$memory = new Memcache;
        if ( !self::$memory->connect(MEMCACHE_HOST,MEMCACHE_PORT) ) { $warning = 'Unable to contact memcache daemon ['.MEMCACHE_HOST.' port '.MEMCACHE_PORT.']. Turn this option to false in config/config_cst.php'; self::$memory = null; }
        break;
      default:
        $warning = 'Memcached and Memcache libraries not found. Turn this option to false in config/config_cst.php';
        self::$library = 'none';
        self::$memory = null;
      }
    }
  }
  public static function getLibraryName()
  {
    if ( MEMCACHE_HOST ) return self::$library; // 'memcached', can be changed to 'memcache' or 'none' by Create function
    return 'Off'; // when config file disables the cache
  }
  public static function get(string $key, $alt=false, bool $set=true)
  {
    // Returns a dataset (if in memory) or regenerates it (if $reset true) using specific memInitialise()
    // memInitialise() returns $alt (false) for invalid $key
    $data = self::memcacheGet($key); // returns NULL if no connection, FALSE if key not found
    if ( $data===false && $set ) {
      $data = memInitialise($key,$alt); // Reset and store, memInitialise is specific for qt[f|i|e]
      SMem::set($key,$data);
    }
    return $data;
  }
  public static function set(string $key, $ref, int $timeout=MEMCACHE_TIMEOUT)
  {
    return SMem::memcacheSet($key,$ref,$timeout);
  }
  public static function clear(string $key)
  {
    SMem::memcacheClear($key);
  }
  // PRIVATE MEHTODS add namespace QT.$key as memcache-key
  private static function memcacheGet(string $key)
  {
    if ( self::$memory===null ) return;
    return self::$memory->get(QT.$key);
  }
  private static function memcacheSet(string $key, $ref, int $timeout=MEMCACHE_TIMEOUT)
  {
    if ( self::$memory===null ) return;
    return self::$library==='memcached' ? self::$memory->set(QT.$key,$ref,$timeout) : self::$memory->set(QT.$key,$ref,false,$timeout); // no compression flag with memcached
  }
  private static function memcacheClear(string $key)
  {
    if ( self::$memory===null || empty($key) ) return;
    if ( $key==='**' ) return self::$memory->flush(); // flush all memcache(s) for all applications!
    return self::$memory->delete(QT.$key);
  }
}

class SLang
{
  // Note: type/id/name uses following storage format:
  // 'index'   'i'  >> index name
  // 'domain'  'd1' >> domain 1 name
  // 'sec'     's1' >> section 1 name
  // 'secdesc' 's1' >> section 1 description
  public static function add(string $type='', string $lang='en', string $id='', string $name='')
  {
    if ( empty($type) || empty($lang) || empty($id) || empty($name) ) die('SLang::add invalid argument');
    if ( !defined('QT_CONVERT_AMP') ) define('QT_CONVERT_AMP',false);
    $lang = strtolower($lang);
    // Process
    global $oDB;
    $oDB->exec( "INSERT INTO TABLANG (objtype,objlang,objid,objname) VALUES (?,?,?,?)", [$type, $lang, $id, qtDb($name)] );
  }
  public static function delete(string $type='', string $id='')
  {
    if ( empty($type) || empty($id) ) die(__FUNCTION__.' invalid argument');
    $type = implode(',', qtQuoted(explode(',',$type), "'"));
    global $oDB;
    $oDB->exec( "DELETE FROM TABLANG WHERE objid='$id' AND objtype IN ($type)" );
  }
  public static function get(string $type='index', string $lang='en', string $id='*')
  {
    // Return the object translations. Can be empty array if translations not defined.
    // Can return an array of object names (in this language) when $id is '*'
    // Can return an array of object translation when $lang is '*'
    if ( empty($type) || empty($lang) || empty($id) ) die('SLang::get invalid argument');
    if ( $id==='*' && $lang==='*' ) die('SLang::get: Arg 2 and 3 cannot be *');
    $lang = strtolower($lang);
    // Process
    global $oDB;
    if ( $id==='*' ) {
      $arr = array();
      $oDB->query( "SELECT objid,objname FROM TABLANG WHERE objtype='$type' AND objlang='$lang'" );
      while($row=$oDB->getRow()) {
        if ( !empty($row['objname']) ) $arr[$row['objid']] = $row['objname'];
      }
      return $arr;
    } elseif ( $lang==='*' ) {
      $arr = array();
      $oDB->query( "SELECT objlang,objname FROM TABLANG WHERE objtype='$type' AND objid='$id'" );
      while($row=$oDB->getRow()) {
        $arr[$row['objlang']] = $row['objname'];
      }
      return $arr;
    } else {
      $oDB->query( "SELECT objname FROM TABLANG WHERE objtype='$type' AND objlang='$lang' AND objid='$id'" );
      $row=$oDB->getRow();
      return empty($row['objname']) ? '' : $row['objname'];
    }
  }
  public static function translate(string $type='index', string $id='i', string $alt='')
  {
    // Returns the translation - if defined! - (must be in session[QT]['L'])
    // Otherwhise returns $alt (or a default objectname is $alt is empty)
    if ( empty($type) || empty($id) ) die(__FUNCTION__.' invalid argument');
    // Look in translations
    $str = empty($GLOBALS['_L'][$type][$id]) ? '' : $GLOBALS['_L'][$type][$id];
    if ( empty($str) )
    {
      // Use alternate (except for index and descriptions)
      switch($type)
      {
      case 'index':  $str = empty($_SESSION[QT]['index_name']) ? '(index)' : $_SESSION[QT]['index_name']; break;
      case 'sec':    $str = empty($alt) ? '(section '.$id.')' : $alt; break;
      case 'domain': $str = empty($alt) ? '(domain '.$id.')' : $alt; break;
      case 'field':
      case 'status':
      case 'tab':    $str = empty($alt) ? ucfirst(str_replace('_',' ',$id)) : $alt; break;
      case 'secdesc':
      case 'statusdesc':
      case 'tabdesc':
      case 'ffield': return $alt; break;
      default: return '(unknown object '.$type.')';
      }
    }
    return $str;
  }
  // functions added for qt v4.0
  public static function addTranslations(string $type='', string $id='', string $name='', array $lang=[])
  {
    if ( empty($lang) ) $lang = array_keys(LANGUAGES);
    if ( empty($type) || empty($lang) || empty($id) || empty($name) ) die('SLang::addTranslations invalid argument');
    if ( !defined('QT_CONVERT_AMP') ) define('QT_CONVERT_AMP',false);
    // Process
    global $oDB;
    foreach($lang as $iso) $oDB->exec( "INSERT INTO TABLANG (objtype,objlang,objid,objname) VALUES (?,?,?,?)", [$type, $iso, $id, qtDb($name)] );
  }
  public static function deleteTranslations(array $type=[], string $id='', array $lang=[])
  {
    if ( empty($lang) ) $lang = array_keys(LANGUAGES);
    if ( empty($type) || empty($lang) || empty($id) ) die('SLang::deteteTanslations invalid argument');
    global $oDB;
    $type = implode("','", $type);
    $lang = implode("','", $lang);
    $oDB->exec( "DELETE FROM TABLANG WHERE objid='$id' AND objlang IN ('$lang') AND objtype IN ('$type')" );
  }
}

class CStats
{
  // This uses dynamic properties
  // Any properties can be created (i.e. to create a property 'items' just set $oStats->items=0)
  // The properties are stored in a session variable (i.e. $_SESSION[QT]['sys_stat_items'])
  // It's USELESS to create several object CStats (there is only one storage per session)
  // NOTE: when properties is not defined, __get throw an error message
  function __get(string $prop)
  {
    if ( empty($prop) ) die(__METHOD__.' property must be a string');
    if ( !isset($this->$prop) && isset($_SESSION[QT]['sys_stat_'.$prop]) ) $this->$prop = $_SESSION[QT]['sys_stat_'.$prop];
    if ( isset($this->$prop) ) return $this->$prop;
    throw new Exception( 'CStats: undefined property '.$prop );
  }
  function __set(string $prop,$value)
  {
    if ( empty($prop) ) die(__METHOD__.' property must be a string');
    $this->$prop = $value;
    $_SESSION[QT]['sys_stat_'.$prop] = $this->$prop;
  }
  public function removeProperty(string $prop)
  {
    if ( empty($prop) ) die(__METHOD__.' property must be a string');
    if ( isset($_SESSION[QT]['sys_stat_'.$prop]) ) unset($_SESSION[QT]['sys_stat_'.$prop]);
    if ( isset($this->$prop) ) unset($this->$prop);
  }
}

class Splash
{
  // Message can have 2 parts seprated by '|' (if no separator, message type is 'O' ok):
  // - a type identifier: 'O'=ok (default), 'I'=info, 'E'=error, 'W'=warning,
  // - a message
  // The message is displayed in a java popup block
  // The session variable is reset on each call
  public static function getSplash(bool $reset=true)
  {
    $type = self::getType();
    return '<div id="splash"><p id="splash-ico" style="'.self::getIconStyle($type).'">'.getSVG(self::getIconClass($type)).'</p><p id="splash-txt"></p></div>
<script type="text/javascript">
const splash = document.getElementById("splash");
const splashtxt = document.getElementById("splash-txt");
splashtxt.innerHTML = "'.self::getText($reset).'";
splash.style.animation = "splashFade 2s ease 1s 2 alternate";
setTimeout(function(){splash.style.display="none";},6000);
</script>
';
  }
  private static function getType()
  {
    // Returns only {O|E|W|I}. Returns 'O' for wrong or missing type
    if ( empty($_SESSION[QT.'splash']) ) return 'O';
    switch(strtoupper(substr($_SESSION[QT.'splash'],0,2))) {
      case 'E|': return 'E';
      case 'W|': return 'W';
      case 'I|': return 'I';
    }
    return 'O';
  }
  private static function getIconClass(string $type='O')
  {
    switch($type) {
      case 'E': return 'window-close';
      case 'W': return 'exclamation-triangle';
      case 'I': return 'info';
    }
    return 'check';
  }
  private static function getIconStyle(string $type='O')
  {
    switch($type) {
      case 'E': return 'font-size:2rem;color:red';
      case 'W': return 'font-size:2rem;color:orange';
      case 'I': return 'font-size:2rem;color:blue';
    }
    return 'font-size:2rem;color:green';
  }
  private static function getText(bool $reset=true)
  {
    if ( empty($_SESSION[QT.'splash']) ) return '';
    $str = $_SESSION[QT.'splash'];
    if ( $reset ) $_SESSION[QT.'splash'] = null;
    if ( in_array(strtoupper(substr($str,0,2)),['O|','E|','W|','I|']) ) $str = substr($str,2); // otherwise uses full $str
    if ( strpos($str,'"')!==false ) $str = str_replace('"','',$str);
    return isset($str[250]) ? substr($str,0,250).'...' : $str;
  }
}