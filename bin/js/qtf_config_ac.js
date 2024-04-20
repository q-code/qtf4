function acUrlConfig(method,value) {
  let s = '';
  let fs = '';
  let dir = '';
  let lang = '';
  switch(method) {
    case 'qkw': break;
    case 'ref':
      if ( document.getElementById('ref-s') ) s = '&s='+document.getElementById('ref-s').value;
      if ( document.getElementById('ref-fs') ) fs = '&fs='+document.getElementById('ref-fs').value;
      break;
    case 'kw':
      if ( document.getElementById('kw-s') ) s = '&s='+document.getElementById('kw-s').value;
      if ( document.getElementById('kw-fs') ) fs = '&fs='+document.getElementById('kw-fs').value;
      break;
    case 'tag-edit':
      if ( document.getElementById('tag-dir') ) dir = '&dir='+document.getElementById('tag-dir').value;
      if ( document.getElementById('tag-lang') ) lang = '&lang='+document.getElementById('tag-lang').value;
      break;
    case 'behalf':
    case 'user':
    case 'userm':
      if ( document.getElementById('user-s') ) s = '&s='+document.getElementById('user-s').value;
      break;
    default: console.log('unknown input method '+method); return;
  }
  return 'bin/srv_query.php?q=' + method + '&fv=' + value + s + fs + lang + dir;
}