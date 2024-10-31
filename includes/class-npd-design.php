<?php

/**
 * Contains all methods and hooks callbacks related to the user design
 *
 * @author Vertim Coders
 */
class NPD_Design {

	function add_custom_design_to_cart_ajax() {
		global $woocommerce;
		$message = '';
		if ( session_status() !== 2 ) {
			session_start();
		}

		if ( npd_woocommerce_version_check() ) {
			$cart_url = wc_get_cart_url();
		} else {
			$cart_url = $woocommerce->cart->get_cart_url();
		}

		$main_variation_id                 = filter_input( INPUT_POST, 'variation_id' );
		$cart_item_key                     = filter_input( INPUT_POST, 'cart_item_key' );
		$preview_img                       = filter_input( INPUT_POST, 'preview_img' );
		$price                             = json_decode( filter_input( INPUT_POST, 'npd_price' ) );
		$_SESSION['npd_calculated_totals'] = false;

		$npd_product               = new NPD_Product( $main_variation_id );
		$npd_metas                 = $npd_product->settings;
		$data                      = array();
		$newly_added_cart_item_key = false;
		// $get_recap                 = sanitize_text_field($_POST['recap']);
		$get_recap                     = map_deep( $_POST['recap'], 'sanitize_text_field' );
		$recap                         = (array) json_decode( wp_unslash( $get_recap ) );
		$price                         = (array) $price;
		$recap['npd_additional_price'] = $price['additional'];
		$file_name                     = uniqid( 'npd-' );
		$this->save_canvas_image( $preview_img, $file_name );
		$preview_img = NPD_IMAGE_URL . '/' . $file_name . '.png';

		if ( $cart_item_key ) {
			$woocommerce->cart->cart_contents[ $cart_item_key ]['npd_generated_data'] = $recap;
			$woocommerce->cart->cart_contents[ $cart_item_key ]['npd_preview_img']    = $preview_img;
			$woocommerce->cart->calculate_totals();
			$message = "<div class='npd_notification success f-right'>" . __( 'Item successfully updated.', 'neon-product-designer' ) . " <a href='$cart_url'>" . __( 'View Cart', 'neon-product-designer' ) . '</a></div>';
		} else {
			$newly_added_cart_item_key = $this->add_designs_to_cart( $recap, $preview_img );
			if ( $newly_added_cart_item_key ) {
				$message = "<div class='npd_notification success f-right'>" . __( 'Product successfully added to cart.', 'neon-product-designer' ) . " <a href='$cart_url'>" . __( 'View Cart', 'neon-product-designer' ) . '</div>';
			} else {
				$message = "<div class='npd_notification failure f-right'>" . __( 'A problem occured while adding the product to the cart. Please try again.', 'neon-product-designer' ) . '</div>';
			}
		}

		echo wp_json_encode(
			array(
				'success'     => $newly_added_cart_item_key,
				'message'     => $message,
				'url'         => $cart_url,
				'form_fields' => $recap,

			)
		);

		die();

	}

	public function get_npd_data_image( $product_image_code, $values, $cart_item_key ) {
		if ( $values['variation_id'] ) {
			$product_id = $values['variation_id'];
		} else {
			$product_id = $values['product_id'];
		}
		$npd_product = new NPD_Product($product_id);
		if ( isset( $values['npd_preview_img'] ) && ! empty( $values['npd_preview_img'] ) ) {
			$product_image_url = $values['npd_preview_img'];
			$product_image_code = "<img class='npd-cartitem-img' src='" . esc_url( $product_image_url ) . "'>";
			return $product_image_code;
		} else {
			return $npd_product->product->get_image();
		}
	}
	function get_product_name( $product_name,$cart_item, $cart_item_key) {
		$npd_product = new NPD_Product( $cart_item['product_id'] );
		return $npd_product->product->get_name();
    }

