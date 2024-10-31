<?php
/**
 * Atd utils file.
 *
 * @link       orionorigin@orionorigin.com
 * @since      1.0.0
 *
 * @package    Atd
 * @subpackage Atd/includes
 */

/**
 * Outputs the settings fields.
 *
 * @param array $options Settings to output.
 */
function vc_admin_fields( $options ) {
	global $vc_row_templates;
	ob_start();
	foreach ( $options as $value ) {
		if ( ! isset( $value['type'] ) ) {
			continue;
		}
		if ( ! isset( $value['id'] ) ) {
			$value['id'] = '';
		}
		if ( ! isset( $value['name'] ) ) {
			$value['name'] = $value['id'];
		}
		if ( ! isset( $value['hierarchy'] ) ) {
			$value['hierarchy'] = array( $value['name'] );
		}
		if ( ! isset( $value['title'] ) ) {
			$value['title'] = isset( $value['name'] ) ? $value['name'] : '';
		}
		if ( ! isset( $value['class'] ) ) {
			$value['class'] = '';
		}
		if ( ! isset( $value['row_class'] ) ) {
			$value['row_class'] = '';
		}
		if ( ! isset( $value['css'] ) ) {
			$value['css'] = '';
		}
		if ( ! isset( $value['row_css'] ) ) {
			$value['row_css'] = '';
		}
		if ( ! isset( $value['default'] ) ) {
			$value['default'] = '';
		}
		if ( ! isset( $value['desc'] ) ) {
			$value['desc'] = '';
		}
		if ( ! isset( $value['desc_tip'] ) ) {
			$value['desc_tip'] = false;
		}
		if ( ! isset( $value['ignore_desc_col'] ) ) {
			$value['ignore_desc_col'] = false;
		}
		if ( ! isset( $value['label_class'] ) ) {
			$value['label_class'] = '';
		}
		$tip = '';
		if ( isset( $value['tip'] ) ) {
			$tip = "<span class='kali-info' data-tooltip-title='" . $value['tip'] . "'></span>";
		}

		// Custom attribute handling.
		$custom_attributes = array();

		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		$description = $value['desc'];

		if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ), true ) ) {
			$description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
		} elseif ( $description && in_array( $value['type'], array( 'checkbox' ), true ) ) {
			$description = wp_kses_post( $description );
		} elseif ( $description ) {
			$description = '<span class="description">' . wp_kses_post( $description ) . '</span>';
		}

		$post_id         = get_the_ID();
		$option_value    = '';
		$url_field_value = '';
		$raw_hierarchy   = explode_x( array( '[', ']' ), $value['name'] );
		$hierarchy       = array_filter( $raw_hierarchy );
		$section_types   = array( 'sectionbegin', 'sectionend' );
		$settings_table  = Kali_Admin_Tools::get_parse_value( $options[0], 'table', 'metas' );

		if ( ! in_array( $value['type'], $section_types, true ) & ! empty( $hierarchy ) ) {
			$root_key    = $hierarchy[0];
			$session_key = $root_key . "_$post_id";
			// We check if the meta is already stored in the session (db optimization) otherwise, we look for the original meta.
			$option_value = Kali_Admin_Tools::get_parse_value( $_SESSION['kali-data'], $session_key, false );

			if ( ! $option_value ) {
				// Retrive from the metas.
				if ( 'metas' === $settings_table ) {
					$option_value                        = get_post_meta( $post_id, $root_key, true );
					$_SESSION['kali-data'][ $session_key ] = $option_value;
				}
				// Retrive from the options.
				elseif ( 'options' === $settings_table ) {
					$option_value                        = get_option( $root_key );
					$_SESSION['kali-data'][ $session_key ] = $option_value;
				}
			}

			$i    = 0;
			$prev = '';

			$session_key = $root_key . "_$post_id";
			$root_value  = Kali_Admin_Tools::get_parse_value( $_SESSION['kali-data'], $session_key, false );
			if ( $root_key !== $value['name'] ) {
				$option_value = vc_find_in_array_by_key( $root_value, $value['name'] );
			}
		}
		if ( ! $option_value && '0' !== $option_value ) {
			$option_value = $value['default'];
		}

		if ( ! in_array( $value['type'], $section_types, true ) && ! $value['ignore_desc_col'] ) {
			?>
			<tr style="<?php echo esc_attr( $value['row_css'] ); ?>" class="<?php echo esc_attr( $value['row_class'] ); ?>">
				<td class='label'>
					<?php echo esc_html( $value['title'] ) . esc_html( $tip ); ?>
					<div class='kali-desc'>
						<?php echo esc_html( $value['desc'] ); ?>
					</div>
				</td>
				<?php
		}

		if ( ! in_array( $value['type'], $section_types, true ) ) {
			if ( isset( $value['show_as_label'] ) ) {
				echo wp_kses_post( "<label class='" . esc_attr( $value['label_class'] ) . "'>" . $value['title'] . $tip );
			} else {
				echo '<td>';
			}
		}
			// Switch based on type.
		switch ( $value['type'] ) {
			case 'sectionbegin':
				// We start/reset the session.
				$_SESSION['kali-data'] = array();
				?>
				<div class="kali-wrap">
									<div id="<?php echo esc_attr( $value['id'] ); ?>" class="kali-metabox-container <?php echo ( isset( $value['class'] ) ) ? esc_attr( $value['class'] ) : ''; ?>">
						<div class='block-form'>
							<table class="wp-list-table widefat fixed pages kali-root">
								<tbody>
								<?php
				break;
			case 'sectionend':
				?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<?php
				break;
				// Standard text inputs and subtypes like 'number'.
			case 'text':
			case 'email':
			case 'number':
			case 'password':
				$type = $value['type'];
				?>

				<input
					name="<?php echo esc_attr( $value['name'] ); ?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					type="<?php echo esc_attr( $type ); ?>"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					value="<?php echo esc_attr( $option_value ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
					<?php echo wp_kses_post( implode( ' ', $custom_attributes ) ); ?>
					/>

					<?php
				break;

			case 'color':
				$type            = 'text';
				$value['class'] .= 'kali-color';
				?>
				<div class="kali-color-container">
					<input
						name="<?php echo esc_attr( $value['name'] ); ?>"
						id="<?php echo esc_attr( $value['id'] ); ?>"
						type="<?php echo esc_attr( $type ); ?>"
						style="<?php echo esc_attr( $value['css'] ); ?>"
						value="<?php echo esc_attr( $option_value ); ?>"
						class="<?php echo esc_attr( $value['class'] ); ?>"
					<?php echo wp_kses_post( implode( ' ', $custom_attributes ) ); ?>
						/>
					<span class="kali-color-btn"></span>
				</div>

					<?php
				break;

			case 'textarea':
				?>

				<textarea
					name="<?php echo esc_attr( $value['name'] ); ?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
					<?php echo wp_kses_post( implode( ' ', $custom_attributes ) ); ?>
					><?php echo esc_textarea( $option_value ); ?></textarea>

					<?php
				break;

			case 'texteditor':
				wp_editor(
					$option_value,
					$value['id'],
					array(
						'wpautop'       => true,
						'media_buttons' => false,
						'textarea_name' => $value['name'],
						'textarea_rows' => 10,
						'false'         => true,
					)
				);
				break;

			case 'select':
			case 'multiselect':
			case 'post-type':
				if ( 'post-type' === $value['type'] ) {
					// We make sure the limit is -1 if not set.
					$value['args']['posts_per_page'] = Kali_Admin_Tools::get_parse_value( $value['args'], 'posts_per_page', -1 );
					$posts                           = get_posts( $value['args'] );
					$posts_ids                       = Kali_Admin_Tools::get_parse_value( $value, 'first_value', array() );
					foreach ( $posts as $post ) {
						$posts_ids[ $post->ID ] = $post->post_title;
					}
					$value['options'] = $posts_ids;
				}
				?>

				<select name="<?php
				echo esc_attr( $value['name'] );
				if ( 'multiselect' === $value['type'] || in_array( 'multiple="multiple"', $custom_attributes ) ) {
					echo '[]';}
				?>"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
					<?php echo wp_kses_post( implode( ' ', $custom_attributes ) ); ?>
					<?php
					if ( 'multiselect' === $value['type'] ) {
						echo 'multiple="multiple"';
					}
					?>
					>
					<?php
					foreach ( $value['options'] as $key => $val ) {
						?>
						<option value="<?php echo esc_attr( $key ); ?>" 
												  <?php
													if ( is_array( $option_value ) ) {
														selected( in_array( $key, $option_value ), true );
													} else {
														selected( $option_value, $key );
													}
													?>
							><?php echo wp_kses_post( $val ); ?></option>
							<?php
					}
					?>
				</select>

					<?php
				break;
			case 'groupedselect':
				?>
				<select
					name="<?php echo esc_attr( $value['name'] ); ?>
					<?php
					if ( 'multiselect' === $value['type'] ) {
						echo '[]';
					}
					?>
					"
					id="<?php echo esc_attr( $value['id'] ); ?>"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
					<?php echo wp_kses_post( implode( ' ', $custom_attributes ) ); ?>
					<?php
					if ( 'multiselect' === $value['type'] ) {
						echo 'multiple="multiple"';
					}
					?>
					>
					<?php
					foreach ( $value['options'] as $group => $group_values ) {
						?>
									<optgroup label="<?php echo esc_attr( $group ); ?>">
							<?php
							foreach ( $group_values as $key => $val ) {
								?>
								<option value="<?php echo esc_attr( $key ); ?>" 
								<?php
								if ( is_array( $option_value ) ) {
									selected( in_array( $key, $option_value, true ), true );
								} else {
									selected( $option_value, $key );
								}
								?>
										><?php echo wp_kses_post( $val ); ?></option>
								<?php
							}
							?>
						</optgroup>
									<?php
					}
					?>
				</select> <?php echo wp_kses_post( $description ); ?>
					<?php
				break;

				// Radio inputs.
			case 'radio':
				?>

				<fieldset>
					<ul>
				<?php
				foreach ( $value['options'] as $key => $val ) {
					?>
							<li>
								<label><input
										name="<?php echo esc_attr( $value['name'] ); ?>"
										value="<?php echo wp_kses_post( $key ); ?>"
										type="radio"
										style="<?php echo esc_attr( $value['css'] ); ?>"
										class="<?php echo esc_attr( $value['class'] ); ?>"
						<?php echo wp_kses_post( implode( ' ', $custom_attributes ) ); ?>
									<?php checked( $key, $option_value ); ?>
										/> <?php echo wp_kses_post( $val ); ?></label>
							</li>
						<?php
				}
				?>
					</ul>
				</fieldset>                
				<?php
				break;

			case 'checkbox':
				$visbility_class = array();

				if ( ! isset( $value['hide_if_checked'] ) ) {
					$value['hide_if_checked'] = false;
				}
				if ( ! isset( $value['show_if_checked'] ) ) {
					$value['show_if_checked'] = false;
				}
				if ( 'yes' === $value['hide_if_checked'] || 'yes' === $value['show_if_checked'] ) {
					$visbility_class[] = 'hidden_option';
				}
				if ( 'option' === $value['hide_if_checked'] ) {
					$visbility_class[] = 'hide_options_if_checked';
				}
				if ( 'option' === $value['show_if_checked'] ) {
					$visbility_class[] = 'show_options_if_checked';
				}

				if ( ! isset( $value['checkboxgroup'] ) || 'start' === $value['checkboxgroup'] ) {
					?>
					<fieldset>
					<?php
				} else {
					?>
						<fieldset class="<?php echo esc_attr( implode( ' ', $visbility_class ) ); ?>">
						<?php
				}

				if ( ! empty( $value['title'] ) ) {
					?>
							<legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ); ?></span></legend>
						<?php
				}
					$cb_value = Kali_Admin_Tools::get_parse_value( $value, 'value', false );
				if ( ! $cb_value ) {
					$cb_value = Kali_Admin_Tools::get_parse_value( $value, 'default', 1 );
				}
				?>
														<label for="<?php echo esc_attr( $value['id'] ); ?>">
							<input
								name="<?php echo esc_attr( $value['name'] ); ?>"
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="checkbox"
								value="<?php echo wp_kses_post( $cb_value ); ?>"
					<?php checked( $option_value, $cb_value ); ?>
							<?php echo wp_kses_post( implode( ' ', $custom_attributes ) ); ?>
								/> <?php echo wp_kses_post( $description ); ?>
						</label> <?php echo wp_kses_post( $tip ); ?>
					<?php
					if ( ! isset( $value['checkboxgroup'] ) || 'end' === $value['checkboxgroup'] ) {
						?>
						</fieldset>

						<?php
					} else {
						?>
					</fieldset>
						<?php
					}
				break;

			case 'image':
				$set_btn_label    = Kali_Admin_Tools::get_parse_value( $value, 'set', 'Set image' );
				$remove_btn_label = Kali_Admin_Tools::get_parse_value( $value, 'remove', 'Remove image' );

				$img_src      = '';
				$root_img_src = Kali_Admin_Tools::get_image_url( $option_value, false );
				if ( $root_img_src ) {
					$img_src = vc_get_medias_root_url( "/$root_img_src" );
				}
				?>
								<div class="<?php echo esc_attr( $value['class'] ); ?>">
					<button class="button kali-add-media"><?php echo wp_kses_post( $set_btn_label ); ?></button>
					<button class="button kali-remove-media"><?php echo wp_kses_post( $remove_btn_label ); ?></button>
										<input type="hidden" name="<?php echo esc_attr( $value['name'] ); ?>" value="<?php echo wp_kses_post( $root_img_src ); ?>">
					<div class="media-preview">
					<?php
					if ( isset( $option_value ) ) {
						echo wp_kses_post( "<img src='$img_src'>" );
					}
					?>
					</div>
				</div>

					<?php
				break;
			case 'file':
				$set_btn_label    = Kali_Admin_Tools::get_parse_value( $value, 'set', 'Set file' );
				$remove_btn_label = Kali_Admin_Tools::get_parse_value( $value, 'remove', 'Remove file' );
				?>
								<div class="<?php echo esc_attr( $value['class'] ); ?>">
					<button class="button kali-add-media"><?php echo wp_kses_post( $set_btn_label ); ?></button>
					<button class="button kali-remove-media"><?php echo wp_kses_post( $remove_btn_label ); ?></button>
										<input type="hidden" name="<?php echo esc_attr( $value['name'] ); ?>" value="<?php echo wp_kses_post( $option_value ); ?>">
					<div class="media-name">
					<?php
					if ( isset( $option_value ) ) {
						echo wp_kses_post( basename( $option_value ) );
					}
					?>
					</div>
				</div>

					<?php
				break;

			case 'date':
				$type            = 'date';
				$value['class'] .= 'kali-date';
				?>
				<div class="kali-date-container">
					<input
						name="<?php echo esc_attr( $value['name'] ); ?>"
						id="<?php echo esc_attr( $value['id'] ); ?>"
						type="<?php echo esc_attr( $type ); ?>"
						style="<?php echo esc_attr( $value['css'] ); ?>"
						value="<?php echo esc_attr( $option_value ); ?>"
						class="<?php echo esc_attr( $value['class'] ); ?>"
					<?php echo wp_kses_post( implode( ' ', $custom_attributes ) ); ?>
						/>
				<!-- <span class="kali-date-btn"></span> -->
				</div>

					<?php
				break;

			case 'repeatable-fields':
				if ( ! is_array( $option_value ) ) {
					$option_value = array();
				}
				$value['popup'] = Kali_Admin_Tools::get_parse_value( $value, 'popup', false );

				$style = Kali_Admin_Tools::get_parse_value( $value, 'style', '' );
				if ( $value['popup'] ) {
					add_thickbox();
					$modal_id = uniqid( 'kali-modal-' );
					echo wp_kses_post( "<a style='$style' class='kali-modal-trigger button button-primary button-large' data-toggle='kali-modal' data-target='#$modal_id' data-modalid='$modal_id'>" . $value['popup_button'] . '</a>' );
					echo wp_kses_post(
						'<div class="omodal fade kali-modal" id="' . $modal_id . '" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                                        <div class="omodal-dialog">
                                                          <div class="omodal-content">
                                                            <div class="omodal-header">
                                                              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                              <h4 class="omodal-title" id="myModalLabel' . $modal_id . '">' . $value['popup_title'] . '</h4>
                                                            </div>
                                                            <div class="omodal-body">'
					);
					$value['class'] .= ' table-fixed-layout';
				}
				?>

								<table id="<?php echo esc_attr( $value['id'] ); ?>" class="<?php echo esc_attr( $value['class'] ); ?> widefat repeatable-fields-table">
					<thead>
						<tr>
					<?php
					foreach ( $value['fields'] as $field ) {
						$tip = '';
						if ( isset( $field['tip'] ) ) {
							$tip = "<span class='kali-info' data-tooltip-title=\"" . $field['tip'] . '"></span>';
						}
						if ( isset( $field['title'] ) ) {
							echo wp_kses_post( '<td style="width: 60px;">' . $field['title'] . "$tip</td>" );
						} else {
							echo wp_kses_post( "<td style='width: 60px;'>$tip</td>" );
						}
					}
					?>
							<td style="width: 20px;"></td>
						</tr>
					</thead>
					<tbody>
					<?php
					foreach ( $option_value as $i => $row ) {
						echo "<tr class='" . esc_attr( $value['row_class'] ) . "'>";
						foreach ( $value['fields'] as $field ) {
							if ( isset( $row[ $field['name'] ] ) ) {
								$field_value = $row[ $field['name'] ];
							} else {
								$field_value = '';
							}
							$field['name'] = $value['name'] . "[$i][" . $field['name'] . ']';
							if ( 'image' === $field['type'] && isset( $field['url_name'] ) ) {
								$field['url_name'] = $value['name'] . "[$i][" . $field['url_name'] . ']';
							}
							$field['default']         = $field_value;
							$field['ignore_desc_col'] = true;

							echo vc_admin_fields( array( $field ) );
						}
						?>
						<td>
							<a class="remove-rf-row"></a>
						</td>
						<?php
						echo '</tr>';
					}
					$row_tpl                     = get_row_template( $value );
					$row_tpl                     = preg_replace( "/\r|\n/", '', $row_tpl );
					$row_tpl                     = preg_replace( '/\s+/', ' ', $row_tpl );
					$tpl_id                      = uniqid();
					$vc_row_templates[ $tpl_id ] = $row_tpl;
					$add_label = Kali_Admin_Tools::get_parse_value( $value, 'add_btn_label', __( 'Add', 'vpc' ) );
					?>
				</tbody>
				</table>
								<a class="button mg-top add-rf-row" data-tpl="<?php echo esc_attr( $tpl_id ); ?>"><?php echo wp_kses_post( $add_label ); ?></a>

					<?php
					if ( $value['popup'] ) {
						echo '</div>
                                                          </div>
                                                        </div>
                                                      </div>';
					}
				break;

			case 'groupedfields':
				?>

				<div class="kali-wrap xl-gutter-8">
				<?php
				foreach ( $value['fields'] as $field ) {
					$field['show_as_label']   = true;
					$field['ignore_desc_col'] = true;
					$field['table']           = $settings_table;
					if ( ! isset( $field['label_class'] ) ) {
						$nb_cols              = count( $value['fields'] );
						$field['label_class'] = 'kali-col xl-1-' . $nb_cols;
					}
					echo vc_admin_fields( array( $field ) );
				}
				?>
				</div>

				<?php
				break;

			case 'custom':
				call_user_func( $value['callback'] );
				break;
			case 'button':
				?>

				<a

					id="<?php echo esc_attr( $value['id'] ); ?>"
					style="<?php echo esc_attr( $value['css'] ); ?>"
					value="<?php echo esc_attr( $option_value ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
					<?php echo wp_kses_post( implode( ' ', $custom_attributes ) ); ?>
					><?php echo esc_attr( $value['title'] ); ?></a>

					<?php
				break;
			case 'google-font':
				vc_get_google_fonts_selector( $option_value, esc_attr( $value['id'] ), esc_attr( $value['name'] ), esc_attr( $value['css'] ), esc_attr( $value['class'] ) );
				break;

				// Default: run an action.
			default:
				do_action( 'o_admin_field_' . $value['type'], $value );
				break;
		}
		if ( ! in_array( $value['type'], $section_types, true ) ) {
			if ( isset( $value['show_as_label'] ) ) {
				echo '</label>';
			} else {
				echo '</td>';
			}
		}
		if ( ! in_array( $value['type'], $section_types, true ) && ! $value['ignore_desc_col'] ) {
			?>
			</tr>
			<?php
		}
	}

	return ob_get_clean();
}

