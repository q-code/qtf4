/* Use localStorage (requires try/catch) to save contrast mode */
function toggleContrast() {
  const d = document.getElementById('contrastcss'); if ( !d ) return;
  d.toggleAttribute('disabled');
  storeContrastState();
}
function storeContrastState() {
  const d = document.getElementById('contrastcss'); if ( !d ) return;
  try {
    localStorage.setItem('qtf4_contrast', d.disabled ? 'off' : 'on');
  } catch {
    console.log('localStorage not available'); return;
  }
}
function applyContrast() {
  const d = document.getElementById('contrastcss'); if ( !d ) return;
  try {
    if ( localStorage.getItem('qtf4_contrast')==='on' ) { d.removeAttribute('disabled'); return; }
    d.setAttribute('disabled', '');
  } catch {
    console.log('localStorage not available'); return;
  }
}