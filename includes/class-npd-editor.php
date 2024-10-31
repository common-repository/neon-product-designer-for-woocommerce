<?php
/**
 * Description of class-npd-editor
 *
 * @author Vertim Coders <freelance@vertimcoders.com>
 */
class NPD_Editor {

	public $item_id;
	public $root_item_id;
	public $npd_product;

	public function __construct( $item_id ) {
		if ( $item_id ) {
			$this->item_id      = $item_id;
			$this->npd_product  = new NPD_Product( $item_id );
			$this->root_item_id = $this->npd_product->root_product_id;
		}
	}

	public function get_editor() {
		global $npd_settings, $wp_query;

		ob_start();
		$product = wc_get_product( $this->item_id );
		// if ( !get_option( "npd-license-key" ) ) {
			// _e( '<strong>Error: Your licence is not valid</strong>', 'neon product designer' ) . '<br>';
			// return;
		// }
		if ( ! $product ) {
			esc_html_e( 'Error: Invalid product: ', 'neon-product-designer' );
			echo esc_html_e( $this->item_id ) . '<br>';
			esc_html_e( '1- Is your customization page defined as homepage?', 'neon-product-designer' );
			echo '<br>';
			esc_html_e( '2- Is your customization page defined as one of woocommerce pages?', 'neon-product-designer' );
			echo '<br>';
			esc_html_e( '3- Does the product you are trying to customize exists and is published in your shop?', 'neon-product-designer' );
			echo '<br>';
			esc_html_e( '4- Are you accessing this page from one of the product designer buttons?', 'neon-product-designer' ) . '<br>';
			return;
		}
		// if ( ! $this->npd_product->has_part() ) {
		// _e( 'Error: No active part defined for this product. A customizable product should have at least one part defined.', 'neon-product-designer' );
		// return;
		// }
		$npc_metas  = $this->npd_product->settings;
		$ui_options = Kali_Admin_Tools::get_parse_value( $npd_settings, 'npd-ui-options', array() );
		$skin       = apply_filters( 'npd_loaded_skin', Kali_Admin_Tools::get_parse_value( $ui_options, 'skin', 'NPD_Skin_Default' ) );

		npd_init_canvas_vars( $npc_metas, $product, $this, $skin );
		npd_init_editor_vars( $npc_metas );
		$editor = new $skin( $this, $npc_metas );

		$raw_output = $editor->display();
		$custom_tags  = apply_filters(
			'npd_custom_tags',
			array(
				'data-id',
				'data-title',
				'data-name',
				'data-size',
				'data-any-color',
				'data-any-size',
				'data-id-color',
				'data-color',
				'data-color-behaviour',
				'data-own-id',
				'data-index',
				'data-url',
				'data-src',
				'data-placement',
				'data-tooltip-title',
				'data-ov',
				'data-vcvni',
				'data-opacity',
				'data-update-id',
				'data-img-name',
				'data-group-name',
				'data-groupid',
				'data-price',
				'data-original',
				'data-default-value',
				'data-unit',
				'data-target',
				'data-toggle',
				'tabindex',
				'variation_name',
				'tab-index',
				'aria-hidden',
				'aria-labelledby',
			)
		);

		$allowed_tags = npd_allowed_global_tags( $custom_tags ); 
		echo wp_kses($raw_output,$allowed_tags);

		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

}