/**
 * Get row template.
 *
 * @param array $value The value.
 * @return string The sting.
 */
function get_row_template( $value ) {
	$row_tpl = "<tr class='kali-rf-row'>";
	// ID unique permettant d'identifier de façon unique tous les indexes de ce template et de la remplacer tous ensemble en cas de besoin.
	$index = uniqid();
	foreach ( $value['fields'] as $field ) {
		$field_tpl                    = $field;
		$field_tpl['name']            = $value['name'] . '[{' . $index . '}][' . $field_tpl['name'] . ']';
		$field_tpl['ignore_desc_col'] = true;
		$row_tpl                     .= vc_admin_fields( array( $field_tpl ) );
	}
	// We add the remove button to the template.
	$row_tpl .= '<td><a class="remove-rf-row"></a></td></tr>';

	return $row_tpl;
}

/**
 * Get a value by key in an array if defined.
 *
 * @param array  $values Array to search into.
 * @param string $search_key Searched key.
 * @param mixed  $default_value Value if the key does not exist in the array.
 * @return mixed
 */
/* function get_parse_value( $values, $search_key, $default_value = '' ) {
	if ( isset( $values[ $search_key ] ) ) {
		$default_value = $values[ $search_key ];
	}
	return $default_value;
} */

/**
 * Explode.
 *
 * @param array $delimiters The delimiters.
 * @param string $string The string.
 * @return array
 */
