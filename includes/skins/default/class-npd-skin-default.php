<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-vpc-default-skin
 *
 * @author Vertim Coders
 */
class NPD_Skin_Default {


	public $editor;
	public $npc_metas;

	public function __construct( $editor_obj, $npc_metas ) {
		if ( $editor_obj ) {
			$this->editor = $editor_obj;
			$this->npc_metas = $npc_metas;
		}
	}

	public function display() {
		global $npd_settings,$wp_query;
		$npd_settings = apply_filters( 'NPD_global_settings', $npd_settings );

		ob_start();

		$this->register_styles();
		$this->register_scripts();

		$fonts = get_option( 'npd-fonts' );
		if ( empty( $fonts ) ) {
			$fonts = npd_get_default_fonts();
		}

		$add_to_cart_label = __( 'ADD TO CART', 'neon-product-designer' );
		if ( isset( $wp_query->query_vars['edit'] ) ) {
			$add_to_cart_label = __( 'UPDATE CART ITEM', 'neon-product-designer' );
		}
		$product = wc_get_product( $this->editor->item_id );

		?>
		<input type="hidden" value="<?php echo esc_attr(wp_create_nonce( 'npd-nonce-security' ));?>" name="npd-nonce-security" id="npd-nonce-security">

		<div id="npd-editor-container" class="npd-inner-content">
			<section id="npd-editor-wrap" class="npd-editor-section">
				<div class="npd-editor-section-inner-wrap">
					
					<div id="npd-editor-content" class="npd-editor-content">
						<div class="npd-editor-left-side">
							<div class="npd-editor-tools-item first-box is-active">
								<div class="npd-editor-tools-title">
									<?php esc_html_e( 'Add your custom text', 'neon-product-designer' ); ?>
								</div>
								<div class="npd-editor-tools-content">
									<textarea id="npd-text-field" class="npd-custom-textarea" name="npd-text" row="4"><?php esc_html_e( 'You glow !!!', 'neon-product-designer' ); ?></textarea>
									
								</div>
							</div>

							<div class="npd-editor-tools-item">
								<div class="npd-editor-tools-title">
									<?php esc_html_e( 'Text Alignment', 'neon-product-designer' ); ?>
								</div>
								<div class="npd-editor-tools-content npd-editor-content-text">
									<div class="npd-radio-item-wrap npd-align-btn-wrap">
										<input type="radio" value="left" name="npd-text-align" id="npd-txt-left">
										<label><i class="fa fa-align-left"></i></label>
									</div>
									<div class="npd-radio-item-wrap npd-align-btn-wrap">
										<input type="radio" name="npd-text-align" id="npd-txt-center" value="center" checked="true">
										<label><i class="fa fa-align-center"></i></label>
									</div>
									<div class="npd-radio-item-wrap npd-align-btn-wrap">
										<input type="radio" name="npd-text-align" id="npd-txt-right" value="right">
										<label><i class="fa fa-align-right"></i></label>
									</div>
									
								</div>
							</div>

							<div class="npd-editor-tools-item">
								<div class="npd-editor-tools-title">
									<?php esc_html_e( 'Font Family', 'neon-product-designer' ); ?>
								</div>
                                <div class="npd-editor-tools-content">
								<?php
    								if ( isset( $this->npc_metas['select-fonts'] ) && !empty($this->npc_metas['select-fonts']) ) {
    								
    							
    									?>
    											<!-- select font family-->
    								<div id="npd-font-picker-wrap" class=" npd-custom-select-wrap ">
    									<div class="npd-custom-select-input-cover"></div>
    									<?php

    										$fonts = $this->npc_metas['select-fonts'];
    										$font_label = json_decode( $fonts[0] )[0];
    						

    									?>
    										<input type="text" name="npd-font" style="font-family:'<?php echo esc_attr( $font_label ); ?>'" value="<?php echo esc_attr( $font_label ); ?>"   class="npd-text-input" id="npd-font-selector" readonly="">
    									   
    										<ul class="hidden" >
    										<?php
    										$preload_div = '';
    											$fonts = $this->npc_metas['select-fonts'];
												$compt = 0;
    											foreach ( $fonts as $font ) {
													if ( $compt === 3 ) continue;
    												$font_label = json_decode( $font )[0];
    												?>
    													<li data-value='<?php echo esc_attr( $font_label ); ?>' style='font-family:"<?php echo esc_attr( $font_label ); ?>"'><?php echo esc_html( $font_label ); ?></li>
    													<?php
    													$preload_div .= "<span style='font-family: " . esc_attr($font_label) . ";'>.</span>";
													++$compt;
    											}
    										?>
    											 

    										</ul>
    								</div>
    								

    										<?php
    								}

    								?>
                                </div>
								
							</div>
							<?php
                            if ( isset( $this->npc_metas['select-colors'] ) && ! empty( $this->npc_metas['select-colors'] ) ) {
                                $colors_post = $this->npc_metas['select-colors'];
                                $colors_palettes = get_post_meta( $colors_post, 'npd-colors-palette-data', true );
                                ?>
                                <div class="npd-editor-tools-item">
                                    <div class="npd-editor-tools-title">
                                        <?php esc_html_e( 'Color', 'neon-product-designer' ); ?>
                                    </div>
                                    <div class="npd-editor-tools-content">

                                        <div class="npd-neon-color-wrap">
                                            <?php
											$compt = 0;
                                            foreach ( $colors_palettes as $color ) {
												if ( $compt === 3 ) continue;
                                                if ( isset( $color['default'] ) && 1 == $color['default'] ) {
                                                    $selected = "checked='true'";
                                                } else {
                                                    $selected = '';
                                                }

                                                $color_name = $color['name'];
                                                $color_code = $color['code_hex'];

                                                ?>
                                                    <div class="npd-radio-item-wrap npd-color-wrap" title='<?php echo esc_attr( $color_name ); ?>' style="background-color:<?php echo esc_attr( $color_code ); ?>;">
                                                        <input type="radio" value="<?php echo esc_attr( $color_code ); ?>" name="npd-neon-color" <?php echo esc_attr( $selected ); ?>>
                                                        <label></label>
                                                    </div>
                                                    <?php
												++$compt;
                                            }

                                            ?>

                                        </div>
                                    </div>
                                </div>
                                <?php
                            }

                            if ( isset( $this->npc_metas['sizes-options'] ) && ! empty( $this->npc_metas['sizes-options']['sizes'] ) ) {
                                ?>
                                        <div class="npd-editor-tools-item">
                                            <form class="npd-dimension-section">
                                                <div class="npd-editor-tools-title">
                                                <?php 
                                                    esc_html_e( 'Dimensions', 'neon-product-designer' ); 
                                                    if(isset($this->npc_metas['sizes-options']['tooltip']) && !empty($this->npc_metas['sizes-options']['tooltip'])){
                                                            ?>
                                                                <span class="npd-infobule fa fa-question-circle" title="<?php echo esc_attr($this->npc_metas['sizes-options']['tooltip']);?>"></span>
                                                            <?php
                                                        }
                                                ?>
                                                </div>
                                                <div class="npd-editor-tools-content ">
                                                <?php
                                                    $sizes = $this->npc_metas['sizes-options']['sizes'];
													$compt = 0;
                                                foreach ( $sizes as $size ) {
													if ( $compt === 3 ) continue;
                                                    if ( isset( $size['default'] ) && 1 == $size['default'] ) {
                                                        $selected = "checked='true'";
                                                    } else {
                                                        $selected = '';
                                                    }
                                                    ?>
                                                            <div class="npd-radio-item-wrap npd-text-size-wrap">
                                                                <input type="radio" value="<?php echo esc_attr( $size['width'] ); ?>" name="npd-txt-size" <?php echo esc_attr( $selected ); ?> data-price="<?php echo esc_attr( $size['price'] ); ?>" data-height="<?php echo esc_attr( $size['height'] ); ?>" data-width="<?php echo esc_attr( $size['width'] ); ?>">
                                                                <label>
                                                                    <div><?php echo esc_attr( $size['label'] ); ?></div>
                                                                    <div><?php echo esc_attr( $size['description'] ); ?></div>
                                                                </label>
                                                            </div>
                                                        <?php
													++$compt;
                                                }
                                                ?>
                                                </div>
                                            </form>
                                        </div>
                                    <?php
                            }

							if ( isset( $this->npc_metas['select-scenes'] ) && ! empty( $this->npc_metas['select-scenes'] ) ) {
								$scenes_post = $this->npc_metas['select-scenes'];
								$scenes_images = get_post_meta( $scenes_post, 'npd-scenes-data', true );
								
								?>
									<div class="npd-editor-tools-item">
										<div class="npd-editor-tools-title">
										 <?php 
                                            esc_html_e( 'Scene', 'neon-product-designer' ); 
                                            if(isset($this->npc_metas['scene-tooltip']) && !empty($this->npc_metas['scene-tooltip'])){
                                                ?>
                                                    <span class="npd-infobule fa fa-question-circle" title="<?php echo esc_attr($this->npc_metas['scene-tooltip']);?>"></span>
                                                <?php
                                            }
                                         ?>
										</div>
										<div class="npd-editor-tools-content">
											<div class="npd-scene-content">
											<?php
											$compt = 0;
											foreach ( $scenes_images as $scene ) {
												if ( $compt === 3 ) continue;
												if ( isset( $scene['default'] ) && 1 == $scene['default'] ) {
													$selected = "checked='true'";
												} else {
													$selected = '';
												}
												
												$bg_url = Kali_Admin_Tools::get_image_url( $scene['id'] );

												?>
													<div class="npd-radio-item-wrap npd-scene-bg-wrap" data-bg="<?php echo esc_attr( $bg_url ); ?>" title="<?php echo esc_attr( $scene['label'] ); ?>" style="background-image:url(<?php echo esc_html( $bg_url ); ?>);">
														<input type="radio" value="<?php echo esc_attr( $bg_url ); ?>" name="npd-scene" <?php echo esc_attr( $selected ); ?>>
														<label></label>
															
														
													</div>
													<?php
												++$compt;
											}

											?>
											</div>

									</div>
								</div>
							
								<?php
							}
							
								?>
                            <div id="npd-bottom-limit"></div>
						</div>
						<div class="npd-editor-right-side">
							<?php
								$purchase_properties = $this->editor->npd_product->get_purchase_properties();
								// $price = $product->get_price() + $tpl_price;
								$price = $product->get_price();
								if(empty($price)){
									$price = 0;
								}
								$custom_quantity = isset( $_GET['custom_qty'] ) ? sanitize_text_field($_GET['custom_qty']) : $purchase_properties['min_to_purchase'];
								$price_html = wc_price( $price * $custom_quantity ) ;
                                $allowed_html = array('span' =>array(
                                                'class'=>array()) , );
							?>
							<div class="npd-price-section" >
                                
                                <span class="npd-total-price"><?php echo wp_kses( $price_html, $allowed_html) ; ?></span>
                               
                                
                            </div>
							<div class="npd-editor-preview">
								<div class="npd-text-render-container">
									<span id="npd-text-render-wrap"></span>
								</div>
							</div>
							<div id="debug"></div>
							<div class="npd-button-payement">
								<?php
								 
								$quantity_display = '';
								if ( $product->is_sold_individually() ) {
									$quantity_display = "style='display: none;'";
								}

								$btn_content = '
								<div class="npd-qty-container" data-id="' . esc_attr( $this->editor->item_id ) . '" ' . esc_attr( $quantity_display ) .'>
									<input type="button" id="minus" value="-" class="minus npd-custom-right-quantity-input-set npd-custom-btn">
									<input type="number" step="' . esc_attr( $purchase_properties['step'] ) . '" value="' . esc_attr( $custom_quantity ) . '" class="npd-custom-right-quantity-input npd-qty" id="npd-qty" min="' . esc_attr( $purchase_properties['min'] ) . '" max="' . esc_attr( $purchase_properties['max'] ) . '" dntmesecondfocus="true" uprice="' . esc_attr( $price ) .'">
									<input type="button" id="plus" value="+" class="plus npd-custom-right-quantity-input-set npd-custom-btn">
								</div>

								<div class="custom-btn-wrap"><img class="loader" src="' . NPD_URL . 'includes/skins/default/assets/img/loader.gif" alt="loader"><button id="npd-add-to-cart-btn" class="npd-custom-btn" data-price="' . $product->get_price() . '" data-id="'. $this->editor->item_id . '">'. $add_to_cart_label .'</button></div>
                               
								';

								echo apply_filters('npd_editor_button_nav', $btn_content);
								?>
							   

							</div>

						</div>
						
					</div>
					
				</div>
			</section>
		</div>


		<?php
		$output = ob_get_clean();
		$output = apply_filters( 'npd_editor_content', $output );
		return $output;
	}


