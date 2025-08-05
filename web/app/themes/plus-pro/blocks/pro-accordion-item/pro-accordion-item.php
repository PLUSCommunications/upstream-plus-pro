<?php global $frwk_theme;

/**
 * A template string of blocks.
 * Need help converting block HTML markup to an array?
 * 👉 https://happyprime.github.io/wphtml-converter/
 *
 * @link https://developer.wordpress.org/block-editor/reference-guides/block-api/block-templates/
 */
  $inner_blocks_template = array(
    array(
      'core/paragraph',
      array(
        'placeholder' => 'Click to enter your content',
      ),
      array(),
    ),
  );
  
  // Map fields to labels for editor
  if ( !isset($context['acf/fields']['heading_size']) ) {
    $context['acf/fields']['heading_size'] = $context['acf/fields']['field_679281c7fa217'] ?? 'h5';
    $context['acf/fields']['closed_icon']  = $context['acf/fields']['field_679282473d184'] ?? 'plus-large';
    $context['acf/fields']['open_icon']    = $context['acf/fields']['field_679284073d186'] ?? 'minus';
    $context['acf/fields']['icon_type']    = $context['acf/fields']['field_6792823b3d183'] ?? 'default';
  }
  
  $class = get_field('load_open') ? 'pro-accordion-item pro-accordion-item--open' : 'pro-accordion-item';
?>
<div <?php echo ($is_preview ? "class=\"{$class}\"" : get_block_wrapper_attributes(['class' => $class])) ?>>
  <?php 
    switch ($context['acf/fields']['icon_type']) {
      case 'default':
        $icon = "<span class='pro-accordion-item__icon'><i class='pro-accordion-item__icon--default'></i></span>";
      break;
      case 'swap':
        $ciconTag = $frwk_theme->fontawesomeIconTag( $context['acf/fields']['closed_icon'], $is_preview );
        $oiconTag = $frwk_theme->fontawesomeIconTag( $context['acf/fields']['open_icon'], $is_preview );
        $icon = "<span class='pro-accordion-item__icon pro-accordion-item__icon--swap'>$ciconTag $oiconTag</span>";
      break;
      case 'rotate':
        $ciconTag = $frwk_theme->fontawesomeIconTag( $context['acf/fields']['closed_icon'], $is_preview );
        $rotate = $context['acf/fields']['open_rotation'];
        $icon = "<span class='pro-accordion-item__icon pro-accordion-item__icon--rotate' style='--open-rotate:{$rotate}deg'>$ciconTag</span>";
      break;
      default:
        # code...
      break;
    }
    
    printf(
      '<%1s class="pro-accordion-item__heading">
        <span>%s</span>%s
      </%1$s>',
      $context['acf/fields']['heading_size'],
      get_field('heading_text'),
      $icon
    );
  ?>
  <InnerBlocks template="<?php echo esc_attr( wp_json_encode( $inner_blocks_template ) ); ?>"/>
</div>
