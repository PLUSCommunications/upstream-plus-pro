
<?php
  $scale     = get_field('scale');
  $area      = $scale * 1000;
  $spacing   = get_field('spacing');
  $hover     = get_field('hover_effect');
  $alignment = get_field('alignment');
  
  $cstyle    = '';
  $cclass    = "pro-logos--$alignment";
  $iclass    = "pro-logos__logo";
  $style     = '';
  $attrs     = '';
  
  if ( $alignment == 'grid' ) {
    $padding = $spacing/2;
    $style   = "padding:{$padding}px;";
    $count   = count(get_field('logos'));
    $max     = pow(8000 * 8 , 1/3) * $scale + $spacing;
    $cstyle  = "grid-template-columns: repeat(auto-fill, minmax({$max}px, 1fr) );";
  }
  else if ( $alignment == 'swiper' ) {
    $cclass = "pro-logos--$alignment swiper-wrapper";
    $iclass = "pro-logos__logo swiper-slide";
    $attrs = "data-gap='$spacing' data-spv='".ceil(16 / $scale)."'";
  }
  
  if ( $alignment == 'swiper' ) echo "<div class='swiper'>";
	
  $bgImage = isset($block['style']['background']['backgroundImage']) ? "background-image:url('{$block['style']['background']['backgroundImage']['url']}');" : '' ;
  $bgSize = isset($block['style']['background']['backgroundSize']) ? "background-size:{$block['style']['background']['backgroundSize']};" : '' ;
  $bgPos = isset($block['style']['background']['backgroundPosition']) ? "background-position:{$block['style']['background']['backgroundPosition']};" : '' ;
	
?>
<div <?php echo ($is_preview ? ' ' : get_block_wrapper_attributes(["style"=>"$bgImage $bgSize $bgPos"])) ?> data-block="<?php echo $block['id'] ?>">
  <div class="pro-logos <?php echo $cclass ?>" <?php echo $attrs ?> style="gap:<?php echo $spacing ?>px;<?php echo $cstyle ?>">
    <?php while( have_rows('logos') ) : the_row(); $logo = get_sub_field('logo'); ?>
      <?php if( $logo['height'] ) : ?>
        <div class="<?php echo $iclass ?>" style="<?php echo $style ?>">
          <?php
            $ratio      = $logo['width']/$logo['height'];
            $new_width  = pow(8000 * $ratio , 1/3) * $scale;
            $new_height = $new_width / $ratio;
            $url        = $logo['url'];
            $link       = $is_preview ? false : get_sub_field('url');
            $title      = $logo['title'];
            
            if( $link ) echo "<a href='$link' class='hover-$hover' target='_blank' title='$title'>";
            else echo "<div class='hover-$hover'>";
            
            echo "<img src='$url' height='$new_height' width='$new_width' title='$title'>";
            // pxl::image($logo, array('w'=>$new_width,'h'=>'auto','attrs'=>array('alt'=> $title)));
            
            if( $link ) echo "</a>";
            else echo "</div>"
          ?>
        </div>
      <?php else: ?>
        <div class="pro-logos__logo" style="<?php echo $style ?>">
         File Error
        </div>
      <?php endif; ?>
    <?php endwhile; ?>
  </div>
  <?php if ( $alignment == 'swiper' ) echo "<div class='swiper-pagination'></div></div>"; ?>
  
  <?php if ( !is_admin() ) : ?>
    <script>
      let <?php echo $block['id'] ?>;
      
      document.addEventListener('DOMContentLoaded', function() {
        <?php echo $block['id'] ?> = proLogosFn('<?php echo $block['id'] ?>');
      });
    </script>
  <?php endif; ?>
</div>