<?php
/**
 * Plus Init
 * Creates Menus, Post Types, Sidebars and Taxonies. Easily enable support for WordPress items.
 */

if ( !class_exists('plus_init') ) :
  class plus_init {
    public $blocks, $config, $framework;
    
    function __construct() {
      $this->init_blocks();
      $this->init_config();
    }
    
    private function init_blocks() {
      $this->blocks = [];
      
      if ( is_dir(BLOCK) ) {
        $blocks = array_diff(scandir(BLOCK), ['.', '..', '.DS_Store']);
        
        foreach ($blocks as $block) $this->blocks[] = str_replace('.php', '', $block);
      }
    }
    private function init_config() {
      /* Load Theme's config.json */
      $this->config = json_decode(file_get_contents(THEME . '/config.json'), TRUE);
      
      if ( !empty($this->config) ) {
        /* Require Specific Classes */
          $classes = array_keys($this->config);
          
          array_walk($classes, function ($file) { require_once("class.$file.php"); });
          
        /* Initialize Specific Classes */
          foreach ($this->config as $area => $config) {
            $class = "plus_$area";
            
            if ( class_exists($class) ) {
              $return = new $class($config);
              
              if ( 'framework' == $area ) $this->$area = $return;
            }
          }
      }
    }
  }
endif;