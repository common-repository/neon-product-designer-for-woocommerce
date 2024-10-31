<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.vertimcoders.com/
 * @since      1.0.0
 *
 * @package    Neon_Product_Designer
 * @subpackage Neon_Product_Designer/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Neon_Product_Designer
 * @subpackage Neon_Product_Designer/includes
 * @author     Vertim Coders <freelance@vertimcoders.com>
 */
class Neon_Product_Designer {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Neon_Product_Designer_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $npd    The string used to uniquely identify this plugin.
	 */
	protected $npd;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
	
		$this->npd = 'npd';
		$this->version = NPD_VERSION;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Neon_Product_Designer_Loader. Orchestrates the hooks of the plugin.
	 * - Neon_Product_Designer_i18n. Defines internationalization functionality.
	 * - Neon_Product_Designer_Admin. Defines all hooks for the admin area.
	 * - Neon_Product_Designer_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-neon-product-designer-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-neon-product-designer-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-neon-product-designer-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-neon-product-designer-public.php';

		$this->loader = new Neon_Product_Designer_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Neon_Product_Designer_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Neon_Product_Designer_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Neon_Product_Designer_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_npd_parts_submenu' );
		$this->loader->add_action( 'init', $plugin_admin, 'init_sessions', 1 );
		$this->loader->add_action( 'init', $plugin_admin, 'init_globals' );
		$this->loader->add_filter( 'upload_mimes', $plugin_admin, 'npd_add_custom_mime_types' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'vc_get_max_input_vars_php_ini' );
		 $this->loader->add_action( 'admin_notices', $plugin_admin, 'notify_customization_page_missing' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'notify_minmimum_required_parameters' );
		$this->loader->add_filter('wp_check_filetype_and_ext', $plugin_admin, 'npd_check_filetype_and_ext',99, 5);
		
		// Scenes hooks
		$scene = new NPD_Scene();
		$this->loader->add_action( 'init', $scene, 'register_cpt_scenes' );
		$this->loader->add_action( 'add_meta_boxes', $scene, 'get_scenes_metabox' );
		$this->loader->add_action( 'save_post_npd-scenes', $scene, 'save_scenes' );
		

		// Colors hooks
		$color = new NPD_Color();
		$this->loader->add_action( 'init', $color, 'register_cpt_colors_palette' );
		$this->loader->add_action( 'add_meta_boxes', $color, 'get_colors_palette_metabox' );
		$this->loader->add_action( 'save_post_npd-colors-palette', $color, 'save_colors_palette', 1 );

		// Config hooks
		$npd_config = new NPD_Config();
		// -----------------------------------------------------------------
		$this->loader->add_action( 'init', $npd_config, 'register_cpt_config' );
		$this->loader->add_action( 'save_post_npd-config', $npd_config, 'save_config' );
		$this->loader->add_action( 'save_post_product', $npd_config, 'save_config' );
		$this->loader->add_action( 'add_meta_boxes', $npd_config, 'get_config_metabox' );
		$this->loader->add_action( 'woocommerce_product_options_general_product_data', $npd_config, 'get_product_config_selector' );
		 $this->loader->add_action( 'woocommerce_product_after_variable_attributes', $npd_config, 'get_variation_product_config_selector', 10, 3 );
		$this->loader->add_filter( 'get_user_option_meta-box-order_npd-config', $npd_config, 'get_metabox_order' );
		$this->loader->add_action( 'admin_action_npd_duplicate_config', $npd_config, 'npd_duplicate_config' );
		$this->loader->add_filter( 'post_row_actions', $npd_config, 'get_duplicate_post_link', 10, 2 );
		$this->loader->add_action( 'woocommerce_save_product_variation', $npd_config, 'save_variation_settings_fields' );
		$this->loader->add_filter( 'screen_layout_columns', $npd_config, 'get_npd_config_screen_layout_columns' );
		$this->loader->add_filter( 'get_user_option_screen_layout_npd-config', $npd_config, 'get_npd_config_config_screen_layout' );

