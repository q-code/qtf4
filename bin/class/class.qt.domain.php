<?php // v4.0 build:20240210

/**
 * CDomain (top container) extends AContainer implement IContainer methods. A domain is a group of sections.
 * @see AContainer
 * @see IContainer
 * @author qt-cute.org
 */
class CDomain extends AContainer implements IContainer
{
  // AContainer properties: id,pid,title,descr,type,status,items
  // NOTE: descr and items are not used by CDomain
  public function __construct($ref=null, $translate=false)
  {
    $this->setFrom($ref);
    if ( $translate) $this->title = SLang::translate('domain', 'd'.$this->id, $this->title);
  }
  public function setFrom($ref=null)
  {
    // $ref can be [null|int|array|obj-class], otherwhise die
    if ( $ref===null ) return; //... exit with void-instance (default properties)
    if ( is_int($ref) ) {
      if ( $ref<0 ) die(__METHOD__.' Argument must be positive');
      global $oDB;
      $oDB->query( "SELECT * FROM TABDOMAIN WHERE id=$ref" );
      $row = $oDB->getRow(); if ( $row===false ) die(__METHOD__.' No id '.$ref);
      $ref = $row; // continue as array
    }
    if ( is_array($ref) ) {
      foreach($ref as $k=>$value) {
        switch((string)$k)
        {
        case 'id':     $this->id     = (int)$value; break;
        case 'pid':    $this->pid    = (int)$value; break;
        case 'title':  $this->title  = $value; if ( empty($this->title) ) $this->title = 'domain-'.$this->id; break;
        case 'descr':  $this->descr  = $value; break; // not used
        case 'type':   $this->type   = $value; break;
        case 'status': $this->status = $value; break;
        case 'items':  $this->items  = (int)$value; break; // not used
        } // Unit test: $k must be [string] otherwhise key 0 can change the first case (0=='id')
      }
      return; //...
    }
    if ( is_a($ref,'CDomain') ) return $this->setFrom(get_object_vars($ref)); //...
    die(__METHOD__.' Invalid argument type');
  }
  public static function create(string $title='untitled', int $pid=-1, bool $uniquetitle=true)
  {
    $title = qtDb($title);
    if ( empty($title) ) throw new Exception( L('Name').' '.L('invalid') );
    global $oDB;
    // unique title
    if ( $uniquetitle && $oDB->count( TABDOMAIN." WHERE title=?", [$title] )>0 ) throw new Exception( L('Name').' '.L('already_used') );
    // create
    $id = $oDB->nextId(TABDOMAIN);
    $oDB->exec( "INSERT INTO TABDOMAIN (id,title,titleorder) VALUES ($id,?,0)", [$title] );
    // clear cache
    SMem::clear('_Domains');
    // $pid not used as domain is top level (no parent)
    return $id;
  }
  public static function translate(int $id)
  {
    // returns translated title (from session memory), uses config name if no translation
    return SLang::translate('domain', 'd'.$id, empty($GLOBALS['_Domains'][$id]['title']) ? '' : $GLOBALS['_Domains'][$id]['title']); //
  }
  public static function delete(int $id)
  {
    if ( $id<1 ) die('CDomain::delete domain 0 cannot be deleted');
    global $oDB;
    // check is empty
    if ( $oDB->count( TABSECTION." WHERE domainid=$id" )>0 ) throw new Exception( 'Cannot delete a domain containing sections' );
    $oDB->exec( "UPDATE TABSECTION SET domainid=0 WHERE domainid=$id" ); // sections return to domain 0
    $oDB->exec( "DELETE FROM TABDOMAIN WHERE id=$id" );
    SLang::delete('domain','d'.$id);
    // clear cache
    SMem::clear('_Domains');
  }
  public static function rename(int $id, string $title='untitled', bool $uniquetitle=true)
  {
    if ( $id<0 ) die('CDomain::rename arg #1 must be positive');
    if ( empty($title) ) die('CDomain::rename arg #2 must be a string');
    $title = qtDb($title);
    global $oDB;
    // unique title
    if ( $uniquetitle && $oDB->count( TABDOMAIN." WHERE title=?", [$title] )>0 ) throw new Exception( L('Name').' '.L('already_used') );
    $oDB->exec( "UPDATE TABDOMAIN SET title=? WHERE id=?", [$title,$id] );
    // clear cache
    SMem::clear('_Domains');
  }
  public static function getTitles(array $arrReject=[], bool $translate=true, string $order='titleorder') {
    global $oDB;
    $oDB->query( "SELECT id,title FROM TABDOMAIN ORDER BY $order");
    while($row=$oDB->getRow()) {
      $id = (int)$row['id']; if ( in_array($id,$arrReject,true) ) continue;
      $arr[$id] = $translate ? SLang::translate('domain','d'.$id,$row['title']) : $row['title'];
    }
    return $arr;
  }
  public static function getOwner(int $id) {
    if ( $id<0 ) die('AContainer::getOwner Invalid argument');
    return 1; // top level container. The owner is the first administrator
  }
  public static function moveSections(int $id=-1, int $dest=-1)
  {
    if ( $id<0 || $dest<0 || $id===$dest ) die('CDomain::moveSections: invalid arguments');
    global $oDB;
    $oDB->exec( "UPDATE TABSECTION SET domainid=$dest WHERE domainid=$id" );
    // clear cache
    SMem::clear('_Sections');
  }
  public static function getProperties(string $order='titleorder')
  {
    // Returns an array of all [CDomain] object-properties
    $arr = [];
    $oDB = new CDatabase();
    $oDB->query( "SELECT * FROM TABDOMAIN ORDER BY ".$order );
    while($row=$oDB->getRow()) {
      $oDom = new CDomain($row); // titles are not translated
      $arr[$oDom->id] = (array)$oDom; // object-properties as ARRAY
    }
    return $arr;
  }
  public static function get_pSectionsVisible(int $pid)
  {
    // list visible sections in memory '_Sections' (hide type 1 section for non-staff)
    if ( !isset($GLOBALS['_Sections']) ) die('CDomain::get_pSectionsVisible missing _Sections');
    if ( $pid<0 ) die('CDomain::get_pSectionsVisible arg #1 must be a integer');
    $arr = [];
    foreach($GLOBALS['_Sections'] as $mId=>$mSec) {
      if ( $mSec['pid']!=$pid ) continue;
      if ( $mSec['type']==='1' && !SUser::isStaff() ) continue;
      $arr[$mId] = $mSec;
    }
    return $arr;
  }

}