function explode_x( $delimiters, $string ) {
	return explode( chr( 1 ), str_replace( $delimiters, chr( 1 ), $string ) );
}

/**
 * Returns a media URL
 *
 * @param int $media_id Media ID.
 * @return string
 */
function vc_get_media_url( $media_id ) {
	$attachment     = wp_get_attachment_image_src( $media_id, 'full' );
	$attachment_url = $attachment[0];
	return $attachment_url;
}

/**
 * Find key in array.
 *
 * @param $root_value the root value.
 * @param string $key the Key.
 * @return array | bool
 */
function vc_find_in_array_by_key( $root_value, $key ) {
		$root_value_temp     = $root_value;
	$bracket_pos             = strpos( $key, '[' );
	$usable_value_index      = substr( $key, $bracket_pos );
	$search                  = array( '[', ']' );
	$replace                 = array( '', ',' );
	$usable_value_index2     = str_replace( $search, $replace, $usable_value_index );
		$usable_value_index3 = explode( ',', $usable_value_index2 );
		$last_value          = array_pop( $usable_value_index3 );
		unset( $usable_value_index3[ $last_value ] );
	if ( is_array( $root_value_temp ) ) {
		foreach ( $usable_value_index3 as $value ) {
			if ( isset( $root_value_temp[ $value ] ) ) {
				$root_value_temp = $root_value_temp[ $value ];
			} else {
				return false;
			}
		}
		return $root_value_temp;
	} else {
		return false;
	}
}

