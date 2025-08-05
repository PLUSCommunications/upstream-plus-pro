<?php
/**
 * Plus ACF
 * Registeres the load and save paths for the config files.
 */

if ( !class_exists('plus_acf') ) {
  class plus_acf {
    // Properties
    
    // Constructor
      function __construct() {
        if ( class_exists('ACF') ) {
          $this->filters();
        }
      }
      
    // Hooks
      private function filters() {
        add_filter('acf/json/save_paths', [$this, 'filter_acf_json_save_paths'], 10, 2);
        add_filter('acf/settings/load_json', [$this, 'filter_acf_settings_load_json']);
      }
      public function filter_acf_json_save_paths( $paths, $post ) {
        if ( !empty($post['post_type']) )         $paths = [ THEME . '/acf-post-types' ];
        else if ( !empty($post['taxonomy']) )     $paths = [ THEME . '/acf-taxonomies' ];
        else if ( !empty($post['data_storage']) ) $paths = [ THEME . '/acf-options' ];
        else if ( !empty($post['location']) ) {
          $location = $post['location'][0][0];
          $path     = FALSE;
          
          switch ($location['param']) {
            case 'block':
              $block = explode('/', $location['value']);
              $paths = [ BLOCK . "/{$block[1]}" ];
            break;
            case 'options_page': $path = 'options'; break;
            case 'post_type': $path    = 'post-types'; break;
            case 'taxonomy': $path     = 'taxonomies'; break;
          }
          
          if ( $path ) $paths = [ THEME . "/acf-{$path}" ];
        }
        
        return $paths;
      }
      public function filter_acf_settings_load_json( $paths ) {
        global $frwk;
        
        $paths[] = THEME . '/acf-options';
        $paths[] = THEME . '/acf-post-types';
        $paths[] = THEME . '/acf-taxonomies';
        
        if ( !empty($frwk->blocks) ) {
          foreach ($frwk->blocks as $block) $paths[] = BLOCK . "/$block";
        }
        
        return $paths;
      }
  }
}
