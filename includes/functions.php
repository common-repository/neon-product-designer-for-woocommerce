<?php

function npd_remove_transients() {
	global $wpdb;
	$sql = "delete from $wpdb->options where option_name like '%_vertim_npd_%transient_%'";
	$wpdb->query( $sql );
}

function npd_get_custom_products() {
	global $wpdb;
	$search       = "(

		pm.meta_value like '%config-id\";s:1%'
		or pm.meta_value like '%config-id\";s:2%'
		or pm.meta_value like '%config-id\";s:3%'
		or pm.meta_value like '%config-id\";s:4%'
		or pm.meta_value like '%config-id\";s:5%'
		or pm.meta_value like '%config-id\";s:6%'
		or pm.meta_value like '%config-id\";s:7%'
		or pm.meta_value like '%config-id\";s:8%'
		or pm.meta_value like '%config-id\";s:9%'
		or pm.meta_value like '%config-id\";i:%'
		)";
		$products = $wpdb->get_results(
			"
                           SELECT p.id
                           FROM $wpdb->posts p
                           JOIN $wpdb->postmeta pm on pm.post_id = p.id 
                           WHERE p.post_type = 'product'
                           AND pm.meta_key = 'npd-metas'
                           AND $search
                           "
		);
	return $products;
}

function npd_woocommerce_version_check( $version = '2.5' ) {
	if ( class_exists( 'WooCommerce' ) ) {
		global $woocommerce;
		if ( version_compare( $woocommerce->version, $version, '>=' ) ) {
			return true;
		}
	}
	return false;
}
// Canvas variable initialisation
function npd_init_canvas_vars( $npc_metas, $product, $editor, $skin ) {
	global $npd_settings, $wp_query, $wpdb;
	$npd_query_vars  = array();
	$general_options = $npd_settings['npd-general-options'];
	
	// $text_options    = $npd_settings[ 'npd-texts-options' ];
	$product_price = $product->get_price();
	$use_retina_mode = 'yes';

	$output_settings = Kali_Admin_Tools::get_parse_value( $npc_metas, 'output-settings', array() );

	$designer_unit = Kali_Admin_Tools::get_parse_value( $npc_metas, 'unit', 'cm' );
	$font_behaviour = Kali_Admin_Tools::get_parse_value( $npc_metas, 'font-bahaviour', 'select' );
	$default_color = Kali_Admin_Tools::get_parse_value( $npc_metas, 'font-bahaviour', '#ffffff' );
	$default_color_behaviour = Kali_Admin_Tools::get_parse_value( $npc_metas, 'data-color-behaviour', 'light-color' );
	
	if ( isset( $general_options['npd-redirect-after-cart'] ) && ! empty( $general_options['npd-redirect-after-cart'] ) ) {
		$redirect_after = $general_options['npd-redirect-after-cart'];
	} else {
		$redirect_after = 0;
	}
	
	if ( isset( $general_options['follow-scroll'] ) && ! empty( $general_options['follow-scroll'] ) ) {
		$follow_scroll = $general_options['follow-scroll'];
	} else {
		$follow_scroll = 0;
	}

	$disable_shortcut = Kali_Admin_Tools::get_parse_value( $general_options, 'disable-keyboard-shortcuts', 0 );


	if ( isset( $wp_query->query_vars['edit'] ) ) {
		$variation_id           = $wp_query->query_vars['product_id'];
		$cart_item_key          = $wp_query->query_vars['edit'];
		$npd_query_vars['edit'] = $cart_item_key;
		global $woocommerce;
		$cart = $woocommerce->cart->get_cart();
		$data = $cart[ $cart_item_key ]['npd_generated_data'];
		// Useful when editing cart item
		if ( $data ) {
			$data = stripslashes_deep( $data );
		}
	} elseif ( isset( $wp_query->query_vars['design_index'] ) ) {
		global $current_user;
		$design_index                   = $wp_query->query_vars['design_index'];
		$npd_query_vars['design_index'] = $design_index;
		$user_designs                   = get_user_meta( $current_user->ID, 'npd_saved_designs' );
		$data                           = $user_designs[ $design_index ][2];
	} elseif ( isset( $wp_query->query_vars['vcid'] ) ) {
		$order_item_id          = $wp_query->query_vars['vcid'];
		$npd_query_vars['vcid'] = $order_item_id;
		$sql                    = 'select meta_value FROM ' . $wpdb->prefix . "woocommerce_order_itemmeta where order_item_id=$order_item_id and meta_key='npd_data'";
		// echo $sql;
		$npc_data = $wpdb->get_var( $sql );
		$data     = unserialize( $npc_data );
	}

	// Previous data to load overwrites everything
	if ( isset( $_SESSION['npd-data-to-load'] ) && ! empty( $_SESSION['npd-data-to-load'] ) ) {
		$previous_design_str = sanitize_text_field(stripslashes_deep( $_SESSION['npd-data-to-load'] ));
		$previous_design     = json_decode( $previous_design_str );
		if ( is_object( $previous_design ) ) {
			$previous_design = (array) $previous_design;
		}
		// We make sure the structure of the data matches the one loaded by the plugin
		foreach ( $previous_design as $part_key => $part_data ) {
			$previous_design[ $part_key ] = array( 'json' => $part_data );
		}
		$data = $previous_design;
	}

	$available_variations = array();
	if ( $product->get_type() === 'variable' ) {
		$available_variations = $product->get_available_variations();
	}

	$price_format  = npd_get_price_format();
	$npd_url       = NPD_URL;
	$editor_params = apply_filters(
		'npd_editor_params',
		array(
			'skin'                   => $skin,
			'npd_url'                => $npd_url,
			'global_variation_id'    => $editor->item_id,
			'redirect_after'         => $redirect_after,
			'follow_scroll'          => $follow_scroll,
			'font_behaviour'         => $font_behaviour,
			'default_color'          => $default_color,
			'default_color_behaviour' => $default_color_behaviour,
			'disable_shortcuts'      => $disable_shortcut,
			'designer_unit'          => $designer_unit,
			'query_vars'             => $npd_query_vars,
			'thousand_sep'           => wc_get_price_thousand_separator(),
			'decimal_sep'            => wc_get_price_decimal_separator(),
			'nb_decimals'            => wc_get_price_decimals(),
			'currency'               => get_woocommerce_currency_symbol(),
			'price_format'           => $price_format,
			'variations'             => $available_variations,
			'lazy_placeholder'       => NPD_URL . '/public/images/rolling.gif',
			'enable_retina'          => $use_retina_mode,
		),
		$npc_metas,
		$product,
		$editor
	);
	?>
	<script>
		var npd;
		npd =<?php echo wp_json_encode( $editor_params ); ?>;
	</script>
	<?php
}

