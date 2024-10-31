<?php
/**
 * Scenes code
 *
 * @link       https://vertimcoders.com
 * @since      3.0
 *
 * @package    Npd
 * @subpackage Npd/includes
 */

/**
 * Scenes code
 *
 * This class defines all code necessary for scenes
 *
 * @since      3.0
 * @package    Npd
 * @subpackage Npd/includes
 * @author     Vertim Coders <freelance@vertimcoders.com>
 */
class NPD_Scene {

	function register_cpt_scenes() {

		$labels = array(
			'name' => __( 'Scenes', 'npd-scenes' ),
			'singular_name' => __( 'Scenes', 'npd-scenes' ),
			'add_new' => __( 'New scenes group', 'npd-scenes' ),
			'add_new_item' => __( 'New scenes group', 'npd-scenes' ),
			'edit_item' => __( 'Edit scenes group', 'npd-scenes' ),
			'new_item' => __( 'New scenes group', 'npd-scenes' ),
			'view_item' => __( 'View group', 'npd-scenes' ),
			'not_found' => __( 'No scenes group found', 'npd-scenes' ),
			'not_found_in_trash' => __( 'No scenes group in the trash', 'npd-scenes' ),
			'menu_name' => __( 'scenes', 'npd-scenes' ),
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => false,
			'description' => 'Scenes for the neon product designer',
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

		register_post_type( 'npd-scenes', $args );
	}

	function get_scenes_metabox() {

		$screens = array( 'npd-scenes' );

		foreach ( $screens as $screen ) {

			add_meta_box(
				'npd-scenes-box',
				__( 'Scenes', 'neon-product-designer' ),
				array( $this, 'get_scenes_page' ),
				$screen
			);
		}
	}



	public function get_scenes_page() {
		wp_enqueue_media();
		?>
		<div class='block-form'>
			<?php
			$begin = array(
				'type' => 'sectionbegin',
				'id' => 'scenes-container',
			);

			$scene_image = array(
				'title' => __( 'Image', 'neon-product-designer' ),
				'name' => 'id',
				'type' => 'image',
				'set' => 'Set',
				'remove' => 'Remove',
				'desc' => __( 'Component icon', 'neon-product-designer' ),
			);

			$label = array(
				'title' => __( 'Name', 'neon-product-designer' ),
				'name' => 'label',
				'type' => 'text',
				'desc' => __( 'Scene name', 'neon-product-designer' ),
			);
			$default_scene = array(
				'title'      => __( 'Default', 'neon-product-designer' ),
				'desc'       => __( 'Select default scene', 'neon-product-designer' ),
				'name'       => 'default',
				'type'       => 'radio',
				'class'      => 'npd-default-radio',
				'options'    => array( 1 => '' ),
				'tip'        => 'yes',
			);

			$scenes = array(
				'title' => __( 'Components', 'neon-product-designer' ),
				'name' => 'npd-scenes-data',
				'type' => 'repeatable-fields',
				'fields' => array( $scene_image, $label, $default_scene ),
				'desc' => __( 'Scenes', 'neon-product-designer' ),
				'ignore_desc_col' => true,
				'class' => 'striped',
				'add_btn_label' => __( 'Add scene', 'neon-product-designer' ),
			);

			$end = array( 'type' => 'sectionend' );
			$settings = apply_filters(
				'npd_scenes_settings',
				array(
					$begin,
					$scenes,
					$end,
				)
			);
			
			echo wp_kses(
				Kali_Admin_Tools::get_fields( $settings ),
				Kali_Admin_Tools::get_allowed_tags()
			);

			global $kali_tmp_rows;
			?>
			 <input type="hidden" name="npd_scenes_nonce" value="<?php echo esc_attr( wp_create_nonce( 'npd_scenes_nonce' ) ); ?>"/>
		</div>
		<script>
			var kali_rows_tpl =<?php echo json_encode( $kali_tmp_rows ); ?>;
		</script>
		<?php
	}

	function save_scenes( $post_id ) {

		if ( isset( $_POST['npd_scenes_nonce'] ) ) {
			if ( wp_verify_nonce( wp_unslash( sanitize_key( $_POST['npd_scenes_nonce'] ) ), 'npd_scenes_nonce' ) ) {
				if ( isset( $_POST['npd-scenes-data'] ) ) {
					$meta_value = map_deep( $_POST['npd-scenes-data'], 'sanitize_text_field' );
					update_post_meta( $post_id, 'npd-scenes-data', $meta_value );
				}
			}
		}
	}

}
