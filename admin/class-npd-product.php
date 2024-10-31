<?php

/**
 * Manage configurable product options
 *
 * @author Vertim Coders
 */
class NPD_Product {

	public $variation_id;
	public $root_product_id;
	public $product;
	public $settings;
	public $variation_settings;

	public function __construct( $id ) {
		if ( $id ) {
			$this->root_product_id = $this->get_parent( $id );
			// If it's a variable product
			if ( $id !== $this->root_product_id ) {
				$this->variation_id = $id;
			}// Simple product and others
			else {
				$this->variation_id = $this->root_product_id;
			}

			$this->product = wc_get_product( $id );
			$config        = get_post_meta( $this->root_product_id, 'npd-metas', true );
			if ( isset( $config[ $this->variation_id ] ) ) {
				$config_id = $config[ $this->variation_id ]['config-id'];
				if ( $config_id ) {
					$this->settings = get_post_meta( $config_id, 'npd-metas', true );
					$product_metas  = get_post_meta( $this->root_product_id, 'npd-metas', true );
				}
			}
		}
	}

	/**
	 * Adds the Custom column to the default products list to help identify which ones are custom
	 *
	 * @param array $defaults Default columns
	 * @return array $defaults result
	 */
	function get_product_columns( $defaults ) {
		$defaults['is_customizable'] = __( 'Neon Product', 'neon-product-designer' );
		return $defaults;
	}
	/**
	 * Sets the Custom column value on the products list to help identify which ones are custom
	 *
	 * @param type $column_name Column name
	 * @param type $id Product ID
	 */
	public function get_products_columns_values( $column_name, $id ) {
		if ( $column_name === 'is_customizable' ) {
			$npd_metas = get_post_meta( $id, 'npd-metas', true );
			if ( isset( $npd_metas[ $id ]['config-id'] ) ) {
				if ( empty( $npd_metas[ $id ]['config-id'] ) ) {
					esc_attr_e( 'No', 'neon-product-designer' );
				} else {
					esc_attr_e( 'Neon', 'neon-product-designer' );
				}
			} else {
				esc_attr_e( 'No', 'neon-product-designer' );
			}
		}
	}

	public function is_customizable() {
		return ( ! empty( $this->settings ) );
	}

	/**
	 * Checks the product contains at least one active part
	 *
	 * @return boolean
	 */
	// public function has_part() {
	// $parts = Kali_Admin_Tools::get_parse_value( $this->settings, 'parts' );
	// return ! empty( $parts );
	// }

	/**
	 * Returns the customization page URL
	 *
	 * @global Array $npd_settings
	 * @param int   $design_index Saved design index to load
	 * @param mixed $cart_item_key Cart item key to edit
	 * @param int   $order_item_id Order item ID to load
	 * @param int   $tpl_id ID of the template to load
	 * @return String
	 */
	public function get_design_url( $design_index = false, $cart_item_key = false, $order_item_id = false, $tpl_id = false ) {
		global $npd_settings;
		// global $wp_query;
		if ( $this->variation_id ) {
			$item_id = $this->variation_id;
		} else {
			$item_id = $this->root_product_id;
		}

		$options = $npd_settings['npd-general-options'];

		if ( isset( $options['page_id'] ) ) {
			$npd_page_id = $options['page_id'];
		} else {
			$npd_page_id = false;
		}

		if ( function_exists( 'icl_object_id' ) ) {
			$npd_page_id = icl_object_id( $npd_page_id, 'page', false, ICL_LANGUAGE_CODE );
		}
		$npd_page_url = '';
		if ( $npd_page_id ) {
			$npd_page_url = get_permalink( $npd_page_id );
			if ( $item_id ) {
				$query = wp_parse_url( $npd_page_url, PHP_URL_QUERY );
				// Returns a string if the URL has parameters or NULL if not
				if ( get_option( 'permalink_structure' ) ) {
					if ( substr( $npd_page_url, -1 ) !== '/' ) {
						$npd_page_url .= '/';
					}
					if ( $design_index || $design_index === 0 ) {
						$npd_page_url .= "saved-design/$item_id/$design_index/";
					} elseif ( $cart_item_key ) {
						$qty_key       = 'qty_' . esc_attr( $cart_item_key ) . '_' . esc_attr( $item_id );
						$npd_page_url .= "edit/$item_id/$cart_item_key/" . '?custom_qty=' . get_option( $qty_key, $this->get_purchase_properties()['min_to_purchase'] );
					} elseif ( $order_item_id ) {
						$npd_page_url .= "ordered-design/$item_id/$order_item_id/";
						$npd_page_url  = apply_filters( 'npd_customized_order_page_url', $npd_page_url );
					} else {
						$npd_page_url .= 'design/' . esc_attr( $item_id ) . '/';
						if ( $tpl_id ) {
							$npd_page_url .= "$tpl_id/";
						}
					}
				} else {
					if ( $design_index !== false ) {
						$npd_page_url .= '&product_id=' . esc_attr( $item_id ) . '&design_index=' . esc_attr( $design_index );
					} elseif ( $cart_item_key ) {
						$qty_key       = 'qty_' . esc_attr( $cart_item_key ) . '_' . esc_attr( $item_id );
						$npd_page_url .= '&product_id=' . esc_attr( $item_id ) . '&edit=' . esc_attr( $cart_item_key ) . '&custom_qty=' . get_option( $qty_key, $this->get_purchase_properties()['min_to_purchase'] );
					} elseif ( $order_item_id ) {
						$npd_page_url .= '&product_id=' . esc_attr( $item_id ) . '&vcid=' . esc_attr( $order_item_id );
					} else {
						$npd_page_url .= '&product_id=' . esc_attr( $item_id );
						if ( $tpl_id ) {
							$npd_page_url .= "&tpl=$tpl_id";
						}
					}
				}
			}
		}

		return $npd_page_url;
	}

