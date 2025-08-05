/* For media/text blocks */
document.addEventListener('DOMContentLoaded', function() {
	var stylesmoved = false
	function transferMediaTextStyles() {
	  const mediaTextBlocks = document.querySelectorAll('.wp-block-media-text:not(.has-background)');

	  mediaTextBlocks.forEach(block => {
	    const styleAttr = block.getAttribute('style');

	    if (styleAttr) {
	      const styleMap = parseStyleAttribute(styleAttr);

	      // Extract grid-template-columns (if it exists)
	      const gridTemplate = styleMap['grid-template-columns'];
	      const mediaElement = block.querySelector('.wp-block-media-text__media');

	      if (mediaElement) {
	        // Remove grid-template-columns from the media styles
	        delete styleMap['grid-template-columns'];

	        // Apply remaining styles to the media element
	        const mediaStyles = Object.entries(styleMap).map(([key, value]) => `${key}: ${value}`).join('; ');
	        mediaElement.setAttribute('style', mediaStyles);
					stylesmoved = true;
	      }

	      // Keep only the grid-template-columns on the block
	      if (gridTemplate) {
	        block.setAttribute('style', `grid-template-columns: ${gridTemplate};`);
	      } else {
	        block.removeAttribute('style'); // No styles left? Remove the style attribute
	      }
	    }
	  });

	  // Utility function to parse the style attribute into a key-value object
	  function parseStyleAttribute(style) {
	    return style
	      .split(';')
	      .map(decl => decl.trim())
	      .filter(Boolean)
	      .reduce((acc, decl) => {
	        const [key, value] = decl.split(':').map(str => str.trim());
	        if (key && value) {
	          acc[key] = value;
	        }
	        return acc;
	      }, {});
	  }
		
	}
	
	/* Sloppy Javascript  - also this only runs once on page load. would be great for this function to respond to block changes in the backend*/
	transferMediaTextStyles();

	setTimeout(function(){
		if (!stylesmoved){
			transferMediaTextStyles();
		}
  }, 500);
	
});

