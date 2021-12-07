<?php

class Custom_Pgh_Catering_Plugin_Admin {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}
 
    public function clear_wc_users_session($id, $post_obj){
        
        if ( get_post_type( $id ) == 'weekly_menus' ) {
            global $wpdb;

            $wpdb->query( "TRUNCATE {$wpdb->prefix}woocommerce_sessions" );
            $result = absint( $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key='_woocommerce_persistent_cart_" . get_current_blog_id() . "';" ) ); // WPCS: unprepared SQL ok.
            wp_cache_flush();
        }
    }

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/custom-pgh-catering-plugin-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/custom-pgh-catering-plugin-admin.js', array( 'jquery' ), $this->version, false );
	}

    public function add_page_templates($page_templates, $wp_theme, $post) {
        $page_templates['template-weekly-menu.php'] = __('PGH Fresh Weekly Menu', CUSTOM_PGH_CATERING_DOMAIN_NAME );
        $page_templates['template-kitchen-report.php'] = __('Kitchen Reports', CUSTOM_PGH_CATERING_DOMAIN_NAME );
        $page_templates['template-packing-report.php'] = __('Packing Reports', CUSTOM_PGH_CATERING_DOMAIN_NAME );

        return $page_templates;
    }

    public function load_template($template) {
        $valid_template_slugs = [
            'template-weekly-menu.php',
            'template-kitchen-report.php',
            'template-packing-report.php'
        ];
        
        $page_template_slug = get_page_template_slug();
        $is_valid_template_slug = in_array($page_template_slug, $valid_template_slugs); 

        if (!$is_valid_template_slug) return $template;

        $theme_file = locate_template(array($page_template_slug));

        $template = ($theme_file) ? $theme_file : CUSTOM_PGH_CATERING_TEMPLATE_DIR . '/' . $page_template_slug;

        return $template;
    }

    public function register_project_templates( $atts ) {

		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		$templates = wp_get_theme()->get_page_templates();
        
		if ( empty( $templates ) ) {
			$templates = array();
		} 

		wp_cache_delete( $cache_key , 'themes');

		$templates = array_merge( $templates, array() );

		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;
	} 

    public function register_bulk_actions( $actions ) {
        $actions['view_kitchen_report'] = __('View Kitchen Report', $this->plugin_name);
        $actions['view_packing_report'] = __('View Packing Report', $this->plugin_name);
        
        return $actions;
    }

    private function is_valid_do_action($doaction) {
        switch ($doaction) {
            case 'view_kitchen_report':
            case 'view_packing_report':
                return true;

            default:
                return false;
        }
    }

    public function bulk_action_handler( $sendback, $doaction, $items ) {
        if ( !$this->is_valid_do_action( $doaction ) ) return $sendback;

        $options = get_option( 'custom_pgh_catering_plugin_options' );

        switch ($doaction) {
            case 'view_kitchen_report':
                if ( isset( $options['kitchen_report_page'] ) && ! empty( $options['kitchen_report_page'] ) ) {
                    $sendback = home_url( $options['kitchen_report_page'] );
                    $sendback = add_query_arg('order_ids', implode('-', $items), $sendback );
                }
                
                break;

            case 'view_packing_report':
                if ( isset( $options['packing_report_page'] ) && ! empty( $options['packing_report_page'] ) ) {
                    $sendback = home_url( $options['packing_report_page'] );
                    $sendback = add_query_arg('order_ids', implode('-', $items), $sendback );
                }
                break;

            default:
                break;
        }
  
        wp_safe_redirect($sendback);
        
        exit;
    }

    public function add_plugin_settings_page() {
        add_options_page( 'PGH Catering Settings', 'PGH Catering', 'manage_options', 'custom_pgh_catering_plugin', array($this, 'render_plugin_settings_page' ) );
    }

    public function render_plugin_settings_page () {
        include_once( 'partials/plugin_settings_page_display.php' );
    }

    public function register_plugin_settings() {

        register_setting( 
            'custom_pgh_catering_plugin_options', 
            'custom_pgh_catering_plugin_options'
        );

        add_settings_section(
            'custom_pgh_catering_plugin_pages_section',
            'Pages',
            array ( $this, 'custom_pgh_catering_plugin_pages_section_callback' ),
            'custom_pgh_catering_plugin'
        );

        add_settings_field( 'custom_pgh_catering_kitchen_report_page',
            'Kitchen Report Page',
            array ( $this, 'kitchen_report_page_callback' ),
            'custom_pgh_catering_plugin',
            'custom_pgh_catering_plugin_pages_section',
            array( 'label_for' => 'custom_pgh_catering_kitchen_report_page' ) 
        );

        add_settings_field( 'custom_pgh_catering_packing_report_page',
            'Packing Report Page',
            array ( $this, 'packing_report_page_callback' ),
            'custom_pgh_catering_plugin',
            'custom_pgh_catering_plugin_pages_section',
            array( 'label_for' => 'custom_pgh_catering_packing_report_page' ) 
        );
    }

    public function custom_pgh_catering_plugin_pages_section_callback() {
        echo '<p>Set or Map PGH Catering pages</p>';
    }

    public function kitchen_report_page_callback() {
        $options = get_option( 'custom_pgh_catering_plugin_options' );
        echo "<input id='custom_pgh_catering_kitchen_report_page' name='custom_pgh_catering_plugin_options[kitchen_report_page]' type='text' value='" . esc_attr( $options['kitchen_report_page'] ) . "' />";
    }

    public function packing_report_page_callback() {
        $options = get_option( 'custom_pgh_catering_plugin_options' );
        echo "<input id='custom_pgh_catering_packing_report_page' name='custom_pgh_catering_plugin_options[packing_report_page]' type='text' value='" . esc_attr( $options['packing_report_page'] ) . "' />";
    }
}