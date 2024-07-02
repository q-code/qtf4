<?php
/**
* @var CHtml $oH
* @var int $id
* @var boolean $canEdit
* @var boolean $edit
*/
if ( SUser::role()==='A' )
echo '<form id="modaction" method="get" action="'.url(APP.'_register.php').'"><div id="optionbar">
'.qtSvg('user-a', 'title='.L('Role_A')).'
<select name="a" onchange="if ( this.value!=`` ) document.getElementById(`modaction`).submit();">
<option value="" disabled selected hidden>'.L('Role_A').' '.L('commands').'</option>
<option value="adm-reset">'.L('Reset_pwd').'...</option>
<option value="role"'.($id<2 ? ' disabled' : '').'>'.L('Change_role').'...</option>
<option value="ban"'.($id<2 ? ' disabled' : '').'>'.L('Ban').'...</option>
<option value="delete"'.($id<2 ? ' disabled' : '').'>'.L('Delete').' '.L('user').'...</option>
</select>
<input type="hidden" name="id" value="'.$id.'"/>
</div></form>&nbsp;';

if ( $canEdit )
echo '<a class="button" href="'.url($oH->php).'?id='.$id.'&edit='.($edit ? 0 : 1).'">'.qtSvg('pen','class=btn-prefix').L($edit ? 'Edit_stop' : 'Edit_start').'</a>';