	/**
	 * Returns a variation root product ID
	 *
	 * @param type $variation_id Variation ID
	 * @return int
	 */
	public function get_parent( $variation_id ) {
		$variable_product = wc_get_product( $variation_id );
		if ( ! $variable_product ) {
			return false;
		}
		if ( $variable_product->get_type() !== 'variation' ) {
			$product_id = $variation_id;
		} else {
			$product_id = $variable_product->get_parent_id();
		}

		return $product_id;
	}

	 /**
	  * Returns the defined value for a product setting which can be local(product metas) or global (options)
	  *
	  * @param array  $product_settings Product options
	  * @param array  $global_settings Global options
	  * @param string $option_name Option name / Meta key
	  * @param int    $field_value Default value to return if empty
	  * @return string
	  */
	public function get_option( $product_settings, $global_settings, $option_name, $field_value = '' ) {
		if ( isset( $product_settings[ $option_name ] ) && ( ( ! empty( $product_settings[ $option_name ] ) ) || $product_settings[ $option_name ] === '0' ) ) {
			$field_value = $product_settings[ $option_name ];
		} elseif ( isset( $global_settings[ $option_name ] ) && ! empty( $global_settings[ $option_name ] ) ) {
			$field_value = $global_settings[ $option_name ];
		}

		return $field_value;
	}

	/**
	 * Returns the minimum and maximum order quantities
	 *
	 * @return type
	 */
	public function get_purchase_properties() {
		if ( $this->variation_id ) {
			$defined_min_qty = get_post_meta( $this->variation_id, 'variation_minimum_allowed_quantity', true );
			// We consider the values defined for the all of them
			if ( ! $defined_min_qty ) {
				$defined_min_qty = get_post_meta( $this->root_product_id, 'minimum_allowed_quantity', true );
			}

			$defined_max_qty = get_post_meta( $this->variation_id, 'variation_maximum_allowed_quantity', true );
			// We consider the values defined for the all of them
			if ( ! $defined_max_qty ) {
				$defined_max_qty = get_post_meta( $this->root_product_id, 'maximum_allowed_quantity', true );
			}
		} else {
			$defined_min_qty = get_post_meta( $this->root_product_id, 'minimum_allowed_quantity', true );
			if ( ! $defined_min_qty ) {
				$defined_min_qty = 1;
			}

			$defined_max_qty = get_post_meta( $this->root_product_id, 'variation_maximum_allowed_quantity', true );
		}

		$step    = apply_filters( 'woocommerce_quantity_input_step', '1', $this->product );
		$min_qty = apply_filters( 'woocommerce_quantity_input_min', $defined_min_qty, $this->product );

		if ( ! $defined_max_qty ) {
			$defined_max_qty = apply_filters( 'woocommerce_quantity_input_max', $this->product->backorders_allowed() ? '' : $this->product->get_stock_quantity(), $this->product );
		}

		$min_to_purchase = $min_qty;
		if ( ! $min_qty ) {
			$min_to_purchase = 1;
		}

		$defaults = array(
			'max_value' => $defined_max_qty,
			'min_value' => $min_to_purchase,
			'step'      => $step,
		);
		$args     = apply_filters( 'woocommerce_quantity_input_args', wp_parse_args( array(), $defaults ), $this->product );

		return array(
			'min'             => $args['min_value'],
			'min_to_purchase' => $args['min_value'],
			'max'             => $args['max_value'],
			'step'            => $args['step'],
		);
	}

