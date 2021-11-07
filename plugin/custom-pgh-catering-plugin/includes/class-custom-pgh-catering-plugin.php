<?php

class Custom_Pgh_Catering_Plugin {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		if ( defined( 'CUSTOM_PGH_CATERING_PLUGIN_VERSION' ) ) {
			$this->version = CUSTOM_PGH_CATERING_PLUGIN_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'custom-pgh-catering-plugin';

		$this->load_dependencies();
		$this->set_locale();
        $this->define_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-pgh-catering-plugin-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-custom-pgh-catering-plugin-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-custom-pgh-catering-plugin-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-custom-pgh-catering-plugin-public.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-meal-preference.php';

		$this->loader = new Custom_Pgh_Catering_Plugin_Loader();
	}

	private function set_locale() {
		$plugin_i18n = new Custom_Pgh_Catering_Plugin_i18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

    private function define_hooks() {
        $this->loader->add_action( 'init', $this, 'register_post_types' );
        $this->loader->add_action( 'after_setup_theme', $this, 'custom_image_sizes' );
    }

	private function define_admin_hooks() {
		$plugin_admin = new Custom_Pgh_Catering_Plugin_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        
        $this->loader->add_filter( 'theme_page_templates', $plugin_admin, 'add_page_templates', 10, 3 );
        $this->loader->add_filter( 'template_include', $plugin_admin, 'load_template' );
        $this->loader->add_filter( 'wp_insert_post_data', $plugin_admin, 'register_project_templates' );       
	}

	private function define_public_hooks() {
		$plugin_public = new Custom_Pgh_Catering_Plugin_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'wp_print_scripts', $plugin_public, 'print_scripts_instead_of_enqueue' );

        $this->loader->add_action( 'wp_ajax_nopriv_adjust_cart', $plugin_public, 'adjust_cart' );
        $this->loader->add_action( 'wp_ajax_adjust_cart', $plugin_public, 'adjust_cart' );

        $this->loader->add_filter( 'woocommerce_add_cart_item_data', $plugin_public, 'add_menu_day', 10, 3 );
	}

    function custom_image_sizes() {
        //add_image_size( 'subscription_thumbnail', 889, 1333, true );
        add_image_size( 'mp_thumbnail', 30, 30, true );
        add_image_size( 'menu_thumbnail', 500, 500, true );
    }

    function register_post_types() {
        if ( post_type_exists( 'weekly_menus' ) ) {
			return;
		}

        $posttype = register_post_type(
			'weekly_menus',
            array(
                'labels' => array(
                    'name'                  => __( 'Weekly Menus', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'singular_name'         => __( 'Weekly Menu', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'all_items'             => __( 'All Weekly Menus', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'add_new'               => __( 'Add New', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'add_new_item'          => __( 'Add new weekly menu', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'edit'                  => __( 'Edit', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'edit_item'             => __( 'Edit weekly menu', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'new_item'              => __( 'New weekly menu', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'view_item'             => __( 'View weekly menu', CUSTOM_PGH_CATERING_DOMAIN_NAME ),
                    'view_items'            => __( 'View weekly menus', CUSTOM_PGH_CATERING_DOMAIN_NAME )
                ),
                'public'                    => true,
                'rewrite'                   => array('slug' => 'weekly_menus'),
                'show_in_menu'              => true,
                'show_in_nav_menus'         => true,
                'show_in_rest'              => true,
                'show_ui'                   => true,
                'menu_icon'                 => 'dashicons-media-spreadsheet',
                'supports'                  => array( 'title', 'thumbnail' )
            )
        );
    }

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}
}