<?php // v4.0 build:20230430 allows app impersonation [qt f|i ]

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php'; if ( SUser::role()!=='A' ) die('Access denied');
include translate('lg_adm.php');

// INITIALISE

$strTitle = APP.'-users.csv';
$strSep = ';';

$oH->selfurl = APP.'_adm_users_exp.php';
$oH->selfname = L('Users_export_csv');
$oH->selfparent = L('Board_content');
$oH->exiturl = APP.'_adm_users.php';
$oH->exitname = '&laquo; '.L('Users');

// --------
// SUBMITTED
// --------

if ( isset($_POST['ok']) ) try {

  // Check inputs
  $strTitle = stripslashes(trim($_POST['title'])); if ( empty($strTitle) ) throw new Exception( 'Invalid filename' );
  $strSep = $_POST['sep'];
  if ( empty($strSep) || preg_match('/[0-9A-Za-z]/',$strSep) ) throw new Exception( L('Separator').' '.L('invalid') );

  // Query
  $csv = '';
  $sqlWhere = '';
  switch($_POST['role']) {
    case '*': break;
    case 'A':
    case 'M':
    case 'U':  $sqlWhere .= "AND role='".$_POST['role']."'"; break;
    case 'FM': $sqlWhere .= "AND firstdate=lastdate"; break; //false members
    case 'SM': $sqlWhere .= "AND lastdate<'".addDate(date('Ymd His'),-1,'year')."'"; break; //sleeping members
    case 'CH': $sqlWhere .= "AND children<>'0'"; break;//children
    case 'SC': $sqlWhere .= "AND children='2'"; break;//sleeping children
    default: throw new Exception( 'Invalid option' );
  }

  $oDB->query( "SELECT role,name,pwd,mail FROM TABUSER WHERE id>0 ".$sqlWhere );
  while($row=$oDB->getRow())
  {
    $row = qtQuote($row);
    $csv .= implode($strSep,$row).PHP_EOL;
  }

  // Write file
  if ( isset($_GET['debug']) ) { echo $csv; exit; }

  // Header sould not have been sent yet. Define a download header. Otherwise file or messages are displayed as a new html page.
  if ( !headers_sent() )
  {
    header('Content-Type: text/csv; charset='.QT_HTML_CHAR);
    header('Content-Disposition: attachment; filename="'.$strTitle.'"');
  }
  echo $csv;
  exit;

} catch (Exception $e) {

  $oH->error = $e->getMessage();
  $_SESSION[QT.'splash'] = 'E|'.$oH->error;

}

// --------
// HTML BEGIN
// --------

include APP.'_adm_inc_hd.php';

echo '<h2 class="config">'.L('File').'</h2>
<form method="post" action="'.$oH->self().'" enctype="multipart/form-data">
<input type="hidden" name="maxsize" value="5242880"/>
<table class="t-conf">
<tr>
<th style="width:200px">CSV file</th>
<td><input type="text" id="title" name="title" size="32" value="'.$strTitle.'" required/></td>
</tr>
<tr>
<th>'.L('Separator').'</th>
<td><input type="text" id="sep" name="sep" size="1" maxlength="1" value="'.$strSep.'" required/></td>
</tr>
<tr>
<th>'.L('Options').'</th>
<td><select name="role" size="1">
<option value="*" selected>('.L('all').')</option>
<optgroup label="'.L('Role').'">
<option value="A">'.L('Role_A+').'</option>
<option value="M">'.L('Role_M+').'</option>
<option value="U">'.L('Role_U+').'</option>
</optgroup>
<optgroup label="'.L('Status').'">
<option value="FM">'.L('Members_FM').'</option>
<option value="SM">'.L('Members_SM').'</option>
<option value="CH">'.L('Members_CH').'</option>
<option value="SC">'.L('Members_SC').'</option>
</optgroup>
</select></td>
</tr>
</table>
';
echo '<p class="submit">
<button type="button" name="cancel" value="cancel" onclick="window.location=`'.url($oH->exiturl).'`;">'.L('Cancel').'</button>
<button type="submit" name="ok" value="ok">'.L('Download').'</button>
</p>
</form>
';

// HTML END

include APP.'_adm_inc_ft.php';