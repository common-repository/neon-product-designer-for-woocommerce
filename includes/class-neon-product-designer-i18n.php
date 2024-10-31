<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.vertimcoders.com/
 * @since      1.0.0
 *
 * @package    Neon_Product_Designer
 * @subpackage Neon_Product_Designer/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Neon_Product_Designer
 * @subpackage Neon_Product_Designer/includes
 * @author     Vertim Coders <freelance@vertimcoders.com>
 */
class Neon_Product_Designer_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'neon-product-designer',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
