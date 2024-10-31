<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.vertimcoders.com/
 * @since      1.0.0
 *
 * @package    Neon_Product_Designer
 * @subpackage Neon_Product_Designer/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Neon_Product_Designer
 * @subpackage Neon_Product_Designer/public
 * @author     Vertim Coders <freelance@vertimcoders.com>
 */
class Neon_Product_Designer_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string $plugin_name  The name of the plugin.
	 * @param      string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/neon-product-designer-public.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'npd-modal-css', NPD_URL . 'public/js/modal/modal.css', array(), $this->version, 'all' );


	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/neon-product-designer-public.js', array( 'jquery' ), $this->version, false );
        wp_localize_script($this->plugin_name, 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
        wp_enqueue_script( 'npd-accounting', NPD_URL . 'public/js/accounting.min.js', array( 'jquery' ), "", false );
         wp_enqueue_script( 'wp-serializejson', NPD_URL . 'public/js/jquery.serializejson.min.js', array( 'jquery' ), "", false );
         wp_enqueue_script('wp-js-hooks', NPD_URL . 'public/js/wp-js-hooks.min.js', array('jquery'), "", false);
         wp_enqueue_script('npd-html2canvas', NPD_URL . 'public/js/html2canvas.js', array('jquery'), "", false);
         wp_enqueue_script('npd-modal-js', NPD_URL . 'public/js/modal/modal.min.js', array('jquery'), "", false);

	}


	/**
	 * Register the plugin shortcodes
	 */
	public function register_shortcodes() {
		// add_shortcode('npd-templates', array($this, 'get_templates'));
		add_shortcode( 'npd-products', array( $this, 'get_products_display' ) );
		add_shortcode( 'npd-editor', array( $this, 'get_editor_shortcode_handler' ) );
	}
	/**
	 * get editor shortcode
	 */
	public function get_editor_shortcode_handler($atts) {
		global $wp_query;
		if ( ! isset( $wp_query->query_vars['product_id'] ) && ! isset( $atts[ 'product' ] )) {
			return __( "You're trying to access the customization page whitout a product to customize. This page should only be accessed using one of the customization buttons.", 'neon-product-designer' );
		}
		else if(isset( $atts[ 'product' ])){
			$product_id = $atts[ 'product' ];
		}
		else if(isset( $wp_query->query_vars['product_id'] )){
			$product_id = $wp_query->query_vars['product_id'];
		}

		$item_id = $product_id;
		$editor_obj = new NPD_Editor( $item_id );
		return $editor_obj->get_editor();

		
	}
	/**
	 * get products display
	 */
	function get_products_display( $atts ) {
		global $wpdb;

		extract(
			shortcode_atts(
				array(
					'cat'      => '',
					'products' => '',
					'cols'     => '3',
				),
				$atts,
				'npd-products'
			)
		);

		$where = '';
		if ( ! empty( $cat ) ) {
			$where .= " AND $wpdb->term_relationships.term_taxonomy_id IN ($cat)";
		} elseif ( ! empty( $products ) ) {
			$where .= " AND p.ID IN ($products)";
		} else {
			$where = '';
		}
		$search = '"is-customizable";s:1:"1"';

		$products = $wpdb->get_results(
			"
                            SELECT distinct p.id
                            FROM $wpdb->posts p
                            JOIN $wpdb->postmeta pm on pm.post_id = p.id
                            INNER JOIN $wpdb->term_relationships ON (p.ID = $wpdb->term_relationships.object_id	)
                            WHERE p.post_type = 'product'
                            AND p.post_status = 'publish'
                            AND pm.meta_key = 'npd-metas'
                            $where
                            
                            "
		);
		ob_start();
		?>
		<div class='container wp-products-container npd-grid npd-grid-pad'>
			<?php
			$shop_currency_symbol = get_woocommerce_currency_symbol();
			foreach ( $products as $product ) {
				$prod                     = wc_get_product( $product->id );
				$url                      = get_permalink( $product->id );
				$npd_product              = new NPD_Product( $product->id );
				$npc_metas                = $npd_product->settings;
				?>
				<div class='npd-col-1-<?php echo esc_attr( $cols ); ?> cat-item-ctn' style='width:calc((100% / <?php echo esc_attr( $cols ); ?>) - 20px);'>
					<div class='cat-item'>
						<h3><?php echo esc_html( $prod->get_title() ); ?> 
							<span><?php echo esc_html( $shop_currency_symbol ) . '' . esc_html( $prod->get_price() ); ?></span>
						</h3>
						<?php echo get_the_post_thumbnail( $product->id, 'medium' ); ?>
						<hr>
						<?php
						if ( $prod->get_type() == 'simple' ) {
								?>
								<a href="<?php echo esc_attr( $npd_product->get_design_url() ); ?>" class='npd-design-product'> <?php esc_html_e( 'Neon Design', 'neon-product-designer' ); ?></a>
								<?php
							
						} else {
							?>
							<a href="<?php echo esc_attr( $url ); ?>" class='npd-design-product'> <?php esc_html_e( 'Select option', 'neon-product-designer' ); ?></a>
							<?php
							
						}
						
						?>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	function get_design_from_blank_urls( $post_id, $product ) {
		$design_from_blank_urls = array();
		if ( $product->get_type() == 'variable' ) {
			$variations = $product->get_available_variations();
			foreach ( $variations as $variation ) {
				$variation_id                            = $variation['variation_id'];
				$npd_product                             = new NPD_Product( $variation_id );
				$design_from_blank_urls[ $variation_id ] = $npd_product->get_design_url();
			}
		} else {
			$npd_product                        = new NPD_Product( $post_id );
			$design_from_blank_urls[ $post_id ] = $npd_product->get_design_url();
		}
		?>
		<script>
			var design_from_blank_urls =<?php echo json_encode( $design_from_blank_urls ); ?>;
		</script>
		<?php
	}

	function get_customize_btn() {
		$product_id  = get_the_ID();
		$npd_product = new NPD_Product( $product_id );
		echo wp_kses_post($npd_product->get_buttons( true ));

	}

	function get_customize_btn_loop( $html, $product ) {
		global $npd_settings;
		$general_options        = $npd_settings['npd-general-options'];
		$hide_buttons_shop_page = Kali_Admin_Tools::get_parse_value( $general_options, 'npd-hide-btn-shop-pages', 0 );
		if ( $hide_buttons_shop_page ) {
			return $html;
		}

		$product_class = get_class( $product );
		if ( $product_class == 'WC_Product_Variable' ) {

		} elseif ( $product_class == 'WC_Product_Simple' ) {
			$npd_product = new NPD_Product( $product->get_id() );
			$html       .= $npd_product->get_buttons();
		}
		return $html;
	}

	private function npd_get_woo_version_number() {
		// If get_plugins() isn't available, require it
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Create the plugins folder and file variables
		$plugin_folder = get_plugins( '/' . 'woocommerce' );
		$plugin_file   = 'woocommerce.php';

		// If the plugin version number is set, return it
		if ( isset( $plugin_folder[ $plugin_file ]['Version'] ) ) {
			return $plugin_folder[ $plugin_file ]['Version'];
		} else {
			// Otherwise return null
			return null;
		}
	}

	function set_variable_action_filters() {
		global $npd_settings;
		$options     = $npd_settings['npd-general-options'];
		$woo_version = $this->npd_get_woo_version_number();
		if ( isset( $options['npd-parts-position-cart'] ) && $options['npd-parts-position-cart'] == 'name' ) {
			if ( $woo_version < 2.1 ) {
				// Old WC versions
				add_filter( 'woocommerce_in_cart_product_title', array( $this, 'get_npd_data' ), 10, 3 );
			} else {
				// New WC versions
				add_filter( 'woocommerce_cart_item_name', array( $this, 'get_npd_data' ), 10, 3 );
			}
		} else {
			if ( $woo_version < 2.1 ) {
				// Old WC versions
				add_filter( 'woocommerce_in_cart_product_thumbnail', array( $this, 'get_npd_data' ), 10, 3 );
			} else {
				// New WC versions
				add_filter( 'woocommerce_cart_item_name', array( $this, 'get_npd_data' ), 10, 3 );
			}
		}
		if ( isset( $options['npd-content-filter'] ) ) {
			$append_content_filter = $options['npd-content-filter'];
		} else {
			$append_content_filter = false;
		}

		if ( $append_content_filter !== '0' && ! is_admin() ) {

			add_filter( 'the_content', array( $this, 'filter_content' ), 99 );
		}
	}

	function filter_content( $content ) {
		global $npd_settings;
		global $wp_query;
		$options     = $npd_settings['npd-general-options'];
		$npd_page_id = $options['page_id'];
		if ( function_exists( 'icl_object_id' ) ) {
			$npd_page_id = icl_object_id( $npd_page_id, 'page', false, ICL_LANGUAGE_CODE );
		}
		$current_page_id = get_the_ID();
		if ( $npd_page_id == $current_page_id ) {
			$item_id    = $wp_query->query_vars['product_id'];
			$editor_obj = new NPD_Editor( $item_id );
			$content   .= $editor_obj->get_editor();
		}
		return $content;
	}
	function get_npd_data( $thumbnail_code, $values, $cart_item_key ) {
		$variation_id = $values['variation_id'];
		if ( isset( $values['npd_design_pricing_options'] ) && ! empty( $values['npd_design_pricing_options'] ) ) {
			$npd_design_pricing_options_data = NPD_Design::get_design_pricing_options_data( $values['npd_design_pricing_options'] );
			$thumbnail_code                 .= '<br>' . $npd_design_pricing_options_data;
		}

		if ( isset( $values['npd_generated_data'] ) && isset( $values['npd_generated_data']['output'] ) ) {
			$thumbnail_code    .= '<br>';
			$customization_list = $values['npd_generated_data'];
			$upload_dir         = wp_upload_dir();
			$modals             = '';

			$i = 0;
			foreach ( $customization_list['output']['files'] as $customisation_key => $customization ) {
				$tmp_dir        = $customization_list['output']['working_dir'];
				$generation_url = $upload_dir['baseurl'] . "/NDP/$tmp_dir/$customisation_key/";
				if ( isset( $customization['preview'] ) ) {
					$image = $generation_url . $customization['preview'];
				} else {
					$image = $generation_url . $customization['image'];
				}
				$original_part_img_url = $customization_list[ $customisation_key ]['original_part_img'];
				$modal_id              = $variation_id . '_' . $cart_item_key . "$customisation_key-$i";

				$thumbnail_code .= '<span><a class="button" data-toggle="kali-modal" data-target="#' . esc_attr($modal_id) . '">' . ucfirst( $customisation_key ) . '</a></span>';
				$modals         .= '<div class="vcmodal fade npd-modal npd_part" id="' . esc_attr($modal_id) . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="vcmodal-dialog">
                                      <div class="vcmodal-content">
                                        <div class="vcmodal-header">
                                          <button type="button" class="close" data-dismiss="omodal" aria-hidden="true">&times;</button>
                                          <h4 class="vcmodal-title">' . __( 'Preview', 'neon-product-designer' ) . '</h4>
                                        </div>
                                        <div class="vcmodal-body txt-center">
                                            <div style="background-image:url(' . esc_url($original_part_img_url) . ')"><img src="' . esc_url($image) . '"></div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>';
				$i++;
			}
			array_push( npd_retarded_actions::$code, $modals );
			add_action( 'wp_footer', array( 'npd_retarded_actions', 'display_code' ), 10, 1 );

			$npd_product     = new NPD_Product( $variation_id );
			$edit_item_url   = $npd_product->get_design_url( false, $cart_item_key );
			$thumbnail_code .= '<a class="button alt" href="' . esc_url($edit_item_url) . '">' . __( 'Edit', 'neon-product-designer' ) . '</a>';
		} 

		echo wp_kses_post($thumbnail_code);
	}

	public function npd_add_query_vars( $a_vars ) {
		$a_vars[] = 'product_id';
		$a_vars[] = 'tpl';
		$a_vars[] = 'edit';
		$a_vars[] = 'design_index';
		$a_vars[] = 'vcid';
		return $a_vars;
	}

	public function npd_add_rewrite_rules( $param ) {
		global $npd_settings;
		global $wp_rewrite;
		$options = $npd_settings['npd-general-options'];
		if ( isset( $options['page_id'] ) ) {
			$npd_page_id = $options['page_id'];
		} else {
			$npd_page_id = false;
		}

		if ( function_exists( 'icl_object_id' ) ) {
			$npd_page_id = icl_object_id( $npd_page_id, 'page', false, ICL_LANGUAGE_CODE );
		}
		$npd_page = get_post( $npd_page_id );
		if ( is_object( $npd_page ) ) {
			$raw_slug = get_permalink( $npd_page->ID );
			$home_url = home_url( '/' );
			$slug     = str_replace( $home_url, '', $raw_slug );
			// If the slug does not have the trailing slash, we get 404 (ex postname = /%postname%)
			$sep = '';
			if ( substr( $slug, -1 ) != '/' ) {
				$sep = '/';
			}
			add_rewrite_rule(
					// The regex to match the incoming URL
				$slug . $sep . 'design' . '/([^/]+)/?$',
				// The resulting internal URL: `index.php` because we still use WordPress
					// `pagename` because we use this WordPress page
					// `designer_slug` because we assign the first captured regex part to this variable
					'index.php?pagename=' . $slug . '&product_id=$matches[1]',
				// This is a rather specific URL, so we add it to the top of the list
					// Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
					'top'
			);
			add_rewrite_rule(
					// The regex to match the incoming URL
				$slug . $sep . 'design' . '/([^/]+)/([^/]+)/?$',
				// The resulting internal URL: `index.php` because we still use WordPress
					// `pagename` because we use this WordPress page
					// `designer_slug` because we assign the first captured regex part to this variable
					'index.php?pagename=' . $slug . '&product_id=$matches[1]&tpl=$matches[2]',
				// This is a rather specific URL, so we add it to the top of the list
					// Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
					'top'
			);
			add_rewrite_rule(
					// The regex to match the incoming URL
				$slug . $sep . 'edit' . '/([^/]+)/([^/]+)/?$',
				// The resulting internal URL: `index.php` because we still use WordPress
					// `pagename` because we use this WordPress page
					// `designer_slug` because we assign the first captured regex part to this variable
					'index.php?pagename=' . $slug . '&product_id=$matches[1]&edit=$matches[2]',
				// This is a rather specific URL, so we add it to the top of the list
					// Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
					'top'
			);
			add_rewrite_rule(
					// The regex to match the incoming URL
				$slug . $sep . 'ordered-design' . '/([^/]+)/([^/]+)/?$',
				// The resulting internal URL: `index.php` because we still use WordPress
					// `pagename` because we use this WordPress page
					// `designer_slug` because we assign the first captured regex part to this variable
					'index.php?pagename=' . $slug . '&product_id=$matches[1]&vcid=$matches[2]',
				// This is a rather specific URL, so we add it to the top of the list
					// Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
					'top'
			);

			add_rewrite_rule(
					// The regex to match the incoming URL
				$slug . $sep . 'saved-design' . '/([^/]+)/([^/]+)/?$',
				// The resulting internal URL: `index.php` because we still use WordPress
					// `pagename` because we use this WordPress page
					// `designer_slug` because we assign the first captured regex part to this variable
					'index.php?pagename=' . $slug . '&product_id=$matches[1]&design_index=$matches[2]',
				// This is a rather specific URL, so we add it to the top of the list
					// Otherwise, the "catch-all" rules at the bottom (for pages and attachments) will "win"
					'top'
			);

			$wp_rewrite->flush_rules( false );
		}
	}

	public function set_email_order_item_meta( $item_id, $item, $order ) {
		$output = '';
		if ( is_order_received_page() ) {
			return;
		}
		if ( isset( $item['item_meta']['_npd_design_pricing_options'] ) && ( ! empty( $item['item_meta']['_npd_design_pricing_options'] ) && ( is_array( $item['item_meta']['_npd_design_pricing_options'] ) ) ) ) {
			$output .= "<div class='npd-order-config kali-wrap xl-gutter-8'><div class='kali-col xl-2-3'>";
			foreach ( $item['item_meta']['_npd_design_pricing_options'] as $key => $ninjaform_option ) {
				$output .= $ninjaform_option;
			}
			$output .= '</div></div>';
		}
		echo wp_kses_post($output);
	}

	public function add_class_to_body( $classes ) {
		global $wp_query;
		if ( isset( $wp_query->query_vars['product_id'] ) ) {
			array_push( $classes, 'npd-customization-page npd-product-' . $wp_query->query_vars['product_id'] );
		}
		return $classes;
	}

	public function get_item_class( $classes, $class, $post_id ) {
		global $npd_settings;
		$general_options  = $npd_settings['npd-general-options'];
		$hide_cart_button = Kali_Admin_Tools::get_parse_value( $general_options, 'npd-hide-cart-button', true );

		if ( in_array( 'product', $classes ) ) {
			$npd_product = new NPD_Product( $post_id );
			if ( $npd_product->is_customizable() ) {
				array_push( $classes, 'npd-is-customizable' );
			}
			if ( $hide_cart_button ) {
				array_push( $classes, 'npd-hide-cart-button' );
			}
		}
		return $classes;
	}

	public function npd_store_variation_attributes() {
		$data       = sanitize_text_field($_POST['data']);
		$variations = $data['data'];
		$transient  = uniqid( 'npd-' );
		set_transient( $transient, $variations, HOUR_IN_SECONDS );
		$_SESSION['npd_key'] = $transient;
		die();
	}


}
