/*
This adds a mouseover-event (calling srv_tagdesc.php) to elements having attribute [data-tagdesc].
[data-tagdesc] must contain the searched tag. When the description is received, it goes in [title] while [data-tagdesc] is cleared.
Other elements having the same [data-tagdesc] are also changed.
TIPS: This script supports OPTIONS as attribute data-{dir|lang|s|xs|sep|ci|na}
*/
const tags = document.querySelectorAll('[data-tagdesc]');
if ( tags.length>0 ) {

const cs_dir = document.currentScript.dataset['dir']; // default repository of csv-files
const cs_lang = document.currentScript.dataset['lang']; // current langue (iso)
const cs_s = document.currentScript.dataset['s']; // section id
const cs_xs = document.currentScript.dataset['xs']; // cross-sections search (default:true)
const cs_sep = document.currentScript.dataset['sep']; // tag-description separator
const cs_ci = document.currentScript.dataset['ci']; // case insensitive (default:true)
const cs_na = document.currentScript.dataset['na']; // show 'no description' (default:false)
const cs_args = ( cs_s ? '&s='+cs_s : '' ) + ( cs_xs==='0' ? '&xs=0' : '' ) + ( cs_dir ? '&dir='+cs_dir : '' ) + ( cs_lang ? '&lang='+cs_lang : '' ) + ( cs_sep ? '&sep='+encodeURI(cs_sep) : '' ) + ( cs_ci==='0' ? '&ci=0' : '' ) + ( cs_na==='1' ? '&na=1' : '' );
tags.forEach( tag => {
  tag.addEventListener('mouseover', () => {
    const fv = tag.dataset.tagdesc; if ( fv==='' ) return; // already described
    fetch( 'bin/srv_tagdesc.php?fv='+encodeURI(fv)+cs_args )
    .then( response => response.text() )
    .then( desc => {
      const siblings = document.querySelectorAll('[data-tagdesc="'+fv+'"]');
      siblings.forEach( sibling => { sibling.title = fv+desc; sibling.dataset.tagdesc = ''; } );
      } )
    .catch( err => console.log(err) );
  }, {once:true} );
});

}