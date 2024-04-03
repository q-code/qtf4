/*
Event mouseover calls bin/srv_tagdesc.php:
Attribute data-tagdesc must contain the searched tag
When found, the description is transfered to the title while the data-tagdesc is cleared.
Other elements in the document having the same tagdesc are also changed
*/
const tags = document.querySelectorAll('[data-tagdesc]');
if ( tags.length>0 ) {
  const tag_dir = document.currentScript.dataset['dir'];
  const tag_lang = document.currentScript.dataset['lang'];
  const tag_s = document.currentScript.dataset['s'];
  const tag_cs = document.currentScript.dataset['cs']; // cross-sections search
  const uri = ( tag_s ? '&s='+tag_s : '' ) + ( tag_cs ? '&cs=1' : '' ) + ( tag_dir ? '&dir='+tag_dir : '' ) + ( tag_lang ? '&lang='+tag_lang : '' );
  tags.forEach( tag => {
    tag.addEventListener('mouseover', () => {
      const fv = tag.dataset.tagdesc; if ( fv==='' ) return; // already described
      fetch( 'bin/srv_tagdesc.php?fv='+fv+uri )
      .then( response => response.text() )
      .then( data => {
        const siblings = document.querySelectorAll('[data-tagdesc="'+fv+'"]');
        siblings.forEach( sibling => { sibling.title = fv+' ('+data+')'; sibling.dataset.tagdesc = ''; } );
        } )
      .catch( err => console.log(err) );
    }, {once:true} );
  });
}