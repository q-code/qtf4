<?php
// Html config
if ( !defined('QT_HTML_CHAR') ) define ('QT_HTML_CHAR', 'utf-8');
if ( !defined('QT_HTML_DIR') ) define ('QT_HTML_DIR', 'ltr');
if ( !defined('QT_HTML_LANG') ) define ('QT_HTML_LANG', 'en');

/**
 * TRANSLATION RULES
 * Use capital on first lettre, the script changes to lower case if required.
 * The index cannot contain the [.] point character.
 * Plural forms are definied by adding '+' to the index
 * The doublequote ["] is forbidden
 * To include a single quote use escape [\']
 * Use html entities for accent characters. You can use plain accent characters if your are sure that this file is utf-8 encoded
 * Note: If you need to re-use a word in lowercase inside an other definition, you can use strtolower($L['Word'])
 */

// TOP LEVEL VOCABULARY
// Use the top level vocabulary to give the most appropriate name for the topics (object items) managed by this application.
// e.g. Ticket, Incident, Subject, Thread, Request...
$L['Domain']='Domain'; $L['Domain+']='Domains';
$L['Section']='Forum'; $L['Section+']='Forums';
$L['Item']='Subject'; $L['Item+']='Subjects';
$L['item']='subject'; $L['item+']='subjects'; // lowercase because re-used in language definition
$L['Reply']='Reply'; $L['Reply+']='Replies';
$L['reply']='reply'; $L['reply+']='replies';
$L['News']='News'; $L['News+']='News'; // In other languages: News=One news, News+=Several news
$L['news']='news'; $L['news+']='news';

// Controls
$L['Y']='Yes';
$L['N']='No';
$L['And']='And';
$L['Or']='Or';
$L['Ok']='Ok';
$L['Cancel']='Cancel';
$L['Save']='Save';
$L['Exit']='Exit';
$L['Reset']='Reset';

// Errors
include 'app_error.php'; // includes roles

// Menu
$L['Administration']='Administration';
$L['Coppa_form']='Coppa form';
$L['Help']='Help';
$L['About']='About';
$L['Legal']='Legal notices';
$L['Login']='Sign in';
$L['Logout']='Sign out';
$L['Memberlist']='Memberlist';
$L['Profile']='Profile';
$L['Register']='Register';
$L['Search']='Search';
$L['View_n']='Normal view';
$L['View_c']='Compact view';

// Specific vocabulary
$L['Item_add']='New subject';
$L['Item_del']='Delete subject';
$L['Item_upd']='Update subject';
$L['Items_in_section']='Subjets in the forum';
$L['User']='User'; $L['User+']='Users';
$L['User_add']='Add user';
$L['User_upd']='Edit profile';
$L['Status']='Status'; $L['Status+']='Statuses';
$L['Hidden']='Hidden'; $L['Hidden+']='Hidden';
$L['Member']='Member'; $L['Member+']='Members';
$L['Message']='Message'; $L['Message+']='Messages';
$L['Message_deleted']='Message deleted...';
$L['Forward']='Forward'; $L['Forward+']='Forwards';
$L['First_message']='First message';
$L['Last_message']='Last message';
$L['Ref']='Ref.';
$L['Title']='Title';
$L['Smiley']='Smiley';
$L['Username']='Username';
$L['Role']='Role';
$L['Joined']='Joined';
$L['Picture']='Picture';
$L['Picture+']='Pictures';
$L['Signature']='Signature';
$L['Item_starter']='Subject starter';
$L['Modified_by']='Modified by';
$L['Deleted_by']='Deleted by';
$L['Top_participants']='Top participants';
$L['Section_moderator']='Forum moderator';
$L['Tag']='Tag'; $L['Tag+']='Tags';
$L['Used_tags']='Used tags';

