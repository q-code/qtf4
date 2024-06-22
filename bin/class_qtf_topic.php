<?php // v4.0 build:20240210

class CTopic extends AContainer
{

// AContainer properties: id,pid,title,descr[csv-tags],type,status,items
// Note: items are number of replies (first post is part of the topic)

public $numid = -1;
public $statusdate = '0'; // last status change date
public $firstpostid = -1;
public $lastpostid = -1;
public $firstpostuser = -1;
public $lastpostuser = -1;
public $firstpostname;
public $lastpostname;
public $firstpostdate = '0';
public $lastpostdate = '0';
public $views = 0;
public $youreply = '&nbsp;';
public $smile = '00';
public $attachinfo;
public $preview = '';

function __construct($ref=null, int $userid=-1)
{
  $this->setFrom($ref);
  // Check default values for AContainer properties
  if ( empty($this->type) ) $this->type = 'T'; // topic
  if ( empty($this->status) ) $this->status = '0'; // open
}
private function setFrom($ref=null)
{
  // $ref can be [null|int|array|obj-class], otherwhise die
  if ( $ref===null || $ref===-1 ) return; // exit with void-instance (default properties)
  if ( is_int($ref) ) {
    if ( $ref<0 ) die(__METHOD__.' Argument must be positive');
    global $oDB;
    $oDB->query( "SELECT * FROM TABTOPIC WHERE id=$ref" );
    $row = $oDB->getRow(); if ( $row===false ) die(__METHOD__.' No id '.$ref);
    $ref = $row; // continue as array
  }
  if ( is_array($ref) ) {
    foreach($ref as $k=>$value) {
      switch((string)$k)
      {
        case 'preview':
        case 'textmsg':      $this->preview      = $_SESSION[QT]['item_firstline']>0 ? qtInline(qtDb($value),QT_FIRSTLINE_SIZE) : ''; break;
        case 'id':           $this->id           = (int)$value; break;
        case 'numid':        $this->numid        = (int)$value; break;
        case 'pid':
        case 'forum':        $this->pid          = (int)$value; break;
        case 'type':         $this->type         = (string)$value; break;
        case 'status':       $this->status       = (string)$value; break;
        case 'statusdate':   $this->statusdate   = (string)$value; break;
        case 'tags':
        case 'descr':        $this->descr        = qtDb((string)$value); break;
        case 'firstpostid':  $this->firstpostid  = (int)$value; break;
        case 'lastpostid':   $this->lastpostid   = (int)$value; break;
        case 'firstpostuser':$this->firstpostuser= (int)$value; break;
        case 'lastpostuser': $this->lastpostuser = (int)$value; break;
        case 'firstpostname':$this->firstpostname= qtDb((string)$value); break;
        case 'lastpostname': $this->lastpostname = qtDb((string)$value); break;
        case 'firstpostdate':$this->firstpostdate= (string)$value; break;
        case 'lastpostdate': $this->lastpostdate = (string)$value; break;
        case 'replies':
        case 'items':        $this->items        = (int)$value; break;
        case 'views':        $this->views        = (int)$value; break;
        case 'youreply':     $this->youreply     = (string)$value; break;
        case 'icon':         $this->smile        = (string)$value; break;
        case 'title':        $this->title        = qtDb((string)$value); break;
        case 'attach':       $this->attachinfo   = (string)$value; break;
      } // Unit test: $k must be [string] otherwhise key 0 can change the first case (0=='id')
    }
    return; //█
  }
  if ( is_a($ref,'CTopic') ) return $this->setFrom(get_object_vars($ref)); //█
  die(__METHOD__.' Invalid argument type' );
}
public function viewsIncrement(int $userid=-1) {
  // +1 when user is not the creator himself
  // Method is not called in __construct, but is called by the display page (after page access is granted)
  if ( $userid>=0 && $userid!=$this->firstpostuser ) {
    try {
      global $oDB; $oDB->exec( "UPDATE TABTOPIC SET views=views+1 WHERE id=$this->id" ); }
    catch (Exception $e) {
      global $oH; $oH->error = '<p class="debug red"><strong>Database error</strong>: '.$e->getMessage().'</p>';
    }
  }
}

/**
 * True when number of replies greater than QT_HOTTOPICS
 * @param number $replies
 * @return boolean (false if QT_HOTTPICS is not defined, false or 0)
 */
public static function isHot($replies=0)
{
  if ( defined('QT_HOTTOPICS') && QT_HOTTOPICS && $replies>QT_HOTTOPICS ) return true;
  return false;
}
public static function getStatuses()
{
  return array(0=>L('Opened'),1=>L('Closed'));
}
public static function getStatus($id, string $alt='unknown')
{
  $arr = self::getStatuses();
  return isset($arr[$id]) ? $arr[$id] : $alt;
}
public static function getTypes()
{
  return array('T'=>L('Item'),'A'=>L('News'));
}
public static function getType($id, string $alt='unknown')
{
  $arr = self::getTypes();
  return isset($arr[$id]) ? $arr[$id] : $alt;
}
public static function getOwner(int $id) {
  global $oDB;
  $oDB->query( "SELECT firstpostuser FROM TABTOPIC WHERE id=$id" );
  $row=$oDB->getRow();
  return (int)$row['firstpostuser'];
}
public static function getRef(int $numid=0, $format='', string $none='&nbsp;')
{
  // This returns the formatted ref number (numid) of this item.
  // Format can be defined by a string, a [int] section-id, or a [CSection] section.
  // In case of undefined format, this returns the numid (as '%03s' string), in case of 'N' format, return the $na string.
  if ( is_a($format,'CSection') ) $format = $format->numfield;
  if ( is_int($format) ) { $arr = SMem::get('_Sections'); if ( isset($arr[$format]['numfield']) ) $format = empty($arr[$format]['numfield']) ? '%03s' : $arr[$format]['numfield']; }
  if ( !is_string($format) ) $format = '%03s';
  if ( $format==='N' ) return $none;
  return empty($format) ? (string)$numid : sprintf($format,$numid);
}
function getIcon(string $skin='skin/default/', string $strurl='', string $strTitleFormat='%s')
{
  $type = strtolower($this->type);
  $status = strtolower($this->status);
  return asImg( $skin.'img/topic_'.$type.'_'.$status.'.gif', 'class=i-item|data-type='.$type.'|data-status='.$status.'|alt='.strtoupper($type).'|title='.sprintf($strTitleFormat,$this->getIconName()), $strurl);
}
function getIconName()
{
  return L('Ico_item_'.strtolower($this->type).'_'.strtolower($this->status));
}
function getTagIcon()
{
  if ( empty($this->descr) ) return '';
  $arr = explode(';',$this->descr);
  return qtSvg('tag'.(count($arr)>1 ? 's' : ''), 'title='.implode(',',$arr));
}
function getTopicTitle()
{
  global $oDB;
  $oDB->query( "SELECT title FROM TABPOST WHERE id=".$this->firstpostid );
  $row = $oDB->getRow();
  $this->title = $row['title'];
  return $this->title;
}
function setStatus(string $status='0')
{
  // attention: status '1' means 'closed' (opened='0')
  if ( $this->status==$status ) return false;
  $this->status=$status;
  $this->statusdate = date('Ymd His');
  global $oDB;
  $oDB->exec( "UPDATE TABTOPIC SET status=?,statusdate=? WHERE id=".$this->id, [$this->status,$this->statusdate] );
}
function setType(string $type='T')
{
  if ( $this->type===$type ) return false;
  $this->type = $type;
  global $oDB;
  $oDB->exec( "UPDATE TABTOPIC SET type=? WHERE id=".$this->id, [$this->type] );
}

/** Returns a csv-string with unique, QT_LOWERCASE_TAG, trimmed, no-accent, not-empty tags */
public static function tagsClear(string $str, bool $dropDiacritics=true, bool $commaAsSep=true)
{
  $str = qtAttr($str); // trim and no doublequote
  if ( $str==='*' ) return '*'; // delete all tags
  if ( empty($str) ) return '';
  if ( $dropDiacritics ) $str = qtDropDiacritics($str);
  if ( QT_LOWERCASE_TAG ) $str = strtolower($str);
  if ( $commaAsSep && strpos($str,',')!==false ) $str = str_replace(',',';',$str);
  return implode(';',qtCleanArray($str));
}
/** Change tags and (optionally) updates section stats */
public function tagsUpdate(CSection $oS=null)
{
  if ( substr($this->descr,-1)===';' ) $this->descr = substr($this->descr,0,-1);
  global $oDB;
  $oDB->exec( "UPDATE TABTOPIC SET tags=?,modifdate=? WHERE id=".$this->id, [qtAttr($this->descr),date('Ymd His')] ); // no doublequote
  // Update section stats
  if ( is_null($oS) ) return; //█
  global $oDB;
  $stats = qtExplode($oS->stats,';'); unset($stats['tags']); // unset to recompute
  $oS->updStats($stats);
}
public function tagsAdd(string $str, CSection $oS=null)
{
  // Check and format
  $str = self::tagsClear($str); // Can return '' or '*'.
  if ( empty($str) || $str==='*' ) return false;
  // Append to current and clear (to remove duplicate)
  $this->descr = self::tagsClear($this->descr.';'.$str);
  // Save
  $this->tagsUpdate($oS);
}
public function tagsDel(string $str, CSection $oS=null)
{
  if  ( empty($this->descr) || empty($str) ) return false;
  // Check and format
  $str = self::tagsClear($str); // distinct tags (lowercased by config). Can return '' or '*'.
  if ( empty($str) ) return false;
  // Build new tags list
  $this->descr = $str==='*' ? '' : implode(';', array_diff(explode(';',$this->descr), explode(';',$str)));
  // Save
  $this->tagsUpdate($oS);
}

/**
 * Delete the topic, replies and attachements (can work on several topics)
 * @param integer|array $ids the topic id or a list of id
 * @param boolean $dropAttachs
 * @return integer the number of topics affected
 */
public static function delete($ids, bool $dropAttachs=true) {
  if ( is_int($ids) ) $ids = array($ids);
  if ( !is_array($ids) ) die('CTopic::delete arg #1 must be an array');
  $i = count($ids);
  $ids = implode(',',$ids);
  if ( $dropAttachs ) CPost::dropAttachSQL( "SELECT id,attach FROM TABPOST WHERE attach<>'' AND topic IN ($ids)", false ); // Warning dropAttach of the replies in topics ids
  global $oDB;
  $oDB->exec( "DELETE FROM TABPOST WHERE topic IN ($ids)" );
  $oDB->exec( "DELETE FROM TABTOPIC WHERE id IN ($ids)" );
  return $i;
}
/**
 * Delete reply-posts in the topic $ids (can work on several topics)
 * @param integer|array $ids the topic id or a list of id
 * @param boolean $dropAttachs
 * @return integer the number of posts affected
 */
public static function deleteReplies($ids, bool $dropAttachs=true) {
  if ( is_int($ids) ) $ids = array($ids);
  if ( !is_array($ids) ) die('CTopic::deleteReplies Invalid argument');
  $i = count($ids);
  global $oDB;
  $ids = implode(',',$ids);
  if ( $dropAttachs ) CPost::dropAttachSql( "SELECT id,attach FROM TABPOST WHERE attach<>'' AND type<>'P' AND topic IN ($ids)", false );
  $oDB->exec( "DELETE FROM TABPOST WHERE type<>'P' AND topic IN ($ids)" );
  return $i;
}
/**
 * Update replies-count and lastpost data
 * @param number $intMax above this value, the topic is closed (0 to skipp)
 */
public function updMetadata(int $intMax=0)
{
  if ( $this->id<0 ) die('CTopic::updMetadata Wrong id');

  // Count
  global $oDB;
  $arr = [];
  $this->items = 0;
  $oDB->query( "SELECT id,userid,username,issuedate,type FROM TABPOST WHERE topic=$this->id ORDER BY issuedate" );
  while($row=$oDB->getRow()) {
    $arr[]=$row;
    if ( $row['type']!=='P' ) ++$this->items;
  }

  // save stats
  $i = count($arr)-1; // $arr 0=firstmessage, $i=lastmessage
  $oDB->exec( "UPDATE TABTOPIC SET replies=?,firstpostid=?,firstpostuser=?,firstpostname=?,firstpostdate=?,lastpostid=?,lastpostuser=?,lastpostname=?,lastpostdate=? WHERE id=$this->id",
  [
  $this->items,
  $arr[0]['id'],
  $arr[0]['userid'],
  $arr[0]['username'],
  $arr[0]['issuedate'],
  $arr[$i]['id'],
  $arr[$i]['userid'],
  $arr[$i]['username'],
  $arr[$i]['issuedate']
  ] );

  // close topic if full
  if ( $intMax>1 && $this->items>$intMax ) $oDB->exec( "UPDATE TABTOPIC SET status='1' WHERE id=$this->id" );
}
public function insertTopic(bool $userStat=true)
{
  if ( empty($this->firstpostdate) ) $this->firstpostdate = date('Ymd His');
  if ( empty($this->lastpostdate) ) $this->lastpostdate = $this->firstpostdate;
  global $oDB;
  $oDB->exec(
  "INSERT INTO TABTOPIC (id,forum,numid,type,status,statusdate,tags,firstpostid,lastpostid,firstpostuser,lastpostuser,firstpostname,lastpostname,firstpostdate,lastpostdate,replies)
  VALUES ($this->id,$this->pid,$this->numid,?,?,?,?,?,?,?,?,?,?,?,?,$this->items)",
  [
  $this->type,
  $this->status,
  $this->statusdate,
  $this->descr,
  $this->firstpostid,
  $this->lastpostid,
  $this->firstpostuser,
  $this->lastpostuser,
  qtDb($this->firstpostname),
  qtDb($this->lastpostname),
  $this->firstpostdate,
  $this->lastpostdate
  ]
  );

  // update user stats
  if ( $userStat ) {
    if ( isset($_SESSION['qtf_usr_posts']) ) $_SESSION['qtf_usr_posts']++;
    $n = $oDB->count( TABPOST." WHERE userid=".$this->firstpostuser );
    $oDB->exec( "UPDATE TABUSER SET lastdate=?, numpost=$n, ip=? WHERE id=$this->firstpostuser", [date('Ymd His'), $_SERVER['REMOTE_ADDR']] );
    $_SESSION[QT.'_usr']['items']++;
  }
}

}