<?php
/**
 * Plus Resources
 * Handles loading CSS, JS, Environment Minification, and Localization
 */

if ( !class_exists('plus_resources') ) :
  class plus_resources {
    private $config, $enqueue;
    
    function __construct( $config ) {
      $this->config = $config;
      
      $this->enqueue = [
        'block_styles'         => ['css' => []],
        'block_assets'         => ['css' => [], 'js' => []],
        'block_assets--front'  => ['css' => [], 'js' => []],
        'block_assets--editor' => ['css' => [], 'js' => []],
        'editor'               => ['css' => [], 'js' => []],
        'front'                => ['css' => [], 'js' => []],
      ];
      
      $this->actions();
      $this->filters();
    }
    
    // Hooks : Actions
      public function actions() {
        add_action('enqueue_block_assets', [$this, 'action_enqueue_block_assets']);
        add_action('enqueue_block_editor_assets', [$this, 'action_enqueue_block_editor_assets']);
        add_action('wp_default_scripts', [$this, 'action_wp_default_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'action_wp_enqueue_scripts']);
        add_action('wp_loaded', [$this, 'action_wp_loaded']);
        
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
      }
      public function action_enqueue_block_assets() {
        $this->enqueue_in('block_assets');
        
        if ( is_admin() ) $this->enqueue_in('block_assets--editor');
        else $this->enqueue_in('block_assets--front');
      }
      public function action_enqueue_block_editor_assets() {
        $this->enqueue_in('editor');
      }
      public function action_wp_default_scripts( $scripts ) {
        /* Disable jQuery Migrate */
          if ( !is_admin() && isset($scripts->registered['jquery']) ) {
            $script = $scripts->registered['jquery'];
            
            if ( $script->deps ) { // Check whether the script has any dependencies
              $script->deps = array_diff($script->deps, array('jquery-migrate'));
            }
          }
      }
      public function action_wp_enqueue_scripts() {
        if ( is_archive() || is_front_page() || is_home() ) wp_deregister_script('wp-embed');
        
        $this->enqueue_in('front');
      }
      public function action_wp_loaded() {
        $this->register();
        $this->enqueue_in('block_styles');
      }
      
    // Hooks : Filters
      public function filters() {
        add_filter('script_loader_tag', array($this, 'filter_script_loader_tag'), 99, 3);
        add_filter('style_loader_tag', array($this, 'filter_style_loader_tag'), 99, 4);
      }
      public function filter_script_loader_tag( $tag, $handle, $src ) {
        if ( isset($this->config['js'][$handle]) ) {
          if ( isset($this->config['js'][$handle]['attrs']) ) {
            $attrs = fns::array_to_attrs($this->config['js'][$handle]['attrs'], TRUE);
            $tag = str_replace("><", " {$attrs}><", $tag);
          }
        }
        
        return $tag;
      }
      public function filter_style_loader_tag($html, $handle, $href, $media) {
        if ( isset($this->config['css'][$handle]['preload']) && $this->config['css'][$handle]['preload'] ) {
          $html = str_replace(array("media='all'", '/>'), array("media='print'", ' onload="this.media=\'all\'; this.onload=null;" />'), $html);
        }
        
        return $html;
      }
      
    // Register Scripts & Styles
      public function register() {
        foreach ($this->config as $type => $items) {
          if ( !empty($items) && $type !== 'min' ) {
            foreach ($items as $handle => $params) {
              $enqueue = isset($params['enqueue']) ? $params['enqueue'] : FALSE;
              
              if ( !$enqueue && (is_string($params) || (is_bool($params) && $params) || (is_array($params) && isset($params[0]))) ) $enqueue = $params;
              
              // Enqueue
                if ( $enqueue ) {
                  if ( !is_array($enqueue) ) $enqueue = [$enqueue];
                  
                  foreach ($enqueue as $item) {
                    $area   = FALSE;
                    $blocks = FALSE;
                    
                    if ( strpos($item, '/') ) {
                      $area   = 'block_styles';
                      $blocks = $item;
                    }
                    else if ( is_bool($params) && $params ) $area = 'front';
                    else $area = $item;
                    
                    if ( $area ) $this->enqueue[$area][$type][$handle] = $blocks;
                  }
                }
                
              // Definee Args and Register
                $args = $this->args($type, $handle, $params);
                
                if ( $type == 'css' ) wp_register_style($handle, $args['src'], $args['deps'], $args['ver'], $args['media']);
                else wp_register_script($handle, $args['src'], $args['deps'], $args['ver'], $args['in_footer']);
            }
          }
        }
      }
      public function enqueue_in( $area = 'front' ) {
        foreach ($this->enqueue[$area] as $type => $items) {
          foreach ($items as $handle => $params) {
            if ( $type == 'css' ) {
              if ( $area == 'block_styles' ) {
                $blocks = !is_array($params) ? [$params] : $params;
                
                foreach ($blocks as $block) {
                  wp_enqueue_block_style($block, [
                    'handle' => "plusfw-{$handle}",
                    'src'    => get_theme_file_uri("resources/{$type}/$handle.css"),
                    'path'   => get_theme_file_path("resources/{$type}/$handle.css")
                  ]);
                }
              }
              else wp_enqueue_style($handle);
            }
            else wp_enqueue_script($handle);
          }
        }
      }
      
    // Helper Functions
      private function args( $type, $handle, $params ) {
        $args = array('src' => FALSE, 'deps' => array(), 'ver' => wp_get_theme()->get('Version'));
        
        if ( 'css' === $type ) $args['media'] = 'all';
        
        if ( 'js' === $type ) {
          $args['localize']  = FALSE;
          $args['in_footer'] = FALSE;
        }
        
        if ( $params && is_array($params) ) $args = array_merge($args, $params);
        
        if ( !$args['src'] ) $args['src'] = $this->source($type, $handle);
        
        return $args;
      }
      private function localize( $handle, $items ) {
        $slug = str_replace('-', '_', $handle);
        
        /* Process */
          foreach ($items as $name => $item) {
            if ( is_array($item) ) {
              $type = array_key_first($item);
              
              switch ($type) {
                case 'env':
                  if ( defined($item[$type]) ) $items[$name] = constant($item[$type]);
                break;
                case 'fn':
                  $fn   = array_key_first($item[$type]);
                  $args = $item[$type][$fn];
                  
                  if ( strpos($fn, 'pxl_theme') !== FALSE ) {
                    global $frwk_theme;
                    
                    $seperator = strpos($fn, '::') ? '::' : '->';
                    $fn        = explode($seperator, $fn);
                    $method    = end($fn);
                    
                    if ( method_exists($frwk_theme, $method) ) {
                      if ( $seperator === '::' ) $items[$name] = $args ? $frwk_theme::$method($args) : $frwk_theme::$method();
                      else $items[$name] = $args ? $frwk_theme->$method($args) : $frwk_theme->$method();
                    }
                    else $items[$name] = NULL;
                  }
                  else {
                    $items[$name] = !function_exists($fn) ? NULL : (
                      $args ? $fn(...$args) : $fn()
                    );
                  }
                break;
                default:break;
              }
            }
          }
          
        wp_localize_script($handle, "vars_{$slug}", $items);
      }
      private function source( $type, $handle ) {
        global $frwk;
        
        $files = array();
        
        /* Identify Minified Version */
          if ( !strpos($handle, '.min') ) {
            if ( isset($this->config['min']) && $this->config['min'] !== FALSE ) {
              if ( $this->config['min'] === TRUE || ('live' === $this->config['min'] && !in_array($frwk->framework->domain['tld'], array('rds', 'test'))) ) {
                array_push($files, "resources/$type/$handle.min.{$type}");
              }
            }
          }
          
        /* Identify Regular Version */
          array_push($files, "resources/$type/$handle.{$type}");
          
        /* Find File */
          $file   = pathinfo(locate_template($files));
          $source = $file['basename'] != '' ? get_stylesheet_directory_uri() . "/resources/$type/{$file['basename']}" : FALSE;
          
        return $source;
      }
  }
endif;