// Common
$L['Action']='Action';
$L['Add']='Add';
$L['All']='All';
$L['Attachment']='Attachment'; $L['Attachment+']='Attachments';
$L['Author']='Author';
$L['Birthday']='Date of birth';
$L['Birthdays_calendar']='Birthdays calendar';
$L['Birthdays_today']='Happy birthday';
$L['By']='By';
$L['Change']='Change';
$L['Change_name']='Change username';
$L['Changed']='Changed';
$L['Close']='Close';
$L['Closed']='Closed';
$L['Column']='Column';
$L['Command']='Command'; $L['Command+']='Commands';
$L['Confirm']='Confirm';
$L['Contact']='Contact';
$L['Containing']='Containing';
$L['Continue']='Continue';
$L['Coord']='Coordinates';
$L['Coord_latlon']='(lat,lon)';
$L['Csv']='Export'; $L['H_Csv']='Download to spreadsheet';
$L['Date']='Date'; $L['Date+']='Dates';
$L['Day']='Day'; $L['Day+']='Days';
$L['Default']='Default'; $L['Use_default']='Use default';
$L['Delete_tags']='Remove (click a tag, or type * to remove all tags)';
$L['Destination']='Destination';
$L['Details']='Details';
$L['Display_at']='Display at date';
$L['Drop_attachment']='Drop attachment';
$L['Edit_tags']='Add/remove tags (use ; as separator)';
$L['Email']='E-mail';
$L['First']='First';
$L['Found']='Found'; $L['Found+']='Found';
$L['From']='From';
$L['Goto']='Jump to';
$L['H_Website']='Url of your website (with http://)';
$L['Help']='Help';
$L['I_wrote']='I wrote';
$L['Information']='Information';
$L['Items_per_month']='Subjects per month';
$L['Items_per_month_cumul']='Cumulative subjects per month';
$L['Last']='Last';
$L['Legend']='Legend';
$L['Location']='Location';
$L['Maximum']='Maximum';
$L['Minimum']='Minimum';
$L['Missing']='Missing information';
$L['Month']='Month';
$L['More']='More';
$L['My_preferences']='My preferences';
$L['Name']='Name';
$L['None']='None';
$L['Only']='Only';
$L['Options']='Options';
$L['Opened']='Opened';
$L['Page']='page'; $L['Page+']='pages';
$L['Password']='Password';
$L['Phone']='Phone';
$L['Preview']='Preview';
$L['Privacy']='Privacy';
$L['Reason']='Reason';
$L['Remove']='Remove';
$L['Result']='Result'; $L['Result+']='Results';
$L['Send']='Post';
$L['Send_on_behalf']='On behalf of';
$L['Show']='Show';
$L['This']='This';
$L['Time']='Time';
$L['Total']='Total';
$L['Type']='Type';
$L['Unchanged']='Unchanged';
$L['Website']='Website';
$L['Welcome']='Welcome';
$L['Welcome_to']='We welcome a new member, ';
$L['Welcome_not']='I\'m not %s';
$L['Year']='Year';

// Section
$L['New_item']='New '.$L['item'];
$L['Goto_message']='View last message';
$L['Item_re-opened']='Subject re-opened';
$L['Item_moved']='Subject moved';
$L['Item_deleted']='Subject deleted';
$L['Close_my_item']='I close my '.$L['item'];
$L['Closed_item']='Closed '.$L['item']; $L['Closed_item+']='Closed '.$L['item+'];
$L['You_reply']='I replied';
$L['Views']='Views';
$L['Quote']='Quote';
$L['Edit']='Edit';
$L['Delete']='Delete';
$L['Move']='Move';
$L['Move_to']='Move to';
$L['Prune']='Prune';
$L['Unreplied']='Unreplied'; $L['Unreplied_news']='Unreplied news';
$L['Unreplied_def']='Opened subjects without reply since %s days or more';
$L['Quick_reply']='Quick reply';
$L['Previous_replies']='Previous messages';
$L['Close_item']='Close the subject';
$L['Edit_message']='Edit message';
$L['Delete_message']='Delete message';
$L['Message_deleted']='Message deleted...';
$L['Members_deleted']='Users deleted...';
$L['Move_keep']='Use same number';
$L['Move_reset']='Remove (reset to zero)';
$L['Move_follow']='Increment (according to destination)';
$L['Edit_start']='Start editing';
$L['Edit_stop']='Stop editing';
$L['Showhide_legend']='Show/minimize info and legend';
$L['Only_your_items']='In this forum, only your '.$L['item+'].' can be displayed.';

