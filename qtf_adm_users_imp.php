<?php // v4.0 build:20230205 allows app impersonation [qt f|i ]

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php'; if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');

// INITIALISE

$strTitle   = '';
$strDelimit = ';';
$skipFirstLine = false;

$oH->selfurl = APP.'_adm_users_imp.php';
$oH->selfname = L('Users_import_csv');
$oH->selfparent = L('Board_content');
$oH->exiturl = APP.'_adm_users.php';
$oH->exitname = '&laquo; '.L('Users');

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) ) try {

  // Check uploaded document
  $str = validateFile($_FILES['title'],'csv,txt,text','',2000); if ( !empty($str) ) throw new Exception( $str );

  // Check form value
  $strDelimit = trim($_POST['delimit']);
  $skipFirstLine = isset($_POST['skip']);
  if ( empty($strDelimit) || strlen($strDelimit)>1 ) throw new Exception( L('Separator').' '.L('invalid') );
  if ( preg_match('/[0-9A-Za-z]/',$strDelimit) ) throw new Exception( L('Separator').' '.L('invalid') );

  // Read file
  if ( $handle = fopen($_FILES['title']['tmp_name'],'r') )
  {
    $i = 0;
    $intCountUser = 0;
    $id = $oDB->nextId(TABUSER);
    $oDB->stoponerror=false;
    while( ($row=fgetcsv($handle,500,$strDelimit))!==FALSE )
    {
      $i++;
      if ( $skipFirstLine && $i===1 ) continue;
      if ( count($row)==1 ) continue;
      if ( count($row)==4 )
      {
        $strRole = 'U'; if ( $row[0]=='A' || $row[0]=='M' || $row[0]=='a' || $row[0]=='m') $strRole=strtoupper($row[0]);
        $strLog = trim($row[1]); if ( !empty($strLog) ) $strLog=utf8_decode($strLog);
        $strPwd = trim($row[2]);
        if ( substr($strPwd,0,3)==='SHA' || substr($strPwd,0,3)==='sha' ) $strPwd = sha1($strPwd);
        if ( empty($strPwd) ) $strPwd=sha1($strLog);
        $strMail = $row[3];
        // insert
        if ( !empty($strLog) )
        {
          if ( $oDB->exec( "INSERT INTO TABUSER (id,name,pwd,mail,role) VALUES ($id,?,?,?,?)", [qtDb($strLog),$strPwd,$strMail,$strRole] ) )
          {
            $id++;
            $intCountUser++;
          }
          else
          {
            echo ' - Cannot insert a new user with username '.$strLog.'<br>';
          }
        }
      }
      else
      {
        $oH->error = 'Number of parameters ('.count($row).') not matching in line '.$i;
      }
    }
  }
  fclose($handle);

  // End message

  if ( empty($oH->error) )
  {
    unlink($_FILES['title']['tmp_name']);
    $oH->pageMessage('', $intCountUser===0 ? 'No user inserted... Check the file and check that you don\'t have duplicate usernames.<br>' : L('User',$intCountUser).'<br>'.L('S_update').'<br>', 'admin');
  }

} catch (Exception $e) {

  $oH->error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;

}

// --------
// HTML BEGIN
// --------

include APP.'_adm_inc_hd.php';

echo '<h2 class="config">'.L('File').'</h2>
<form method="post" action="'.$oH->selfurl.'" enctype="multipart/form-data">
<input type="hidden" name="maxsize" value="5242880"/>
<table class="t-conf">
<tr>
<th style="width:200px"><label for="title">CSV file</label></th>
<td><input type="file" id="title" name="title" size="32" value="'.$strTitle.'" required/></td>
</tr>
<tr>
<th><label for="delimit">'.L('Separator').'</label></th>
<td><input type="text" id="delimit" name="delimit" size="1" maxlength="1" value="'.$strDelimit.'" required/></td>
</tr>
<tr>
<th>'.L('First_line').'</th>
<td><input type="checkbox" id="skip" name="skip"'.($skipFirstLine ? ' checked' : '').'/> <label for="skip">'.L('Skip_first_line').'</label></td>
</tr>
</table>
';
echo '
<p class="submit">
<button type="button" name="cancel" value="cancel" onclick="window.location=`'.Href($oH->exiturl).'`;">'.L('Cancel').'</button>
<button type="submit" name="ok" value="ok">'.L('Ok').'</button>
</p>
</form>
';

// HTML END

include APP.'_adm_inc_ft.php';