<?php
/**
 * Plus Plugins
 * Handles recommending and requiring plugins
 */

if ( !class_exists('plus_plugins') ) :
  require_once('class.tgm-plugin-activation.php');
  
  class plus_plugins {
    private $plugins;
    
    function __construct( $plugins ) {
      $this->plugins = $plugins;
      
      $this->actions();
    }
    
    // Hooks : Actions
      public function actions() {
        add_action('tgmpa_register', array($this, 'action_tgmpa_register'));
      }
      public function action_tgmpa_register() {
        $config = array(
          'id'           => 'plus-framework',
          'default_path' => '',
          'menu'         => 'tgmpa-install-plugins',
          'parent_slug'  => 'plugins.php',
          'capability'   => 'edit_theme_options',
          'has_notices'  => TRUE,
          'dismissable'  => TRUE,
          'dismiss_msg'  => '',
          'is_automatic' => FALSE,
          'message'      => '',
        );
        
        tgmpa($this->plugins, $config);
      }
  }
endif;