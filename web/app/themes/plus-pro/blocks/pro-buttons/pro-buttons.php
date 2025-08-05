<?php

/**
 * A template string of blocks.
 * Need help converting block HTML markup to an array?
 * 👉 https://happyprime.github.io/wphtml-converter/
 *
 * @link https://developer.wordpress.org/block-editor/reference-guides/block-api/block-templates/
 */
$inner_blocks_template = array(
    array(
        'pluspro/pro-button',
        array(),
        array(),
    ),
);
?>

<?php if ( ! $is_preview ) { ?>
    <div <?php echo wp_kses_data( get_block_wrapper_attributes() );  ?>>
<?php } ?>
  <InnerBlocks class="wp-block-pluspro-pro-buttons" template="<?php echo esc_attr( wp_json_encode( $inner_blocks_template ) ); ?>" orientation="horizontal"/>
<?php if ( ! $is_preview ) { ?>
    </div>
<?php } ?>