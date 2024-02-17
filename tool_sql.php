<?php // v4.0 build:20240210

session_start();
/**
 * @var CHtml $oH
 * @var CDatabase $oDB
 */
require 'bin/init.php';
if ( SUser::role()!=='A' ) die('Access denied');

$oH->selfurl = 'tool_sql.php';
$oH->selfname = 'SQL statement';

$oDB->startStats();
$q = ''; // query
qtArgs('q',false,true,true,false); // do not strip tags because <> can be used in the sql

// Certificates
$certificate = makeFormCertificate('b6826b6aefa03e354430e0d6da779262'); // search publickey certificate

// ------
// HTML BEGIN
// ------
include APP.'_inc_hd.php';
echo '<style>
#sqldump{display:block;max-height:500px;overflow:auto;white-space:nowrap}
#sqldump tr:first-child td{background-color:#eee;font-size:1rem}
#sqldump td{padding:1px 2px;border:1px solid #ddd;font-size:0.8rem}
</style>
';

// Dataset (form)

echo '
<h1>'.$oH->selfname.' '.qtSVG('user-a', 'title=Administrator only').'</h1>
<p class="small">As tablename can have prefix in your database, use following alias to query the correct table:<br>
TABSETTING TABDOMAIN TABSECTION TABTOPIC TABPOST TABUSER TABLANG</p>

<form id="form_q" method="post" action="tool_sql.php">
<textarea id="q" name="q" cols="100">'.$q.'</textarea>
<p><button type="submit" name="ok" value="'.$certificate.'">query</button></p>
';

echo '
</form>
<br>
';

// ------
// SUBMITTED
// ------
if ( isset($_POST['ok']) )
{
  if ( $_POST['ok']!==$certificate ) die('Unable to check certificate');
  $q = trim($q);
  $oDB->debug = 'log';
  $oDB->query( $q );
  if ( strtoupper(substr($q,0,6))==='SELECT' )
  {
    echo '<table id="sqldump">';
    $i=0;
    $bText = false;
    while($row=$oDB->getRow())
    {
      if ( $i==0 ) {
        printf( '<tr><td>%s</td></tr>',implode('</td><td>',array_keys($row)) );
        if ( isset($row['textmsg']) ) $bText=true;
      }
      if ( $bText && isset($row['textmsg'][200]) ) $row['textmsg'] = qtInline($row['textmsg'],199,'■');
      printf( '<tr><td>%s</td></tr>',implode('</td><td>',$row) );
      if ( $i>250 ) break;
      $i++;
    }
    echo '</table>';
    // warning
    if ( $bText ) echo '<p class="small">For the field textmsg, the content is compacted in one line of 200 characters</p>';
    // update stats
    if ( isset($oDB->stats['rows']) ) $oDB->stats['rows'] = ($i>250 ? '>250' : $i);
  } else {
    echo '<p class="small">Insert/Update/Delete query done</p>';
  }
}

// ------
// HTML END
// ------
include APP.'_inc_ft.php';