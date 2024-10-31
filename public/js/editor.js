var NPD_EDITOR = (function($, npd_editor){
	// Action in front
	'use strict';
	//alert('bonjour');

	var npd_editor = {};
	npd_editor.canvas = {};
	npd_editor.canvas_form = {};
	npd_editor.box_center_x = false;
    npd_editor.box_center_y = false;
    npd_editor.scale_factor = false;
	npd_editor.canvas_spacing = 100;
	 //Manage convert size to px 
    npd_editor.convert_to_px = function (dimensions, unit) {
		var dimensions = dimensions;
		var unit = unit;
		if (typeof unit != 'undefined' && typeof dimensions != 'undefined') {
		    switch (unit) {
			case 'cm':
			    dimensions = dimensions * 120;
			    break;
			case 'mm':
			    dimensions = dimensions * 12;
			    break;
			case 'in':
			    dimensions = dimensions * 300;
			    break;
			case 'pt':
			    dimensions = dimensions /0.75;
			    break;
			case 'Px':
			    dimensions = dimensions;
			default:
			    break;

		    }
		    return parseFloat(dimensions).toFixed(2);
		}
    };
	

	

	npd_editor.isEmpty = function (value) {
	return typeof value == 'string' && !value.trim() || typeof value == 'undefined' || value === null;
    };


	var canvas_h, canvas_w;
	npd_editor.available_w= $('.npd-editor-preview').width();
	npd_editor.available_h= $('.npd-editor-preview').height();
	var optimal_user_h, optimal_user_w;

	var size_option = $("input:radio[name='size-opt-name']:checked").attr('id');
	if (size_option=="npd-default-opt-size") {
		npd_editor.default_user_w = parseFloat($('#npd-size-select').attr('data-width'));
		npd_editor.default_user_h = parseFloat($('#npd-size-select').attr('data-height'));
		// console.log(default_user_w,default_user_h);
		// console.log(npd_editor.isEmpty(default_user_w));
		if(npd_editor.isEmpty(npd_editor.default_user_w) === true && npd_editor.isEmpty(npd_editor.default_user_h) === true){
			npd_editor.default_user_w = parseFloat($('.npd-editor-preview').width());
			npd_editor.default_user_h = parseFloat($('.npd-editor-preview').height());
		}
	}else if (size_option=="npd-custom-opt-size") {
		npd_editor.default_user_w = parseFloat($("input:number[name='npd-custom-user-width']").val());
		npd_editor.default_user_h = parseFloat($("input:number[name='npd-custom-user-height']").val());
	}



	// Function Initialize canvas
    function init_canvas(){
    	// console.log(available_w,available_h);
    	// console.log(default_user_w,default_user_h);
    	
    	var form_bg = $("input[type='radio'][name='npd-bg-opt']:checked").attr('data-color');
    	var optimal_dimensions = npd_editor.get_optimal_canvas_dimensions(npd_editor.default_user_w,npd_editor.default_user_h);
		//console.log(available_w);
		npd_editor.canvas = new fabric.Canvas('npd-editor',{
	 		width: optimal_dimensions[0],
	 		height: optimal_dimensions[1],
	 		// width: npd_editor.available_w,
	 		// height: npd_editor.available_h,
			//backgroundColor: "#fff5ef",
			backgroundColor: form_bg,
			//backgroundColor: "#000",
		});

		npd_editor.canvas.controlsAboveOverlay = true;
        npd_editor.canvas.backgroundImageStretch = false;
        var w_bound = optimal_dimensions[0] - npd_editor.canvas_spacing;
		var h_bound = w_bound * (optimal_dimensions[1]/optimal_dimensions[0]);

		
		// console.log(w_bound);
		// console.log(h_bound);
		//npd_editor.form_construct(optimal_dimensions[0],optimal_dimensions[1]);
		var form_bg = $("input[type='radio'][name='npd-form-opt']:checked").attr('data-bg');
		npd_editor.form_construct(w_bound,h_bound,form_bg);
		//npd_editor.center_properties();
		_setCenterProperties();

		var shadow = new fabric.Shadow({
           	 color: 'green',
            blur: 20,
            offsetX: 0,
    		offsetY: 0,
    		//strokeWidth : 80,
    		opacity: 1,
    		
        });
		npd_editor.text = new fabric.Text("Testing", {
		fontSize: 40,
		fill : "green",
		fontFamily:"sans-serif",
		opacity: .5,
		//backgroundColor:"rgba(0,128,0,1)",
		shadow: shadow,
		// borderColor: '#f09',
		 strokeWidth: -1,
     	stroke: '#fff',
    	//padding: 50
		//objectCaching: true,

	});
	npd_editor.canvas.centerObject(npd_editor.text);
	npd_editor.canvas.add(npd_editor.text);
	

		npd_editor.canvas.renderAll();
    }
   
    // Function to convert size from px into another unit
    npd_editor.convert_from_px = function (dimensions, unit) {
		var dimensions = dimensions;
		var unit = unit;
		if (typeof unit != 'undefined' && typeof dimensions != 'undefined') {
		    switch (unit) {
			case 'cm':
			    dimensions = dimensions / 120;
			    break;
			case 'mm':
			    dimensions = dimensions / 12;
			    break;
			case 'in':
			    dimensions = dimensions / 300;
			    break;
			case 'pt':
			    dimensions = dimensions* 0.75;
			    break;
			case 'Px':
			    dimensions = dimensions;
			default:
			    break;

		    }
		    return parseFloat(dimensions).toFixed(2);
		}
    };


    // function de construction des bounding box

    npd_editor.form_construct = function(width, height,form_bg){
		//var form_bg = $("input[type='radio'][name='npd-form-opt']:checked").attr('data-bg');
		//var form_bg = "http://localhost/cj-scotch/wp-content/plugins/vpc-aw-cj/public/aw-assets/images/dials-bg/dial-bg.svg";
		console.log(width); 
		console.log(form_bg);
		console.log();
		var shadow = {
		    color: 'red',
		    blur: 0,
		    offsetX: 100,
		    offsetY: 100,
		    opacity: 1
		}
		fabric.loadSVGFromURL(form_bg, function(objects, options) {
	      objects.forEach(function(svg) {
	        svg.set({
	            hasBorders: true,
	          	hasControls: false,
	          	lockMovementX: true,
	          	lockMovementY: true,
	            originX: 'center',
	            originY: 'center',
	            evented: false,
		          opacity: 1,
		          // fill: "#000000",
		           // backgroundColor: '#ffffff',
		           selectable: false,
		           objectCaching: false,
		          // boundingBox: true,
		        //     stroke: '#07C', 
      				// strokeWidth: 50,
		           shadow: shadow,
		          // viewBoxHeight:height,
		          // viewBoxWidth: width,

	         }); 
	        // var width = width * 2;
	        // var height = height * 2;
	        // svg.scaleToWidth(width);
	        // svg.scaleToHeight(height);
	        svg.scaleToWidth(width);
	        svg.scaleToHeight(height);
	       // svg.setShadow(shadow);
	        npd_editor.canvas_form = svg;



	        console.log(npd_editor.canvas_form.d);
	        //npd_editor.canvas_form.path.forEach(function(path) {path.fill = "red"});

	       	//npd_editor.canvas_form.globalCompositeOperation = 'source-atop';

	        // console.log(npd_editor.canvas_form.viewBoxHeight);
	        // console.log(npd_editor.canvas_form.viewBoxWidth);
	        //npd_editor.canvas.clipPath = npd_editor.canvas_form;

	        //npd_editor.canvas.add(npd_editor.canvas_form);
    		npd_editor.canvas.centerObject(npd_editor.canvas_form);

    		npd_editor.canvas.clipPath = npd_editor.canvas_form;
    		
    	// 	npd_editor.canvas.clipTo = function (ctx) {
			  //     npd_editor.canvas_form.render(ctx);
			  // };

	      });
	   });


		// var path = new fabric.Path('M 272.70141,238.71731 \
		//     C 206.46141,238.71731 152.70146,292.4773 152.70146,358.71731  \
		//     C 152.70146,493.47282 288.63461,528.80461 381.26391,662.02535 \
		//     C 468.83815,529.62199 609.82641,489.17075 609.82641,358.71731 \
		//     C 609.82641,292.47731 556.06651,238.7173 489.82641,238.71731  \
		//     C 441.77851,238.71731 400.42481,267.08774 381.26391,307.90481 \
		//     C 362.10311,267.08773 320.74941,238.7173 272.70141,238.71731  \
		//     z ', {
		//     	stroke: '#f55',
		//       fill: "transparent",
		//       top: 50,
		//       left: 50,
		//       objectCaching: false
		//     });
		  
		//   path.selectable = false;
		//   npd_editor.canvas.add(path);
		//   npd_editor.canvas.renderAll();
		//   npd_editor.canvas.clipTo = function (ctx) {
		//       path.render(ctx);
		//   };

		  
		  //npd_editor.canvas.clipPath = npd_editor.canvas_form;



		
		//npd_editor.canvas.controlsAboveOverlay = true;
		//npd_editor.canvas.setOverlayImage(form_bg, npd_editor.canvas.renderAll.bind(npd_editor.canvas));

		//npd_editor.canvas.setBackgroundImage(form_bg, npd_editor.canvas.renderAll.bind(npd_editor.canvas));
		
		 //npd_editor.canvas.renderAll();


	}
    // Prend deux parametre le width et le heigth du boundingbox
    npd_editor.get_optimal_canvas_dimensions = function(default_user_w,default_user_h){

    	var default_user_w = npd_editor.convert_to_px(default_user_w,npd.designer_unit);
    	var default_user_h = npd_editor.convert_to_px(default_user_h,npd.designer_unit);
    	var ratio ;
    	optimal_user_w = Number(default_user_w) + Number(npd_editor.canvas_spacing);
		optimal_user_h = (optimal_user_w * default_user_h) / default_user_w;
		// optimal_user_w = default_user_w;
		// optimal_user_h = default_user_h;

			if(optimal_user_w <= npd_editor.available_w){
				canvas_w = optimal_user_w;
			}
			else{
				canvas_w = npd_editor.available_w;
			}

			if(optimal_user_w > optimal_user_h){
				ratio = optimal_user_h/optimal_user_w;
			}
			else if(optimal_user_w < optimal_user_h){
				ratio = optimal_user_w/optimal_user_h;
			}
			else{
				ratio = 1;
			}
			canvas_h = canvas_w * ratio;
			if(canvas_h > npd_editor.available_h){
				canvas_h = npd_editor.available_h;
				ratio = optimal_user_w / optimal_user_h
				canvas_w = canvas_h * ratio; 
			}
			else{
				canvas_h = canvas_h;
				canvas_w = canvas_w;
			}



			console.log(canvas_w);
			console.log(canvas_h);
			return [canvas_w, canvas_h];
    }

    init_canvas();
    

    $( window ).load(function() {
    	var form_bg = $("input[type='radio'][name='npd-form-opt']:checked").attr('data-bg');
    	//change_img_tag_on_svg_tag(form_bg, image, type);
    });

    npd_editor.get_img_optimal_fit_dimensions = function (img, max_width, max_height)
	{
	    var w = img.width;
	    var h = img.height;

	    if (w < max_width && h < max_height)
		return [w, h];

	    var ratio = w / h;
	    w = max_width;
	    h = max_width / ratio;

	    if (h > max_height)
	    {
		h = max_height;
		w = max_height * ratio;
	    }
	    return wp.hooks.applyFilters('NPD_EDITOR.npd_img_dimensions', [w, h], img);
	}

	npd_editor.centerObject = function (object)
	{
	    npd_editor.centerObjectV(object);
	    npd_editor.centerObjectH(object);
	}

	npd_editor.centerObjectH = function (object)
	{
	    if (npd_editor.box_center_x)
	    {
			if (npd_editor.scale_factor && npd.clip_x)
			{
			    object.set("left", npd_editor.box_center_x / npd_editor.scale_factor);
			} 
			else
			    object.set("left", npd_editor.box_center_x);
			} 
		else
	    {
			//var realWidth = object.getWidth();
			var realWidth = object.width;
			console.log(realWidth);
			console.log(npd_editor.scale_factor);
			//We make sure we're making our calculations based on the scaled width
			if (npd_editor.scale_factor)
			    realWidth = realWidth * npd_editor.scale_factor;
			var left = (parseFloat(npd.canvas_w)) / 2;

			if (npd_editor.box_center_x)
			    left = npd_editor.box_center_x - realWidth / 2;
			if (object.originX == 'left')
			    left = left - (realWidth / 2);

				console.log(left);
				object.set("left", left);
		}
	}

	npd_editor.centerObjectV = function (object)
	{
	    if (npd_editor.box_center_y)
	    {
		if (npd_editor.scale_factor && npd.clip_y)
		    object.set("top", npd_editor.box_center_y / npd_editor.scale_factor);
		else
		    object.set("top", npd_editor.box_center_y);
	    } else
	    {
	    	console.log(object.height);
		//var realHeight = object.getHeight();
		var realHeight = object.height;
		//We make sure we're making our calculations based on the scaled height
		if (npd_editor.scale_factor)
		    realHeight = realHeight * npd_editor.scale_factor;
		var top = (parseFloat(npd_editor.canvas.getHeight())) / 2;
		if (npd_editor.box_center_y)
		    top = parseFloat(npd_editor.box_center_y) - realHeight / 2;
		object.set("top", top);
	    }
	}

	function _setCustomProperties(object)
	{
	    object.toObject = (function (toObject) {
		return function () {
		    return fabric.util.object.extend(toObject.call(this), {
			lockMovementX: this.lockMovementX,
			lockMovementY: this.lockMovementY,
			lockScalingX: this.lockScalingX,
			lockScalingY: this.lockScalingY,
			lockDeletion: this.lockDeletion,
			price: this.price,
			originalText: this.originalText,
			//boundingBox: this.boundingBox,
			radius: this.radius,
			spacing: this.spacing,
			svgSrc: this.svgSrc,
			is_grid_group: this.is_grid_group,
			is_top_line: this.is_top_line,
			is_left_line: this.is_left_line,
			is_top_size: this.is_top_size,
			is_left_size: this.is_left_size
		    });
		};
	    })(object.toObject);
	}
	npd_editor.setCustomProperties = function (object)
	{
	    _setCustomProperties(object);
	}

	

	function _setCenterProperties(){
		var optimal_dimensions = npd_editor.get_optimal_canvas_dimensions(npd_editor.default_user_w,npd_editor.default_user_h);
	    console.log(optimal_dimensions);
	    //npd_editor.canvas.clipTo = null;
	    var scaleFactor = optimal_dimensions[0] / optimal_dimensions[0];
	    console.log(scaleFactor);
	    if (scaleFactor != 1)
			npd_editor.scale_factor = scaleFactor;
	    if (npd.clip_w && npd.clip_h && npd.clip_w > 0 && npd.clip_h > 0 && npd.clip_type == "rect")
	    {
			var clip_x = (optimal_dimensions[0] - npd.clip_w * scaleFactor) / 2;
			if (npd.clip_x || npd.clip_x == "0")
			{
			    clip_x = npd.clip_x * scaleFactor;

			    npd_editor.box_center_x = parseFloat(clip_x) + parseFloat(npd.clip_w * scaleFactor) / 2;
			    console.log(npd_editor.box_center_x);
			} 
			else if (scaleFactor != 1)
		    	npd_editor.box_center_x = optimal_dimensions[0] / 2;
			else
		    	npd_editor.box_center_x = optimal_dimensions[0] / 2;

			var clip_y = (optimal_dimensions[1] - npd.clip_h * scaleFactor) / 2;
			if (npd.clip_y || npd.clip_y == "0")
			{
			    clip_y = npd.clip_y * scaleFactor;
			    npd_editor.box_center_y = parseFloat(clip_y) + parseFloat(npd.clip_h * scaleFactor) / 2;
			} 
			else if (scaleFactor != 1)
		    	npd_editor.box_center_y = npd.canvas_h / 2;
			else
		    	npd_editor.box_center_y = optimal_dimensions[1] / 2;
		}

	}

	npd_editor.center_properties = function (){
		
	    _setCenterProperties();
	}


	return npd_editor;
//})(jQuery);
}(jQuery, NPD_EDITOR));