	public function get_npd_data( $values, $cart_item_key ) {
		global $woocommerce, $vpc_settings;
		$thumbnail_code="";
		if ( ! isset( $values['npd_preview_img'] ) ) {
			return $thumbnail_code;
		}

		if ( $values['variation_id'] ) {
			$product_id = $values['variation_id'];
		} else {
			$product_id = $values['product_id'];
		}

		$npd_product = new NPD_Product( $product_id );

		$allowed_html = array(
			'div'  => array( 'class' => array() ),
			'span' => array(
				'class' => array(),
				'style' => array(),
			),
			'img'  => array(
				'src'    => array(),
				'width'  => array(),
				'height' => array(),
			),
			'a'    => array( 'href' => array() ),
			'p'    => array(),
		);
		if ( isset( $values['npd_generated_data'] ) && ! empty( $values['npd_generated_data'] ) ) {
			$details='';	
			foreach ( $values['npd_generated_data'] as $key => $value ) {
				$name     = explode( '_', $key );
				$name     = str_replace( 'npd-', '', $name );
				$details .= '<div class="npd-cart-item-data-wrap">';
				if ( $key == 'npd-scene' ) {
					$details .= '<div class="variation-' . ucfirst( end( $name ) ) . '">' . ucfirst( end( $name ) ) . ':</div>
                    <div class="variation-' . ucfirst( end( $name ) ) . '"><p><a href= "' . ucfirst( $value ) . '"> <img src="' . ucfirst( $value ) . '" height="50" width="50"></a></p></div>';
				} elseif ( $key == 'npd-neon-color' ) {
					 $details .= '<div class="variation-' . ucfirst( end( $name ) ) . '">' . ucfirst( end( $name ) ) . ':</div>
                    <div class="variation-' . ucfirst( end( $name ) ) . '"><span class="cart-item-data-color" style="background-color:' . ucfirst( $value ) . '"></span><span>' . ucfirst( $value ) . '</span></div>';
				} elseif ( $key == 'npd_additional_price' ) {

				} else {
					$details .= '<div class="variation-' . ucfirst( end( $name ) ) . '">' . ucfirst( end( $name ) ) . ':</div>
                    <div class="variation-' . ucfirst( end( $name ) ) . '"><p>' . ucfirst( $value ) . '</p></div>';
				}
				$details .= '</div>';
			}
			$thumbnail_code .= '<div class="npd-cart-item-data-container">' . wp_kses( $details, $allowed_html ) . '</div>';
		}

		$config_url   = '';
		$cart_content = $woocommerce->cart->cart_contents;
		$item_content = $cart_content[ $cart_item_key ];

		$npd_config_metas = get_post_meta( $product_id, 'npd-config', true );

		$modal_id = uniqid() . "$product_id" . "_$cart_item_key";

		$thumbnail_code .= '<span><a class="o-modal-trigger button" data-toggle="o-modal" data-target="#' . esc_attr( $modal_id ) . '">' . __( 'Preview', 'neon-product-designer' ) . '</a></span>';
		$thumbnail_code .= '<div class="omodal fade o-modal " id="' . esc_attr( $modal_id ) . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <div class="omodal-dialog">
                      <div class="omodal-content">
                        <div class="omodal-header">
                          <button type="button" class="close" data-dismiss="omodal" aria-hidden="true">&times;</button>
                          <h4 class="omodal-title" id="myModalLabel' . esc_attr( $modal_id ) . '">Preview</h4>
                        </div>
                        <div class="omodal-body">';
		$thumbnail_code .= '<img src="' . esc_url( $values['npd_preview_img'] ) . '">';
		$thumbnail_code .= '
                        </div>
                      </div>
                    </div>
                  </div>';

		$thumbnail_code = apply_filters( 'npd_get_config_data', $thumbnail_code, $values, $cart_item_key );
		echo $thumbnail_code;
	}

