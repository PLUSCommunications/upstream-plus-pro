<?php global $frwk_theme;
  
  // Classes
    $classes = [
      "pro-button--icon-".get_field('icon_side'),
      "pro-button--align-".get_field('align'),
      "pro-button--width-".get_field('width'),
      "pro-button--size-".get_field('size'),
    ];
    
    $classList = implode(' ', $classes);
    
  // Inner Blocks
    $inner_blocks_template = array(
      array(
        'core/paragraph',
        array('placeholder' => 'Click to add button text'),
        array(),
      ),
    );
		
	$iconTag = get_field('icon') ? $frwk_theme->fontawesomeIconTag( get_field('icon'), $is_preview, 'pro-button__icon' ) : '' ;
	
    
  // Params
    if ( $linkType = get_field('link_type') ) {
    	$target  = get_field('target') && $linkType == 'external' ? "target='".get_field('target')."'" : '' ;
      $link = get_field("link_{$linkType}");
      if ( $linkType == 'post' ) {
      	$link = get_permalink($context['postId']);
				
				//override?
				$override = get_field('override', $context['postId']);
				if($override){
					$override_object = get_field($override, $context['postId']);
					switch ( $override ) {
						case 'url':
							$url = $override_object;
							break;
						case 'internal':
							$url = get_permalink($override_object->ID);
							break;
					}
					$link = $url;
					$target = "target='_blank'";
				}
      }
      if ( $linkType == 'hashtag' ) $link = "#{$link}";
      
      $link = !$link || $is_preview ? '#' : $link;
    }
    else {
      $target = false;
      $link = false;
    }
    
?>

<?php if ( !$is_preview ) : ?>
  <div <?php echo get_block_wrapper_attributes(); ?>>
<?php endif; ?>
  
  <a class="pro-button <?php echo $classList ?> wp-element-button" <?php if( !$is_preview ) echo "href='$link' $target"; ?>">
    <InnerBlocks template="<?php echo esc_attr( wp_json_encode( $inner_blocks_template ) ); ?>" />
    <?php echo $iconTag; ?>
  </a>
  
<?php if ( !$is_preview ) : ?>
  </div>
<?php endif; ?>
