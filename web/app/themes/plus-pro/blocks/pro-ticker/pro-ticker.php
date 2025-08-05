<?php global $frwk_theme;
	$gap = $frwk_theme->toCSSVar($block['style']['spacing']['blockGap']);
?>
<div <?php echo ($is_preview ? ' ' : get_block_wrapper_attributes()) ?>>
	<div class="pro-ticker" style="gap:<?php echo $gap ?>;">
		 <InnerBlocks />
	</div>
</div>