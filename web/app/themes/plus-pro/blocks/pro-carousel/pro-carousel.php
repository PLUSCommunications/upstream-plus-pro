<?php
  $blockClass = $is_preview ? 'class="swiper"' : get_block_wrapper_attributes(["class" => "swiper"]);
  $params     = get_fields();
?>
<div <?php echo $blockClass; ?> data-block="<?php echo $block['id'] ?>">
  <InnerBlocks class="acf-innerblocks-container swiper-wrapper"/>
  
  <?php
    if ( $params['navigation_arrows'] ) echo "<a class='swiper-button-prev'></a><a class='swiper-button-next'></a>";
    if ( $params['paging'] )            echo "<div class='swiper-pagination'></div>";
    // Doesn't exist yet: if ( $params['scrollbar'] )         echo "<div class='swiper-scrollbar'></div>";
  ?>
</div>
<?php if ( !is_admin() ) : ?>
  <script>
    let <?php echo $block['id'] ?>;
    
    document.addEventListener('DOMContentLoaded', function() {
      <?php echo $block['id'] ?> = proCarouselFn('<?php echo $block['id'] ?>', <?php echo json_encode($params); ?>);
    });
  </script>
<?php endif; ?>
