<?php

class Custom_Pgh_Catering_Plugin_Shortcodes {
    public function __construct() {
    }

    public function register_shortcodes() { 
        add_shortcode( 'report_buttons', array ($this, 'display_report_buttons' ) );
        add_shortcode( 'kitchen_report_view', array ($this, 'display_kitchen_report_view' ) );
        add_shortcode( 'packing_report_view', array ($this, 'display_packing_report_view' ) );
    }

    public function display_report_buttons( $atts ) {
        $atts = shortcode_atts( array(
            
        ), $atts, 'report_buttons' );

        $options = get_option( 'custom_pgh_catering_plugin_options' );

        $kitchen_report_page = home_url( $options['kitchen_report_page'] );
        $packing_report_page = home_url( $options['packing_report_page'] );

        if ( isset( $_GET['order_ids'] ) && ! empty( $_GET['order_ids'] ) ) {
            $selected = explode('-', $_GET['order_ids']);

            $kitchen_report_page = add_query_arg('order_ids', implode('-', $selected), $kitchen_report_page );
            $packing_report_page = add_query_arg('order_ids', implode('-', $selected), $packing_report_page );
        }
        
        include 'partials/display_report_buttons.php';
    }

    public function display_kitchen_report_view( $atts ) {
        $atts = shortcode_atts( array(
            
        ), $atts, 'kitchen_report_view' );

        include 'partials/display_kitchen_report_view.php';
    }

    public function display_packing_report_view( $atts ) {
        $atts = shortcode_atts( array(
            
        ), $atts, 'packing_report_view' );

        include 'partials/display_packing_report_view.php';
    }
}