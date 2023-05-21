/**
 * @constant {object} qtFormSafe - form initial value controller
 */
const qtFormSafe = {
  initial: true,
  not: function(){ this.initial=false; },
  exit: function(msg='Data not yet saved. Quit without saving?'){ if ( !this.initial && !confirm(msg) ) return false; return true; }
};
/**
 * @param {string} id
 */
function qtFocus(id) {
  const e = document.getElementById(id); if ( !e ) return;
  e.focus();
}
/**
 * @param {string} id
 */
function qtFocusOut(id) {
  const e = document.getElementById(id); if ( !e ) return;
  e.blur();
}
/**
 * @param {string} id
 */
function qtFocusAfter(id) {
  const e = document.getElementById(id); if ( !e || e.value===undefined || e.value.length===0 ) return;
  // Focus the input and push cursor after the value to avoid having the value selected
  const value = e.value;
  e.value = ''; e.focus(); e.value = value;
}
/**
 * @param {string} id
 * @param {string} display
 * @param {string} idctrl
 * @param {string} attr
 */
function qtToggle(id='tgl-container', display='block', idctrl='tgl-ctrl', attr='expanded') {
  const e = document.getElementById(id); if ( !e ) return;
  e.style.display = e.style.display==='none' ? display : 'none';
  // if attr and idctrl are not empty, adds/removes the class [attr] to the controller (i.e. container is visible/hidden)
  if ( attr!=='' ) {
    const ctrl = document.getElementById(idctrl);
    if ( ctrl ) ctrl.classList.toggle(attr);
  }
}
/**
 * @param {HTMLAnchorElement} a
 */
function qtEmailShow(a) {
  const emails = qtDecodeEmails(a.dataset.emails, ',');
  a.href = 'mailto:' + emails;
  a.title = emails.indexOf(',')<1 ? emails : emails.split(',')[0]+', ...';
}
/**
 * @param {HTMLAnchorElement} a
 */
function qtEmailHide(a) {
  a.href = 'javascript:void(0)';
  a.title = '';
}
/**
 * @param {string} str
 * @param {string} sep
 * @returns {string}
 */
function qtDecodeEmails(str, sep=', ') {
  return str.split('').reverse().join('').replace(/-at-/g,'@').replace(/-dot-/g,'.').replace(',',sep);
}
/**
 * @param {string} value
 * @param {boolean} useAlert
 * @param {number} maxSize
 */
function qtClipboard(value, useAlert=true, maxSize=255) {
  navigator.clipboard.writeText(value);
  if ( useAlert ) {
   if ( maxSize>0 && value.length>maxSize ) value = value.substring(0,maxSize) + ' ...';
   alert('Copied:\n' + value);
  }
}
/**
 * Hide an element when a table row count is less than [rows]
 * @param {string} element element (id) to hide
 * @param {string} table parent table (id)
 * @param {boolean} inflow TRUE applies visiblity=hidden, FALSE applies display=none
 * @param {number} rows
 */
function qtHideAfterTable(element, table='t1', inflow=false, rows=5) {
  const t = document.getElementById(table);
  const e = document.getElementById(element);
  if ( t && e && t.rows.length<rows ) inflow ? e.style.visibility = 'hidden' : e.style.display = 'none';
}
/**
 * Puts an attribute-value in localstorage
 * @param {string} id
 * @param {string} key uses 'qt-id' if empty
 * @param {string} attr
 */
function qtAttrStorage(id, key='', attr='aria-checked') {
  const e = document.getElementById(id); if ( !e ) throw new Error('qtAttrStorage: no element with id='+id);
  if ( key==='' ) key = 'qt-'+id;
  try {
    localStorage.setItem(key, e.getAttribute(attr));
  } catch {
    console.log('qtAttrStorage: localStorage not available'); return;
  }
}
/**
 * @param {string} casename 'aside' or 'contrastcss'
 */
function qtApplyStoredState(casename) {
  let e;
  switch (casename) {
    case 'contrast':
      e = document.getElementById('contrastcss'); if ( !e ) throw new Error('no element with id=contrastcss');
      // caution d is the <link> stylesheet, not the control
      try {
        const isOn = localStorage.getItem('qt-contrast')==='true';
        if ( isOn ) { e.removeAttribute('disabled'); } else { e.setAttribute('disabled', ''); }
        const ctrl = document.getElementById('contrast-ctrl'); if ( !ctrl ) throw new Error('no element with id=contrast-ctrl');
        ctrl.setAttribute('aria-checked', isOn ? 'true' : 'false');
        return;
      } catch {
        console.log('qtApplyStoredState: localStorage not available');
      }
      break;
    case 'aside':
      e = document.getElementById('aside-ctrl'); if ( !e ) throw new Error('no element with id=aside-ctrl');
      try {
        const isOn = localStorage.getItem('qt-aside')==='true';
        e.classList.toggle('expanded', isOn);
        e.setAttribute('aria-checked', isOn ? 'true' : 'false');
        e = document.getElementById('aside__status'); if (e) e.style.display = isOn ? 'none' : 'block';
        e = document.getElementById('aside__info'); if (e) e.style.display = isOn ? 'block' : 'none';
        e = document.getElementById('aside__detail'); if (e) e.style.display = isOn ? 'block' : 'none';
        e = document.getElementById('aside__legend'); if (e) e.style.display = isOn ? 'block' : 'none';
      } catch {
        console.log('qtApplyStoredState: localStorage not available');
      }
      break;
    default:
      throw new Error('invalid casename');
  }
}