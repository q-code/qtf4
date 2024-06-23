// Focus
qtFocusAfter("kw");
["ref","tag-edit","user"].forEach( id => {
  if ( document.getElementById(id) && document.getElementById(id).value.length>0 ) qtFocusAfter(id);
});

// Search options (for each SELECT changed)
const optionsEl = document.getElementById("broadcasted-options");
optionsEl.addEventListener("change", (e)=>{
  e.stopPropagation();
  if ( e.target.tagName==="SELECT") broadcastOption(e.target.name,e.target.value);
});
function iconSpin() {
  const icon = document.getElementById("opt-icon");
  icon.classList.remove("spinning");
  if (document.getElementById("opt-s").value!=="-1" || document.getElementById("opt-fs").value!=="") icon.classList.add("spinning");
}
function broadcastOption(option,value) {
  ["ref-","id-","kw-","adv-","user-"].forEach( id => {
     if ( document.getElementById(id+option) ) document.getElementById(id+option).setAttribute("value", value);
  });
  ["btn_recent","btn_news","btn_my"].forEach( id => {
    if ( document.getElementById(id) ) document.getElementById(id).setAttribute("data-"+option, value);
  });
  iconSpin();
}
function addHrefData(d, args) {
  for(const arg of args) {
    if ( d.dataset[arg]==="" || d.dataset[arg]===undefined ) continue;
    d.href += "&"+arg+"="+d.dataset[arg];
  }
}
// Specific autocomplete-click, requires acOnclicks exist (must be VAR to be global)
if ( typeof acOnClicks==="undefined" ) { var acOnClicks = []; }
acOnClicks["ref"] = function(focusInput,btn) {
  if ( focusInput.id=="ref" && focusInput.value.substring(0,1)=="#") window.location="qtf_item.php?t="+focusInput.value.substring(1);
}
acOnClicks["user"] = function(focusInput,btn) {
  if ( focusInput.id==="user" ) document.getElementById("userid").value = btn.dataset.id;
}