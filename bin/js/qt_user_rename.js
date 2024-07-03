const inputRename = document.getElementById('rename');
const outputRename = document.getElementById('rename-error');
const submitRename = document.getElementById('rename-submit');
const err_used = document.currentScript.dataset['used'] ?? 'Already used';
inputRename.addEventListener('keyup', (e) => {
  submitRename.disabled = false;
  outputRename.innerHTML = '';
  if ( inputRename.value.length>2 ) {
    fetch( `bin/srv_query.php?q=userexists&fv=${inputRename.value}` )
    .then( response => response.json() )
    .then( data => { if ( data ) {
      submitRename.disabled = true;
      outputRename.innerHTML = `<span class="error">${err_used}</span>`;
    } } )
    .catch( err => console.log(err) );
  }
});