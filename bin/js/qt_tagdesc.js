const tag_dir = document.getElementById('tagdesc').getAttribute('data-dir');
const tag_lang = document.getElementById('tagdesc').getAttribute('data-lang');
const tag_s = document.getElementById('tagdesc').getAttribute('data-s');
const tag_cs = document.getElementById('tagdesc').getAttribute('data-cs')=='1' ? '&cs' : ''; //cross-sections search
const tags = document.querySelectorAll('[data-tagdesc]');
tags.forEach( tag => {
  tag.addEventListener('mouseover', () => {
    const thistag = tag.dataset.tagdesc; if ( thistag==='' ) return;
    fetch( `bin/srv_tagdesc.php?s=${tag_s}&fv=${thistag}&src=../${tag_dir}tags_${tag_lang}&lang=${tag_lang}${tag_cs}` )
    .then( response => response.text() )
    .then( data => {
      const siblings = document.querySelectorAll('[data-tagdesc="'+thistag+'"]');
      siblings.forEach( sibling => { sibling.title = thistag+' ('+data+')'; sibling.dataset.tagdesc=''; } );
      } )
    .catch( err => console.log(err) );
  }, {once:true} );
});