<?php if ( !class_exists('plus_theme') ) {
  class plus_theme {
    private $gtm, $palette;
    
    function __construct() {
      $this->palette = array_column(wp_get_global_settings(['color','palette','theme']), NULL, 'slug');
      
      $this->actions();
      $this->filters();
    }
    
    // Hooks : Actions
      public function actions() {
        add_action('acf/init', array($this, 'action_acf_init'));
        add_action('acf/input/admin_footer', array($this, 'action_acf_input_admin_footer'), 99);
        add_action('enqueue_block_assets', array($this, 'action_enqueue_block_assets'));
        add_action('enqueue_block_editor_assets', array($this, 'action_enqueue_block_editor_assets'));
        add_action('gform_enqueue_scripts', array($this, 'action_gform_enqueue_scripts'), 10);
        add_action('init', array($this, 'action_init'));
        add_action('pre_get_posts', array($this, 'action_pre_get_posts'));
        add_action('rest_authentication_errors', array($this, 'action_rest_authentication_errors'), 99);
				add_action('template_redirect', array($this, 'action_template_redirect'), 10);
				add_action('wp_body_open', array($this, 'action_wp_body_open'));
        add_action('wp_head', array($this, 'action_wp_head'), 100);
        add_action('wp_head', array($this, 'action_wp_head_schema'), 101);
      }
			
      public function action_acf_init() {
        acf_update_setting('enable_shortcode', TRUE);
        
        // Set Google Maps API key if available
        if (function_exists('get_field')) {
          $google_api_key = get_field('google_api_key', 'options');
          if ($google_api_key) {
            acf_update_setting('google_api_key', $google_api_key);
          }
        }
      }
      public function action_acf_input_admin_footer() {
        if ( class_exists('ACF') ) {
          $new = '';
          
          foreach ($this->palette as $custom_color) $new .= sprintf("'%s', ", $custom_color['color']);
          
          echo "
            <script id='acf-admin-script' type='text/javascript'>
              acf.add_filter('color_picker_args', function(args, field) {
                args.palettes = [ $new ];
                return args;
              });
            </script>
          ";
        }
      }
      public function action_enqueue_block_assets() {
        if ( function_exists('get_field') && $typekit = get_field('typekit', 'options') ) {
          wp_enqueue_style('typekit', $typekit);
        }
      }
      public function action_enqueue_block_editor_assets() {
        $stylesheet_uri = get_stylesheet_directory_uri();
        $stylesheet_dir = get_stylesheet_directory();
        
        wp_enqueue_script(
          'mytheme-block-editor-globals',
          $stylesheet_uri . '/block-editor/block-editor-globals.js',
          [ 'wp-element', 'wp-components', 'wp-block-editor', 'wp-compose', 'wp-hooks', 'wp-api-fetch' ],
          filemtime($stylesheet_dir . '/block-editor/block-editor-globals.js'),
          TRUE
        );
        
        wp_enqueue_script(
          'pro-custom-format',
          $stylesheet_uri . '/block-editor/custom-format.js',
          array( 'wp-rich-text', 'wp-editor', 'wp-element', 'wp-components', 'wp-i18n' ),
          '1.0',
          TRUE
        );
        
        wp_enqueue_script(
          'pro-extend-media-text',
          $stylesheet_uri . '/block-editor/extend-media-text.js',
          array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-post', 'wp-components', 'wp-element', 'wp-compose', 'wp-editor' ),
          filemtime( $stylesheet_dir . '/block-editor/extend-media-text.js' ),
          TRUE
        );
        
        wp_enqueue_script(
          'pro-extend-query',
          $stylesheet_uri . '/block-editor/extend-query.js',
          array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-post', 'wp-components', 'wp-element', 'wp-compose', 'wp-editor' ),
          filemtime( $stylesheet_dir . '/block-editor/extend-query.js' ),
          TRUE
        );
        
        wp_enqueue_script(
          'pro-extend-text-shadow',
          $stylesheet_uri . '/block-editor/extend-text-shadow.js',
          array( 'wp-blocks', 'wp-dom-ready', 'wp-editor', 'wp-components', 'wp-element', 'wp-compose', 'wp-block-editor', 'wp-hooks' ),
          filemtime( $stylesheet_dir . '/block-editor/extend-text-shadow.js' ),
          TRUE
        );
        
      }
      public function action_gform_enqueue_scripts() {
        wp_enqueue_style('gf');
        // gravity_form_enqueue_scripts(1, TRUE);
      }
      public function action_init() {
        $this->gutenberg();
        remove_theme_support('core-block-patterns');
        
        // Register REST filters for all public post types
        foreach ( get_post_types([ 'public' => TRUE ]) as $type ) {
					add_filter("rest_{$type}_query", function( $args, $request ) use ( $type ) {
					  $is_query_preview = false;

					  if ( isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'block-editor') !== false ) {
					    // Look for typical preview REST call context
					    $is_query_preview = strpos($_SERVER['HTTP_REFERER'], 'context=edit') !== false
					      || strpos($_SERVER['HTTP_REFERER'], 'post=') !== false;
					  }

					  if ( ! $is_query_preview ) {
					    return $args;
					  }

					  return $this->filter_rest_query_manual_posts( $args, $request );
					}, 10, 2 );
        }
      }
      public function action_pre_get_posts( $query ){
        // for testing purposes
      }
      public function action_rest_authentication_errors( $access ) {
        if ( is_user_logged_in() ) return $access;
        
        if ( 
          preg_match('/users/i', $_SERVER['REQUEST_URI']) !== 0
          || (
            isset($_REQUEST['rest_route'])
            &&
            preg_match('/users/i', $_REQUEST['rest_route']) !== 0
          )
        ) {
          return new \WP_Error(
            'rest_cannot_access',
            'Only authenticated users can access the User endpoint REST API.',
            ['status' => rest_authorization_required_code()]
          );
        }
        
        return $access;
      }
      public function action_template_redirect() {
				if ( is_admin() || is_preview() ) return;
        if ( class_exists('ACF') ) {
				  if ( is_singular() && get_field('redirect') ) {
				  	$override = get_field('override');
				  	error_log('🔍 override: ' . var_export($override, true));
          
				  	if ( in_array($override, ['url', 'internal'], true) ) {
				  		$override_object = get_field($override);
				  		error_log('📦 override_object: ' . print_r($override_object, true));
          
				  		switch ( $override ) {
				  			case 'url':
				  				wp_redirect($override_object, 301);
				  				exit;
				  			case 'internal':
				  				$url = get_permalink($override_object->ID);
				  				wp_redirect($url, 301);
				  				exit;
				  		}
				  	} else {
				  		error_log('⏭ override is "none" or invalid, skipping redirect');
				  	}
				  }
        }
      }
			public function action_wp_body_open() {
        if ( $this->gtm ) {
          printf('
            <!-- Google Tag Manager (noscript): %s -->
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-%s"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->
            ',
            $this->gtm['title'],
            $this->gtm['tag']
          );
        }
      }
      public function action_wp_head() {
        $this->google_tag_manager();
        
        /*
          This SVG is printed with no size so it is not visible and takes up no space.
          It contains gradients with unique id's that can be used as gradient fills for icons.
        */
        echo(
          "<svg width='0' height='0' version='1.1' xmlns='http://www.w3.org/2000/svg' style='position:absolute;'>
            <defs>
              <linearGradient id='primary-to-secondary' x1='0' x2='1' y1='0' y2='1'>
                <stop offset='0%' stop-color='{$this->palette['primary']['color']}' />
                <stop offset='100%' stop-color='{$this->palette['secondary']['color']}' />
              </linearGradient>
              <linearGradient id='primary-to-tertiary' x1='0' x2='1' y1='0' y2='1'>
                <stop offset='0%' stop-color='{$this->palette['primary']['color']}' />
                <stop offset='100%' stop-color='{$this->palette['tertiary']['color']}' />
              </linearGradient>
              <linearGradient id='secondary-to-tertiary'' x1='0' x2='1' y1='0' y2='1'>
                <stop offset='0%' stop-color='{$this->palette['secondary']['color']}' />
                <stop offset='100%' stop-color='{$this->palette['tertiary']['color']}' />
              </linearGradient>
            </defs>
          </svg>"
        );
      }
      
      public function action_wp_head_schema() {
        if (!function_exists('get_field')) return;
        
        $schemas = [];
        
        // Organization OR Person Schema
        $org_name = get_field('organization_name', 'options');
        $org_type = get_field('organization_type', 'options') ?: 'Organization';
        
        if ($org_name) {
          if ($org_type === 'Person') {
            // Person Schema
            $person_schema = [
              '@context' => 'https://schema.org',
              '@type' => 'Person',
              'name' => $org_name,
              'url' => home_url()
            ];
            
            // Add person-specific fields
            $given_name = get_field('person_given_name', 'options');
            $family_name = get_field('person_family_name', 'options');
            
            if ($given_name) {
              $person_schema['givenName'] = $given_name;
            }
            if ($family_name) {
              $person_schema['familyName'] = $family_name;
            }
            
            $job_title = get_field('person_job_title', 'options');
            if ($job_title) {
              $person_schema['jobTitle'] = $job_title;
            }
            
            $works_for = get_field('person_works_for', 'options');
            if ($works_for) {
              $person_schema['worksFor'] = [
                '@type' => 'Organization',
                'name' => $works_for
              ];
            }
            
            $birth_date = get_field('person_birth_date', 'options');
            if ($birth_date) {
              $person_schema['birthDate'] = $birth_date;
            }
            
            $nationality = get_field('person_nationality', 'options');
            if ($nationality) {
              $person_schema['nationality'] = $nationality;
            }
            
            $political_affiliation = get_field('person_political_affiliation', 'options');
            if ($political_affiliation) {
              $person_schema['affiliation'] = [
                '@type' => 'Organization',
                'name' => $political_affiliation
              ];
            }
            
            // Add education
            $education = get_field('person_education', 'options');
            if ($education && is_array($education)) {
              $education_array = [];
              foreach ($education as $edu) {
                if (!empty($edu['institution'])) {
                  $edu_item = [
                    '@type' => 'EducationalOrganization',
                    'name' => $edu['institution']
                  ];
                  if (!empty($edu['degree'])) {
                    $edu_item['hasCredential'] = $edu['degree'];
                  }
                  $education_array[] = $edu_item;
                }
              }
              if (!empty($education_array)) {
                $person_schema['alumniOf'] = $education_array;
              }
            }
            
            // Add shared fields (logo as image, address, phone, email, description, founding date as birth date)
            $org_logo = get_field('organization_logo', 'options');
            if ($org_logo && isset($org_logo['url'])) {
              $person_schema['image'] = [
                '@type' => 'ImageObject',
                'url' => $org_logo['url'],
                'width' => isset($org_logo['width']) ? $org_logo['width'] : null,
                'height' => isset($org_logo['height']) ? $org_logo['height'] : null
              ];
              
              // Remove null values
              $person_schema['image'] = array_filter($person_schema['image'], function($value) {
                return $value !== null;
              });
            }
            
            $org_address = get_field('organization_address', 'options');
            if ($org_address && !empty($org_address['address'])) {
              $address_schema = [
                '@type' => 'PostalAddress'
              ];
              
              $address_schema['streetAddress'] = $org_address['address'];
              
              if (isset($org_address['street_number']) && isset($org_address['street_name'])) {
                $address_schema['streetAddress'] = trim($org_address['street_number'] . ' ' . $org_address['street_name']);
              }
              
              if (isset($org_address['city']) && !empty($org_address['city'])) {
                $address_schema['addressLocality'] = $org_address['city'];
              }
              
              if (isset($org_address['state']) && !empty($org_address['state'])) {
                $address_schema['addressRegion'] = $org_address['state'];
              }
              
              if (isset($org_address['post_code']) && !empty($org_address['post_code'])) {
                $address_schema['postalCode'] = $org_address['post_code'];
              }
              
              if (isset($org_address['country']) && !empty($org_address['country'])) {
                $address_schema['addressCountry'] = $org_address['country'];
              }
              
              $person_schema['address'] = $address_schema;
            }
            
            $org_phone = get_field('organization_phone', 'options');
            if ($org_phone) {
              $person_schema['telephone'] = $org_phone;
            }
            
            $org_email = get_field('organization_email', 'options');
            if ($org_email) {
              $person_schema['email'] = $org_email;
            }
            
            $org_description = get_field('organization_description', 'options');
            if ($org_description) {
              $person_schema['description'] = $org_description;
            }
            
            // Add social media profiles from existing socials repeater
            $social_profiles = get_field('socials', 'options');
            if ($social_profiles && is_array($social_profiles)) {
              $same_as = [];
              foreach ($social_profiles as $profile) {
                if (!empty($profile['link'])) {
                  $same_as[] = $profile['link'];
                }
              }
              if (!empty($same_as)) {
                $person_schema['sameAs'] = $same_as;
              }
            }
            
            $schemas[] = $person_schema;
            
          } else {
            // Organization Schema (existing code)
            $org_schema = [
              '@context' => 'https://schema.org',
              '@type' => $org_type,
              'name' => $org_name,
              'url' => home_url()
            ];
            
            // Add logo if provided (enhanced with ImageObject)
            $org_logo = get_field('organization_logo', 'options');
            if ($org_logo && isset($org_logo['url'])) {
              $org_schema['logo'] = [
                '@type' => 'ImageObject',
                'url' => $org_logo['url'],
                'width' => isset($org_logo['width']) ? $org_logo['width'] : null,
                'height' => isset($org_logo['height']) ? $org_logo['height'] : null
              ];
              
              // Remove null values
              $org_schema['logo'] = array_filter($org_schema['logo'], function($value) {
                return $value !== null;
              });
            }
            
            // Add address if provided  
            $org_address = get_field('organization_address', 'options');
            if ($org_address && !empty($org_address['address'])) {
              $address_schema = [
                '@type' => 'PostalAddress'
              ];
              
              // Use the full formatted address as streetAddress if no components available
              $address_schema['streetAddress'] = $org_address['address'];
              
              // Extract detailed address components if available
              if (isset($org_address['street_number']) && isset($org_address['street_name'])) {
                $address_schema['streetAddress'] = trim($org_address['street_number'] . ' ' . $org_address['street_name']);
              }
              
              if (isset($org_address['city']) && !empty($org_address['city'])) {
                $address_schema['addressLocality'] = $org_address['city'];
              }
              
              if (isset($org_address['state']) && !empty($org_address['state'])) {
                $address_schema['addressRegion'] = $org_address['state'];
              }
              
              if (isset($org_address['post_code']) && !empty($org_address['post_code'])) {
                $address_schema['postalCode'] = $org_address['post_code'];
              }
              
              if (isset($org_address['country']) && !empty($org_address['country'])) {
                $address_schema['addressCountry'] = $org_address['country'];
              }
              
              $org_schema['address'] = $address_schema;
            }
            
            // Add phone if provided
            $org_phone = get_field('organization_phone', 'options');
            if ($org_phone) {
              $org_schema['telephone'] = $org_phone;
            }
            
            // Add email if provided
            $org_email = get_field('organization_email', 'options');
            if ($org_email) {
              $org_schema['email'] = $org_email;
            }
            
            // Add additional recommended properties
            $org_description = get_field('organization_description', 'options');
            if ($org_description) {
              $org_schema['description'] = $org_description;
            }
            
            $org_founding_date = get_field('organization_founding_date', 'options');
            if ($org_founding_date) {
              $org_schema['foundingDate'] = $org_founding_date;
            }
            
            // Add social media profiles from existing socials repeater
            $social_profiles = get_field('socials', 'options');
            if ($social_profiles && is_array($social_profiles)) {
              $same_as = [];
              foreach ($social_profiles as $profile) {
                if (!empty($profile['link'])) {
                  $same_as[] = $profile['link'];
                }
              }
              if (!empty($same_as)) {
                $org_schema['sameAs'] = $same_as;
              }
            }
            
            $schemas[] = $org_schema;
          }
        
          
          // Add logo if provided (enhanced with ImageObject)
          $org_logo = get_field('organization_logo', 'options');
          if ($org_logo && isset($org_logo['url'])) {
            $org_schema['logo'] = [
              '@type' => 'ImageObject',
              'url' => $org_logo['url'],
              'width' => isset($org_logo['width']) ? $org_logo['width'] : null,
              'height' => isset($org_logo['height']) ? $org_logo['height'] : null
            ];
            
            // Remove null values
            $org_schema['logo'] = array_filter($org_schema['logo'], function($value) {
              return $value !== null;
            });
          }
          
          // Add address if provided  
          $org_address = get_field('organization_address', 'options');
          if ($org_address && !empty($org_address['address'])) {
            $address_schema = [
              '@type' => 'PostalAddress'
            ];
            
            // Use the full formatted address as streetAddress if no components available
            $address_schema['streetAddress'] = $org_address['address'];
            
            // Extract detailed address components if available
            if (isset($org_address['street_number']) && isset($org_address['street_name'])) {
              $address_schema['streetAddress'] = trim($org_address['street_number'] . ' ' . $org_address['street_name']);
            }
            
            if (isset($org_address['city']) && !empty($org_address['city'])) {
              $address_schema['addressLocality'] = $org_address['city'];
            }
            
            if (isset($org_address['state']) && !empty($org_address['state'])) {
              $address_schema['addressRegion'] = $org_address['state'];
            }
            
            if (isset($org_address['post_code']) && !empty($org_address['post_code'])) {
              $address_schema['postalCode'] = $org_address['post_code'];
            }
            
            if (isset($org_address['country']) && !empty($org_address['country'])) {
              $address_schema['addressCountry'] = $org_address['country'];
            }
            
            $org_schema['address'] = $address_schema;
          }
          
          // Add phone if provided
          $org_phone = get_field('organization_phone', 'options');
          if ($org_phone) {
            $org_schema['telephone'] = $org_phone;
          }
          
          // Add email if provided
          $org_email = get_field('organization_email', 'options');
          if ($org_email) {
            $org_schema['email'] = $org_email;
          }
          
          // Add additional recommended properties
          $org_description = get_field('organization_description', 'options');
          if ($org_description) {
            $org_schema['description'] = $org_description;
          }
          
          $org_founding_date = get_field('organization_founding_date', 'options');
          if ($org_founding_date) {
            $org_schema['foundingDate'] = $org_founding_date;
          }
          
          // Add social media profiles from existing socials repeater
          $social_profiles = get_field('socials', 'options');
          if ($social_profiles && is_array($social_profiles)) {
            $same_as = [];
            foreach ($social_profiles as $profile) {
              if (!empty($profile['link'])) {
                $same_as[] = $profile['link'];
              }
            }
            if (!empty($same_as)) {
              $org_schema['sameAs'] = $same_as;
            }
          }
          
          $schemas[] = $org_schema;
        }
        
        
        // Article Schema for single posts
        if (is_single() && get_post_type() === 'post') {
          global $post;
          
          // Get the selected schema type, default to Article
          $schema_type = get_field('post_schema_type', 'options') ?: 'Article';
          
          $article_schema = [
            '@context' => 'https://schema.org',
            '@type' => $schema_type,
            'headline' => get_the_title(),
            'url' => get_permalink(),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'author' => [
              '@type' => 'Person',
              'name' => get_the_author()
            ]
          ];
          
          // Add featured image if available
          if (has_post_thumbnail()) {
            $thumbnail_id = get_post_thumbnail_id();
            $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'full');
            if ($thumbnail_url) {
              $article_schema['image'] = $thumbnail_url;
            }
          }
        
          // Add excerpt as description
          $excerpt = get_the_excerpt();
          if ($excerpt) {
            $article_schema['description'] = $excerpt;
          }
          
          // Add publisher if organization name is set
          if ($org_name) {
            $article_schema['publisher'] = [
              '@type' => 'Organization', 
              'name' => $org_name
            ];
            
            // Add publisher logo if available
            if ($org_logo && isset($org_logo['url'])) {
              $article_schema['publisher']['logo'] = [
                '@type' => 'ImageObject',
                'url' => $org_logo['url']
              ];
            }
          }
          
          $schemas[] = $article_schema;
        }
        
        // Output schemas
        if (!empty($schemas)) {
          foreach ($schemas as $schema) {
            echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
          }
        }
      }
      
    // Hooks : Filters
      public function filters() {
        add_filter('acf/blocks/wrap_frontend_innerblocks', array($this, 'filter_acf_blocks_wrap_frontend_innerblocks'), 10, 2);
        add_filter('acf/fields/page_link/result', array($this, 'filter_acf_fields_page_link_result'), 10, 4);
        add_filter('acf/shortcode/allow_in_block_themes_outside_content', '__return_true');
        add_filter('allowed_block_types_all', array($this, 'filter_allowed_block_types_all'), 10, 2);
        add_filter('block_categories_all', array($this, 'filter_block_categories_all'), 10, 2);
        add_filter('block_editor_settings_all', array($this, 'filter_block_editor_settings_all'), 1);
        add_filter('block_type_metadata_settings', array($this, 'filter_block_type_metadata_settings'), 10, 2 );
        add_filter('gform_default_styles', array($this, 'filter_gform_default_styles'), 10, 2);
        add_filter('query_loop_block_query_vars', array($this, 'filter_query_loop_block_query_vars'), 10, 2 );
        add_filter('render_block_data', array($this, 'filter_render_block_data'), 10, 1 );
        add_filter('render_block', array($this, 'filter_render_block'), 10, 2 );
        add_filter('script_loader_tag', array($this, 'filter_script_loader_tag'), 99, 3);
        add_filter('wp_theme_json_data_theme', array($this, 'filter_wp_theme_json_data_theme'), 1);
      }
      public function filter_acf_blocks_wrap_frontend_innerblocks( $wrap, $name ) {
        $wrap = in_array(
          $name,
          [
            'acf/pro-icon',
            'pluspro/pro-accordion-item',
            'pluspro/pro-accordion',
            'pluspro/pro-carousel',
            'pluspro/pro-parallax',
          ]
        );
        
        return $wrap;
      }
      public function filter_acf_fields_page_link_result( $text, $post, $field, $post_id ) {
        if ( $post->post_parent ) {
          $parent_post = get_post( $post->post_parent );
          $text        = $parent_post->post_title . ' -' . $text;
        }
        
        return $text;
      }
      public function filter_allowed_block_types_all( $allowed_block_types, $editor_context ) {
        // Get all registered blocks.
        $blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
        
        // Remove all Core blocks.
        $non_core_blocks = array_filter(
          $blocks,
          function( $block_type ) {
            return strpos( $block_type->name, 'core/' ) !== 0;
          }
        );
        
        $non_core_blocks = array_keys( $non_core_blocks );
        
        // Re-add the Core blocks we want to have.
        $allowed_core_blocks = [
          'core/archives',
          'core/audio',
          // 'core/avatar',
          'core/block',
          // 'core/button',
          // 'core/buttons',
          'core/calendar',
          'core/categories',
          'core/code',
          'core/column',
          'core/columns',
          // 'core/comment-author-name',
          // 'core/comment-content',
          // 'core/comment-date',
          // 'core/comment-edit-link',
          // 'core/comment-reply-link',
          // 'core/comment-template',
          // 'core/comments',
          // 'core/comments-pagination',
          // 'core/comments-pagination-next',
          // 'core/comments-pagination-numbers',
          // 'core/comments-pagination-previous',
          // 'core/comments-title',
          'core/cover',
          'core/embed',
          'core/file',
          'core/freeform',
          'core/gallery',
          'core/group',
          'core/heading',
          'core/home-link',
          'core/html',
          'core/image',
          'core/list',
          'core/list-item',
          'core/loginout',
          'core/media-text',
          'core/missing',
          'core/more',
          'core/navigation',
          'core/navigation-link',
          'core/navigation-submenu',
          'core/nextpage',
          'core/page-list',
          'core/paragraph',
          'core/pattern',
          'core/post-author',
          'core/post-author-biography',
          // 'core/post-comments',
          // 'core/post-comments-form',
          'core/post-content',
          'core/post-date',
          'core/post-excerpt',
          'core/post-featured-image',
          'core/post-navigation-link',
          'core/post-template',
          'core/post-terms',
          'core/post-title',
          'core/preformatted',
          'core/pullquote',
          'core/query',
          'core/query-no-results',
          'core/query-pagination',
          'core/query-pagination-next',
          'core/query-pagination-numbers',
          'core/query-pagination-previous',
          'core/query-title',
          'core/quote',
          'core/read-more',
          'core/rss',
          'core/search',
          'core/separator',
          'core/shortcode',
          // 'core/site-logo',
          'core/site-tagline',
          'core/site-title',
          'core/social-link',
          'core/social-links',
          // 'core/spacer',
          'core/table',
          // 'core/tag-cloud',
          'core/template-part',
          'core/term-description',
          'core/text-columns',
          'core/verse',
          'core/video',
          'core/widget-group',
        ];
        
        // Add our allowed Core blocks back to the allowed blocks, and return the result.
        return array_merge(
          $allowed_core_blocks,
          $non_core_blocks
        );
      }
      public function filter_block_categories_all( $block_categories ) {
        $pro_cat = array(
          'slug'  => 'pro',
          'title' => 'Plus Pro',
          'icon'  => 'smiley',
        );
        
        array_unshift($block_categories , $pro_cat);
        
        return $block_categories;
      }
      public function filter_block_type_metadata_settings( $settings, $metadata ) {
        if ( $metadata['name'] === 'core/media-text' ) $settings['supports']['shadow'] = TRUE;
        
        if ( $metadata['name'] === 'core/query' ) {
          $settings['attributes']['manualPostIds'] = [
            'type'    => 'array',
            'default' => [],
            'items'   => [
              'type' => 'number',
            ],
          ];
        }
        
        return $settings;
      }
      public function filter_block_editor_settings_all( $theme ) {
        $theme['gradients'] = array_merge($theme['gradients'], [
          array(
            'slug'     => 'primary-to-secondary',
            'gradient' => 'linear-gradient(160deg, '.$this->palette['primary']['color'].', '.$this->palette['secondary']['color'].')',
            'name'     => 'Primary to Secondary'
          ),
          array(
            'slug'     => 'primary-to-tertiary',
            'gradient' => 'linear-gradient(160deg, '.$this->palette['primary']['color'].', '.$this->palette['tertiary']['color'].')',
            'name'     => 'Primary to Tertiary'
          ),
          array(
            'slug'     => 'secondary-to-tertiary',
            'gradient' => 'linear-gradient(160deg, '.$this->palette['secondary']['color'].', '.$this->palette['tertiary']['color'].')',
            'name'     => 'Secondary to Tertiary'
          ),
          array(
            'slug'     => 'primary-to-transparent',
            'gradient' => 'linear-gradient(160deg, '.$this->palette['primary']['color'].', '.$this->palette['primary']['color'].'00)',
            'name'     => 'Primary to Transparent'
          ),
          array(
            'slug'     => 'secondary-to-transparent',
            'gradient' => 'linear-gradient(160deg, '.$this->palette['secondary']['color'].', '.$this->palette['secondary']['color'].'00)',
            'name'     => 'Secondary to Transparent'
          ),
          array(
            'slug'     => 'tertiary-to-transparent',
            'gradient' => 'linear-gradient(160deg, '.$this->palette['tertiary']['color'].', '.$this->palette['tertiary']['color'].'00)',
            'name'     => 'Tertiary to Transparent'
          ),
          array(
            'slug'     => 'base-to-contrast',
            'gradient' => 'linear-gradient(160deg, '.$this->palette['base']['color'].', '.$this->palette['contrast']['color'].')',
            'name'     => 'Base to Contrast'
          ),
          array(
            'slug'     => 'base-to-transparent',
            'gradient' => 'linear-gradient(160deg, '.$this->palette['base']['color'].', '.$this->palette['base']['color'].'00)',
            'name'     => 'Base to Transparent'
          ),
          array(
            'slug'     => 'contrast-to-transparent',
            'gradient' => 'linear-gradient(160deg, '.$this->palette['contrast']['color'].', '.$this->palette['contrast']['color'].'00)',
            'name'     => 'Contrast to Transparent'
          )
        ]);
        
        // [2024-10-12:Dewey] Needed for now in order for the gradients to show up in the editor.
        $theme['__experimentalFeatures']['color']['gradients']['theme'] = $theme['gradients'];
        
        return $theme;
      }
      public function filter_gform_default_styles( $styles ){
        if( !$styles ) $styles = array();
        
        if ( function_exists('get_field') ) $radius = get_field('border_radius','options') ? : "0";
        else $radius = 0;
        
        //$styles['theme'] = "Orbital Theme";
        //$styles['inputSize'] = ;
        $styles['inputBorderRadius'] = $radius;
        $styles['inputBorderColor'] = "#DDDDDD";
        $styles['inputBackgroundColor'] = $this->palette['base']['color'];
        $styles['inputColor'] = $this->palette['contrast']['color'];
        //$styles['inputPrimaryColor'] = ;
        $styles['labelFontSize'] = "16";
        $styles['labelColor'] = $this->palette['contrast']['color'];
        $styles['descriptionFontSize'] = "14";
        $styles['descriptionColor'] = $this->palette['contrast']['color'];
        $styles['buttonPrimaryBackgroundColor'] = $this->palette['primary']['color'];
        $styles['buttonPrimaryColor'] = $this->palette['base']['color'];
        
        return $styles;
      }
      public function filter_query_loop_block_query_vars( $query_args, $block ){
        if ( empty($GLOBALS['mytheme_manual_post_ids']) || !is_array($GLOBALS['mytheme_manual_post_ids']) ) {
          return $query_args;
        }
        
        $manual_ids = is_array( $GLOBALS['mytheme_manual_post_ids'] ) ? $GLOBALS['mytheme_manual_post_ids'] : [ $GLOBALS['mytheme_manual_post_ids'] ];
        
        if ( empty($manual_ids) ) return $query_args;

        $query_args['post__in'] = $manual_ids;
        $query_args['orderby']  = 'post__in';
        $query_args['nopaging'] = TRUE;
        
        return $query_args;
      }
      public function filter_render_block( $block_content, $block ) {
        // Ensure we always return a string to prevent fatal errors
        if ( ! is_string( $block_content ) ) {
          error_log('render_block received null or non-string — returning empty string.');
          $block_content = '';
        }
        
        // MEDIA/TEXT BLOCK CUSTOMIZATION
        if ( $block['blockName'] === 'core/media-text' ) {
          $attrs = $block['attrs'] ?? [];
          
          $video_attributes = [
            'autoplayVideo'    => 'autoplay',
            'loopVideo'        => 'loop',
            'mutedVideo'       => 'muted',
            'controlsVideo'    => 'controls',
            'playsinlineVideo' => 'playsinline',
          ];
          
          $enabled_attrs = [];
          
          foreach ( $video_attributes as $attr_key => $html_attr ) {
            if ( !empty($attrs[$attr_key]) ) $enabled_attrs[] = $html_attr;
          }
          
          if ( ! empty( $enabled_attrs ) ) {
            $block_content = preg_replace_callback(
              '/<video(.*?)>/i',
              function ( $matches ) use ( $enabled_attrs ) {
                $existing = $matches[1];
                $cleaned  = preg_replace(
                  '/\s(?:autoplay|loop|muted|controls|playsinline)\b(="[^"]*")?/i',
                  '',
                  $existing
                );
                
                $new_attrs = implode( ' ', $enabled_attrs );
                
                return '<video' . $cleaned . ' ' . $new_attrs . '>';
              },
              $block_content
            );
          }
        }
        
        // QUERY BLOCK: MANUAL POST SELECTION
        if ( $block['blockName'] === 'core/query' ) {
          $attrs = $block['attrs'] ?? [];
          
          if ( isset($attrs['manualPostIds']) && is_array($attrs['manualPostIds']) ) {
            static $manual_post_stack = [];
            
            // Push to global stack
            array_unshift($manual_post_stack, $attrs['manualPostIds']);
            
            // Save to global for next block
            $GLOBALS['mytheme_manual_post_ids'] = $manual_post_stack;
          }
        }
				
				// Archive Title to work on Blog Home
				if ($block['blockName'] === 'core/query-title' && is_home()) {
					
					$posts_page_id = get_option('page_for_posts');

					if (function_exists('apply_filters')) {
						$posts_page_id = apply_filters('wpml_object_id', $posts_page_id, 'page', true);
					}

					if ($posts_page_id) {
						$title = esc_html(get_the_title($posts_page_id));

						$attrs = $block['attrs'];

						$level = isset($attrs['level']) && in_array($attrs['level'], [1,2,3,4,5,6]) ? (int) $attrs['level'] : 1;

						$class_names = ['wp-block-query-title'];
						if (!empty($attrs['textAlign'])) {
							$class_names[] = 'has-text-align-' . sanitize_html_class($attrs['textAlign']);
						}
						if (!empty($attrs['className'])) {
							$class_names[] = sanitize_html_class($attrs['className']);
						}
						if (!empty($attrs['align'])) {
							$class_names[] = 'align' . sanitize_html_class($attrs['align']);
						}

						$block_content = sprintf(
							'<h%d class="%s">%s</h%d>',
							$level,
							implode(' ', $class_names),
							$title,
							$level
						);
					}
				}
        
        return $block_content;
      }
      public function filter_render_block_data( $block ){
        if ( $block['blockName'] === 'core/query' && !empty($block['attrs']['manualPostIds']) ) {
          $GLOBALS['mytheme_manual_post_ids'] = $block['attrs']['manualPostIds'];
        }
        
        return $block;
      }
      public function filter_rest_query_manual_posts( $args, $request ) {
				
				if ( isset( $request['manualPostIds'] ) && is_array( $request['manualPostIds'] ) ) {
					$manual_ids = array_map( 'absint', $request['manualPostIds'] );
					error_log('✅ manualPostIds from REST request: ' . implode(',', $manual_ids));

					$args['post__in'] = $manual_ids;
					$args['orderby']  = 'post__in';
					$args['nopaging'] = true;
					return $args;
				}
				
				
				$post_id = 0;

	      // Extract post_id from the referer URL if available (editor REST previews don't pass it directly)
	      if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
	        parse_str( parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_QUERY ), $params );
	        if ( isset($params['post']) ) {
	          $post_id = absint($params['post']);
	        }
	      }

	      if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
	        error_log('⛔ Invalid or unauthorized post');
	        return $args;
	      }

	      $post = get_post( $post_id );
	      if ( ! $post ) {
	        error_log('⛔ Post not found');
	        return $args;
	      }

	      $blocks = parse_blocks( $post->post_content );
	      error_log('🧱 Parsed block count: ' . count($blocks));

	      foreach ( $blocks as $block ) {
	        if (
	          $block['blockName'] === 'core/query' &&
	          ! empty($block['attrs']['manualPostIds'])
	        ) {
	          $manual_ids = $block['attrs']['manualPostIds'];
	          $ids = implode(',', $manual_ids);
	          error_log('✅ Found manualPostIds: ' . $ids);

	          $args['post__in'] = $manual_ids;
	          $args['orderby']  = 'post__in';
	          $args['nopaging'] = true;

	          break;
	        }
	      }

	      return $args;
      }
      public function filter_script_loader_tag( $tag, $handle, $src ) {
        if ( $handle == 'fontawesome' && function_exists('get_field') ) {
          if ( $override = get_field('fontawesome_kit', 'options') ) $tag = str_replace($src, $override, $tag);
        }
        
        return $tag;
      }
      public function filter_wp_theme_json_data_theme( $theme ) {
        $data         = $theme->get_data();
        $radius       = function_exists('get_field') ? get_field('border_radius','options') : 0;
        $typekitFonts = [];
        
        if ( function_exists('get_field') && $typekit = get_field('typekit','options') ){
          $response = wp_remote_get($typekit);
          
          if ( is_array( $response ) && ! is_wp_error($response) ) {
            $body  = $response['body'];
            $fonts = array();
            
            preg_match_all('/font-family:"([a-zA-Z0-9\-]*)"/', $body, $familes);
            preg_match_all('/font-style:([a-zA-Z0-9\-]*);/', $body, $styles);
            preg_match_all('/font-weight:([a-zA-Z0-9\-]*);/', $body, $weights);
            preg_match_all('/src:url\("([^"]+)"\)/', $body, $urls);
            
            foreach ($familes[1] as $key => $family) {  
              $style  = isset($styles[1][$key]) ? $styles[1][$key] : 'normal';
              $title  = ucwords(str_replace('-', ' ', $family));
              $weight = isset($weights[1][$key]) ? $weights[1][$key] : '400';
              $url    = $urls[1][$key];
              
              if ( !isset($fonts[$title]) ) {
                // if family not already set, create a new array for it and add the first fontface
                $fonts[$title] = array(
                  'fontFamily' => "$family",
                  'name' => $title,
                  'slug' => $family,
                  'fontFace' => array(
                    array(
                      'fontFamily' => $title,
                      'fontWeight' => $weight,
                      'fontStyle' => $style,
                      'fontStretch' => "normal",
                      'src' => [ $url ]
                    )
                  )
                );
              }
              else {
                $fonts[$title]['fontFace'][] = array(
                  'fontFamily'  => $title,
                  'fontWeight'  => $weight,
                  'fontStyle'   => $style,
                  'fontStretch' => "normal",
                  'src'         => [ $url ]
                );
              }
            }
            
            sort($fonts);
            
            $typekitFonts = $fonts;
          }
        }
        
        $font_family = wp_get_global_styles(array('elements', 'button', 'typography', 'fontFamily'));
        // $heading_font  = get_field('headings', 'options');
        // $body_font     = get_field('body', 'options');
        
        /* Add Duotone and Gradients */
        
        // pulls from theme.json fns::error($data['settings']['color']['gradients']['theme']);
        $update = array(
          'version'  => 3,
          'settings' => array(
            'color' => array(
              'gradients' => array_merge($data['settings']['color']['gradients']['theme'], array(
                array(
                  'slug'     => 'primary-to-secondary',
                  'gradient' => 'linear-gradient(160deg, '.$this->palette['primary']['color'].', '.$this->palette['secondary']['color'].')',
                  'name'     => 'Primary to Secondary'
                ),
                array(
                  'slug'     => 'primary-to-tertiary',
                  'gradient' => 'linear-gradient(160deg, '.$this->palette['primary']['color'].', '.$this->palette['tertiary']['color'].')',
                  'name'     => 'Primary to Tertiary'
                ),
                array(
                  'slug'     => 'secondary-to-tertiary',
                  'gradient' => 'linear-gradient(160deg, '.$this->palette['secondary']['color'].', '.$this->palette['tertiary']['color'].')',
                  'name'     => 'Secondary to Tertiary'
                ),
                array(
                  'slug'     => 'primary-to-transparent',
                  'gradient' => 'linear-gradient(160deg, '.$this->palette['primary']['color'].', '.$this->palette['primary']['color'].'00)',
                  'name'     => 'Primary to Transparent'
                ),
                array(
                  'slug'     => 'secondary-to-transparent',
                  'gradient' => 'linear-gradient(160deg, '.$this->palette['secondary']['color'].', '.$this->palette['secondary']['color'].'00)',
                  'name'     => 'Secondary to Transparent'
                ),
                array(
                  'slug'     => 'tertiary-to-transparent',
                  'gradient' => 'linear-gradient(160deg, '.$this->palette['tertiary']['color'].', '.$this->palette['tertiary']['color'].'00)',
                  'name'     => 'Tertiary to Transparent'
                ),
                array(
                  'slug'     => 'base-to-contrast',
                  'gradient' => 'linear-gradient(160deg, '.$this->palette['base']['color'].', '.$this->palette['contrast']['color'].')',
                  'name'     => 'Base to Contrast'
                ),
                array(
                  'slug'     => 'base-to-transparent',
                  'gradient' => 'linear-gradient(160deg, '.$this->palette['base']['color'].', '.$this->palette['base']['color'].'00)',
                  'name'     => 'Base to Transparent'
                ),
                array(
                  'slug'     => 'contrast-to-transparent',
                  'gradient' => 'linear-gradient(160deg, '.$this->palette['contrast']['color'].', '.$this->palette['contrast']['color'].'00)',
                  'name'     => 'Contrast to Transparent'
                ),
              )),
            ),
            'custom' => array(
              'borderRadius' => $radius.'px',
              'buttonFontFamily' => $font_family
            ),
            'typography' => array(
              'fontFamilies' => array()
            ),
          ),
        );
        
        $update['settings']['typography']['fontFamilies'] = $typekitFonts;
        
        $updates = $theme->update_with($update);
        // runs twice - but merge works fns::error($theme);
        
        $datanew = $theme->get_data();
        // runs twice - but merge works fns::error($datanew);
        
        return $updates;
      }
      
    /* Helper Functions */
      public function fontawesomeIconTag( $val, $is_preview, $class = '' ) {
        if ( strpos($val, '<') === 0 ) $iconTag = $val;
        elseif ( strpos($val, 'fa-') === 0 ) {
          $iconTag = "<i class='$val $class fa-fw'></i>";
          
          if ( $is_preview ) $iconTag = sprintf('<x%s>%s</x%1$s>', preg_replace('/\s+/', '', $val), $iconTag);
        }
        else {
          $words   = explode(" ", $val);
          $classes = "$class fa-regular";

          foreach($words as $word) $classes.=" fa-$word";
          
          $iconTag = "<i class='$classes  fa-fw'></i>";

          if ( $is_preview ) $iconTag = sprintf('<x%s>%s</x%1$s>', preg_replace('/\s+/', '', $val), $iconTag);
        }
        
        return $iconTag;
      }
      public function toCSSVar( $val ) {
        if ( isset($val) && str_contains($val, 'var:') ) {
          $var = str_replace('var:', '--wp--', $val);
          $var = "var(".str_replace('|', '--', $var).")";
        }
        elseif ( isset($val) ) $var = $val;
        else $var = "0";
        
        return $var;
      }
      
    // Private
      private function google_tag_manager() {
        if ( !$this->gtm && function_exists('get_field') ) {
          if ( $gtms = get_field('google_tag_manager', 'options') ) {
            global $post;
            
            $gtm       = get_field('gtm');
            $gtm_keys  = array_column($gtms, 'title');
            $gtm_index = $gtm ? array_search($gtm, $gtm_keys) : 0;
            
            $this->gtm = $gtms[$gtm_index];
            
            if ( $this->gtm ) {
              printf("
                <!-- Google Tag Manager: %s-->
                <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-%s');</script>
                <!-- End Google Tag Manager -->",
                $this->gtm['title'],
                $this->gtm['tag']
              );
            }
          }
        }
      }
      private function gutenberg() {
        // FIXME: Consider moving easy ones into config.json
        // This is currently ONLY for registering block styles to Gutenberg.
        register_block_style('core/cover', array('name' => 'parallax', 'label' => 'Parallax', 'style_handle' => 'parallax'));
        register_block_style('core/heading', array('name' => 'tighten', 'label' => 'Tighten', 'style_handle' => 'tighten'));
        register_block_style('core/heading', array('name' => 'gradient-mask', 'label' => 'Gradient Mask', 'style_handle' => 'gradient-mask'));
        
        register_block_style('core/image', array('name' => 'br-arched', 'label' => 'Arched', 'style_handle' => 'br-arched'));
        register_block_style('core/image', array('name' => 'br-leaf', 'label' => 'Leaf', 'style_handle' => 'br-leaf'));
        
        register_block_style('core/group', array('name' => 'two-wide', 'label' => '2 Wide (row)', 'style_handle' => 'two-wide'));
        register_block_style('core/group', array('name' => 'three-wide', 'label' => '3 Wide (row)', 'style_handle' => 'three-wide'));
        register_block_style('core/group', array('name' => 'four-wide', 'label' => '4 Wide (row)', 'style_handle' => 'four-wide'));
        
        register_block_style('core/media-text', array('name' => 'wide-text-margin', 'label' => 'Wide Text Margin', 'style_handle' => 'wide-text-margin'));
        register_block_style('core/media-text', array('name' => 'normal-text-margin', 'label' => 'Normal Text Margin', 'style_handle' => 'normal-text-margin'));
        
        // Post Template block styles
        register_block_style('core/post-template', array('name' => 'carousel', 'label' => 'Carousel', 'style_handle' => 'carousel'));
                
        // Add custom button styles - not finished. need to show preview
        if ( function_exists('have_rows') ) {
          if ( have_rows('button_styles','options') ) {
            while( have_rows('button_styles','options') ) {
              the_row();

              $name   = get_sub_field('style_name');
              $handle = 'btn-'.sanitize_title($name);

              register_block_style('pluspro/pro-button', array(
                'name' => $handle, 
                'label' => $name,
                'inline_style' => sprintf('
                  .is-style-%1$s .pro-button{background-color:%2$s !important;border-color:%3$s !important;color:%4$s !important;%5$s}
                  .is-style-%1$s .pro-button:hover{background-color:%6$s !important;border-color:%7$s !important;color:%8$s !important;%9$s}',
                  $handle, 
                  get_sub_field('background'), 
                  get_sub_field('border'), 
                  get_sub_field('text'),
                  get_sub_field('btn_css'), 
                  get_sub_field('hover_background'), 
                  get_sub_field('hover_border'), 
                  get_sub_field('hover_text'),
                  get_sub_field('hover_btn_css')
                ),
              ));

              register_block_style('core/navigation-link', array(
                'name'         => $handle, 
                'label'        => $name,
                'inline_style' => sprintf('
                  .is-style-%1$s .wp-block-navigation-item__content{background-color:%2$s !important;border-color:%3$s !important;color:%4$s !important;%5$s}
                  .is-style-%1$s .wp-block-navigation-item__content:hover{background-color:%6$s !important;border-color:%7$s !important;color:%8$s !important;%9$s}', 
                  $handle, 
                  get_sub_field('background'), 
                  get_sub_field('border'), 
                  get_sub_field('text'), 
                  get_sub_field('btn_css'), 
                  get_sub_field('hover_background'), 
                  get_sub_field('hover_border'), 
                  get_sub_field('hover_text'),
                  get_sub_field('hover_btn_css')
                ),
              ));
              
              register_block_style('pluspro/pro-navbar-drawer-trigger', array(
                'name'         => $handle, 
                'label'        => $name,
                'inline_style' => sprintf('
                  .is-style-%1$s .pro-navbar-drawer-trigger{background-color:%2$s !important;border-color:%3$s !important;color:%4$s !important;%5$s}
                  .is-style-%1$s .pro-navbar-drawer-trigger:hover{background-color:%6$s !important;border-color:%7$s !important;color:%8$s !important;%9$s}', 
                  $handle, 
                  get_sub_field('background'), 
                  get_sub_field('border'), 
                  get_sub_field('text'), 
                  get_sub_field('btn_css'), 
                  get_sub_field('hover_background'), 
                  get_sub_field('hover_border'), 
                  get_sub_field('hover_text'),
                  get_sub_field('hover_btn_css')
                ),
              ));
            }
          }
        }
        
        // Add custom navbar styles - not finished. need to show preview
        if ( function_exists('have_rows') ) {
          if ( have_rows('navbar_styles','options') ) {
            while( have_rows('navbar_styles','options') ) {
              the_row();
              
              $name   = get_sub_field('style_name');
              $handle = 'nav-'.sanitize_title($name);

              register_block_style('core/navigation', array(
                'name'         => $handle,
                'label'        => $name,
                'inline_style' => sprintf('
                  .is-style-%1$s {color:%2$s !important;}
                  .is-style-%1$s .wp-block-navigation-item__content{color:%2$s !important;%4$s}
                  .is-style-%1$s .wp-block-navigation-item__content:hover{color:%3$s !important;%5$s}
                  .scrolled .is-style-%1$s .wp-block-navigation-item__content{color:%6$s !important;%8$s}
                  .scrolled .is-style-%1$s .wp-block-navigation-item__content:hover{color:%7$s !important;%9$s}',
                  $handle,
                  get_sub_field('color'),
                  get_sub_field('hover_color'),
                  get_sub_field('item_css'),
                  get_sub_field('item_hover_css'),
                  get_sub_field('color_scrolled'),
                  get_sub_field('hover_color_scrolled'),
                  get_sub_field('item_css_scrolled'),
                  get_sub_field('item_hover_css_scrolled')
                ),
              ));
              register_block_style('pluspro/pro-social-links', array(
                'name'         => $handle,
                'label'        => $name,
                'inline_style' => sprintf('
                  .is-style-%1$s .pro-social-link{color:%2$s !important;%4$s}
                  .is-style-%1$s .pro-social-link:hover{color:%3$s !important;%5$s}
                  .scrolled .is-style-%1$s .pro-social-link{color:%6$s !important;%8$s}
                  .scrolled .is-style-%1$s .pro-social-link:hover{color:%7$s !important;%9$s}',
                  $handle,
                  get_sub_field('color'),
                  get_sub_field('hover_color'),
                  get_sub_field('item_css'),
                  get_sub_field('item_hover_css'),
                  get_sub_field('color_scrolled'),
                  get_sub_field('hover_color_scrolled'),
                  get_sub_field('item_css_scrolled'),
                  get_sub_field('item_hover_css_scrolled')
                ),
              ));
            }
          }
        }
      }
  }
  
  new plus_theme();
}