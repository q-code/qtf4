<?php

// v4.0 build:20230430
// Script added in qtf_edit.php when rss is activated.

function toXml($str)
{
  $str = html_entity_decode($str,ENT_QUOTES);
  if ( strpos($str,'<') ) $str = str_replace('<','&#60;',$str);
  if ( strpos($str,'<') ) $str = str_replace('>','&#62;',$str);
  $str = str_replace(chr(160),' ',$str); // required for xml
  return $str;
}

function MakeRss($s=-1)
{

// check
if ( $_SESSION[QT]['m_rss']!=='1' ) Return false;
if ( $s<0 ) die('MakeFeeds: Wrong section');

global $oDB;

// read section info
$oS = new CSection($s);

if ( $oS->type!=='0' ) Return false;

$arr = explode(' ',$_SESSION[QT]['m_rss_conf']);
$top = $arr[2];

// search new topics
$strState = 't.*,p.title,p.textmsg ';
$strState .= 'FROM TABTOPIC t INNER JOIN TABPOST p ON t.firstpostid = p.id ';
$strState .= "WHERE t.forum = $s ";
$strOrder = 't.lastpostdate DESC';
$strQ = sqlLimit($strState,$strOrder,0,$top);
$oDB->query($strQ);
$i=0;
$item = array();
while ($row = $oDB->getRow())
{
  $item[$i]['title'] = toXml($row['title']);
  $item[$i]['link'] = $_SESSION[QT]['site_url']."/qtf_item.php?t=".$row['id']."&p=".$row['firstpostid'];

  // format the RSS text
  $item[$i]['description'] = toXml( qtInline($row['textmsg'],400) );
  $item[$i]['pudDate'] = $row['lastpostdate'];
  $item[$i]['author'] = $row['firstpostname'];
  $i++;
}

// write rss 2.0

$strFilename = 'rss/qtf_rss2_'.$s.'.xml';
if ( file_exists($strFilename) && !is_writable($strFilename) ) { echo $strFilename,' not writable<br>'; return false; }
$handle = fopen($strFilename,'w');
fwrite($handle,'<?xml version="1.0" encoding="'.QT_HTML_CHAR.'" ?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>'."\n");
fwrite($handle,'<title>'.toXml($_SESSION[QT]['site_name'].' - '.$oS->title).'</title>');
fwrite($handle,'<link>'.$_SESSION[QT]['site_url'].'/qtf_items.php?s='.$s.'</link>');
fwrite($handle,'<description>'.toXml($oS->descr).'</description>');
fwrite($handle,"<language>".QT_HTML_LANG."</language>\n");
fwrite($handle,"<generator>QuickTalk ".substr(VERSION,0,3)."</generator>\n");
fwrite($handle,"<managingEditor>{$_SESSION[QT]['admin_email']} (webmaster)</managingEditor>\n");
fwrite($handle,"<category>Troubleticket</category>\n");
fwrite($handle,"<image><url>{$_SESSION[QT]['site_url']}/rss/qtf_logo.gif</url><title>".$_SESSION[QT]['site_name'].' - '.$oS->title."</title><link>{$_SESSION[QT]['site_url']}/qtf_items.php?s=$s</link><width>110</width><height>50</height></image>\n");
fwrite($handle,'<atom:link href="'.$_SESSION[QT]['site_url'].'/rss/qtf_rss2_'.$s.'.xml" rel="self" type="application/rss+xml"/>'."\n");
for ($n=0; $n<$i; $n++)
{
fwrite($handle,"<item>\n");
fwrite($handle,"<title>{$item[$n]['title']}</title>\n");
fwrite($handle,"<link>{$item[$n]['link']}</link>\n");
fwrite($handle,"<description>{$item[$n]['description']}</description>\n");
fwrite($handle,"<pubDate>".qtDatestr($item[$n]['pudDate'],'D, d M Y H:i:00 O','')."</pubDate>\n");
fwrite($handle,"<guid>{$item[$n]['link']}</guid>\n");
fwrite($handle,"</item>\n");
}
fwrite($handle,'</channel></rss>');
fclose($handle);

// write atom 1.0

$strFilename = 'rss/qtf_atom_'.$s.'.xml';
if ( file_exists($strFilename) && !is_writable($strFilename) ) { echo $strFilename,' not writable<br>'; return false; }
$handle = fopen($strFilename,'w');
fwrite($handle,'<?xml version="1.0" encoding="'.QT_HTML_CHAR.'" ?><feed xmlns="http://www.w3.org/2005/Atom">'."\n");
fwrite($handle,'<title>'.toXml($_SESSION[QT]['site_name'].' - '.$oS->title).'</title>');
fwrite($handle,'<link href="'.$_SESSION[QT]['site_url'].'/qtf_items.php?s='.$s.'"/>');
fwrite($handle,'<link href="'.$_SESSION[QT]['site_url'].'/rss/qtf_atom_'.$s.'.xml" rel="self"/>');
fwrite($handle,"<id>{$_SESSION[QT]['site_url']}/qtf_items.php?s=$s</id>\n");
fwrite($handle,"<updated>".qtDatestr(date('Y-m-d H:i:s'),'RFC-3339')."</updated>\n");
fwrite($handle,"<author><name>Webmaster</name><email>{$_SESSION[QT]['admin_email']}</email></author>\n");
fwrite($handle,'<category term="Troubleticket"/>');
fwrite($handle,"<generator>QuickTalk ".substr(VERSION,0,3)."</generator>\n");
fwrite($handle,"<icon>{$_SESSION[QT]['site_url']}/rss/qtf_icon.gif</icon>\n");
fwrite($handle,"<logo>{$_SESSION[QT]['site_url']}/rss/qtf_logo.gif</logo>\n");
for ($n=0; $n<$i; $n++)
{
fwrite($handle,"<entry>\n");
fwrite($handle,"<id>{$item[$n]['link']}</id>\n");
fwrite($handle,"<title>{$item[$n]['title']}</title>\n");
fwrite($handle,"<updated>".qtDatestr($item[$n]['pudDate'],'RFC-3339')."</updated>\n");
fwrite($handle,"<author><name>{$item[$n]['author']}</name></author>\n");
fwrite($handle,"<content>{$item[$n]['description']}</content>\n");
fwrite($handle,'<link href="'.$item[$n]['link'].'"/>');
fwrite($handle,"</entry>\n");
}
fwrite($handle,'</feed>');
fclose($handle);

}

/**
 * @var int $s
 */
MakeRss($s);