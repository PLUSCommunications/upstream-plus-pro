<?php
  $classes = "alignfull has-global-padding pro-navbar-container";
  $styles = "";
  switch (get_field('behavior')) {
    case 'static':
      $classes.= " is-position-static";
      break;
    case 'sticky':
      $classes.= " is-position-sticky";
      break;
    case 'sticky-auto':
      $classes.= " is-position-sticky is-position-sticky--auto";
      break;
  
    default:
      # should never happen
      break;
  }
  if( get_field('overlay') == 1 ){
    $classes.= " has-property-overlay";
    $overlay_background = get_field('overlay_background') ? : "rgba(255,255,255,0)";
    $overlay_text = get_field('overlay_text') ? : "#fff";
    $styles.= "--pro-overlay-background:$overlay_background;--pro-overlay-text:$overlay_text;";
  }
?>
<div <?php echo ($is_preview ? ' ' : get_block_wrapper_attributes(['class' => $classes, 'style' => $styles])) ?>>

  <InnerBlocks />

</div>

<script>
	
	
	function watchElementHeight(element, callback) {

	    // Create a new ResizeObserver instance
	    const resizeObserver = new ResizeObserver(entries => {
	        for (let entry of entries) {
						if ( entry.target.classList.contains('has-property-overlay') && !entry.target.parentElement.classList.contains('scrolled') ){
							callback(entry.borderBoxSize[0].blockSize + entry.target.firstElementChild.clientHeight);
						} else {
							callback(entry.borderBoxSize[0].blockSize);
						}
	        	
	        }
	    });

	    // Start observing the element
	    resizeObserver.observe(element);

	    // Return a function to stop observing when needed
	    return () => resizeObserver.unobserve(element);
	}

	// Example usage:
	const element = document.querySelector(".pro-navbar-container");

	const stopObserving = watchElementHeight(element, newHeight => {
	    const root = document.documentElement;
			
			root.style.setProperty('--pro-navbar-container--height', newHeight+'px');
	});

	// To stop observing later, call:
	// stopObserving();
	
</script>
