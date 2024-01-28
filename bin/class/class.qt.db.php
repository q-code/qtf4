<?php // v6.2 build:20230618

// REQUIREMENTS:
// Methods use typed-arguments (basic types)
// Methods typed-return and typed-properties are NOT used to stay compatible with php 7.1 (typed-properties requires php 7.4, method typed-return requires php 8, mixed or pseudo-types requires php 8)

/*
PUBLIC METHODS:
__construct()  Construstor, uses the Connect() private method. Uses QDB_* constants if [null] arguments
query()        Perform an sql query (select). Use a PREPRARE statement when 2 arguments are provided, otherwise uses QueryDirect
exec()         Perform an sql query (insert,update,delete,create). Use a PREPRARE statement when 2 arguments are provided, otherwise uses ExecDirect
beginTransac() commitTransac() RolbackTransac() Manage transaction (only with pdo framework)
nextId()       Returns the next id [int] from a field
getRows(999)   Returns the rows (after a select query). By default the limit is 1000 rows.
getRow()       Returns the next row (after a select query)
startStats()   Starts the statistical info (timer and queries counter)
updSetting()   Update one appliation setting (in table TABSETTING, requires an Admin user-account)

PRIVATE METHODS:
queryDirect()  Perform an sql query (select).
execDirect()   Perform an sql query (insert,update,delete,create). For non-PDO frameworks execDirect() uses queryDirect()
Connect()      Connect database host, login and select database
halt()         Build error messages, show errors, and stop (level of error message is defined by $showerror)
addErrorInfo() Build error messages for non-PDO connection frameworks.

DEVELOPPER TIPS: following properties can be overrided with session variables.
$debug       can be set to TRUE through the session variable $_SESSION['QTdebugsql'] (not empty)
startStats() can be triggered through the session variable $_SESSION['QTstatsql'] (not empty)

REQUIERMENT: constante QT must be defined before instanciating CDatabase
REQUIERMENT: constante QDB_SQLITEPATH must be defined if using SQLite engine

debug into log-stack requires in addition a $oH object (instance of the CHtml class)
*/