	private function register_scripts() {
		wp_enqueue_script( 'npd-editor-skin-js', NPD_URL . 'includes/skins/default/assets/js/editor-scripts.js', array( 'jquery', 'npd-slick-js' ), '', false );
		wp_enqueue_script( 'npd-spectrum-js', NPD_URL . 'includes/skins/default/assets/js/spectrum.min.js', array( 'jquery' ), '', false );
		wp_enqueue_script( 'npd-slick-js', NPD_URL . 'includes/skins/default/assets/js/slick.min.js', array( 'jquery' ), '', false );
		wp_enqueue_script( 'npd-mCustomScrollbar-js', NPD_URL . 'includes/skins/default/assets/js/jquery.mCustomScrollbar.min.js', array( 'jquery' ), NPD_VERSION, false );
        wp_enqueue_script( 'npd-qtip-js', NPD_URL . 'public/js/jquery.qtip.min.js', array( 'jquery' ), NPD_VERSION, false );

		wp_enqueue_script( 'npd-editor-js', NPD_URL . 'public/js/editor.js', array( 'jquery', 'npd-fabric' ), '', false );

	}

	private function register_styles() {
		wp_enqueue_style( 'npd-editor-style', NPD_URL . 'includes/skins/default/assets/css/editor-styles.css', array(), NPD_VERSION, 'all' );
		wp_enqueue_style( 'npd-spectrum-css', NPD_URL . 'includes/skins/default/assets/css/spectrum.min.css', array(), NPD_VERSION, 'all' );
        wp_enqueue_style( 'npd-qtip-css', NPD_URL . 'public/css/jquery.qtip.min.css', array(), NPD_VERSION, 'all' );

		wp_enqueue_style( 'npd-slik-style', NPD_URL . 'includes/skins/default/assets/css/slick.css', array(), '', 'all' );
		wp_enqueue_style( 'npd-font-awesome', NPD_URL . 'includes/skins/default/assets/css/font-awesome.css', array(), '', 'all' );
		wp_enqueue_style( 'npd-slick-theme-style', NPD_URL . 'includes/skins/default/assets/css/slick-theme.css', array(), '', 'all' );
		wp_enqueue_style( 'npd-mCustomScrollbar-css', NPD_URL . 'includes/skins/default/assets/css/jquery.mCustomScrollbar.min.css', array(), NPD_VERSION, 'all' );
		npd_register_fonts();
	}


}
