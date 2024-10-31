<?php

/**
 * Contains all methods and hooks callbacks related to the product configuration
 *
 * @author Vertim Coders
 */
class NPD_Config {
	/**
	 * Register configuration post type
	 *
	 * @var $npd
	 */
	private $npd;
	private $version;

	public function register_cpt_config() {

		$labels = array(
			'name'               => _x( 'Configurations', 'neon-product-designer' ),
			'singular_name'      => _x( 'Configurations', 'neon-product-designer' ),
			'add_new'            => _x( 'New configuration', 'neon-product-designer' ),
			'add_new_item'       => _x( 'New configuration', 'neon-product-designer' ),
			'edit_item'          => _x( 'Edit configuration', 'neon-product-designer' ),
			'new_item'           => _x( 'New configuration', 'neon-product-designer' ),
			'view_item'          => _x( 'View configuration', 'neon-product-designer' ),
			'not_found'          => _x( 'No configuration found', 'neon-product-designer' ),
			'not_found_in_trash' => _x( 'No configuration in the trash', 'neon-product-designer' ),
			'menu_name'          => _x( 'Neon Product Designer', 'neon-product-designer' ),
			'all_items'          => _x( 'Configurations', 'neon-product-designer' ),
		);

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => false,
			'description'         => 'Configurations',
			'supports'            => array( 'title' ),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'has_archive'         => false,
			'query_var'           => false,
			'can_export'          => true,
		);

