<?php global $frwk_theme;
  $inner_blocks_template = array(
    array(
    	'core/heading',
      array(
        'placeholder'   => 'Name Goes Here',
        'level' => 5,
        'style' => array(
          'spacing' => array(
            'margin' => array(
              'bottom' => '0',
            ),
          ),
        ),
      ),
      array()
    ),
    array(
      'core/paragraph',
      array(
        'placeholder'   => 'Title goes here',
        'style' => array(
          'spacing' => array(
            'margin' => array(
              'bottom' => '0',
            ),
          ),
        ),
      ),
      array()
    ),
  );
  
  $bgImage = isset($block['style']['background']['backgroundImage']) ? "background-image:url('{$block['style']['background']['backgroundImage']['url']}');" : '' ;
  $bgSize  = isset($block['style']['background']['backgroundSize']) ? "background-size:{$block['style']['background']['backgroundSize']};" : '' ;
  $bgPos   = isset($block['style']['background']['backgroundPosition']) ? "background-position:{$block['style']['background']['backgroundPosition']};" : '' ;
	
  $layout       = !empty($block['layout']) ? $block['layout']['justifyContent'] : '';
	$orientation  = !empty($block['layout']) ? $block['layout']['orientation'] : '';
	$vAlignment   = isset($block['layout']['verticalAlignment']) ? $block['layout']['verticalAlignment'] : '';
  $layoutClass  = "is-content-justification-{$layout} is-valign-{$vAlignment} is-{$orientation}";
	
 	if ( isset($block['style']['spacing']['blockGap']) ) $blockGap = $frwk_theme->toCSSVar($block['style']['spacing']['blockGap']);
 	else $blockGap = "var(--wp--preset--spacing--05)";
  
  $portrait  = get_field('portrait');
  $width     = get_field('width');
  $height    = ( get_field('aspect_ratio') == 'auto' ) ? 'auto' : ($width * get_field('aspect_ratio'));
	$style     = "height:{$height}px;width:{$width}px;";
	$crop      = get_field('crop');
  $cropstyle = "is-style-br-$crop";
  
	if ( $crop == 'custom' ) $style .= "border-radius:".get_field('custom_crop');
?>

<div <?php echo ($is_preview ? "style='gap:$blockGap;display:flex;' class='$layoutClass';" : get_block_wrapper_attributes(["style"=>"$bgImage $bgSize $bgPos gap:$blockGap;", "class"=>"is-valign-{$vAlignment}"])) ?>>
  <div class="pro-portrait <?php echo $cropstyle ?>">
    <img src="<?php echo $portrait['sizes']['medium_large'] ?>" width="<?php echo $width ?>" height="<?php echo $height ?>" style="object-fit:cover;<?php echo $style ?>">
  </div>
  <?php if ( ! $is_preview ) echo "<div class='pro-portrait-content'>"; ?>
    <InnerBlocks class="pro-portrait-content" template="<?php echo esc_attr( wp_json_encode( $inner_blocks_template ) ); ?>"/>
  <?php if ( ! $is_preview ) echo "</div>"; ?>
</div>