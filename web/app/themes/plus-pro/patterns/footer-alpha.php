<?php
/**
 * Title: Footer Alpha
 * Slug: pluspro/footer-alpha
 * Categories: footer
 * Block Types: core/template-part/footer
 * Description: A standard footer section with a logo, menu and disclaimer
 */
?>

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|10"},"blockGap":"var:preset|spacing|10"},"elements":{"link":{"color":{"text":"var:preset|color|tertiary"}}}},"backgroundColor":"contrast","textColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-color has-contrast-background-color has-text-color has-background has-link-color" style="padding-top:var(--wp--preset--spacing--10);padding-bottom:var(--wp--preset--spacing--10)"><!-- wp:group {"align":"wide","className":"mobile-flex-wrap mobile-justify-center","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","orientation":"horizontal"}} -->
<div class="wp-block-group alignwide mobile-flex-wrap mobile-justify-center"><!-- wp:pluspro/pro-logo {"name":"pluspro/pro-logo","data":{"logo":"","_logo":"field_66d9e21ecdfa9","link":"/","_link":"field_66e33d3c1f84d","height":"48","_height":"field_66d9e576cdfae","alternate_logos":"","_alternate_logos":"field_66d9e22bcdfaa"},"mode":"preview"} /-->

<!-- wp:navigation {"ref":4,"showSubmenuIcon":false,"overlayMenu":"never","className":"is-style-columns","style":{"spacing":{"blockGap":"var:preset|spacing|05"}},"layout":{"type":"flex","orientation":"horizontal","justifyContent":"right"}} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"opacity50","style":{"border":{"width":"1px"},"spacing":{"padding":{"right":"1em","left":"1em","top":"1em","bottom":"1em"}}},"borderColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group opacity50 has-border-color has-base-border-color" style="border-width:1px;padding-top:1em;padding-right:1em;padding-bottom:1em;padding-left:1em"><!-- wp:paragraph {"align":"center","style":{"spacing":{"margin":{"top":"0","bottom":"0"}}},"fontSize":"small"} -->
<p class="has-text-align-center has-small-font-size" style="margin-top:0;margin-bottom:0">Disclaimer</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"align":"center","className":"opacity50","style":{"spacing":{"margin":{"top":"var:preset|spacing|05","bottom":"0"}}},"fontSize":"tiny"} -->
<p class="has-text-align-center opacity50 has-tiny-font-size" style="margin-top:var(--wp--preset--spacing--05);margin-bottom:0"><a href="/privacy-policy">Privacy Policy</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->