function npd_init_editor_vars( $npc_metas ) {
	if ( isset( $npc_metas['princing_options'] ) ) {
		$rules = $npc_metas['princing_options'];
	} else {
		$rules = array();
	}

	if ( isset( $npc_metas['sizes-options'] ) && isset( $npc_metas['sizes-options']['unit'] ) ) {
		$unit = $npc_metas['sizes-options']['unit'];
	} else {
		$unit = 'cm';
	}

	if ( isset( $_GET['edit'] ) && ! empty( $_GET['edit'] ) ) {
		$edit_key         = filter_input( INPUT_GET, 'edit' );
		$edit_global_data = WC()->cart->get_cart_item( $edit_key );

		if ( isset( $edit_global_data['quantity'] ) ) {
			$qty = $edit_global_data['quantity'];
		} else {
			$qty = 1;
		}

		if ( isset( $edit_global_data['npd_generated_data'] ) ) {
			$edit_data = $edit_global_data['npd_generated_data'];
		} else {
			$edit_data = array();
		}
	} else {
		$edit_data = array();
		$edit_key  = '';
		$qty       = 1;
	}

	?>
	<script>
	  var npd_design_data = {
		rules_options:'<?php echo wp_json_encode( $rules ); ?>',
		unit:'<?php echo esc_attr( $unit ); ?>',
		edit:{
		  key:'<?php echo esc_attr( $edit_key ); ?>',
		  data:'<?php echo wp_json_encode( $edit_data ); ?>',
		  qty:<?php echo esc_attr( $qty ); ?>
		}
	  }
	</script>
	<?php
}

// Get price format
function npd_get_price_format() {
	$currency_pos = get_option( 'woocommerce_currency_pos' );
	$format       = '%s%v';

	switch ( $currency_pos ) {
		case 'left':
			$format = '%s%v';
			break;
		case 'right':
			$format = '%v%s';
			break;
		case 'left_space':
			$format = '%s %v';
			break;
		case 'right_space':
			$format = '%v %s';
			break;
		default:
			$format = '%s%v';
			break;
	}
	return $format;
}

function npd_register_fonts() {
	$fonts = get_option( 'npd-fonts' );
	if ( empty( $fonts ) ) {
		$fonts = npd_get_default_fonts();
	}

	foreach ( $fonts as $font ) {
		$font_label = $font[0];
		$font_url   = str_replace( 'http://', '//', $font[1] );
		if ( $font_url ) {
			$handler = sanitize_title( $font_label ) . '-css';
			wp_register_style( $handler, $font_url, array(), false, 'all' );
			wp_enqueue_style( $handler );
		} elseif ( ! empty( $font[2] ) && is_array( $font[2] ) ) {
			npd_get_ttf_font_style( $font );
		}
	}
}

