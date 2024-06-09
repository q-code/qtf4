<?php

/*****
 * Name    : TabTable
 * Content : TabTable is a package of 5 classes (TabItem, TabHead, TabData and TabTable)
 * version : 2.0 (replace CTable v1.6)
 * Date    : 12/03/2022
 * Author  : qt-cute.org
 * Abstract: These classes allow creating table and populating cells and cells headers in an easy way.
 *           It also supports header hyperlinks management option (allowing to build a change order mechanism).
 *           It makes possible to define attributes with a single compacted-string
 *
 * Package contains:
 * TabItem class  - Base properties and methods avalaible in all classes
 * TabData class  - TabItem having a content string (td, caption)
 * TabHead class  - TabItem having a content string and link management (th)
 * TabTable class - TabItem having multiple other properties and methods (table)
 *
 * This TabTable class is designed to work sequentially, row by row,
 * with methods setTDtag() or setTDcontent() that creates one row of several columns.
 * Other methods allow applying attribute(s) to all columns at once (or to specific columns)
 * The method getTHrow() or getTDrow() allows outputing the <tr> row
 * Attributes can be an array [attribute=>value] or a compacted-string format ex: 'title=My title|colspan=2|hidden|width=125px'
 *
 * See also .txt file to use group tags (tbody|thead|tfoot) or caption and to use hyperlink management for the hearders
 */

class TabItem
{
  public $tag = ''; // html tag (always in lowercase)
  public $attr = []; // array of attribute=>value (constructor can generate this array from a compacted-string)
  public $eolc = false; // add PHP_EOL after the closing tag
  public $eolo = false; // add PHP_EOL after the opening tag
  public function __construct(string $tag='', $attr=[]) {
    $this->tag = strtolower($tag); if ( !in_array($this->tag,['table','caption','tr','td','th','tbody','thead','tfoot']) ) die(__METHOD__.' unsupported entity '.$this->tag);
    // Add attributes
    if ( is_string($attr) ) $attr = self::attrDecode($attr); // support for compacted-string
    if ( !is_array($attr)) die(__METHOD__.' must be an array or a compacted-string');
    foreach($attr as $key=>$value) $this->set((string)$key, (string)$value);
  }
  public function set(string $key='', string $value='') {
    if ( empty($key) ) die(__METHOD__.' invalid argument');
    $this->attr[$key] = $value;
  }
  public function append(string $key='', string $value='', string $sep=' ', bool $unique=false) {
    if ( empty($key) ) die(__METHOD__.' invalid argument');
    if ( !isset($this->attr[$key]) ) { $this->set($key,$value); return; } // create attribute if not yet existing
    if ( $unique && strpos($this->attr[$key],$value)!==false ) return; // skip if value already in
    $this->attr[$key] .= (strlen($this->attr[$key])===0 ? '' : $sep).$value;
  }
  public function remove(string $key='') {
    if ( empty($key) ) die(__METHOD__.' invalid argument');
    if ( !isset($this->attr[$key]) ) return; // attribute not existing
    unset($this->attr[$key]);
  }
  public function setAttributes($attr=[]) {
    if ( is_string($attr) ) $attr = self::attrDecode($attr); // support for compacted-string
    if ( !is_array($attr)) die(__METHOD__.' must be an array');
    foreach($attr as $key=>$value) $this->set($key,$value);
  }
  public function unsetAttributes($attr=[]) {
    if ( is_string($attr) ) $attr = self::attrDecode($attr); // support for compacted-string
    if ( !is_array($attr)) die(__METHOD__.' must be an array');
    foreach(array_keys($attr) as $key) $this->remove($key);
  }
  public function start() { return '<'.$this->tag.self::attrRender($this->attr).'>'.($this->eolo ? PHP_EOL : ''); }
  public function end() { return '</'.$this->tag.'>'.($this->eolc ? PHP_EOL : ''); }
  private static function attrDecode(string $str, string $sep='|') {
    // Explode a compacted-string 'x1=y1|x2=y2|x3' into an array of attribute value [x1=>y1,...]
    // Values are un-quoted. Attributes are lowercase. An attribute without value is possible (value is null)
    // Note: For an unformatted $str, the array [0=>$str] is returned
    if ( empty($str) ) return [];
    if ( substr_count($str,$sep)===0 && substr_count($str,'=')>1 ) return [$str]; // check if $str is compacted
    $attr = [];
    foreach(qtCleanArray($str,$sep) as $str) {
      $a = array_map('trim',explode('=',$str,2)); // cut on first '=' only
      if ( !isset($a[1]) || $a[1]==='' || $a[1]==='"' || $a[1]==='""' ) $a[1] = null; // support for attribute without value
      if ( isset($a[1]) ) {
        // remove first and last quote
        if ( substr($a[1],0,1)==='"' ) $a[1] = substr($a[1],1);
        if ( substr($a[1],-1,1)==='"' ) $a[1] = substr($a[1],0,-1);
      }
      $attr[$a[0]] = $a[1];
    }
    return array_change_key_case($attr); // W3C recommends attribute-names in lowercase, strict XHTML requires lowercase
  }
  private static function attrRender(array $attr=[]) {
    $str = '';
    foreach ($attr as $key=>$value) $str .= ' '.$key.'="'.str_replace('"','\"',$value).'"';
    return $str;
  }
}

