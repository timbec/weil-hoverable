/********************************
 * Simple Lightbox
 * 1.4.1.8
 *
 *******************************/

// I'd have to create a way to cycle through all the elements on a page. However this might not work with an external modal. 
//Some mother examples: http://www.ericmmartin.com/projects/simplemodal/, http://flaviusmatis.github.io/easyModal.js/. Or just use facebox.js and update to modern jquery standards. Don't want to make this a pain. Or hell: W3Schools: http://www.w3schools.com/howto/howto_css_modals.asp. However, not sure how this would work with multiple modals on a page. Maybe if you just cycled through them all? Again, don't want to spend excessive time on this. 

var body = document.querySelector( 'body' ),
    lightboxDemo = document.getElementById( 'lightbox-demo' ),
    lightboxLinks = document.querySelectorAll( 'a.lightbox' ),
    wapuuLink = lightboxLinks[0],
    overlay = document.createElement( 'div' ),
    overlayCloseLink = document.createElement( 'a' ),
    overlayCloseText = document.createTextNode( 'X' ),
    displayOverlay,
    openLightbox,
    closeLightBox,
    addImageToOverlay;

closeLightBox = function closeLightBox( e ) {

  e.preventDefault();
  overlayCloseLink.removeEventListener( 'click', closeLightBox, false );
  overlay.querySelector( 'img' ).remove();
  overlay.remove();

};

displayOverlay = function displayOverlay() {

  overlay.setAttribute( 'id', 'overlay'  );
  overlayCloseLink.appendChild( overlayCloseText );
  overlayCloseLink.setAttribute( 'href', '#' );
  overlayCloseLink.classList.add( 'close' );
  overlayCloseLink.addEventListener( 'click', closeLightBox, false );

  overlay.appendChild( overlayCloseLink );
  body.appendChild( overlay );
  //console.log( 'here' );

};

addImageToOverlay = function addImageToOverlay( img ) {

  overlay.appendChild( img )

}

openLightbox = function openLightbox( e ) {

  e.preventDefault();
  displayOverlay();
  addImageToOverlay( e.target.cloneNode() );

};



wapuuLink.addEventListener( 'click', openLightbox );

lightboxDemo.style.display = 'block';