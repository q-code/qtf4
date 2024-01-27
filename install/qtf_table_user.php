<?php // v4.0 build:20230618
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtauser (
  id int,
  name varchar(24) UNIQUE,
  closed char(1) default "0",
  role char(1) default "V",
  pwd varchar(40),
  www varchar(255),
  mail varchar(255),
  privacy char(1),
  location varchar(24),
  firstdate varchar(20),
  lastdate varchar(20),
  birthday varchar(20),
  numpost int,
  signature varchar(255),
  picture varchar(255),
  children char(1),
  parentmail varchar(255),
  parentagree char(1),
  secret_q varchar(255),
  secret_a varchar(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  ip varchar(24),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtauser (
  id int CONSTRAINT pk_'.QDB_PREFIX.'qtauser PRIMARY KEY,
  name varchar(24) CONSTRAINT uk_'.QDB_PREFIX.'qtauser UNIQUE,
  closed char(1) default "0",
  role char(1) default "V",
  pwd varchar(40) NULL,
  www varchar(255) NULL,
  mail varchar(255) NULL,
  privacy char(1) NULL,
  location varchar(24) NULL,
  firstdate varchar(20) NULL,
  lastdate varchar(20) NULL,
  birthday varchar(20) NULL,
  numpost int NULL,
  signature varchar(255) NULL,
  picture varchar(255),
  children char(1) NULL,
  parentmail varchar(255) NULL,
  parentagree char(1) NULL,
  secret_q varchar(255)NULL,
  secret_a varchar(255) NULL,
  x decimal(13,10) NULL,
  y decimal(13,10) NULL,
  z decimal(13,2) NULL,
  ip varchar(24) NULL
  )';
  break;

case 'pdo.pg':
case 'pg':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtauser (
  id integer,
  name varchar(24),
  closed char(1) default "0",
  role char(1) default "V",
  pwd varchar(40),
  www varchar(255),
  mail varchar(255),
  privacy char(1),
  location varchar(24),
  firstdate varchar(20),
  lastdate varchar(20),
  birthday varchar(20),
  numpost integer,
  signature varchar(255),
  picture varchar(255),
  children char(1),
  parentmail varchar(255),
  parentagree char(1),
  secret_q varchar(255),
  secret_a varchar(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  ip varchar(24),
  PRIMARY KEY (id),
  UNIQUE (name)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtauser (
  id integer,
  name text,
  closed text default "0",
  role text default "V",
  pwd text,
  www text,
  mail text,
  privacy text,
  location text,
  firstdate text,
  lastdate text,
  birthday text,
  numpost integer,
  signature text,
  picture text,
  children text,
  parentmail text,
  parentagree text,
  secret_q text,
  secret_a text,
  x real,
  y real,
  z real,
  ip text,
  PRIMARY KEY (id),
  UNIQUE (name)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $sql = 'CREATE TABLE '.QDB_PREFIX.'qtauser (
  id number(32),
  name varchar2(24) ,
  closed char(1) default "0" ,
  role char(1) default "V" ,
  pwd varchar2(40),
  www varchar2(255),
  mail varchar2(255),
  privacy char(1),
  location varchar2(24),
  firstdate varchar2(20),
  lastdate varchar2(20),
  birthday varchar2(20),
  numpost number(32),
  signature varchar2(255),
  picture varchar2(255),
  children char(1),
  parentmail varchar2(255),
  parentagree char(1),
  secret_q varchar2(255),
  secret_a varchar2(255),
  x decimal(13,10),
  y decimal(13,10),
  z decimal(13,2),
  ip varchar2(24),
  CONSTRAINT pk_'.QDB_PREFIX.'qtauser PRIMARY KEY (id))';
  break;

default:
  die('Database type ['.$oDB->type.'] not supported... Must be mysql, sqlsrv, pg, oci');
}

$oDB->query($sql);
$oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtauser (id,name,picture,closed,role,firstdate,lastdate,numpost,privacy,children,parentagree) VALUES (0,"Visitor","0","0","V","'.date('Ymd His').'","'.date('Ymd His').'",0,"0","0","0")' );
$oDB->exec( 'INSERT INTO '.QDB_PREFIX.'qtauser (id,name,picture,closed,role,pwd,firstdate,lastdate,numpost,privacy,signature,children,parentagree) VALUES (1,"Admin","0","0","A","'.sha1('Admin').'","'.date('Ymd His').'","'.date('Ymd His').'",0,"0","[i][b]The forum Administrator[/b][/i]","0","0")' );