(function( $ ) {
	'use strict';

	$( document ).ready(
		function () {

			$('#user-manual').parent().attr('target', '_blank');
			// $( ".repeatable-fields-table .npd-default-radio" ).each(function( index, value ) {
			// console.log($('.repeatable-fields-table .npd-default-radio:first-child'));
			// $('.repeatable-fields-table tr:first-of-type .npd-default-radio').trigger("click");

			// });

			// manage defaut radio button in config
			$( document ).on(
				"change",
				".npd-default-radio",
				function (e) {
					// wordpress 5.6
					$( this ).parents( ".repeatable-fields-table" ).find( "input[type=radio]" ).attr( 'value',0 );
					$( this ).parents( ".repeatable-fields-table" ).find( "input[type=radio]" ).not( $( this ) ).prop( 'checked', false );
					if ($( this ).is( ':checked' )) {
						$( this ).attr( 'value',1 );
					} else {
						$( this ).attr( 'value',0 );
					}

				}
			);

			$( document ).on(
				"change",
				".npd-colors-group",
				function () {
					get_colors_field_based_on_type();
				}
			);

			if ($( '.npd-colors-group' ).length) {
				get_colors_field_based_on_type();
			}

			function get_colors_field_based_on_type()
			{
				if ($( "input[name='npd-metas[use-global-colors]']:checked" ).val() === "no") {

					$( "input[name='npd-metas[use-global-colors]']" ).parent().parents( ".npd-colors-group" ).next().show();

				} else {

					$( "input[name='npd-metas[use-global-colors]']" ).parent().parents( ".npd-colors-group" ).next().hide();
					$( "#npd-colors-selector" ).attr( "required", false );
				}

			}

			// we delay the init to avoid conflicts with plugin that hook on the select2 classes and create conflicts
			setTimeout(
				function () {
					$( "#font" ).select2( {allowClear: true, maximumSelectionLength: 3, language: {
						// You can find all of the options in the language files provided in the
						// build. They all must be functions that return the string that should be
						// displayed.
						maximumSelected: function (e) {
							var t = "You can only select " + e.maximum + " item";
							e.maximum != 1 && (t += "s");
							return t + ' - Upgrade Now and Select More';
						}} 
					});
				},
				500
			);

			load_select2();

			function load_select2(container) {
				if (typeof container == "undefined") {
					container = "";
				}
				$(container + " select.kali-select2").each(
					function () {
						$(this).select2({ allowClear: true, maximumSelectionLength: 3, language: {
							// You can find all of the options in the language files provided in the
							// build. They all must be functions that return the string that should be
							// displayed.
							maximumSelected: function (e) {
								var t = "You can only select " + e.maximum + " item";
								e.maximum != 1 && (t += "s");
								return t + ' - Upgrade Now and Select More';
							} }
						});
					}
				);
			}

			$( document ).on(
				'change',
				'#font',
				function () {
					var name = $( '#font  option:selected' ).text();
					var url = $( '#font   option:selected' ).val();
					$( '.font_auto_name' ).val( name );
					$( '.font_auto_url' ).val( url );
				}
			);

			// Config metabox option js
			$( document ).on(
				"change",
				"#npd-texts-options, input[name='npd-metas[texts-options][visible-tab]']",
				function (e) {
					var checked = $( this ).is( ":checked" );
					$( "#npd-texts-options tr:not(:first-child) input[name^='npd-metas[texts-options]'][type='checkbox']" ).prop( "checked", checked );
				}
			);

			$( document ).on(
				"change",
				"#npd-toolbar-options input[name='npd-toolbar-options[visible-tab]']",
				function(e){
					var checked = $( this ).is( ":checked" );
					$( "#npd-toolbar-options tr:not(:first-child) input[name^='npd-toolbar-options'][type='checkbox']" ).prop( "checked", checked );
				}
			);

			$( "#select-fonts" ).select2( {placeholder: "Select fonts", width: '100%', maximumSelectionLength: 3,  language: {
				// You can find all of the options in the language files provided in the
				// build. They all must be functions that return the string that should be
				// displayed.
				maximumSelected: function (e) {
					var t = "You can only select " + e.maximum + " item";
					e.maximum != 1 && (t += "s");
					return t + ' - Upgrade Now and Select More';
				}
			}} );

			$( "#npd-colors-selector" ).select2( {placeholder: "Select colors", width: '100%', maximumSelectionLength: 3, language: {
				// You can find all of the options in the language files provided in the
				// build. They all must be functions that return the string that should be
				// displayed.
				maximumSelected: function (e) {
					var t = "You can only select " + e.maximum + " item";
					e.maximum != 1 && (t += "s");
					return t + ' - Upgrade Now and Select More';
				}} 
			});

			$( "#select-additional-options" ).select2( {placeholder: "Select Custom options", width: '100%', maximumSelectionLength: 3, language: {
				// You can find all of the options in the language files provided in the
				// build. They all must be functions that return the string that should be
				// displayed.
				maximumSelected: function (e) {
					var t = "You can only select " + e.maximum + " item";
					e.maximum != 1 && (t += "s");
					return t + ' - Upgrade Now and Select More';
				}} 
			});
			

			$( "#select-fonts" ).attr( "required", true );

			// font-family-dependance
			if ($( "input[name='npd-metas[texts-options][ffamily]']" ).attr( 'checked' ) == "checked") {

				$( ".font-active" ).parents( 'tr' ).show();
			} else {
				$( ".font-active" ).parents( 'tr' ).hide();
			}

			// font-dependance
			if ($( "input[name='npd-metas[texts-options][font-size]']" ).attr( 'checked' ) == "checked") {

				$( ".font-dependance" ).parents( 'tr' ).show();
			} else {
				$( ".font-dependance" ).parents( 'tr' ).hide();
			}

			$( document ).on(
				"change",
				"#npd-texts-options input[name='npd-metas[texts-options][font-size]']",
				function (e) {
					var checked = $( this ).is( ":checked" );
					if (checked) {
						$( ".font-dependance" ).parents( 'tr' ).show();
					} else {
						$( ".font-dependance" ).parents( 'tr' ).hide();
					}

				}
			);

			if ($( "input[name='npd-metas[texts-options][text-color]']" ).attr( 'checked' ) == "checked") {

				$( ".color-dependance" ).parents( 'tr' ).show();
			} else {
				$( ".color-dependance" ).parents( 'tr' ).hide();
			}

			$( document ).on(
				"change",
				"#npd-texts-options input[name='npd-metas[texts-options][text-color]']",
				function (e) {
					var checked = $( this ).is( ":checked" );
					console.log( checked );
					if (checked) {
						$( ".color-dependance" ).parents( 'tr' ).show();
					} else {
						$( ".color-dependance" ).parents( 'tr' ).hide();
					}

				}
			);

			function dataToBlob(dataURI) {
				var get_URL = function () {
					return window.URL || window.webkitURL || window;
				};
				var byteString = atob( dataURI.split( ',' )[1] ),
				mimeString = dataURI.split( ',' )[0].split( ':' )[1].split( ';' )[0],
				arrayBuffer = new ArrayBuffer( byteString.length ),
				_ia = new Uint8Array( arrayBuffer );

				for (var i = 0; i < byteString.length; i++) {
					_ia[i] = byteString.charCodeAt( i );
				}

				var dataView = new DataView( arrayBuffer );
				var blob = new Blob( [dataView], { type: mimeString } );
				return { blob: get_URL().createObjectURL( blob ), data: dataURI };
			}

			$( document ).on(
				'click',
				'.npd_admin_download_image',
				function (e) {
					e.preventDefault();
					var imgageData = $( this ).attr( "href" );
					var preview_img = dataToBlob( imgageData ).blob;
					var downloadLink = document.createElement( "a" );
					downloadLink.href = preview_img;
					downloadLink.download = "custom_neon" + ".png";
					document.body.appendChild( downloadLink );
					downloadLink.click();
					document.body.removeChild( downloadLink );
				}
			)

			$(document).on(
				'mouseover mousedown click ready',
				function() {
					let selector = $('.widefat.repeatable-fields-table');
					if(typeof selector.size === 'function') {
						var line = selector.find(
							'tbody > tr'
						).size();
					}
					else {
						var line = selector.find(
							'tbody > tr'
						).length;
					}

					var text = selector.siblings(
						'.kali-add-new-row'
					).text();

										
					if(typeof selector.siblings(
						'.kali-add-new-row'
					).attr("data-text") === "undefined") {
						selector.siblings(
							'.kali-add-new-row'
						).attr("data-text", text);
					}

					if(line >= 3) {
						selector.siblings(
							'.kali-add-new-row'
						).attr('disabled', 'true');

						selector.siblings(
							'.kali-add-new-row'
						).text(selector.siblings(
							'.kali-add-new-row'
						).attr("data-text") + " (PRO)")

						selector.siblings(
							'.kali-add-new-row'
						).addClass('npd-block-row');

						selector.siblings(
							'.kali-add-new-row'
						).removeClass('kali-add-new-row');
					}
					else {
						selector.siblings(
							'.kali-add-new-row'
						).removeAttr('disabled');

						selector.siblings(
							'.kali-add-new-row'
						).text(selector.siblings(
							'.kali-add-new-row'
						).attr("data-text"))

						selector.siblings(
							'.npd-block-row'
						).removeAttr('disabled');

						selector.siblings(
							'.npd-block-row'
						).addClass('kali-add-new-row');

						selector.siblings(
							'.kali-add-new-row'
						).removeClass('npd-block-row');
					}
				}
			)
		}
	);

})( jQuery );
