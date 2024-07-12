// Checkbox-events (and command-events) are added if the table has the class "table-cb"
// The table must includes [data-formid]=formid (ie. the id of the form around the table). If missing uses "form-items".
// Top checkbox with [data-target] attribute is linked to row checkboxes by the [name] attribute
// ShiftClick event works when checkboxes include an data-row attribute
const cbTables = document.querySelectorAll('.table-cb');
const noselect = document.currentScript.dataset['noselect'] ?? 'Nothing selected...';
cbTables.forEach( cbTable => {
  const formid = cbTable.dataset.formid ?? 'form-items';
  const cbRows = cbTable.querySelectorAll('input[type="checkbox"][name]');
  if ( cbRows.length===0 ) return;
  const cbTop = cbTable.querySelector('input[type="checkbox"][data-target]'); // can be null
  const cbName = cbRows[0].name;
  cbRows.forEach( cb => {
    cb.addEventListener('click', cbCheckboxShiftClick);
    if ( cbTop ) cb.addEventListener('click', cbCheckboxAllUpdate);
   });
  if ( cbTop ) { cbTop.addEventListener('click', cbCheckboxAll); }
  // commands event, search for commands in block menu having [data-table=tableid]
  document.querySelectorAll('.cmds-cb[data-table="'+cbTable.id+'"] .cmd-cb[data-action]').forEach( cmd => {
    cmd.addEventListener('click', ()=>{
      if ( !cbCheckboxChecked(cbTable) ) { qtShowAlert(cmd.parentNode,noselect ?? 'Nothing selected...', 'top:-4px;right:-1rem'); return false; }
      document.getElementById(formid+'-action').value = cmd.dataset.action;
      document.getElementById(formid).submit();
    });
  });
});
function cbCheckboxChecked(cbTable) {
  if ( cbTable.querySelectorAll(`input[name="${cbTable.id}-cb[]"]:checked`).length===0 ) return false;
  return true;
}
function cbCheckboxAll(e) {
  const target = e.target.dataset.target; if ( !target ) return;
  document.querySelectorAll(`[name="${target}"]`).forEach( item => { item.checked = e.target.checked; });
}
function cbCheckboxAllUpdate(e) {
  const cbTop = document.querySelector(`[data-target="${e.target.name}"]`);
  if ( !cbTop ) return;
  cbTop.checked = document.querySelectorAll(`[name="${e.target.name}"]`).length===document.querySelectorAll(`[name="${e.target.name}"]:checked`).length;
}
function cbCheckboxIds(ids, prefixId='t1-cb-', state=true) {
  // Check/uncheck the checkboxes from a list of ids
  ids.forEach( id => {
    const el = getElementById(prefixId+id);
    if ( el ) el.checked = state;
  } );
}
function cbCheckboxShiftClick(e) {
  if ( !e.shiftKey ) return;
  // find checkbox checked having the same [name]
  const items = document.querySelectorAll(`[name="${e.target.name}"]:checked`);
  if ( items.length<2 ) return; // no others
  const idx = [...items].map(item => parseInt(item.dataset.row));
  const idxMin = Math.min(...idx);
  const idxMax = Math.max(...idx);
  for(let i=idxMin; i<idxMax+1; ++i ) {
    const el = document.querySelector(`[name="${e.target.name}"][data-row="${i}"]`);
    if ( el ) el.checked = true;
  }
}
function cbShowMore(parentContainer,dataset)
{
  qtHideDlg();
  const tableid = parentContainer.dataset.table; if ( !tableid ) { console.log('parentNode without data-table'); return false; }
  const cbTable = document.getElementById(tableid); if ( !cbTable ) { console.log('table '+tableid+' not found'); return false; }
  if ( !cbCheckboxChecked(cbTable) ) { qtShowAlert(parentContainer,noselect ?? 'Nothing selected...', 'top:-4px;right:-1rem'); return false; }
  const formid = cbTable.dataset.formid ?? 'form-items';
  const form = document.getElementById(formid); if ( !form ) { console.log('form '+formid+' not found'); return false; }
  const formaction = document.getElementById(formid+'-action'); if ( !formaction ) { console.log('form '+formid+'-action not found'); return false; }
  const box = document.createElement('div');
  box.id = 'cmd-cb-dlg';
  box.style = 'position:absolute;z-index:2;top:-5px;right:-1rem;display:flex;gap:0.2rem;align-items:start';
  const select = document.createElement('p');
  select.setAttribute('style','display:flex;flex-direction:column');
  dataset.forEach( (set)=>{
    const optgroup = document.createElement('span');
    optgroup.innerText = set.optgroup;
    optgroup.setAttribute('class','optgroup');
    select.appendChild(optgroup);
    set.options.forEach( (item)=>{
      const option = document.createElement('a');
      option.innerText = item.text;
      option.onclick = ()=>{
        if ( !cbCheckboxChecked(cbTable) ) { qtShowAlert(parentContainer,noselect ?? 'Nothing selected...', 'top:-4px;right:-1rem'); return false; }
        formaction.value = item.value;
        form.submit(); }
      select.appendChild(option);
    });
  });
  const button = qtXbutton();
  button.onclick = function(){ box.remove(); };
  box.appendChild(select);
  select.after(button);
  parentContainer.style.position = 'relative';
  parentContainer.appendChild(box);
  select.focus();
}