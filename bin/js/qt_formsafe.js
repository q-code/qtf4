/*
qtFormSafe object allows checking if an input changes in a <form>
in order to ask confirmation before exiting the page.
Note: the <script> can include an attribute data-safemsg with the confirmation question
*/
const qtFormSafe = {
  safe: true,
  not: function(){ this.safe = false; },
  exit: function(msg='Quit without saving?'){ return this.safe || confirm(msg); }
};
// Detect <form> having the class 'formsafe'
const forms = document.querySelectorAll('form.formsafe');
if ( forms.length ) {
  forms.forEach( (form)=>{
    // Select all input/select/textarea to add a 'change' event listener
    const inputs = form.querySelectorAll('input:not([type="hidden"]), select, textarea');
    inputs.forEach( (input)=>{
      input.addEventListener('change', ()=>{ qtFormSafe.not(); });
     } );
  } );
  // Select all anchors in the page to add a 'click' event listener.
  const hrefs = document.querySelectorAll('a:not([href^="javascript:void"])'); // Exclude "javascript:void(0)" (can have specific event).
  const msg = document.currentScript.dataset['safemsg']; // on missing data [undefined] will use fallback confirmation question
  hrefs.forEach( (href)=>{
    href.addEventListener('click', (event)=>{ if ( qtFormSafe.exit(msg) ) return true; event.preventDefault(); return false; });
  } );
}