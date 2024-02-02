<?php // v6.3 build:202400202

/*
Methods use typed-arguments (basic types).
Methods typed-return and typed-properties are NOT used to stay compatible with php 7.1
(typed-properties requires php 7.4, method typed-return requires php 8, mixed or pseudo-types requires php 8)

REQUIREMENTS:
Constant QT must be defined before instanciating CDatabase
Constant QDB_SQLITEPATH must be defined if using SQLite engine
debug into log-stack requires in addition a [CHtml] $oH object

PUBLIC METHODS:
query() Perform a select query using PREPRARE statement when 2 arguments are provided, otherwise uses queryDirect
exec()  Perform a insert,update,delete,create query using PREPRARE statement when 2 arguments are provided, otherwise uses execDirect

PRIVATE METHODS:
queryDirect() Perform a select query.
execDirect()  Perform a insert,update,delete,create query. For non-PDO frameworks execDirect() uses queryDirect()

TIPS:
Properties $debug and startStats() can be overrided with session variables
through $_SESSION['QTdebugsql'] and $_SESSION['QTstatsql'] (not empty)
*/

class CDatabase
{

public $type;  // [public] framework (lowercase) used to connect database. Ex: pdo.mysql
private $host; // db host name and port if required. NOT USED with sqlite. When port is used, storing format is 'host=hostname;port=1234'
private $db;   // database name
private $user; // NOT USED with sqlite
private $pwd;  // NOT USED with sqlite
private $con;  // Connection as PDO object (or connection id for legacy driver)
private $qry;  // PDOstatement object (or query id for legacy driver)
private $transac = false; // is transaction started
private $singlequote = true; // String-literal delimiter: singlequote is recommended because db-server may have ansi_quote enabled (i.e. use doublequote)
public $stats; // [NULL|array] timer and counters (NULL = no stats). startStats() initializes the array.
public $error = ''; // Error message (must be set as string)
public $debug = false; // {true|false|'log'} show sql statement. With 'log' queries are send to CHtml->log stack
private $debugrole = 'U'; // {'V|U|M|A'} Staffmembers 'M|A' have rights to see the queries while debugging.

// SET/GET METHODS
public function setSinglequote(bool $b=true){ $this->singlequote = $b; }
public function getHost(){ return $this->type==='pdo.sqlite' || $this->type==='sqlite' ? '(local file) '.$this->db : $this->host; }
public function startStats(){ $this->stats = ['num'=>0, 'start'=>(float)vsprintf('%d.%06d', gettimeofday()), 'rows'=>0]; }

// CORE METHODS
public function __construct(string $type='', string $host='', string $db='', string $user='', string $pwd='', bool $createSqliteFile=false)
{
  $this->type = empty($type) ? QDB_SYSTEM : $type;
  $this->host = empty($host) ? QDB_HOST : $host;
  $this->db = empty($db) ? QDB_DATABASE : $db;
  if ( defined('QDB_SQLITEPATH') ) $this->db = QDB_SQLITEPATH.$this->db; // sqlite may required filepath "../" if php is running from subfolder
  $this->user = empty($user) ? QDB_USER : $user;
  $this->pwd = empty($pwd) ? QDB_PWD : $pwd;

  // QT SESSION hacks
  if ( isset($_SESSION[QT.'_usr']['role']) ) $this->debugrole = $_SESSION[QT.'_usr']['role'];
  if ( !empty($_SESSION['QTstatsql']) ) $this->startStats();
  if ( !empty($_SESSION['QTdebugsql']) ) $this->debug = $_SESSION['QTdebugsql']==='log' && isset($GLOBALS['oH']) ? 'log' : true;

  // Connect
  return $this->connect($createSqliteFile);
}
private function connect(bool $createSqliteFile=false)
{
  // This function connects the database (and select database if required)
  // Returns true|false
  $e = 'Unable to connect the database.';
  try {

    switch($this->type) {
      case 'pdo.mysql':
        $this->con = new PDO('mysql:host='.$this->host.';dbname='.$this->db, $this->user, $this->pwd);
        break;
      case 'pdo.sqlsrv':
        $this->con = new PDO('sqlsrv:server='.$this->host.';Database='.$this->db, $this->user, $this->pwd);
        break;
      case 'pdo.pg':
        $this->con = new PDO('pgsql:host='.$this->host.';dbname='.$this->db, $this->user, $this->pwd);
        break;
      case 'pdo.sqlite':
        if ( !file_exists($this->db) && !$createSqliteFile ) throw new Exception($e);
        $this->con = new PDO('sqlite:'.$this->db);
        break; // $this->db can be '' or contains the sqlite file
      case 'pdo.oci':
        $this->con = new PDO('oci:dbname='.$this->host, $this->user, $this->pwd);
        break;
      case 'mysql':
        $this->con = mysql_connect($this->host,$this->user,$this->pwd); if ( !$this->con ) throw new Exception($e);
        if ( !mysql_select_db($this->db,$this->con) ) throw new Exception('Cannot select database.');
        return true;
      case 'pg':
        $this->con = pg_connect('host='.$this->host.' dbname='.$this->db.' user='.$this->user.' password='.$this->pwd); if ( !$this->con ) throw new Exception($e);
        return true;
      case 'sqlite':
        if ( !file_exists($this->db) && !$createSqliteFile ) throw new Exception($e);
        $this->con = sqlite_open($this->db,0666,$this->error); if ( !$this->con ) throw new Exception($e);
        return true;
      case 'sqlsrv':
        $arr=array('Database'=>$this->db,'UID'=>$this->user,'PWD'=>$this->pwd);
        // use windows authentication if no UID and no PWD
        if ( empty($this->user) && empty($this->pwd) ) $arr=array('Database'=>$this->db);
        $this->con = sqlsrv_connect($this->host,$arr); if ( !$this->con ) throw new Exception($e);
        return true;
      case 'oci':
        $this->con = oci_connect($this->user,$this->pwd,$this->db); if ( !$this->con ) throw new Exception($e);
        return true;
      default:
        die('Database object interface ['.$this->type.'] is not supported.');
    }
    $b = $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // false if unable to set attributes
    if ( !$b ) $this->showDebug('Unable to add pdo-errormode attribute');

  } catch(Exception $e) {

    $this->halt($e, 'connect');
    return false;

  }
  return true;
}
public function beginTransac()
{
  if ( $this->transac ) return; // already in transaction
  switch($this->type) {
    case 'pdo.mysql':
    case 'pdo.sqlsrv':
    case 'pdo.pg':
    case 'pdo.oci':
    case 'pdo.sqlite':
      $this->transac = $this->con->beginTransaction(); // Can be false (in case of transaction not supported by server)
      break;
  }
  $this->showDebug('Begin transation: '.($this->transac ? 'success' : 'failed'));
}
public function commitTransac(bool $rollbackOnError=true)
{
  if ( !$this->transac ) return true; // already committed
  if ( !empty($this->error) && $rollbackOnError ) { $this->rollbackTransac(); return false; }
  $b = false; // commit successful
  switch($this->type) {
    case 'pdo.mysql':
    case 'pdo.sqlsrv':
    case 'pdo.pg':
    case 'pdo.sqlite':
    case 'pdo.oci': $b = $this->con->commit(); break;
  }
  $this->transac = false;
  $this->showDebug('Transaction committed: '.($b ? 'success' : 'failed'));
  return $b;
}
public function rollbackTransac()
{
  if ( !$this->transac ) return true; // already committed
  $b = false; // rollback successful
  switch($this->type) {
    case 'pdo.mysql':
    case 'pdo.sqlsrv':
    case 'pdo.pg':
    case 'pdo.sqlite':
    case 'pdo.oci': $b = $this->con->rollBack();
  }
  $this->transac = false;
  $this->showDebug('Rollback transaction: '.($b ? 'success' : 'failed'));
}
private function sqlPrepare(string $sql, array $sqlValues)
{
  // Emulates a prepare function for non pdo driver. Supports [?] (sequential) or [:named] placeholder)
  // Arguments are required.
  if ( empty($sql) || empty($sqlValues) || substr($this->type,0,4)==='pdo.' ) die(__METHOD__.' invalid arguments');

  // $sqlValue can have [int] index (when using ? placeholder) or [string]index (when using :named placeholder)
  if ( strpos($sql,'?')!==false ) {
    foreach($sqlValues as $val) $sql = preg_replace('/\?/', $val, $sql, 1);
  } else {
    foreach($sqlValues as $k=>$val) {
      if ( !is_string($k) ) throw new Exception('type mismatch (queries named keys in the array)');
      if ( substr($k,0,1)!==':' ) $k = ':'.$k; // autocomplete ':' with name
      $sql = str_replace($k, $this->sqlQuote($val), $sql);// String values are quoted and slashed
    }
  }
  $this->showDebug('Sql prepare: ', $sql);
  return $sql;
}
private function sqlQuote($value)
{
  if ( !is_string($value) ) return $value; // only string values are quoted
  $q = $this->singlequote ? "'" : '"';
  return $q.$value.$q;
}
private function sqlConst(string $sql)
{
  // Use actual tablenames (constant in the sql statement is replaced by the constant value) - only uppercase
  foreach(TABTABLES as $table) {
    if ( strpos($sql,$table) ) {
      $sql = str_replace($table, constant($table), $sql);
      if ( strpos($sql,'TAB')===false ) break; // skip if no more tab
    }
  }
  return $sql;
}
public function query(string $sql, array $sqlValues=[])
{
  $sql = $this->sqlConst($sql); // actual tablenames (constant-name in the sql statement is replaced by the constant value) - only uppercase
  // Direct query (no preparation args)
  if ( count($sqlValues)===0 ) return $this->queryDirect($sql);
  // Using preparation. PDO uses prepare() and execute(), non-pdo emulate a preparation with SqlPrepare
  if ( $this->singlequote && strpos($sql,'"')!==false ) $sql = str_replace('"', "'", $sql);
  $this->showDebug('Query prepared: ', $sql, $sqlValues);

  try {

    switch($this->type) {
      case 'pdo.mysql':
      case 'pdo.sqlsrv':
      case 'pdo.pg':
      case 'pdo.sqlite':
      case 'pdo.oci':
        $this->qry = $this->con->prepare($sql); // if ( $this->qry===false) throw new Exception('Preparation failed');
        $this->qry->execute($sqlValues); //returns false if failed
        break; // warning this->qry is now a PDOstatement object
      case 'mysql':  $sql = $this->sqlPrepare($sql,$sqlValues); if ( $sql===false) throw new Exception($this->error); $this->qry = mysql_query($sql,$this->con); break;
      case 'sqlsrv': $sql = $this->sqlPrepare($sql,$sqlValues); if ( $sql===false) throw new Exception($this->error); $this->qry = sqlsrv_query($this->con,$sql); break;
      case 'pg':     $sql = $this->sqlPrepare($sql,$sqlValues); if ( $sql===false) throw new Exception($this->error); $this->qry = pg_query($this->con,$sql); break;
      case 'sqlite': $sql = $this->sqlPrepare($sql,$sqlValues); if ( $sql===false) throw new Exception($this->error); $this->qry = sqlite_query($this->con,$sql); break;
      case 'oci':    $sql = $this->sqlPrepare($sql,$sqlValues); if ( $sql===false) throw new Exception($this->error); $this->qry = oci_parse($this->con,$sql); oci_execute($this->qry); break;
      default: die('db_type ['.$this->type.'] not supported.');
    }

  } catch(Exception $e) {

    $this->halt($e,$sql);
    return false;

  }
  if ( !$this->qry ) {
    $this->addErrorInfo();
    $this->halt(null,$sql);
    return false;
  }
  if ( $this->stats ) { ++$this->stats['num']; $this->stats['end']=(float)vsprintf('%d.%06d', gettimeofday()); }
  return true; // success
}
private function queryDirect(string $sql)
{
  if ( $this->singlequote && strpos($sql,'"')!==false ) $sql = str_replace('"',"'",$sql);
  $this->showDebug('Query direct: ', $sql);

  try {
    switch($this->type) {
      case 'pdo.mysql':
      case 'pdo.sqlsrv':
      case 'pdo.pg':
      case 'pdo.sqlite':
      case 'pdo.oci': $this->qry = $this->con->query($sql); break; // warning this->qry is now a PDOstatement object
      case 'mysql':   $this->qry = mysql_query($sql,$this->con); break;
      case 'sqlsrv':  $this->qry = sqlsrv_query($this->con,$sql); break;
      case 'pg':      $this->qry = pg_query($this->con,$sql); break;
      case 'sqlite':  $this->qry = sqlite_query($this->con,$sql); break;
      case 'oci':     $this->qry = oci_parse($this->con,$sql); oci_execute($this->qry); break;
      default: die('db_type ['.$this->type.'] not supported.');
    }
  } catch(Exception $e) {
    $this->halt($e,$sql);
    return false;
  }
  if ( !$this->qry ) {
    $this->addErrorInfo();
    $this->halt(null);
    return false;
  }
  if ( $this->stats ) { ++$this->stats['num']; $this->stats['end'] = (float)vsprintf('%d.%06d', gettimeofday()); }
  return true; // success
}
public function exec(string $sql, array $sqlValues=[])
{
  if ( $this->stats ) ++$this->stats['num'];
  $sql = $this->sqlConst($sql); // actual tablenames (constant-name in the sql statement is replaced by the constant value) - only uppercase

  // Direct query (no preparation args or no pdo)
  if ( count($sqlValues)===0 ) return $this->execDirect($sql);
  if ( substr($this->type,0,4)!=='pdo.' ) return $this->query($sql,$sqlValues);

  // Using preparation. PDO uses prepare() and execute(), non-pdo emulate a preparation with SqlPrepare
  try {
    if ( $this->singlequote && strpos($sql,'"')!==false ) $sql = str_replace('"',"'",$sql);
    $this->showDebug('Exec prepared: ', $sql, $sqlValues);
    $this->qry = $this->con->prepare($sql); // if ( $this->qry ===false) throw new Exception('Preparation failed');
    return $this->qry->execute($sqlValues); // CAUTION: returns true or false (no number of affect rows!)
  } catch (Exception $e) {
    $this->halt($e,$sql);
    return false;
  }
}
private function execDirect(string $sql)
{
  // Direct query (no preparation args)
  if ( substr($this->type,0,4)!=='pdo.' ) return $this->queryDirect($sql);

  // PDO exec
  try {
    if ( $this->singlequote && strpos($sql,'"')!==false ) $sql = str_replace('"',"'",$sql);
    $this->showDebug('Query direct: ', $sql);
    return $this->con->exec($sql); // Returns the number of affected rows. With CREATE TABLE, returns false if table exists
  } catch (Exception $e) {
    $this->halt($e,$sql);
    return false;
  }
}
public function count(string $sql, array $sqlValues=[])
{
  // Execute a "SELECT count(*) as countid FROM..." and returns [int] or 0 if field countid is missing
  if ( empty($sql) ) die(__METHOD__.' invalid sql');
  if ( strtoupper(substr($sql,0,6))!=='SELECT' ) $sql = 'SELECT count(*) as countid FROM '.$sql; // auto-complete when SELECT is missing
  $this->query($sql,$sqlValues);
  $row = $this->getRow();
  return isset($row['countid']) ? (int)$row['countid'] : 0;
}
public function nextId(string $table='', string $field='id', string $where='')
{
  if ( empty($table) || empty($field) ) die(__METHOD__.' invalid argument');
  return $this->count( "SELECT max($field)+1 as countid FROM $table $where" );
}
public function getRows(int $max=999)
{
  $rows = [];
  $i=0;
  while($row = $this->getRow()) { $rows[] = $row; ++$i; if ( $i===$max ) break; }
  return $rows;
}
public function getRow()
{
  $row = false;
  switch($this->type) {
    case 'pdo.mysql':
    case 'pdo.sqlsrv':
    case 'pdo.pg':
    case 'pdo.sqlite':
    case 'pdo.oci': $row = $this->qry->fetch(PDO::FETCH_ASSOC); break;
    case 'mysql': $row = mysql_fetch_assoc($this->qry); break; // php 5.0.3
    case 'sqlsrv':$row = sqlsrv_fetch_array($this->qry,SQLSRV_FETCH_ASSOC); break;
    case 'pg': $row = pg_fetch_assoc($this->qry); break;// php 4.3.0
    case 'sqlite':
      $row = sqlite_fetch_array($this->qry,SQLITE_ASSOC);// php 5.0
      if ( $row===false ) return false;
      $arr = [];
      foreach($row as $k=>$value) {
        if ( substr($k,1,1)==='.') $k = strtolower(substr($k,2));
        $arr[$k]=$value;
      }
      $row = $arr;
      break;
    case 'oci':
      $row = oci_fetch_assoc($this->qry); if ( $row===false ) return false;
      $arr = [];
      foreach($row as $k=>$value) $arr[strtolower($k)]=$value;
      $row = $arr;
      break;
    default: die('db_type ['.$this->type.'] not supported.');
  }
  return $row;
}
private function halt($e, string $sql='')
{
  if ( $sql==='connect' ) die('<p class="debug red">Please contact the webmaster for further information.<br>The webmaster must check that server is up and running, and that the settings in the config file are correct for the database.<br>'.$e.'</p>' );
  // Puts error message in $this->error, shows error and stop (following settings)
  // Prepare $this->error
  if ( is_a($e,'PDOException') ) {
    $this->error = $e->getCode().' '.$e->getMessage();
  } elseif ( is_a($e,'Exception') ) {
    $this->error = $e->getMessage(); $this->addErrorInfo();
  } else {
    $this->error = 'Database error while executing '.$sql;
  }
  throw new Exception($this->error);
}
private function addErrorInfo()
{
  // Puts error message in $this->error. Only used with non PDO
  switch($this->type) {
    case 'mysql':  $this->error .= '['.mysql_errno().'] '.mysql_error(); break;
    case 'sqlsrv': $err=end(sqlsrv_errors());  $this->error .= $err['message']; break;
    case 'pg':     $this->error .= pg_last_error(); break;
    case 'sqlite': $this->error .= '['.sqlite_last_error($this->con).'] '.sqlite_error_string(sqlite_last_error($this->con)); break;
    case 'oci':    $e=oci_error(); $this->error .= $e['message']; break;
  }
}
public function updSetting($param, $setting=null, bool $userAsAdmin=false)
{
  if ( !$userAsAdmin || $this->debugrole==='U' || $this->debugrole==='V'  ) die(__METHOD__.' access denied');
  // works recursively on array
  if ( is_array($param) ) { foreach($param as $item) $this->updSetting($item,$setting); return; }
  // NOTE: arguments must be [strict]string and cannot contain single-quote
  if ( !is_string($param) || empty($param) ) die(__METHOD__.' arg #1 must be a string');
  $setting = is_null($setting) && isset($_SESSION[QT][$param]) ? $_SESSION[QT][$param] : $setting;
  if ( !is_string($setting) ) die(__METHOD__.' arg #2 must be a string');
  if ( strpos($setting,"'")!==false || strpos($param,"'")!==false ) die(__METHOD__.' setting or param contains a quote');
  $this->exec( "UPDATE TABSETTING SET setting='$setting' WHERE param='$param'" );
}
public function getSettings(string $where='', bool $register=false)
{
  // Returns settings [array] matching with $where condition (use '' to get ALL settings). Can also register key-values in $_SESSION[QT]
  $arr = [];
  $this->query( "SELECT param,setting FROM ".TABSETTING.(empty($where) ? '' : ' WHERE '.$where) );
  while ($row = $this->getRow()) {
    $arr[$row['param']] = $row['setting'];
    if ( $register ) $_SESSION[QT][$row['param']] = $row['setting'];
  }
  return $arr;
}
public function getSetting(string $key='', string $alt='')
{
  // Returns the db setting [string] for param $key, or $alt if $key does not existing
  if ( empty($key) ) die(__METHOD__.' invalid key');
  $this->query( "SELECT setting FROM TABSETTING WHERE param='$key'" );
  $row = $this->getRow();
  return isset($row['setting']) ? (string)$row['setting'] : $alt;
}
public static function sqlEncode(string $str, bool $double=true, bool $amp=false, bool $tag=true)
{
  if ( empty($str) ) return $str;
  // Convert apostrophe (and optionally doublequote, &, <, >) to html entity (used for sql statement values insertion)
  if ( $amp && strpos($str,'&')!==false ) $str = str_replace('&','&#38;',$str);
  if ( $double && strpos($str,'"')!==false ) $str = str_replace('"','&#34;',$str);
  if ( $tag && strpos($str,'<')!==false ) $str = str_replace('<','&#60;',$str);
  if ( $tag && strpos($str,'>')!==false ) $str = str_replace('>','&#62;',$str);
  return strpos($str,"'")===false ? $str : str_replace("'",'&#39;',$str);
}
public static function sqlDecode(string $str, bool $double=true, bool $amp=false, bool $tag=true)
{
  if ( empty($str) || strpos($str,'&')===false ) return $str;
  if ( $amp && strpos($str,'&#38;')!==false ) $str = str_replace('&#38;','&',$str);
  if ( $double && strpos($str,'&#34;')!==false ) $str = str_replace('&#34;','"',$str);
  if ( $tag && strpos($str,'&#60;')!==false ) $str = str_replace('&#60;','<',$str);
  if ( $tag && strpos($str,'&#62;')!==false ) $str = str_replace('&#62;','>',$str);
  return strpos($str,'&#39;')===false ? $str : str_replace('&#39;',"'",$str);
}
private function showDebug(string $msg='Query: ', string $sql='',  array $sqlValues=[]) {
  // only role {M|A} staffmembers or admin can see the sql statement while debugging
  if ( !$this->debug ) return;
  if ( $sqlValues ) $msg .= '['.implode(' ',array_map(function($k,$v){return $k.'='.$v;},array_keys($sqlValues),$sqlValues)).'] ';
  if ( $sql ) $msg .= $this->debugrole==='M' || $this->debugrole==='A' ? $sql : '...you are not granted to see the statement';
  if ( $this->debug==='log' ) { global $oH; $oH->log[] = $msg; return; }
  echo '<p class="debug">'.$msg.'</p>';
}

}