<?php
// Html config
if ( !defined('QT_HTML_CHAR') ) define ('QT_HTML_CHAR', 'utf-8');
if ( !defined('QT_HTML_DIR') ) define ('QT_HTML_DIR', 'ltr');
if ( !defined('QT_HTML_LANG') ) define ('QT_HTML_LANG', 'fr');

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
$L['Domain']='Domaine'; $L['Domain+']='Domaines';
$L['Section']='Forum';  $L['Section+']='Forums';
$L['Item']='Sujet';     $L['Item+']='Sujets';
$L['item']='sujet';     $L['item+']='sujets'; // lowercase because re-used in language definition
$L['Reply']='Réponse'; $L['Reply+']='Réponses';
$L['reply']='réponse'; $L['reply+']='réponses';
$L['News']='News'; $L['News+']='News'; // News=One news, Newss=Several news
$L['news']='news'; $L['news+']='news';

// Controls
$L['Y']='Oui';
$L['N']='Non';
$L['And']='Et';
$L['Or']='Ou';
$L['Ok']='Ok';
$L['Cancel']='Annuler';
$L['Save']='Sauver';
$L['Exit']='Exit'; $L['Reset']='Effacer';

// Errors
include 'app_error.php'; // includes roles

// Menu
$L['Administration']='Administration';
$L['Coppa_form']='Formulaire Coppa';
$L['Help']='Aide';
$L['About']='À propos';
$L['Legal']='Notices légales';
$L['Login']='Connexion';
$L['Logout']='Déconnexion';
$L['Memberlist']='Membres';
$L['Profile']='Profil';
$L['Register']='S\'enregistrer';
$L['Search']='Chercher';
$L['View_n']='Vue normale';
$L['View_c']='Vue compacte';

// Specific vocabulary
$L['Item_add']='Nouveau sujet';
$L['Item_del']='Effacer le sujet';
$L['Item_upd']='Modifier le sujet';
$L['Items_in_section']='Sujets dans le forum';
$L['User']='Utilisateur'; $L['User+']='Utilisateurs';
$L['User_add']='Ajouter un utilisateur';
$L['User_upd']='Editer le profil';
$L['Status']='Statut'; $L['Status+']='Statuts';
$L['Hidden']='Caché'; $L['Hidden+']='Cachés';
$L['Member']='Membre'; $L['Member+']='Membres';
$L['Message']='Message'; $L['Message+']='Messages';
$L['Message_deleted']='Message effacé...';
$L['Forward']='Transfers'; $L['Forward+']='Transfers';
$L['First_message']='Premier message';
$L['Last_message']='Dernier message';
$L['Reply']='Réponse'; $L['Reply+']='Réponses';
$L['Ref']='Ref.';
$L['Title']='Titre';
$L['Smiley']='Emoticone';
$L['Role']='Rôle';
$L['Username']='Nom d\'utilisateur';
$L['Joined']='Depuis';
$L['Picture']='Photo';
$L['Picture+']='Photos';
$L['Signature']='Signature';
$L['Item_starter']='Initiateur';
$L['Modified_by']='Modifié par';
$L['Deleted_by']='Effacé par';
$L['Top_participants']='Top participants';
$L['Section_moderator']='Modérateur du forum';
$L['Tag']='Tag';
$L['Tag+']='Tags';
$L['Used_tags']=$L['Tag+'].' utilisés';
$L['Edit_tags']='Ajouter/enlever des '.strtolower($L['Tag+']).' (séparés par ; )';
$L['Delete_tags']='Enlever (clickez un '.strtolower($L['Tag']).', ou tappez * pour tout enlever)';
$L['With_tag']= 'Tag';
$L['Show_only_tag']='Tags dans cette list <small>(clickez pour filtrer)</small>';

