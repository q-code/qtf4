function clickRowCmd(checkboxname,action)
{
  const checkboxes = document.getElementsByName(checkboxname);
  let n = 0;
  for (let i=0; i<checkboxes.length; ++i) if ( checkboxes[i].checked ) ++n;
  if ( n>0 ) {
    document.getElementById("form-users-action").value=action;
    document.getElementById("form-users").submit();
  } else {
    alert("'.L('Nothing_selected').'");
  }
  return false;
}
for (const el of document.getElementsByClassName("checkboxcmds")) {
  el.addEventListener("click", (e)=>{
    if ( e.target.tagName==="A" ) clickRowCmd("t1-cb[]", e.target.dataset.action);
  });
}
qtHideAfterTable("tablebot");