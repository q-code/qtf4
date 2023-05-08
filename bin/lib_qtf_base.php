<?php // v4.0 build:20230430

function makeFormCertificate(string $publickey)
{
  switch($publickey) {
    case '2b174f48ab4d9704934dda56c6997b3a':
    case '9db03580c02d0ac85d9d2d6611098fac':
    case '344cdd26d2c91e14d6fd27ab7e452a6f':
    case 'a2038e83fd6618a444a5de51bf2313de':
    case 'ec8a0d9ab2cae03d0c7314491eb60d0b':
    case 'db6a94aa9b95da757a97f96ab4ce4ca5': return md5($publickey.APP.$_SERVER['REMOTE_ADDR'].SECURE_QT_HASHKEY);
    default: global $oH; $oH->log[]='makeFormCertificate: cannot make certificate on public key '.$publickey; return '';
  }
  // Allow checking that POST requests come from a qtx page (register,login,search,items,item,edit)
}

// --------
// Specific functions: added for or using [SMem] class
// --------

function memInit(string $key, $onUnknownKey=false)
{
  // Recomputes basic data (to be stored in shared-memory)
  if ( !empty($_SESSION['QTdebugmem']) ) { global $oH; $oH->log[] = 'debug: '.__FUNCTION__.' '.$key; }
  // Object-translation memory, in current language: '_Len', '_Lfr', ...
  if ( substr($key,0,2)==='_L' ) {
    $iso = substr($key,2);
    return [
      'index' => SLang::get('index', $iso, '*'),
      'domain' => SLang::get('domain', $iso, '*'),
      'sec' => SLang::get('sec', $iso, '*'),
      'secdesc' => SLang::get('secdesc', $iso, '*') ];
  }
  // Dataset memory
  switch($key) {
    case 'settingsage': return time();
    case '_Domains':
      // all domains (including empty/invisible domains), array contains property=>value from CDomain class
      return CDomain::getPropertiesAll();
    case '_Sections':
      // all sections (including empty/invisible sections), array contains property=>value from CSection class
      // items,replies,lastpost are recomputed for each section
      return CSection::getPropertiesAll();
    case '_SectionsStats': return CSection::getSectionsStats(); // count topics and replies, by section (all)
    case '_NewUser': global $oDB; return SUser::getLastMember($oDB); // last registered user
  }
  // Unknown key (false)
  return $onUnknownKey;
}
function memFlush(array $arrKeep=['_Domains'], string $option='')
{
  if ( MEMCACHE_HOST===false ) return;
  // DEEP FLUSH
  if ( $option==='**' ) SMem::clear('**'); // only admin can use option to deep flush
  // Flush keys, if not in the $arrKeep list (by default, _Domains is preserved)
  foreach(['_Domains','_Sections','_SectionsStats'] as $k) if ( !in_array($k,$arrKeep) ) SMem::clear($k);
  return true;
}
function memFlushLang()
{
  if ( MEMCACHE_HOST===false ) return;
  foreach(array_keys(LANGUAGES) as $iso) SMem::clear('_L'.$iso);
  return true;
}
function memFlushStats($arrYears='default')
{
  if ( MEMCACHE_HOST===false ) return;
  // Flushes global stat
  SMem::clear('statG');
  // Check list of years. 'default' means last 2 years.
  if ( $arrYears==='default' ) { $y=(int)date('Y'); $arrYears=array($y-1,$y); }
  if ( !is_array($arrYears) ) die('memFlushStats: arg #1 must be array');
  // Stats are stored by [year][serie][blocktime] using keys:
  // serie: T=topics, R=replies, Z=unreplied and opened, U=users having post, N=type news, C=status closed, ATT=attachments,
  // blocktime: m=per month, q=per quarter, d=last 10 days
  foreach($arrYears as $year) {
  foreach(array('T','R','Z','U','N','C','ATT') as $serie) {
  foreach(array('q','m','d') as $bt) {
  SMem::clear('statD'.$year.$serie.$bt);
  SMem::clear('statS'.$year.$serie.$bt);
  }}}
  return true;
}

// --------
// COMMON FUNCTIONS
// --------

