<?php

  $inner_blocks_template = array(
    array(
      'pluspro/pro-accordion-item',
      array(),
      array()
    ),
  );
  $classes = '';
  $classes.= 'pro-accordion--'.get_field('behavior');
  $classes.= ' pro-accordion--icon-'.get_field('icon_side');
  
  $params = get_fields();
?>

<div <?php echo ($is_preview ? ' ' : get_block_wrapper_attributes()) ?> data-block="<?php echo $block['id'] ?>">
  <?php
    if ( !empty($params['search_filter']) ) {
      // Doesn't work
      //printf(
      //  '
      //    <div class="pro-accordion-filters">
      //      <div class="pro-accordion-filters__search">
      //        <input class="accordion-search__input" type="search" size="10" placeholder="search" value="%s"/>
      //      </div>
      //    </div>
      //  ',
      //  (isset($_GET['s']) ? $_GET['s'] : '')
      //);
      echo "Search and filter capabilities coming soon.";
    }
  ?>
  
  <InnerBlocks template="<?php echo esc_attr(wp_json_encode($inner_blocks_template)); ?>" class="pro-accordion <?php echo $classes ?>" />
  
  <?php if ( !is_admin() ) : ?>
    <script>
      let <?php echo $block['id'] ?>;
      
      document.addEventListener('DOMContentLoaded', function() {
        <?php echo $block['id'] ?> = new proAccordion('<?php echo $block['id'] ?>', <?php echo json_encode($params); ?>);
      });
    </script>
  <?php endif; ?>
</div>
