<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.vertimcoders.com/
 * @since      1.0.0
 *
 * @package    Neon_Product_Designer
 * @subpackage Neon_Product_Designer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Neon_Product_Designer
 * @subpackage Neon_Product_Designer/admin
 * @author     Vertim Coders <freelance@vertimcoders.com>
 */
class Neon_Product_Designer_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $npd    The ID of this plugin.
	 */
	private $npd;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $npd       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $npd, $version ) {

		$this->npd     = $npd;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Neon_Product_Designer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Neon_Product_Designer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style(
			$this->npd,
			plugin_dir_url( __FILE__ ) . 'css/neon-product-designer-admin.css',
			array(),
			$this->version,
			'all'
		);

		wp_enqueue_style(
			'kali-admin-ui',
			NPD_URL . 'includes/kali-admin-tools/css/kali-admin-ui.css',
			array(),
			'1.0.0',
			'all'
		);

		wp_enqueue_style(
			'kali-select2',
			NPD_URL . 'includes/kali-admin-tools/css/select2.min.css',
			array(),
			'1.0.0',
			'all'
		);

		wp_enqueue_style(
			'kali-modal',
			NPD_URL . 'includes/kali-admin-tools/js/modal/modal.min.css',
			array( 'jquery' ),
			$this->version,
			false
		);

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Neon_Product_Designer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Neon_Product_Designer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script(
			'iris',
			admin_url( 'js/iris.min.js' ),
			array(
				'jquery-ui-draggable',
				'jquery-ui-slider',
				'jquery-touch-punch',
			),
			$this->version,
			1
		);

		$colorpicker_l10n = array(
			'clear'         => __( 'Clear' ),
			'defaultString' => __( 'Default' ),
			'pick'          => __( 'Select Color' ),
			'current'       => __( 'Current Color' ),
		);

		wp_localize_script(
			'wp-color-picker',
			'wpColorPickerL10n',
			$colorpicker_l10n
		);

		wp_enqueue_script(
			$this->npd,
			plugin_dir_url( __FILE__ ) .
			'js/neon-product-designer-admin.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		wp_enqueue_script(
			'select2',
			NPD_URL . 'includes/kali-admin-tools/js/select2.min.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		wp_enqueue_script(
			'SpryTabbedPanels',
			NPD_URL . 'includes/kali-admin-tools/js/SpryTabbedPanels.min.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		/* wp_enqueue_script(
			'kali-modal',
			NPD_URL . 'includes/kali-admin-tools/js/modal/modal.min.js',
			array( 'jquery' ),
			$this->version,
			true
		); */

		wp_enqueue_script(
			'kali-admin-tools',
			NPD_URL . 'includes/kali-admin-tools/js/kali-admin-tools-scripts.js',
			array( 'jquery' ),
			$this->version,
			true
		);
		wp_localize_script(
			'kali-admin-tools',
			'home_url',
			array(
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'data_var_1' => 'value 1',
				'data_var_2' => 'value 2',
			)
		);

	}

	/**
	 * Builds all the plugin menu and submenu
	 */
	public function add_npd_parts_submenu() {
		if ( class_exists( 'WooCommerce' ) ) {
			global $submenu;
			$icon = NPD_URL . 'admin/images/npd-dashicon.png';

			add_menu_page(
				'Neon Product Designer',
				'NPD',
				'manage_product_terms',
				'npd-manage-dashboard',
				array( $this, 'get_fonts_page' ),
				$icon,
				10
			);

			add_submenu_page(
				'npd-manage-dashboard',
				__( 'Manage Fonts', 'neon-product-designer' ),
				__( 'Manage Fonts', 'neon-product-designer' ),
				'manage_product_terms',
				'npd-manage-fonts',
				array( $this, 'get_fonts_page' )
			);

			add_submenu_page(
				'npd-manage-dashboard',
				__( 'Manage Scenes', 'neon-product-designer' ),
				__( 'Manage Scenes', 'neon-product-designer' ),
				'manage_product_terms',
				'edit.php?post_type=npd-scenes',
				false
			);

			add_submenu_page(
				'npd-manage-dashboard',
				__(
					'Manage Colors',
					'neon-product-designer'
				),
				__( 'Manage Colors', 'neon-product-designer' ),
				'manage_product_terms',
				'edit.php?post_type=npd-colors-palette',
				false
			);


			add_submenu_page(
				'npd-manage-dashboard',
				__(
					'Configurations',
					'neon-product-designer'
				),
				__( 'Configurations', 'neon-product-designer' ),
				'manage_product_terms',
				'edit.php?post_type=npd-config',
				false
			);
			

			add_submenu_page(
				'npd-manage-dashboard',
				__( 'Settings', 'neon-product-designer' ),
				__( 'Settings', 'neon-product-designer' ),
				'manage_product_terms',
				'npd-manage-settings',
				array( $this, 'get_settings_page' )
			);

			$submenu[ 'npd-manage-dashboard' ][] = array( '<div id="user-manual">Go Pro</div>', 'manage_product_terms', 'https://neon-configurator.vertimcoders.com?utm_source=free_version&utm_medium=cpc&utm_campaign=Neon%20Product%20Designer' );
			
		}
	}

	/**
	 * Builds the fonts management page
	 */
	public function get_fonts_page() {
		include_once NPD_DIR . '/includes/npd-add-fonts.php';
		npd_add_fonts();
	}

	/**
	 *
	 * Add additionnel mime type for upload file
	 * @param array $mimes
	 * @return array $mimes
	 */
	public function npd_add_custom_mime_types( $mimes ) {
		return array_merge(
			$mimes,
			array(
				'svg' => 'image/svg+xml',
				'ttf' => 'application/x-font-ttf',
				'icc' => 'application/vnd.iccprofile',
			)
		);
	}

	public function vc_get_max_input_vars_php_ini() {
		$total_max_normal = ini_get( 'max_input_vars' );
		$msg              = __( "Your max input var is <strong>$total_max_normal</strong> but this page contains <strong>{nb}</strong> fields. You may experience a lost of data after saving. In order to fix this issue, please increase <strong>the max_input_vars</strong> value in your php.ini file.", 'neon-product-designer' );
		
		$allowed_html = array(
		    'br'     => array(),
		    'b'     => array(),
		    'em'     => array(),
		    'strong' => array(),
		);

		?>

		<script type="text/javascript">
			var vc_max_input_vars = <?php echo esc_html($total_max_normal); ?>;
			var vc_max_input_msg = "<?php echo wp_kses( $msg, $allowed_html) ; ?>";
		</script>         
		<?php
	}

	/**
	 * Initialize the plugin sessions
	 */
	function init_sessions() {
		if ( ! session_id() ) {
			session_start();
		}

		if ( ! isset( $_SESSION['npd-data-to-load'] ) ) {
			$_SESSION['npd-data-to-load'] = '';
		}

		$_SESSION['npd_calculated_totals'] = false;
	}

	/**
	 * Gets the settings and put them in a global variable
	 *
	 * @global array $npd_settings Settings
	 */
	function init_globals() {
		global $npd_settings;
		$npd_settings['npd-general-options'] = get_option( 'npd-general-options' );
	}

	private function get_admin_option_field( $title, $option_group, $field_name, $type, $default, $class, $css, $tip, $options_array ) {
		$field = array(
			'title'   => __( $title, 'neon-product-designer' ),
			'name'    => $option_group . '[' . $field_name . ']',
			'type'    => $type,
			'default' => $default,
			'class'   => $class,
			'css'     => $css,
			'desc'    => __( $tip, 'neon-product-designer' ),
		);
		if ( ! empty( $options_array ) ) {
			$field['options'] = $options_array;
		}
		return $field;
	}



	/**
	 * Builds the settings page
	 */
	function get_settings_page() {
		npd_remove_transients();

		if ( isset( $_POST ) && ! empty( $_POST ) ) {
			$this->save_npd_settings_tab_options();
			global $wp_rewrite;
			$wp_rewrite->flush_rules( false );
		}

		$tab = array(
			'title'	=> 'General',
			'content'	=> $this->get_general_settings()
		);

		$pannel_block = array();

		array_push( $pannel_block, $tab);

		$pannel_block = apply_filters( 'npd_general_setting_panel', $pannel_block );

		wp_enqueue_media();
		?>
		<form method="POST">
			<div id="npd-settings">
				<div class="wrap">
					<h2><?php esc_html_e( 'Neon Product Designer Settings', 'neon-product-designer' ); ?></h2>
				</div>

				<?php
					$panel_id = 1;
					foreach ($pannel_block as $key => $panel) {
						?>
							<div id="TabbedPanels<?php echo esc_attr($panel_id) ;?>" class="TabbedPanels">
								<ul class="TabbedPanelsTabGroup ">
									<li class="TabbedPanelsTab " tabindex="1"><span><strong><?php esc_html_e( $panel['title'], 'neon-product-designer' ); ?></strong></span> </li>
								</ul>

								<div class="TabbedPanelsContentGroup">
									<div class="TabbedPanelsContent">
										<div class='npd-grid npd-grid-pad'>
											<?php echo $panel['content']; 
										?>
										</div>
									</div>
								</div>
							</div>
						<?php
						$panel_id += 1;
					}
				?>
			
			</div>
			<input type="hidden" name="npdw-nonce-security" value="<?php echo esc_attr(wp_create_nonce('npdw-nonce-security-action')) ;?>" class="">
			<input type="submit" value="<?php esc_attr_e( 'Save', 'neon-product-designer' ); ?>" class="button button-primary button-large mg-top-10-i">
		</form>
		<?php
	}

	/**
	 * Save the settings
	 */
	private function save_npd_settings_tab_options() {
		if(isset($_POST['npdw-nonce-security']))
		{
			if ( wp_verify_nonce($_POST['npdw-nonce-security'], 'npdw-nonce-security-action')) {
				
				if ( isset( $_POST ) && ! empty( $_POST ) ) {
					foreach ( $_POST as $key => $values ) {
						$opt_value = map_deep( $values, 'sanitize_text_field' );
						update_option( $key, $opt_value);
					}
					
					$this->init_globals();
					?>
					<div id="message" class="updated below-h2"><p><?php echo __( 'Settings successfully saved.', 'neon-product-designer' ); ?></p></div>
					<?php
				}
			}
		}
	}

	/**
	 * Format the checkbox option in the settings
	 *
	 * @param type $option_name
	 * @param type $option_array
	 */
	public function transform_checkbox_value( $option_name, $option_array ) {
		foreach ( $option_array as $option ) {
			if ( ! isset( $_POST[ $option_name ][ $option ] ) ) {
				$_POST[ $option_name ][ $option ] = 'no';
			}
		}
	}



	// Builds the general settings options
	private function get_general_settings() {
		$options = array();

		$general_options_begin = array(
			'type'  => 'sectionbegin',
			'id'    => 'npd-general-options',
			'table' => 'options',
			'title' => __( 'General Settings', 'neon-product-designer' ),
		);

		$args = array(
			'post_type' => 'page',
			'nopaging'  => true,
		);

		$customizer_page = array(
			'title'   => __( 'Design page', 'neon-product-designer' ),
			'desc'    => __( 'This setting allows the plugin to locate the page where customizations are made. Please note that this page can only be accessed by our plugin and should not appear in any menu.', 'neon-product-designer' ),
			'name'    => 'npd-general-options[page_id]',
			'type'    => 'post-type',
			'default' => '',
			'table'	=> 'options',
			'class'   => 'chosen_select_nostd',
			'args'    => $args,
		);

		$content_filter = array(
			'title'   => __( 'Manage the configuration page', 'neon-product-designer' ),
			'name'    => 'npd-general-options[npd-content-filter]',
			'default' => '1',
			'type'    => 'radio',
			'table'	=> 'options',
			'desc'    => __( 'If yes, automatically append editor to the design page. This option allows you to define whether or not you want to use a shortcode to display the the customizer in the selected page.', 'neon-product-designer' ),
			'options' => array(
				'1' => __( 'Yes', 'neon-product-designer' ),
				'0' => __( 'No', 'neon-product-designer' ),
			),
			'class'   => 'chosen_select_nostd',
		);

		$customizer_cart_display = array(
			'title'   => __( 'Parts position in cart', 'neon-product-designer' ),
			'name'    => 'npd-general-options[npd-parts-position-cart]',
			'default' => 'thumbnail',
			'type'    => 'radio',
			'table'	=> 'options',
			'desc'    => __( 'This option allows you to set where to show your customized products parts on the cart page', 'neon-product-designer' ),
			'options' => array(
				'thumbnail' => __( 'Thumbnail column', 'neon-product-designer' ),
				'name'      => __( 'Name column', 'neon-product-designer' ),
			),
			'class'   => 'chosen_select_nostd',
		);

		$send_attachments = array(
			'title'   => __( 'Send the design as an attachment', 'neon-product-designer' ),
			'name'    => 'npd-general-options[npd-send-design-mail]',
			'default' => '1',
			'type'    => 'radio',
			'table'	=> 'options',
			'desc'    => __( 'This option allows you to send or not the design by mail after checkout', 'neon-product-designer' ),
			'options' => array(
				'1' => __( 'Yes', 'neon-product-designer' ),
				'0' => __( 'No', 'neon-product-designer' ),
			),
			'class'   => 'chosen_select_nostd',
		);

		$cart_button                   = array(
			'title'   => __( 'Add to cart', 'neon-product-designer' ),
			'name'    => 'npd-general-options[npd-cart-btn]',
			'default' => '1',
			'type'    => 'radio',
			'table'	=> 'options',
			'desc'    => __( 'This option allows you to show/hide the cart button on the customization page', 'neon-product-designer' ),
			'options' => array(
				'1' => __( 'Yes', 'neon-product-designer' ),
				'0' => __( 'No', 'neon-product-designer' ),
			),
			'class'   => 'chosen_select_nostd',
		);
		$hide_design_buttons_cart_page = array(
			'title'   => __( 'Hide design buttons on shop page', 'neon-product-designer' ),
			'name'    => 'npd-general-options[npd-hide-btn-shop-pages]',
			'default' => '0',
			'type'    => 'radio',
			'table'	=> 'options',
			'desc'    => __( 'This option allows you to show/hide the cart button on the customization page', 'neon-product-designer' ),
			'options' => array(
				'1' => __( 'Yes', 'neon-product-designer' ),
				'0' => __( 'No', 'neon-product-designer' ),
			),
			'class'   => 'chosen_select_nostd',
		);
		$add_to_cart_action            = array(
			'title'   => __( 'Redirect after adding a custom design to the cart?', 'neon-product-designer' ),
			'name'    => 'npd-general-options[npd-redirect-after-cart]',
			'default' => '0',
			'type'    => 'radio',
			'table'	=> 'options',
			'desc'    => __( 'This option allows you to define what to do after adding a design to the cart', 'neon-product-designer' ),
			'options' => array(
				'1' => __( 'Yes', 'neon-product-designer' ),
				'0' => __( 'No', 'neon-product-designer' ),
			),
			'class'   => 'chosen_select_nostd',
		);

		$hide_requirements_notices = array(
			'title'     => __( 'Hide requirements notices', 'neon-product-designer' ),
			'name'      => 'npd-general-options[hide-requirements-notices]',
			'default'   => '0',
			'type'      => 'radio',
			'table'	=> 'options',
			'desc'      => __( 'This option allows you to define whether or not you want to hide the requirement notice.', 'neon-product-designer' ),
			'options'   => array(
				'0' => __( 'No', 'neon-product-designer' ),
				'1' => __( 'Yes', 'neon-product-designer' ),
			),
			'row_class' => 'npd_hide_requirements',
			'class'     => 'chosen_select_nostd',
		);

		$hide_cart_button_for_custom_products = array(
			'title'   => __( 'Hide add to cart button for custom products', 'neon-product-designer' ),
			'name'    => 'npd-general-options[npd-hide-cart-button]',
			'default' => '1',
			'type'    => 'radio',
			'table'	=> 'options',
			'desc'    => __( 'This option allows you to define whether or not you want to hide the add to cart button for custom products on the products page.', 'neon-product-designer' ),
			'options' => array(
				'1' => __( 'Yes', 'neon-product-designer' ),
				'0' => __( 'No', 'neon-product-designer' ),
			),
			'class'   => 'chosen_select_nostd',
		);

		$follow_scroll = array(
			'title'   => __( 'Follow scroll', 'neon-product-designer' ),
			'name'    => 'npd-general-options[follow-scroll]',
			'default' => '1',
			'type'    => 'radio',
			'table'	=> 'options',
			'desc'    => __( 'This option allows you to define whether or not you want to enable follow scroll.', 'neon-product-designer' ),
			'options' => array(
				'1' => __( 'Yes', 'neon-product-designer' ),
				'0' => __( 'No', 'neon-product-designer' ),
			),
			'class'   => 'chosen_select_nostd',
		);

		$general_options_end = array( 'type' => 'sectionend' );
		
		//array_push( $options, $nonce_security );
		array_push( $options, $general_options_begin );
		array_push( $options, $customizer_page );
		array_push( $options, $content_filter );
		array_push( $options, $customizer_cart_display );
		array_push( $options, $send_attachments );
		array_push( $options, $cart_button );
		array_push( $options, $add_to_cart_action );
		array_push( $options, $hide_cart_button_for_custom_products );
		array_push( $options, $hide_design_buttons_cart_page );
		array_push( $options, $hide_requirements_notices );
		array_push( $options, $follow_scroll );
		array_push( $options, $general_options_end );
		$options = apply_filters( 'npd_general_options', $options );
		return Kali_Admin_Tools::get_fields( $options );
	}



	/**
	 * Alerts the administrator if the customization page is missing
	 *
	 * @global array $npd_settings
	 */
	function notify_customization_page_missing() {
		$options      = get_option('npd-general-options');
		$hide_notices = Kali_Admin_Tools::get_parse_value( $options, 'hide-requirements-notices', false );
		if ( isset( $options['page_id'] ) ) {
			$npd_page_id = $options['page_id'];
		} else {
			$npd_page_id = '';
		}

		$settings_url = get_bloginfo( 'url' ) . '/wp-admin/admin.php?page=npd-manage-settings';
		if ( ! class_exists( 'WooCommerce' ) ) {
			echo '<div class="error">
	                   <p><b>Neon Product Designer: </b>WooCommerce is not installed on your website. You will not be able to use the features of the plugin.</p>
	                </div>';
		} else {
			if ( empty( $npd_page_id ) ) {
				echo '<div class="error">
	                   <p><b>Neon Product Designer: </b>The design page is not defined. Please configure it in <a href="' . esc_url($settings_url) . '">plugin settings page</a>: .</p>
	                </div>';
			}
			if ( ! extension_loaded( 'zip' ) && ! $hide_notices ) {
				echo '<div class="error">
	                   <p><b>Neon Product Designer: </b>ZIP extension not loaded on this server. You won\'t be able to generate zip outputs.</p>
	                </div>';
			}
			// if ( ! class_exists( 'Imagick' ) && ! $hide_notices ) {
			// 	echo '<div class="error">
			// 	<p><b>Neon Product Designer: </b>Imagick classes not installed on this server. You won\'t be able to generate cmyk outputs or handle adobe files conversion to image.</p>
			// 	</div>';
			// }
		}
	}

	/**
	 * Alerts the administrator if the minimum requirements are not met
	 */
	function notify_minmimum_required_parameters() {
		global $npd_settings;
		$general_options = Kali_Admin_Tools::get_parse_value( $npd_settings, 'npd-general-options' );
		$hide_notices    = Kali_Admin_Tools::get_parse_value( $general_options, 'hide-requirements-notices', false );
		$allowed_html = array(
		    'a'      => array(
		        'href'  => array(),
		        'title' => array(),
		        'class' =>array(),
		    ),
		    'div'      => array(
		        'class'  => array(),
		        'id' => array(),
		    ),
		    'br'     => array(),
		    'b'     => array(),
		    'em'     => array(),
		    'strong' => array(),
		);
		if ( $hide_notices ) {
			return;
		}
		

		$message              = '';
		$permalinks_structure = get_option( 'permalink_structure' );
		if ( strpos( $permalinks_structure, 'index.php' ) !== false ) {
			$message .= 'Your permalinks structure is currently set to <b>custom</b> with index.php present in the structure. We recommand to set this value to <b>Post name</b> to avoid any issue with our plugin.<br>';
		}
		if ( ! empty( $message ) ) {
			echo '<div class="error"><p><b>Neon Product Designer: </b><br>' . wp_kses($message,$allowed_html) . '</p></div>';
		}
	}

	public function npd_check_filetype_and_ext($data, $file, $filename, $mimes, $real_mime) {
        if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
            return $data;
        }

        $wp_file_type = wp_check_filetype( $filename, $mimes );

        // Check for the file type you want to enable, e.g. 'svg'.
        if ( 'ttf' === $wp_file_type['ext'] ) {
            $data['ext'] = 'ttf';
            $data['type'] = 'font/ttf';
        }

        if ( 'otf' === $wp_file_type['ext'] ) {
            $data['ext'] = 'otf';
            $data['type'] = 'font/otf';
        }

        return $data;
    }


}