/**
 * Get proper image url.
 *
 * @param string $suspected_link The suspected link.
 * @param bool   $with_root root Is with root.
 */
/* function get_image_url( $suspected_link, $with_root = true ) {
	if ( empty( $suspected_link ) ) {
		return $suspected_link;
	}
	$img_src = $suspected_link;
	$root_link = vc_get_medias_root_url( '/' );
	if ( is_numeric( $suspected_link ) ) {
		$raw_img_src = wp_get_attachment_url( $suspected_link );
		$img_src     = str_replace( $root_link, '', $raw_img_src );
	}

	if ( strpos( $root_link, 'https' ) !== false && strpos( $img_src, 'https' ) === false ) {
		$img_src = str_replace( 'http', 'https', $img_src );
	}

	$img_src = str_replace( $root_link, '', $img_src );
	// Code for bad https handling.
	if ( strpos( $root_link, 'https' ) === false ) {
		$https_home = str_replace( 'http', 'https', $root_link );
		$img_src    = str_replace( $https_home, '', $img_src );
	}

	if ( $with_root ) {
		$img_src = vc_get_medias_root_url( "/$img_src" );
	}
	return $img_src;
} */
/**
 * Search backwards starting from haystack length characters from the end.
 *
 * @param string $haystack The haystack.
 * @param string $needle The needle.
 */
