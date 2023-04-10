function tagAdd(diff=false,idOld='tag-saved',idNew='tag-new',idShown='tag-shown',idEdit='tag-edit'){
  const iniTags = document.getElementById(idNew).value.toLowerCase().split(';');
  let d = document.getElementById(idEdit);
  let allTags = [];
  if ( d && d.value.length>0 ) {
    if ( iniTags.length>0 ) allTags = iniTags;
    let newTags = d.value.trim().replace(/"/g,'').toLowerCase().split(';');
    newTags.forEach(function(item){
      if ( item.length>0 && item!=='*' && item!==' ' && !allTags.includes(item) ) allTags.unshift(item);
      });
    document.getElementById(idNew).value = allTags.join(';');
  }
  if ( diff ) { tagRenderDiff(idShown,idOld,idNew); } else { tagRender(idShown); }
  tagReset(idOld,idNew,idEdit);
  return false;
}
function tagDel(diff=false,idOld='tag-saved',idNew='tag-new',idShown='tag-shown',idEdit='tag-edit'){
  const iniTags = document.getElementById(idNew).value.toLowerCase().split(';');
  let d = document.getElementById(idEdit);
  if ( d && d.value.length>0 ) {
    if ( d.value.trim()=='*' ) {
      document.getElementById(idNew).value = '';
    } else {
      let newTags = d.value.trim().replace(/"/g,'').toLowerCase().split(';');
      let allTags = iniTags;
      newTags.forEach(function(item){
        if ( item.length>0 && item!==' ' && item!=='*' ){
          let i = allTags.indexOf(item);
          allTags.splice(i,1);
          }
        });
      document.getElementById(idNew).value = allTags.join(';');
    }
  }
  if ( diff ) { tagRenderDiff(idShown,idOld,idNew); } else { tagRender(idShown); }
  tagReset(idOld,idNew,idEdit);
  return false;
}
function tagCancel(idOld='tag-saved',idNew='tag-new',idShown='tag-shown',idEdit='tag-edit'){
  const d = document.getElementById(idShown);
  const dOld = document.getElementById(idOld);
  const dNew = document.getElementById(idNew);
  if ( d && dOld && dNew ){
    dNew.value = dOld.value;
    tagRender(idShown);
    tagReset(idOld,idNew,idEdit);
  }
}
function tagReset(idOld='tag-saved',idNew='tag-new',idEdit='tag-edit'){
  if ( document.getElementById(idEdit) ) document.getElementById(idEdit).value='';
  const dOld = document.getElementById(idOld);
  const dNew = document.getElementById(idNew);
  if ( dOld && dNew ){
    let unchanged = dOld.value==dNew.value;
    if ( document.getElementById('tag-save') ) { document.getElementById('tag-save').disabled=unchanged; if ( !unchanged ) document.getElementById('tag-save').focus(); }
    if ( document.getElementById('tag-cancel') ) document.getElementById('tag-cancel').disabled=unchanged;
  }
}
function tagClick(item,idEdit='tag-edit'){
  let d = document.getElementById(idEdit); if ( !d ) return;
  if ( d.value.trim()==='' ) { d.value = item; return; }
  // handled multiple tags
  multiInput = d.dataset.multi ? true : false;
  const sep = d.value.trim().endsWith(';') ? '' : ';';
  d.value = multiInput ? (d.value + sep + item) : item;
}
function tagRender(idShown='tag-shown'){
  let d = document.getElementById('tag-new');
  let newTags = [];
  if ( d && d.value.length>0 ){
    let allTags = document.getElementById('tag-new').value.toLowerCase().split(';');
    allTags.forEach(function(item){
      if ( item.length>0 && item!==' ' && item!=='*') newTags.push( '<span class="tag" onclick="tagClick(this.innerHTML)">' + item + '</span>' );
    });
  }
  document.getElementById(idShown).innerHTML = newTags.join(' ');
}
function tagRenderDiff(idShown='tag-shown',idOld='tag-saved',idNew='tag-new'){
  const d = document.getElementById(idShown);
  const dOld = document.getElementById(idOld);
  const dNew = document.getElementById(idNew);
  if ( d && dOld && dNew ){
    let tagsOld = dOld.value.trim().toLowerCase().split(';');
    let tagsNew = dNew.value.trim().toLowerCase().split(';');
    let tags = [];
    tagsNew.forEach(function(item){
      if ( item.length>0 && item!==' ' && item!=='*'){
        tags.push( '<span class="tag' + (tagsOld.includes(item) ? '' : ' added') + '" onclick="tagClick(this.innerHTML)">' + item + '</span>' );
      }
    });
    tagsOld.forEach(function(item){
      if ( item.length>0 && item!==' ' && item!=='*'){
        if ( !tagsNew.includes(item) ) tags.push( '<span class="tag deleted" onclick="tagClick(this.innerHTML)">' + item + '</span>' );
      }
    });
    d.innerHTML = tags.join(' ');
  }
}