		// Products
		$product_admin = new NPD_Product( false );
		$this->loader->add_filter( 'manage_edit-product_columns', $product_admin, 'get_product_columns' );
		$this->loader->add_action( 'manage_product_posts_custom_column', $product_admin, 'get_products_columns_values', 10, 2 );
		$this->loader->add_action( 'save_post_product', $product_admin, 'save_product_settings_fields' );
		$this->loader->add_action( 'woocommerce_save_product_variation', $product_admin, 'save_product_settings_fields' );
		$this->loader->add_action( 'woocommerce_product_options_inventory_product_data', $product_admin, 'get_variable_product_details_location_notice' );

		// custom line
		$this->loader->add_action( 'woocommerce_after_add_to_cart_button', $product_admin, 'hide_cart_button' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Neon_Product_Designer_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_admin = new Neon_Product_Designer_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
		$this->loader->add_action( 'woocommerce_after_add_to_cart_button', $plugin_public, 'get_customize_btn' );
		
		$this->loader->add_filter( 'woocommerce_loop_add_to_cart_link', $plugin_public, 'get_customize_btn_loop', 10, 2 );

		// Add query vars and rewrite rules
		$this->loader->add_filter( 'query_vars', $plugin_public, 'npd_add_query_vars' );
		$this->loader->add_filter( 'init', $plugin_public, 'npd_add_rewrite_rules', 99 );

		// Products
		$npd_product = new NPD_Product( false );
		//$this->loader->add_action( 'woocommerce_add_to_cart', $npd_product, 'set_custom_upl_cart_item_data', 99, 6 );
		$this->loader->add_filter( 'body_class', $npd_product, 'get_custom_products_body_class', 10, 2 );

		// Variable filters
		$this->loader->add_action( 'init', $plugin_public, 'set_variable_action_filters', 99 );


		// Body class
		$this->loader->add_filter( 'body_class', $plugin_public, 'add_class_to_body' );

		// Shop loop item class
		$this->loader->add_filter( 'post_class', $plugin_public, 'get_item_class', 10, 3 );

		// Sessions
		$this->loader->add_action( 'init', $plugin_admin, 'init_sessions', 1 );

		// Emails
		$this->loader->add_action( 'woocommerce_order_item_meta_start', $plugin_public, 'set_email_order_item_meta', 10, 3 );

		// Save variation attributes in transients
		$this->loader->add_action( 'wp_ajax_npd_store_variation_attributes', $plugin_public, 'npd_store_variation_attributes' );
		$this->loader->add_action( 'wp_ajax_nopriv_npd_store_variation_attributes', $plugin_public, 'npd_store_variation_attributes' );

		// Design hooks
		$npd_design = new NPD_Design();

		$this->loader->add_action( 'wp_ajax_add_custom_design_to_cart', $npd_design, 'add_custom_design_to_cart_ajax' );
		$this->loader->add_action( 'wp_ajax_nopriv_add_custom_design_to_cart', $npd_design, 'add_custom_design_to_cart_ajax' );
		$this->loader->add_filter( 'woocommerce_cart_item_thumbnail', $npd_design, 'get_npd_data_image', 99, 3 );
		$this->loader->add_filter( 'woocommerce_cart_item_name', $npd_design, 'get_product_name', 99, 3 );
		$this->loader->add_filter( 'woocommerce_after_cart_item_name', $npd_design, 'get_npd_data', 99, 3 );

		$this->loader->add_action( 'woocommerce_after_order_itemmeta', $npd_design, 'get_order_custom_admin_data', 10, 3 );
		
		$this->loader->add_action( 'woocommerce_checkout_create_order_line_item', $npd_design, 'save_cart_item_custom_meta_as_order_item_meta', 10, 4 );

		//$this->loader->add_filter( 'woocommerce_email_attachments', $npd_design, 'add_order_design_to_mail', 10, 3 );

		$this->loader->add_action( 'woocommerce_before_calculate_totals', $npd_design, 'get_cart_item_price', 10 );
		
		// Emails
		$this->loader->add_action( 'woocommerce_order_item_meta_start', $plugin_public, 'set_email_order_item_meta', 10, 3 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->npd;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Neon_Product_Designer_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
