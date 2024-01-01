<?php

// QuickTalk forum 4.0 build:20230618

session_start();
include 'init.php';

// --------
// Basic check
// --------

$bConfig = false;
if ( file_exists('../config/config_db.php') ) {
if ( is_readable('../config/config_db.php') ) {
if ( is_writable('../config/config_db.php') ) {
  $bConfig=true;
}}}

// --------
// Html start
// --------

include APP.'_setup_hd.php'; // this will show $oH->error

if ( !$bConfig )
{
  echo '<h2 style="color:#000000;margin:10px 0 5px 0">[EN] Before install</h2>';
  echo '<p>The configuration file <span class="bold">config/config_db.php</span> is not writable. Please make this file writable before starting installation (e.g. with a FTP client, set the file attributes to chmod 777)</p>';
  echo '<h2 style="color:#666666;margin:0 0 5px 20px">[FR] Avant d\'installer</h2>';
  echo '<p style="color:#666666;margin:0 0 5px 20px">Le fichier <span class="bold">config/config_db.php</span> n\'est pas inscriptible. Veillez rendre ce fichier inscriptible avant de lancer l\'installation (ex. avec un logiciel de FTP changer les attributs de securité de ce fichier en chmod 777)</p>';
  echo '<h2 style="color:#666666;margin:0 0 5px 20px">[NL] Voor installatie</h2>';
  echo '<p style="color:#666666;margin:0 0 5px 20px">De file <span class="bold">config/config_db.php</span> is read-only. U moet dit inschrijfbaar maken (bvb. met een FTP software u kan de veiligheid attributen naar chmod 777 veranderen)</p>';
  include APP.'_setup_ft.php';
  exit;
}
if ( !file_exists('../config/config_lang.php') )
{
  echo '<h2 style="color:#000000;margin:10px 0 5px 0">[EN] Before install</h2>';
  echo '<p>The configuration file <span class="bold">config/config_lang.php</span> is missing. Please make this file available before starting installation.</p>';
  echo '<h2 style="color:#666666;margin:0 0 5px 20px">[FR] Avant d\'installer</h2>';
  echo '<p style="color:#666666;margin:0 0 5px 20px">Le fichier <span class="bold">config/config_lang.php</span> n\'est pas disponible. Veuillez rendre ce fichier disponible avant de lancer l\'installation.</p>';
  echo '<h2 style="color:#666666;margin:0 0 5px 20px">[NL] Voor installatie</h2>';
  echo '<p style="color:#666666;margin:0 0 5px 20px">De file <span class="bold">config/config_lang.php</span> is niet beschikbaar. Maak dit beschikbaar voordat u met de installatie begint.</p>';
  include APP.'_setup_ft.php';
  exit;
}

// Read language subdirectories
include '../config/config_lang.php';
$arrOptions = [];
foreach(LANGUAGES as $k=>$values)
{
  $arr = explode(' ',$values,2); if ( empty($arr[1]) ) $arr[1]=$arr[0];
  if ( file_exists('../language/'.$k.'/'.'lg_install.php') ) $arrOptions[$k] = $arr[1];
}
asort($arrOptions);

echo '<br><br>
<h2 class="center">Language ?</h2>
<form method="get" action="'.APP.'_setup_1.php">
<p class="center"><select name="lang" size="1">';
foreach($arrOptions as $key=>$str)
{
echo '<option value="',$key,'"',($_SESSION[APP.'_setup_lang']===$key ? ' selected' : ''),'>',$str,'</option>';
}
echo '</select>
<button type="submit">Ok</button>
</p>
</form>
<br>
<br>
';

// --------
// HTML END
// --------

include APP.'_setup_ft.php';