function vc_starts_with( $haystack, $needle ) {
	// search backwards starting from haystack length characters from the end.
	return '' === $needle || false !== strrpos( $haystack, $needle, -strlen( $haystack ) );
}

/**
 * Search forward starting from end minus needle length characters.
 *
 * @param string $haystack the haystack.
 * @param string $needle the needle.
 * @return bool
 */
function vc_ens_with( $haystack, $needle ) {
	// search forward starting from end minus needle length characters.
	return '' === $needle || ( ( $temp = strlen( $haystack ) - strlen( $needle ) ) >= 0 && strpos( $haystack, $needle, $temp ) !== false );
}

/**
 * Get medias root url.
 *
 * @param string $path The path.
 */
function vc_get_medias_root_url( $path = '/' ) {
	$upload_url_path = get_option( 'upload_url_path' );
	if ( $upload_url_path ) {
		return $upload_url_path . $path;
	} else {
		if ( function_exists( 'icl_object_id' ) ) {
			return site_url( $path );
		} else {
			return home_url( $path );
			// Fixed broken images issue when defining pictures in admin area when home_url !=site_url.
		}
	}
}

/**
 * Get google fonts selector.
 *
 * @param string $selected_font The selected font.
 * @param string $id The id.
 * @param string $name The name.
 * @param string $style The styles.
 * @param string $class the class.
 */
