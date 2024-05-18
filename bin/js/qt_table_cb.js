// Checkbox-events (and command-events) are added if the table has the class "table-cb"
// Top checkbox with [data-target] attribute is linked to row checkboxes by the [name] attribute
// ShiftClick event works when checkboxes include an data-row attribute
const cbTables = document.querySelectorAll('table.table-cb');
const noselect = document.currentScript.dataset['noselect'] ?? 'Nothing selected';
const formid = document.currentScript.dataset['formid'] ?? 'form-items';
cbTables.forEach( cbTable => {
  // top and rows checkbox event
  const cbTop = cbTable.querySelector('input[type="checkbox"][data-target]');
  const cbRows = cbTable.querySelectorAll('input[type="checkbox"][name]');
  if ( !cbTop || cbRows.length===0 ) return;
  const cbName = cbRows[0].name;
  cbTop.addEventListener('click', qtCheckboxAll);
  cbRows.forEach( cb => {
    cb.addEventListener('click', qtCheckboxShiftClick);
    cb.addEventListener('click', qtCheckboxAllUpdate);
  });
  // commands event, search for commands in block menu having [data-table=tableid]
  document.querySelectorAll('.rowcmds[data-table="'+cbTable.id+'"] a.rowcmd').forEach( cmd => {
    cmd.addEventListener('click', ()=>{
      if ( document.querySelectorAll(`input[name="${cbName}"]:checked`).length===0 ) {
        alert( noselect );
        return false;
      }
      document.getElementById(formid+'-action').value = cmd.dataset.action;
      document.getElementById(formid).submit();
    });
  });
});
function qtCheckboxAll(e) {
  const target = e.target.dataset.target; if ( !target ) return;
  document.querySelectorAll(`[name="${target}"]`).forEach( item => { item.checked = e.target.checked; });
}
function qtCheckboxAllUpdate(e) {
  const cbTop = document.querySelector(`[data-target="${e.target.name}"]`);
  if ( !cbTop ) return;
  cbTop.checked = document.querySelectorAll(`[name="${e.target.name}"]`).length===document.querySelectorAll(`[name="${e.target.name}"]:checked`).length;
}
function qtCheckboxIds(ids, prefixId='t1-cb-', state=true) {
  // Check/uncheck the checkboxes from a list of ids
  ids.forEach( id => {
    const el = getElementById(prefixId+id);
    if ( el ) el.checked = state;
  } );
}
function qtCheckboxShiftClick(e) {
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