	public function save_product_settings_fields( $item_id ) {
		$meta_key  = 'npd-metas';
		$variation = wc_get_product( $item_id );
		// If we're dealing with a variation, Product ID is the root ID of the product
		if ( get_class( $variation ) === 'WC_Product_Variation' ) {
			$product_id = $variation->get_parent_id();
		} else {
			$product_id = $item_id;
		}

		if ( isset( $_POST[ $meta_key ] ) ) {
			$meta_value = map_deep( $_POST[ $meta_key ], 'sanitize_text_field' );
			update_post_meta( $product_id, $meta_key, $meta_value );
		}
	}


	public function hide_cart_button() {
		global $product;
		global $npd_settings;
		$general_options     = $npd_settings['npd-general-options'];
		$hide_cart_button    = Kali_Admin_Tools::get_parse_value( $general_options, 'npd-hide-cart-button', true );
		$custom_products     = npd_get_custom_products();
		$anonymous_function  = function ( $vc ) {
			return $vc->id;
		};
		$custom_products_ids = array_map( $anonymous_function, $custom_products );
		$pid                 = $product->get_id();
		if ( in_array( $pid, $custom_products_ids ) && $hide_cart_button ) {
			?>
			<script type="text/javascript">
				var hide_cart_button = <?php echo esc_html( $hide_cart_button ); ?>;
				jQuery('[value="<?php echo esc_attr( $pid ); ?>"]').parent().find('.add_to_cart_button').hide();
				jQuery('[value="<?php echo esc_attr( $pid ); ?>"]').parent().find('.single_add_to_cart_button').hide();
			</script>
			<?php
		}
	}

	public function get_custom_products_body_class( $classes, $class ) {
		if ( is_singular( array( 'product' ) ) ) {
			global $npd_settings;
			$general_options  = $npd_settings['npd-general-options'];
			$hide_cart_button = Kali_Admin_Tools::get_parse_value( $general_options, 'npd-hide-cart-button', true );

			$custom_products     = npd_get_custom_products();
			$anonymous_function  = function ( $vc ) {
				return $vc->id;
			};
			$custom_products_ids = array_map( $anonymous_function, $custom_products );
			$pid                 = get_the_ID();
			$product             = new NPD_Product( $pid );
			if ( in_array( $pid, $custom_products_ids ) ) {
				array_push( $classes, 'npd-is-customizable' );
				if ( $hide_cart_button ) {
					array_push( $classes, 'npd-hide-cart-button' );
				}
			}
		}
		return $classes;
	}

	public function get_buttons( $with_upload = false ) {
		ob_start();
		$content      = '';
		$product      = $this->product;
		$npd_metas    = $this->settings;
		$product_page = get_permalink( $product->get_id() );

		if ( $this->variation_id ) {
			$item_id = $this->variation_id;
		} else {
			$item_id = $this->root_product_id;
		}

		if ( $product->get_type() === 'variable' ) {
			$variations = $product->get_available_variations();
			foreach ( $variations as $variation ) {
				if ( ! $variation['is_purchasable'] || ! $variation['is_in_stock'] ) {
					continue;
				}
				$npd_product = new NPD_Product( $variation['variation_id'] );
				if ( $npd_product->is_customizable() ) {
					echo $npd_product->get_buttons( $with_upload );
				}
			}
		} else if( $this->is_customizable() ) {

			?>
			<div class="npd-buttons-wrap-<?php echo $product->get_type(); ?>" data-id="<?php echo $this->variation_id; ?>">
					
			<?php

			$default_design_btn_url = $this->get_design_url();
			$content               .= '<a  href="' . esc_url( $default_design_btn_url ) . '" class="mg-top-10 npd-design-product">' . apply_filters( 'npd_default_design_btn_filter', __( 'Neon Design', 'neon-product-designer' ) ) . '</a>';

			if ( ! isset( $item_id ) ) {
				$item_id = '';
			}
			if ( ! isset( $default_design_btn_url ) ) {
				$default_design_btn_url = '';
			}
			echo apply_filters( 'npd_show_customization_buttons_in_modal', $content, $item_id, $default_design_btn_url, $product->get_type() );
			?>
			</div>
			<?php
		}
		$output = ob_get_clean();
		return $output;
	}

	public function get_variable_product_details_location_notice() {
		?>
		<div class="options_group show_if_simple show_if_variable">
			<p class="form-field _sold_individually_field show_if_simple show_if_variable" style="background: #00a0d2;color: white;display: block;padding:  0 !important;padding-left:  10px !important;">
			<?php esc_attr_e( 'In order to assign a configuration to a variation, you will need to go into the variation properties (same area you define a variation price).', 'neon-product-designer' ); ?>
			</p>
		</div>
		<?php
	}



	public function save_config( $post_id ) {
		$meta_key = 'npd-metas';

		if ( isset( $_POST[ $meta_key ] ) ) {
			$meta_value = map_deep( $_POST[ $meta_key ], 'sanitize_text_field' );
			update_post_meta( $post_id, $meta_key, $meta_value );
		}
	}










}
