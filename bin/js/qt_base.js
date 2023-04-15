const qtFormSafe = {
  saved: true,
  not  : function(){ this.saved=false; },
  exit : function(msg='Data not yet saved. Quit without saving?'){
    if ( !this.saved && !confirm(msg) ) return false;
    return true;
    }
};
function qtFocus(id){
  const el = document.getElementById(id); if ( !el ) return;
  el.focus();
}
function qtFocusOut(id){
  const el = document.getElementById(id); if ( !el ) return;
  el.blur();
}
function qtFocusAfter(id, reset=false){
  const el = document.getElementById(id); if ( !el || el.value===undefined || el.value.length===0 ) return;
  // Focus the input and push cursor after the value to avoid having the value selected (i.e. a simple focus() selects content)
  const value = reset ? '' : el.value;
  el.value = ''; el.focus(); el.value = value;
}
function qtToggle(id='tgl-container', display='block', idctrl='tgl-ctrl', attr='expanded'){
  const cnt = document.getElementById(id); if ( !cnt ) return;
  // change targetted container display
  cnt.style.display = cnt.style.display==='none' ? display : 'none';
  // if attr and idctrl are not empty, adds/removes the class [attr] to the controller (i.e. container is visible/hidden)
  if ( attr!=='' ) {
    const ctrl = document.getElementById(idctrl);
    if ( ctrl ) ctrl.classList.toggle(attr);
  }
}
function qtHrefShow(a) {
  const emails = qtDecodeEmails(a.dataset.emails);
  a.href = 'mailto:' + emails;
  a.title = emails.indexOf(',')<1 ? emails : emails.split(',')[0]+', ...';
}
function qtHrefHide(a){
  a.href = 'javascript:void(0)';
  a.title = '';
}
function qtDecodeEmails(str){
  return str.split('').reverse().join('').replace(/-at-/g,'@').replace(/-dot-/g,'.').replace(',',', ');
}

function qtClipboard(value, doAlert=true, maxSize=255){
  navigator.clipboard.writeText(value);
  if (doAlert) {
   if ( maxSize>0 && value.length>maxSize ) value = value.substring(0,maxSize) + ' ...';
   alert('Copied:\n' + value);
  }
}
function qtHideAfterTable(element, table='t1', inflow=false, rows=5){
  const t = document.getElementById(table);
  const e = document.getElementById(element);
  if ( t && e && t.tBodies[0].rows.length<rows ) inflow ? e.style.visibility = 'hidden' : e.style.display = 'none';
}
function qtAttrStorage(id, key='', attr='aria-current')
{
  const d = document.getElementById(id);
  if ( !d ) { console.log('qtAttrStorage: no element with id='+id); return; }
  if ( key==='' ) key = 'qt-'+id;
  try {
    // Only stores true|false. Defaulf key is qt-{id}
    localStorage.setItem(key, d.getAttribute(attr)==='true' ? 'true' : 'false');
  } catch {
    console.log('qtAttrStorage: localStorage not available'); return;
  }
}
function qtApplyStoredState(casename) {
  let d;
  switch (casename) {
    case 'contrast':
      d = document.getElementById('contrastcss');
      if ( !d ) { console.log('qtApplyStoredState: no element with id=contrastcss'); return; }
      try {
        if ( localStorage.getItem('qt-contrast')==='true' ) { d.removeAttribute('disabled'); return; }
        d.setAttribute('disabled', '');
      } catch {
        console.log('qtApplyStoredState: localStorage not available');
      }
      break;
    case 'aside':
      d = document.getElementById('aside-ctrl');
      if ( !d ) { console.log('qtApplyStoredState: no element with id=aside-ctrl'); return; }
      try {
        const isExpanded = localStorage.getItem('qt-aside')==='true';
        d.classList.toggle('expanded', isExpanded);
        d.setAttribute('aria-current', isExpanded ? 'true' : 'false');
        d = document.getElementById('aside__status'); if (d) d.style.display = isExpanded ? 'none' : 'block';
        d = document.getElementById('aside__info'); if (d) d.style.display = isExpanded ? 'block' : 'none';
        d = document.getElementById('aside__detail'); if (d) d.style.display = isExpanded ? 'block' : 'none';
        d = document.getElementById('aside__legend'); if (d) d.style.display = isExpanded ? 'block' : 'none';
      } catch {
        console.log('qtApplyStoredState: localStorage not available');
      }
      break;
    default:
      console.log('qtApplyStoredState: invalid casename');
  }
}