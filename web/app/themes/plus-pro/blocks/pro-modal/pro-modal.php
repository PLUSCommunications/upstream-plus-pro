<?php
  $inner_blocks_template = array(
    array(
      'core/paragraph',
      [],
      [],
    ),
  );
  
  $params     = get_fields();
  $maxwidth   = $params['max_width'] ? "{$params['max_width']}rem" : "42rem";
  $overlay    = $params['overlay_color'] ?? "rgba(0,0,0,0.5)";
  $closecolor = $params['close_button_color'] ?? "currentColor";
  $position   = $params['position'] ?? FALSE;
  $transition = $params['transition'] ?? '';
  $type       = $params['type'] ?? FALSE;
  
  $bgImage = isset($block['style']['background']['backgroundImage']) ? "background-image:url('{$block['style']['background']['backgroundImage']['url']}');" : '' ;
  $bgSize = isset($block['style']['background']['backgroundSize']) ? "background-size:{$block['style']['background']['backgroundSize']};" : '' ;
  $bgPos = isset($block['style']['background']['backgroundPosition']) ? "background-position:{$block['style']['background']['backgroundPosition']};" : '' ;
  
  $classes = "pro_modal--$position pro_modal--transition-$transition";
  $style   = "--overlay-color:$overlay;--max-width:min( $maxwidth, calc(100vw - var(--wp--preset--spacing--10)) );";
?>

<?php if ( !$is_preview ) : ?>
  <dialog id="<?php echo $block['id'] ?>" <?php echo get_block_wrapper_attributes(["class"=>"pro_modal $classes", "style"=>"$style $bgImage $bgSize $bgPos"]) ?> data-dialog="<?php echo $type ?>" data-block="<?php echo $block['id'] ?>">
<?php endif; ?>
  
  <button class="pro_modal__close" style="color:<?php echo $closecolor ?>;"><i class="fa-regular fa-times"></i></button>
  <InnerBlocks template="<?php echo esc_attr( wp_json_encode( $inner_blocks_template ) ); ?>"/>
  
  <?php if ( !is_admin() ) : ?>
    <script>
      let <?php echo $block['id'] ?>;
      document.addEventListener('DOMContentLoaded', function() {
        <?php echo $block['id'] ?> = proModalFn('<?php echo $block['id'] ?>', <?php echo json_encode($params) ?>);
      });
    </script>
  <?php endif; ?>

<?php if ( !$is_preview ) : ?>
  </dialog>
<?php endif; ?>