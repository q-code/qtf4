<?php
// v4.0 build:20240210
// WARNING: requires config/config_db.php
// WARNING: requires php 5.6.x or next (uses scalar expression const)

// ------
// System constants (CANNOT be changed by webmasters)
// ------
const APP = 'qtf'; // application file prefix
const APPNAME = 'QuickTalk forum';
define('QT', APP.(defined('QDB_INSTALL') ? substr(QDB_INSTALL,-1) : '')); // memory namespace "qtf{n}"
const VERSION = '4.0';
const BUILD = 'build:20240210';
const TABDOMAIN = QDB_PREFIX.'qtadomain';
const TABSECTION = QDB_PREFIX.'qtaforum';
const TABUSER = QDB_PREFIX.'qtauser';
const TABTOPIC = QDB_PREFIX.'qtatopic';
const TABPOST = QDB_PREFIX.'qtapost';
const TABSETTING = QDB_PREFIX.'qtasetting';
const TABLANG = QDB_PREFIX.'qtalang';
const TABTABLES = ['TABTOPIC','TABPOST','TABSECTION','TABUSER','TABLANG','TABSETTING','TABDOMAIN'];
const QSEPARATOR = ';'; // Values separator in search queries (used as jQuery autocomplete-ajax separator and qt_tags.js) CANNOT BE EMPTY!
// About the memory namespace (session variablenames starting with constant QT):
//   By default QT = "qtf{n}" where n is the last character of QDB_INSTALL value (in the configuration file config_db.php)
//   If you run 2 applications on the same server, each application requires a unique namespace:
//   check that QDB_INSTALL values are different in each config_db.php files (ex: qtf1 and qtf2)
const PAGE_SIZES = [10,25,50,100]; // Items shown par page (user can select as preferences)
const BAN_DAYS = [0,1,7,15,30,90,365]; // Index 0..6 correspond to ban duration 0...365 days

// ------
// Interface constants (can be changed by webmasters)
// ------
const QT_TITLE_WITH_PAGENAME = false; // add page name to the html title (eg. 'QT-cute Profile')
const QT_COLOR_SCHEME = 'light dark'; // meta color-schemes for the browser
const QT_LOGIN_WITH_EMAIL = true; // allow login with email (false to use only username)
const QT_MENU_CONTRAST = true; // allow user to change css mode: contrast/normal
const QT_DIR_DOC = 'upload/'; // document storage directory (with final '/')
const QT_DIR_PIC = 'avatar/'; // profile pic storage directory (with final '/')
const QT_FLOOD = 5; // Prevent double-post of message by a user (delay in seconds)
const QT_UPLOAD_MAXSIZE = 8; // Maximum attachement size in Mb (8 recommended). Severs have several limits (upload_max_filesize, post_max_size and memory_limits). Some providers limit upload at 8Mb.
const QT_CHANGE_USERNAME = false;// Allow users changing their username (login). False = only administrators can change the username.
const QT_HOTTOPICS = 20; // Number of replies for a topic to become hottopic. Use FALSE to disable hottopic icon
const QT_SIMPLESEARCH = true; // Shows simple search popup (false goes directly to advanced search)
const QT_DFLT_VIEWMODE = 'N'; // default view mode: N=normal view, C=compact view
const QT_SHOW_VIEWMODE = true; // allow user to change view mode
const QT_SHOW_PARENT_DESCR = true; // Show section description or ticket reference as page title (if no search criteria)
const QT_FIRSTLINE_SIZE = 64; // Message first line (size) in the list of topics
const QT_SHOW_MODERATOR = true; // show moderator in the bottom bar
const QT_SHOW_JUMPTO = true;  // show gotolist in the bottom bar
const QT_CRUMBTRAIL = '&#8201;&middot;&#8201;'; // crumbtrail separator (should include spaces)
const QT_SHOW_DOMAIN = false; // show domain + section name in the crumb trail bar
const QT_CONVERT_AMP = false; // Store & as &#38; in the DB. True makes symbol &#...; NOT working. WARNING if true, & in username/password are also stored as &#38;
const QT_LIST_ME = true; // execute the 'i-replied' search in section list
const QT_LIST_TAG = true; // display a the tag-list under the section (can be used as search tool).
const QT_JAVA_MAIL = true; // Protect e-mail by a javascript
const QT_LOWERCASE_TAG = true; // store tags as lowercase (true recommended). With false, tags are case sensitive (ie. user likely inputs "duplicate").
const QT_WEEKSTART = 1; // Start of the week (use code 1=monday,...,7=sunday)
const QT_STAFFEDITUSER = true; // Staff member can edit some profile info of a user (picture,signature,contact-info)
const QT_STAFFEDITSTAFF = false; // Staff member can edit some profile info of an other staff member (picture,signature,contact-info)
const QT_STAFFEDITADMIN = false; // Staff member can edit some profile info of an administrator (picture,signature,contact-info)
const QT_SECTIONLOGO_SIZE = 2; // Maximum size of section logo (MB). Used in picture upload cropping tool
const QT_SECTIONLOGO_WIDTH = 100; // Maximum size of section logo (pixels). Used in picture upload cropping tool
const QT_SECTIONLOGO_HEIGHT = 100; // Maximum size of section logo (pixels). Used in picture upload cropping tool
const QT_REMEMBER_ME = true; // Allows "remember me" on login page (i.e. coockie login + confirmation message)
const QT_URLREWRITE = false;
// URL rewriting (for expert only):
// Rewriting url requires that your server is configured with following rule for your quicktalk folder: RewriteRule ^(.+)\.html(.*) qtf_$1.php$2 [L]
// This can NOT be activated if your quicktalk folder contains html pages (they will not be accessible anymore when urlrewriting is acticated)
const QT_URLCONST = false; // use 'forum' as url replacement or use FALSE to show url
// When using Q_URLCONST, it's recommanded to add following .htaccess rewriting rule in the application folder:
// RewriteEngine on
// RewriteRule "forum" "qtf_index.php"

// ------
// MEMCACHE (this can be changed by webmaster)
// ------
// If memcache is not available on your server use MEMCACHE_HOST = false;
// otherwise define your host name. Ex: const MEMCACHE_HOST = 'localhost';
const MEMCACHE_HOST = 'localhost'; // Memcache allows storing frequently used values in memcache server (instead of runnning sql requests)
const MEMCACHE_PORT = 11211; // memcache port (integer). Default port is 11211.
const MEMCACHE_TIMEOUT = 9999; // default memcache timeout in seconds (0=no timeout)

// ------
// OTHER
// ------
if ( !defined('PHP_VERSION_ID') ) { $arr=explode('.',PHP_VERSION); define('PHP_VERSION_ID',($arr[0]*10000+$arr[1]*100+$arr[2])); }