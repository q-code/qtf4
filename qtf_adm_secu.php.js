function regsafeChanged(str) {
  document.getElementById("recaptcha2").style.display = str==="recaptcha2" ? "table-row" : "none";
  document.getElementById("recaptcha3").style.display = str==="recaptcha3" ? "table-row" : "none";
}
function toggleParams(id,status) {
  document.getElementById(id+"-params").style.display = status==="0" ? "none" : "inline-block";
}