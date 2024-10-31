var NPD_EDITOR = (function($, npd_editor){
	console.log( npd_editor );
	$( document ).ready(
		function(){
			$( '.npd-size-picker ul li' ).on(
				'click',
				function() {
					var get_user_size_mod = $( ".size-opt-radio input[name='size-opt-name']:checked" ).attr( "id" );
					$( '.npd-size-picker ul li' ).removeClass( 'is_selected' );
					$( this ).addClass( 'is_selected' );
					$( "#npd-editor-size-w-wrap .size-w" ).html( $( this ).attr( "data-width" ) + '' + npd.designer_unit );
					$( "#npd-editor-size-h-wrap .size-h" ).html( $( this ).attr( "data-height" ) + '' + npd.designer_unit );

					// $("#npd-editor-size-we-wrap .size-we").html( available_we + '' + npd.designer_unit);

					if (get_user_size_mod == 'npd-default-opt-size') {
						var get_user_w = $( this ).attr( "data-width" );
						var get_user_h = $( this ).attr( "data-height" );
						console.log( get_user_w,get_user_h );
						update_canvas_behaviour( get_user_w,get_user_h );
					}
				}
			);

			// setup typing functions for key up event
			var typingTimer;// timer identifier
			var doneTypingInterval = 1000;  // time in ms, 1 second for example
			var user_custom_h_field = $( '.npd-size-custom-user input[name="npd-custom-user-height"]' );
			var user_custom_w_field = $( '.npd-size-custom-user input[name="npd-custom-user-width"]' );

			// on keyup, start the countdown
			user_custom_w_field.on(
				'keyup',
				function () {
					clearTimeout( typingTimer );
					typingTimer = setTimeout( doneTyping, doneTypingInterval );
				}
			);
			user_custom_h_field.on(
				'keyup',
				function () {
					clearTimeout( typingTimer );
					typingTimer = setTimeout( doneTyping, doneTypingInterval );
				}
			);

			// on keydown, clear the countdown
			user_custom_w_field.on(
				'keydown',
				function () {
					clearTimeout( typingTimer );
				}
			);
			user_custom_h_field.on(
				'keydown',
				function () {
					clearTimeout( typingTimer );
				}
			);

			function doneTyping(){
				var get_user_size_mod = $( ".size-opt-radio input[name='size-opt-name']:checked" ).attr( "id" );
				if (get_user_size_mod === 'npd-custom-opt-size') {
					var get_user_w = $( '.npd-size-custom-user input[name="npd-custom-user-width"]' ).val();
					var get_user_h = $( '.npd-size-custom-user input[name="npd-custom-user-height"]' ).val();

					$( "#npd-editor-size-w-wrap .size-w" ).html( get_user_w + '' + npd.designer_unit );
					$( "#npd-editor-size-h-wrap .size-h" ).html( get_user_h + '' + npd.designer_unit );

					if (npd_editor.isEmpty( get_user_w ) === false && npd_editor.isEmpty( get_user_w ) === false) {
						update_canvas_behaviour( get_user_w,get_user_h );
					}

				}
			}
		}
	);

	// function to update canvas behaviour when user change editor size
	function update_canvas_behaviour(get_user_w,get_user_h){
		console.log( get_user_w,get_user_h );
		var optimal_dimensions = npd_editor.get_optimal_canvas_dimensions( get_user_w,get_user_h );
		npd_editor.canvas.setDimensions( {width:optimal_dimensions[0], height:optimal_dimensions[1]} );

		// var get_canvas_form = npd_editor.canvas_form;
		// var old_bounding = get_canvas_form.canvas;
		// var old_bounding = old_bounding._objects[0];
		// npd_editor.canvas.remove(old_bounding);

		var bounding_w = optimal_dimensions[0] - npd_editor.canvas_spacing;
		var bounding_h = bounding_w * (optimal_dimensions[1] / optimal_dimensions[0]);
		console.log( optimal_dimensions[0] );
		console.log( optimal_dimensions[1] );
		// console.log(bounding_w);
		// console.log(bounding_h);

		// setTimeout(function(){

			// npd_editor.canvas_form.scaleToWidth(optimal_dimensions[0]);
		// npd_editor.canvas_form.scaleToHeight(optimal_dimensions[1]);
		// npd_editor.canvas.centerObject(npd_editor.canvas_form);

		// }, 3000);

		// npd_editor.canvas_form.width(bounding_w);
		// npd_editor.canvas_form.height(bounding_h);

		// var bounding_w = bounding_w * 2;
		// var bounding_h = bounding_h * 2;

		npd_editor.canvas_form.scaleToHeight( bounding_h );
		npd_editor.canvas_form.scaleToWidth( bounding_w );
		npd_editor.canvas.centerObject( npd_editor.canvas_form );
		 npd_editor.canvas.centerObject( npd_editor.text );
		npd_editor.canvas.renderAll();
		// npd_editor.form_construct(bounding_w,bounding_h);
	}

	return npd_editor;
}(jQuery, NPD_EDITOR));