	private function add_designs_to_cart( $recap, $preview_img ) {
		global $woocommerce;
		$newly_added_cart_item_key = false;
		$variations_str            = stripslashes_deep( filter_input( INPUT_POST, 'variations' ) );
		$variations                = json_decode( $variations_str, true );
		foreach ( $variations as $variation_name => $variation_info ) {

			$variation_id = $variation_info['id'];
			$quantity     = $variation_info['qty'];
			if ( $quantity <= 0 ) {
				continue;
			}

			$product   = wc_get_product( $variation_id );
			$variation = array();
			if ( $product->get_type() == 'simple' ) {
				$product_id = $variation_id;
			} else {
				$variation  = $product->get_variation_attributes();
				$product_id = $product->get_parent_id();
			}

			$variations = array();

			if ( isset( $_SESSION['npd_key'] ) ) {
				$session_key = sanitize_key( $_SESSION['npd_key'] );
				$variations  = get_transient( $session_key );
			}

			foreach ( $variation as $key => $value ) {
				if ( isset( $variations[ $key ] ) && '' === $value ) {
					$variation[ $key ] = $variations[ $key ];
				}
			}

			if ( isset( $_SESSION['combinaison'][ $variation_name ] ) ) {
				$session_combinaison = sanitize_text_field( $_SESSION['combinaison'][ $variation_name ] );
				$variation           = $session_combinaison;
			}

			$product = wc_get_product( $product_id );

			if ( 'variable' !== $product->get_type() ) {
				$variation_id = 0;
				$variation    = '';
			}

			

			$newly_added_cart_item_key = $woocommerce->cart->add_to_cart(
				$product_id,
				$quantity,
				$variation_id,
				$variation,
				array(
					'npd_generated_data' => $recap,
					'npd_preview_img'    => $preview_img,
				)
			);

			if ( method_exists( $woocommerce->cart, 'maybe_set_cart_cookies' ) ) {
				$woocommerce->cart->maybe_set_cart_cookies();
			}
		}

		return $newly_added_cart_item_key;
	}

	public function get_order_custom_admin_data( $item_id, $item, $_product ) {
		$wpc_metas   = get_post_meta( $item['product_id'], 'npd-metas', true );
		$order_data  = wc_get_order_item_meta( $item_id, 'npd_generated_data' );
		$preview_img = wc_get_order_item_meta( $item_id, 'npd_preview_img' );

		$allowed_html = array(
			'div'  => array( 'class' => array() ),
			'span' => array(
				'class' => array(),
				'style' => array(),
			),
			'img'  => array(
				'src'    => array(),
				'width'  => array(),
				'height' => array(),
			),
			'a'    => array( 'href' => array() ),
			'p'    => array(),
		);
		if ( isset( $order_data ) && ! empty( $order_data ) ) {
			$details = '';

			foreach ( $order_data as $key => $value ) {
				$name     = explode( '_', $key );
				$name     = str_replace( 'npd-', '', $name );
				$details .= '<div class="npd-cart-item-data-wrap">';
				if ( $key == 'npd-scene' ) {
					$details .= '<div class="variation-' . ucfirst( end( $name ) ) . '">' . ucfirst( end( $name ) ) . ':</div>
                    <div class="variation-' . ucfirst( end( $name ) ) . '"><p><a href= "' . ucfirst( $value ) . '"> <img src="' . ucfirst( $value ) . '" height="50" width="50"></a></p></div>';
				} elseif ( $key == 'npd-neon-color' ) {
					 $details .= '<div class="variation-' . ucfirst( end( $name ) ) . '">' . ucfirst( end( $name ) ) . ':</div>
                    <div class="variation-' . ucfirst( end( $name ) ) . '"><span class="cart-item-data-color" style="background-color:' . ucfirst( $value ) . '"></span><span>' . ucfirst( $value ) . '</span></div>';
				} elseif ( $key == 'npd_additional_price' ) {

				} else {
					$details .= '<div class="variation-' . ucfirst( end( $name ) ) . '">' . ucfirst( end( $name ) ) . ':</div>
                    <div class="variation-' . ucfirst( end( $name ) ) . '"><p>' . ucfirst( $value ) . '</p></div>';
				}
				$details .= '</div>';
			}

			$modal_id = uniqid() . $item_id;

			if ( isset( $order_data['npd_preview_img'] ) ) {
				$details .= '<button class="button alt npd_admin_download_image" href="' . $order_data['npd_preview_img'] . '">' . __( 'Download', 'neon-product-designer' ) . '</button>';

				$details .= '<span><a class="o-modal-trigger button" data-toggle="o-modal" data-target="#' . esc_attr( $modal_id ) . '">' . __( 'Preview', 'neon-product-designer' ) . '</a></span>';
				$details .= '<div class="omodal fade o-modal wpc_part" id="' . esc_attr( $modal_id ) . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="omodal-dialog">
							<div class="omodal-content">
								<div class="omodal-header">
								<button type="button" class="close" data-dismiss="omodal" aria-hidden="true">&times;</button>
								<h4 class="omodal-title" id="myModalLabel' . esc_attr( $modal_id ) . '">Preview</h4>
								</div>
								<div class="omodal-body">';
				$details .= '<img src="' . esc_url( $order_data['npd_preview_img'] ) . '">';
				$details .= '
								</div>
							</div>
							</div>
						</div>';
			}

			echo wp_kses( $details, $allowed_html );
		}
	}