// Search
$L['Advanced_search']='Advanced search';
$L['Recent_items']='Recent '.$L['item+'];
$L['All_news']='All news';
$L['All_my_items']='My messages';
$L['Keywords']='Keyword(s)';
$L['Search_option']='Search option';
$L['Search_criteria']='Search criteria';
$L['Search_by_key']='Search by keyword(s)';
$L['Search_by_ref']='Search reference number';
$L['Search_by_date']='Search by date';
$L['Search_by_tags']='Search by tags';
$L['Any_time']='Any time';
$L['Any_status']='Any status';
$L['Search_result']='Search result';
$L['In_title_only']='In title only';
$L['In_all_sections']='In all forums';
$L['H_Reference']='(type the numeric part only)';
$L['Too_many_keys']='Too many keys';
$L['Number_or_keyword']='Reference number or keyword';
$L['Search_by_words']='Search separate words';
$L['Search_exact_words']='Search';
$L['This_week']='This week';
$L['This_month']='This month';
$L['This_year']='This year';
$L['With_tag']= 'With tag';
$L['Show_only_tag']='Tags in this list <small>(click to filter)</small>';
$L['Multiple_input']='You can enter several words separated by %1$s (ex.: t1%1$st2 means subjects containing "t1" or "t2").';

// Search result
$L['Search_results']=$L['Item+'];
$L['Search_results_tags']=$L['Item+'].' with tag %s';
$L['Search_results_ref']=$L['Item+'].' with ref. %s';
$L['Search_results_keyword']=$L['Message+'].' containing %s';
$L['Search_results_user']=$L['Item+'].' issued by %s';
$L['Search_results_user_m']='Messages issued by %s';
$L['Search_results_last']='Recent '.$L['item+'].' (last week)';
$L['Search_results_news']=$L['News'];
$L['Only_in_section']='Only in forum';
$L['Username_starting']='Username starting with';
$L['other_char']='other char';

// Stats
$L['Statistics']='Statistics';
$L['Board_start_date']='Board start date';
$L['General_site']='General site';

// Privacy
$L['Privacy_visible_0']='Hidden data';
$L['Privacy_visible_1']='Data visible to members';
$L['Privacy_visible_2']='Data visible to visitors';

// Restrictions
$L['R_login_register']='Access is restricted to members only.<br><br>Please log in, or proceed to registration to become member.';
$L['R_member']='Access is restricted to members only.';
$L['R_staff']='Access is restricted to moderators only.';
$L['R_security']='Security settings does not allow using this function.';
$L['No_attachment_preview']='Attachment not available in preview';
$L['Closed_hidden_by_pref']='Closed '.$L['item+'].' are not displayed due to my preferences';

// Success
$L['S_registration']='Registration successful...';
$L['S_update']='Update successful...';
$L['S_delete']='Delete completed...';
$L['S_insert']='Creation successful...';
$L['S_save']  ='Save completed...';
$L['S_message_saved']='Message saved...<br>Thank you';

// Dates
$L['dateMMM']=array(1=>'January','February','March','April','May','June','July','August','September','October','November','December');
$L['dateMM'] =array(1=>'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
$L['dateM']  =array(1=>'J','F','M','A','M','J','J','A','S','O','N','D');
$L['dateDDD']=array(1=>'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
$L['dateDD'] =array(1=>'Mon','Tue','Wed','Thu','Fri','Sat','Sun');
$L['dateD']  =array(1=>'M','T','W','T','F','S','S');
$L['dateSQL']=array(
  'January'  => 'January',
  'February' => 'February',
  'March'    => 'March',
  'April'    => 'April',
  'May'      => 'May',
  'June'     => 'June',
  'July'     => 'July',
  'August'   => 'August',
  'September'=> 'September',
  'October'  => 'October',
  'November' => 'November',
  'December' => 'December',
  'Monday'   => 'Monday',
  'Tuesday'  => 'Tuesday',
  'Wednesday'=> 'Wednesday',
  'Thursday' => 'Thursday',
  'Friday'   => 'Friday',
  'Saturday' => 'Saturday',
  'Sunday'   => 'Sunday',
  'Today'=>'Today',
  'Yesterday'=> 'Yesterday',
  'Jan'=>'Jan',
  'Feb'=>'Feb',
  'Mar'=>'Mar',
  'Apr'=>'Apr',
  'May'=>'May',
  'Jun'=>'Jun',
  'Jul'=>'Jul',
  'Aug'=>'Aug',
  'Sep'=>'Sep',
  'Oct'=>'Oct',
  'Nov'=>'Nov',
  'Dec'=>'Dec',
  'Mon'=>'Mon',
  'Tue'=>'Tue',
  'Wed'=>'Wed',
  'Thu'=>'Thu',
  'Fri'=>'Fri',
  'Sat'=>'Sat',
  'Sun'=>'Sun');