class TabData extends TabItem
{
  public $content;
  public function __construct(string $content='', $attr=[], string $tag='td') {
    if ( !in_array($tag,['td','caption']) ) die(__METHOD__.' invalid entity '.$tag);
    parent::__construct($tag,$attr);
    $this->content = $content;
  }
  public function get(bool $skipEmptyContent=true) {
    if ( $skipEmptyContent && $this->content==='' ) return '';
    return $this->start().$this->content.$this->end();
  }
}

class TabHead extends TabItem
{
  public $content = ''; // [string]  Content of the <th></th> entity
  public $link = '';    // [string]  Pattern to apply to $content (e.g. '<a href="your-url">%s</a>'). If $link=='', the initial $content will be used.
  public function __construct(string $content='', $attr=[], string $link='') {
    parent::__construct('th',$attr);
    $this->content = $content;
    $this->link = $link;
  }
}

class TabTable extends TabItem
{
  public $caption;             // [TabData]          [optional] one <caption> entity. Can be created using caption()
  public $row;                 // [TabItem]          [mandatory] one <tr> entity. All get/set methods creates this <tr>
  public $arrTh = [];          // [array of TabHead] List of <th> entities. Note, array-key is used to identify the column in the advanced methods. The key can be a name or a column number
  public $arrTd = [];          // [array of TabData] List of <td> entities. Note, array-key is used to identify the column in the advanced methods. The key can be a name or a column number
  public $countDataRows = 0;   // [integer]          Number of data rows (rows containing <td>).
  public $minimumDataRows = 2; // [integer]          Minimum number of rows to apply the $actvielink content pattern to the active column header
  public $activecol = '';      // [string|integer]   Current active column (i.e. key from $arrTh). This column header will use the content pattern $activelink
  public $activelink = '';     // [string]           Content pattern to apply to the active column header (if $countDataRows>$minimumDataRows)
  public $thead,$tbody,$tfoot; // [TabItem]          optional <tbody|tfoot|thead> tag.

  /**
   * @param string|array $attr
   * @param int $countDataRows number of td rows
   * @param int $minimumDataRows number of td rows to trigger th link changes
   */
  public function __construct($attr=[], int $countDataRows=0, int $minimumDataRows=2) {
    parent::__construct('table',$attr);
    $this->countDataRows = $countDataRows;
    $this->minimumDataRows = $minimumDataRows;
    $this->eolc = true;
    $this->eolo = true;
  }

  /**
   * Starts the table and add the caption if defined
   * {@inheritDoc}
   * @see TabItem::start()
   */
  public function start() {
    return parent::start() . (empty($this->caption) ? '' : $this->caption->get());
  }
  /**
   * Ends the table and optionally unset the contents
   * {@inheritDoc}
   * @see TabItem::end()
   */
  public function end(bool $unsetData=false, bool $unsetHead=false, bool $unsetRow=false) {
    // allow resetting properties before using parent TabItem::end
    if ( $unsetData )  $this->arrTd = [];   // removes the <td> cells
    if ( $unsetHead )  $this->arrTh = [];   // removes the <th> cells
    if ( $unsetRow )   $this->row = null;   // removes the default <tr> row
    return parent::end();
  }