// Common
$L['Action']='Action';
$L['Add']='Ajouter';
$L['All']='Tous';
$L['Attachment']='Document attaché'; $L['Attachment+']='Documents attachés';
$L['Author']='Auteur';
$L['Birthday']='Date de naissance';
$L['Birthdays_calendar']='Calendrier des anniversaires';
$L['Birthdays_today']='Joyeux anniversaire';
$L['By']='Par';
$L['Change']='Changer';
$L['Change_name']='Changer l\'identifiant';
$L['Close']='Fermer';
$L['Closed']='Fermé';
$L['Column']='Colonne';
$L['Command']='Commande'; $L['Command+']='Commandes';
$L['Confirm']='Confirmer';
$L['Contact']='Contact';
$L['Containing']='Contenant';
$L['Continue']='Continuer';
$L['Coord']='Coordonnées';
$L['Coord_latlon']='(lat,lon)';
$L['Csv']='Export'; $L['H_Csv']='Ouvrir dans un tableur';
$L['Date']='Date'; $L['Date+']='Dates';
$L['Day']='Jour'; $L['Day+']='Jours';
$L['Default']='Défaut'; $L['Use_default']='Utiliser le défaut';
$L['Destination']='Destination';
$L['Details']='Détails';
$L['Display_at']='Afficher à la date';
$L['Drop_attachment']='Enlever document attaché';
$L['Email']='E-mail';
$L['First']='Première';
$L['Found']='Trouvé'; $L['Found+']='Trouvés';
$L['From']='De';
$L['Goto']='Allez';
$L['H_Website']='Url avec http://';
$L['I_wrote']='J\'ai écrit';
$L['Information']='Information';
$L['Items_per_month']='Sujets par mois';
$L['Items_per_month_cumul']='Cumul des sujets par mois';
$L['Last']='Dernière';
$L['Legend']='Légende';
$L['Location']='Localisation';
$L['Maximum']='Maximum';
$L['Minimum']='Minimum';
$L['Missing']='Un champ obligatoire est vide';
$L['Month']='Mois';
$L['More']='Plus';
$L['My_preferences']='Mes préférences';
$L['Name']='Nom';
$L['None']='Aucun';
$L['Only']='Uniquement';
$L['Options']='Options';
$L['Opened']='Ouvert';
$L['Page']='page'; $L['Page+']='pages';
$L['Password']='Mot de passe';
$L['Phone']='Téléphone';
$L['Preview']='Aperçu';
$L['Privacy']='Vie privée';
$L['Reason']='Raison';
$L['Remove']='Enlever';
$L['Result']='Résultat'; $L['Result+']='Résultats';
$L['Send']='Poster';
$L['Send_on_behalf']='Au nom de';
$L['Show']='Afficher';
$L['Time']='Heure';
$L['Total']='Total';
$L['Type']='Type';
$L['Unchanged']='Inchangé';
$L['Website']='Site web';
$L['Welcome']='Bienvenue';
$L['Welcome_to']='Bienvenue à un nouveau membre, ';
$L['Welcome_not']='Je ne suis pas %s';
$L['Year']='Année';

// Section
$L['New_item']='Nouveau '.$L['item'];
$L['Goto_message']='Voir le dernier message';
$L['Item_re-opened']='Sujet ré-ouvert';
$L['Item_moved']='Sujet déplacé';
$L['Item_deleted']='Sujet effacé';
$L['Close_my_item']='Je ferme mon sujet';
$L['Closed_item']=$L['Item'].' fermé';
$L['Closed_item+']=$L['Item+'].' fermés';
$L['You_reply']='Vous avez répondu';
$L['Views']='Vues';
$L['Quote']='Citer';
$L['Edit']='Editer';
$L['Delete']='Effacer';
$L['Move']='Déplacer';
$L['Move_to']='Déplacer vers';
$L['Prune']='Elaguer';
$L['Unreplied']='Abandonnés';
$L['Unreplied_news']='News abandonnées';
$L['Unreplied_def']='Sujets ouverts et sans réponse depuis plus de %s jours';
$L['Quick_reply']='Réponse rapide';
$L['Previous_replies']='Messages précédents';
$L['Close_item']='Fermer le sujet';
$L['Edit_message']='Editer le message';
$L['Delete_message']='Effacer le message';
$L['Message_deleted']='Message effacé...';
$L['Members_deleted']='Membres effacés...';
$L['Move_keep']='Même numéro';
$L['Move_reset']='Supprimer (remettre à zéro)';
$L['Move_follow']='Incrémenter (suivant destination)';
$L['Edit_start']='Modifier';
$L['Edit_stop']='Arrêter l\'édition';
$L['Showhide_legend']='Afficher/réduire les info et légende';
$L['Only_your_items']='Dans ce forum, seuls vos propres '.$L['item+'].' peuvent être affichés.';

// Search

$L['Advanced_search']='Recherche avancée';
$L['Recent_items']=$L['Item+'].' récents';
$L['All_news']='Toutes les news';
$L['All_my_items']='Mes messages';
$L['Keywords']='Mot(s) clé';
$L['Search_option']='Option de recherche';
$L['Search_criteria']='Critère de recherche';
$L['Any_time']='Toute date';
$L['Any_status']='Tout statut';
$L['Number_or_keyword']='Numéro de référence ou mot clé';
$L['Search_by_key']='Chercher par mot(s) clé';
$L['Search_by_ref']='Chercher par numéro';
$L['Search_by_date']='Chercher par date';
$L['Search_by_tags']='Chercher par catégories';
$L['Search_result']='Résultat de la recherche';
$L['In_title_only']='Dans le titre uniquement';
$L['In_all_sections']='Dans tous les forums';
$L['H_Reference']='(entrez seulement la partie numérique)';
$L['Too_many_keys']='Trop de mots clés';
$L['Search_by_words']='Chercher ces mots séparés';
$L['Search_exact_words']='Chercher';
$L['This_week']='Cette semaine';
$L['This_month']='Ce mois';
$L['This_year']='Cette année';
$L['Multiple_input']='Vous pouvez indiquer plusieurs mots séparés par %1$s (ex.: t1%1$st2 recherche les sujets contenant "t1" ou "t2").';

