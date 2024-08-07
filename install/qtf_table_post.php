<?php // v4.0 build:20240210
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtapost (
  id int,
  forum int default 0,
  topic int default 0,
  icon char(2) default "00",
  title varchar(64),
  type char(1) default "R",
  userid int,
  username varchar(24),
  issuedate varchar(20),
  modifdate varchar(20),
  modifuser int,
  modifname varchar(24),
  attach varchar(255),
  textmsg text,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtapost (
  id int CONSTRAINT pk_'.QDB_PREFIX.'qtapost PRIMARY KEY,
  forum int default 0,
  topic int default 0,
  icon char(2) default "00",
  title varchar(64) NULL,
  type char(1) default "R",
  userid int NULL,
  username varchar(24) NULL,
  issuedate varchar(20) NULL,
  modifdate varchar(20) NULL,
  modifuser int NULL,
  modifname varchar(24) NULL,
  attach varchar(255) NULL,
  textmsg varchar(8000) NULL
  )';
  break;

case 'pdo.pg':
case 'pg':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtapost (
  id integer,
  forum integer default 0,
  topic integer default 0,
  icon char(2) default "00",
  title varchar(64),
  type char(1) default "R",
  userid integer,
  username varchar(24),
  issuedate varchar(20),
  modifdate varchar(20),
  modifuser integer,
  modifname varchar(24),
  attach varchar(255),
  textmsg varchar(8000),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtapost (
  id integer,
  forum integer default 0,
  topic integer default 0,
  icon text default "00",
  title text,
  type text default "R",
  userid integer ,
  username text,
  issuedate text,
  modifdate text,
  modifuser integer,
  modifname text,
  attach text,
  textmsg text,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtapost (
  id number(32),
  forum number(32) default 0,
  topic number(32) default 0,
  icon char(2) default "00",
  title varchar2(64),
  type char(1) default "R",
  userid number(32),
  username varchar2(24),
  issuedate varchar2(20) default "0",
  modifdate varchar2(20) default "0",
  modifuser number(32),
  modifname varchar2(24),
  attach varchar(255),
  textmsg varchar(4000),
  CONSTRAINT pk_'.QDB_PREFIX.'qtapost PRIMARY KEY (id))';
  break;

default:
die('Database type ['.$oDB->type.'] not supported... Must be mysql, sqlsrv, pg, sqlite, oci');

}

$oDB->query($sql);