function emptyFloat($i)
{
  // Return true when $i is empty or a value starting with '0.000000'
  if ( empty($i) ) return true;
  if ( !is_string($i) && !is_float($i) && !is_int($i) ) die('emptyFloat: Invalid argument #1, must be a float, int or string');
  if ( substr((string)$i,0,8)==='0.000000' ) return true;
  return false;
}
function renderEmail($emails, string $mode='txt', int $size=0, string $failed='')
{
  // Works recursively on array
  if ( is_array($emails) ) { foreach($emails as $k=>$email) $emails[$k] = renderEmail($email,$mode,$size,$failed); return $emails; }
  // Check input
  if ( !is_string($emails) || empty($emails) ) return $failed;
  if ( strpos($emails,';')!==false ) $emails = str_replace(';', ',', $emails); //comma is recommended as email separator
  $emails = asCleanArray($emails, ','); // array
  if ( !$emails ) return $failed;
  if ( $size && count($emails)>$size ) $emails = array_slice($emails, 0, $size);
  // Render unprotected mailto
  $mailto = implode(',', $emails);
  switch($mode) {
    case 'txt': return '<a href="mailto:'.$mailto.'">'.implode(', ',$emails).'</a>';
    case 'ico':
    case 'img': return '<a href="mailto:'.$mailto.'" title="'.$emails[0].(isset($emails[1]) ? ', ...' : '').'">'.getSVG('envelope').'</a>';
    case 'symbol': return '<a href="mailto:'.$mailto.'" title="'.$emails[0].(isset($emails[1]) ? ', ...' : '').'"><svg class="svg-symbol"><use href="#symbol-envelope" xlink:href="#symbol-envelope"></use></svg></a>';
  }
  // Render reverse-human-readable mailto (javascript converts on mouseover)
  $mailto = strrev(str_replace(['@','.'], ['-at-','-dot-'], $mailto));
  switch($mode) {
    case 'txtjava': return '<script type="text/javascript">const m = "'.$mailto.'"; document.write(`<a href="javascript:void(0)" onmouseover="qtEmailShow(this);" onmouseout="qtEmailHide(this);" data-emails="${m}">${qtDecodeEmails(m)}</a>`);</script>';
    case 'icojava':
    case 'imgjava': return '<a href="javascript:void(0)" onmouseover="qtEmailShow(this);" onmouseout="qtEmailHide(this);" data-emails="'.$mailto.'">'.getSVG('envelope').'</a>';
    case 'symboljava': return '<a href="javascript:void(0)" onmouseover="qtEmailShow(this);" onmouseout="qtEmailHide(this);" data-emails="'.$mailto.'"><svg class="svg-symbol"><use href="#symbol-envelope" xlink:href="#symbol-envelope"></use></svg></a>';
  }
  die('invalid render mode');
}
function asImg(string $src='', string $attr='', string $href='', string $attrurl='', string $imgDflt='alt=S|class=i-sec')
{
  $attr = attrDecode($attr, '|', $imgDflt);
  // no href
  if ( empty($href) ) return '<img src="'.$src.'"'.attrRender($attr).'/>';
  // with href
  return '<a href="'.$href.'"'.attrRender($attrurl).'><img src="'.$src.'"'.attrRender($attr).'/></a>';
}
/**
 * @param string $d
 * @param int $i
 * @param string $str
 * @return string
 */
