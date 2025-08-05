<?php
/**
 * Plus WP
 * 
 */

if ( !class_exists('plus_wp') ) :
  class plus_wp {
    private $config;
    
    function __construct( $config ) {
      $this->config = $config;
      
      $this->actions();
      $this->filters();
    }
    
    // Hooks : Actions
      public function actions() {
        add_action('init', array($this, 'action_init'), 9);
        add_action('wp_dashboard_setup', array($this, 'action_wp_dashboard_setup'), 99);
      }
      public function action_init() {
        if ( gettype($this->config) == 'array' ) {
          foreach ($this->config as $area => $items) {
            if ( method_exists($this, $area) ) $this->$area($items);
          }
        }
        
        if ( is_admin() ) $this->setup();
        
        if ( isset($_REQUEST['author']) && preg_match('/\\d/', $_REQUEST['author']) > 0 && !is_user_logged_in() ) wp_die('YOU SHALL NOT PASS!');
      }
      public function action_wp_dashboard_setup() {
        global $wp_meta_boxes;
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
      }
      
    // Hooks : Filters
      public function filters() {
        add_filter('activated_plugin', array($this, 'filter_activate_plugin'), 101);
        add_filter('admin_email_check_interval', '__return_false');
        add_filter('facetwp_is_main_query', array($this, 'filter_facetwp_is_main_query'), 10, 2 );
        add_filter('gform_bypass_template_library', '__return_true');
        add_filter('gform_confirmation_anchor', '__return_false');
        add_filter('gform_default_notification', '__return_false');
        add_filter('gform_enable_field_label_visibility_settings', '__return_true');
        add_filter('gform_init_scripts_footer', '__return_true');
        add_filter('remove_author_from_oembed', array($this, 'filter_remove_author_from_oembed'), 10, 2 );
        add_filter('rest_endpoints', array($this, 'filter_rest_endpoints'), 900, 1);
        add_filter('script_loader_src', array($this, 'filter_loader_src'), 10, 2);
        add_filter('style_loader_src', array($this, 'filter_loader_src'), 10, 2);
        add_filter('the_generator', '__return_empty_string');
        add_filter('upload_mimes', array($this, 'filter_upload_mimes'), 20, 2);
        add_filter('wp_headers', array($this, 'filter_wp_headers'));
        add_filter('wp_sitemaps_add_provider', array($this, 'filter_wp_sitemaps_add_provider'), 10, 2);
        add_filter('xmlrpc_enabled', '__return_false');
        remove_action('wp_head', 'wp_generator');
      }
      public function filter_activate_plugin( $plugin ) {
        switch ($plugin) {
          case 'advanced-custom-fields-pro/acf.php':
            if ( !get_option('acf_pro_license') ) acf_pro_activate_license('b3JkZXJfaWQ9ODMyNTh8dHlwZT1kZXZlbG9wZXJ8ZGF0ZT0yMDE2LTA2LTA4IDE5OjU5OjUx');
          break;
          case 'gravityforms/gravityforms.php':
            if ( !get_option('rg_gforms_key') ) {
              update_option('rg_gforms_key', md5('363a287203343c50432f74da67b3a12e'));
              delete_option('gform_pending_installation');
              update_option('gform_enable_background_updates', 1);
              update_option('gform_enable_noconflict', 0);
              update_option('rg_gforms_captcha_private_key', '');
              update_option('rg_gforms_captcha_public_key', '');
              update_option('rg_gforms_currency', 'USD');
              update_option('rg_gforms_enable_akismet', 0);
              update_option('rg_gforms_message', '<!--GFM-->');
            }
          break;
          default:break;
        }
      }
      public function filter_facetwp_is_main_query( $is_main_query, $query ) {
        if ( 'template' == $query->get('post_type') ) $is_main_query = FALSE;
        
        return $is_main_query;
      }
      public function filter_loader_src( $src, $handle ) {
        $src  = esc_url(remove_query_arg('ver', $src));
        $file = $_SERVER['DOCUMENT_ROOT'] . wp_make_link_relative($src);
        
        if ( file_exists($file) ) {
          if ( strpos($file, 'wp-content') || strpos($file, 'wp-includes') ) $src = add_query_arg('u', filemtime($file), $src);
        }
        
        return $src;
      }
      public function filter_remove_author_from_oembed() {
        unset($data['author_url']);
        unset($data['author_name']);
        
        return $data;
      }
      public function filter_rest_endpoints( $rest_endpoints ) {
        if ( !is_admin() ) {
          if ( $this->is_gutenberg_request() ) return $rest_endpoints;
          
          $endpoints = array_keys($rest_endpoints);
          $ignore    = apply_filters('plus_framework_rest_endpoints_ignore', [], $endpoints);
          $routes    = [
            'batch',
            'oembed',
            'wp-block-editor',
            'wp-site-health',
            'wp/v2',
          ];
          
          foreach ($endpoints as $endpoint) {
            foreach ($routes as $route) {
              if ( strpos($endpoint, $route) !== FALSE ) {
                $unset = TRUE;
                
                if ( in_array($endpoint, $ignore) ) $unset = FALSE;
                
                if ( $unset ) unset($rest_endpoints[$endpoint]);
              }
            }
          }
        }
        
        return $rest_endpoints;
      }
      public function filter_upload_mimes( $t ) {
        $t['svg']   = 'image/svg+xml';
        $t['svgz']  = 'image/svg+xml';
        $t['webp']  = "image/webp";
        $t['woff']  = 'font/woff';
        $t['woff2'] = 'font/woff2';
        
        return $t;
        
      }
      public function filter_wp_headers( $headers ) {
        $headers['Referrer-Policy']        = 'no-referrer-when-downgrade';
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['X-Frame-Options']        = 'SAMEORIGIN';
        
        return $headers;
      }
      public function filter_wp_sitemaps_add_provider( $provider, $name ) {
        if ( 'users' === $name ) return false;
        
        return $provider;
      }
      
    // Private: Theme Setup
      private function setup() {
        if ( isset($_REQUEST['setup']) && $_REQUEST['setup'] == 'theme' ) {
          $this->setup_pages_posts();
          $this->setup_permalink_structure();
          $this->setup_clean();
          $this->setup_options();
          
          wp_redirect(admin_url("?setup=success"));
          exit;
        }
      }
      private function setup_clean() {
        if ( !function_exists('get_plugins') ) require_once ABSPATH . 'wp-admin/includes/plugin.php';
        
        if ( $plugins = get_plugins() ) {
          $remove = array('akismet/akismet.php', 'hello.php');
          
          foreach ($plugins as $name => $v) {
            if ( in_array($name, $remove) ) {
              if ( strpos($name, '/') !== FALSE ) {
                $dir = plugin_dir_path(WP_PLUGIN_DIR . '/' . $name);
                $this->setup_remove($dir);
              }
              else unlink(WP_PLUGIN_DIR . '/' . $name);
            }
          }
        }
        
        if ( $themes = get_site_transient('theme_roots') ) {
          foreach ($themes as $name => $v) {
            if ( strpos($name, 'twenty') !== FALSE ) $this->setup_remove(get_theme_root() . "/$name");
          }
        }
      }
      private function setup_remove( $dir ) {
        $files = array_diff(scandir($dir), array('.','..'));
        
        foreach ($files as $file) {
          (is_dir("$dir/$file")) ? $this->setup_remove("$dir/$file") : unlink("$dir/$file");
        }
        
        return rmdir($dir);
      }
      private function setup_options() {
        $options = array(
          'blogdescription'              => '',
          'close_comments_for_old_posts' => '',
          'comments_notify'              => '',
          'comment_moderation'           => 1,
          'comment_previously_approved'  => '',
          'comment_registration'         => 1,
          'default_comment_status'       => '',
          'default_pingback_flag'        => '',
          'default_ping_status'          => '',
          'page_comments'                => '',
          'moderation_notify'            => '',
          'require_name_email'           => 1,
          'show_avatars'                 => '',
        );
        
        foreach ($options as $option_name => $option_value) update_option($option_name, $option_value);
      }
      private function setup_pages_posts() {
        // Pages
          $pages = array(
            'homepage'  => get_post(2),
            'postspage' => FALSE,
            'privacy'   => FALSE,
          );
          
        // Delete "Hello World!" Post
          wp_delete_post(1, TRUE);
          
        // Update Home Page
          if ( $pages['homepage'] ) {
            $pages['homepage']->post_title   = 'Home';
            $pages['homepage']->post_name    = sanitize_title($pages['homepage']->post_title);
            $pages['homepage']->post_content = "";
          
            wp_update_post($pages['homepage']);
            update_option('page_on_front', $pages['homepage']->ID);
            update_option('show_on_front', 'page');
          }
          
        // Add Updates/Posts Page
          if ( $this->version != 'base' ) {
            $updates = array(
              'post_type'   => 'page',
              'post_title'  => 'Updates',
              'post_status' => 'publish'
            );
          
            if ( $postspage_id = wp_insert_post($updates) ) {
              update_option('page_for_posts', $postspage_id );
              $pages['postspage'] = get_post($postspage_id);
            }
          }
          
        // Add Privacy Policy page
          $page = new WP_Query([
            'title'     => 'Privacy Policy',
            'post_type' => 'page'
          ]);
          
          if ( $page->post ) $pages['privacy'] = $page->post;
          
        return $pages;
      }
      private function setup_permalink_structure() {
        global $wp_rewrite;
        
        $wp_rewrite->set_permalink_structure('/%postname%/');
        $wp_rewrite->flush_rules();
      }
      
    // Blocks
      private function blocks() {
        global $frwk;
        
        if ( !empty($frwk->blocks) ) {
          foreach ($frwk->blocks as $block) register_block_type(BLOCK . "/$block");
        }
      }
      
    // Helpers
      private function is_gutenberg_request() {
        global $pagenow;
        
        if ( isset( $pagenow ) && ( 'post-new.php' === $pagenow ) ) return true;
        
        if ( isset( $_REQUEST['_locale'] ) ) return true;
        
        if ( isset( $_REQUEST['action'] ) && ( 'edit' === $_REQUEST['action'] ) ) return true;
        
        if ( isset( $_REQUEST['action'] ) && ( 'acf/ajax/render_block_preview' === $_REQUEST['action'] ) ) return true;
        
        if ( isset( $_REQUEST['context'] ) && ( 'edit' === $_REQUEST['context'] ) ) return true;
        
        return FALSE;
      }
  }
endif;