		register_post_type( 'npd-config', $args );
	}

	/*
	 -----------------------------------------------------------------
	 fonction de gestion des checkbox
	*/

	/**
	 * Save Configuration post type
	 *
	 * @param int $post_id
	 */
	public function save_config( $post_id ) {

		$admin = new Neon_Product_Designer_Admin( $this->npd, $this->version );

		$meta_key = 'npd-metas';

		$checkboxes_map = array(
			'add-note' => array( 'field' ),
		);

		if ( isset( $_POST['npd_config_nonce'] ) ) {
			if ( wp_verify_nonce( wp_unslash( sanitize_key( $_POST['npd_config_nonce'] ) ), 'npd_config_nonce' ) ) {

				if ( isset( $_POST[ $meta_key ] ) ) {

					foreach ( $checkboxes_map as $map_key => $value ) {
						if ( isset( $_POST[ $map_key ] ) ) {

							$admin->transform_checkbox_value( $map_key, $checkboxes_map[ $map_key ] );
						} else {
							foreach ( $checkboxes_map[ $map_key ] as $option ) {
								$_POST[ $map_key ][ $option ] = 'no';
							}
						}
					}

					$meta_value = filter_input( INPUT_POST, $meta_key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

					if ( isset( $meta_value['select-fonts'] ) ) {
						$meta_value['select-fonts'] = wp_slash( $meta_value['select-fonts'] );
					}

					update_post_meta( $post_id, $meta_key, $meta_value );
				}
			}
		}

	}

	// Add meta boxes
	public function get_config_metabox() {
		$screens = array( 'npd-config' );
		foreach ( $screens as $screen ) {
			// Hook to add new metabox in config
			apply_filters( 'add_custom_metabox_to_config', $screen );

			add_meta_box(
				'npd-font-settings',
				__( 'Font settings', 'neon-product-designer' ),
				array( $this, 'get_font_settings' ),
				$screen
			);

			add_meta_box(
				'npd-color-settings',
				__( 'Color settings', 'neon-product-designer' ),
				array( $this, 'get_color_settings' ),
				$screen
			);

			add_meta_box(
				'npd-scene-settings',
				__( 'Scene settings', 'neon-product-designer' ),
				array( $this, 'get_scene_settings' ),
				$screen
			);

			add_meta_box(
				'npd-metas-size',
				__( 'Sizes', 'neon-product-designer' ),
				array( $this, 'get_size_settings' ),
				$screen
			);

		}
	}


	/**
	 * Get color settings
	 */
	public function get_color_settings() {
		$begin = array(
			'type' => 'sectionbegin',
			'id'   => 'npd-color-settings',
		);

		$color_groups = get_posts(
			array(
				'post_status' => 'publish',
				'post_type'   => 'npd-colors-palette',
				'nopaging'    => true,
			)
		);

		$color_options = array();
		foreach ( $color_groups as $color_group ) {
			$color_options[ $color_group->ID ] = $color_group->post_title;
		}

		$select_color = array(
			'title'   => __( 'Select colors', 'neon-product-designer' ),
			'name'    => 'npd-metas[select-colors]',
			'type'    => 'select',
			'id'      => 'select-colors',
			'desc'    => __( 'Select colors palette on this configuration', 'neon-product-designer' ),
			'options' => $color_options,
		);

		$default_color           = array(
			'title'   => __( 'Default colors', 'neon-product-designer' ),
			'name'    => 'npd-metas[default-colors]',
			'type'    => 'color',
			'default' => '#ffffff',
			'desc'    => __( 'This option allows you to set a default color.', 'neon-product-designer' ),
		);
		$color_behaviour_options = array(
			'txt-color' => __( 'Ligth Color', 'neon-product-designer' ),
			'same-color'  => __( 'Same Color', 'neon-product-designer' ),
			'light-color'   => __( 'Text Color', 'neon-product-designer' ),
		);
		$default_color_behaviour = array(
			'title'   => __( 'Select default colors behaviour', 'neon-product-designer' ),
			'name'    => 'npd-metas[default-color-behaviour]',
			'type'    => 'select',
			'id'      => 'default-color-behaviour',
			'desc'    => __( 'Select default colors behaviour', 'neon-product-designer' ),
			'options' => $color_behaviour_options,
		);

		$end      = array( 'type' => 'sectionend' );
		$settings = array(
			$begin,
			$select_color,
			$default_color,
			$default_color_behaviour,
			$end,
		);

		echo wp_kses(
			Kali_Admin_Tools::get_fields( $settings ),
			Kali_Admin_Tools::get_allowed_tags()
		);
	}

	/**
	 * Get scene settings
	 */
	public function get_scene_settings() {
		$begin = array(
			'type' => 'sectionbegin',
			'id'   => 'npd-scene-settings',
		);

		$scene_groups = get_posts(
			array(
				'post_status' => 'publish',
				'post_type'   => 'npd-scenes',
				'nopaging'    => true,
			)
		);

		$scene_options = array();
		foreach ( $scene_groups as $scene_group ) {
			$scene_options[ $scene_group->ID ] = $scene_group->post_title;
		}

		$select_scene = array(
			'title'   => __( 'Select Scenes', 'neon-product-designer' ),
			'name'    => 'npd-metas[select-scenes]',
			'type'    => 'select',
			'id'      => 'select-scenes',
			'desc'    => __( 'Select scenes group on this configuration', 'neon-product-designer' ),
			'options' => $scene_options,
		);

		$tooltip = array(
			'title' => __( 'Section info', 'neon-product-designer' ),
			'name'  => 'npd-metas[scene-tooltip]',
			'type'  => 'textarea',
			'desc'  => __( 'Tooltip info', 'neon-product-designer' ),
		);

		$end      = array( 'type' => 'sectionend' );
		$settings = array(
			$begin,
			$select_scene,
			$tooltip,
			$end,
		);
		
		echo wp_kses(
			Kali_Admin_Tools::get_fields( $settings ),
			Kali_Admin_Tools::get_allowed_tags()
		);

	}


	// function des champs du module size
	// ------------------------------------------------------------------------------------------

	/**
	 * Get size settings
	 */
	public function get_size_settings() {

		$options = array();

		?>
		<div class='block-form'>
			<?php
			$begin = array(
				'type' => 'sectionbegin',
				'id'   => 'size-options',
			);

			$tooltip = array(
				'title' => __( 'Section info', 'neon-product-designer' ),
				'name'  => 'npd-metas[sizes-options][tooltip]',
				'type'  => 'textarea',
				'desc'  => __( 'Tooltip info', 'neon-product-designer' ),
			);

			$Unit = array(
				'title'   => __( 'Unit', 'neon-product-designer' ),
				'desc'    => __( 'Select default unit', 'neon-product-designer' ),
				'name'    => 'npd-metas[sizes-options][unit]',
				'type'    => 'select',
				'options' => array(
					'cm' => esc_html__( 'Centimeter', 'neon-product-designer' ),

				),

				'value'   => 'cm',
				'default' => 'cm',
			);

			$end = array( 'type' => 'sectionend' );

			array_push( $options, $begin );

			array_push( $options, $Unit );
			array_push( $options, $tooltip );
			array_push( $options, $end );

			$settings = apply_filters(
				'npd_size_settings',
				$options
			);

			echo wp_kses(
				Kali_Admin_Tools::get_fields( $settings ),
				Kali_Admin_Tools::get_allowed_tags()
			);
			?>
			
		</div>
		<?php
	}


	// -----------------------------------------------------------------------------------------




	/**
	 * Register font settings
	 */
	public function get_font_settings() {
		$begin = array(
			'type' => 'sectionbegin',
			'id'   => 'npd-font-settings',
		);

		$fonts         = get_option( 'npd-fonts' );
		$display_fonts = array();
		if ( is_array( $fonts ) ) {
			foreach ( $fonts as $font ) {
				if ( isset( $font[0] ) && ! empty( $font[0] ) ) {
					$display_fonts[ wp_json_encode( $font ) ] = __( $font[0], 'neon-product-designer' );
				}
			}
		}

		$select_font = array(
			'title'   => __( 'Select fonts', 'neon-product-designer' ),
			'name'    => 'npd-metas[select-fonts]',
			'type'    => 'multiselect',
			'id'      => 'select-fonts',
			'desc'    => __( 'Select usable fonts on this configuration', 'neon-product-designer' ),
			'options' => $display_fonts,
		);

		$end      = array( 'type' => 'sectionend' );
		$settings = array(
			$begin,
			$select_font,
			$end,
		);
		echo wp_kses(
			Kali_Admin_Tools::get_fields( $settings ),
			Kali_Admin_Tools::get_allowed_tags()
		);
		?>
		<input type="hidden" name="npd_config_nonce" value="<?php echo esc_attr( wp_create_nonce( 'npd_config_nonce' ) ); ?>"/>
		<?php
	}


	/**
	 * Configuration Selector from product admin page
	 */
	public function get_product_config_selector() {
		$id = get_the_ID();

		$args        = array(
			'post_type' => 'npd-config',
			'nopaging'  => true,
		);
		$configs     = get_posts( $args );
		$configs_ids = array( '' => 'None' );
		foreach ( $configs as $config ) {
			$configs_ids[ $config->ID ] = $config->post_title;
		}
		?>
		<div id="npd_config_data" class="show_if_simple">
			<?php
			$this->get_product_tab_row( $id, $configs_ids, 'Select your NPD Configuration' );
			?>
		</div>
		<?php
	}

	private function get_product_tab_row( $pid, $configs_ids, $title ) {
		$begin = array(
			'type' => 'sectionbegin',
			'id'   => 'npd-metas-data',
		);

		$configurations = array(
			'title'   => $title,
			'name'    => "npd-metas[$pid][config-id]",
			'type'    => 'select',
			'options' => $configs_ids,
		);

		$end      = array( 'type' => 'sectionend' );
		$settings = apply_filters(
			'npd_product_tab_settings',
			array(
				$begin,
				$configurations,
				$end,
			)
		);

		echo "<div class='npd-product-config-row'>" . Kali_Admin_Tools::get_fields( $settings ) . '</div>';
	}

	/*
	 *  Set Variables Product configuration form
	 */

	public function get_variation_product_config_selector( $loop, $variation_data, $variation ) {
		$id = $variation->ID;

		$args        = array(
			'post_type' => 'npd-config',
			'nopaging'  => true,
		);
		$configs     = get_posts( $args );
		$configs_ids = array( '' => 'None' );
		foreach ( $configs as $config ) {
			$configs_ids[ $config->ID ] = $config->post_title;
		}
		?>
	<tr>
		<td>
		<?php
		$this->get_product_tab_row( $id, $configs_ids, 'Your NPD Configuration' );
		?>
		</td>
	</tr>
		<?php
	}

	function get_duplicate_post_link( $actions, $post ) {
		if ( $post->post_type == 'npd-config' && current_user_can( 'edit_posts' ) ) {
			$actions['duplicate'] = '<a href="admin.php?action=npd_duplicate_config&amp;post=' . esc_attr( $post->ID ) . '&amp;duplicate_nonce=' . wp_create_nonce( basename( __FILE__ ) ) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
		}
		return $actions;
	}

	/*
	 * Function creates config duplicate as a draft and redirects then to the edit config screen
	 */

	public function npd_duplicate_config() {
		global $npdb;
		if ( ! ( isset( $_GET['post'] ) || isset( $_POST['post'] ) || ( isset( $_REQUEST['action'] ) && 'npd_duplicate_config' == $_REQUEST['action'] ) ) ) {
			wp_die( 'No configuration to duplicate has been supplied!' );
		}

		/*
		 * Nonce verification
		 */
		if ( ! isset( $_GET['npd_config_duplicate_nonce'] ) || ! wp_verify_nonce( $_GET['npd_config_duplicate_nonce'], basename( __FILE__ ) ) ) {
			return;
		}

		/*
		 * get the original post id
		 */
		$post_id = ( isset( $_GET['post'] ) ? absint( sanitize_text_field( $_GET['post'] ) ) : absint( sanitize_text_field( $_POST['post'] ) ) );
		/*
		 * and all the original post data then
		 */
		$post = get_post( $post_id );

		/*
		 * if you don't want current user to be the new post author,
		 * then change next couple of lines to this: $new_post_author = $post->post_author;
		 */
		$current_user    = wp_get_current_user();
		$new_post_author = $current_user->ID;

		/*
		 * if post data exists, create the post duplicate
		 */
		if ( isset( $post ) && $post != null ) {

			/*
			 * new post data array
			 */
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'draft',
				'post_title'     => $post->post_title . __( ' - copy', 'neon-product-designer' ),
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order,
			);

			/*
			 * insert the post by wp_insert_post() function
			 */
			$new_post_id = wp_insert_post( $args );

			/*
			 * get all current post terms ad set them to the new post draft
			 */
			$taxonomies = get_object_taxonomies( $post->post_type ); // returns array of taxonomy names for post type, ex array("category", "post_tag");
			foreach ( $taxonomies as $taxonomy ) {
				$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
				wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
			}

			/*
			 * duplicate all post meta just in two SQL queries
			 */
			$post_meta_infos = $wpdb->get_results( "SELECT meta_key, meta_value FROM $npdb->postmeta WHERE post_id=$post_id" );
			if ( count( $post_meta_infos ) != 0 ) {
				$sql_query = "INSERT INTO $npdb->postmeta (post_id, meta_key, meta_value) ";
				foreach ( $post_meta_infos as $meta_info ) {
					$meta_key = $meta_info->meta_key;
					if ( $meta_key == '_wp_old_slug' ) {
						continue;
					}
					$meta_value      = addslashes( $meta_info->meta_value );
					$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
				}
				$sql_query .= implode( ' UNION ALL ', $sql_query_sel );
				$wpdb->query( $sql_query );
			}

			/*
			 * finally, redirect to the edit post screen for the new draft
			 */
			wp_redirect( admin_url( 'post.php?action=edit&post=' . esc_attr( $new_post_id ) ) );
			exit;
		} else {
			wp_die( 'Post creation failed, could not find original post: ' . esc_html( $post_id ) );
		}
	}

	public function save_variation_settings_fields( $variation_id ) {
		$meta_key = 'npd-metas';
		if ( isset( $_POST[ $meta_key ] ) ) {
			$variation  = wc_get_product( $variation_id );
			$meta_value = map_deep( $_POST[ $meta_key ], 'sanitize_text_field' );
			update_post_meta( $variation->get_parent_id(), $meta_key, $meta_value );
		}
	}

	public function get_npd_config_screen_layout_columns( $columns ) {
		$columns['npd-config'] = 1;
		return $columns;
	}

	public function get_npd_config_config_screen_layout() {
		return 1;
	}

	public function get_metabox_order( $order ) {
		$order['advanced'] = 'slugdiv,npd-font-settings,npd-color-settings,npd-scene-settings,npd-metas-size,npd-metas-back-board,npd-metas-custom-options,npd-metas-pricing,npd-metas-parts,submitdiv';

		return $order;
	}


}
