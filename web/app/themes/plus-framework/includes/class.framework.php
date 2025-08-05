<?php
/**
 * Plus Framework
 * Handles custom functionality
 */

if ( !class_exists('plus_framework') ) :
  class plus_framework {
    private $config, $domain;
    
    function __construct( $config ) {
      $this->config = $config;
      
      foreach ($config as $fn => $params) {
        if ( $params && method_exists($this, $fn) ) $this->$fn($params);
      }
      
      $this->actions();
    }
    
    // Hooks : Actions
      public function actions() {}
      
    // Public Functions
      public function meta_tags() {
        $meta_tags = array(
          'referrer' => array('name' => 'referrer', 'content' => 'unsafe-url')
        );
        
        if ( isset($this->config['meta']) && is_array($this->config['meta']) ) {
          $meta_tags = array_merge($this->config['meta'], $meta_tags);
        }
        
        foreach ($meta_tags as $tag => $attrs) {
          $attrs = fns::array_to_attrs($attrs);
          printf("\t<meta %s>\n", $attrs);
        }
      }
      
    // Private Functions
      private function domain( $params ) {
        $this->domain = FALSE;
        
        if ( isset($_SERVER['HTTP_HOST']) ) {
          $domain  = filter_var(wp_unslash($_SERVER['HTTP_HOST']), FILTER_SANITIZE_ENCODED);
          $pattern = substr_count($domain, '.') > 1 ? '/(?<subdomain>[0-9a-z\-]*)[\.]{1}(?<domain>[0-9a-z\-]*)[\.]{1}(?<tld>[a-z]{2,})/' : '/(?<domain>[0-9a-z\-]*)[\.]{1}(?<tld>[a-z]{2,})/';
          
          preg_match($pattern, $domain, $host);
          
          $this->domain = array_merge(array('subdomain' => FALSE, 'domain' => FALSE, 'tld' => FALSE), $host);
        }
      }
      private function meta( $params ) {
        add_action('wp_head', array($this, 'meta_tags'), 1);
      }
  }
endif;