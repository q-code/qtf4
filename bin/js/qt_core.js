function qtFocus(id) {
  const e = document.getElementById(id); if ( e ) e.focus();
}
function qtFocusOut(id) {
  const e = document.getElementById(id); if ( e ) e.blur();
}
function qtFocusAfter(id, clear=false) {
  const e = document.getElementById(id); if ( !e || e.value===undefined || e.value.length===0 ) return;
  if ( clear ) { e.value=''; e.focus(); return; }
  // Focus the input and push cursor after the value to avoid having the value selected
  const value = e.value;
  e.value = ''; e.focus(); e.value = value;
}
/** Toggle child(s) between "state1 state2 display|visibilty|data-state|toggle" inside one parent */
function qtToggle(childsSelector='#tgl-container', args='', parentObj='body') {
  const arg = args==='' ? ['block','none','display'] : args.split(' ');
  if ( arg.length===1 ) arg.push('none');
  if ( arg[1]==='toggle') arg.push('toggle');
  if ( arg.length===2 ) arg.push('display');
  if ( arg.length!==3 ) { console.log('qtToggle: arg requires 3 arguments'); return; }
  let parent;
  if ( typeof parentObj==='string' ) {
    parent = parentObj==='' || parentObj==='document' ? document : document.querySelector(parentObj);
    if ( !parent ) { console.log('qtToggle: parent ['+parentObj+'] not found'); return; }
  } else {
    parent = parentObj;
  }
  const childs = parent.querySelectorAll(childsSelector);
  if ( !childs ) { console.log('qtToggle: childs ['+childsSelector+'] not found'); return; }
  childs.forEach( (child)=>{
    switch(arg[2]) {
      case 'display': child.style.display = window.getComputedStyle(child,null).display===arg[0] ? arg[1] : arg[0]; break;
      case 'visibility': child.style.visibility = window.getComputedStyle(child,null).visibility===arg[0] ? arg[1] : arg[0]; break;
      case 'data-state':
      case 'state': child.dataset.state = child.dataset.state===arg[0] ? arg[1] : arg[0]; break;
      case 'toggle': child.classList.toggle(arg[0]); break;
      default: console.log('qtToggle: unknown mode ['+arg[2]+']'); return;
    }
  });
}
/* Simple Alert */
function qtShowAlert(parentContainer,msg,shift='top:-5px;right:-3px') {
  qtHideDlg();
  const box = document.createElement('div');
  box.id = 'cmd-cb-dlg';
  box.style = 'position:absolute;z-index:2;display:flex;gap:0.25rem;align-items:center;'+shift;
  const select = document.createElement('span');
  select.innerText = msg;
  const button = qtXbutton();
  button.onclick = function(){ box.remove(); };
  box.appendChild(select);
  select.after(button);
  parentContainer.style.position = 'relative';
  parentContainer.appendChild(box);
}
function qtHideDlg(id='cmd-cb-dlg') {
  const el = document.getElementById(id);
  if ( el ) el.remove();
}
function qtXbutton() {
  const button = document.createElement('button');
  button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" style="vertical-align:-0.125em" width="0.69em" height="1em" viewBox="0 0 352 512"><path fill="currentColor" d="m242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28L75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256L9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z"></path></svg>';
  button.style = 'padding:1px 4px';
  return button;
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
 * @param {string} element selector-element to hide
 * @param {string} table parent table (id)
 * @param {boolean} inflow TRUE uses visiblity=hidden, FALSE uses display=none
 * @param {number} rows
 */
function qtHideAfterTable(element, table='t1', inflow=false, rows=5) {
  const t = document.getElementById(table);
  const e = document.querySelector(element);
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
      try {
        const isOn = localStorage.getItem('qt-contrast')==='true';
        if ( isOn ) { e.removeAttribute('disabled'); } else { e.setAttribute('disabled', ''); }
        const ctrl = document.getElementById('contrast-ctrl'); if ( !ctrl ) throw new Error('no element with id=contrast-ctrl');
        ctrl.setAttribute('aria-checked', isOn ? 'true' : 'false');
        document.body.setAttribute('data-contrast', isOn ? '1' : '0');
        return;
      } catch {
        console.log('qtApplyStoredState: localStorage not available');
      }
      break;
    case 'aside':
      e = document.getElementById('aside-ctrl'); if ( !e ) throw new Error('no element with id=aside-ctrl');
      try {
        const isOn = localStorage.getItem('qt-aside')==='true';
        e.setAttribute('aria-checked', isOn ? 'true' : 'false');
        const icons = e.querySelectorAll('.tgl-ico');
        if ( icons.length===2 ) {
          icons[0].classList.remove('nodisplay');
          icons[1].classList.remove('nodisplay');
          icons[isOn ? 0 : 1].classList.add('nodisplay');
        }
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
/**
 * Insert bbc tags around selected text
 * @param {string} bbc
 * @param {string} id
 */
function qtBbc(bbc, id='text-area') {
  const e = document.getElementById(id); if ( !e ) return;
  const txtBegin = e.value.substring(0, e.selectionStart);
  const txtEnd = e.value.substring(e.selectionEnd, e.textLength);
  let txtSelected = e.value.substring(e.selectionStart, e.selectionEnd);
  if ( bbc==='img' && txtSelected.length===0 ) txtSelected = '@';
  e.value = txtBegin + "[" + bbc + "]" + txtSelected + "[/" + bbc + "]" + txtEnd;
  e.selectionStart = txtBegin.length;
  e.selectionEnd = (txtBegin + "[" + bbc + "]" + txtSelected + "[/" + bbc + "]").length;
  e.focus();
  e.setSelectionRange(txtBegin.length + bbc.length + 2, txtBegin.length + bbc.length + 2);
}
/**
 * @param {string} selectTD querySelector like '#tableid td.columnclass'
 * @param {string} selectTR querySelector like '#tableid th.columnclass'
 */
function qtHideEmptyColumn(selectTD='#t1 td.c-numid', selectTR='#t1 th.c-numid') {
  let nodes = document.querySelectorAll(selectTD); if ( nodes.length===0 ) return;
  for(const node of nodes) { if ( node.innerHTML!=='' ) return; }
  // Hide empty td-cells then th-cells (even if not empty)
  nodes.forEach( node => { node.style.display = 'none'; } );
  nodes = document.querySelectorAll(selectTR); if ( nodes.length===0 ) return;
  nodes.forEach( node => { node.style.display = 'none'; } );
}
function qtPost(href, params, sep='&')
{
  const form = document.createElement('form');
  form.action = href;
  form.method = 'post';
  for (let param of params.split(sep)) {
    if ( param.length===0 ) continue;
    const field = document.createElement('input');
    field.type = 'hidden';
    param = param.split('=');
    field.name = param[0];
    param.shift(); // after first '=', joins others
    field.value = param.join('=');
    form.appendChild(field);
  }
  document.body.appendChild(form);
  form.submit();
  return false; // preventdefault
}