  // extra entities inside table
  public function thead($attr=[]) { $this->thead = new TabItem('thead',$attr); $this->thead->eolc = true; $this->thead->eolo = true; }
  public function tbody($attr=[]) { $this->tbody = new TabItem('tbody',$attr); $this->tbody->eolc = true; $this->tbody->eolo = true; }
  public function tfoot($attr=[]) { $this->tfoot = new TabItem('tfoot',$attr); $this->tfoot->eolc = true; $this->tfoot->eolo = true; }
  public function caption(string $content='', $attr=[]) { $this->caption = new TabData($content,$attr,'caption'); $this->caption->eolc = true; }

  public function getTHrow($attr=[]) { return $this->getRow($this->arrTh,$attr,'th'); }
  public function getTDrow($attr=[]) { return $this->getRow($this->arrTd,$attr,'td'); }
  public function getTHnames() {
    $arr = [];
    foreach($this->arrTh as $key=>$objTh) $arr[$key] = $objTh->content;
    return $arr;
  }
  public function getTable() {
    // Simple table (support only th and td rows, without thead|tbody|tfoot)
    $str  = $this->start();
    $str .= $this->getTHrow();
    $str .= $this->getTDrow();
    $str .= $this->end();
    return $str;
  }
  public function getEmptyTable(string $content='No data...', bool $showHeaders=false, $attr=[]) {
    // Single row table (support only th and td rows, without thead|tbody|tfoot)
    // $attr are the attributes of the <td>
    // The <tr> tag uses the current row attributes (or create a <tr> entity without attributes)
    $cols = 1; // total number of th columns use as td colspan
    $row = '';
    // th
    if ( $showHeaders ) {
      $i = $this->countDataRows; // ensure that countDataRows is null to disable the header links (if any)
      $this->countDataRows = 0;
      if ( empty($this->arrTh) ) $this->arrTh[] = new TabHead();
      $row .= empty($this->thead) ? '' : $this->thead->start();
      $row .= $this->getTHrow();
      $row .= empty($this->thead) ? '' : $this->thead->end();
      $this->countDataRows = $i;
      $cols = count($this->arrTh);
    }
    // td, single cell containing $content
    $objTd = new TabData($content,$attr); if ( $cols>1 ) $objTd->set('colspan',$cols);
    $this->arrTd = [$objTd];
    $row .= empty($this->tbody) ? '' : $this->tbody->start();
    $row .= $this->getTDrow();
    $row .= empty($this->tbody) ? '' : $this->tbody->end();

    // build table
    return $this->start().$row.$this->end();
  }

  // Advanced methods: allows creating/changing all columns at once (tag, inner-content or an attribute)
  // Tips: When $values is a string, change is apply to all columns
  //       When $values is an array, change is apply to specific columns (the array index indicates the column)
  public function setTHtag($values=[], bool $createCol=true, bool $namedCol=true) { $this->make('arrTh','[tag]',$values,$createCol,$namedCol); }
  public function setTDtag($values=[], bool $createCol=true, bool $namedCol=true) { $this->make('arrTd','[tag]',$values,$createCol,$namedCol); }
  public function setTHcontent($values=[], bool $createCol=true, bool $namedCol=true) { $this->make('arrTh','[content]',$values,$createCol,$namedCol); }
  public function setTDcontent($values=[], bool $createCol=true, bool $namedCol=true) { $this->make('arrTd','[content]',$values,$createCol,$namedCol); }
  public function setTHattr(string $attr, $values=[], bool $createCol=true, bool $namedCol=true) { $this->make('arrTh',$attr,$values,$createCol,$namedCol); }
  public function setTDattr(string $attr, $values=[], bool $createCol=true, bool $namedCol=true) { $this->make('arrTd',$attr,$values,$createCol,$namedCol); }

