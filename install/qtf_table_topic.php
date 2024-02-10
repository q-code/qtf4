<?php // v4.0 build:20240210
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
$sql = 'CREATE TABLE '.QDB_PREFIX.'qtatopic (
  id int,
  numid int default 0,
  forum int default 0,
  type char(1) default "T",
  status char(1) NOT NULL default "0",
  statusdate varchar(20) NOT NULL default "0",
  tags varchar(4000),
  firstpostid int default 0,
  lastpostid int default 0,
  firstpostuser int default 0,
  lastpostuser int default 0,
  firstpostname varchar(24),
  lastpostname varchar(24),
  firstpostdate varchar(20),
  lastpostdate varchar(20),
  replies int default 0,
  views int default 0,
  modifdate varchar(20) NOT NULL default "0",
  PRIMARY KEY (id)
)';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtatopic (
  id int CONSTRAINT pk_'.QDB_PREFIX.'qtatopic PRIMARY KEY,
  numid int default 0,
  forum int default 0,
  type char(1) default "T",
  status char(1) NOT NULL default "0",
  statusdate varchar(20) NOT NULL default "0",
  tags varchar(4000),
  firstpostid int default 0,
  lastpostid int default 0,
  firstpostuser int default 0,
  lastpostuser int default 0,
  firstpostname varchar(24),
  lastpostname varchar(24),
  firstpostdate varchar(20),
  lastpostdate varchar(20),
  replies int default 0,
  views int default 0,
  modifdate varchar(20) NOT NULL default "0"
  )';
  break;

case 'pdo.pg':
case 'pg':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtatopic (
  id integer,
  numid integer default 0,
  forum integer default 0,
  type char(1) default "T",
  status char(1) NOT NULL default "0",
  statusdate varchar(20) NOT NULL default "0",
  tags varchar(4000),
  firstpostid integer default 0,
  lastpostid integer default 0,
  firstpostuser integer default 0,
  lastpostuser integer default 0,
  firstpostname varchar(24),
  lastpostname varchar(24),
  firstpostdate varchar(20),
  lastpostdate varchar(20),
  replies integer default 0,
  views integer default 0,
  modifdate varchar(20) NOT NULL default "0",
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtatopic (
  id integer,
  numid integer default 0,
  forum integer default 0,
  type text default "T",
  status text default "0",
  statusdate text NOT NULL default "0",
  tags text,
  firstpostid integer default 0,
  lastpostid integer default 0,
  firstpostuser integer default 0,
  lastpostuser integer default 0,
  firstpostname text,
  lastpostname text,
  firstpostdate text,
  lastpostdate text,
  replies integer default 0,
  views integer default 0,
  modifdate text NOT NULL default "0",
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtatopic (
  id number(32),
  numid number(32) default 0,
  forum number(32) default 0,
  type char(1) default "T",
  status char(1) default "0",
  statusdate varchar2(20) default "0",
  tags varchar2(4000),
  firstpostid number(32) default 0,
  lastpostid number(32) default 0,
  firstpostuser number(32) default 0,
  lastpostuser number(32) default 0,
  firstpostname varchar2(24),
  lastpostname varchar2(24),
  firstpostdate varchar2(20) default "0",
  lastpostdate varchar2(20) default "0",
  replies number(32) default 0,
  views number(32) default 0,
  modifdate varchar2(20) default "0",
  CONSTRAINT pk_'.QDB_PREFIX.'qtatopic PRIMARY KEY (id))';
  break;

default:
  die('Database type ['.$oDB->type.'] not supported... Must be mysql, sqlsrv, pg, oci');
}

$oDB->query($sql);