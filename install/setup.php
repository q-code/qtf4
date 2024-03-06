<?php // v4.0 build:20240210 allows app impersonation [qt f|i|e|n]

session_start();
include 'init.php';

// ------
// Html start
// ------
include 'setup_hd.php'; // this will show $oH->error

// Check config_db
if ( !file_exists('../config/config_db.php') || !is_readable('../config/config_db.php') || !is_writable('../config/config_db.php') ) {
  echo '<h1 style="color:#000000;margin:10px 0 5px 0">[EN] Before install</h1>';
  echo '<p>The configuration file <span class="bold">config/config_db.php</span> is not writable. Please make this file writable before starting installation (e.g. with a FTP client, set the file attributes to chmod 777)</p>';
  echo '<h1 style="color:#666666;margin:0 0 5px 20px">[FR] Avant d\'installer</h1>';
  echo '<p style="color:#666666;margin:0 0 5px 20px">Le fichier <span class="bold">config/config_db.php</span> n\'est pas inscriptible. Veillez rendre ce fichier inscriptible avant de lancer l\'installation (ex. avec un logiciel de FTP changer les attributs de securité de ce fichier en chmod 777)</p>';
  echo '<h1 style="color:#666666;margin:0 0 5px 20px">[NL] Voor installatie</h1>';
  echo '<p style="color:#666666;margin:0 0 5px 20px">De file <span class="bold">config/config_db.php</span> is read-only. U moet dit inschrijfbaar maken (bvb. met een FTP software u kan de veiligheid attributen naar chmod 777 veranderen)</p>';
  include 'setup_ft.php';
  exit;
}

// Check config_lang
if ( !file_exists('../config/config_lang.php') ) {
  echo '<h1 style="color:#000000;margin:10px 0 5px 0">[EN] Before install</h1>';
  echo '<p>The configuration file <span class="bold">config/config_lang.php</span> is missing. Please make this file available before starting installation.</p>';
  echo '<h1 style="color:#666666;margin:0 0 5px 20px">[FR] Avant d\'installer</h1>';
  echo '<p style="color:#666666;margin:0 0 5px 20px">Le fichier <span class="bold">config/config_lang.php</span> n\'est pas disponible. Veuillez rendre ce fichier disponible avant de lancer l\'installation.</p>';
  echo '<h1 style="color:#666666;margin:0 0 5px 20px">[NL] Voor installatie</h1>';
  echo '<p style="color:#666666;margin:0 0 5px 20px">De file <span class="bold">config/config_lang.php</span> is niet beschikbaar. Maak dit beschikbaar voordat u met de installatie begint.</p>';
  include 'setup_ft.php';
  exit;
}

// Read language subdirectories
include '../config/config_lang.php';
$arrOptions = [];
foreach(LANGUAGES as $iso=>$lang) {
  $arr = explode(' ',$lang,2); if ( empty($arr[1]) ) $arr[1] = $arr[0];
  if ( file_exists('src/'.$iso.'/lg_install.php') ) $arrOptions[$iso] = $arr[1];
}
asort($arrOptions);

echo '<h1 class="center">Language ?</h1>
<form method="get" action="setup_1.php">
<p class="center"><select name="lang" size="1">';
foreach($arrOptions as $iso=>$lang) echo '<option value="'.$iso.'"'.($_SESSION['setup_lang']===$iso ? ' selected' : '').'>'.$lang.'</option>';
echo '</select>
<button type="submit">Ok</button>
</p>
</form>
';

// ------
// HTML END
// ------
include 'setup_ft.php';