function addDate(string $d='', int $i=-1, string $str='year')
{
  if ( empty($d) ) die('addDate: Argument #1 must be a string');
  $intY = (int)substr($d,0,4);
  $intM = (int)substr($d,4,2);
  $intD = (int)substr($d,6,2);
  switch($str)
  {
    case 'year': $intY += $i; break;
    case 'month': $intM += $i; break;
    case 'day': $intD += $i; break;
  }
  if ( in_array($intM,array(1,3,5,7,8,10,12)) && $intD>31 ) { $intM++; $intD -= 31; }
  if ( in_array($intM,array(4,6,9,11)) && $intD>30 ) { $intM++; $intD -= 30; }
  if ( $intD<1 ) { $intM--; $intD += 30; }
  if ( $intM>12 ) { $intY++; $intM -= 12; }
  if ( $intM<1 ) { $intY--; $intM += 12; }
  if ( $intM==2 && $intD>28 ) { $intM++; $intD -= 28; }
  return (string)($intY*10000+$intM*100+$intD).(strlen($d)>8 ? substr($d,8) : '');
}
function getSections(string $role='V', int $domain=-1, array $reject=[], string $filter='', string $order='d.titleorder,s.titleorder')
{
  // Returns an array of [key] section id, array of [values] section
  // Use $domain to get section in this domain only
  // $domain=-1 mean in alls domains. -2 means in all domains but grouped by domain
  // Attention: using $domain -2, when a domains does NOT contains sections, this key is NOT existing in the returned list !

  global $oDB;
  $sqlWhere = $domain>=0 ? "s.domainid=$domain" : "s.domainid>=0";
  if ( $role==='V' || $role==='U' ) $sqlWhere .= " AND s.type<>'1'";
  if ( !empty($filter) ) $sqlWhere .= " AND $filter";

  $arrSections = array();
  $oDB->query( "SELECT s.* FROM TABSECTION s INNER JOIN TABDOMAIN d ON s.domainid=d.id WHERE $sqlWhere ORDER BY $order" );
  while($row=$oDB->getRow()) {
    $id = (int)$row['id'];
    // if reject
    if ( in_array($id,$reject,true) ) continue;
    // search translation
    $row['title'] = SLang::translate('sec', 's'.$id, $row['title']);
    // compile sections
    if ( $domain==-2 ) {
    $arrSections[(int)$row['domainid']][$id] = $row;
    } else {
    $arrSections[$id] = $row;
    }
  }
  return $arrSections;
}
function getItemsInfo(CDatabase $oDB)
{
  $arr = array();
  $arr['post'] = $oDB->count( TABPOST );
  $arr['startdate'] = $arr['post']==0 ? '' : qtDatestr( $oDB->count( "SELECT min(firstpostdate) as countid FROM ".TABTOPIC ),'$', '' );
  $arr['topic'] = $oDB->count( TABTOPIC );
  $arr['reply'] = $oDB->count( TABPOST." WHERE type<>'P'" );
  $arr['content'] = L('Message',$arr['post']).' <span  class="small">('.L('Item',$arr['topic']).', '.L('Reply',$arr['reply']).')</span>';
  return $arr;
}
function getUserInfo($ids, string $fields='name', bool $excludezero=true)
{
  return array_shift(getUsersInfo($ids, $fields, $excludezero)); // can return null (if ids not found)
}
function getUsersInfo($ids, string $fields='name', bool $excludezero=true)
{
  // ids can be a int|'A'|'M'|'S'|csv|array
  $where = '';
  if ( is_int($ids) && $ids>=0 ) {
    if ( $excludezero && $ids===0 ) die('getUsersInfo: ids=0');
    $where = "id=$ids";
  } elseif ( $ids==='A' || $ids==='M' ) {
    $where = "role='$ids'";
  } elseif ( $ids==='S' ) {
    $where = "(role='A' OR role='M')";
  } else {
    if ( is_string($ids) ) $ids = explode(',',$ids);
    if ( !is_array($ids) ) die('getUsersInfo: unknown ids type');
    $ids = array_map('intval',$ids); // csv and array are casted as int (non-intval members become 0)
    if ( $excludezero && in_array(0,$ids) ) die('getUsersInfo: ids includes 0');
    $where = 'id IN ('.implode(',',$ids).')';
  }
  if ( empty($where) ) die('getUsersInfo: invalid ids');

  $res = array();
  global $oDB; $oDB->query( "SELECT id,$fields FROM TABUSER WHERE $where" );
  while( $row=$oDB->getRow() ) $res[(int)$row['id']] = $row;
  return $res;
}
function getUsers(string $q='A', string $name='', int $max=100)
{
  // $q={A|S|M|U|N|N*} role admin, admin or moderator, moderator, user or name $name or name starting by $name
  // $max maximum number of results (0 means unlimited)
  global $oDB;
  switch($q)
  {
    case 'A': $where = "role='A'"; break;
    case 'S': $where = "role='A' OR role='M'"; break;
    case 'M': $where = "role='M'"; break;
    case 'U': $where = "role='U'"; break;
    case 'N':
      if ( empty($name) ) die('getUsers: invadid name value');
      $name = qtDb(trim($name));
      $where = "name='$name'";
      break;
    case 'N*':
      if ( empty($name) ) die('getUsers: invadid name value');
      $name = qtDb(trim($name));
      $like = $oDB->type==='pg' ? 'ILIKE' : 'LIKE';
      $where = "name $like '$name%'";
      break;
    default: die('getUser: invalid query');
  }
  $oDB->query( "SELECT id,name FROM TABUSER WHERE $where ORDER BY name" );
  $res = array();
  while ($row=$oDB->getRow())
  {
    $res[(int)$row['id']]=$row['name'];
    if ( --$max===0 ) break; // never breaks when $max starts at 0
  }
  return $res;
}

