<?php // v4.0 build:20230205

session_start();
require 'bin/init.php';
/**
* @var CHtml $oH
* @var array $L
* @var CDatabase $oDB
*/
include translate('lg_adm.php');
include translate(APP.'m_export.php');
if ( SUser::role()!=='A' ) die(L('E_13'));
if ( !defined('QT_XML_CHAR') ) define('QT_XML_CHAR','iso-8859-1');

function toXml($str)
{
  $str = html_entity_decode($str,ENT_QUOTES);
  if ( strpos($str,'<') ) $str = str_replace('<','&#60;',$str);
  if ( strpos($str,'<') ) $str = str_replace('>','&#62;',$str);
  $str = str_replace(chr(160),' ',$str); // required for xml
  return $str;
}

// INITIALISE

$arrYears = array(
  strval(date('Y'))=>strval(date('Y')),
  strval(date('Y')-1)=>strval(date('Y')-1),
  'old'=>'&lt; '.strval(date('Y')-1)
  );

if ( !isset($_SESSION['m_export_xml']) )
{
  $_SESSION['m_export_xml'] = array(
  'title'   => 'export_'.date('Ymd').'.xml',
  'dropbbc' => 'Y');
}

$oH->selfurl = 'qtfm_export_adm.php';
$oH->selfname = L('Export_Admin');
$oH->exiturl = $oH->selfurl;
$oH->exitname = $oH->selfname;
$oH->selfversion = L('Export_Version').' 4.0';

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) )
{
  // read and check mandatory
  if ( isset($_POST['dropbbc']) ) { $_SESSION['m_export_xml']['dropbbc']='Y'; } else { $_SESSION['m_export_xml']['dropbbc']='N'; }
  if ( empty($_POST['title']) ) $oH->error='Filename '.L('invalid');
  if ( substr($_POST['title'],-4,4)!=='.xml' ) $_POST['title'] .= '.xml';
  if ( $_POST['section']=='-' ) $oH->error='No data found';
  if ( $_POST['year']=='-' ) $oH->error='No data found';

  // EXPORT COUNT
  if ( empty($oH->error) )
  {
    $sqlWhere = '';
    if ( $_POST['section']!='*' ) { $sqlWhere .= 'forum='.$_POST['section']; } else { $sqlWhere .= 'forum>=0'; }
    if ( $_POST['year']!='*' ) $sqlWhere .= ' AND '.sqlDateCondition($_POST['year'],'firstpostdate');
    $oDB->query( 'SELECT count(*) as countid FROM TABTOPIC WHERE '.$sqlWhere );
    $row=$oDB->getRow();
    if ( $row['countid']==0 ) $oH->error='No data found';
  }

  // ------
  // EXPORT XML
  // ------

  if ( empty($oH->error) )
  {
    $oDB2 = new CDatabase();

    // start export

    if ( !headers_sent())
    {
      header('Content-Type: text/xml; charset='.QT_XML_CHAR);
      header('Content-Disposition: attachment; filename="'.$_POST['title'].'"');
    }

    echo '<?xml version="1.0" encoding="'.QT_XML_CHAR.'"?>'.PHP_EOL;
    echo '<quicktalk version="3.0">'.PHP_EOL;

    // export topic
    $oDB->query( 'SELECT * FROM TABTOPIC WHERE '.$sqlWhere );
    while($row=$oDB->getRow())
    {
      $oT = new CTopic($row);

      echo '<topic id="',$oT->id,'" type="',$oT->type,'" forum="',$oT->pid,'">'.PHP_EOL;
      echo '<numid>',$oT->numid,'</numid>'.PHP_EOL;
      echo '<status>',$oT->status,'</status>'.PHP_EOL;
      if ( !empty($oT->statusdate) )    echo '<statusdate>',$oT->statusdate,'</statusdate>'.PHP_EOL;
      //if ( !empty($oT->eventdate) )     echo '<eventdate>',$oT->eventdate,'</eventdate>'.PHP_EOL;
      //if ( !empty($oT->wisheddate) )    echo '<wisheddate>',$oT->wisheddate,'</wisheddate>'.PHP_EOL;
      if ( !empty($oT->firstpostid) )   echo '<firstpostid>',$oT->firstpostid,'</firstpostid>'.PHP_EOL;
      if ( !empty($oT->lastpostid) )    echo '<lastpostid>',$oT->lastpostid,'</lastpostid>'.PHP_EOL;
      if ( !empty($oT->firstpostuser) ) echo '<firstpostuser>',$oT->firstpostuser,'</firstpostuser>'.PHP_EOL;
      if ( !empty($oT->lastpostuser) )  echo '<lastpostuser>',$oT->lastpostuser,'</lastpostuser>'.PHP_EOL;
      if ( !empty($oT->firstpostname) ) echo '<firstpostname>',$oT->firstpostname,'</firstpostname>'.PHP_EOL;
      if ( !empty($oT->lastpostname) )  echo '<lastpostname>',$oT->lastpostname,'</lastpostname>'.PHP_EOL;
      if ( !empty($oT->firstpostdate) ) echo '<firstpostdate>',$oT->firstpostdate,'</firstpostdate>'.PHP_EOL;
      if ( !empty($oT->lastpostdate) )  echo '<lastpostdate>',$oT->lastpostdate,'</lastpostdate>'.PHP_EOL;
      if ( !empty($oT->x) )             echo '<x>',$oT->x,'</x>'.PHP_EOL;
      if ( !empty($oT->y) )             echo '<y>',$oT->y,'</y>'.PHP_EOL;
      if ( !empty($oT->z) )             echo '<z>',$oT->z,'</z>'.PHP_EOL;
      if ( !empty($oT->descr) )          echo '<tags>',$oT->descr,'</tags>'.PHP_EOL;
      if ( !empty($oT->param) )         echo '<param>',$oT->param,'</param>'.PHP_EOL;

      echo '<posts>'.PHP_EOL;

        $oDB2->query( 'SELECT * FROM TABPOST WHERE topic='.$oT->id );
        while($row2=$oDB2->getRow())
        {
          $oP = new CPost($row2);
          echo '<post id="',$oP->id,'" type="',$oP->type,'">'.PHP_EOL;
          echo '<icon>',$oP->icon,'</icon>'.PHP_EOL;
          echo '<title>',toXml($oP->title),'</title>'.PHP_EOL;
          echo '<userid>',$oP->userid,'</userid>'.PHP_EOL;
          echo '<username>',$oP->username,'</username>'.PHP_EOL;
          echo '<issuedate>',$oP->issuedate,'</issuedate>'.PHP_EOL;
          if ( !empty($oP->modifdate) ) echo '<modifdate>',$oP->modifdate,'</modifdate>'.PHP_EOL;
          if ( !empty($oP->modifuser) ) echo '<modifuser>',$oP->modifuser,'</modifuser>'.PHP_EOL;
          if ( !empty($oP->modifname) ) echo '<modifname>',$oP->modifname,'</modifname>'.PHP_EOL;
          echo '<textmsg>',toXml($oP->text),'</textmsg>'.PHP_EOL;
          echo '</post>'.PHP_EOL;  // doc is not exported
        }

      echo '</posts>'.PHP_EOL;
      echo '</topic>'.PHP_EOL;
    }

    // end export

    echo '</quicktalk>';
    exit;
  }

}

