function qtCaret(bbc, area='text') {
  const el = document.getElementById(area); if ( !el ) return;
  const txtBegin = el.value.substring(0, el.selectionStart);
  const txtEnd = el.value.substring(el.selectionEnd, el.textLength);
  let txtSelected = el.value.substring(el.selectionStart, el.selectionEnd);
  if ( bbc==='img' && txtSelected.length===0 ) txtSelected = '@';
  el.value = txtBegin + "[" + bbc + "]" + txtSelected + "[/" + bbc + "]" + txtEnd;
  el.selectionStart = txtBegin.length;
  el.selectionEnd = (txtBegin + "[" + bbc + "]" + txtSelected + "[/" + bbc + "]").length;
  el.focus();
  el.setSelectionRange(txtBegin.length + bbc.length + 2, txtBegin.length + bbc.length + 2);
}
