<?php 

  $height = get_field('height');
  $link = get_field('link') ? : '/';
?>
<div <?php echo ($is_preview ? ' ' : get_block_wrapper_attributes()) ?>>
  <a <?php echo ($is_preview ? '' : "href='$link'") ?> class="pro_logo">
    <?php if ( $logo = get_field('logo') ) : $med_file_url = $logo['sizes']['medium_large']; ?>
      <img class="pro_logo__img" src="<?php echo $med_file_url ?>" alt="<?php echo $logo['alt']; ?>" height="<?php echo $height ?>" style="height:<?php echo $height ?>px;width:auto;">
    <?php else: ?>
      <img class="pro_logo__img" src="<?php echo RES.'/images/logo-placeholder.png' ?>" height="<?php echo $height ?>" style="height:<?php echo $height ?>px;width:auto;">
    <?php endif; ?>
    <?php
      if( have_rows('alternate_logos') ){
        while( have_rows('alternate_logos') ){
          the_row();
          if( $sub_logo = get_sub_field('logo') ){
            $sub_med_file_url = $sub_logo['sizes']['medium_large'];
            $sub_height = get_sub_field('height') ? : $height ;
            $condition = " pro_logo__img--". get_sub_field('condition');
            echo "<img class='pro_logo__img $condition' src='$sub_med_file_url' height='$sub_height' style='height:{$sub_height}px;width:auto;'>";
          }
        }
      }
    ?>
  </a>
</div>
