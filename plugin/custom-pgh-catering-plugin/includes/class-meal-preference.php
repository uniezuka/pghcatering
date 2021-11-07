<?php
    if ( ! class_exists( 'MP_TAX_META' ) ) {
        class Meal_Preference {
            private $customer_avoids;
            private $customer_prefers;
            private $customer_requests;

            public function __construct() {
            }

            public function init() {
                add_action( 'init', array ($this, 'register_meal_preference_taxonomy'), 0 );
                add_action( 'wp_loaded', array( $this, 'set_user_meal_preferences'), 0 );
                add_filter( 'manage_edit-meal_preferences_columns',  array ( $this, 'image_columns') );
                add_filter( 'manage_meal_preferences_custom_column', array( $this, 'meal_preferences_column' ), 10, 3 );
                add_action( 'meal_preferences_add_form_fields', array ( $this, 'add_meal_preference_image' ), 10, 2 );
                add_action( 'created_meal_preferences', array ( $this, 'save_meal_preference_image' ), 10, 2 );
                add_action( 'meal_preferences_edit_form_fields', array ( $this, 'update_meal_preference_image' ), 10, 2 );
                add_action( 'edited_meal_preferences', array ( $this, 'update_meal_preference_image' ), 10, 2 );
                add_action( 'admin_enqueue_scripts', array( $this, 'load_media' ) );
                add_action( 'admin_footer', array ( $this, 'add_meal_preference_script' ) );
          
                add_action( 'woocommerce_product_thumbnails', array( $this, 'show_meal_preferences' ) );
                add_action( 'woocommerce_product_meta_end', array( $this, 'show_meal_preferences' ) );
                add_action( 'woocommerce_before_shop_loop_item_title',  array( $this, 'shop_page_meal_preferences' ), 11 );
                add_action( 'woocommerce_before_shop_loop_item_title',  array( $this, 'meal_preference_product_flag' ), 15 );
                add_action( 'woocommerce_product_thumbnails',  array( $this, 'meal_preference_product_flag' ), 15 );

                add_action( 'init', array( $this, 'add_meal_preferences_endpoint') );
                add_filter( 'query_vars', array( $this, 'meal_preferences_query_vars'), 0 );
                add_filter( 'woocommerce_account_menu_items', array( $this, 'meal_preferences_link_my_account') );
                add_action( 'woocommerce_account_meal-preferences_endpoint', array( $this, 'meal_preferences_end_point_content') );
            }

            public function set_user_meal_preferences(){
                if ( is_admin() ) return; 

                $this->customer_avoids = $this->get_user_meal_preferences('avoids');
                $this->customer_prefers = $this->get_user_meal_preferences('prefers');
                $this->customer_requests = $this->get_user_meal_preferences('requests');
            }

            public function get_user_meal_preferences($preference){
                if ( is_admin() ) return;
          
                $field = 'customer_' . $preference;
          
                $user = 'user_' . get_current_user_id();
                $mps = get_field( $field, $user );
          
                if ( $preference === 'avoids' || $preference === 'prefers' ) {
                    $mps_array = array();

                    if ( ! empty( $mps ) ) {
                        foreach ($mps as $key => $mp) {
                            $term = get_term($mp,'meal_preferences');
                            
                            if ( gettype($term) === 'object' ) {
                                if ( $term->parent !== 0 ) {
                                    $mps_array[] = $term->term_id;
                                }
                            }
                        }
                    }
                    
                    return $mps_array;
                } else if ( $preference === 'requests' ) {
                  return $mps;
                } else {
                  return '';
                }
            }

            function meal_preferences_end_point_content() {
                echo '<h2>Please set your meal preferences</h2>';
                echo '<p>We do our best to cater each meal to your preferences. Please set your meal preferences below!</p>';
                echo '<p><strong>REMEMBER: </strong> Pittsburgh Fresh is NOT a certified gluten-free, nut-free or dairy free kitchen. Cross-contamination is possible, though our chefs are trained to prepare each meal according to your individual dietary needs. However, if you have severe allergies, there is nowhere safer to cook than in your own kitchen.</p>';
                //gravity_form( 2, true, true, false, null, true, '789', true );
            }
  
            function add_meal_preferences_endpoint() {
                add_rewrite_endpoint( 'meal-preferences', EP_ROOT | EP_PAGES );
            }

            function meal_preferences_query_vars( $vars ) {
                $vars[] = 'meal-preferences';
                return $vars;
            }

            function meal_preferences_link_my_account( $items ) {
                $items['meal-preferences'] = 'My Meal Preferences';
                return $items;
            }

            function meal_preference_product_flag() {
                if ( is_admin() ) return;

                global $product;
                $customer_avoids = $this->customer_avoids;
                $product_preference_flags = array();
                $contains_food_you_avoid = false;
                $the_food_you_avoid = '';
                $product_id = $product->get_id();
        
                $terms = get_the_terms( $product_id, 'meal_preferences' );
                if ( $terms && ! is_wp_error( $terms ) ) {
                    foreach ( $terms as $term ) {
                        if ( $term->parent !== 0 ) {
                            $product_preference_flags[] = $term->term_id;
                        }
                    }
                }
        
                foreach ($product_preference_flags as $product_preference_flag) {
                    if ( in_array($product_preference_flag, $customer_avoids) ) {
                        $contains_food_you_avoid = true;
                        $this_food_youre_avoiding = get_term($product_preference_flag,'meal_preferences');
                        $the_food_you_avoid .= $this_food_youre_avoiding->name . ', ';
                    }
                }
        
                $the_food_you_avoid = rtrim($the_food_you_avoid,', ');
        
                // TODO: call this conditionally
                if ( $contains_food_you_avoid ) {
                    printf( '<div class="mp-conflict-flag"><span class="mp-conflict-message" title="%s">%s</span></div>', $the_food_you_avoid, __('Contains food you avoid','pgh-fresh') );
                } else {
                    $terms = get_the_terms( $product_id, 'meal_flags' );
                    if ( $terms && ! is_wp_error( $terms ) ) {
                        $meal_flag = $terms[0]->name;
                        printf( '<div class="meal-flag"><span class="meal-flag-message">%s</span></div>', $meal_flag );
                    }
                }
            }

            function shop_page_meal_preferences(){
                global $product;
                $at_least_one = false;
                $terms = get_the_terms( $product->get_id(), 'meal_preferences' );
                
                if ( $terms && ! is_wp_error( $terms ) ) {
                    echo '<div class="pgh-cat-list-wrapper">';
                    foreach ( $terms as $term ) {
                        $term_id = 'meal_preferences_' . $term->term_id;
                        $display = get_field('display_on_frontend',$term_id);
                        
                        if ( $display ) {
                            $at_least_one = true;
                            $thumbnail_id = get_term_meta( $term->term_id, 'meal-preference-image-id', true );

                            if ( $thumbnail_id ) {
                                $image = wp_get_attachment_thumb_url( $thumbnail_id );
                            } else {
                                $image = wc_placeholder_img_src();
                            }
                
                            echo '<a href="'.get_term_link( $term->term_id) . '" title="'.$term->name.'" >'.'<img class="mp-images"  src="' . esc_url( $image ) . '" alt="' .$term->name .'"  height="50px" width=" 50px" />'.'</a>';
                        }
                    }

                    if ( $at_least_one === false ) {
                        // if it has terms but none are display_on_frontend, show placeholder
                        echo '<a href="'.$product->get_permalink().'" title="'.$product->get_title().'" >'.'<img class="mp-images"  src="' . esc_url( wc_placeholder_img_src() ) . '" alt="PGH Fresh"  height="50px" width=" 50px" />'.'</a>';
                    }

                    echo '</div>';
                } else {
                    echo '<div class="pgh-cat-list-wrapper">';
                    echo '<a href="'.$product->get_permalink().'" title="'.$product->get_title().'" >'.'<img class="mp-images"  src="' . esc_url( wc_placeholder_img_src() ) . '" alt="PGH Fresh"  height="50px" width=" 50px" />'.'</a>';
                    echo '</div>';
                }
            }

            function show_meal_preferences(){
                global $product, $_wp_additional_image_sizes;
                $terms =  get_the_terms( $product->get_id(), 'meal_preferences' );

                if ( $terms && ! is_wp_error( $terms ) ) {
                    foreach ( $terms as $term ) {
                        $term_id = 'meal_preferences_' . $term->term_id;
                        $display = get_field('display_on_frontend',$term_id);

                        if ( $display ) {
                            $thumbnail_id = get_term_meta( $term->term_id, 'meal-preference-image-id', true );
                            
                            if ( $thumbnail_id ) {
                                $image = wp_get_attachment_thumb_url( $thumbnail_id );
                            } else {
                                $image = wc_placeholder_img_src();
                            }

                            echo '<a href="'.get_term_link( $term->term_id) . '" class="mp-image-link" title="'.$term->name.'" >'.
                            '<img class ="mp-images"  src="' . esc_url( $image ) . '" alt="' .$term->name .
                            '"  height="'.$_wp_additional_image_sizes['shop_thumbnail']['height'].'" width="'. $_wp_additional_image_sizes['shop_thumbnail']['width'].'" />'.'</a>';
                        }
                    }
                }
            }

            public function add_meal_preference_script() {
                include plugin_dir_path( dirname( __FILE__ ) ) . 'includes/partials/add_meal_preference_script_view.php';
            }

            public function load_media() {
                wp_enqueue_media();
            }

            public function update_meal_preference_image ( $term, $taxonomy ) {
                include plugin_dir_path( dirname( __FILE__ ) ) . 'includes/partials/update_meal_preference_image_view.php';
            }

            public function save_meal_preference_image ( $term_id, $tt_id ) {
                if( isset( $_POST['meal-preference-image-id'] ) && '' !== $_POST['meal-preference-image-id'] ){
                    $image = $_POST['meal-preference-image-id'];
                    add_term_meta( $term_id, 'meal-preference-image-id', $image, true );
                }
            }

            public function add_meal_preference_image( $taxonomy ) { 
                include plugin_dir_path( dirname( __FILE__ ) ) . 'includes/partials/add_meal_preference_image_view.php';
            }

            function image_columns($columns) {
                $new_columns = array();
          
                if ( isset( $columns['cb'] ) ) {
                  $new_columns['cb'] = $columns['cb'];
                  unset( $columns['cb'] );
                }
          
                $new_columns['thumb'] = __( 'Image', CUSTOM_PGH_CATERING_DOMAIN_NAME );
          
                $columns = array_merge( $new_columns, $columns );
                $columns['handle'] = '';
          
                return $columns;
            }

            function meal_preferences_column( $columns, $column, $id ) {
                if ( 'thumb' === $column ) {
                    $thumbnail_id = get_term_meta ( $id, 'meal-preference-image-id', true );
            
                    if ( $thumbnail_id ) {
                        $image = wp_get_attachment_thumb_url( $thumbnail_id );
                    } else {
                        $image = wc_placeholder_img_src();
                    }
                
                    $columns .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr__( 'Thumbnail', 'pgh-fresh' ) . '" class="wp-post-image" height="48" width="48" />';
                }
        
                if ( 'handle' === $column ) {
                    $columns .= '<input type="hidden" name="term_id" value="' . esc_attr( $id ) . '" />';
                }

                return $columns;
            }

            function register_meal_preference_taxonomy() {
                $labels = array(
                    'name'                       => _x( 'Meal Preferences', 'Taxonomy General Name', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'singular_name'              => _x( 'Meal Preference', 'Taxonomy Singular Name', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'menu_name'                  => __( 'Meal Preferences', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'all_items'                  => __( 'All Meal preferences', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'parent_item'                => __( 'Parent Meal preference', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'parent_item_colon'          => __( 'Parent Meal preference:', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'new_item_name'              => __( 'New Meal preference Name', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'add_new_item'               => __( 'Add New Meal Preference', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'edit_item'                  => __( 'Edit Meal preference', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'update_item'                => __( 'Update Meal Preference', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'view_item'                  => __( 'View Meal Preference', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'separate_items_with_commas' => __( 'Separate items with commas', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'add_or_remove_items'        => __( 'Add or remove Meal preferences', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'choose_from_most_used'      => __( 'Choose from the most used', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'popular_items'              => __( 'Popular Items', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'search_items'               => __( 'Search Meal Preferences', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'not_found'                  => __( 'Not Found', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'no_terms'                   => __( 'No Meal Preference', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'items_list'                 => __( 'Meal Preferences list', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'items_list_navigation'      => __( 'Meal Preferences list navigation', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                );
        
                $args = array(
                    'labels'                     => $labels,
                    'hierarchical'               => true,
                    'public'                     => false, // changed from true to false to prevent search index
                    'show_ui'                    => true,
                    'show_admin_column'          => true,
                    'show_in_nav_menus'          => false,
                    'show_tagcloud'              => false,
                );
        
                register_taxonomy( 'meal_preferences', array( 'product' ), $args );
            }
        }
    }

    $meal_preference = new Meal_Preference();
    $meal_preference->init();
?>