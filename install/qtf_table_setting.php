<?php // v4.0 build:20230618
/**
 * @var CDatabase $oDB
 */
switch($oDB->type)
{

case 'pdo.mysql':
case 'mysql':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtasetting (
  param varchar(24),
  setting varchar(255),
  PRIMARY KEY (param)
  )';
  break;

case 'pdo.sqlsrv':
case 'sqlsrv':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtasetting (
  param varchar(24) NOT NULL CONSTRAINT pk_'.QDB_PREFIX.'qtasetting PRIMARY KEY,
  setting varchar(255)
  )';
  break;

case 'pdo.pg':
case 'pg':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtasetting (
  param varchar(24),
  setting varchar(255),
  PRIMARY KEY (param)
  )';
  break;

case 'pdo.sqlite':
case 'sqlite':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtasetting (
  param text,
  setting text,
  PRIMARY KEY (param)
  )';
  break;

case 'pdo.oci':
case 'oci':
  $strQ='CREATE TABLE '.QDB_PREFIX.'qtasetting (
  param varchar2(24),
  setting varchar2(255),
  CONSTRAINT pk_'.QDB_PREFIX.'qtasetting PRIMARY KEY (param))';
  break;

default:
  die('Database type ['.$oDB->type.'] not supported... Must be mysql, sqlsrv, pg, sqlite, oci');

}

echo '<span style="color:blue">';
$oDB->exec($strQ);
echo '</span>';

if ( !empty($oDB->error) )
{
  echo '<div class="setup_err">',sprintf (L('E_install'),QDB_PREFIX.'qtasetting',QDB_DATABASE,QDB_USER),'</div>';
  echo '<br><table class="button"><tr><td></td><td class="button" style="width:120px">&nbsp;<a href="qtf_setup_1.php">',L('Restart'),'</a>&nbsp;</td></tr></table>';
  exit;
}

$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('version','4.0')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('board_offline','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('site_name','QT-cute')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('site_url','http://')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('home_menu','0')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('home_name','Home')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('home_url','http://www.qt-cute.org')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('admin_email','')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('admin_phone','')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('admin_name','')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('admin_addr','')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('posts_per_item','100')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('chars_per_post','4000')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('lines_per_post','250')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('time_zone','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('show_time_zone','0')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('posts_per_day','100')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('site_width','780')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('register_safe','text')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('smtp_password','')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('smtp_username','')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('smtp_host','')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('use_smtp','0')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('show_welcome','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('items_per_page','20')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('replies_per_page','20')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('language','".(empty($_SESSION['qtf_setup_lang']) ? 'en' : $_SESSION['qtf_setup_lang'])."')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('userlang','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('item_firstline','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('show_banner','2')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('show_legend','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('index_name','Forum index')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('skin_dir','default')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('javamail','0')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('bbc','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('formatdate','j M Y')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('formattime','G:i')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('formatpicture','mime=gif jpg jpeg png;width=120;height=120')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('show_id','0')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('id_format','T-%03s')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('show_back','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('news_on_top','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('show_closed','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('register_mode','direct')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('visitor_right','4')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('login_qte_web','0')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('login_qte','0')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('register_coppa','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('show_quick_reply','1')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('show_calendar','U')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('upload','U')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('upload_size','1024')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('show_memberlist','U')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('show_stats','M')" );
$oDB->exec( "INSERT INTO ".QDB_PREFIX."qtasetting (param,setting) VALUES ('tags','M')" ); //v 2.0