<?php

class Custom_Pgh_Catering_Plugin_i18n {

	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'custom-pgh-catering-plugin',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}