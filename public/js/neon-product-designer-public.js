(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(document).ready(function () {

		$(".npd-qty").keypress(function (e) {
            if (e.which < 48 || e.which > 57) {
                return(false);
            }
        });

		$(".single_variation_wrap").on("show_variation", function (event, variation) {
            // Fired when the user selects all the required dropdowns / attributes
            // and a final variation is selected / shown
            var variation_id = $("input[name='variation_id']").val();
            if (variation_id)
            {
                $(".npd-buttons-wrap-variation").hide();
                $(".npd-buttons-wrap-variation[data-id='" + variation_id + "']").show();

                if (typeof hide_cart_button !== 'undefined') {
                    if ($(".npd-buttons-wrap-variation[data-id='" + variation_id + "']").length > 0 && hide_cart_button === 1) {
                        $(".npd-buttons-wrap-variation").parent().find('.add_to_cart_button').hide();
                        $(".npd-buttons-wrap-variation").parent().find('.single_add_to_cart_button').hide();
                    } else {
                        $(".npd-buttons-wrap-variation").parent().find('.add_to_cart_button').show();
                        $(".npd-buttons-wrap-variation").parent().find('.single_add_to_cart_button').show();
                    }
                }

            }
        });

        $(".single_variation_wrap").on("hide_variation", function (event, variation) {
            console.log("hide");
            $(".npd-buttons-wrap-variation").hide();
        });

		var cartForm = $('.woocommerce-cart-form');
		cartForm.contents().each(function() {
			if (this.nodeType === Node.TEXT_NODE) {
				this.textContent = "";
			}
		});
		var cartForm = $('.woocommerce-cart-form');
		cartForm.contents().each(function() {
			// Vérifier si le nœud est un nœud de texte
			if (this.nodeType === Node.TEXT_NODE) {
			  // Modifier le contenu du nœud de texte
			  this.textContent = "";
			}
			
		});
		var productInCartForm = $('.woocommerce-cart-form .woocommerce-cart-form__cart-item .product-name');
		productInCartForm.contents().each(function() {
			// Vérifier si le nœud est un nœud de texte
			if (this.nodeType === Node.TEXT_NODE) {
			  // Modifier le contenu du nœud de texte
			  this.textContent = "";
			}
			
		});

	});


	 

})( jQuery );