function vc_get_google_fonts_selector( $selected_font = false, $id = '', $name = '', $style = '', $class = '' ) {
		$file_path       = plugin_dir_path( __FILE__ ) . 'googlefont.json';
		$fonts_json_file = fopen( $file_path, 'r' );
		$font_content    = fread( $fonts_json_file, filesize( $file_path ) );
		fclose( $fonts_json_file );
		$decoded_fonts = json_decode( $font_content, true );
	?>
						<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" style="<?php echo esc_attr( $style ); ?>" class="kali-google-font-selector <?php echo esc_attr( $class ); ?>">
	<?php
	echo '<option value="">Pick a font</option>';
	foreach ( $decoded_fonts['items'] as $font ) {
		if ( isset( $font['family'] ) && isset( $font['files'] ) && isset( $font['files']['regular'] ) ) {
			$selected    = '';
			$field_value = 'http://fonts.googleapis.com/css?family=' . rawurlencode( $font['family'] ) . '|' . $font['family'] . '|' . $font['category'];
			if ( $selected_font === $field_value ) {
				$selected = 'selected';
			}
			echo wp_kses_post( '<option value="' . $field_value . '" ' . $selected . '>' . $font['family'] . '</option> ' );
		}
	}
	?>
	</select>
		<?php
		return $decoded_fonts['items'];
}

	/**
	 * Register google font
	 *
	 * @param string $font_name The font name.
	 * @param string $raw_url The url.
	 */
function o_register_google_font( $font_name, $raw_url ) {
	$font_url = str_replace( 'http://', '//', $raw_url );
	if ( $font_url ) {
		$handler = sanitize_title( $font_name );
		wp_register_style( $handler, $font_url, array(), ATD_VERSION, 'all' );
		wp_enqueue_style( $handler );
	}
}
