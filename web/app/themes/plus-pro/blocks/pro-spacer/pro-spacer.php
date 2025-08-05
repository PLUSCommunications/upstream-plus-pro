<?php 
  $variable = get_field('variable_height');
  $fixed = get_field('fixed_height');
  $negative = ( $variable <= 0 && $fixed <= 0 ) ? true : false;
?>
<div <?php echo ($is_preview ? ' ' : get_block_wrapper_attributes()) ?> style="margin-top:calc(<?php echo $variable ?>vw + <?php echo $fixed ?>px)">
  <?php if( $is_preview ) : ?>
    <div class="pro-block-handle" title="<?php echo $block['title'] ?>"></div>
  <?php endif; ?>
</div>
