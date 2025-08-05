<?php
	$inner_blocks_template = array(
		array(
			'core/navigation',
			array(
				'overlayMenu' => 'never',
				'layout' => array(
					'type' => 'flex',
					'orientation' => 'vertical',
					'justifyContent' => 'center'
				)
			),
			array()
		),
	);
	// probably not needed
	$styles = '';
	
  $classes = 'pro-navbar-drawer';
  $classes.= ' pro-navbar-drawer--'.get_field('position');
?>
<div <?php echo ($is_preview ? ' ' : get_block_wrapper_attributes(['style' => $styles, 'class'=>$classes])) ?>>
	<a href='#' class='pro-navbar-drawer__close'><i class='fa-light fa-close'></i></a>
	<div class="pro-navbar-drawer__inner">
  	<InnerBlocks template="<?php echo esc_attr(wp_json_encode($inner_blocks_template)); ?>" />
	</div>
</div>