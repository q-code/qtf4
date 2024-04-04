const inputNewname = document.getElementById('newname');
const outputNewname = document.getElementById('newname-error');
const submitNewname = document.getElementById('newname-submit');
const err_used = document.currentScript.dataset['used'] ? document.currentScript.dataset['used'] : 'Already used';
inputNewname.addEventListener('keyup', (e) => {
  submitNewname.disabled = false;
  outputNewname.innerHTML = '';
  if ( inputNewname.value.length>2 ) {
    fetch( `bin/srv_query.php?q=userexists&fv=${inputNewname.value}` )
    .then( response => response.json() )
    .then( data => { if ( data ) { submitNewname.disabled = true; outputNewname.innerHTML = err_used;} } )
    .catch( err => console.log(err) );
  }
});