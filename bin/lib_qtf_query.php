<?php // v4.0 build:20230430

/**
 * Returns a sql date condition selecting a timeframe
 * @param string $dbtype database type
 * @param string $tf timeframe {y|m|w|1..12|YYYY|YYYYMM|*}
 * @param string $prefix AND
 * @param string $field
 * @return string
 */
function getSqlTimeframe($dbtype,$tf='*',$prefix=' AND ',$field='t.firstpostdate') {
  if ( empty($tf) || $tf==='*' ) return ''; // no timeframe
  if ( !is_string($dbtype) || !is_string($tf) || !is_string($prefix) || !is_string($prefix) || empty($field) ) die(__FUNCTION__.' requires string arguments');
  // $tf can be {y|m|w|1..12|YYYY|YYYYMM} i.e. this year, this month, last week, previous month#, a specific year YYYY, a specific yearmonth YYYYMM
  $operator = '=';
  switch($tf)
  {
    case 'y':	// this year
      $strDate = date('Y');
      break;
    case 'm': // this month
      $strDate = date('Ym');
      break;
    case 'w':	// last week
      $operator = '>';
      $strDate = (string)date('Ymd', strtotime("-8 day", strtotime(date('Ymd'))));
      break;
    default: // $tf is the month number or a specific datemonth
      if ( !qtCtype_digit($tf) ) die(__FUNCTION__.' invalid tf argument');
      switch(strlen($tf))
      {
        case 1:
        case 2:
          $intMonth = (int)$tf;
          $intYear = (int)date('Y'); if ( $intMonth>date('n') ) --$intYear; // check if month from previous year
          $strDate = (string)($intYear*100+$intMonth);
          break;
        case 4:
          $strDate = $tf;
          break;
        case 6:
          $strDate = $tf;
          break;
        default: die(__FUNCTION__.' invalid tf argument');
      }
  }
  $len = strlen($strDate);
  switch($dbtype)
  {
    case 'pdo.pg':
    case 'pg': return $prefix . "SUBSTRING($field FROM 1 FOR $len) $operator '$strDate'"; break;
    case 'pdo.sqlite':
    case 'sqlite':
    case 'pdo.oci':
    case 'oci': return $prefix . "SUBSTR($field,1,$len) $operator '$strDate'"; break;
    default: return $prefix . "LEFT($field,$len) $operator '$strDate'";
  }
}

/**
 * Parse url arguments, urldecode and check contents
 * @param mixed $query urlencoded arguments string or array
 * @param boolean $trimV true to trim the searched-text ($args['v'])
 * @return array or die if some arguments are missing or invalid
 * <p>Query arguments are:<br>q=type of query<br>s=section<br>v=searched-text<br>v2=optional 2nd searched-text<br>y=optional year</p>
 */
