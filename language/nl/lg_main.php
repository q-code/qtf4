<?php
// Html config
if ( !defined('QT_HTML_CHAR') ) define ('QT_HTML_CHAR', 'utf-8');
if ( !defined('QT_HTML_DIR') ) define ('QT_HTML_DIR', 'ltr');
if ( !defined('QT_HTML_LANG') ) define ('QT_HTML_LANG', 'nl');

/**
 * TRANSLATION RULES
 * Use capital on first lettre, the script changes to lower case if required.
 * The index cannot contain the [.] point character. Plural forms are definied by adding '+' to the index
 * The doublequote ["] is forbidden
 * To include a single quote use escape [\']
 * Use html entities for accent characters. You can use plain accent characters if your are sure that this file is utf-8 encoded
 * Note: If you need to re-use a word in lowercase inside an other definition, you can use strtolower($L['Word'])
 */

// TOP LEVEL VOCABULARY
// Use the top level vocabulary to give the most appropriate name for the topics (object items) managed by this application.
// e.g. Ticket, Incident, Subject, Thread, Request...
$L['Domain']='Domein';   $L['Domain+']='Domeinen';
$L['Section']='Forum';   $L['Section+']='Forums';
$L['Item']='Onderwerp';  $L['Item+']='Onderwerpen';
$L['item']='onderwerp';  $L['item+']='onderwerpen'; // lowercase because re-used in language definition
$L['Reply']='Antwoord';  $L['Reply+']='Antwoorden';
$L['reply']='antwoord';  $L['reply+']='antwoorden';
$L['News']='Nieuws';     $L['News+']='Nieuws'; //News=One news, Newss=Several news
$L['news']='nieuws';     $L['news+']='nieuws';

// Controls
$L['Y']='Ja';
$L['N']='Nee';
$L['And']='En';
$L['Or']='Of';
$L['Ok']='Ok';
$L['Save']='Opslaan';
$L['Cancel']='Annuleren';
$L['Exit']='Exit';
$L['Reset']='Resetten';

// Errors
include 'app_error.php'; // includes roles

// Menu
$L['Administration']='Administratie';
$L['Coppa_form']='Coppa vorm';
$L['Help']='Hulp';
$L['About']='Over';
$L['Legal']='Privacybeleid';
$L['Login']='Inloggen';
$L['Logout']='Uitloggen';
$L['Memberlist']='Gebruikerslijst';
$L['Profile']='Profiel';
$L['Register']='Registreer';
$L['Search']='Zoeken';
$L['View_n']='Normale stijl';
$L['View_c']='Compacte stijl';

// Specific vocabulary
$L['Item_add']='Nieuwe bericht';
$L['Item_del']='Bericht verwijderen';
$L['Item_upd']='Bericht bewerken';
$L['Items_in_section']='Berichten in het forum';
$L['User']='Gebruiker';
$L['User+']='Gebruikers';
$L['User_add']='Gebruiker toevoegen';
$L['User_upd']='Profiel bewerken';
$L['Status']='Statuut'; $L['Status+']='Statuten';
$L['Hidden']='Verborgen'; $L['Hidden+']='Verborgen';
$L['Member']='Lid'; $L['Member+']='Leden';
$L['Message']='Bericht'; $L['Message+']='Berichten';
$L['Message_deleted']='Bericht verwijderd...';
$L['Forward']='Verstuurd bericht'; $L['Forward+']='verstuurd berichten';
$L['First_message']='Eerste bericht';
$L['Last_message']='Laatste bericht';
$L['Ref']='Ref.';
$L['Title']='Titel';
$L['Smiley']='Smiley';
$L['Username']='Gebruikersnaam';
$L['Role']='Rang';
$L['Joined']='Geregistreerd op';
$L['Picture']='Foto';
$L['Picture+']='Fotos';
$L['Signature']='Onderschrift';
$L['Item_starter']='Auteur';
$L['Modified_by']='Bewerkt door';
$L['Deleted_by']='Geschrapt door';
$L['Top_participants']='Top deelnemers';
$L['Section_moderator']='Forum moderator';
$L['Tag']='Label';
$L['Tag+']='Labels';
$L['Used_tags']='Gebruikte labels';

