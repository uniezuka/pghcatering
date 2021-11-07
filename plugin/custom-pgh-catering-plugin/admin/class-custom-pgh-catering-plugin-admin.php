<?php

class Custom_Pgh_Catering_Plugin_Admin {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/custom-pgh-catering-plugin-admin.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/custom-pgh-catering-plugin-admin.js', array( 'jquery' ), $this->version, false );
	}

    public function add_page_templates($page_templates, $wp_theme, $post) {
        $page_templates['template-weekly-menu.php'] = __('PGH Fresh Weekly Menu', CUSTOM_PGH_CATERING_DOMAIN_NAME );

        return $page_templates;
    }

    public function load_template($template) {
        $valid_template_slugs = [
            'template-weekly-menu.php'
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
}