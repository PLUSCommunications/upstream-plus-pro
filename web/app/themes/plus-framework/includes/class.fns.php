<?php
/**
 * PXL Functions
 * Developer tools and other useful functions
 */

if ( !class_exists('fns') ) :
  class fns {
    private static $console_count;
    public static function array_count($key_name, array $array) {
      $values = self::array_vals($key_name, $array);
      return count($values);
    }
    public static function array_sortBy( $field, &$array, $direction = 'asc' ) {
      usort($array, function($a, $b) use ($field, $direction) {
        $a = is_array($a) ? $a[$field] : $a->$field;
        $b = is_array($b) ? $b[$field] : $b->$field;
        
        if ($a == $b) return 0;
        
        $direction  = strtolower(trim($direction));
        $comparison = $direction == 'asc' ? $a < $b : $a > $b;
        
        return $comparison ? -1 : 1;
      });
      
      return true;
    }
    public static function array_sum($key_name, array $array) {
      $values = self::array_vals($key_name, $array);
      return array_sum($values);
    }
    public static function array_to_attrs( array $array, $format = false ) {
      $attrs = join(' ', array_map(function($key) use ($array, $format) {
        if ( $format ) {
          if ( is_bool($format) && $format ) $format = 'default';
          
          switch ($format) {
            case 'data': 
              return sprintf('data-%s="%s"', $key, $array[$key]);
            break;
            case 'class':
              return str_replace('_', '-', "{$key}-{$array[$key]}");
            break;
            default:
              $value = $array[$key];
              
              if ( is_bool($array[$key]) ) $value = $array[$key] ? 'true' : 'false';
              
              return sprintf(
                '%s="%s"',
                $key,
                $value
              );
            break;
          }
        }
        
        return sprintf('%s="%s"', $key, $array[$key]);
      }, array_keys($array)));
      
      return $attrs;
    }
    public static function array_vals($key_name, array $array) {
      $newArray = array();
      
      if ( strpos($key_name,'|') !== FALSE ) {
        $kv       = explode('|',$key_name);
        $key_name = $kv[0];
        $value    = $kv[1];
      }
      else $value = '';
      
      $json  = json_encode($array);
      $array = json_decode($json, true);
      
      if ( $value != '' ) {
        $returnValues = function($value, $key, $attr) {
          if ( $key === $attr['match'] && $value == $attr['value'] ) array_push($attr['array'], $value);
        };
      }
      else {
        $returnValues = function($value, $key, $attr) {
          if ( $key === $attr['match'] && $value != "" ) array_push($attr['array'], $value);
        };
      }
      
      array_walk_recursive($array, $returnValues, array( 'match' => $key_name, 'array' => &$newArray, 'value' => $value));
      
      return $newArray;
    }
    public static function array_unmix( array $array, $default = true ) {
      $unmixed = array();
      foreach ($array as $key => $value) {
        if ( is_int($key) ) $unmixed[$value] = $default;
        else $unmixed[$key] = $value;
      }
      return $unmixed;
    }
    public static function brightness($hex) {
      $hex   = str_replace("#", "", $hex);
      $split = strlen($hex) == 3 ? 1 : 2;
      $vals = 0;
      
      $rgb = str_split($hex, $split);
      
      
      foreach($rgb as $key => $color){
        $rgb[$key] = hexdec($color);
      }
      
      $rgb[0] *= 0.2126;
      $rgb[1] *= 0.7152;
      $rgb[2] *= 0.0722;
             
      $brightness = round((($rgb[0] + $rgb[1] + $rgb[2]))/255*1000)/1000;
      
      return $brightness;
    }
    public static function cached( $option_name, $cached_for = 3600 ) {
      $now       = time();
      $cached_on = (int)get_option("{$option_name}_cached_on");
      $elapsed   = $now - $cached_on;
      return $elapsed <= $cached_for;
    }
    public static function color($hex, $options = array()) {
      $return   = '#';
      $defaults = array(
        'action' => 'darken',
        'factor' => 0.2
      );
      
      // Get Options
        if ( !is_array($options) ) $options = array('action' => $options);
        extract(array_merge($defaults, $options));
        
      // Factor Percentage
        if ( strpos($factor, '%') ) $factor = intval($factor)/100;
        
      // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if ( strlen($hex) == 3 ) $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
        
      // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        
      // Change Color
        foreach ($color_parts as $color) {
          $color   = hexdec($color); // Convert to decimal
          $change  = $action == 'darken' ? $color * (1 - $factor) : $color + ((255 - $color) * $factor);
          $color   = max(0, min(255, $change)); // Adjust color
          $return .= trim(str_pad(dechex($color), 2, '0', STR_PAD_LEFT)); // Make two char hex code
        }
        
      return $return;
    }
    public static function debug( $target = FALSE, $data = FALSE, $title = FALSE ) {
      $debug = FALSE;
      
      if ( isset($_REQUEST['debug']) ) {
        if ( $_REQUEST['debug'] === '' || $target && $_REQUEST['debug'] === $target ) $debug = TRUE;
      }
      
      if ( $debug && $data ) {
        if ( !$title ) $title = $target;
        fns::log($data, $title);
      }
      
      return $debug;
    }
    public static function error( $message, $title = FALSE ) {
      $items = array(print_r($message, true));
      if ( $title ) array_unshift($items, $title);
      
      error_log(vsprintf(
        $title ? "____[ %s ]____\n%s" : "%s",
        $items
      ));
    }
    public static function hex2rgb($hex, $alpha = FALSE) {
      $hex   = str_replace("#", "", $hex);
      $split = strlen($hex) == 3 ? 1 : 2;
      $rgb   = array();
      
      for ($index = 0; $index < 3 * $split; $index+=$split) { 
        $v = $split == 1 ? substr($hex, $index, $split).substr($hex, $index, $split) : substr($hex, $index, $split);
        array_push($rgb, hexdec($v));
      }
      
      $rgb = implode(',', $rgb);
      
      return $alpha ? "rgba($rgb, $alpha)" : "rgb($rgb)";
    }
    public static function log( $data, $name = FALSE, $jsEval = FALSE, $log = 'log' ) {
      $console_count = self::$console_count;
      $isevaled      = FALSE;
      $type          = ($data || gettype($data)) ? gettype($data) : '';
      $data          = json_encode($data);
      
      if ( $type != 'string') {
        $console_count++;
        $data_obj = $data;
      }
      
      # sanitalize
      $data          = $data ? $data : '';
      $search_array  = array("/=\'(\w*)\'/", "#'#", '#""#', "#''#", "#\n#", "#\r\n#", "/:,/", "/(\d{1,}),(\d{1,})/");
      $replace_array = array('=\"$1\"','"', '', '', '\\n', '\\n', ":\"\",", '$1-$2');
      $data          = preg_replace($search_array,  $replace_array, $data);
      $data          = ltrim(rtrim($data, '"'), '"');
      $data          = $isevaled ? $data : ((!empty($data) && $data[0] === "'") ? $data : "'" . $data . "'");
      $output        = '';
      $output       .= "console.log('\\n');";
      $title         = ( $name ?: $type );
      
      if ( $type != 'string' && $title != $type ) $title = "($type) $title";
      
      $output .= "console.log('$title');";
      $output .= "console.log('------------------------------------------');";
      
      if ( $type != 'string' ) {
        $label   = ( $name ? strtolower($name) : "data{$console_count}" );
        $slug    = str_replace('-', '_', sanitize_file_name($label));
        
        $output .= sprintf('%1$s = %2$s; console.%3$s(%1$s);', $slug, $data_obj, $log);
      } else $output .= "console.log($data);";
      
      $js = <<<JSCODE
      \n<script>
        if (! window.console) console = {};
        console.log = console.log || function(name, data){};
        $output
      </script>
JSCODE;
      echo $js;
    }
    public static function put( $data ) {
      echo '<pre>'; print_r($data); echo '</pre>';
    }
    public static function strtolast($str, $last_of = array(), $limit_search = false) {
      $text        = trim($str);
      $breakpoints = array();
      $strlen      = strlen($text);
      $search      = $text;
      
      if ( $limit_search ) {
        $substring = $strlen * $limit_search;
        $search = substr($text, $substring);
      }
      
      foreach ($last_of as $lf => $weight) {
        if ( $pos = strrpos($search, $lf) ) {
          $char = $lf === ' ' ? 'space' : $lf;
          $breakpoints[$char] = array($pos, $pos * $weight);
        }
      }
      
      if ( !empty($breakpoints) ) {
        uasort($breakpoints, function($a, $b) { return $a[1] <=> $b[1]; });
        
        $keys     = array_keys($breakpoints);
        $break_on = $breakpoints[$keys[0]][0];
        
        if ( $limit_search ) $break_on += $substring;
        
        $cut = $keys[0] == '. ' ? $break_on+1 : $break_on;
        
        if ( $cut ) $text = substr($text, 0, $cut);
      }
      
      $last_char = substr($text, strlen($text)-1);
      
      if ( !in_array($last_char, array("\n", ".", " ")) ) $text .= "<span class=\"elipsis\">&hellip;</span>";
      
      return $text;
    }
    public static function table( $data, $name = FALSE, $jsEval = FALSE ) {
      fns::log($data, $name, $jsEval, 'table');
    }
  }
endif;
