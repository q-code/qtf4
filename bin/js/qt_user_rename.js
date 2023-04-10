const inputNewname = document.getElementById('newname');
const outputNewname = document.getElementById('newname-error');
const submitNewname = document.getElementById('newname-submit');
inputNewname.addEventListener('keyup', (e) => {
  submitNewname.disabled = false;
  outputNewname.innerHTML = '';
  if ( inputNewname.value.length>2 ) {
    fetch( `bin/srv_query.php?q=userexists&v=${inputNewname.value}` )
    .then( response => response.json() )
    .then( data => { if ( data ) { submitNewname.disabled = true; outputNewname.innerHTML = w_already_used;} } )
    .catch( err => console.log(err) );
  }
});