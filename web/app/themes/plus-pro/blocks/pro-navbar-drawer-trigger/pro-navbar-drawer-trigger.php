<?php global $frwk_theme;
  $params = get_fields(); 
?>
<div <?php echo ($is_preview ? "" : get_block_wrapper_attributes()) ?>>
  <?php 
    switch ($params['icon_type']) {
      case 'default':
        $icon = "<span class='pro-navbar-drawer-trigger__icon'><i class='pro-navbar-drawer-trigger__icon--default'></i></span>";
      break;
      case 'swap':
        $ciconTag = $frwk_theme->fontawesomeIconTag($params['closed_icon'], $is_preview);
        $oiconTag = $frwk_theme->fontawesomeIconTag($params['open_icon'], $is_preview);
        $icon = "<span class='pro-navbar-drawer-trigger__icon pro-navbar-drawer-trigger__icon--swap'>$ciconTag $oiconTag</span>";
      break;
      case 'rotate':
        $ciconTag = $frwk_theme->fontawesomeIconTag($params['closed_icon'], $is_preview);
        $rotate = $params['open_rotation'];
        $icon = "<span class='pro-navbar-drawer-trigger__icon pro-navbar-drawer-trigger__icon--rotate' style='--open-rotate:{$rotate}deg'>$ciconTag</span>";
      break;
      default:
        # code...
      break;
    }
		
		$text = get_field('text') ? "<span>".get_field('text')."</span>" : "";
    
    printf(
      '<a class="pro-navbar-drawer-trigger pro-button wp-element-button">%s%s</a>',
      $text,
      $icon
    );
  ?>
</div>

<script>
	document.addEventListener("DOMContentLoaded", () => {
	    const trigger = document.querySelector(".pro-navbar-drawer-trigger");
	    const drawer = document.querySelector(".pro-navbar-drawer");
			
			const drawerclose = document.querySelector(".pro-navbar-drawer__close");

	    if ( trigger && drawer ) {
	        trigger.addEventListener("click", () => {
	            drawer.classList.toggle("pro-navbar-drawer--open");
							trigger.classList.toggle("pro-navbar-drawer-trigger--open");
	        });
					
					if ( drawerclose ) {
		        drawerclose.addEventListener("click", () => {
		            drawer.classList.remove("pro-navbar-drawer--open");
								trigger.classList.remove("pro-navbar-drawer-trigger--open");
		        });
					}
	    }
	});
</script>