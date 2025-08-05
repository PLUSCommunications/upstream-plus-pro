<?php
  $float = get_field('float') == "none" || !get_field('float') ? '' : 'pro-parallax--float-'.get_field('float');
  $speed = get_field('speed');

  $zindex = 10 + $speed;
?>
<div <?php echo get_block_wrapper_attributes(['class' => 'rellax '.$float, 'style' => 'z-index:'.$zindex]); ?> data-rellax-speed="<?php the_field('speed'); ?>" data-rellax-zindex="5" data-rellax-percentage="<?php echo get_field('percentage')/100; ?>">
	
  <InnerBlocks />
  
</div>
