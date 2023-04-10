let row; // <tr>
let rowtype; // {s|d} section or domain
let currentorder;
let draggables;
function getDragIds(arr) {
  let ids = [];
  arr.forEach( el => { ids.push(el.dataset.dragid); } );
  return ids.join(";");
}
function dragStart(e){
  row = e.target;
  if ( row.nodeName==="SPAN" ) row = e.target.parentNode.parentNode;
  if ( row.nodeName!=="TR" ) return;
  rowtype = row.dataset.dragid.substring(0,1);
  currentorder = getDragIds(Array.from(row.parentNode.children));
}
function dragOver(e){
  e.preventDefault();
  let rowOver = e.target;
  if ( rowOver.nodeName==="SPAN" ) rowOver = e.target.parentNode.parentNode;
  if ( rowOver.nodeName==="TD" ) rowOver = e.target.parentNode;
  if ( rowOver.nodeName!=="TR" || rowOver.dataset.dragid.substring(0,1)!==rowtype ) return;
  let children = Array.from(rowOver.parentNode.children);
  row.dataset.state="dragging";
  if ( children.indexOf(rowOver)>children.indexOf(row) )
    rowOver.after(row);
  else
    rowOver.before(row);
}
function dragEnd(e){
  let rowOver = e.target;
  if ( rowOver.nodeName==="SPAN" ) rowOver = e.target.parentNode.parentNode;
  if ( rowOver.nodeName==="TD" ) rowOver = e.target.parentNode;
  if ( rowOver.nodeName!=="TR" ) return;
  rowOver.dataset.state="";
  const neworder = getDragIds(Array.from(rowOver.parentNode.children));
  if ( neworder===currentorder ) return;
  document.getElementById("neworder").value = neworder;
  // autosave when dragging a section
  if ( neworder.substring(0,1)==="s" ) document.getElementById("neworder-save").click();
}

// Add event listeners
draggables = document.querySelectorAll('[draggable="true"]');
draggables.forEach( (el)=>{
   el.addEventListener('dragstart', dragStart);
});
draggables = document.querySelectorAll('[data-dragid]');
draggables.forEach( (el)=>{
  el.addEventListener('dragover', dragOver);
  el.addEventListener('dragend', dragEnd);
});