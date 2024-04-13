// v4.0 build:20240210 allows app impersonation [qt f|i|e|n]
function gmapInfo(marker,info) {
  if ( !marker || !info || info=="" ) return;
  google.maps.event.addListener(marker, "click", function() { gmapInfoBox.setContent(info); gmapInfoBox.open(gmap,marker); });
}
function gmapPan(latlng) {
  if ( !latlng ) return;
  if ( latlng.length==0 ) return;
  if ( gmapInfoBox ) gmapInfoBox.close();
  var yx = latlng.split(",");
  gmap.panTo(new google.maps.LatLng(parseFloat(yx[0]),parseFloat(yx[1])));
}
function gmapRound(num) {
  return Math.round(num*Math.pow(10,11))/Math.pow(10,11);
}
function gmapYXfield(id,marker) {
  const e = document.getElementById(id); if ( !e ) return;
  if ( marker ) {
    e.value = gmapRound(marker.position.lat) + "," + gmapRound(marker.position.lng);
  } else {
    e.value = "";
  }
}