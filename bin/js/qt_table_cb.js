// Checkbox-events are added if the table(s) has a data-cbe attribute
// Top checkbox (having the data-target attribute) is linked to row checkboxes by the name attribute
// ShiftClick event works when checkboxes include an data-row attribute
const cbTables = document.querySelectorAll('table[data-cbe]');
cbTables.forEach( cbTable => {
  const cbTop = cbTable.querySelector('input[type="checkbox"][data-target]');
  const cbRows = cbTable.querySelectorAll('input[type="checkbox"][name]');
  if ( !cbTop || cbRows.length===0 ) return;
  cbTop.addEventListener("click", qtCheckboxAll);
  cbRows.forEach( cb => {
    cb.addEventListener("click", qtCheckboxShiftClick);
    cb.addEventListener("click", qtCheckboxAllUpdate);
  });
});
function qtCheckboxAll(e) {
  const target = e.target.dataset.target;
  if ( !target ) return;
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
  const sorted_rows = [...items].map(item => parseInt(item.dataset.row)).sort();
  // checks between first and last found
  const max = sorted_rows.pop();
  for(let i=sorted_rows[0]+1; i<max; ++i ) {
    const el = document.querySelector(`[name="${e.target.name}"][data-row="${i}"]`);
    if ( el ) el.checked = true;
  }
}