// Search result
$L['Search_results']=$L['Item+'];
$L['Search_results_tags']=$L['Item+'].' avec tag %s';
$L['Search_results_ref']=$L['Item+'].' ayant la réf. %s';
$L['Search_results_keyword']=$L['Message+'].' contenant %s';
$L['Search_results_user']=$L['Item+'].' créés par %s';
$L['Search_results_user_m']=$L['Message+'].' créés par %s';
$L['Search_results_last']=$L['Item+'].' récents (dernière semaine)';
$L['Search_results_news']=$L['News'];
$L['Only_in_section']='Dans le forum';
$L['Username_starting']='Nom d\'utilisateur commençant par';
$L['other_char']='un chiffre ou symbole';

// Stats
$L['Statistics']='Statistiques';
$L['General_site']='Site en géneral';
$L['Board_start_date']='Date de début';

// Pricacy
$L['Privacy_visible_0']='Donnée masquée';
$L['Privacy_visible_1']='Donnée visible par les membres';
$L['Privacy_visible_2']='Donnée visible par les visiteurs';

// Restrictions
$L['R_login_register']='Accès réservé aux seuls membres...<br><br>Veuillez vous connecter pour pouvoir continuer. Pour devenir membre, utilisez le menu s\'enregistrer.';
$L['R_member']='Accès réservé aux seuls membres.';
$L['R_staff']='Accès réservé aux seuls modérateurs.';
$L['R_security']='Les paramètres de sécurités ne permettent pas d\'utiliser cette fonction.';
$L['No_attachment_preview']='Pièce jointe non visible en prévisualisation';
$L['Closed_hidden_by_pref']='Les '.$L['item+'].' fermés ne sont pas affichés en raison de vos préférences';

// Success

$L['S_registration']='Inscription effectuée...';
$L['S_update']='Changement effectué...';
$L['S_delete']='Effacement effectué...';
$L['S_insert']='Création terminée...';
$L['S_save']='Sauvegarde réussie...';
$L['S_message_saved']='Message sauvé...<br>Merci';

// Timezones

$L['dateMMM']=array(1=>'Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Ao&ucirc;t','Septembre','Octobre','Novembre','Décembre');
$L['dateMM']=array(1=>'Jan','Fév','Mars','Avr','Mai','Juin','Juil','Ao&ucirc;t','Sept','Oct','Nov','Déc');
$L['dateM']=array(1=>'J','F','M','A','M','J','J','A','S','O','N','D');
$L['dateDDD']=array(1=>'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche');
$L['dateDD']=array(1=>'Mon','Tue','Wed','Thu','Fri','Sat','Sun');
$L['dateD']=array(1=>'L','M','M','J','V','S','D');
$L['dateSQL']=array(
  'January'  => 'Janvier',
  'February' => 'Février',
  'March'    => 'Mars',
  'April'    => 'Avril',
  'May'      => 'Mai',
  'June'     => 'Juin',
  'July'     => 'Juillet',
  'August'   => 'Ao&ucirc;t',
  'September'=> 'Septembre',
  'October'  => 'Octobre',
  'November' => 'Novembre',
  'December' => 'Décembre',
  'Monday'   => 'Lundi',
  'Tuesday'  => 'Mardi',
  'Wednesday'=> 'Mercredi',
  'Thursday' => 'Jeudi',
  'Friday'   => 'Vendredi',
  'Saturday' => 'Samedi',
  'Sunday'   => 'Dimanche',
  'Today'=>'Aujourd\'hui',
  'Yesterday'=>'Hier',
  'Jan'=>'Jan',
  'Feb'=>'Fév',
  'Mar'=>'Mar',
  'Apr'=>'Avr',
  'May'=>'Mai',
  'Jun'=>'Jun',
  'Jul'=>'Jul',
  'Aug'=>'Ao&ucirc;t',
  'Sep'=>'Sept',
  'Oct'=>'Oct',
  'Nov'=>'Nov',
  'Dec'=>'Déc',
  'Mon'=>'Lu',
  'Tue'=>'Ma',
  'Wed'=>'Me',
  'Thu'=>'Je',
  'Fri'=>'Ve',
  'Sat'=>'Sa',
  'Sun'=>'Di');