// Common
$L['Action']='Actie';
$L['Add']='Toevoegen';
$L['All']='Alles';
$L['Attachment']='Bijgevoegd document'; $L['Attachment+']='Bijgevoegde documenten';
$L['Author']='Auteur';
$L['Birthday']='Geboortedatum';
$L['Birthdays_calendar']='Verjaardag agenda';
$L['Birthdays_today']='Gelukkige verjaardag';
$L['By']='Door';
$L['Change']='Bewerk';
$L['Change_name']='Gebruikersnaam veranderen';
$L['Changed']='Bewerkt';
$L['Close']='Afsluiten';
$L['Closed']='Gesloten';
$L['Command']='Functie';
$L['Command+']='Functies';
$L['Confirm']='Bevestigen';
$L['Column']='Kolom';
$L['Contact']='Contact';
$L['Containing']='Bevat';
$L['Continue']='Voortduren';
$L['Coord']='Co&ouml;rdinaten';
$L['Coord_latlon']='(lat,lon)';
$L['Csv']='Export'; $L['H_Csv']='Tonen in spreadsheet';
$L['Date']='Datum'; $L['Date+']='Datum';
$L['Day']='Dag'; $L['Day+']='Dagen';
$L['Default']='Standaard';  $L['Use_default']='Standaardinstelling';
$L['Delete_tags']='Verwijderen (click een tag, of type * om alles te verwijderen)';
$L['Destination']='Bestemming';
$L['Details']='Details';
$L['Display_at']='Tonen op datum van';
$L['Drop_attachment']='Attachment wegnemen';
$L['Edit_tags']=$L['Tag+'].' toevoegen/verwijderen (gebruik ; als scheidingsteken)';
$L['Email']='E-mail';
$L['First']='Eerste';
$L['Found']='Gevonden'; $L['Found+']='Gevonden';
$L['From']='Uit';
$L['Goto']='Ga naar';
$L['H_Website']='Uw website url (met http://)';
$L['I_wrote']='Ik schreef';
$L['Information']='Informatie';
$L['Items_per_month']='Berichten per maand';
$L['Items_per_month_cumul']='Cumul berichten per maand';
$L['Last']='Laatste';
$L['Legend']='Legend';
$L['Location']='Woonplaats';
$L['Maximum']='Maximum';
$L['Minimum']='Minimum';
$L['Missing']='Verplicht data niet gevonden';
$L['Month']='Maand';
$L['More']='Meer';
$L['My_preferences']='Mijn voorkeuren';
$L['Name']='Naam';
$L['None']='Niets';
$L['Only']='Alleen';
$L['Options']='Opties';
$L['Opened']='Geopend';
$L['Page']='pagina'; $L['Page+']='pagina\'s';
$L['Password']='Wachtwoord';
$L['Phone']='Telefoon';
$L['Preview']='Voorproef';
$L['Privacy']='Privé-leven';
$L['Reason']='Reden';
$L['Remove']='Uitwissen';
$L['Result']='Resultaat'; $L['Result+']='Resultaten';
$L['Send']='Zenden';
$L['Send_on_behalf']='Namens';
$L['Show']='Tonen';
$L['Time']='Uren';
$L['Total']='Totaal';
$L['Type']='Type';
$L['Unchanged']='Onveranderd';
$L['Website']='Website';
$L['Welcome']='Welkom';
$L['Welcome_to']='Welkom voor een nieuwe gebruiker, ';
$L['Welcome_not']='Ik ben %s niet';
$L['Year']='Jaar';

// Section
$L['New_item']='Nieuw '.$L['item'];
$L['Goto_message']='Laatste bericht';
$L['Item_re-opened']='Heropend bericht';
$L['Item_moved']='Topic moved';
$L['Item_deleted']='Topic deleted';
$L['Close_my_item']='Ik sluit mijn bericht';
$L['Closed_item']='Gesloten '.$L['item']; $L['Closed_item+']='Gesloten '.$L['item+'];
$L['You_reply']='U antwoordt';
$L['Views']='Bekeken';
$L['Quote']='Citeren';
$L['Edit']='Bewerken';
$L['Delete']='Uitwissen';
$L['Move']='Verplaatsen';
$L['Move_to']='Verplatsen naar';
$L['Prune']='Snoeien';
$L['Unreplied']='Verloren';
$L['Unreplied_news']='Verloren nieuws';
$L['Unreplied_def']='Berichten zijn open en onbeantwoord voor meer dan %s dagen';
$L['Quick_reply']='Snel antwoord';
$L['Previous_replies']='Vorige '.$L['item+'];
$L['Close_item']='Sluit dit '.$L['item'];
$L['Edit_message']='Bewerk '.$L['item'];
$L['Delete_message']='Verwijder '.$L['item'];
$L['Message_deleted']='Bericht verwijderd...';
$L['Members_deleted']='Gebruikers verwijderd...';
$L['Move_keep']='Houd origineel nummer';
$L['Move_reset']='Verwijderen (terug naar 0)';
$L['Move_follow']='Nummer (volgt het bestemmingsforum)';
$L['Edit_start']='Wijzigen';
$L['Edit_stop']='Wijziging stoppen';
$L['Showhide_legend']='Tonen/verkleinen info en legenda';
$L['Only_your_items']='In dit forum kunnen, alleen uw  '.$L['item+'].'  worden weergegeven';

