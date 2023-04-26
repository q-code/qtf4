function acSplit(val) { return val.split( ";" ); }
function acExtractLast(term) { return acSplit( term ).pop().replace(/^\s+/g,"").replace(/\s+$/g,""); }
function acInputChange(e) {
  // identify control
  focusInput = e.target;
  multiInput = focusInput.dataset.multi ? true : false;
  // minimum length to trigger auto-complete search is 2, unless a "minlength" value exists in the <input> control
  const minInputLength = focusInput.minLength>0 ? focusInput.minLength : 2;
  // clear and check
  acRemoveDropdown(focusInput.id);
  const value = multiInput ? acExtractLast(e.target.value.toLowerCase()) : e.target.value.toLowerCase();
  if ( value.length<minInputLength ) return;
  // query the url build by the function acUrlConfig(method,value) [ must be defined in the application! ] Note: here the method is just the id of the input
  fetch( acUrlConfig(focusInput.id,value) )
  .then( response => response.json() )
  .then( data => {
    acCreateDropdown(data);
    } )
  .catch( err => console.log(err) );
}
function acButtonClick(e) {
  e.preventDefault();
  const btn = e.target.nodeName=='SPAN' ? e.target.parentNode : e.target;
  if ( multiInput ) {
    let terms = acSplit(focusInput.value);
    terms.pop(); // remove current input
    terms.push(btn.value); // add the selected item
    terms.push(''); // add placeholder to get the comma-and-space at the end
    focusInput.value = terms.join(';');
  } else {
    focusInput.value = btn.value;
  }
  if ( typeof acOnClicks==='object' && Object.keys(acOnClicks).includes(focusInput.id) ) acOnClicks[focusInput.id](focusInput,btn);
  acRemoveDropdown(focusInput.id);
  focusInput.focus();
}
function acRemoveDropdown(id) {
  const listEl = document.getElementById('ac-list-'+id);
  if ( listEl ) listEl.remove();
}
function acCreateDropdown(responses) {
  const drop = document.createElement('ul'); drop.className = 'ac-list'; drop.id = 'ac-list-'+focusInput.id;
  responses.forEach( (response) => {
    const buttons = document.createElement('li');
    const button = document.createElement('button');
    button.setAttribute('data-id',response.hasOwnProperty('rId') ? response.rId  : '');
    button.value = response.rSelect ? response.rSelect : response.rItem;
    button.className = 'li-button';
    const sep = response.rItem==='' || response.rItem.endsWith(' ') || response.rInfo==='' ? '' : ' &middot; ';
    button.innerHTML = `<span class="jvalue">${response.rItem}</span>${sep}<span class="jinfo">${response.rInfo}</span>`;
    button.addEventListener('click', acButtonClick);
    buttons.appendChild(button);
    drop.appendChild(buttons);
  });
  document.getElementById('ac-wrapper-'+focusInput.id).appendChild(drop);
}

let focusInput = null;
let multiInput = false;
const wrappers = document.querySelectorAll(".ac-wrapper");
wrappers.forEach( (wrapper) => {
  const id = wrapper.id.substring(11);
  const input = document.getElementById(id);
  if ( input ) {
    input.addEventListener('input', acInputChange);
    input.addEventListener('focusout', (e) => {
      if ( e.relatedTarget && e.relatedTarget.className=='li-button' ) return;
      acRemoveDropdown(id);
    });
  }
} );