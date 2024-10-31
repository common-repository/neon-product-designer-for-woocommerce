var NPD_EDITOR = (function($, npd_editor){

	$(document).ready(function () {
		$('.npd-clipart-item').on('click', function ()
	    {
			var medium_url = $(this).find('.npd-img-gallery-item').attr("data-img");
			// if (typeof medium_url == "undefined")
			//     medium_url = $(this).attr("data-original");
			var price = $(this).data("price");
			var clipart_size = $(this).data("size");
			var clipart_height = $(this).data("height");
			var clipart_width = $(this).data("width");
			add_img_on_editor(medium_url, price, clipart_size, clipart_height, clipart_width);
			wp.hooks.doAction('NPD_EDITOR.after_click_clipart');
		});

		function add_img_on_editor(url, price, clipart_size, clipart_height, clipart_width)
		{
		    var ext = url.split('.').pop();
		    if (typeof price === "undefined")
				price = 0;

		    if (typeof clipart_size === "undefined")
				clipart_size = "no";

		    if (typeof clipart_height === "undefined")
				clipart_height = 0;

		    if (typeof clipart_width === "undefined")
				clipart_width = 0;
		    var npd_canvas = npd_editor.canvas;
		    if (ext == "svg")
		    {
				fabric.loadSVGFromURL(url, function (objects, options) {
				    var obj = fabric.util.groupSVGElements(objects, options);
				    optimize_img_width(obj);
				    //wpd_editor.setCustomProperties(obj);
				    obj.set("price", price);
				    obj.set("originX", "center");
				    obj.set("originY", "center");
				    obj.set("svgSrc", url);
				    wp.hooks.doAction('NPD_EDITOR.before_adding_img_on_canvas', obj, npd_canvas);
				    console.log(clipart_size);
				    if (npd_editor.box_center_x)
				    {
						npd_editor.centerObject(obj);
						if ("yes" === clipart_size) {
						    npd_editor.canvas.add(
							    obj.set(
								    {
									width: clipart_width,
									height: clipart_height,
								    })
							    ).calcOffset().renderAll();
						} else {
						    npd_editor.canvas.add(obj).calcOffset().renderAll();
						}
				  	} 
				  	else
				    {
						if ("yes" === clipart_size) {
					    	npd_editor.canvas.add(obj.set(
						    {
								width: clipart_width,
								height: clipart_height,
								angle: 0
							    }));
							} else {
							    npd_editor.canvas.add(obj);
							}
							npd_editor.centerObject(obj);
							npd_editor.canvas.setActiveObject(obj);
							npd_editor.canvas.calcOffset().renderAll();

				    }
				    wp.hooks.doAction('NPD_EDITOR.after_adding_img_on_canvas', obj, npd_canvas);
				    obj.setCoords();
				    //wpd_editor.save_canvas();
				});

		    } 
		    else{
				
				fabric.Image.fromURL(url, function (img)
				{
				    optimize_img_width(img);
				    npd_editor.setCustomProperties(img);
				    img.set("price", price);
				    img.set("originX", "center");
				    img.set("originY", "center");
				    wp.hooks.doAction('NPD_EDITOR.before_adding_img_on_canvas', img, npd_canvas);
				    console.log(npd_editor.box_center_x);
				    if (npd_editor.box_center_x)
				    {
					if ("yes" === clipart_size) {
					    npd_editor.canvas.add(img.set(
						    {
							width: clipart_width,
							height: clipart_height,
							angle: 0
						    })
						    ).renderAll();
					} else {
					    npd_editor.canvas.add(img.set(
						    {
							angle: 0
						    })
						    ).renderAll();
					}
					npd_editor.centerObject(img);
					npd_editor.canvas.calcOffset().renderAll();
				    } else
				    {
					if ("yes" === clipart_size) {
					    npd_editor.canvas.add(img.set(
						    {
							width: clipart_width,
							height: clipart_height,
							angle: 0
						    })
						    );
					} else {
					    npd_editor.canvas.add(img.set(
						    {
							angle: 0
						    })
						    );
					}

						npd_editor.centerObject(img);
						npd_editor.canvas.setActiveObject(img);
						npd_editor.canvas.calcOffset().renderAll();
				    }
				    wp.hooks.doAction('NPD_EDITOR.after_adding_img_on_canvas', img, npd_canvas);

				    img.setCoords();
				    //wpd_editor.save_canvas();
				}, {crossOrigin: 'anonymous'});
	    }

	}
    function optimize_img_width(obj)
	{
	    var available_canvas_w = npd_editor.canvas.getWidth();
	    var available_canvas_h = npd_editor.canvas.getHeight();

	    var displayable_area_width = available_canvas_w - npd_editor.canvas_spacing;
		var displayable_area_height = displayable_area_width * (available_canvas_h/available_canvas_w);

	 //    if (wpd.clip_w && wpd.clip_h && wpd.clip_w > 0 && wpd.clip_h > 0 && wpd.clip_type == "rect")
	 //    {
		// 	displayable_area_width = wpd.clip_w;
		// 	displayable_area_height = wpd.clip_h;
	 //    } else if (wpd.clip_r && wpd.clip_r > 0 && wpd.clip_type == "arc")
	 //    {
		// displayable_area_width = wpd.clip_r;
		// displayable_area_height = wpd.clip_r;
	 //    }
	    var dimensions = npd_editor.get_img_optimal_fit_dimensions(obj, displayable_area_width, displayable_area_height);
	    var scaleW = displayable_area_width / dimensions[0];
	    var scaleH = displayable_area_height / dimensions[1];
	    if (scaleW > scaleH)
		obj.scaleToWidth(dimensions[0]);
	    else
		obj.scaleToHeight(dimensions[1]);
	    return dimensions;
	}

	//Uplaods scripts 
	$(".native-uploader #userfile").change(function (e) {
	    var file = $(this).val().toLowerCase();
	    if (file != "")
	    {
		$("#userfile_upload_form").ajaxForm({
		    success: upload_image_callback
		}).submit();
	    }
	});

	function upload_image_callback(responseText, statusText, xhr, form)
	{
	    if (npd_editor.is_json(responseText))
	    {
		var response = $.parseJSON(responseText);

		if (response.success)
		{
		    
			$("#npd-uploaded-img").append(response.message);
		   

		    if (response.img_url)
		    {
			
			    add_img_on_editor(response.img_url, 0);
		    }
		    wp.hooks.applyFilters('npd.after_upload_image');
		} else
		    alert(response.message);
	    } else
			$("#debug").html(responseText);

	    	$("#userfile").val("");
	}


	npd_editor.is_json = function (data)
	{
	    if (/^[\],:{}\s]*$/.test(data.replace(/\\["\\\/bfnrtu]/g, '@').
		    replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
		    replace(/(?:^|:|,)(?:\s*\[)+/g, '')))
		return true;
	    else
		return false;
	}


	});



return npd_editor;
}(jQuery, NPD_EDITOR));