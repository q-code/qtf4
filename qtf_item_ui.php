<?php
/**
* @var CTopic $oT
*/
echo '<div id="optionsbar">
'.qtSvg('user-m').'
<form method="post" action="'.url('qtf_item.php').'" id="modaction">
<select name="Maction" onchange="document.getElementById(`modaction`).submit()">
<option hidden disabled selected>'.L('Staff').' '.L('commands').'...</option>
<option value="reply">'.L('Reply').'...</option>
<option value="move">'.L('Move').'...</option>
<option value="delete">'.L('Delete').'...</option>
<optgroup label="'.L('Status').'">';
foreach(CTopic::getStatuses() as $k=>$value) echo '<option value="status_'.$k.'"'.($oT->status==$k ? ' disabled' : '').'>'.$value.($oT->status==$k ? ' &#10004;' : '').'</option>'; // caution == array keys can be [int]
echo '</optgroup>
<optgroup label="'.L('Type').'">';
foreach(CTopic::getTypes() as $k=>$value) echo '<option value="type_'.$k.'"'.($oT->type==$k ? ' disabled' : '').'>'.$value.($oT->type==$k ? ' &#10004;' : '').'</option>'; // caution == array keys can be [int]
echo '</optgroup>
</select>
';
echo '<input type="hidden" name="s" value="'.$oT->pid.'"/>
<input type="hidden" name="t" value="'.$oT->id.'"/>
</form></div>
';