function validateQueryArguments($query,$trimV=true)
{
  if ( !is_string($query) && !is_array($query) ) die(__FUNCTION__.' first argument must be string or array of strings');
  $args = is_array($query) ? $query : array();
  if ( is_string($query) ) parse_str($query,$args);
  // check
  if ( !isset($args['q']) ) $args['q']='s'; // if missing q, assume q=s
  if ( !empty($args['v']) && strpos($args['v'],'"')!==false ) $args['v'] = qtDb(trim($args['v']));
  if ( !empty($args['v2']) && strpos($args['v2'],'"')!==false ) $args['v2'] = qtDb(trim($args['v2']));
  switch($args['q'])
  {
    case 's':
      if ( !isset($args['s']) || !is_numeric($args['s']) || $args['s']<0 ) $args['s'] = -1;
      if ( count($args)!==2 ) die(__FUNCTION__.' Invalid arguments'); // For section-query, only q and s can be used
      break;
    case 'ref':
    case 'kw':
    case 'qkw':
      if ( empty($args['v']) || strlen($args['v'])>64 ) die(__FUNCTION__.' Invalid argument v');
      if ( $trimV ) $args['v'] = trim($args['v']);
      break;
    case 'news':
      break;
    case 'user':
    case 'userm':
      // search using userid [v2] (search [v2] from [v] if missing)
      if ( empty($args['v2']) && !empty($args['v']) ) { global $oDB; $args['v2'] = SUser::getUserId($oDB,$args['v']); } // return false if not found)
      if ( empty($args['v2']) || !is_numeric($args['v2']) || $args['v2']<0 ) die(__FUNCTION__.' Invalid argument v2');
      break;
    case 'btw':
      if ( empty($args['v']) || empty($args['v2']) || $args['v']<'19000101' || $args['v2']>'21000101' ) die(__FUNCTION__.' Invalid argument dates');
      $args['v'] = qtDateClean($args['v'],8); // Returns YYYYMMDD (no time) while browser should provide YYYY-MM-DD. Returns '' if format not supported. If $v='now', returns today
      $args['v2'] = qtDateClean($args['v2'],8);
      if ( $args['v']>$args['v2'] ) die(__FUNCTION__.' Invalid date (date1 > date2)');
      break;
    case 'adv':
      if ( empty($args['v']) && $args['v2']==='*' ) die(__FUNCTION__.' Invalid argument date or tag');
      if ( strlen($args['v'])>128 ) die(__FUNCTION__.' Invalid argument tag');
      if ( strlen($args['v2'])>2 ) die(__FUNCTION__.' Invalid argument date');
      if ( $trimV ) $args['v'] = trim($args['v']);
      break;
    case 'last':
      if ( isset($args['v']) ) die(__FUNCTION__.' Invalid argument v'); // only filter arguments, no text argument
      break;
    default: die(__FUNCTION__.' Invalid query argument q');
  }
  // check injection
  if ( isset($args['s']) && $args['s']==='*' ) $args['s']='-1';
  if ( isset($args['s']) && !is_numeric($args['s']) ) die(__FUNCTION__.' Invalid argument s');
  if ( isset($args['v2']) && ( strpos($args['v2'],'"')!==false || strpos($args['v2'],"'")!==false ) ) die(__FUNCTION__.' Invalid timeframe');
  if ( isset($args['st']) && strlen($args['st'])>1 ) die(__FUNCTION__.' Invalid status');

  return $args;
}

/**
 * Update sql statement parts (from,where,values,count-query) using the url arguments ($query)
 * @param string $sqlFrom
 * @param string $sqlWhere
 * @param string $sqlValues
 * @param string $sqlCount
 * @param string $sqlCountAlt
 * @param string $query
 * @return string '' or a result warning (string parts are updated by reference)
 */
