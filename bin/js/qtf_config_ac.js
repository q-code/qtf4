function acUrlConfig(method,value) {
  let s = '';
  let fst = '';
  let dir = '';
  let lang = '';
  switch(method) {
    case 'qkw': break;
    case 'ref':
      if ( document.getElementById('ref-s') ) s = '&s='+document.getElementById('ref-s').value;
      if ( document.getElementById('ref-fst') ) fst = '&fst='+document.getElementById('ref-fst').value;
      break;
    case 'kw':
      if ( document.getElementById('kw-s') ) s = '&s='+document.getElementById('kw-s').value;
      if ( document.getElementById('kw-fst') ) fst = '&fst='+document.getElementById('kw-fst').value;
      break;
    case 'tag-edit':
      if ( document.getElementById('tag-dir') ) dir = '&dir='+document.getElementById('tag-dir').value;
      if ( document.getElementById('tag-lang') ) lang = '&lang='+document.getElementById('tag-lang').value;
      break;
    case 'behalf':
    case 'user':
    case 'userm':
      if ( document.getElementById('user-s') ) s = '&s='+document.getElementById('user-s').value;
      url += s;
      break;
    default: console.log('unknown input method '+method); return;
  }
  return 'bin/srv_query.php?q=' + method + '&fv=' + value + s + fst + lang + dir;
}