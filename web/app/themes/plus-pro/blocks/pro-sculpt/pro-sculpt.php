<?php

$type = get_field('type'); // slant-right slant-left buldge-up bulge-down
$loc = get_field('location');
$s = get_field('scale')*10;
$s1 = get_field('scale')*10 + 1;
$topcolor = get_field('top_color') ? : 'transparent ';
$bottomcolor = get_field('bottom_color') ? : 'transparent ';

// scale hardcoded as 48 below to start
?>
<div <?php echo get_block_wrapper_attributes(['class' => "pro-sculpt--$loc"]); ?> >
  <?php
    if ($type == 'slant-up'){
      $td = "M 0,$s L 0,0 L 1000,0 ";
      $bd = "M 0,$s L 1000,0 L 1000,$s1 L 0,$s1";
    } elseif ($type == 'slant-down'){
      $td = "M 0,0 L 1000,0 L 1000,$s ";
      $bd = "M 0,0 L 1000,$s L 1000,$s1 L 0,$s1 ";
    } elseif ($type == 'angle-down'){
      $td = "M 0,0 L 1000,0 L 500,$s ";
      $bd = "M 0,0 L 500,$s L 1000,0 L 1000,$s1 L 0,$s1";
    } elseif ($type == 'angle-up'){
      $td = "M 0,$s L 500,0 L 1000,$s L 1000,0 L 0,0";
      $bd = "M 0,$s L 500,0 L 1000,$s L 1000,$s1 L 0,$s1";
    } elseif ($type == 'caret-down'){
      $offsetA = 500 - $s;
      $offsetB = 500 + $s;
      $td = "M $offsetA,0 L 500,$s L $offsetB,0";
      $bd = "M 0,0 L $offsetA,0 L 500,$s L $offsetB,0 L 1000,0 L 1000,$s1 L 0,$s1";
    } elseif ($type == 'caret-up'){
      $offsetA = 500 - $s;
      $offsetB = 500 + $s;
      $td = "M 0,0 L 1000,0 L 1000,$s L $offsetB,$s L 500,0 L $offsetA,$s L 0,$s";
      $bd = "M $offsetA,$s1 L 500,0 L $offsetB,$s1";
    } elseif ($type == 'bump-up'){
      // eyeballed the math here - not sure if there is a cleaner way
      $rx = 500*1.1;
      $ry = $s*1.7;
      $td = "M 0,$s A $rx,$ry 0 0 1 1000,$s L 1000,0 L 0,0";
      $bd = "M 0,$s A $rx,$ry 0 0 1 1000,$s L 1000,$s1 L 0,$s1";
    } elseif ($type == 'bump-down'){
      $rx = 500*1.1;
      $ry = $s*1.7;
      $td = "M 0,0 A $rx,$ry 0 0 0 1000,0";
      $bd = "M 0,0 A $rx,$ry 0 0 0 1000,0 L 1000,$s1 L 0,$s1";
    } elseif ($type == 'custom'){
      $td = get_field('custom_top');
      $bd = get_field('custom_bottom');
    } elseif ($type == 'zig-zag'){
      $zigs = round(1000/$s);
      if ($zigs % 2 == 1) $zigs++;
      $step = 1000/$zigs;
      
      $i = 1;
      $dx = 0;
      $zigzag = "M 0,0 ";
      while ($i <= $zigs/2) {
        $dx += $step;
        $zigzag .= "L $dx,$s ";
        $dx += $step;
        $zigzag .= "L $dx,0 ";
        $i++;
      }

      $td = $zigzag;
      $bd = "$zigzag L 1000,$s1 L 0,$s1";
    } else {
      $td = "";
      $bd = "";
    } 
    
  ?>
	<svg viewBox="0 0 1000 <?php echo $s ?>" preserveAspectRatio>
    <path fill="<?php echo $topcolor ?>" class="top" d="<?php echo $td ?>" />
    <path fill="<?php echo $bottomcolor ?>" class="bottom" d="<?php echo $bd ?>" />
  </svg>
</div>
