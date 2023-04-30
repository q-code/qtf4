<?php // v4.0 build:20230430
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtaforum (
  id int,
  type char(1) NOT NULL default "0",
  status char(1) NOT NULL default "0",
  notify char(1) NOT NULL default "1",
  domainid int NOT NULL default 0,
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 255,
  moderator int NOT NULL default 1,
  moderatorname varchar(24) NOT NULL default "Administrator",
  options varchar(255),
  titlefield char(1) NOT NULL default "0",
  numfield varchar(24) NOT NULL default "N",
  prefix char(1),
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtaforum (
  id int NOT NULL CONSTRAINT pk_'.QDB_PREFIX.'qtaforum PRIMARY KEY,
  type char(1) NOT NULL default "0",
  status char(1) NOT NULL default "0",
  notify char(1) NOT NULL default "1",
  domainid int NOT NULL default 0,
  title varchar(64) NOT NULL default "untitled",
  titleorder int NOT NULL default 0,
  moderator int NOT NULL default 1,
  moderatorname varchar(24) NOT NULL default "Administrator",
  options varchar(255),
  titlefield char(1) NOT NULL default "0",
  numfield varchar(24) NOT NULL default "N",
  prefix char(1) NULL
  )';
  break;

case 'pdo.pg':
case 'pg':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtaforum (
  id integer,
  type char(1) NOT NULL default "0",
  status char(1) NOT NULL default "0",
  notify char(1) NOT NULL default "1",
  domainid integer NOT NULL default 0,
  title varchar(64) NOT NULL default "untitled",
  titleorder integer NOT NULL default 255,
  moderator integer NOT NULL default 1,
  moderatorname varchar(24) NOT NULL default "Administrator",
  options varchar(255) NULL,
  titlefield char(1) NOT NULL default "0",
  numfield varchar(24) NOT NULL default "N",
  prefix char(1) NULL,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtaforum (
  id integer,
  type text NOT NULL default "0",
  status text NOT NULL default "0",
  notify text NOT NULL default "1",
  domainid integer NOT NULL default 0,
  title text NOT NULL default "untitled",
  titleorder integer NOT NULL default 255,
  moderator integer NOT NULL default 1,
  moderatorname text NOT NULL default "Administrator",
  options text,
  titlefield text NOT NULL default "0",
  numfield text NOT NULL default "N",
  prefix text,
  PRIMARY KEY (id)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtaforum (
  id number(32),
  type char(1) default "0" NOT NULL,
  status char(1) default "0" NOT NULL,
  notify char(1) default "1" NOT NULL,
  domainid number(32) default 0 NOT NULL,
  title varchar2(64) default "untitled" NOT NULL,
  titleorder number(32) default 255 NOT NULL,
  moderator number(32) default 1 NOT NULL,
  moderatorname varchar2(24) default "Administrator" NOT NULL,
  options varchar2(255),
  numfield varchar2(24) default "N" NOT NULL,
  titlefield char(1) default "0" NOT NULL,
  prefix char(1),
  CONSTRAINT pk_'.QDB_PREFIX.'qtaforum PRIMARY KEY (id))';
  break;

default:
 die('Database type ['.$oDB->type.'] not supported... Must be mysql, sqlsrv, pg, sqlite, oci');

}

echo '<span style="color:blue">';
$b=$oDB->query($strQ);
echo '</span>';

if ( !empty($oDB->error) || !$b )
{
  echo '<div class="setup_err">',sprintf (L('E_install'),QDB_PREFIX.'qtaforum',QDB_DATABASE,QDB_USER),'</div>';
  echo '<br><table  class="ta_button"><tr><td></td><td class="td_button" style="width:120px">&nbsp;<a href="qtf_setup_1.php">',L('Restart'),'</a>&nbsp;</td></tr></table>';
  exit;
}

$strQ="INSERT INTO ".QDB_PREFIX."qtaforum (
id,type,status,notify,domainid,title,titleorder,moderator,moderatorname,options,titlefield,numfield,prefix)
VALUES (0,'1','0','0',0,'Administration forum',0,1,'Admin','logo=0','1','N','a')";
$oDB->query($strQ);

$strQ="INSERT INTO ".QDB_PREFIX."qtaforum (
  id,type,status,notify,domainid,title,titleorder,moderator,moderatorname,options,titlefield,numfield,prefix)
  VALUES (1,'0','0','0',1,'Public forum',0,1,'Admin','logo=0','1','N','a')";
$oDB->query($strQ);