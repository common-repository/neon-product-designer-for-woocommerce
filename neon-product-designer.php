<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://neon-configurator.vertimcoders.com/
 * @since             1.0.0
 * @package           Neon_Product_Designer
 *
 * @wordpress-plugin
 * Plugin Name:       Neon Product Designer for Woocommerce
 * Plugin URI:        https://neon-configurator.vertimcoders.com/
 * Description:       The ultimate woocommerce neon product designer plugin for your signs.
 * Version:           2.1.1
 * Author:            Vertim Coders
 * Author URI:        https://www.vertimcoders.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       neon-product-designer
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'NPD_VERSION', '2.1.1' );
define( 'NPD_URL', plugins_url( '/', __FILE__ ) );
define( 'NPD_DIR', dirname( __FILE__ ) );
define( 'NPD_MAIN_FILE', 'neon-product-designer/neon-product-designer.php' );
define( 'NPD_PLUGIN_NAME', 'Neon Product Designer' );

$upload_dir = wp_upload_dir();
$generation_path = $upload_dir['basedir'] . "/NPD/";
$generation_url = $upload_dir['baseurl'] . "/NPD/";

define('NPD_IMAGE_PATH', $generation_path . "image");
define('NPD_IMAGE_URL', $generation_url . "image");

define('NPD_ORDER_PATH', $generation_path . "ORDER");
define('NPD_ORDER_URL', $generation_url . "ORDER");

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-neon-product-designer-activator.php
 */
function activate_neon_product_designer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-neon-product-designer-activator.php';
	Neon_Product_Designer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-neon-product-designer-deactivator.php
 */
function deactivate_neon_product_designer() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-neon-product-designer-deactivator.php';
	Neon_Product_Designer_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_neon_product_designer' );
register_deactivation_hook( __FILE__, 'deactivate_neon_product_designer' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-neon-product-designer.php';

if ( ! class_exists( 'Kali_Admin_Tools' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/kali-admin-tools/kali-admin-tools.php';
}


require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';

require_once plugin_dir_path( __FILE__ ) . 'includes/class-npd-design.php';

require_once NPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'skins' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . 'class-npd-skin-default.php';
require_once NPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-npd-editor.php';
require_once NPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-npd-scenes.php';
require_once NPD_DIR . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'class-npd-color.php';
require_once NPD_DIR . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'class-npd-config.php';
require_once NPD_DIR . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'class-npd-product.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_neon_product_designer() {

	$plugin = new Neon_Product_Designer();
	$plugin->run();

}
run_neon_product_designer();