function npd_get_ttf_font_style( $font ) {
	$font_label     = $font[0];
	$font_ttf_files = $font[2];
	foreach ( $font_ttf_files as $font_file ) {
		$font_styles   = $font_file['styles'];
		$font_file_url = wp_get_attachment_url( $font_file['file_id'] );
		if ( ! $font_file_url ) {
			continue;
		}
		foreach ( $font_styles as $font_style ) {
			if ( $font_style === '' ) {
				$font_style_css = '';
			} elseif ( $font_style === 'I' ) {
				$font_style_css = 'font-style:italic;';
			} elseif ( $font_style === 'B' ) {
				$font_style_css = 'font-weight:bold;';
			}
			?>
		  <style>
			  @font-face {
			font-family: "<?php echo esc_html($font_label); ?>";
			src: url('<?php echo esc_url($font_file_url); ?>') format('truetype');
			<?php echo esc_html($font_style_css); ?>
			  }
		  </style>
			<?php
		}
	}
}

function npd_get_default_fonts() {
	$default = array(
		array( 'Shadows Into Light', 'http://fonts.googleapis.com/css?family=Shadows+Into+Light' ),
		array( 'Droid Sans', 'http://fonts.googleapis.com/css?family=Droid+Sans:400,700' ),
		array( 'Abril Fatface', 'http://fonts.googleapis.com/css?family=Abril+Fatface' ),
		array( 'Arvo', 'http://fonts.googleapis.com/css?family=Arvo:400,700,400italic,700italic' ),
		array( 'Lato', 'http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic' ),
		array( 'Just Another Hand', 'http://fonts.googleapis.com/css?family=Just+Another+Hand' ),
	);

	return $default;
}

function NPD_register_upload_scripts() {
	wp_register_script( 'npd-jquery-form-js', NPD_URL . 'public/js/jquery.form.min.js' );
	wp_enqueue_script( 'npd-jquery-form-js', array( 'jquery' ), '', false );
}

/**
 * Builds a select dropdpown
 *
 * @param type $name Name
 * @param type $id ID
 * @param type $class Class
 * @param type $options Options
 * @param type $selected Selected value
 * @param type $multiple Can select multiple values
 * @return string HTML code
 */
function npd_get_html_select( $name, $id, $class, $options, $selected = '', $multiple = false ) {
	ob_start();
	?>
	<select name="<?php echo esc_attr( $name ); ?>" <?php echo esc_attr( $id ) ? "id=\"$id\"" : ''; ?> <?php echo esc_attr( $class ) ? "class=\"$class\"" : ''; ?> <?php echo esc_attr( $multiple ) ? 'multiple' : ''; ?> >
	<?php
	if ( is_array( $options ) && ! empty( $options ) ) {
		foreach ( $options as $name => $label ) {
			if ( 'None' === $label ) {
					$label = __( $label, 'neon-product-designer' );
			}
			if ( ! $multiple && $name === $selected ) {
				?>
		<option value="<?php echo esc_attr( $name ); ?>"  selected="selected" > <?php echo esc_attr( $label ); ?></option> 
				<?php
			} elseif ( $multiple && in_array( $name, $selected ) ) {
				?>
		<option value="<?php echo esc_attr( $name ); ?>"  selected="selected" > <?php echo esc_attr( $label ); ?></option> 
				<?php
			} else {
				?>
		<option value="<?php echo esc_attr( $name ); ?>"> <?php echo esc_attr( $label ); ?></option> 
				<?php
			}
		}
	}
	?>
	</select>
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function npd_get_editor_size_options( $npc_metas ) {
	// var_dump($npc_metas);

	$default_editor_unit = $npc_metas['sizes-options']['unit'];

	if ( isset( $npc_metas['sizes-options']['depth'] ) && ! empty( $npc_metas['sizes-options']['depth'] ) ) {
		$default_editor_weight = $npc_metas['sizes-options']['depth'];
	} else {
		$default_editor_weight = '';
	}

	if ( isset( $npc_metas['sizes-options']['sizes'] ) && ! empty( $npc_metas['sizes-options']['sizes'] ) ) {

		foreach ( $npc_metas['sizes-options']['sizes'] as $editor_size ) {
			if ( isset( $editor_size['default'] ) && 1 === $editor_size['default'] ) {
				$default_width  = $editor_size['width'];
				$default_height = $editor_size['height'];
			}
		}
		if ( isset( $default_width ) && ! empty( $default_width ) && isset( $default_height ) && ! empty( $default_height ) ) {
			$default_editor_w = $default_width;
			$default_editor_h = $default_height;
		} else {
			$default_editor_w = $npc_metas['sizes-options']['sizes'][0]['width'];
			$default_editor_h = $npc_metas['sizes-options']['sizes'][0]['height'];
		}

		return array( $default_editor_w, $default_editor_h, $default_editor_weight, $default_editor_unit );

	} else {
		// recup available space size

		?>
	<script type="text/javascript">
		jQuery(document).ready(function($){
			console.log(npd.designer_unit);  
			var mm = "mm";
			var available_w = Number($('.npd-editor-preview').width());
			var available_h = Number($('.npd-editor-preview').height());
			 var size_label = available_w  + ' X ' + available_h + ' ' + '' + <?php echo esc_attr( $default_editor_unit ); ?>;
			var available_we = <?php echo esc_attr( $default_editor_weight ); ?>;
		  //setTimeout(function(){  
			
			console.log(available_w);
			
			//var editor_unit = <?php echo esc_attr( $default_editor_unit ); ?>;
		   
			$("#npd-size-select").attr('data-width', available_w);
			$("#npd-size-select").attr('data-height', available_h);
			$("#npd-size-select").attr('value', size_label);

			$("#npd-editor-size-w-wrap .size-w").html(available_w + ''+<?php echo esc_attr( $default_editor_unit ); ?>);
			$("#npd-editor-size-h-wrap .size-h").html(available_h + ''+ <?php echo esc_attr( $default_editor_unit ); ?>);
			console.log(available_we);
			if(typeof available_we != 'undefined'  &&  available_we != '0'){
			  $("#npd-editor-size-we-wrap .size-we").html( available_we + '' + <?php echo esc_attr( $default_editor_unit ); ?>);
			}

		  //}, 3000);
			
			

	});
	</script>
		<?php

	}

}

if (!function_exists('array_key_first')) {
    function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }
}

