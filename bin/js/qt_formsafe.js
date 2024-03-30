/*
The object qtFormSafe allows checking if form input(s) has changed to ask confirmation before exiting the page
This applied to forms having a class "formsafe" (and pages having this script)
*/
const qtFormSafe = {
  safe: true,
  not: function(){ this.safe = false; },
  exit: function(msg='Quit without saving?'){ return this.safe || confirm(msg); }
};
// select from .formsafe all input and select
const forms = document.querySelectorAll('.formsafe');
forms.forEach( (form)=>{
  const inputs = document.querySelectorAll('.formsafe input:not([type="hidden"]), .formsafe select');
  inputs.forEach( (input)=>{
    input.addEventListener('change', ()=>{ qtFormSafe.not(); });
  } );
} );
// select all anchor in the page
if ( forms.length ) {
  const safemsg = document.currentScript.dataset['safemsg']; // [undefined] if missing (fallback to default msg)
  const hrefs = document.querySelectorAll('a[href]:not(.active)');
  hrefs.forEach( (href)=>{
    href.addEventListener('click', (event)=>{ if (qtFormSafe.exit(safemsg)) return true; event.preventDefault(); return false; });
  } );
}