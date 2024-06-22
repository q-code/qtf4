<?php // v4.0 build:20240210

function sectionsAsOption(int $selected=-1, array $reject=[], array $disabled=[], string $all='', int $textsize=32, int $max=100, string $prefixValue='')
{
  // If $all is not empty, the list includes a $all option in first position having value '-1'.
  // To remove some section(s) from this list, use $reject and provide an array of id's [int]. Providing one id [int] is also possible.
  $arrDS = [];
  $countS = 0;
  foreach(array_keys($GLOBALS['_Domains']) as $mDid) {
  foreach($GLOBALS['_Sections'] as $mId=>$mSec) {
      if ( $mSec['pid']!==$mDid || in_array($mId,$reject) || ($mSec['type']=='1' && !SUser::isStaff()) ) continue; // Skip rejected or hidden
      $arrDS[$mDid][$mId] = SLang::translate('sec', 's'.$mId, $mSec['title']);
      ++$countS;
  }}
  // render as options
  $optgroup = $countS>2 && count($arrDS)>1;
  $str = ''; if ( !empty($all) ) $str ='<option value="-1"'.($selected===-1 ? ' selected' : '').(in_array(-1,$disabled,true) ? ' disabled': '').'>'.qtTrunc($all,$textsize).'</option>';
  foreach($arrDS as $domId=>$arrS) {
    if ( $optgroup ) $str .= '<optgroup label="'.qtTrunc( SLang::translate('domain', 'd'.$domId, $GLOBALS['_Domains'][$domId]['title']), $textsize ).'">';
    foreach($arrS as $id=>$name) {
      $str .= '<option value="'.$prefixValue.$id.'"'.($id===$selected ? ' selected' : '').(in_array($id,$disabled,true) ? ' disabled': '').'>'.qtTrunc($name,$textsize).'</option>';
      if ( --$max<1 ) break;
    }
    if ( $optgroup ) $str .= '</optgroup>';
  }
  return $str;
}
function htmlCsvLink($strUrl,$intCount=20,$pn=1)
{
  if ( empty($strUrl) ) return '';
  if ( $intCount<=$_SESSION[QT]['items_per_page'] ) {
    return '<a href="'.$strUrl.'&size=all&n='.$intCount.'" class="csv" title="'.L('H_Csv').'">'.L('Csv').'</a>';
  } else {
    $strCsv = '<a href="'.$strUrl.'&pn='.$pn.'&size=p'.$pn.'&n='.$intCount.'" class="csv" title="'.L('H_Csv').'">'.L('Csv').' ('.strtolower(L('Page')).')</a>';
    if ( $intCount<=1000 ) $strCsv .= ' &middot; <a href="'.$strUrl.'&size=all&n='.$intCount.'" class="csv" title="'.L('H_Csv').'">'.L('Csv').' ('.strtolower(L('All')).')</a>';
    if ( $intCount>1000 && $intCount<=2000 ) $strCsv .= ' &middot; <a href="'.$strUrl.'&size=m1&n='.$intCount.'" class="csv" title="'.L('H_Csv').'">'.L('Csv').' (1-1000)</a> &middot; <a href="'.$strUrl.'&size=m2&n='.$intCount.'" class="csv" title="'.L('H_Csv').'">'.L('Csv').' (1000-'.$intCount.')</a>';
    if ( $intCount>2000 && $intCount<=5000 ) $strCsv .= ' &middot; <a href="'.$strUrl.'&size=m5&n='.$intCount.'" class="csv" title="'.L('H_Csv').'">'.L('Csv').' (1-5000)</a>';
    if ( $intCount>5000 ) $strCsv .= ' &middot; <a href="'.$strUrl.'&size=m5&n='.$intCount.'" class="csv" title="'.L('H_Csv').'">'.L('Csv').' (1-5000)</a> &middot; <a href="'.$strUrl.'&size=m10&n='.$intCount.'" class="csv" title="'.L('H_Csv').'">'.L('Csv').' >(5000-10000)</a>';
  }
  return $strCsv;
}
function htmlLettres(string $baseFile, string $current='ALL', string $all='All', string $class='lettres', string $title='Username starting with ', int $size=1, bool $filterForm=true)
{
  // When $baseFile have other arguments, group argument will be appended
  // $current is the current group, $all is the label of the 'ALL' group
  // Note: $baseFile can be urlrewrited
  $current = strtoupper($current);
  $and = strpos($baseFile,'?') ? '&' : '?';
  switch($size) {
    case 1: $arr = explode('.','A.B.C.D.E.F.G.H.I.J.K.L.M.N.O.P.Q.R.S.T.U.V.W.X.Y.Z.~'); break;
    case 2: $arr = explode('.','A|B.C|D.E|F.G|H.I|J.K|L.M|N.O|P.Q|R.S|T.U|V.W|X.Y|Z.~'); break;
    case 3: $arr = explode('.','A|B|C.D|E|F.G|H|I.J|K|L.M|N|O.P|Q|R.S|T|U.V|W.X|Y|Z.~'); break;
    case 4: $arr = explode('.','A|B|C|D.E|F|G|H.I|J|K|L.M|N|O|P.Q|R|S|T.U|V|W.X|Y|Z.~'); break;
  }
  $str = '<a '.($current==='ALL' ? ' class="active"' : '').' href="'.($current==='ALL' ? 'javascript:void(0)' : $baseFile.$and.'fg=all').'">'.$all.'</a>';
  foreach($arr as $g) {
    $title = $title.($g==='~' ? L('other_char') : str_replace('|',' '.L('or').' ',$g));
    $str .= '<a'.($current===$g ? ' class="active"' : '').' href="'.($current===$g ? 'javascript:void(0)' : $baseFile.$and.'fg='.$g).'"'.(empty($title) ? '' : ' title="'.qtAttr($title).'"').'>'.str_replace('|','',$g).'</a>';
  }
  $group  = '<div class="'.$class.'">';
  $group .= L('Show').' '.$str;
  if ( $filterForm ) {
  $group .= ' <form method="get" action="'.$baseFile.'">';
  $group .= '<input required type="text" value="'.($current==='ALL' || in_array($current,$arr) ? '' : qtAttr($current)).'" name="group" size="3" maxlength="10" title="'.qtAttr($title).'"/>';
  $group .= '<button type="submit" value="submit">'.qtSvg('search').'</button>';
  $group .= qtTags(array_map('urldecode',qtExplodeUri($baseFile,'page|group')), '', 'tag=hidden');
  $group .= '</form>';
  }
  $group .= '</div>';
  return $group;
}
function bbcButtons(int $size=1, string $id='text-area')
{
  if ( !QT_BBC || $size===0 ) return '';
  $str = '<a class="bbc" onclick="qtBbc(`b`,`'.$id.'`)" title="'.L('Bbc.bold').'">'.qtSvg('bold').'</a>';
  $str .= '<a class="bbc" onclick="qtBbc(`i`,`'.$id.'`)" title="'.L('Bbc.italic').'">'.qtSvg('italic').'</a>';
  $str .= '<a class="bbc" onclick="qtBbc(`u`,`'.$id.'`)" title="'.L('Bbc.under').'">'.qtSvg('underline').'</a>';
  $str .= '<a class="bbc" onclick="qtBbc(`quote`,`'.$id.'`)" title="'.L('Bbc.quote').'">'.qtSvg('quote-right').'</a>';
  if ( $size>1 ) {
  $str .= '<a class="bbc" onclick="qtBbc(`code`,`'.$id.'`)" title="'.L('Bbc.code').'">'.qtSvg('code').'</a>';
  $str .= '<a class="bbc" onclick="qtBbc(`url`,`'.$id.'`)" title="'.L('Bbc.url').'">'.qtSvg('link').'</a>';
  $str .= '<a class="bbc" onclick="qtBbc(`mail`,`'.$id.'`)" title="'.L('Bbc.mail').'">'.qtSvg('envelope').'</a>';
  }
  if ( $size>2 ) $str .= '<a class="bbc" onclick="qtBbc(`img`,`'.$id.'`)" title="'.L('Bbc.image').'">'.qtSvg('image').'</a>';
  return $str;
}
function icoPrefix(string $serie, int $i, string $src='config/prefix/')
{
  if ( file_exists($src.'serie-'.$serie.'.php') ) {
    include $src.'serie-'.$serie.'.php';
    if ( isset($prefixIcon[$i]) ) {
      // svg
      if ( substr($prefixIcon[$i],-4)==='.svg' ) {

        return '<span class="prefix_icon" title="'.L('PrefixIcon.'.$serie.'0'.$i).'"'.(isset($prefixStyle[$i]) ? ' style="'.$prefixStyle[$i].'"' : '').'>'.qtSvg(substr($prefixIcon[$i],0,-4)).'</span>';
      }
      // image
      return '<img class="prefix_icon" src="'.$src.$prefixIcon[$i].'" alt="'.$i.'" title="'.L('PrefixIcon.'.$serie.'0'.$i).'"'.(isset($prefixStyle[$i]) ? ' style="'.$prefixStyle[$i].'"' : '').'/>';
    }
  }
}
function formatCsvRow($arrFLD,$row,$oS=null)
{
  if ( !is_array($row) ) die('formatItemRow: Wrong argument #3');

  // PRE-PROCESS
  $formatRef='N';
  if ( isset($arrFLD['numid']) ) {
    $formatRef='%s';
    if ( is_a($oS,'CSection') ) $formatRef=$oS->numfield;
    if ( isset($row['numfield']) ) $formatRef=$row['numfield'];
    if ( !isset($row['numid']) ) $row['numid']='';
  }
  if ( isset($arrFLD['smile']) ) {
  	if ( !isset($row['smile']) || $row['smile']=='00' ) $row['smile']='';
  }

  // Process
  $arrValues = [];
  foreach(array_keys($arrFLD) as $k) {
    $str='';
    switch((string)$k) {
    	// type and title are default
    	case 'id': $str = (int)$row['id']; break;
    	case 'numid': $str = ( $formatRef=='N' ? '' : sprintf($formatRef,$row['numid']) ); break;
      case 'status':
      	$arr = CTopic::getStatuses();
      	$str = isset($arr[$row['status']]) ? $arr[$row['status']] : 'unknown';
      	break;
      case 'title': $str = isset($row['posttype']) && $row['posttype']==='R' ? '('.L('reply').')' : $row['title'] ; break;
      case 'text': $str = $row['preview']; break;
      case 'section': $str = SLang::translate('sec','s'.$row['forum']); break;
      case 'firstpostdate': $str = qtDate($row['firstpostdate'],'Y-m-d','$'); break;
      case 'lastpostdate': $str = qtDate($row['lastpostdate'],'Y-m-d','$'); break;
      case 'posts':
      case 'replies': $str = (int)$row['replies']; break;
      case 'coord':
        if ( isset($row['y']) && isset($row['x']) ) {
          $y = floatval($row['y']);
          $x = floatval($row['x']);
          if ( !empty($y) && !empty($x) ) $str = str_replace('&#176;','?',QTdd2dms($y).','.QTdd2dms($x));
        }
        break;
      case 'tags':
        $arrTags = empty($row['tags']) ? array() : explode(';',$row['tags']);
        foreach (array_keys($arrTags) as $i) if ( empty($arrTags[$i]) ) unset($arrTags[$i]);
        if ( count($arrTags)>5 ) {
          $arrTags = array_slice($arrTags,0,5);
          $arrTags[]='...';
        }
        $str = implode(' ',$arrTags);
        break;
      case 'user.id': $str = (int)$row['id']; break;
      case 'user.name': $str = $row['name']; break;
      case 'user.role': $str = $row['role']; break;
      case 'user.contact': $str = (isset($row['mail']) ? $row['mail'].' ' : '').(isset($row['www']) ? $row['www'] : ''); break;
      case 'user.location': $str = $row['location']; break;
      case 'user.notes': $str = (int)$row['notes']; break;
      case 'user.firstdate': $str = qtDate($row['firstdate'],'Y-m-d',''); break;
      case 'user.lastdate': $str = qtDate($row['lastdate'],'Y-m-d','').(empty($row['ip']) ?  '&nbsp;' : ' ('.$row['ip'].')'); break;
      default: if ( isset($row[$k]) ) $str = $row[$k]; break;
    }
    $arrValues[] = toCsv($str);
  }
  return implode(';',$arrValues);
}
function renderUserMailSymbol($row)
{
  // required $row['id|privacy|mail']
  if ( empty($row['mail']) || empty($row['id']) || !isset($row['privacy']) )
  return '<span class="disabled" title="no e-mail"><svg class="svg-symbol"><use href="#symbol-envelope" xlink:href="#symbol-envelope"/></svg></span>';
  $str = '';
  if ( (int)$row['privacy']===2 ) $str = renderEmail($row['mail'],'symbol'.(QT_JAVA_MAIL ? 'java' : ''));
  if ( (int)$row['privacy']===1 && SUser::role()!=='V' ) $str = renderEmail($row['mail'],'symbol'.(QT_JAVA_MAIL ? 'java' : ''));
  if ( SUser::id()==$row['id'] || SUser::isStaff() ) $str = renderEmail($row['mail'],'symbol'.(QT_JAVA_MAIL ? 'java' : ''));
  return $str;
}
function renderUserWwwSymbol($row)
{
  if ( empty($row['www']) || !isset($row['privacy']) )
  return '<span class="disabled" title="no web site"><svg class="svg-symbol svg-125"><use href="#symbol-home" xlink:href="#symbol-home"/></svg></span>';
  return '<a href="'.$row['www'].'" title="web site"><svg class="svg-symbol svg-125"><use href="#symbol-home" xlink:href="#symbol-home"/></svg></a>';
}
function renderUserPrivSymbol(array $row=[], string $empty='')
{
  // required $row['id|privacy']
  if ( empty($row['id']) || !isset($row['privacy']) ) return $empty;
  if ( SUser::isStaff() || SUser::id()===(int)$row['id'] ) {
    if ( (int)$row['privacy']===2 )
    return '<span data-private="2" title="'.L('Privacy_visible_2').'"><svg class="svg-symbol svg-125"><use href="#symbol-door-open" xlink:href="#symbol-door-open"/></svg></span>';
    return '<span data-private="'.$row['privacy'].'" title="'.L('Privacy_visible_'.$row['privacy']).'"><svg class="svg-symbol"><use href="#symbol-key" xlink:href="#symbol-key"/></svg></span>';
  }
  return $empty;
}
function formatItemRow(string $strTableId='t1', array $arrFLD=[], $row, $oS, array $arrOptions=[])
{
  if ( is_a($row,'CTopic') ) $row = get_object_vars($row);
  if ( !is_array($row) ) die('formatItemRow: Wrong argument $row');
  if ( !isset($row['replies']) ) $row['replies']=0;
  if ( isset($row['type']) ) $row['type'] = strtoupper($row['type']);
  if ( isset($arrFLD['numid']) && !isset($row['numid']) ) $row['numid'] = '';
  // handle options
  $showFirstline = isset($arrOptions['firstline']) ? $arrOptions['firstline'] : false;
  if ( isset($arrFLD['numid']) ) {
    $formatRef = isset($arrOptions['numfield']) ? $arrOptions['numfield'] : ''; // '' means build format using row-section and memory
    if ( empty($formatRef) && isset($row['forum']) && isset($GLOBALS['_Sections'][(int)$row['forum']]['numfield']) ) $formatRef = $GLOBALS['_Sections'][(int)$row['forum']]['numfield'];
  }
  if ( empty($formatRef) ) $formatRef = 'N';

  // PRE-PROCESS
  $arr = [];
  $strPrefixSerie='';
  // prefix smile
  if ( isset($row['icon']) ) {
    // smile-group
    if ( is_a($oS,'CSection') && $oS->id>=0 ) $strPrefixSerie = $oS->prefix;
    if ( isset($row['prefix']) ) $strPrefixSerie = $row['prefix'];
    if ( empty($strPrefixSerie) || $strPrefixSerie==='0' ) $strPrefixSerie = '';
    if ( !isset($row['icon']) ) $row['icon'] = '00';
  }
  if ( isset($arrFLD['tags']) || isset($arrFLD['title']) ) {
    $arrTags=array();
    $arrMoreTags=array();
    if ( !empty($row['tags']) ) $arrTags=explode(';',$row['tags']);
    if ( count($arrTags)>3 ) $arrMoreTags = array_slice($arrTags,3,10);
    $arrTags = array_slice($arrTags,0,3);
  }
  // when searching in posts without title, use this to report empty title
  if ( isset($arrFLD['title']) ) {
    if ( trim($row['title'])==='' ) $row['title']='('.L('reply').')';
    if ( empty($row['title']) && $row['title']!='0' ) $row['title']='('.L('Reply').')';
  }

  // icon
  if ( isset($arrFLD['icon']) ) {
    if ( !isset($row['posttype']) ) $row['posttype'] = 'P';
    $strTicon = CPost::getIconType($row['posttype'], $row['type'], $row['status'], QT_SKIN, CTopic::isHot($row['replies']) ? '_h' : '');
  }

  // FORMAT

  // ::::::::::
  foreach(array_keys($arrFLD) as $k) {
  // ::::::::::

    switch((string)$k) {
    case 'icon':
      $arr[$k] = '<a href="'.url('qtf_item.php').'?t='.$row['id'].'">'.$strTicon.'</a>';
      break;
    case 'numid':
      $arr[$k] = $formatRef=='N' ? '' : sprintf($formatRef,$row['numid']);
      break;
    case 'title':
      // smile merged in title
      $arr[$k] = '<a class="item" href="'.url('qtf_item.php').'?t='.$row['id'].'"'.(!empty($row['preview']) ? ' title="'.$row['preview'].'"' : '').'>'.$row['title'].'</a>';
      if ( !empty($strPrefixSerie) && !empty($row['icon']) && $row['icon']!=='00' ) {
        $arr[$k] .= ' '.icoPrefix($strPrefixSerie,(int)$row['icon']);
      }
      if ( !empty($strCoord) ) {
      $arr[$k] .= ' '.$strCoord;
      }
      if ( !empty($arrTags) ) {
        if ( count($arrTags)>1 ) {
          $arr[$k] .= ' <span class="tags" title="'.implode(', ',$arrTags).(empty($arrMoreTags) ? '' : '...').'">'.qtSvg('#tags').'</span>';
        } else {
          $arr[$k] .= ' <span class="tags" title="'.$arrTags[0].'" data-tagdesc="'.$arrTags[0].'">'.qtSvg('#tag').'</span>';
        }
      }
      if ( !empty($row['textmsg']) && $_SESSION[QT]['item_firstline']>0 && $showFirstline ) {
      $arr[$k] .= '&nbsp;<small class="item-msg-preview">'.qtTrunc(qtBBclean($row['textmsg'],true,L('Bbc.*')),QT_FIRSTLINE_SIZE).(empty($row['attach']) ? '' : ' '.qtSvg('paperclip')).'</small>';
      }
      break;
    case 'replies':
      // youreply merged in replies
      $arr[$k] = $row['replies']==='0' ? '0' : '<span id="'.$strTableId.'re'.$row['id'].'">'.qtSvg('#ireplied').'</span>&thinsp;<span>'.qtK((int)$row['replies']).'</span>';
      break;
    case 'views':
      $arr[$k] = $row['views']==='0' ? '0' : qtK((int)$row['views']);
      break;
    case 'section':
      $i = (int)$row['forum'];
      $arr[$k] = '<a href="'.url('qtf_items.php').'?s='.$i.'">'.(isset($GLOBALS['_Sections'][$i]['title']) ? $GLOBALS['_Sections'][$i]['title'] : 'Section '.$i).'</a>';
      break;
    case 'firstpostname':
      $arr[$k] = '<a href="'.url('qtf_user.php').'?id='.$row['firstpostuser'].'">'.$row['firstpostname'].'</a>';
      $arr[$k] .= '<br><small>'.qtDate($row['firstpostdate'],'$','$').'</small>';
      break;
    case 'lastpostdate':
      if ( empty($row['lastpostdate']) ) {
        $arr[$k] = '&nbsp;';
      } else {
        $arr[$k] = qtDate($row['lastpostdate'],'$','$').'<a class="lastitem" href="'.url('qtf_item.php').'?t='.$row['id'].'#p'.$row['lastpostid'].'">'.qtSvg('#caret-square-right').'</a>';
        $arr[$k] .= '<br><small>'.L('by').' <a href="'.url('qtf_user.php').'?id='.$row['lastpostuser'].'" title="'.qtAttr($row['lastpostname']).'">'.$row['lastpostname'].'</a></small>';
      }
      break;
    case 'status':
      $arrStatuses = CTopic::getStatuses(); $arr[$k] = '<span title="'.(empty($row['statusdate']) ? '' : qtDate($row['statusdate'],'d M','H:i',true,true)).'">'.$arrStatuses[$row['status']].'</span>';
      break;
    case 'tags':
    	$strTags = '';
    	foreach($arrTags as $str) if ( !empty($str) ) $strTags .= '<span class="tag" data-tagdesc="'.$str.'">'.$str.'</span>';
    	if ( !empty($arrMoreTags) ) $strTags .= '<abbr title="'.implode(', ',$arrMoreTags).'">...</abbr>';
    	$arr[$k] = (empty($strTags) ? '&nbsp;' : $strTags);
      break;
    case 'userphoto':
      $arr[$k] = '<div class="magnifier center">'.SUser::getPicture( (int)$row['id'], 'data-magnify=0|onclick=this.dataset.magnify=this.dataset.magnify==1?0:1;', '' ).'</div>';
      break;
    case 'username':
      $arr[$k] = '<a href="'.url('qtf_user.php').'?id='.$row['id'].'">'.$row['name'].'</a>';
      break;
    case 'usermarker':
      $arr[$k] = empty($strCoord) ? '&nbsp;' : $strCoord;
      break;
    case 'userrole':
      $arr[$k] = L('Role_'.$row['role']);
      break;
    case 'userlocation':
      $arr[$k] = empty($row['location']) ? '&nbsp;' : $row['location'];
      break;
    case 'usernumpost':
      $arr[$k] = $row['numpost'];
      break;
    case 'firstdate':
      $arr[$k] = empty($row['firstdate']) ? '&nbsp;' : qtDate($row['firstdate'],'$','',true,false,true);
      break;
    case 'modifdate':
      $arr[$k] = empty($row['modifdate']) ? '&nbsp;' : qtDate($row['modifdate'],'$','',true,false,true);
      break;
    default:
      if ( isset($row[$k]) ) {
        $arr[$k] = $row[$k];
      } else {
        $arr[$k] = '';
      }
      if ( $arr[$k]!=='' ) $arr[$k] = $arr[$k];
      break;
    }

  // ::::::::::
  }
  // ::::::::::

  return $arr;
}