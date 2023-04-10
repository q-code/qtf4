function toggleHome(status) {
  document.getElementById("home_name").disabled = status==="0";
  document.getElementById("home_url").disabled = status==="0";
  document.getElementById("home_url_help").style.display = status==="0" ? "none" : "table-row";
}
function toggleCustomCss(id,css) {
  if ( css==="" ) return;
  const d = document.getElementById("custom-css");
  if ( d ) d.style.display = id===css ? "inline" : "none";
}