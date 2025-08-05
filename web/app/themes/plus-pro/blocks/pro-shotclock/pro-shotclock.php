<?php
  if ( $timer = get_field('timer') ) :
    $params = [
      'show_labels' => get_field('show_labels'),
      'on_finish'   => get_field('on_finish') ?: 'onFinish',
      'timer'       => $timer,
    ];
    
    $labels     = $params['show_labels'] ? '' : 'shotclock--nolabels';
    $layout     = !empty($block['layout']) ? $block['layout']['justifyContent'] : '';
    $blockClass = $is_preview
                  ? "class=\"wp-block-pluspro-pro-shotclock shotclock $labels is-content-justification-{$layout}\""
                  : wp_kses_data(get_block_wrapper_attributes(['class' => "shotclock {$labels}"]));
    $blockStyle = $is_preview ? '{opacity:0.5;}' : '{pointer-events:none;position:absolute;opacity:0;}';
    $timer      = array_combine(['h', 'm', 's'], explode(':', $timer));
    $pieces     = ['m' => 'minutes', 's' => 'seconds'];
?>
  <div <?php echo $blockClass ?> data-block="<?php echo $block['id'] ?>">
    <?php
      echo "<style>.{$params['on_finish']}{$blockStyle}</style>";
    
      foreach($pieces as $piece => $title) {
        printf('
          <div class="shotclock__unit">
            <h6 class="shotclock__number %1$s">%2$s</h6>
            <span class="shotclock__unit-label">%1$s</span>
          </div>
          <div class="shotclock__unit">
            <h6 class="shotclock__number ">:</h6>
          </div>',
          $title, ( isset($timer[$piece]) ? $timer[$piece] : 0 )
        );
      }
    ?>
  </div>
  
  <?php if ( !is_admin() ) : ?>
    <script>
      let <?php echo $block['id'] ?>;
      
      document.addEventListener('DOMContentLoaded', function() {
        <?php echo $block['id'] ?> = proShotClockFn('<?php echo $block['id'] ?>', <?php echo json_encode($params); ?>);
      });
    </script>
  <?php endif; ?>
<?php else : echo "Set Timer" ; endif; ?>