function validateFile(&$arrFile=[], $extensions='', $mimes='', int $size=0, int $width=0, int $height=0, bool $strictName=true)
{
  // For the uploaded document ($arrFile), this function returns [string] '' if it matches with all conditions
  // and an error message otherwize (and unlink the uploaded document)
  // $arrFile: The uploaded document ($_FILES['fieldname']).
  // $extensions: csv valid extensions. Empty to skip (any extension valid)
  // $mimes: csv valid mimetypes. Empty to skip (any mimetype valid)
  // $size: Maximum file size (kb). 0 to skip.
  // $width: Maximum image width (pixels). 0 to skip.
  // $height: Maximum image width (pixels). 0 to skip.

  // Check arguments
  if ( is_array($extensions) ) $extensions = implode(',', $extensions);
  if ( is_array($mimes) ) $mimes = implode(',', $mimes);
  if ( !is_array($arrFile) || !is_string($extensions) || !is_string($mimes) ) die('CheckUpload: invalid argument type');

  // Check load
  if ( !is_uploaded_file($arrFile['tmp_name']) ) {
    unlink($arrFile['tmp_name']);
    return 'You did not upload a file!';
  }

  // Check size (kb)
  if ( $size>0 && $arrFile['size']>($size*1024+16) ) {
    unlink($arrFile['tmp_name']);
    return L('E_file_size').' (&lt;'.$size.' Kb)';
  }

  // check extension
  if ( !empty($extensions) ) {
    $result = validateFileExt($arrFile['name'], $extensions);
    if ( $result ) {
      unlink($arrFile['tmp_name']);
      return $result;
    }
  }

  // Check mimetype
  if ( !empty($mimes) && strpos(strtolower($mimes),strtolower($arrFile['type']))===false ) {
    unlink($arrFile['tmp_name']);
    return 'Format ['.$arrFile['type'].'] not supported... Use '.$extensions;
  }

  // Check size (pixels)
  if ( $width>0 || $height>0 ) {
    $size = getimagesize($arrFile['tmp_name']);
    if ( $width>0 && $size[0]>$width ) {
      unlink($arrFile['tmp_name']);
      return $width.'x'.$height.' '.L('e_pixels_max');
    }
    if ( $height>0 && $size[1]>$height ) {
      unlink($arrFile['tmp_name']);
      return $width.'x'.$height.' '.L('e_pixels_max');
    }
  }

  if ( $strictName ) {
    $arrFile['name'] = strtolower(qtDropDiacritics($arrFile['name']));
    $arrFile['name'] = preg_replace('/[^a-z0-9_\-\.]/i', '_', $arrFile['name']);
  }

  return '';
}
function validateFileExt($file, $extensions='')
{
  if ( is_array($extensions) ) $extensions = implode(',', $extensions);
  if ( !is_string($file) || empty($file) ) die('validateFileExt: argument #1 must be a string');
  if ( !is_string($extensions) || empty($extensions) ) die('validateFileExt: argument #2 must be a string');
  $file = strtolower($file);
  $extensions = strtolower($extensions);
  $ext = strrpos($file,'.'); if ( $ext===false ) return 'file extension not found';
  $ext = substr($file,$ext+1);
  if ( strpos($extensions,$ext)===false ) return 'Format ['.$ext.'] not supported... Use '.$extensions;
  return '';
}
function makePager(string $uri, int $count, int $intPagesize=50, int $currentpage=1, string $sep='', string $currentclass='current')
{
  // $sep (space) is inserted before each page-number
  if ( $currentpage<1 ) $currentpage=1;
  if ( $intPagesize<5 ) $intPagesize=50;
  if ( $count<2 || $count<=$intPagesize ) return ''; //...
  $arg = qtImplode(qtExplodeUri($uri,['page'])); // extract query part and drop the 'page'-part (arguments remain urlencoded)
  $uri = parse_url($uri, PHP_URL_PATH); // redifine $uri as the path-part only
  $strPages='';
  $firstpage='';
  $lastpage='';
  $top = ceil($count/$intPagesize);
  $arrPages = array(1,2,3,4,5);
  if ( $currentpage>4 ) $arrPages = $currentpage==$top ? array($top-4,$top-3,$top-2,$top-1,$top) : array($currentpage-2,$currentpage-1,$currentpage,$currentpage+1,$currentpage+2);
  // pages
  foreach($arrPages as $page)
  {
    if ( $page>=1 && $page<=$top )
    {
      $first = $page==1 ? ' first' : '';
      $last = $page==$top ? ' last' : '';
      $strPages .= $sep.($currentpage===$page ? '<a class="page '.$currentclass.$first.$last.'" href="javascript:void(0)" tabindex="-1">'.$page.'</a>' : '<a class="page'.$first.$last.'" href="'.$uri.'?'.$arg.'&page='.$page.'">'.$page.'</a>');
    }
  }
  // extreme
  if ( $count>($intPagesize*5) )
  {
    if ( $arrPages[0]>1 ) $firstpage = $sep.'<a class="page first" href="'.$uri.'?'.$arg.'&page=1" title="'.L('First').'">&laquo;</a>';
    if ( $arrPages[4]<$top ) $lastpage = $sep.'<a class="page last" href="'.$uri.'?'.$arg.'&page='.$top.'" title="'.L('Last').': '.$top.'">&raquo;</a>';
  }
  return $firstpage.$strPages.$lastpage;
}
function toCsv($val, string $quote='"',string $quoteAlt="'", string $sep=';', string $null='""')
{
  // Works recursively with an array
  // Note: Value can be null, string, bool, int or float (cannot be an object)
  // Note: null value becomes "" by default, boolean becomes 0|1
  if ( is_array($val) ) {
    $arr = [];
    foreach($val as $v) $arr[] = toCsv($v,$quote,$quoteAlt,$sep,$null);
    return implode($sep,$arr);
  }
  if ( is_int($val) || is_float($val) ) return $val;
  if ( $val==='' ) return $quote.$quote;
  if ( is_bool($val) ) return (int)$val;
  if ( is_null($val) ) return $null;
  if ( !is_string($val) ) die('toCsv: invalid argument');
  $val = str_replace("\r\n",' ',$val);
  if ( strpos($val,'&')!==false ) {
    $val = str_replace('&nbsp;',' ',$val);
    $val = CDatabase::sqlDecode($val);
  }
  $val = str_replace($quote,$quoteAlt,$val);
  return $quote.$val.$quote;
}
function sqlLimit(string $state, string $order='id', int $start=0, int $length=50)
{
  if ( empty($order) ) die('sqlLimit: invalid argument'); // order is required with limit
  global $oDB;
  $order = trim($order); if ( strtolower(substr($order,-3,3))!=='asc' && strtolower(substr($order,-4,4))!=='desc' ) $order .= ' asc';
  switch($oDB->type)
  {
  case 'mysql':
  case 'pdo.mysql': return "SELECT $state ORDER BY $order LIMIT $start,$length"; break;
  case 'sqlsrv':
  case 'pdo.sqlsrv':
    if ( $start==0 ) return "SELECT TOP $length $state ORDER BY $order";
    return "SELECT * FROM (SELECT ROW_NUMBER() OVER (ORDER BY $order) AS rownum, $state) AS orderrows WHERE rownum BETWEEN ".($start+1)." AND ".($start+$length)." ORDER BY rownum )"; break;
  case 'pdo.pg':
  case 'pg': return "SELECT $state ORDER BY $order LIMIT $length OFFSET $start"; break;
  case 'pdo.sqlite':
  case 'sqlite': return "SELECT $state ORDER BY $order LIMIT $length OFFSET $start"; break;
  case 'pdo.oci':
  case 'oci': return ($start==0 ? "SELECT * FROM (SELECT $state ORDER BY $order) WHERE ROWNUM<$length" : "SELECT * FROM (SELECT a.*, rownum rn FROM (SELECT $state ORDER BY $order) a WHERE rownum<$start+1+$length) WHERE rn>=$start"); break;
  default: return "SELECT $state ORDER BY $order LIMIT $start,$length"; break;
  }
}
function sqlFirstChar(string $field, string $case='u', int $len=1)
{
  // returns the whereclause of the $field's first-character(s) being:
  // 'u' uppercase, 'l' lowercase, '~' symbol/digit or '' unchanged (strick case)
  global $oDB;
  switch($oDB->type)
  {
    case 'pdo.sqlsrv':
    case 'sqlsrv':
      if ( $case==='u' ) return "UPPER(LEFT($field,$len))";
      if ( $case==='l' ) return "LOWER(LEFT($field,$len))";
      if ( $case==='~' ) return "(ASCII(UPPER(LEFT($field,1)))<65 OR ASCII(UPPER(LEFT($field,1)))>90)";
      if ( empty($case) ) return "LEFT($field,$len)";
      break;
    case 'pdo.pg':
    case 'pg':
      if ( $case==='u' ) return "UPPER(SUBSTRING($field FROM 1 FOR $len))";
      if ( $case==='l' ) return "LOWER(SUBSTRING($field FROM 1 FOR $len))";
      if ( $case==='~' ) return "UPPER($field) !~ '^[A-Z]'";
      if ( empty($case) ) return "SUBSTRING($field FROM 1 FOR $len)";
      break;
    case 'pdo.sqlite':
    case 'sqlite':
      if ( $case==='u' ) return "UPPER(SUBSTR($field,1,$len))";
      if ( $case==='l' ) return "LOWER(SUBSTR($field,1,$len))";
      if ( $case==='~' ) return "(UPPER(SUBSTR($field,1,1))<'A' OR UPPER(SUBSTR($field,1,1))>'Z')";
      if ( empty($case) ) return "SUBSTR($field,1,$len)";
      break;
    case 'pdo.oci':
    case 'oci':
      if ( $case==='u' ) return "UPPER(SUBSTR($field,1,$len))";
      if ( $case==='l' ) return "LOWER(SUBSTR($field,1,$len))";
      if ( $case==='~' ) return "(ASCII(UPPER(SUBSTR($field,1,1)))<65 OR ASCII(UPPER(SUBSTR($field,1,1)))>90)";
      if ( empty($case) ) return "SUBSTR($field,1,$len)";
      break;
    default:
      if ( $case==='u' ) return "UPPER(LEFT($field,$len))";
      if ( $case==='l' ) return "LOWER(LEFT($field,$len))";
      if ( $case==='~' ) return "UPPER($field) NOT REGEXP '^[A-Z]'";
      if ( empty($case) ) return "LEFT($field,$len)";
      break;
  }
}
/**
 * @param string $date (or 'old')
 * @param string $field
 * @param number $length 8 for yyyymmdd, 4 for year only
 * @param string $oper operator
 * @param string $quote single or double quote (or empty in case of prepared statement)
 * @return string
 */
