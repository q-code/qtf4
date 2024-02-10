<?php // v4.0 build:20240210
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtadomain (
  id int,
  title varchar(64) default "untitled",
  titleorder int default 0,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtadomain (
  id int CONSTRAINT pk_'.QDB_PREFIX.'qtadomain PRIMARY KEY,
  title varchar(64) default "untitled",
  titleorder int default 0
  )';
  break;

case 'pdo.pg':
case 'pg':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtadomain (
  id integer,
  title varchar(64) default "untitled",
  titleorder integer default 0,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtadomain (
  id integer,
  title text default "untitled",
  titleorder integer default 0,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtadomain (
  id number(32),
  title varchar2(64) default "untitled",
  titleorder number(32) default 0,
  CONSTRAINT pk_'.QDB_PREFIX.'qtadomain PRIMARY KEY (id))';
  break;

default:
  die('Database type ['.$oDB->type.'] not supported... Must be mysql, sqlsrv, pg, sqlite, oci');

}


$b = $oDB->query($sql);
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtadomain (id,title,titleorder) VALUES (0,'Administration domain',0)" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtadomain (id,title,titleorder) VALUES (1,'Public domain',1)" );