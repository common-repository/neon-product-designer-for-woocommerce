<?php
/**
 * Color palette class.
 *
 * @link       freelance@vertimcoders.com
 * @since      1.0.0
 *
 * @package    Npd
 * @subpackage Npd/admin
 */

/**
 * Contains all methods and hooks callbacks related to the color palette post types.
 *
 * @author Vertimcoders
 *
 * @package    Npd
 * @subpackage Npd/admin
 * @author     Vertim Coders <freelance@vertimcoders.com>
 */
class NPD_Color {

	/**
	 * Register the npd-colors-palettes postype.
	 */
	public function register_cpt_colors_palette() {

		$labels = array(
			'name' => __( 'Colors palettes', 'neon-product-designer' ),
			'singular_name' => __( 'Colors palettes', 'neon-product-designer' ),
			'add_new' => __( 'New palette', 'neon-product-designer' ),
			'add_new_item' => __( 'New palette', 'neon-product-designer' ),
			'edit_item' => __( 'Edit palette', 'neon-product-designer' ),
			'new_item' => __( 'New palette', 'neon-product-designer' ),
			'view_item' => __( 'View palette', 'neon-product-designer' ),
			'not_found' => __( 'No palette found', 'neon-product-designer' ),
			'not_found_in_trash' => __( 'No palette in the trash', 'neon-product-designer' ),
			'menu_name' => __( 'Colors palettes', 'neon-product-designer' ),
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => false,
			'description' => 'Colors palettes for the product designer',
			'supports' => array( 'title' ),
			'public' => false,
			'menu_icon' => 'dashicons-images-alt',
			'show_ui' => true,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'has_archive' => false,
			'query_var' => false,
			'can_export' => true,
		);

		register_post_type( 'npd-colors-palette', $args );
	}

	/**
	 * Create the npd-colors-palette metabox.
	 */
	public function get_colors_palette_metabox() {

		$screens = array( 'npd-colors-palette' );

		foreach ( $screens as $screen ) {

			add_meta_box(
				'npd-colors-palette-box',
				__( 'Colors palettes', 'neon-product-designer' ),
				array( $this, 'get_colors_palettes_page' ),
				$screen
			);
		}
	}

	/**
	 * Get colors palette page.
	 */
	public function get_colors_palettes_page() {

		$begin = array(
			'type' => 'sectionbegin',
			'id' => 'colors-palette-container',
		);

		$name = array(
			'title' => __( 'Name', 'neon-product-designer' ),
			'name' => 'name',
			'type' => 'text',
		);

		$code_hex = array(
			'title' => __( 'Hex code', 'neon-product-designer' ),
			'name' => 'code_hex',
			'class' => 'kali-color',
			'id' => 'colors-palette-code-hex',
			'type' => 'text',
			'value' => '#fff',
		);

		$default_color = array(
			'title'      => __( 'Default', 'neon-product-designer' ),
			'desc'       => __( 'Select default color', 'neon-product-designer' ),
			'name'       => 'default',
			'type'       => 'radio',
			'class'      => 'npd-default-radio',
			'options'    => array( 1 => '' ),
			'tip'        => 'yes',
		);

		$colors_palette = array(
			'title' => __( 'Components', 'neon-product-designer' ),
			'name' => 'npd-colors-palette-data',
			'type' => 'repeatable-fields',
			'fields' => array( $name, $code_hex, $default_color ),
			'desc' => __( 'Colors palettes', 'neon-product-designer' ),
			'ignore_desc_col' => true,
			'class' => 'striped',
			'add_btn_label' => __( 'Add color', 'neon-product-designer' ),
		);

		$end = array( 'type' => 'sectionend' );
		$settings = array(
			$begin,
			$colors_palette,
			$end,
		);

		echo wp_kses(
			Kali_Admin_Tools::get_fields( $settings ),
			Kali_Admin_Tools::get_allowed_tags()
		);

		global $kali_tmp_rows;
		?>
		<input type="hidden" name="npd_colors_nonce" value="<?php echo esc_attr( wp_create_nonce( 'npd_colors_nonce' ) ); ?>"/>
		<script>
			var kali_rows_tpl =<?php echo wp_json_encode( $kali_tmp_rows ); ?>;
		</script>
		<?php
	}

	/**
	 * Save the color palette.
	 *
	 * @param int $post_id The post id.
	 */
	public function save_colors_palette( $post_id ) {
		if ( isset( $_POST['npd_colors_nonce'] ) ) {
			if ( wp_verify_nonce( wp_unslash( sanitize_key( $_POST['npd_colors_nonce'] ) ), 'npd_colors_nonce' ) ) {
				if ( isset( $_POST['npd-colors-palette-data'] ) ) {
					$meta_value = map_deep( $_POST['npd-colors-palette-data'], 'sanitize_text_field' );
					update_post_meta( $post_id, 'npd-colors-palette-data', $meta_value );
				}
			}
		}
	}



}