class CDatabase
{

public $type; // framework used to connect database engine (must be in lowercase). Ex: pdo.mysql
public $error = ''; // Error message (must be set as string)
public $debug = false; // With debug true, queries are shown (and full error message), with 'log' query is send to CHtml->log stack
public $debugForStaffOnly = true; // When degug is active, show the SQL-query/prepare only when a staff/admin is connected (hides sql-statements when visitor/user try to debug)
public $stats; // timer and query counter, initialized by using startStats() method
private $host; // server host name and port if required. NOT USED with sqlite. When port is used, storing format is 'host=hostname;port=1234'
private $db;   // database name
private $user; // NOT USED with sqlite
private $pwd;  // NOT USED with sqlite
private $con;  // Connection as PDO object (or connection id for legacy driver)
private $qry;  // PDOstatement object (or query id for legacy driver)
private $transac = false; // is transaction started
private $singlequote = true; // String-literal delimiter: singlequote is recommended because db-server may have ansi_quote enabled (i.e. use doublequote as identifier)
private $userisstaff = false; // true when construct() detect that current user (session) is a staff member. Used to be independant from SUser class
private $throwexception = true; // Halt() will throw exception in case of sql error. With FALSE, the script continues. In case of database connection error it die()

public function __construct(string $type='', string $host='', string $db='', string $user='', string $pwd='', bool $createSqliteFile=false)
{
  $this->type = empty($type) ? QDB_SYSTEM : $type;
  $this->host = empty($host) ? QDB_HOST : $host;
  $this->db = empty($db) ? QDB_DATABASE : $db;
  if ( defined('QDB_SQLITEPATH') ) $this->db = QDB_SQLITEPATH.$this->db; // sqlite may required filepath "../" if php is running from subfolder
  $this->user = empty($user) ? QDB_USER : $user;
  $this->pwd = empty($pwd) ? QDB_PWD : $pwd;
  $this->userisstaff = isset($_SESSION[QT.'_usr']['role']) && ($_SESSION[QT.'_usr']['role']==='A' || $_SESSION[QT.'_usr']['role']==='M') ;
  if ( !empty($_SESSION['QTstatsql']) ) $this->startStats();
  // debug mode
  if ( isset($_SESSION['QTdebugsql']) && $_SESSION['QTdebugsql'] ) $this->debug = true;
  if ( isset($_SESSION['QTdebugsql']) && $_SESSION['QTdebugsql']==='log' && isset($GLOBALS['oHtml']) ) { $this->debug = 'log'; }
  // connect
  return $this->connect($createSqliteFile);
}
public function setSinglequote(bool $b=true)
{
  $this->singlequote = $b;
}
public function setThrowException(bool $b=true)
{
  $this->throwexception = $b;
}
private function connect(bool $createSqliteFile=false)
{
  // This function connects the database (and select database if required)
  // Returns true if connection was successful otherwise false
  try
  {
    switch($this->type)
    {
    case 'pdo.mysql': $this->con = new PDO('mysql:host='.$this->host.';dbname='.$this->db, $this->user, $this->pwd); break;
    case 'pdo.sqlsrv':
      $this->con = new PDO('sqlsrv:server='.$this->host.';Database='.$this->db, $this->user, $this->pwd); break;
    case 'pdo.pg': $this->con = new PDO('pgsql:host='.$this->host.';dbname='.$this->db, $this->user, $this->pwd); break;
    case 'pdo.sqlite':
      if ( !file_exists($this->db) && !$createSqliteFile ) throw new Exception('Unable to connect the database.');
      $this->con = new PDO('sqlite:'.$this->db);
      break; // $this->db can be '' or contains the sqlite file
    case 'pdo.oci': $this->con = new PDO('oci:dbname='.$this->host, $this->user, $this->pwd); break;
    case 'mysql':
      $this->con = mysql_connect($this->host,$this->user,$this->pwd); if ( !$this->con ) throw new Exception('Unable to connect the database.');
      if ( !mysql_select_db($this->db,$this->con) ) throw new Exception('Cannot select database.');
      return true;
      break;
    case 'pg':
      $this->con = pg_connect('host='.$this->host.' dbname='.$this->db.' user='.$this->user.' password='.$this->pwd); if ( !$this->con ) throw new Exception('Unable to connect the database.');
      return true;
      break;
    case 'sqlite':
      if ( !file_exists($this->db) && !$createSqliteFile ) throw new Exception('Unable to connect the database.');
      $this->con = sqlite_open($this->db,0666,$this->error); if ( !$this->con ) throw new Exception('Unable to connect the database.');
      return true;
      break;
    case 'sqlsrv':
      $arr=array('Database'=>$this->db,'UID'=>$this->user,'PWD'=>$this->pwd);
      // use windows authentication if no UID and no PWD
      if ( empty($this->user) && empty($this->pwd) ) $arr=array('Database'=>$this->db);
      $this->con = sqlsrv_connect($this->host,$arr); if ( !$this->con ) throw new Exception('Unable to connect the database.');
      return true;
      break;
    case 'oci':
      $this->con = oci_connect($this->user,$this->pwd,$this->db); if ( !$this->con ) throw new Exception('Unable to connect the database.');
      return true;
      break;
    default: die('Database object interface ['.$this->type.'] is not supported.');
    }
    $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return true;
  }
  catch(PDOException $e)
  {
    $this->halt($e,'CDatabase::connect');
    return false;
  }
  catch(Exception $e)
  {
    $this->halt($e,'CDatabase::connect');
    return false;
  }
  return true;
}
public function beginTransac()
{
  if ( $this->transac ) return; // already in transaction

  switch($this->type)
  {
  case 'pdo.mysql':
  case 'pdo.sqlsrv':
  case 'pdo.pg':
  case 'pdo.oci':
  case 'pdo.sqlite':
    $this->transac = $this->con->beginTransaction(); // Can be false (in case of transaction not supported by server)
    break;
  }
  if ( $this->debug===true ) echo '<p style="padding:2px;background-color:#fff;color:#1364B7;font-size:9pt;font-weight:normal;font-family:Verdana,Arial">SQL: begin transaction ('.($this->transac ? 'success' : 'failed').')</p>';
  if ( $this->debug==='log' ){ global $oH; $oH->log[] = 'SQL: begin transaction ('.($this->transac ? 'success' : 'failed').')'; }
}
public function commitTransac(bool $rollbackOnError=true)
{
  if ( !$this->transac ) return true; // already committed
  if ( !empty($this->error) && $rollbackOnError ) { $this->rollbackTransac(); return false; }

  $b = false; // commit successful
  switch($this->type)
  {
  case 'pdo.mysql':
  case 'pdo.sqlsrv':
  case 'pdo.pg':
  case 'pdo.sqlite':
  case 'pdo.oci': $b = $this->con->commit(); break;
  }
  $this->transac=false;

  if ( $this->debug===true ) echo '<p style="padding:2px;background-color:#fff;color:#1364B7;font-size:9pt;font-weight:normal;font-family:Verdana,Arial">SQL: transaction committed ('.($b ? 'success' : 'failed').')</p>';
  if ( $this->debug==='log' ) { global $oH; $oH->log[] = 'SQL: transaction committed ('.($b ? 'success' : 'failed').')'; }
  return $b;
}
public function rollbackTransac()
{
  if ( !$this->transac ) return true; // already committed

  $b = false; // rollback successful
  switch($this->type)
  {
  case 'pdo.mysql':
  case 'pdo.sqlsrv':
  case 'pdo.pg':
  case 'pdo.sqlite':
  case 'pdo.oci': $b = $this->con->rollBack(); break;
  }
  $this->transac=false;

  if ( $this->debug===true ) echo '<p style="padding:2px;background-color:#fff;color:#1364B7;font-size:9pt;font-weight:normal;font-family:Verdana,Arial">SQL: transaction rollbacked ('.($b ? 'success' : 'failed').')</p>';
  if ( $this->debug==='log' ) { global $oH; $oH->log[] = 'SQL: transaction rollbacked ('.($b ? 'success' : 'failed').')'; }
}
private function sqlPrepare(string $sql, array $sqlValues=[])
{
  // Emulate prepare function for non pdo driver: support ? (sequential) or :named placeholder)
  // Values must be string or numeric in an array with :named key (it is not possible to include function with value nor concatenating several values with comma)
  // String values are quoted and slashed in SqlQuote function.
  // Function can be skipped by using $sqlValues=[] (source $sql is returned)
  if ( empty($sqlValues) || substr($this->type,0,4)==='pdo.' ) return $sql;
  if ( empty($sqlValues) ) { $this->error='CDatabase::sqlPrepare type mismatch in arguments (must be an array)'; return false; }
  // emulate ? prepared statement
  if ( strpos($sql,'?')!==false ) {
    foreach($sqlValues as $value) $sql = preg_replace('/\?/', $value, $sql, 1);
  } else {
    // emulate :name prepared statement
    foreach($sqlValues as $key=>$value)
    {
      if ( !is_string($key) ) { $this->error='SqlPrepare: type mismatch in arguments (queried :named key array)'; return false; }
      if ( substr($key,0,1)!==':' ) $key = ':'.$key; // key format :name is required
      $sql = str_replace($key,$this->sqlQuote($value),$sql);
    }
  }
  if ( $this->debug===true ) echo '<p style="padding:2px;background-color:#fff;color:#1364B7;font-size:9pt;font-weight:normal;font-family:Verdana,Arial">SqlPrepare: '.($this->debugForStaffOnly && !$this->userisstaff ? '...you are not granted to see the statement' : $sql).'</p>';
  if ( $this->debug==='log' ) { global $oH; $oH->log[] = 'SqlPrepare: '.($this->debugForStaffOnly && !$this->userisstaff ? '...you are not granted to see the statement' : $sql); }
  return $sql;
}
private function sqlQuote($value)
{
  if ( !is_string($value) ) return $value;
  // only string values are quoted
  $q = $this->singlequote ? "'" : '"';
  return $q.$value.$q;
}
private function sqlConst(string $sql) {
  // Use actual tablenames (constant in the sql statement is replaced by the constant value) - only uppercase
  foreach(TABTABLES as $table) {
    if ( strpos($sql,$table) ) {
      $sql = str_replace($table,constant($table),$sql);
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
  if ( $this->singlequote && strpos($sql,'"')!==false ) $sql = str_replace('"',"'",$sql);
  if ( $this->debug===true ) echo '<p style="padding:2px;background-color:#fff;color:#1364B7;font-size:9pt;font-weight:normal;font-family:Verdana,Arial">Query prepared: ['.implode(' ',array_map(function($k,$v){return $k.'='.$v;},array_keys($sqlValues),$sqlValues)).'] '.($this->debugForStaffOnly && !$this->userisstaff ? '...you are not granted to see the statement' : $sql).'</p>';
  if ( $this->debug==='log' ) { global $oH; $oH->log[] = 'Query prepared: ['.implode(' ',array_map(function($k,$v){return $k.'='.$v;},array_keys($sqlValues),$sqlValues)).'] '.($this->debugForStaffOnly && !$this->userisstaff ? '...you are not granted to see the statement' : $sql); }
  try
  {
    switch($this->type)
    {
    case 'pdo.mysql':
    case 'pdo.sqlsrv':
    case 'pdo.pg':
    case 'pdo.sqlite':
    case 'pdo.oci':
      $this->qry = $this->con->prepare($sql); // if ( $this->qry ===false) throw new Exception('Preparation failed');
      $this->qry->execute($sqlValues); //returns false if failed
      break; // warning this->qry is now a PDOstatement object
    case 'mysql':  $sql = $this->sqlPrepare($sql,$sqlValues); if ( $sql===false) throw new Exception($this->error); $this->qry = mysql_query($sql,$this->con); break;
    case 'sqlsrv': $sql = $this->sqlPrepare($sql,$sqlValues); if ( $sql===false) throw new Exception($this->error); $this->qry = sqlsrv_query($this->con,$sql); break;
    case 'pg':     $sql = $this->sqlPrepare($sql,$sqlValues); if ( $sql===false) throw new Exception($this->error); $this->qry = pg_query($this->con,$sql); break;
    case 'sqlite': $sql = $this->sqlPrepare($sql,$sqlValues); if ( $sql===false) throw new Exception($this->error); $this->qry = sqlite_query($this->con,$sql); break;
    case 'oci':    $sql = $this->sqlPrepare($sql,$sqlValues); if ( $sql===false) throw new Exception($this->error); $this->qry = oci_parse($this->con,$sql); oci_execute($this->qry); break;
    default:       die('db_type ['.$this->type.'] not supported.');
    }
  }
  catch(PDOException $e)
  {
    $this->halt($e,$sql);
    return false;
  }
  catch(Exception $e)
  {
    $this->halt($e,$sql);
    return false;
  }
  if ( !$this->qry )
  {
    $this->addErrorInfo();
    $this->halt(null,$sql);
    return false;
  }
  if ( isset($this->stats) ) { ++$this->stats['num']; $this->stats['end']=(float)vsprintf('%d.%06d', gettimeofday()); }
  return true; // success
}
private function queryDirect(string $sql)
{
  if ( $this->singlequote && strpos($sql,'"')!==false ) $sql = str_replace('"',"'",$sql);
  if ( $this->debug===true ) echo '<p style="padding:2px;background-color:#fff;color:#1364B7;font-size:9pt;font-weight:normal;font-family:Verdana,Arial">Query direct: '.($this->debugForStaffOnly && !$this->userisstaff ? '...you are not granted to see the statement' : $sql).'</p>';
  if ( $this->debug==='log' ) { global $oH; $oH->log[] = 'Query direct: '.($this->debugForStaffOnly && !$this->userisstaff ? '...you are not granted to see the statement' : $sql); }
  try
  {
    switch($this->type)
    {
    case 'pdo.mysql':
    case 'pdo.sqlsrv':
    case 'pdo.pg':
    case 'pdo.sqlite':
    case 'pdo.oci':    $this->qry = $this->con->query($sql); break; // warning this->qry is now a PDOstatement object
    case 'mysql':      $this->qry = mysql_query($sql,$this->con); break;
    case 'sqlsrv':     $this->qry = sqlsrv_query($this->con,$sql); break;
    case 'pg':         $this->qry = pg_query($this->con,$sql); break;
    case 'sqlite':     $this->qry = sqlite_query($this->con,$sql); break;
    case 'oci':        $this->qry = oci_parse($this->con,$sql); oci_execute($this->qry); break;
    default:           die('db_type ['.$this->type.'] not supported.');
    }
  }
  catch(PDOException $e)
  {
    $this->halt($e,$sql);
    return false;
  }
  catch(Exception $e)
  {
    $this->halt($e,$sql);
    return false;
  }
  if ( !$this->qry )
  {
    $this->addErrorInfo();
    $this->halt(null);
    return false;
  }
  if ( isset($this->stats) ) { ++$this->stats['num']; $this->stats['end']=(float)vsprintf('%d.%06d', gettimeofday()); }
  return true; // success
}
public function exec(string $sql, array $sqlValues=[])
{
  if ( isset($this->stats) ) ++$this->stats['num'];
  $sql = $this->sqlConst($sql); // actual tablenames (constant-name in the sql statement is replaced by the constant value) - only uppercase

  // Direct query (no preparation args or no pdo)
  if ( count($sqlValues)===0 ) return $this->execDirect($sql);
  if ( substr($this->type,0,4)!=='pdo.' ) return  $this->query($sql,$sqlValues);

  // Using preparation. PDO uses prepare() and execute(), non-pdo emulate a preparation with SqlPrepare
  try
  {
    if ( $this->singlequote && strpos($sql,'"')!==false ) $sql = str_replace('"',"'",$sql);
    if ( $this->debug===true ) echo '<p style="padding:2px;background-color:#fff;color:#1364B7;font-size:9pt;font-weight:normal;font-family:Verdana,Arial">Exec prepared: ['.implode(' ',array_map(function($k,$v){return $k.'='.$v;},array_keys($sqlValues),$sqlValues)).'] '.($this->debugForStaffOnly && !$this->userisstaff ? '...you must be staff member to see the statement' : $sql).'</p>';
    if ( $this->debug==='log' ) { global $oH; $oH->log[] = 'Exec prepared: ['.implode(' ',array_map(function($k,$v){return $k.'='.$v;},array_keys($sqlValues),$sqlValues)).'] '.($this->debugForStaffOnly && !$this->userisstaff ? '...you must be staff member to see the statement' : $sql); }
    $this->qry = $this->con->prepare($sql); // if ( $this->qry ===false) throw new Exception('Preparation failed');
    return $this->qry->execute($sqlValues); // CAUTION: returns true or false (no number of affect rows!)
  }
  catch(PDOException $e)
  {
    $this->halt($e,$sql);
    return false;
  }
  catch (Exception $e)
  {
    $this->halt($e,$sql);
    return false;
  }
}
private function execDirect(string $sql)
{
  // Direct query (no preparation args)
  if ( substr($this->type,0,4)!=='pdo.' ) return $this->queryDirect($sql);

  // PDO exec
    try
    {
      if ( $this->singlequote && strpos($sql,'"')!==false ) $sql = str_replace('"',"'",$sql);
      if ( $this->debug===true ) echo '<p style="padding:2px;background-color:#fff;color:#1364B7;font-size:9pt;font-weight:normal;font-family:Verdana,Arial">Exec direct: '.($this->debugForStaffOnly && !$this->userisstaff ? '...you must be staff member to see the statement' : $sql).'</p>';
      if ( $this->debug==='log' ) { global $oH; $oH->log[] = 'Exec direct: '.($this->debugForStaffOnly && !$this->userisstaff ? '...you must be staff member to see the statement' : $sql); }
      return $this->con->exec($sql); // Returns the number of affected rows. With CREATE TABLE, returns false if table exists
    }
    catch(PDOException $e)
    {
      $this->halt($e,$sql);
      return false;
    }
    catch (Exception $e)
    {
      $this->halt($e,$sql);
      return false;
    }

}
/**
 * Execute a sql count (note: the sql can be shortened to tablename + where clause)
 * @param string $sql is handled as a shortened sql, unless it starts with SELECT
 * @param array|false $sqlValues list of values in case of prepared statement (false to make a direct query)
 * @return integer the count result (0 if no result field "countid")
 */
public function count(string $sql, array $sqlValues=[]) {
  // Execute a count(*) query. Can also be math function like max(id)...
  // Note: the computed value is supposed to be returned as integer (or false if no data found)
  if ( empty($sql) ) die('CDatabase::count invalid argument');
  if ( strtoupper(substr($sql,0,6))!=='SELECT' ) $sql = 'SELECT count(*) as countid FROM '.$sql; // support short sql statement
  $this->query($sql,$sqlValues);
  $row = $this->getRow();
  return isset($row['countid']) ? (int)$row['countid'] : 0;
}
public function nextId(string $table='', string $field='id', string $where='')
{
  if ( empty($table) || empty($field) ) die('CDatabase->nextId: invalid arguments');
  return $this->count( "SELECT max($field)+1 as countid FROM $table $where" );
}
public function getRows(int $max=999)
{
  $rows=array();
  $i=0;
  while($row=$this->getRow()) { $rows[]=$row; ++$i; if ( $i===$max ) break; }
  return $rows;
}
public function getRow()
{
  $row = false;
  switch($this->type)
  {
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
    foreach($row as $key=>$val)
    {
      if ( substr($key,1,1)==='.') $key = strtolower(substr($key,2));
      $arr[$key]=$val;
    }
    $row = $arr;
    break;
  case 'oci':
    $row = oci_fetch_assoc($this->qry); if ( $row===false ) return false;
    $arr = [];
    foreach($row as $key=>$val) $arr[strtolower($key)]=$val;
    $row = $arr;
    break;
  default: die('db_type ['.$this->type.'] not supported.');
  }
  return $row;
}
private function halt($e, string $sql='', bool $echo=true)
{
  // Puts error message in $this->error, shows error and stop (following settings)
  // Prepare $this->error
  if ( is_a($e,'PDOException') ) {
    $this->error = $e->getCode().' '.$e->getMessage();
  } elseif ( is_a($e,'Exception') ) {
    $this->error = $e->getMessage(); $this->addErrorInfo();
  } else {
    $this->error = 'Database error';
  }
  if ( $echo ) echo '<p style="padding:2px;background-color:#fff;color:#CC0000;font-size:9pt;font-weight:normal;font-family:Verdana,Arial">'.$this->error.($this->userisstaff ? '<br>'.$sql : '').'</p>';
  if ( $sql=='CDatabase::connect' ) die('<p style="padding:2px;background-color:#fff;color:#444;font-size:9pt;font-weight:normal;font-family:Verdana,Arial">Please contact the webmaster for further information.<br>The webmaster must check that server is up and running, and that the settings in the config file are correct for the database.</p>' );
  if ( $this->throwexception ) throw new Exception('[halt]');
}
private function addErrorInfo()
{
  // Puts error message in $this->error (only used with non PDO)
  switch($this->type)
  {
  case 'mysql':  $this->error .= '['.mysql_errno().'] '.mysql_error(); break;
  case 'sqlsrv': $err=end(sqlsrv_errors());  $this->error .= $err['message']; break;
  case 'pg':     $this->error .= pg_last_error(); break;
  case 'sqlite': $this->error .= '['.sqlite_last_error($this->con).'] '.sqlite_error_string(sqlite_last_error($this->con)); break;
  case 'oci':    $e=oci_error(); $this->error .= $e['message']; break;
  }
}
public function startStats()
{
  $t = (float)vsprintf('%d.%06d', gettimeofday());
  $this->stats=array( 'num'=>0, 'start'=>$t, 'pagestart'=>$t, 'rows'=>0 );
}
public function getHost() {
  if ( $this->type=='sqlite' || $this->type=='pdo.sqlite' ) return '(local file) '.$this->db;
  return $this->host;
}
/**
 * Update application setting(s), can be used only when user is Admin. Value must be unquoted
 * @param string|array $arrParam parametre(s) to update
 * @param null|string $value use null to get value from session variable having the same name
 * @param bool $userAsAdmin overwrite session user control (is used during setup)
 */
public function updSetting($param, $setting=null, bool $userAsAdmin=false) {
  if ( !$this->userisstaff && !$userAsAdmin ) die(__METHOD__.' access denied');
  // works recursively on array
  if ( is_array($param) ) { foreach($param as $item) $this->updSetting($item,$setting); return; }
  // NOTE: arguments must be [strict]string and cannot contain single-quote
  if ( !is_string($param) || empty($param) ) die(__METHOD__.' arg #1 must be a string');
  $setting = is_null($setting) && isset($_SESSION[QT][$param]) ? $_SESSION[QT][$param] : $setting;
  if ( !is_string($setting) ) die(__METHOD__.' arg #2 must be a string');
  if ( strpos($setting,"'")!==false || strpos($param,"'")!==false ) die(__METHOD__.' setting or param contains a quote');
  $this->exec( "UPDATE TABSETTING SET setting='$setting' WHERE param='$param'" );
}
public function getSettings(string $where='', bool $register=false) {
  // Returns settings [array] matching with $where condition (use '' to get ALL settings)
  // Can also register key-value in $_SESSION[QT]
  $arr = [];
  $this->query( "SELECT param,setting FROM ".TABSETTING. (empty($where) ? '' : ' WHERE '.$where) );
  while ($row = $this->getRow()) {
    $arr[$row['param']] = $row['setting'];
    if ( $register ) $_SESSION[QT][$row['param']] = $row['setting'];
  }
  return $arr;
}
public function getSetting(string $key='', string $dflt='')
{
  // Returns the db setting [string] for param $key, or $dflt if $key does not existing
  if ( empty($key) ) die('CDatabase::getSetting invalid key');
  $this->query( "SELECT setting FROM TABSETTING WHERE param='$key'" );
  $row = $this->getRow();
  return isset($row['setting']) ? (string)$row['setting'] : $dflt;
}
/**
 * Convert apostrophe (and optionally doublequote, &, <, >) to html entity (used for sql statement values insertion)
 * @param string $str
 * @param boolean $double convert doublequote (true by default)
 * @param boolean $amp convert ampersand (false by default)
 * @param boolean $tag convert < and > (true by default)
 * @return string
 */
public static function sqlEncode(string $str, bool $double=true, bool $amp=false, bool $tag=true) {
  if ( empty($str) ) return $str;
  if ( $amp && strpos($str,'&')!==false ) $str = str_replace('&','&#38;',$str);
  if ( $double && strpos($str,'"')!==false ) $str = str_replace('"','&#34;',$str);
  if ( $tag && strpos($str,'<')!==false ) $str = str_replace('<','&#60;',$str);
  if ( $tag && strpos($str,'>')!==false ) $str = str_replace('>','&#62;',$str);
  return strpos($str,"'")===false ? $str : str_replace("'",'&#39;',$str);
}
public static function sqlDecode(string $str, bool $double=true, bool $amp=false, bool $tag=true) {
  if ( empty($str) || strpos($str,'&')===false ) return $str;
  if ( $amp && strpos($str,'&#38;')!==false ) $str = str_replace('&#38;','&',$str);
  if ( $double && strpos($str,'&#34;')!==false ) $str = str_replace('&#34;','"',$str);
  if ( $tag && strpos($str,'&#60;')!==false ) $str = str_replace('&#60;','<',$str);
  if ( $tag && strpos($str,'&#62;')!==false ) $str = str_replace('&#62;','>',$str);
  return strpos($str,'&#39;')===false ? $str : str_replace('&#39;',"'",$str);
}

}