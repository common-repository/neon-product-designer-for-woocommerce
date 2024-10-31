var NPD_EDITOR = (function($, npd_editor){

	$(document).ready(function(){
		$(document).on("change","input[type='radio'][name='npd-form-opt']", function(){
			console.log($(this));
			var form_bg = $(this).attr('data-bg');
			var optimal_dimensions = npd_editor.get_optimal_canvas_dimensions(npd_editor.default_user_w,npd_editor.default_user_h);
			var w_bound = optimal_dimensions[0] - npd_editor.canvas_spacing;
			var h_bound = w_bound * (optimal_dimensions[1]/optimal_dimensions[0]);
			npd_editor.form_construct(w_bound,h_bound,form_bg);

		});

		$(document).on("change","input[type='radio'][name='npd-bg-opt']", function(){
			// console.log($(this));
			$("input[type='radio'][name='npd-bg-opt']").attr("checked",false);
			$(this).attr("checked",true);
			var form_bg = $(this).attr('data-color');
			console.log(form_bg);
			npd_editor.canvas.setBackgroundColor(form_bg);
			npd_editor.canvas.renderAll();
			// var optimal_dimensions = npd_editor.get_optimal_canvas_dimensions(npd_editor.default_user_w,npd_editor.default_user_h);
			// var w_bound = optimal_dimensions[0] - npd_editor.canvas_spacing;
			// var h_bound = w_bound * (optimal_dimensions[1]/optimal_dimensions[0]);
			//npd_editor.form_construct(w_bound,h_bound,form_bg);

		});


	});



return npd_editor;
}(jQuery, NPD_EDITOR));