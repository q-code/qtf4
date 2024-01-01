<?php // v4.0 build:20230618
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtalang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtalang (
  objtype varchar(10) NOT NULL,
  objlang varchar(2) NOT NULL,
  objid varchar(24) NOT NULL,
  objname varchar(4000) NULL,
  CONSTRAINT pk_'.QDB_PREFIX.'qtalang PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'pdo.pg':
case 'pg':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtalang (
  objtype varchar(10),
  objlang varchar(2),
  objid varchar(24),
  objname varchar(4000),
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtalang (
  objtype text,
  objlang text,
  objid text,
  objname text,
  PRIMARY KEY (objtype,objlang,objid)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtalang (
  objtype varchar2(10),
  objlang varchar2(2),
  objid varchar2(24),
  objname varchar2(4000),
  CONSTRAINT pk_'.QDB_PREFIX.'qtalang PRIMARY KEY (objtype,objlang,objid))';
  break;

default:
  die('Database type ['.$oDB->type.'] not supported... Must be mysql, sqlsrv, pg, sqlite, oci');

}

echo '<span style="color:blue">';
$b = $oDB->query($sql);
echo '</span>';

if ( !empty($oDB->error) || !$b )
{
  echo '<div class="setup_err">',sprintf (L('E_install'),QDB_PREFIX.'qtalang',QDB_DATABASE,QDB_USER),'</div>';
  echo '<br><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qtf_setup_1.php">',L('Restart'),'</a>&nbsp;</td></tr></table>';
  exit;
}