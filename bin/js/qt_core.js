const qtFormSafe = {
  saved: true,
  not  : function(){ this.saved=false; },
  exit : function(msg='Data not yet saved. Quit without saving?'){
    if ( !this.saved && !confirm(msg) ) return false;
    return true;
    }
};
function qtFocus(id) {
  const el = document.getElementById(id); if ( !el ) return;
  el.focus();
}
function qtFocusOut(id) {
  const el = document.getElementById(id); if ( !el ) return;
  el.blur();
}
function qtFocusAfter(id) {
  const el = document.getElementById(id); if ( !el || el.value===undefined || el.value.length===0 ) return;
  // Focus the input and push cursor after the value to avoid having the value selected (i.e. a simple focus() selects content)
  const value = el.value;
  el.value = ''; el.focus(); el.value = value;
}
function qtToggle(id='tgl-container', display='block', idctrl='tgl-ctrl', attr='expanded') {
  const cnt = document.getElementById(id); if ( !cnt ) return;
  cnt.style.display = cnt.style.display==='none' ? display : 'none';
  // if attr and idctrl are not empty, adds/removes the class [attr] to the controller (i.e. container is visible/hidden)
  if ( attr!=='' ) {
    const ctrl = document.getElementById(idctrl);
    if ( ctrl ) ctrl.classList.toggle(attr);
  }
}
function qtEmailShow(a) {
  const emails = qtDecodeEmails(a.dataset.emails, ',');
  a.href = 'mailto:' + emails;
  a.title = emails.indexOf(',')<1 ? emails : emails.split(',')[0]+', ...';
}
function qtEmailHide(a) {
  a.href = 'javascript:void(0)';
  a.title = '';
}
function qtDecodeEmails(str, sep=', ') {
  return str.split('').reverse().join('').replace(/-at-/g,'@').replace(/-dot-/g,'.').replace(',',sep);
}
function qtClipboard(value, doAlert=true, maxSize=255) {
  navigator.clipboard.writeText(value);
  if (doAlert) {
   if ( maxSize>0 && value.length>maxSize ) value = value.substring(0,maxSize) + ' ...';
   alert('Copied:\n' + value);
  }
}
function qtHideAfterTable(element, table='t1', inflow=false, rows=5) {
  const t = document.getElementById(table);
  const e = document.getElementById(element);
  if ( t && e && t.rows.length<rows ) inflow ? e.style.visibility = 'hidden' : e.style.display = 'none';
}
/**
 * Puts true|false in localstorage
 * @param {string} id
 * @param {string} key if missing, uses qt-id
 * @param {string} attr attribute (default is aria-checked value)
 */
function qtAttrStorage(id, key='', attr='aria-checked') {
  const d = document.getElementById(id); if ( !d ) throw new Error('qtAttrStorage: no element with id='+id);
  if ( key==='' ) key = 'qt-'+id;
  try {
    localStorage.setItem(key, d.getAttribute(attr)); // Stores true|false
  } catch {
    console.log('qtAttrStorage: localStorage not available'); return;
  }
}

/**
 * @param {string} casename 'aside' or 'contrastcss'
 */
function qtApplyStoredState(casename) {
  let d;
  switch (casename) {
    case 'contrast':
      d = document.getElementById('contrastcss'); if ( !d ) throw new Error('no element with id=contrastcss');
      // caution d is the <link> stylesheet, not the control
      try {
        const isOn = localStorage.getItem('qt-contrast')==='true';
        if ( isOn ) { d.removeAttribute('disabled'); } else { d.setAttribute('disabled', ''); }
        const ctrl = document.getElementById('contrast-ctrl'); if ( !ctrl ) throw new Error('no element with id=contrast-ctrl');
        ctrl.setAttribute('aria-checked', isOn ? 'true' : 'false');
        return;
      } catch {
        console.log('qtApplyStoredState: localStorage not available');
      }
      break;
    case 'aside':
      d = document.getElementById('aside-ctrl'); if ( !d ) throw new Error('no element with id=aside-ctrl');
      try {
        const isOn = localStorage.getItem('qt-aside')==='true';
        d.classList.toggle('expanded', isOn);
        d.setAttribute('aria-checked', isOn ? 'true' : 'false');
        d = document.getElementById('aside__status'); if (d) d.style.display = isOn ? 'none' : 'block';
        d = document.getElementById('aside__info'); if (d) d.style.display = isOn ? 'block' : 'none';
        d = document.getElementById('aside__detail'); if (d) d.style.display = isOn ? 'block' : 'none';
        d = document.getElementById('aside__legend'); if (d) d.style.display = isOn ? 'block' : 'none';
      } catch {
        console.log('qtApplyStoredState: localStorage not available');
      }
      break;
    default:
      throw new Error('invalid casename');
  }
}