function sqlQueryParts(&$sqlFrom,&$sqlWhere,&$sqlValues,&$sqlCount,&$sqlCountAlt,$query)
{
  $result = '';
  $args = validateQueryArguments($query); // values are string and can be '*', except s that must be a numeric-string. arg[v|v2] are slashed
  if ( count($args)==0 ) die(__FUNCTION__.' missing query argument');

  // Assgin query arguments or set to default
  $s = isset($args['s']) && is_numeric($args['s']) && $args['s']>=0 ? (int)$args['s'] : -1;
  $to = empty($args['to']) ? '0' : '1';
  $v  = isset($args['v']) ? $args['v'] : '';
  $v2 = isset($args['v2']) ? $args['v2'] : '';
  $st = isset($args['st']) ? $args['st'] : '*';
  $arrV = strlen(trim($v))===0 ? [] : array_unique(array_filter(array_map('trim',explode(';',mb_strtolower(str_replace("\r\n"," ",$v))))));

  // Prepare sql parts
  $sqlFrom = ' FROM TABTOPIC t INNER JOIN TABPOST p ON t.id=p.topic';
  $sqlWhere = ' WHERE t.forum'.($s>=0 ? '='.$s : '>=0');
  // prevent searching in Admin sections while not staffmember
  if ( $s<0 && !SUser::isStaff() && isset($GLOBALS['_Sections']) )
  {
    $ad_Sections = array();
    foreach($GLOBALS['_Sections'] as $mId=>$mSec) if ( isset($mSec['type']) && $mSec['type']=='1' ) $ad_Sections[] = $mId;
    if ( !empty($ad_Sections) ) $sqlWhere = ' WHERE t.forum NOT IN ('.implode(',',$ad_Sections).')';
  }
  // prevent user searching (other creator) in private section (can search announces)
  if ( $s>=0 && !SUser::isStaff() && isset($GLOBALS['_Sections'][$s]['type']) && $GLOBALS['_Sections'][$s]['type']=='2' ) {
    $sqlWhere .= " AND (t.firstpostuser=".SUser::id()." OR t.type='A')";
  }
  // status
  if ( $st!=='*' ) { $sqlWhere .= ' AND t.status=:status'; $sqlValues[':status'] = $st; }

  switch($args['q']) {

    case 'qkw':

      // support multiple qkw (arrV)
      // search in posts and in replies (no type condition)
      if ( count($arrV)>0 )
      {
        for($i=0;$i<count($arrV);$i++)
        {
          if ( is_numeric($arrV[$i]) )
          {
            $sqlValues[':numid'.$i] = $arrV[$i];
            $arrV[$i]='t.numid=:numid'.$i;
          }
          else
          {
            if ( strlen($arrV[$i])<2) { $arrV[$i]=null; $result=L('Search_minimum_2'); continue; }
            $sqlValues[':like'.$i] = '%'.strtoupper($arrV[$i]).'%';
            global $oDB;
            switch($oDB->type) {
              case 'pdo.sqlsrv':
              case 'sqlsrv': $arrV[$i] = 'UPPER(CAST(p.title AS VARCHAR(2000))) LIKE :like'.$i.(empty($to) ? ' OR UPPER(CAST(p.textmsg AS VARCHAR(2000))) LIKE :like'.$i : ''); break;
              default:       $arrV[$i] = 'UPPER(p.title) LIKE :like'.$i.(empty($to) ? ' OR UPPER(p.textmsg) LIKE :like'.$i : ''); break;
            }
          }
        }
        $arrV = array_filter($arrV, function($item){ return !is_null($item);}); // drop null values
        $sqlWhere .= ' AND ('.implode(' OR ',$arrV).')';
      }
      $sqlCount = "SELECT count(*) as countid".$sqlFrom.$sqlWhere;
      break;

    case 'ref':

      // support multiple ref (arrV)
      // search in posts (not replies) and only ref sections
      $refSections = [];
      foreach($GLOBALS['_Sections'] as $mSec) if ( $mSec['numfield']!=='N' ) $refSections[] = $mSec['id'];
      if ( empty($refSections) ) $refSections = [0];
      $refSections = implode(',',$refSections);

      $sqlWhere .= " AND t.forum IN ($refSections) AND p.type='P'";
      if ( count($arrV)>0 )
      {
        for($i=0;$i<count($arrV);$i++) {
          $sqlValues[':numid'.$i] = $arrV[$i];
          $arrV[$i]='t.numid=:numid'.$i;
        }
        $sqlWhere .= " AND (".implode(' OR ',$arrV).")";
      }
      $sqlCount = "SELECT count(*) as countid $sqlFrom $sqlWhere";
      break;

    case 'kw':

      // support multiple kw (arrV)
      // search in posts and in replies (no type condition)
      global $oDB;
      for($i=0;$i<count($arrV);$i++)
      {
        if ( strlen($arrV[$i])<2) { $arrV[$i]=null; $result=L('Search_minimum_2'); continue; }
        $sqlValues[':like'.$i] = '%'.$arrV[$i].'%';
        switch($oDB->type) {
          case 'pdo.sqlsrv':
          case 'sqlsrv': $arrV[$i] = 'LOWER(CAST(p.title AS VARCHAR(2000))) LIKE :like'.$i.(empty($to) ? ' OR LOWER(CAST(p.textmsg AS VARCHAR(2000))) LIKE :like'.$i : ''); break;
          default:       $arrV[$i] = 'LOWER(p.title) LIKE :like'.$i.(empty($to) ? ' OR LOWER(p.textmsg) LIKE :like'.$i : ''); break;
        }
      }
      $sqlWhere .= ' AND ('.implode(' OR ',$arrV).')';
      $sqlCount = "SELECT count(*) as countid $sqlFrom $sqlWhere";
      break;

    case 'last':

      // get the lastpost date
      // search in posts (not replies)
      global $oDB;
      $oDB->query( "SELECT max(p.issuedate) as f1 FROM TABPOST p ");
      $row = $oDB->getRow();
      if ( empty($row['f1']) ) $row['f1'] = date('Ymd');
      $sqlValues[':lastdate'] = substr(addDate($row['f1'],-8,'day'), 0, 8);
      // query post of this day
      $sqlWhere .= " AND p.type='P' AND ".sqlDateCondition(':lastdate','p.issuedate',8,'>','');
      $sqlCount = "SELECT count(*) as countid $sqlFrom $sqlWhere";
      break;

    case 'user':
    case 'userm':

      if ( $args['q']==='user') $sqlWhere .= " AND p.type='P'";
      $sqlWhere .= " AND p.userid=$v2";
      $sqlCount  = "SELECT count(*) as countid $sqlFrom $sqlWhere"; // count all messages
      $sqlCountAlt = "SELECT count(*) as countid FROM TABTOPIC WHERE firstpostuser=$v2"; // count topic only
      break;

    case 'btw':

      global $oDB;
      $sqlValues[':postdate_a'] = $v;
      $sqlValues[':postdate_b'] = $v2;
      $sqlWhere .= sqlDateCondition(':postdate_a','t.firstpostdate',8,'>=','').' AND '.sqlDateCondition(':postdate_b','t.firstpostdate',8,'<=','');
      $sqlWhere  .= " AND p.type='P' AND ";
      switch($oDB->type)
      {
        case 'pdo.pg':
        case 'pg': $sqlWhere .= '(SUBSTRING(t.firstpostdate FROM 1 FOR 8)>=:postdate_a AND SUBSTRING(t.firstpostdate FROM 1 FOR 8)<=:postdate_b)'; break;
        case 'pdo.sqlite':
        case 'sqlite':
        case 'pdo.oci':
        case 'oci': $sqlWhere .= '(SUBSTR(t.firstpostdate,1,8)>=:postdate_a AND SUBSTR(t.firstpostdate,1,8)<=:postdate_b)'; break;
        default: $sqlWhere .= '(LEFT(t.firstpostdate,8)>=:postdate_a AND LEFT(t.firstpostdate,8)<=:postdate_b)';
      }
      $sqlCount = "SELECT count(*) as countid".$sqlFrom.$sqlWhere;
      break;

    case 'adv':

      global $oDB;
      // timeframe
      $sqlWhere .= getSqlTimeframe($oDB->type,$v2);

      // search in posts (not replies)
      $sqlWhere .= " AND p.type='P'";

      // Topics Tags
      if ( count($arrV)>0 )
      {
        for($i=0;$i<count($arrV);++$i)
        {
          $sqlValues[':like'.$i] = '%'.strtoupper($arrV[$i]).'%';
          switch($oDB->type)
          {
            case 'pdo.sqlsrv':
            case 'sqlsrv':$arrV[$i] = 'LOWER(CAST(t.tags AS VARCHAR(2000))) LIKE :like'.$i; break;
            default:     $arrV[$i] = 'LOWER(t.tags) LIKE :like'.$i; break;
          }
        }
        $sqlWhere .= ' AND ('.implode(' OR ',$arrV).')';
      }

      $sqlCount = "SELECT count(*) as countid".$sqlFrom.$sqlWhere;
      break;

    case 'news':

      $sqlWhere .= " AND p.type='P' AND t.type='A'";
      $sqlCount = "SELECT count(*) as countid".$sqlFrom.$sqlWhere;
      break;

    default: die(__FUNCTION__.' invalid argument q ['.$args['q'].']' );
  }
  return $result;
}