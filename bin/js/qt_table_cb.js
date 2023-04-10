// Checkbox event added to [th|td]checkboxes belonging to a specific table
// The target table is declared in a html element having id="cbe" and the attribute data-tableid
// tips: here, the <script> itself has id="cbe" and data-tableid="t1"
const cbTable = document.getElementById("cbe").getAttribute("data-tableid");
const cbTop = document.querySelector(`#${cbTable} th input[type="checkbox"]`);
const cbRows = document.querySelectorAll(`#${cbTable} td input[type="checkbox"]`);
if ( cbTop ) cbTop.addEventListener("click", function(){qtCheckboxAll(cbTop,cbRows);}, true );
if ( cbRows.length>0 ) cbRows.forEach( cb => {
  cb.addEventListener("click", qtCheckboxShiftClick, true);
  cb.addEventListener("click", function(){qtCheckboxUpdateTop(cbTop,cbRows);}, true);
} );
// Parametres to handle shift-click (requires an attribute data-row numbering on the checkboxes)
let firstChecked = -1;
let lastChecked = -1;

function qtCheckboxAll(cbTop, cbRows){
  // Apply the top checkbox state to all rows checkboxes
	if ( cbRows.length<1 ) return;
  cbRows.forEach( cb => { cb.checked = cbTop.checked; } );
}
function qtCheckboxUpdateTop(cbTop, cbRows){
	// Check/uncheck the top checkbox when not all boxes are checked/unchecked
	if ( cbRows.length<1 ) return;
  let n = 0;
  cbRows.forEach( cb => { if (cb.checked) n++; } );
  cbTop.checked = cbRows.length===n;
}
function qtCheckboxClick(id){
	if ( document.getElementById(id) ) document.getElementById(id).click();
}
function qtCheckboxIds(ids, prefixId='t1-cb-', state=true){
  // Check/uncheck the checkboxes from a list of ids
  ids.forEach( id => {
    const el = getElementById(prefixId+id);
    if ( el ) el.checked = state;
  } );
}
function qtCheckboxShiftClick(e) {
  if ( firstChecked>=0 && e.shiftKey ) { lastChecked = parseInt(e.target.dataset.row); } else { firstChecked = parseInt(e.target.dataset.row); }
  if ( firstChecked>=0 && lastChecked>=0 && firstChecked<lastChecked ) {
    for(let i=firstChecked; i<=lastChecked; ++i) {
      const el = document.querySelector(`#${cbTable} td [data-row="${i}"]`);
      if ( el ) el.checked = true;
    }
    firstChecked = -1;
    lastChecked = -1;
  }
}