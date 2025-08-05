<?php global $frwk_theme;
  wp_enqueue_style('magnific-popup');
  wp_enqueue_script('modal');
	
	$params = get_fields();
?>
<div <?php echo ($is_preview ? ' ' : get_block_wrapper_attributes()) ?> data-block="<?php echo $block['id'] ?>">
  <?php
    $items = $params['items'];
    $scale  = $params['scale']; // 140 default - adjust base size
			
			
		if(isset($block['style']['border']['radius'])){
			if (is_array($block['style']['border']['radius'])) {
	        $topLeft = $block['style']['border']['radius']['topLeft'] ?? '0px';
	        $topRight = $block['style']['border']['radius']['topRight'] ?? '0px';
	        $bottomRight = $block['style']['border']['radius']['bottomRight'] ?? '0px';
	        $bottomLeft = $block['style']['border']['radius']['bottomLeft'] ?? '0px';
	        $radius = "$topLeft $topRight $bottomRight $bottomLeft";
	    } elseif (is_string($block['style']['border']['radius'])) {
	        $radius = $block['style']['border']['radius'];
	    }
		} else { $radius = 0; };
		
		$shadow = isset($block['style']['shadow']) ? $block['style']['shadow'] : '';

		
	 	$blockGap =  isset($block['style']['spacing']['blockGap']) ? $frwk_theme->toCSSVar($block['style']['spacing']['blockGap']) : "1em";
		
		if( $items ) : 
			if ( $params['flow'] == 'justify' ) $items = array_reverse($items);
  ?>
	  <div class="pro-gallery pro-gallery--<?php echo $params['flow'] ?>" style="gap:<?php echo $blockGap; ?>;justify-content:<?php if(isset($params['align'])) echo $params['align'] ?>;">
	    <?php
	      foreach ($items as $item) {
					
          $image_thumb_url = $item['type'] == 'video' ? $item['url'] : $item['sizes']['large'];
	        $image_ratio  = $item['width']/$item['height'];
	        $scaled_width = $image_ratio * $scale;
          $scaled_height = $scale;
          
          switch ($params['flow']) {
            case 'fix':
              $img_style = "height:{$scale}px;width:auto;";
              $item_style= "border-radius:$radius;box-shadow:$shadow;";
              break;
            case 'justify':
              $img_style = "width:100%;height:calc(100% / {$image_ratio});";
              $item_style= "border-radius:$radius;box-shadow:$shadow;flex:$image_ratio;min-width:".$image_ratio*$scale."px;";
              break;
          }
          
					$attrs = sprintf(" alt='%s' src='%s' data-ratio='%s' width='%s' height='%s' style='%s' ", $item['caption'], $image_thumb_url, $image_ratio, $scaled_width, $scaled_height, $img_style);
          
          if( $item['type'] == 'video' ){
            $image_tag  = "<video class='pro-gallery__image' $attrs autoplay muted playsinline loop>
              <source src='$image_thumb_url' type='video/mp4'>
              </video>" ;	        
          } else {
          	$image_tag = "<img class='pro-gallery__image' $attrs>";
          }
          
					if($params['click_action'] == 'lightbox'){
						$el_start = "a href='{$item['url']}' title='Click to Enhance'";
						$el_end = "a";
					} elseif($params['click_action'] == 'download') {
						$el_start = "a href='{$item['url']}' download title='Click to Download'";
						$el_end = "a";
					} else {
						$el_start = "div";
						$el_end = "div";
					}
					
					if($is_preview){
						$el_start = "div";
						$el_end = "div";
					}
					
					echo sprintf("
		        <%s class='pro-gallery__item' style='%s' data-pswp-width='%s' data-pswp-height='%s' >
		          %s
		        </%s>
						", $el_start, $item_style, $item['width'], $item['height'], $image_tag, $el_end);

				}
			?>
	  </div>
	  <?php if ( !is_admin() && $params['click_action'] == 'lightbox') : ?>
	    <script>
	      let <?php echo $block['id'] ?>;
      
	      document.addEventListener('DOMContentLoaded', function() {
	        <?php echo $block['id'] ?> = new proGallery('<?php echo $block['id'] ?>', <?php echo json_encode($params); ?>);
	      });
	    </script>
	  <?php endif; ?>
	<?php else: ?>
		<center> Empty Gallery </center>
	<?php endif; ?>
</div>
