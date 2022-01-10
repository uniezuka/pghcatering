<?php
/**
 * @link              https://stellarwebdev.com/
 * @since             1.0.0
 * @package           Custom_Pgh_Catering_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Custom PGH Catering Plugin
 * Plugin URI:        https://stellarwebdev.com/
 * Description:       Custom Plugin specifically designed for PGH Fresh ecommerce site.
 * Version:           1.1.3
 * Author:            J Lorenzo
 * Author URI:        https://stellarwebdev.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       custom-pgh-catering-plugin
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CUSTOM_PGH_CATERING_PLUGIN_VERSION', '1.1.3' );

if ( !defined( 'CUSTOM_PGH_CATERING_PLUGIN_DIR') ) {
	define( 'CUSTOM_PGH_CATERING_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( !defined( 'CUSTOM_PGH_CATERING_TEMPLATE_DIR') ) {
	define('CUSTOM_PGH_CATERING_TEMPLATE_DIR', CUSTOM_PGH_CATERING_PLUGIN_DIR . '/templates');
}

if ( !defined( 'CUSTOM_PGH_CATERING_PLUGIN_DEBUG_DIR') ) {
	define( 'CUSTOM_PGH_CATERING_PLUGIN_DEBUG_DIR', CUSTOM_PGH_CATERING_PLUGIN_DIR . '_debug' );
}

if ( !defined( 'CUSTOM_PGH_CATERING_PLUGIN_LOG_FILE') ) {
	define( 'CUSTOM_PGH_CATERING_PLUGIN_LOG_FILE', CUSTOM_PGH_CATERING_PLUGIN_DEBUG_DIR . '/log.txt' );
}

if ( !defined( 'CUSTOM_PGH_CATERING_DOMAIN_NAME') ) {
	define( 'CUSTOM_PGH_CATERING_DOMAIN_NAME', 'custom-pgh-catering-plugin' );
}

if ( !defined( 'CUSTOM_PGH_CATERING_DOMAIN_NAME') ) {
	define( 'CUSTOM_PGH_CATERING_DOMAIN_NAME', 'custom-pgh-catering-plugin' );
}

function activate_custom_pgh_catering_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-pgh-catering-plugin-activator.php';
	Custom_Pgh_Catering_Plugin_Activator::activate();
}

function deactivate_custom_pgh_catering_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-custom-pgh-catering-plugin-deactivator.php';
	Custom_Pgh_Catering_Plugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_custom_pgh_catering_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_custom_pgh_catering_plugin' );

require plugin_dir_path( __FILE__ ) . 'includes/class-custom-pgh-catering-plugin.php';

function run_custom_pgh_catering_plugin() {

	$plugin = new Custom_Pgh_Catering_Plugin();
	$plugin->run();

}

run_custom_pgh_catering_plugin();