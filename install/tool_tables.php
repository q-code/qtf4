<?php // v4.0 build:20230205
/**
* @var CHtml $oH
 * @var string $strPrev
 * @var string $strNext
 * @var string $urlPrev
 * @var string $urlNext
 */
session_start();
include 'init.php';
$urlPrev = APP.'_setup_4.php';
$urlNext  = APP.'_setup_4.php';

function SqlDrop(string $table, string $constrain='')
{
  global $oDB;
  if ( !empty($constrain) && $oDB->type=='oci' ) $oDB->exec( 'ALTER TABLE '.$table.' DROP CONSTRAINT '.$constrain);
  $oDB->exec( 'DROP TABLE '.$table);
}

// INITIALISATION

include 'lg_install.php';

// --------
// HTML BEGIN
// --------

include APP.'_setup_hd.php'; // this will show $oH->error

echo '<p>1. <span class="bold">Opening database connection</span>... ';

$oDB = new CDatabase();

echo 'done.</p>
<p class="indent">driver: '.$oDB->type.'
<br>database name: '.QDB_DATABASE.'</p>';

// SUBMITTED

if ( isset($_GET['a']) )
{
  switch($_GET['a'])
  {
  case 'Drop ALL tables':
    echo ' Dropping Post...'; SqlDrop(TABPOST,'pk_'.QDB_PREFIX.'qtapost'); echo 'done.<br>';
    echo ' Dropping Topic...'; SqlDrop(TABTOPIC,'pk_'.QDB_PREFIX.'qtatopic'); echo 'done.<br>';
    echo ' Dropping Section...'; SqlDrop(TABSECTION,'pk_'.QDB_PREFIX.'qtaforum'); echo 'done.<br>';
    echo ' Dropping Domain...'; SqlDrop(TABDOMAIN,'pk_'.QDB_PREFIX.'qtadomain'); echo 'done.<br>';
    echo ' Dropping User...'; SqlDrop(TABUSER,'pk_'.QDB_PREFIX.'qtauser'); echo 'done.<br>';
    echo ' Dropping Setting...'; SqlDrop(TABSETTING); echo 'done.<br>';
    echo ' Dropping Lang...'; SqlDrop(TABLANG); echo 'done.<br>';
    break;
  case 'Drop table Post':
    echo ' Dropping Post...'; SqlDrop(TABPOST,'pk_'.QDB_PREFIX.'qtapost'); echo 'done.<br>'; break;
  case 'Drop table Topic':
    echo ' Dropping Topic...'; SqlDrop(TABTOPIC,'pk_'.QDB_PREFIX.'qtatopic'); echo 'done.<br>'; break;
  case 'Drop table Section':
    echo ' Dropping Section...'; SqlDrop(TABSECTION,'pk_'.QDB_PREFIX.'qtaforum'); echo 'done.<br>'; break;
  case 'Drop table Domain':
    echo ' Dropping Domain...'; SqlDrop(TABDOMAIN,'pk_'.QDB_PREFIX.'qtadomain'); echo 'done.<br>'; break;
  case 'Drop table User':
    echo ' Dropping User...'; SqlDrop(TABUSER,'pk_'.QDB_PREFIX.'qtauser'); echo 'done.<br>'; break;
  case 'Drop table Setting':
    echo ' Dropping Setting...'; SqlDrop(TABSETTING); echo 'done.<br>'; break;
  case 'Drop table Lang':
    echo ' Dropping Lang...'; SqlDrop(TABLANG); echo 'done.<br>'; break;
  case 'Add table Post':
    include 'qtf_table_post.php'; echo $_GET['a'],' done'; break;
  case 'Add table Topic':
    include 'qtf_table_member.php'; echo $_GET['a'],' done'; break;
  case 'Add table Sectionm':
    include 'qtf_table_section.php'; echo $_GET['a'],' done'; break;
  case 'Add table Domain':
    include 'qtf_table_domain.php'; echo $_GET['a'],' done'; break;
  case 'Add table User':
    include 'qtf_table_user.php'; echo $_GET['a'],' done'; break;
  case 'Add table Setting':
    include 'qtf_table_setting.php'; echo $_GET['a'],' done'; break;
  case 'Add table Lang':
    include 'qtf_table_lang.php'; echo $_GET['a'],' done'; break;
  }
}

// Tables do drop

echo '<br><p>2. <span class="bold">Drop tables</span></p>';

echo '<form action="tool_tables.php" method="get">';
echo '<p><button type="submit" name="a" value="Drop ALL tables">Drop ALL tables</button> from the database ',QDB_DATABASE,'</p><br>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table Setting" onclick="return doIt(this.value);">Drop table Setting</button> ',TABSETTING,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table Post" onclick="return doIt(this.value);">Drop table Post</button> ',TABPOST,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table Topic" onclick="return doIt(this.value);">Drop table Topic</button> ',TABTOPIC,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table User" onclick="return doIt(this.value);">Drop table User</button> ',TABUSER,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table Section" onclick="return doIt(this.value);">Drop table Section</button> ',TABSECTION,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table Domain" onclick="return doIt(this.value);">Drop table Domain</button> ',TABDOMAIN,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Drop table Lang" onclick="return doIt(this.value);">Drop table Lang</button> ',TABLANG,'</p>';
echo '<br><p>3. <span class="bold">Add tables</span></p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table Setting" onclick="return doIt(this.value);">Add table Setting</button> ',TABSETTING,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table Post" onclick="return doIt(this.value);">Add table Post</button> ',TABPOST,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table Topic" onclick="return doIt(this.value);">Add table Topic</button> ',TABTOPIC,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table User" onclick="return doIt(this.value);">Add table User</button> ',TABUSER,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table Section" onclick="return doIt(this.value);">Add table Section</button> ',TABSECTION,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table Domain" onclick="return doIt(this.value);">Add table Domain</button> ',TABDOMAIN,'</p>';
echo '<p>&nbsp;&nbsp;<button type="submit" name="a" value="Add table Lang" onclick="return doIt(this.value);">Add table Lang</button> ',TABLANG,'</p>';
echo '</form>
<script type="text/javascript">
function doIt(a) { return confirm("Are you sure you want to "+a+"?"); }
</script>
';

echo '<br><p><a href="qtf_setup.php">Install...</a>';
if ( file_exists('tool_check.php') ) echo ' | <a href="tool_check.php">Check installation...</a>';
echo '</p>';

// --------
// HTML END
// --------
include APP.'_setup_ft.php'; // this will show $oH->error
