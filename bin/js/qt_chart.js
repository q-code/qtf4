function iniChartsOptions(){
  if( typeof(Storage)=="undefined" ) return;
  let d = document.getElementById("chartsPercent");
  if ( d ) { let sPC = sessionStorage.getItem("chartsPercent"); d.checked = sPC=="true"; }
  d = document.getElementById("chartsType");
  if ( d ) { let sType = sessionStorage.getItem("chartsType"); if ( sType ) d.value=sType; }
}
function storeChartsOptions(){
  if( typeof(Storage)=="undefined" ) return;
  let d = document.getElementById("chartsPercent");
  if ( d ) sessionStorage.setItem( "chartsPercent", d.checked ? "true" : "false" );
  d = document.getElementById("chartsType");
  if ( d ) sessionStorage.setItem( "chartsType", d.value );
}
function checkY0(year){
  let d = document.getElementById("y0");
  if ( d ) {
    let y = parseInt(year);
    let y0 = parseInt(d.value);
    if ( y0>=y ) d.value = String(y-1);
  }
}
function chartY(ids){
  if ( !Array.isArray(ids) ) ids = [ids];
  var pc = document.getElementById("chartsPercent").checked;
  for (var i=0;i<ids.length;++i) {
    var n = ids[i];
    // use global chart{n} and c{n}
    var chart = window["chart"+n];
    var c = window["chartconf"+n];
    for(j=0;j<c.data.length;++j) chart.data.datasets[j].data = pc ? qtPercent(c.data[j],100,c.stacktotal[j]) : c.data[j];
    chart.options.plugins.title.text = c.title + (pc ? " (%)" : "");
    chart.options.scales.y.max = pc ? 100 : c.maxy;
    chart.update();
  }
}
function makeChart(ids,stacks=[]){
  if ( !Array.isArray(ids) ) ids = [ids];
  if ( !Array.isArray(stacks) ) stacks = [stacks];
  const type = document.getElementById("chartsType").value==="l" ? "line" : "bar";
  let stacked = stacks.length>0 ? true : false;
  for (var i=0;i<ids.length;++i) {
    var n = ids[i];
    var ctx = document.getElementById("chart"+n);
    var c = window["chartconf"+n];
    window["chart"+n] = new Chart(ctx, {
      type: type,
      data: { labels: labels, datasets: makeDatasets(c,stacks) },
      options: {
        scales: {
          y: {
            min: 0,
            max: c.maxy,
            ticks:{precision:0}
          }
        },
        plugins: {
          title: { display:true, text: c.title },
          legend: { display:false }
        }
      }
    });
  }
  chartY(ids,stacked);
}
function makeDatasets(c,stacks=[]){
  if (c.data.length===1) return [{label: c.label[0], data: c.data[0], backgroundColor: "rgba("+c.color[0]+", 0.7)",borderColor: "rgba("+c.color[0]+", 1)", borderWidth: 1}];
  let sets = [];
  for (var i=0;i<c.data.length;++i) {
    if ( i>stacks.length-1 ) stacks[i]="stack"+i;
    if ( i>c.color.length-1 ) c.color.push("22,200,200");
    sets.push( {label: c.label[i], stack: stacks[i], data: c.data[i], backgroundColor: "rgba("+c.color[i]+", 0.7)",borderColor: "rgba("+c.color[i]+", 1)", borderWidth: 1} );
  }
  return sets;
}
function resetCharts(ids,stacks=[]){
  if ( !Array.isArray(ids) ) ids = [ids];
  for (var i=0;i<ids.length;++i){
    if ( i>stacks.length-1 ) stacks[i]="stack"+i;
    var n = ids[i];
    var chart = window["chart"+n];
    var labels = chart.data.labels;
    chart.destroy();
    makeChart(n,stacks);
  }
}
function qtPercent(arr,pc=100,stacktotal=false){
  if ( Number.isInteger(arr) ) arr = [arr];
  let sum = Number.isInteger(stacktotal) ? stacktotal : qtSum(arr);
  if ( sum===0 ) return arr;
  // clone arr and compute %
  var arrP = arr.map((x) => x);
  for (var i=0;i<arrP.length;++i) { if ( (arrP[i])>0 ) arrP[i] = arrP[i]*pc/sum; }
  return arrP;
}
function qtSum(arr){
  if ( Number.isInteger(arr)) arr = [arr];
  for (var i=0;i<arr.length;++i) { if (!Number.isInteger(arr[i])) arr[i]=0; }
  return arr.reduce((a,b) => a + b, 0);
}
function tableFill(tableid,style=true){
  var pc = document.getElementById("tPercent").checked;
  var data = window[tableid+"_data"+(pc ? "_pc" : "")];
  for (var i=0;i<data.length;++i) {
    for(var j=0;j<data[i].length;++j) {
      var cell = document.getElementById( tableid+"-"+i+"-"+j );
      cell.innerHTML = data[i][j];
      if ( style ) tableCellStyle(tableid+"-"+i+"-"+j);
    }
  }
}
function tableCellStyle(cellid,signPos=true,signNeg=false,colorPos="red",colorNeg="green",colorDefault="#444444")
{
  var pc = document.getElementById("tPercent").checked;
  var cell = document.getElementById( cellid );
  var val = Number(cell.innerHTML);
  cell.style.color = colorDefault;
  if ( pc && val!==0 ) cell.innerHTML = cell.innerHTML+"%";
  if ( signPos && val>0 ) cell.innerHTML = "+"+cell.innerHTML;
  if ( signNeg && val<0 ) cell.innerHTML = "-"+cell.innerHTML;
  if ( colorPos && val>0 ) cell.style.color = colorPos;
  if ( colorNeg && val<0 ) cell.style.color = colorNeg;
}