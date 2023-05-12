<?php
/**
* @var CHtml $oH
* @var CSection $oS
* @var string $forceShowClosed
* @var int $intCount
*/
$ui = '<button id="optionsbar-ctrl" class="nostyle square42'.($_SESSION['EditByRows'] ? ' expanded' : '').'" onclick="qtToggle(`optionsbar`,`flex`,this.id);qtFocusOut(this.id);qtFocus(`pref`);" title="'.L('My_preferences').'">'.qtSVG('cog').'</button> '.PHP_EOL;
$ui .= '<div id="optionsbar"'.($_SESSION['EditByRows'] ? '' : ' style="display:none"').'><form method="post" action="'.url($oH->selfurl).'?'.qtURI('page').'" id="formPref">'.PHP_EOL;
$ui .= '<select id="pref" name="pref" onchange="doSubmit(`formPref`);">'.PHP_EOL;
$ui .= '<option value="-" selected disabled hidden>'.L('Show').'</option>';
$ui .= '<option value="togglenewsontop">'.L('News_on_top').($_SESSION[QT]['news_on_top'] ? ' &#10004;' : ' &#10008;').'</option>';
$ui .= '<option value="toggleclosed"'.($_SESSION[QT]['show_closed']=='0' && isset($forceShowClosed) && $forceShowClosed=='1' ? ' disabled' : '').'>'.L('Closed_item+').($_SESSION[QT]['show_closed'] ? ' &#10004;' : ' &#10008;').'</option>';
$ui .= '<optgroup label="'.L('Item+').'">';
foreach(['10','25','50','100'] as $i)
$ui .= '<option value="n'.$i.'"'.($_SESSION[QT]['items_per_page']===$i ? 'disabled' : '').'>'.$i.' / '.L('page').($_SESSION[QT]['items_per_page']===$i ? ' &#10004;' : '').'</option>';
$ui .= '</optgroup></select>'.PHP_EOL;
$ui .= '</form>'.PHP_EOL;
$oH->scripts[] = 'function doSubmit(idform,idhide="optionsbar"){
let d = document.getElementById(idhide); if ( d ) d.style.display="none";
d = document.getElementById(idhide+"-ctrl"); if ( d ) d.style.visibility="hidden";
d = document.getElementById(idform); if ( d ) d.submit();}';
if ( SUser::isStaff() ) {
  $ui .= '<form id="modaction" method="post" action="'.url($oH->selfurl).'?'.$oH->selfuri.'">'.PHP_EOL;
  $ui .= '<select name="modaction" onchange="doSubmit(`modaction`);">';
  $ui .= '<option disabled selected hidden>'.L('Add').'</option>';
  $ui .= '<optgroup label="'.L('Staff').' '.L('action').'">';
  if ( is_a($oS,'CSection') && $oS->id>=0 ) $ui .= '<option value="nt">'.L('New_item').'...</option>';
  $ui .= '</optgroup>';
  if ( !empty($intCount) ) {
    $dflt_lastcol = $oS->getMF('options','last'); if ( empty($dflt_lastcol) || strtolower($dflt_lastcol)==='n' ) $dflt_lastcol='none';
    $ui .= '<optgroup label="'.L('Column').'">';
    $arr = ['default'=>L('Use_default'),'none'=>L('None'),'id'=>'Id','views'=>L('Views'),'status'=>L('Status')];
    if ( $_SESSION[QT]['tags'] ) $arr['tags'] = L('Tag+'); // list of last columns
    foreach($arr as $k=>$val) $ui .= '<option value="'.$k.'">'.$val.($dflt_lastcol===$k ? ' ('.L('default').')' : '').($_SESSION[QT]['lastcolumn']===$k ? ' &#10004;' : '').'</option>';
    $ui .= '</optgroup>';
  }
  $ui .= '</select>'.PHP_EOL;
  $ui .= '<input type="hidden" id="toggleedit" name="toggleedit" value="0"/>'.PHP_EOL;
  $ui .= '</form>'.PHP_EOL;

  if ( $intCount>0 ) {
    $ui .= '<button class="nostyle square32" onclick="tglEditState(this);" id="showeditor-ctrl" data-state="'.$_SESSION['EditByRows'].'" title="'.L('Edit_start').'">'.qtSVG('edit').'</button>'.PHP_EOL;
    $oH->scripts[] = 'function tglEditState(obj){ document.getElementById("toggleedit").setAttribute("value","1"); doSubmit("modaction"); }';
  }

}
$ui .= '</div>'.PHP_EOL;