function sqlDateCondition(string $date='', string $field='firstpostdate', int $length=4, string $oper='=', string $quote="'")
{
  // Creates a where close for a date field. strDate can be an integer or the string 'old' (2 years or more)
  global $oDB;
  if ( $date==='old' ) { $oper = '<='; $date = Date('Y')-2; }
  switch($oDB->type) {
  case 'pdo.pg':
  case 'pg': return 'SUBSTRING('.$field.' FROM 1 FOR '.$length.')'.$oper.$quote.$date.$quote; break;
  case 'pdo.sqlite':
  case 'sqlite':
  case 'pdo.oci':
  case 'oci': return 'SUBSTR('.$field.',1,'.$length.')'.$oper.$quote.$date.$quote; break;
  default: return 'LEFT('.$field.','.$length.')'.$oper.$quote.$date.$quote;
  }
}
function postsTodayAcceptable(int $intMax=100)
{
  if ( SUser::isStaff() || SUser::getInfo('numpost',0)<$intMax ) return true;
  // count if not yet defined
  if ( !isset($_SESSION[QT.'_usr']['posts_today']) ) {
    global $oDB;
    $_SESSION[QT.'_usr']['posts_today'] = $oDB->count( TABPOST." WHERE userid=".SUser::id()." AND ".sqlDateCondition(date('Ymd'),'issuedate',8) );
  }
  if ( $_SESSION[QT.'_usr']['posts_today']===false || $_SESSION[QT.'_usr']['posts_today']<$intMax ) return true;
  return false;
}