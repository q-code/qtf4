/**
 * @param {string} bbc
 * @param {string} id
 */
function qtCaret(bbc, id='text-area') {
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