  // About functions setTHcompactAttr() and setTDcompcatAttr()
  // They can use ONE compacted-string (to apply the attributes to each column)
  // They can also use an ARRAY of compacted-strings (to apply specific attributes to specific column).
  // Tips #1: When working with an ARRAY of compacted-strings, if the columns were created with named-index, you must use the same index for your array.
  //          If the columns were created without index, the attributes are added in sequential order (no need to declare indexes in you array)
  // Tips #2: You can apply several time the function setTDcompactAttr() in sequence,
  //          for example, first with ONE compacted-string to apply attributes to all columns,
  //          then with an ARRAY indicating some specific columns where attributes must be changed or added.
  public function setTHcompactAttr($values='', bool $namedCol=true) { $this->make('arrTh','[attr]',$values,false,$namedCol); }
  public function setTDcompactAttr($values='', bool $namedCol=true) { $this->make('arrTd','[attr]',$values,false,$namedCol); }

  // tools
  public function cloneThTd(bool $withContent=false, string $withAttr='class') {
    // Clone each th to create new td {$this->arrTd}
    // use option $withAttr={'*'|''|'class'} to copy all attributes, none, or one attribute
    foreach(array_keys($this->arrTh) as $k) {
      $attr = [];
      if ( $withAttr==='*' ) {
        $attr = $this->arrTh[$k]->attr;
      } elseif ( isset($this->arrTh[$k]->attr[$withAttr]) ) {
        $attr[$withAttr] = $this->arrTh[$k]->attr[$withAttr]; // key is required
      }
      $this->arrTd[$k] = new TabData($withContent ? $this->arrTh[$k]->content : '', $attr);
    }
  }

  private function setRow($attr=[]) { $this->row = new TabItem('tr',$attr); $this->row->eolc = true; }
  private function getRow(array $arrObjTab=[], $attr=[], string $dfltTag='td') {
    // If not yet defined, the method create a new TabItem <tr> object.
    // Attention, if $row is alreay set, $attr are not used
    if ( !isset($this->row) ) $this->setRow($attr);
    // initialize
    $str='';
    $strVoid = $dfltTag==='th' ? '<th></th>' : '<td></td>';
    // columns
    foreach($arrObjTab as $key=>$objTab)  {
      if ( is_a($objTab,'TabHead') ) {
        /* @var TabHead $objTab */
        if ( $this->activecol===$key && $this->countDataRows>$this->minimumDataRows && $objTab->link!=='' )
        {
          $objTab->link = $this->activelink; // replace link for the active column

        }
        // build column
        $str .= $objTab->start();
        $str .= ($this->countDataRows>$this->minimumDataRows && $objTab->link!=='' ? str_replace('%s',$objTab->content,$objTab->link) : $objTab->content);
        $str .= $objTab->end();
      }
      if ( is_a($objTab,'TabData') ) {
        /* @var TabData $objTab */
        $str .= $objTab->start() . $objTab->content . $objTab->end();
      }
    }
    // return the row (and clear row)
    $str = $this->row->start() . (empty($str) ? $strVoid : $str) . $this->row->end();
    $this->row = null;
    return $str;
  }
  private function make(string $property, string $entity, $values=[], bool $createCol=true, bool $namedCol=true) {
    if ( $property!=='arrTh' && $property!=='arrTd' ) die(__METHOD__.' invalid property, must be arrTh or arrTd');

    // When $values is 1 value, it will be inserted in each column
    if ( !is_array($values) ) {
      $value = $values;
      $values = [];
      foreach(array_keys($this->$property) as $key) $values[$key] = $value; // use property $this->arrTh or $this->arrTh
    }
    // Process each column
    $i=0;
    foreach($values as $key=>$value) {
      if ( !$namedCol ) $key=$i;
      // If column is missing, function can create a new column (with $this->arrTh or $this->arrTh)
      if ( !isset($this->$property[$key]) && $createCol ) $this->$property[$key] = $property==='arrTh' ? new TabHead($value) : new TabData($value);
      // If column exists, changes the tag [tag], the inner text [content], or attributes [attr]
      if ( isset($this->$property[$key]) ) {
        switch($entity) {
          case '[tag]':     $this->$property[$key]->tag = $value; break;
          case '[content]': $this->$property[$key]->content = $value; break;
          case '[attr]':    $this->$property[$key]->setAttributes($value); break;
          default:          $this->$property[$key]->set($entity,$value);
        }
      }
      ++$i;
    }
  }

}