// Search
$L['Advanced_search']='Geavanceerd zoeken';
$L['Recent_items']='Recente '.$L['item+'];
$L['All_news']='Alle mededelingen';
$L['All_my_items']='Mijn berichten';
$L['Keywords']='Sleutelwoord(en)';
$L['Search_option']='Onderzoeksoptie';
$L['Search_criteria']='Onderzoekscriterium';
$L['Number_or_keyword']='Sleutelwoord(en)';
$L['Search_by_key']='Zoeken met sleutelwoord(en)';
$L['Search_by_ref']='Zoeken met nummer';
$L['Search_by_date']='Zoeken met datum';
$L['Search_by_tags']='Zoeken per tags';
$L['Search_result']='Resultaat van het onderzoek';
$L['In_title_only']='In titel alleen';
$L['In_all_sections']='In alle forums';
$L['Any_time']='Alle tijd';
$L['Any_status']='Alle statuut';
$L['H_Reference']='(typ het numerieke deel)';
$L['Too_many_keys']='Te veel sleutelwoorden';
$L['Search_by_words']='Elk woord afzonderlijk zoeken';
$L['Search_exact_words']='Zoeken';
$L['This_week']='Deze week';
$L['This_month']='Deze maand';
$L['This_year']='Dit jaar';
$L['With_tag']= 'Met tag';
$L['Show_only_tag']='Tags in deze lijst <small>(click om te filteren)</small>';
$L['Multiple_input']='Met %1$s kunt u verschillende worden invoeren (b.v.: t1%1$st2 betekend berichten met "t1" of "t2").';

// Search result
$L['Search_results']=$L['Item+'];
$L['Search_results_tags']=$L['Item+'].' met tag %s';
$L['Search_results_ref']=$L['Item+'].' met ref. %s';
$L['Search_results_keyword']=$L['Message+'].' met sleutelwoord %s';
$L['Search_results_user']=$L['Item+'].' door %s';
$L['Search_results_user_m']=$L['Message+'].' door %s';
$L['Search_results_last']='Recente '.$L['Item+'].' (laatste week)';
$L['Search_results_news']=$L['News'];
$L['Only_in_section']='Alleen in forum';
$L['Username_starting']='Gebruikersnaam begin met';
$L['other_char']='nummer of symbool';

// Stats
$L['Statistics']='Statistieken';
$L['General_site']='Algemene site';
$L['Board_start_date']='Begin datum';

// Privacy
$L['Privacy_visible_0']='Data onzichtbaar';
$L['Privacy_visible_1']='Data zichtbaar voor leden';
$L['Privacy_visible_2']='Data zichtbaar voor bezoekers';

// Restrictions
$L['R_login_register']='De toegang is beperkt tot slechts leden.<br><br>Gelieve in te loggen, of ga naar Registreerd om lid te worden.';
$L['R_member']='De toegang is beperkt tot slechts leden.';
$L['R_staff']='De toegang is beperkt tot slechts moderatoren.';
$L['R_security']='De veiligheid instellingen laten deze functie geen toe.';
$L['Closed_hidden_by_pref']='Gesloten '.$L['item+'].' worden niet weergegeven vanwege mijn voorkeuren';
$L['No_attachment_preview']='Bijlage niet zichtbaar in preview';

// Success
$L['S_registration']='Voltooide registratie...';
$L['S_update']='Voltooide update...';
$L['S_delete']='Schrap voltooid...';
$L['S_insert']='Succesvolle verwezenlijking...';
$L['S_save']='Sparen voltooid...';
$L['S_message_saved']='Bewaard bericht...<br>Dank u';

// Dates
$L['dateMMM']=array(1=>'Januari','Februari','Maart','April','Mei','Juni','Juli','Augustus','Septembre','Oktober','November','December');
$L['dateMM']=array(1=>'Jan','Feb','Mrt','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec');
$L['dateM']=array(1=>'J','F','M','A','M','J','J','A','S','O','N','D');
$L['dateDDD']=array(1=>'Maandag','Dinsdag','Woensdag','Donderdag','Vrijdag','Zaterdag','Zondag');
$L['dateDD']=array(1=>'Ma','Di','Wo','Do','Vr','Za','Zo');
$L['dateD']=array(1=>'M','D','W','D','V','Z','Z');
$L['dateSQL']=array(
  'January'  => 'januari',
  'February' => 'februari',
  'March'    => 'maart',
  'April'    => 'april',
  'May'      => 'mei',
  'June'     => 'juni',
  'July'     => 'juli',
  'August'   => 'augustus',
  'September'=> 'september',
  'October'  => 'oktober',
  'November' => 'november',
  'December' => 'december',
  'Monday'   => 'maandag',
  'Tuesday'  => 'dinsdag',
  'Wednesday'=> 'woensdag',
  'Thursday' => 'donderdag',
  'Friday'   => 'vrijdag',
  'Saturday' => 'zaterdag',
  'Sunday'   => 'zondag',
  'Today'=>'Vandaag',
  'Yesterday'=>'Gisteren',
  'Jan'=>'jan',
  'Feb'=>'feb',
  'Mar'=>'mrt',
  'Apr'=>'apr',
  'May'=>'mei',
  'Jun'=>'jun',
  'Jul'=>'jul',
  'Aug'=>'aug',
  'Sep'=>'sep',
  'Oct'=>'okt',
  'Nov'=>'nov',
  'Dec'=>'dec',
  'Mon'=>'ma',
  'Tue'=>'di',
  'Wed'=>'wo',
  'Thu'=>'do',
  'Fri'=>'vr',
  'Sat'=>'za',
  'Sun'=>'zo');