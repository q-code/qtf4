const qtUrlConst = document.currentScript.dataset['url'] ?? false;
if ( qtUrlConst ) {
  let qtUrl = window.location.href;
  window.history.replaceState({},"",qtUrlConst);
  document.onvisibilitychange = ()=>{ window.history.replaceState({},"",qtUrl); };
}