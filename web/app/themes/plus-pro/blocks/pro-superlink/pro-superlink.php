<?php
  $inner_blocks_template = array(
    array(
      'core/group',
      array(
        'className' => 'superlink-default',
        'metadata' => array(
    			'name' => 'Default Content'
    		)
      ),
      array(
        array(
          'core/paragraph',
          array(
            'placeholder'   => 'Default Content Here'
          ),
          array(),
        ),
      ),
    ),
    array(
      'core/group',
      array(
        'className' => 'superlink-hover',
        'metadata' => array(
    			'name' => 'Hover Content'
    		)
      ),
      array(
        array(
          'core/paragraph',
          array(
            'placeholder'   => 'Hover Content Here'
          ),
          array(),
        ),
      ),
    ),
  );
  
  // Params
    if ( $linkType = get_field('link_type') ) {
      $link = get_field("link_{$linkType}");
      if ( $linkType == 'post' ) $link = get_permalink($context['postId']);
      if ( $linkType == 'hashtag' ) $link = "#{$link}";
    
      $link = !$link || $is_preview ? '#' : $link;
    } else {
      $link = false;
    }
  
  $time = get_field('transition_time') ? : '0.2' ;
  $tag = $link ? 'a' : 'div' ;
  $href = $is_preview ? '#' : $link;
  $target = get_field('target') && $linkType == 'external' ? "target='".get_field('target')."'" : '' ;
?>
<div <?php echo ($is_preview ? ' ' : get_block_wrapper_attributes()) ?>>
  <<?php echo $tag ?>  <?php if($link) echo "href=$href $target"; ?> class="pro-superlink" style="--transition-time:<?php echo $time ?>s">
    <InnerBlocks template="<?php echo esc_attr( wp_json_encode( $inner_blocks_template ) ); ?>"/>
  </<?php echo $tag ?>>
</div>