function npd_allowed_global_tags( $attributes = '' ) {

	$default_attribs = array(
		'id'             => array(),
		'class'          => array(),
		'title'          => array(),
		'style'          => array(),
		'data'           => array(),
		'data-mce-id'    => array(),
		'data-mce-style' => array(),
		'data-mce-bogus' => array(),
		'data-color' => array(),
		'data-color-behaviour' => array(),
		'data-value'          => array(),

	);

	if ( is_array( $attributes ) && ! empty( $attributes ) ) {
		foreach ( $attributes as $value ) {
			$default_attribs[ $value ] = array();
		}
	}
	$allowed_tags = array(
		'div'        => $default_attribs,
		'img'        => array_merge(
			$default_attribs,
			array(
				'src'    => array(),
				'alt'    => array(),
				'title'  => array(),
				'height' => array(),
			)
		),
		'span'       => $default_attribs,
		'canvas'     => $default_attribs,
		'textarea'   => $default_attribs,
		'script'     => array(
			'type' => array(),
			'src'  => array(),
		),
		'style'     => $default_attribs,
		'p'          => $default_attribs,
		'label' => $default_attribs,
		'a'          => array_merge(
			$default_attribs,
			array(
				'href'   => array(),
				'target' => array( '_blank', '_top' ),
			)
		),
		'input'      => array_merge(
			$default_attribs,
			array(
				'type'        => array(),
				'id'          => array(),
				'step'        => array(),
				'min'         => array(),
				'max'         => array(),
				'name'        => array(),
				'value'       => array(),
				'checked'     => array(),
				'placeholder' => array(),
				'multiple'    => array(),
				'accept'      => array(),
			)
		),

		'select'     => array_merge(
			$default_attribs,
			array(
				'value'   => array(),
				'checked' => array(),
			)
		),

		'option'     => array_merge(
			$default_attribs,
			array(
				'value'   => array(),
				'checked' => array(),
			)
		),

		'optgroup'   => array_merge(
			$default_attribs,
			array(
				'value'   => array(),
				'checked' => array(),
			)
		),

		'u'          => $default_attribs,
		'i'          => $default_attribs,
		'q'          => $default_attribs,
		'b'          => $default_attribs,
		'ul'         => $default_attribs,
		'ol'         => $default_attribs,
		'li'         => $default_attribs,
		'br'         => $default_attribs,
		'hr'         => $default_attribs,
		'strong'     => $default_attribs,
		'blockquote' => $default_attribs,
		'del'        => $default_attribs,
		'strike'     => $default_attribs,
		'em'         => $default_attribs,
		'code'       => $default_attribs,
		'button'     => $default_attribs,
		'tr'         => $default_attribs,
		'td'         => $default_attribs,
	);
	return $allowed_tags;
}


?>
