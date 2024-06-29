// Checkbox-events (and command-events) are added if the table has the class "table-cb"
// The table must includes [data-formid]=formid (ie. the id of the form around the table). If missing uses "form-items".
// Top checkbox with [data-target] attribute is linked to row checkboxes by the [name] attribute
// ShiftClick event works when checkboxes include an data-row attribute
const cbTables = document.querySelectorAll('table.table-cb');
const noselect = document.currentScript.dataset['noselect'] ?? 'Nothing selected';
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
      if ( !cbCheckboxChecked(cbTable,cmd,true) ) return false;
      document.getElementById(formid+'-action').value = cmd.dataset.action;
      document.getElementById(formid).submit();
    });
  });
});
function cbCheckboxChecked(cbTable,cmd,showAlert=false) {
  if ( cbTable.querySelectorAll(`input[name="${cbTable.id}-cb[]"]:checked`).length===0 ) {
    if ( showAlert ) cbShowAlert(cmd,noselect);
    return false;
  }
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
function cbShowMore(el,dataset)
{
  cbHideDlg();
  const tableid = el.parentNode.dataset.table; if ( !tableid ) { console.log('parentNode without data-table'); return false; }
  const cbTable = document.getElementById(tableid); if ( !cbTable ) { console.log('table '+tableid+' not found'); return false; }
  if ( !cbCheckboxChecked(cbTable,tableid+'-cb[]') ) { cbShowAlert(el,noselect ?? 'Nothing selected'); return false; }
  const formid = cbTable.dataset.formid ?? 'form-items';
  const box = document.createElement('div');
  box.id = 'cmd-cb-dlg';
  box.style = 'position:absolute;z-index:2;top:-8px;right:-1rem;display:flex;gap:0.2rem;align-items:start;font-size:0.85rem;background-color:#fff;padding:0.2rem;border:solid 1px var(--bgadmin);border-radius:5px;box-shadow:0 0 5px #aaa';
  const select = document.createElement('select');
  select.setAttribute('size','8');
  select.setAttribute('multiple','');
  select.onchange = function() {
    console.log('onchange event');//!!!
    document.getElementById(formid+'-action').value = select.value;
    document.getElementById(formid).submit();
  };
  dataset.forEach( (set)=>{
    const optgroup = document.createElement('optgroup');
    optgroup.label = set.optgroup;
    set.options.forEach( (item)=>{
      const option = document.createElement('option');
      option.text = item.text;
      option.value = item.value;
      optgroup.appendChild(option);
    });
    select.appendChild(optgroup);
  });

  const button = document.createElement('button');
  button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" style="vertical-align:-0.125em" width="0.69em" height="1em" viewBox="0 0 352 512"><path fill="currentColor" d="m242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28L75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256L9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z"></path></svg>';
  button.onclick = function(){ box.remove(); };
  button.style = 'padding:1px 4px;background-color:var(--bgadmin);border-color:var(--bgadmin)';
  box.appendChild(select);
  select.after(button);

  el.after(box);
  select.focus();
}
function cbShowAlert(el,msg,postfix='...')
{
  cbHideDlg();
  const box = document.createElement('div');
  box.id = 'cmd-cb-dlg';
  box.style = 'position:absolute;z-index:2;top:-8px;right:-1rem;display:flex;gap:0.2rem;align-items:center;font-size:0.85rem;background-color:#fff;padding:0.2rem;border:solid 1px var(--bgadmin);border-radius:5px;box-shadow:0 0 5px #aaa';
  const select = document.createElement('span');
  select.innerText = msg+postfix;
  const button = document.createElement('button');
  button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" style="vertical-align:-0.125em" width="0.69em" height="1em" viewBox="0 0 352 512"><path fill="currentColor" d="m242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28L75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256L9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z"></path></svg>';
  button.onclick = function(){ box.remove(); };
  button.style = 'padding:1px 4px;background-color:var(--bgadmin);border-color:var(--bgadmin)';
  box.appendChild(select);
  select.after(button);
  el.after(box);
}
function cbHideDlg()
{
  const el = document.getElementById('cmd-cb-dlg');
  if ( el ) el.remove();
}