	// Checkout
	public function save_cart_item_custom_meta_as_order_item_meta( $item, $cart_item_key, $values, $order ) {
		$meta_key = 'npd_generated_data';
		if ( isset( $values[ $meta_key ] ) ) {
			if ( isset( $values['npd_preview_img'] ) ) {
				$values[ $meta_key ]['npd_preview_img'] = $values['npd_preview_img'];
			}
			$item->update_meta_data( $meta_key, $values[ $meta_key ] );
		}
	}



	public function get_cart_item_price( $cart ) {
		if ( session_status() != 2 ) {
			session_start();
		}
		if ( $_SESSION['npd_calculated_totals'] == true ) {
			return;
		}
		foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {
			if ( $cart_item['variation_id'] ) {
				$variation_id = $cart_item['variation_id'];
			} else {
				$variation_id = $cart_item['product_id'];
			}

			if ( function_exists( 'icl_object_id' ) ) {
				// WPML runs the hook twice which doubles the price in cart.
				// We just need to make sure the plugin uses the original price so it won't matter
				$variation  = wc_get_product( $variation_id );
				$item_price = $variation->get_price();
			} else {
				$item_price = $cart_item['data']->get_price();
			}

			if ( isset( $cart_item['npd_generated_data'] ) ) {
				$data             = $cart_item['npd_generated_data'];
				$npd_product      = new NPD_Product( $variation_id );
				$npd_metas        = $npd_product->settings;
				$form_fields_data = array();
				$total_price_form = 0;

				$a_price = 0;
				if ( isset( $cart_item['npd_generated_data']['npd_additional_price'] ) ) {
					$a_price += $cart_item['npd_generated_data']['npd_additional_price'];
				}
				$item_price += $a_price + $total_price_form;
			}

			if ( isset( $cart_item['npd_design_pricing_options'] ) && ! empty( $cart_item['npd_design_pricing_options'] ) ) {
				$a_price    = $this->get_design_options_prices( $cart_item['npd_design_pricing_options'] );
				$item_price = apply_filters( 'npd_cart_item_price_with_options', ( $item_price + $a_price ), $variation_id, $item_price, $a_price );
			}

			// Ajout d'un filtre pour mettre à jour le prix total de l'element dans le panier.
			$item_price = apply_filters( 'npd_cart_item_price', $item_price );

			$cart_item['data']->set_price( $item_price );
		}
		$_SESSION['npd_calculated_totals'] = true;
		if ( session_status() == 2 ) {
			session_write_close();
		}
	}



	private function get_design_options_prices( $json_wpc_design_options ) {
		$wpc_design_options_prices = 0;
		if ( ! empty( $json_wpc_design_options ) ) {
			$json           = $json_wpc_design_options;
			$json           = str_replace( "\n", '|n', $json );
			$unslashed_json = stripslashes_deep( $json );
			$decoded_json   = json_decode( $unslashed_json );
			if ( is_object( $decoded_json ) && property_exists( $decoded_json, 'opt_price' ) ) {
				$wpc_design_options_prices = $decoded_json->opt_price;
			}
		}
		return $wpc_design_options_prices;
	}


	/**
	 * Save design image
	 *
	 * @param string $image
	 * @param string $file_name
	 * @return void
	 */
	private function save_canvas_image( $image, $file_name ) {
		$upload_dirs = NPD_IMAGE_PATH;
		wp_mkdir_p( $upload_dirs );
		$upload_dir = $upload_dirs . DIRECTORY_SEPARATOR;
		$img        = str_replace( 'data:image/png;base64,', '', $image );
		$img        = str_replace( ' ', '+', $img );
		$data       = base64_decode( $img );
		$file       = $upload_dir . $file_name . '.png';
		$success    = file_put_contents( $file, $data );
		return $success;
	}

}