// --------
// HTML BEGIN
// --------

include 'qtf_adm_inc_hd.php';

echo '<form method="post" action="',$oH->selfurl,'">
<h2 class="config">',$L['Export_Content'],'</h2>
<table class="t-conf">
<tr>
<th><label for="section">',$L['Section'],'</label></th>
<td>
<select id="section" name="section" size="1">
',sectionsAsOption(),'
<option value="*">[ ',$L['All'],' ]</option>
</select>
</td>
</tr>
<tr><th><label for="year">',$L['Item+'],'</label></th>
<td><select id="year" name="year" size="1">
<option value="*">[ ',$L['All'],' ]</option>
',asTags($arrYears),'
</select></td>
</tr>
<tr>
<th><label for="dropbbc">',$L['Export_Drop_bbc'],'</label></th>
<td><span class="cblabel"><input type="checkbox" id="dropbbc" name="dropbbc"',($_SESSION['m_export_xml']['dropbbc']=='Y' ? 'checked' : ''),'/> <label for="dropbbc">',$L['Export_H_Drop_bbc'],'</label></span></td>
</tr>
</table>
';

echo '<h2 class="config">',$L['Destination'],'</h2>
<table class="t-conf">
<tr>
<th><label for="title">',$L['Export_Filename'],'</label></th>
<td><input required type="text" id="title" name="title" size="32" maxlength="32" value="',qtAttr($_SESSION['m_export_xml']['title']),'"/></td>
</tr>
</table>
';
echo '<p class="submit"><button type="submit" name="ok" value="ok">'.L('Ok').'</button></p>
</form>
';

// HTML END

include 'qtf_adm_inc_ft.php';