<?php if ( ! $is_preview ) { ?>
    <div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
<?php } else { ?>
    <div class="wp-block-pluspro-pro-social-links is-content-justification-<?php echo $block['layout']['justifyContent'] ?>">
<?php } ?>
  <?php 
  
    if( have_rows('socials','options') ):
      
      while( have_rows('socials','options') ): 
        the_row();
        $link = get_sub_field('link');
				$icon = get_sub_field('icon');
				if( str_contains( $icon, 'fa-' ) ){
					$faicon = $icon;
					$class = "custom";
 				} else {
					$faicon = "fa-brands fa-".$icon;
					$class = $icon;
				}
       
        
  ?>
    <a class='pro-social-link pro-social-link--<?php echo $class ?>' href='<?php echo ( $is_preview ? "#" : $link ) ?>'><i class='<?php echo $faicon ?>'></i></a>
  <?php
      endwhile;
    endif;
  ?>
</div>
