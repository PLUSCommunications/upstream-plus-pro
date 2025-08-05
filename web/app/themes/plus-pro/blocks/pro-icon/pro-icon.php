<?php global $frwk_theme;

  $layers    = get_field('icon') ?  : array(array('icon' => 'shapes','offset'=>'0,0','color' => '#000'));
  $size      = get_field('size');
  $location  = get_field('location');
  $alignment = get_field('alignment');
  
  $bgImage = isset($block['style']['background']['backgroundImage']) ? "background-image:url('{$block['style']['background']['backgroundImage']['url']}');" : '' ;
  $bgSize  = isset($block['style']['background']['backgroundSize']) ? "background-size:{$block['style']['background']['backgroundSize']};" : '' ;
  $bgPos   = isset($block['style']['background']['backgroundPosition']) ? "background-position:{$block['style']['background']['backgroundPosition']};" : '' ;
 	
 	if ( isset($block['style']['spacing']['blockGap']) ) $blockGap = $frwk_theme->toCSSVar($block['style']['spacing']['blockGap']);
 	else $blockGap = "var(--wp--preset--spacing--05)";
?>

<div <?php echo ($is_preview ? "style='gap:$blockGap;'" : get_block_wrapper_attributes(["style"=>"$bgImage $bgSize $bgPos gap:$blockGap;"])) ?>>
  <div class="pro-icon pro-icon--position-<?php echo $location ?>" style="font-size:<?php echo $size ?>px; align-self:<?php echo $alignment ?>; gap:var(<?php echo $alignment ?>);">
    <?php if ( $shape = get_field('shape') ) : $shapeColor = get_field('shape_color'); ?>
      <svg class="pro-icon__shape" viewBox='0 0 24 24' style="border-radius:var(--wp--custom--border-radius);">
        <?php if ($shape == 'circle') echo "<circle fill='".$shapeColor."' cx='12' cy='12' r='12'/>"; ?>
        <?php if ($shape == 'square') echo "<rect fill='".$shapeColor."' x='0' y='0' width='24' height='24' />"; ?>
      </svg>
    <?php endif; ?>
    
    <?php if ( $upload = get_field('icon_upload') ) : ?>
      <div class="pro-icon__layer">
        <img class="pro-icon__file" src="<?php echo $upload['sizes']['medium'] ?>" width="<?php echo $size * 1.5 ?>px" height="<?php echo $size; ?>px" style="width:<?php echo $size * 1.5 ?>px;height:<?php echo $size; ?>px;object-fit:contain;">
      </div>
    <?php elseif ($layers) : $layers = array_reverse($layers); foreach($layers as $layer) :  $icon = $layer['icon']; $color = $layer['color']; $color_second = isset($layer['second_color']) ? $layer['second_color'] : '#fff'; $offset = $layer['offset'];?>
      <div class="pro-icon__layer" style="color:<?php echo $color ?>;transform:translate(<?php echo $offset ?>); --fa-primary-color: <?php echo $color ?>; --fa-secondary-color: <?php echo $color_second ?>; --fa-secondary-opacity:1;">
        <?php echo $frwk_theme->fontawesomeIconTag( $icon, $is_preview ); ?>
      </div>
    <?php endforeach; endif; ?>
  </div>
  <?php if ( ! $is_preview ) echo "<div class='pro-icon-content' style='align-self:$alignment'>"; ?>
    <InnerBlocks class="pro-icon-content" style="align-self:<?php echo $alignment ?>;"/>
  <?php if ( ! $is_preview ) echo "</div>"; ?>
</div>