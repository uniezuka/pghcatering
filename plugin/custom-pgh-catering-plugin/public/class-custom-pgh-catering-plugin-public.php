<?php

class Custom_Pgh_Catering_Plugin_Public {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

    function wc_checkout_create_order_line_item($item, $cart_item_key, $values, $order) {

        if(array_key_exists('pgh_menu_day', $values)) {
            $item->add_meta_data('_pgh_menu_day',$values['pgh_menu_day']);
        }
    }

    public function wc_get_item_data($item_data, $cart_item) {
        if(array_key_exists('pgh_menu_day', $cart_item)) {
            $custom_details = $cart_item['pgh_menu_day'];
    
            $item_data[] = array(
                'key'   => 'Day',
                'value' => $custom_details
            );
        }
    
        return $item_data;
    }

    public function wc_add_cart_item_data($cart_item_data, $product_id, $variation_id) {
        if(isset($_POST['pgh_menu_day'])) {
            $cart_item_data['pgh_menu_day'] = sanitize_text_field($_POST['pgh_menu_day']);
        }

        return $cart_item_data;
    }

    public function wps_store_select_first_option($label) {
        return 'Select a location';
    }

    public function adjust_cart() {
        if( empty($_POST) || !isset($_POST) ) {
            error_log('Error! _POST empty or not set.');
        } 
        else {
            $data = $_POST;
            $take_action = $data['take_action'];
            $product_id = $data['product_id'];

            if ( $take_action === 'add' ) {
                WC_AJAX::add_to_cart();
            }
            else {
                ob_start();

                $ctr = 0;

                foreach ( WC()->cart->get_cart() as $cart_item_key => $values ){
                    if ( intval( $values['data']->get_id() ) === intval($product_id) ) {
                        $quantity = $values['quantity'];
                        $quantity--;

                        if ( $quantity <= 0 ) {
                            if (WC()->cart->remove_cart_item($cart_item_key) === false) {
                                wp_send_json_error();
                            }
                        } 
                        else {
                            WC()->cart->set_quantity( $cart_item_key, $quantity, $refresh_totals = true );
                        }

                        WC()->cart->calculate_totals();
                        WC()->cart->maybe_set_cart_cookies();
                        $ctr++;
                    }
                }

                $data = array(
                    'error'       => false,
                    'affected'    => $ctr,
                    'product_id'  => $product_id
                );
                wp_send_json( $data );
            }
        }
    }

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/custom-pgh-catering-plugin-public.css', array(), $this->version, 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/custom-pgh-catering-plugin-public.js', array( 'jquery' ), $this->version, false );

        $config_custom = array( 
            'ajax_url'   => admin_url( 'admin-ajax.php' ),
            'site_url'   => site_url(),
            'userID'     => get_current_user_id(),
            'wps_security'   => wp_create_nonce( 'wps_security' ),
            'wc_store_api' => wp_create_nonce( 'wc_store_api' ),
            'wc_add_to_cart_url' => site_url('/wp-json/wc/store/cart/add-item')
        );

        wp_localize_script( $this->plugin_name, 'config_custom', $config_custom );
	}

    function print_scripts_instead_of_enqueue(){
        echo '<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-9ralMzdK1QYsk4yBY680hmsb4/hJ98xK3w0TIaJ3ll4POWpWUYaA2bRjGGujGT8w" crossorigin="anonymous">';
  
        if ( is_front_page() ) {
            if (get_field('homepage_hero_image', 'option')) {
                $homepage_image = wp_get_attachment_url( get_field('homepage_hero_image', 'option') );
                //$homepage_image_2 = home_url() . '/wp-content/uploads/pgh-fresh-placeholder-img2-1024x1024.jpg';
                $homepage_image_2 = home_url() . '/wp-content/uploads/Ok-to-use-5-1-e1573654544564.jpg';

                echo '<style>';
                echo '
                    #hero {
                        background-color: #F9F4EE;
                        background-repeat: no-repeat;
                        background-attachment: fixed;
                        text-align: center;
                    }
                    @media only screen and (max-width:767px) {
                        #hero {
                            background-image: url('. $homepage_image_2 . ');
                            background-size: cover;
                            background-position: 50% -100%;
                            height: 100%;
                        }
                    }
                    @media only screen and (min-width:768px) {
                        #hero {
                            background-image: url('. $homepage_image . ');
                            background-size: cover;
                            background-position: 0 0;
                            height: 60vh;
                        }
                    }
                ';
                echo '</style>';
            }
        }
      }
}