(function ($) {
  "use strict";
  $(document).ready(function () {
    var npd_editor = {};
    npd_editor.is_json = function (data) {
      if (/^[\],:{}\s]*$/.test(data.replace(/\\["\\\/bfnrtu]/g, '@').
        replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
        replace(/(?:^|:|,)(?:\s*\[)+/g, '')))
        return true;
      else
        return false;
    }

    var cart_item_key = "";

    if (typeof npd_design_data !== "undefined") {
      if (typeof npd_design_data.rules_options !== "undefined" && npd_editor.is_json(npd_design_data.rules_options)) var npd_rules_options = JSON.parse(npd_design_data.rules_options);
      else var npd_rules_options = [];

      if (typeof npd_design_data.unit !== "undefined") var npd_unit = npd_design_data.unit;
      else var npd_unit = "cm";

      if (typeof npd_design_data.edit !== "undefined" && npd_design_data.edit.key != "" && npd_editor.is_json(npd_design_data.edit.data)) {
        var edit_data = JSON.parse(npd_design_data.edit.data);
        var default_option = ["npd-text", "npd-text-align", "npd-txt-size", "npd-font", "npd-additional-note", "npd-scene"]
        if (typeof edit_data["npd-text"] !== "undefined")
          $("#npd-text-field").text(edit_data["npd-text"]);

        if (typeof edit_data["npd-text-align"] !== "undefined")
          $(".npd-align-btn-wrap input[value='" + edit_data["npd-text-align"] + "']").click();


        if (typeof edit_data["npd-txt-size"] !== "undefined")
          $(".npd-text-size-wrap input[value='" + edit_data["npd-txt-size"] + "']").click();

        if (typeof edit_data["npd-font"] !== "undefined") {
          $(".npd-font-item[data-value='" + edit_data["npd-font"] + "'] input").click();
          $("#npd-font-selector").val(edit_data["npd-font"]);
        }

        if (typeof edit_data["npd-additional-note"] !== "undefined")
          $(".npd-additional-note-field[name='npd-additional-note']").val(edit_data["npd-additional-note"]);

        if (typeof edit_data["npd-scene"] !== "undefined")
          $(".npd-scene-content input[value='" + edit_data["npd-scene"] + "']").click();

        if (typeof edit_data["npd-neon-color"] !== "undefined")
          $(".npd-neon-color-wrap input[value='" + edit_data["npd-neon-color"] + "']").click();

        $.each(edit_data, function (index, value) {
          if ($.inArray(index, default_option) == -1) {
            var selecter = $(".npd-additional-options-form input[name='" + index + "']");
            selecter.each(function () {
              if ($(this).val() == value && $(this).attr("type") == "radio") {
                $(this).attr("checked", true);
                $(this).click();
              }
              else if ($(this).attr("type") == "text") $(this).val(value);
            })
          }
        })

        $("#npd-qty").val(npd_design_data.edit.qty);

        cart_item_key = npd_design_data.edit.key;
      }
    }

    //Manage convert size to px 
    window.convert_to_px = function (dimensions, unit) {
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
            dimensions = dimensions / 0.75;
            break;
          case 'Px':
            dimensions = dimensions;
          default:
            break;

        }
        return parseFloat(dimensions).toFixed(2);
      }
    };

    console.log("Custom Neon Running");
    var j = jQuery.noConflict();

    //setup typing functions for key up event
    var typingTimer;//timer identifier
    var doneTypingInterval = 500;  //time in ms, 1 second for example
    var user_custom_label = $('#npd-text-field');

    //on keyup, start the countdown
    user_custom_label.on('keyup', function () {
      clearTimeout(typingTimer);
      typingTimer = setTimeout(doneTyping, doneTypingInterval);
    });

    //on keydown, clear the countdown 
    user_custom_label.on('keydown', function () {
      clearTimeout(typingTimer);
    });

    $("#npd-text-render-wrap").css("font-size",  "60px");

    function doneTyping() {
      var user_text = $("#npd-text-field").val();
      var user_text = user_text.replace(/<br\s*\/?>/mg, "\n");
      resize_to_fit($(".npd-text-render-container"), $("#npd-text-render-wrap"));
      //var editor_size = change_editor_size(npd_txt_width, npd_txt_height);
      $("#npd-text-render-wrap").css("font-size",  "60px");
      $("#npd-text-render-wrap").html(user_text);
      npd_text_dimensions();

    }

    var element3 = $('#npd-bottom-limit');
    var limitY = 0;

    function npd_preview_follow_scroll() {

      var follow_allow = window.npd.follow_scroll;
      if (follow_allow == 1) {

        var screen = $(window).width();
        if (screen > 767) {
          var element = $('.npd-editor-right-side'); // element2 = $('.VPC_ln_Skin .vpc-component .vpc-component-content');
          var elementY, fullHeight;

          if (typeof element3 !== 'undefined' && element.length) {
            elementY = element.offset().top;
            fullHeight = element.innerHeight(); // + 20; //  20 for slider nav margin top
          }
          //console.log("element3.length : " + element3.length);
          if (typeof element3 !== 'undefined' && element3.length) {
            limitY = element3.offset().top;
          }

          var scrollTop;
          // Space between element and top of screen (when scrolling)
          var topMargin = 0;

          // Should probably be set in CSS; but here just for emphasis
          // element.css('position', 'relative');
          if (typeof element3 !== 'undefined' && element.length) {

            window.addEventListener('scroll', function (e) {
              scrollTop = $(window).scrollTop();

              if (scrollTop < elementY) {
                element.stop(false, false).animate({
                  top: 0
                }, 100);

              }
              if (scrollTop > elementY && (scrollTop + fullHeight + topMargin) < limitY) {
                element.stop(false, false).animate({
                  top: scrollTop - elementY + topMargin
                }, 100);

              }
              if (limitY < (scrollTop + fullHeight + topMargin)) {
                element.stop(false, false).animate({
                  top: 'auto'
                }, 100);

              }

            });

          }
        }


      }



    }

    window.get_custom_option_price_total = function (form_id, fields) {
      var total_price = 0;
      $(form_id).find('[name]').each(function (index, value) {
        var that = $(this),
          name = that.attr('name'),
          type = that.prop('type');

        if (type == 'input') {
          $(that).find('[value]').each(function (index, value) {
            var option = $(this);
            var price = option.attr('data-price');
            var value = option.attr('value');
            for (var i in fields) {
              if (name == i && value == fields[i]) {
                if (undefined !== price && '' !== price) {

                  total_price += parseFloat(price);
                }
              }
            }
          });
        }
        else if (type == 'radio') {
          var price = that.attr('data-price');
          for (var i in fields) {
            if (name == i) {
              if (typeof (fields[i]) == 'object') {
                var options = fields[i];
                for (var j in options) {
                  if (value.value == options[j]) {
                    if (undefined !== price && '' !== price) {
                      total_price += parseFloat(price);
                    }
                  }
                }
              } else {
                if (value.value == fields[i]) {
                  if (undefined !== price && '' !== price) {
                    total_price += parseFloat(price);
                  }
                }
              }
            }
          }
        }
        else {
          var price = that.attr('data-price');
          var value = that.val();
          if (value.length > 0) {
            for (var i in fields) {
              if (name == i) {
                if (undefined !== price && '' !== price) {
                  total_price += parseFloat(price);
                }
              }
            }
          }
        }

      });
      return total_price;
    }

    window.update_price = function () {
      if (typeof npd == 'undefined' || (!$("#npd-add-to-cart-btn").length))
        return;

      if ($("#npd-add-to-cart-btn").data("price")) {
        if (npd.decimal_separator == ',')
          var total_price = parseFloat($("#npd-add-to-cart-btn").data("price").toString().replace(',', '.'));
        else
          var total_price = parseFloat($("#npd-add-to-cart-btn").data("price"));
      }
      var form_data = $('form.npd-additional-options-form').serializeJSON();
      var form_data2 = $('form.npd-dimension-section').serializeJSON();
      //var form_price = get_form_total('form.formbuilt', form_data);
      var additional_options_price = get_custom_option_price_total('form.npd-additional-options-form', form_data);
      additional_options_price += get_custom_option_price_total('form.npd-dimension-section', form_data2);
      var total_option_price = 0;
      var configurator_array = [];
      if (!total_price)
        total_price = 0;




      total_price += total_option_price;
      total_price += additional_options_price;
      total_price = wp.hooks.applyFilters('vpc.total_price', total_price);
      var vpc_qty = $('#npd-qty').val();
      total_price *= vpc_qty;
      $(".npd-total-price").html(accounting.formatMoney(total_price));
      return {
        additional: additional_options_price,
        regular: total_price
      }
    }

    window.onload = function () {
      initialise_editor();
      update_price();
      setTimeout(function () {
        npd_preview_follow_scroll();
      }, 2000);

    };
    onload();

    $('.npd-infobule[title]').qtip({
      content: {
        text: false // Use each elements title attribute
      },
      position: {
        my: "bottom center",
        at: "top center",
      },
      style: 'npd-custom-tooltip' // Give it some style
    });

    $('.npd-custom-option-txt-wrap[title]').qtip({
      content: {
        text: false // Use each elements title attribute
      },
      position: {
        my: "bottom center",
        at: "top center",
      },
      style: 'npd-custom-tooltip' // Give it some style
    });

    $('.npd-custom-option-img-wrap[title]').qtip({
      content: {
        text: false // Use each elements title attribute
      },
      position: {
        my: "bottom center",
        at: "top center",
      },
      style: 'npd-custom-tooltip' // Give it some style
    });

    $(".npd-custom-option-img-wrap,.npd-scene-bg-wrap").each(function () {
      if (typeof $(this).attr("title") != "undefined") {
        var opt_title = "<div>" + $(this).attr("title") + "</div>";
      }
      else {
        var opt_title = "";
      }
      if (typeof $(this).attr("data-bg") != "undefined" && $(this).attr("data-bg") != "") {
        var opt_img = "<img src='" + $(this).attr("data-bg") + "' class='dit'>";
      }
      else {
        var opt_img = "";
      }

      $(this).qtip({
        content: {
          text: function (api) {
            return opt_img + opt_title;
          }
        },
        position: {
          my: "bottom center",
          at: "top center",
        },
        style: 'npd-custom-tooltip'
      });
    });






    $(".npd-light-btn-wrap > span").click(function () {
      $(".npd-light-btn-wrap > span").removeClass("is_activated");
      $(this).addClass('is_activated');
      var color = $("input[name='npd-neon-color']:checked").val();
      get_light_color(color);

    });

    function get_light_color(color) {
      //var default_color = $("#npd-editor-container").attr("data-color");
      var default_color = window.npd.default_color;
      var default_color_behaviour = window.npd.default_color_behaviour;
      // var default_color_behaviour = $("#npd-editor-container").attr("data-color-behaviour");
      var light_selector = $(".npd-light-btn-wrap > span.is_activated").attr("id");
      if (default_color_behaviour == 'light-color') {
        var neonHighlight = default_color;
        var neonHighlightColor = color;
        var shadow = '0 0 10px ' + neonHighlight + ',0 0 20px ' + neonHighlight + ',0 0 30px ' + neonHighlight + ',0 0 40px ' + neonHighlightColor + ',0 0 70px ' + neonHighlightColor + ',0 0 80px ' + neonHighlightColor + ',0 0 100px ' + neonHighlightColor;
        $("#npd-text-render-wrap").css({ "text-shadow": shadow, "color": color });
      }
      else if (default_color_behaviour == 'same-color') {
        var neonHighlight = color;
        var neonHighlightColor = color;
        var shadow = '0 0 10px ' + neonHighlight + ',0 0 20px ' + neonHighlight + ',0 0 30px ' + neonHighlight + ',0 0 40px ' + neonHighlightColor + ',0 0 70px ' + neonHighlightColor + ',0 0 80px ' + neonHighlightColor + ',0 0 100px ' + neonHighlightColor;
        $("#npd-text-render-wrap").css({ "text-shadow": shadow, "color": color });
      }
      else if (default_color_behaviour == 'txt-color') {
        var neonHighlight = color;
        var neonHighlightColor = color;
        var shadow = '0 0 10px ' + neonHighlight + ',0 0 20px ' + neonHighlight + ',0 0 30px ' + neonHighlight + ',0 0 40px ' + neonHighlightColor + ',0 0 70px ' + neonHighlightColor + ',0 0 80px ' + neonHighlightColor + ',0 0 100px ' + neonHighlightColor;
        $("#npd-text-render-wrap").css({ "text-shadow": shadow, "color": default_color });
      }
      else {
        var neonHighlight = default_color;
        var neonHighlightColor = color;
        var shadow = '0 0 10px ' + neonHighlight + ',0 0 20px ' + neonHighlight + ',0 0 30px ' + neonHighlight + ',0 0 40px ' + neonHighlightColor + ',0 0 70px ' + neonHighlightColor + ',0 0 80px ' + neonHighlightColor + ',0 0 100px ' + neonHighlightColor;
        $("#npd-text-render-wrap").css({ "text-shadow": shadow, "color": color });
      }

    }

    function initialise_editor() {
      if (window.npd.font_behaviour == "text") {
        var font = $("input[name='npd-font']:checked").val();
      }
      else {
        var font = $("#npd-font-selector").val();
      }

      var bg = $("input[name='npd-scene']:checked").val();
      var color = $("input[name='npd-neon-color']:checked").val();
      var first_color = $("input[name='npd-neon-color']:first").val();

      if (typeof color === "undefined" && typeof first_color !== "undefined") {
        color = first_color;
      }
      else if (typeof color === "undefined" && typeof first_color === "undefined") {
        color = "#ffffff";
      }

      get_light_color(color);

      var txt_align = $(".npd-align-btn-wrap input[name='npd-text-align']:checked").val();
      var user_text = $("#npd-text-field").val();
      var user_text = user_text.replace(/<br\s*\/?>/mg, "\n");

      // var npd_txt_height = $("input[name='npd-txt-size']:checked").attr("data-height");
      // var npd_txt_width = $("input[name='npd-txt-size']:checked").attr("data-width");
      resize_to_fit($(".npd-text-render-container"), $("#npd-text-render-wrap"));
      $("#npd-text-render-wrap").html(user_text);
      $(".npd-editor-preview").css("background-image", "url('" + bg + "')");
      $("#npd-text-render-wrap").css("max-width", "100%");
      //$("#npd-text-render-wrap").css("font-size", editor_size + "px");
      $("#npd-text-render-wrap").css("font-family", "'" + font + "'");
      npd_change_text_align(txt_align);

    }

    $("#npd-font-picker-wrap ul li").click(function () {
      var font = $(this).attr("data-value");
      npd_change_font(font);
      $(this).parents(".npd-custom-select-wrap").find('input').css('font-family', "'" + font + "'");
    });

    $(".npd-custom-option-wrap .npd-custom-select-wrap  ul li").click(function () {
      var price = $(this).attr("data-price");
      $(this).parents(".npd-custom-select-wrap").find('input').attr('data-price', price);
    });



    $(".npd-font-item input[name='npd-font']").on("change", function () {
      var font = $(this).parent().attr("data-value");
      npd_change_font(font);
    });


    $("input[name='npd-scene']").on("change", function () {
      var scene = $(this).val();
      npd_change_scene(scene);
    });
    $("input[name='npd-neon-color']").on("change", function () {
      var color = $(this).val();
      npd_change_color(color);
    });

    $(".npd-align-btn-wrap input[name='npd-text-align']").on("change", function () {
      var txt_align = $(this).val();
      npd_change_text_align(txt_align);
    });


    $(".npd-text-size-wrap input[name='npd-txt-size']").on("change", function () {


      var npd_txt_height = $(this).attr("data-height");
      var npd_txt_width = $(this).attr("data-width");
      resize_to_fit($(".npd-text-render-container"), $("#npd-text-render-wrap"));
      $(".npd-content-width-value").text(npd_txt_width + npd_unit);
      $(".npd-content-height-value").text(npd_txt_height + npd_unit);
      window.update_price();
      //$("#npd-text-render-wrap").css("fontSize", editor_size + 'px');

    });

    $(".npd-text-size-wrap input[name='npd-txt-size']:checked").trigger("change");

    function npd_change_font(font) {
      $("#npd-text-render-wrap").css("font-family", "'" + font + "'");
    }

    function npd_change_scene(scene) {
      $(".npd-editor-preview").css("background-image", "url('" + scene + "')");
    }
    function npd_change_color(color) {
      get_light_color(color);
    }
    function npd_change_text_align(txt_align) {
      var spacing = "0";
      if (txt_align === 'center') {
        $("#npd-text-render-wrap").css({ "left": 0, "right": 0, "text-align": "center" });
      }
      else if (txt_align === 'left') {
        $("#npd-text-render-wrap").css({ "left": spacing, "right": "unset", "text-align": "left" });
      }
      else if (txt_align === 'right') {
        $("#npd-text-render-wrap").css({ "left": "unset", "right": spacing, "text-align": "right" });
      }
    }

    window.npd_text_dimensions = function () {

      var width = $('#npd-text-render-wrap').width();
      var height = $('.npd-text-render-container').height();
      return [width, height];
    };
    npd_text_dimensions();

    // var original_w = parseFloat($("#npd-text-render-wrap").width());
    // var original_h = parseFloat($(".npd-text-render-container").height()) / 2;
    // function change_editor_size(neon_w, neon_h) {
    //   if (typeof original_w === "undefined") {
    //     original_w = parseFloat($("#npd-text-render-wrap").width());
    //   }

    //   if (typeof original_h === "undefined") {
    //     original_h = parseFloat($(".npd-text-render-container").height());
    //   }

    //   var font_size = parseFloat($("#npd-text-render-wrap").css("font-size"));
    //   var text_w = ($("#npd-text-render-wrap").text().length / 2) * font_size;
    //   var text_h = $("#npd-text-render-wrap").height();
    //   var alltime = [];
    //   if (text_w >= original_w) {
    //     font_size -= 0.1;
    //       var time = setTimeout(function () {
    //         $("#npd-text-render-wrap").css("font-size", font_size + "px");
    //         change_editor_size(neon_w, neon_h);
    //         clearTimeout(time)
    //       }, 1);
    //       alltime.push(time);
    //   }
    //   else if(text_h < original_h && text_w < original_w - 1){
    //     font_size += 0.1;
    //     var time = setTimeout(function () {
    //       $("#npd-text-render-wrap").css("font-size", font_size + "px");
    //       change_editor_size(neon_w, neon_h);
    //       clearTimeout(time);
    //     }, 1);
    //     alltime.push(time);
    //   }
    //   else {
    //     alltime.forEach(mtime => {
    //       clearTimeout(mtime)
    //     });
    //   }

    // }

    function resize_to_fit(outer, inner) {
      var default_s = $("input[name='npd-txt-size']:checked").attr("data-font-size");
      var fontsize = parseFloat( inner.css('font-size') );
       if(screen < 767){
            var fontsize = parseFloat( inner.css('font-size'));
            var default_s = default_s / 2;
        }

      if(isNaN(parseFloat(default_s))) {
        default_s = 60;
      }
      if (outer.width() < inner.width() || inner.height() > outer.height()) {
        inner.css('font-size', (fontsize - 0.1) + 'px');
        var time = setTimeout(function(){
          resize_to_fit(outer, inner);
        }, 10);
      }
      else if(outer.width() > inner.width() && fontsize < parseFloat(default_s)){
        inner.css('font-size', (fontsize + 0.1) + 'px');
        if((outer.width() - 2)  > inner.width()){
          var timer = setTimeout(function(){
            resize_to_fit(outer, inner);
          }, 10);
        }
      }
      else{
        if(typeof time !== "undefined")
          clearTimeout(time);
        if(typeof timer !== "undefined")
          clearTimeout(timer);
      }

      fontsize = parseFloat( inner.css('font-size') );
      if(fontsize > parseFloat(default_s)) {        
        inner.css('font-size', default_s + 'px');
      }
      wp.hooks.doAction('npd.text_resize_dimensions');
    }

    //personalisation des selects

    $(".npd-custom-select-input-cover").click(function () {
      $(this).parent(".npd-custom-select-wrap").find("ul").slideToggle("fast");
      window.update_price();
    })

    //MANAGE GENERAL CUSTOM SELECT
    $(".npd-custom-select-wrap ul li").click(function () {
      var selected = $(this).text();
      $(this).parents(".npd-custom-select-wrap").find('input').attr('value', selected);
      window.update_price();
    })

    $('body').on('click', function (event) {
      if (!$(event.target).is('.npd-custom-select-wrap .npd-custom-select-input-cover') && !$(event.target).is('.npd-custom-select-wrap ul li')) {
        $(".npd-custom-select-wrap ul").hide();

      }
      window.update_price();
    });

    //Quantity setter
    $(document).on('click', '.npd-qty-container .plus, .npd-qty-container .minus', function () {

      // Get values
      var $qty = $(this).siblings("#npd-qty");
      var currentVal = parseFloat($qty.val());
      var max = parseFloat($qty.attr('max'));
      var min = parseFloat($qty.attr('min'));
      var step = $qty.attr('step');

      // Format values
      if (!currentVal || currentVal === '' || currentVal === 'NaN')
        currentVal = 0;
      if (max === '' || max === 'NaN')
        max = '';
      if (min === '' || min === 'NaN')
        min = 0;
      if (step === 'any' || step === '' || step === undefined || parseFloat(step) === 'NaN')
        step = 1;

      // Change the value
      if ($(this).is('.plus')) {

        if (max && (max == currentVal || currentVal > max)) {
          $qty.val(max);
        } else {
          $qty.val(currentVal + parseFloat(step));
        }

      } else {

        if (min && (min == currentVal || currentVal < min)) {
          $qty.val(min);
        } else if (currentVal > 0) {
          $qty.val(currentVal - parseFloat(step));
        }

      }

      // Trigger change event
      $qty.trigger('change');

    });

    $(document).on('change', '#npd-qty', function () {
      window.update_price();
    });


    function dataToBlob(dataURI) {
      var get_URL = function () {
        return window.URL || window.webkitURL || window;
      };

      var byteString = atob(dataURI.split(',')[1]),
        mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0],
        arrayBuffer = new ArrayBuffer(byteString.length),
        _ia = new Uint8Array(arrayBuffer);

      for (var i = 0; i < byteString.length; i++) {
        _ia[i] = byteString.charCodeAt(i);
      }

      var dataView = new DataView(arrayBuffer);
      var blob = new Blob([dataView], { type: mimeString });
      return { blob: get_URL().createObjectURL(blob), data: dataURI };
    }


    $(document).on('click', '.npd-editor-tools-item .npd-editor-tools-title', function (e) {
      //$(".npd-editor-tools-content").hide();
      $(".npd-editor-tools-item .npd-editor-tools-title.first-box").removeClass("first-box");
      $(".npd-editor-tools-item.is-active .npd-editor-tools-content").hide();
      $(".npd-editor-tools-item.is-active").removeClass("is-active");
      $(this).parent().find(".npd-editor-tools-content").slideToggle();
      $(this).parent().toggleClass("is-active");
    });

    if (typeof wc_cart_fragments_params !== "undefined") {
      var $fragment_refresh = {
        url: wc_cart_fragments_params.wc_ajax_url
          .toString()
          .replace("%%endpoint%%", "get_refreshed_fragments"),
        type: "POST",
        success: function (data) {
          if (data && data.fragments) {
            $.each(data.fragments, function (key, value) {
              $(key).replaceWith(value);
            });
            var test = JSON.stringify(data.fragments);
            $(document.body).trigger("wc_fragments_refreshed");
            $(".wc-mini-cart").html(test);
          }
        },
      };
    }

    $(document).on('click', '#npd-add-to-cart-btn', function (e) {
      $(".npd-light-btn-wrap").hide();
      e.preventDefault();
      var form_data = {};
      var product_id = $(this).attr("data-id");
      var alt_products = [];
      var quantity = $("#npd-qty").val();
      var recap = $('#npd-editor-content').find(':input').serializeJSON();
      var variations = {};

      $.each($(".npd-qty-container"), function (key, curr_object) {
        var qty = $(this).find(".npd-qty").val();
        var variation_name = $(this).find(".npd-qty").attr("variation_name");
        variations[variation_name] = {};
        variations[variation_name]["qty"] = qty;
        variations[variation_name]["id"] = $(this).data("id");

      });

      $('#npd-add-to-cart-btn').addClass('disabledClick');
      $(this).parent().find('.loader').show();
      setTimeout(function () {
        html2canvas(document.querySelector(".npd-editor-preview")).then(canvas => {
          window.getCanvas = canvas;

          var imgageData = getCanvas.toDataURL("image/png");
          var preview_img = dataToBlob(imgageData).data;

          var npd_price = window.update_price();

          var npd_scene_choice = $(".npd-scene-content input[name='npd-scene']:checked").val();
          var frm_data = new FormData();
          frm_data.append("variation_id", product_id);
          frm_data.append("variations", JSON.stringify(variations));
          frm_data.append("action", "add_custom_design_to_cart");
          frm_data.append("cart_item_key", cart_item_key);
          frm_data.append("preview_img", preview_img);
          frm_data.append("recap", JSON.stringify(recap));
          frm_data.append("npd_price", JSON.stringify(npd_price));
          frm_data.append("npd_scene_choice", npd_scene_choice);
          frm_data.append("quantity", quantity);

          $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: frm_data,
            processData: false,
            contentType: false
          }).done(function (data) {
            if (npd_editor.is_json(data)) {
              var response = JSON.parse(data);
              $("#debug").html(response.message);
              if (typeof $fragment_refresh !== "undefined")
                $.ajax($fragment_refresh);
            } else {
              $("#debug").html(data);

            }
            $('.loader').hide();
            $(".npd-light-btn-wrap").show();
          })
            .fail(function (xhr, status, error) {
              $('.loader').hide();
              $(".npd-light-btn-wrap").show();
            });



        });

      }, 3000);

    });

  });

})(jQuery);