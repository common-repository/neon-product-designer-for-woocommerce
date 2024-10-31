(function ($) {
	'use strict';

	$( document ).ready(
		function () {
			var vc_uids = {};

			$( document ).on(
				"click",
				".vc-add-font-file",
				function (e) {
					e.preventDefault();
					var uploader = wp.media(
						{
							title: 'Please set the picture',
							button: {
								text: "Select picture(s)"
							},
							multiple: false
						}
					)
					.on(
						'select',
						function () {
							var selection = uploader.state().get( 'selection' );
							selection.map(
								function (attachment) {
									attachment = attachment.toJSON();
									var new_rule_index = $( ".font_style_table tbody tr" ).length;
									var font_tpl = $( "#npd-font-tpl" ).val();
									var tpl = font_tpl.replace( /{index}/g, new_rule_index );
									$( '.font_style_table tbody' ).prepend( tpl );
									$( '#file_data_' + new_rule_index ).find( "input[type=hidden]" ).val( attachment.id );
									$( '#file_data_' + new_rule_index ).parent().find( ".media-name" ).html( attachment.filename );
								}
							);
						}
					)
					.open();
				}
			);

			$( document ).on(
				"click",
				".vc-remove-font-file",
				function (e) {
					e.preventDefault();
					$( this ).parent().find( "input[type=hidden]" ).val( "" );
					$( this ).parent().parent().find( ".media-name" ).html( "" );
					$( this ).parent().parent().remove();
				}
			);

			$( ".TabbedPanels" ).each(
				function ()
				{
						var defaultTab = 0;
						new Spry.Widget.TabbedPanels( $( this ).attr( "id" ), {defaultTab: defaultTab} );
				}
			);

			window.check_the_max_input_vars = function () {
				var all_champs_form = $( "form#post input[type=text], form#post input[type=hidden], form#post input[type=button], form#post input[type=checkbox]:checked, form#post input[type=radio]:checked, form#post select, form#post input[type=date], form#post input[type=url], form#post input[type=time], form#post input[type=email], form#post input[type=number], form#post input[type=tel], form#post input[type=search], form#post input[type=image], form#post input[type=reset], form#post input[type=mot-cle], form#post textarea, form#post input[type=password] " ).length;
				var msg = vc_max_input_msg.replace( "{nb}", all_champs_form );
				var message_admin_notice = "<div class='vpc-error error'><p>" + msg + "</p></div>";
				if (all_champs_form >= vc_max_input_vars) {
					if ($( ".vpc-error" ).length) {
						$( ".vpc-error" ).html( "<p>" + msg + "</p>" );
					} else {
						$( '#wpbody-content, #publishing-action' ).prepend( message_admin_notice );
					}
					$( ".vpc-error" ).show();
					$( "#publishing-action #publish, #save-action #save-post" ).prop( 'disabled', true );

				} else {

					$( ".vpc-error" ).hide();
					$( "#publishing-action #publish, #save-action #save-post" ).prop( 'disabled', false );

				}
			}

			function get_tables_hierarchy(raw_tpl, element)
			{
				var raw_tpl_tmp = raw_tpl;
				var regExp = /{(.*?)}/g;
				var matches = raw_tpl_tmp.match( regExp );// regExp.exec(raw_tpl_tmp);
				// Attention on doit trouver un moyen d'identifier tous les éléments de la même ligne afin de remplacer leurs index correctement
				var count = (raw_tpl.match( regExp ) || []).length;

				// Loop through all parents repeatable fields rows
				if (count > 0) {
					var table_hierarchy = element.parents( ".vc-rf-row" );
					$.each(
						table_hierarchy,
						function (i, e) {
							var re = new RegExp( matches[0], 'g' );
							var row_index = $( e ).index();
							raw_tpl_tmp = raw_tpl_tmp.replace( re, row_index );
							matches.shift();
							// raw_tpl_tmp=raw_tpl_tmp.replace(regExp, row_index);
						}
					);

				}
				// The last or unique index in the template is the number of rows in the table
				var table_body = element.siblings( "table.repeatable-fields-table" ).children( "tbody" ).first();
				var new_key_index = table_body.children( "tr" ).length;
				var re = new RegExp( matches[0], 'g' );
				raw_tpl_tmp = raw_tpl_tmp.replace( re, new_key_index );
				return raw_tpl_tmp;
			}

			$( document ).on(
				"click",
				".add-rf-row",
				function (e)
				{
						setTimeout( check_the_max_input_vars, 200 );
						var table_body = $( this ).siblings( "table" ).find( "tbody" ).first();
						var tpl_id = $( this ).data( "tpl" );
						var raw_tpl = vc_rows_tpl[tpl_id];
						var tpl1 = get_tables_hierarchy( raw_tpl, $( this ) );
						table_body.append( tpl1 );

						// Makes sure the newly added rows uses unique modals popups
						// otherwise the click on two different options buttons may open the same popup
						var modal_ids = table_body.children( ".vc-rf-row" ).last().find( "a.vc-modal-trigger" );
					if (modal_ids.length) {
						$.each(
							modal_ids,
							function (i, e) {
								var modal_id = $( this ).data( "modalid" );
								var new_modal_id = vc_uniqid( "vc-modal-" );
								$( this ).attr( "data-target", "#" + new_modal_id );
								$( "#" + modal_id ).attr( "id", new_modal_id );
							}
						);
					}
					if ($( this ).html() == "Add color") {
						load_color_picker();
					}
				}
			);

			$( document ).on(
				"click",
				".remove-rf-row",
				function (e)
				{
						setTimeout( check_the_max_input_vars, 200 );
						$( this ).parent().parent().remove();
				}
			);

			if ($( ".add-rf-row" ).length) {
				setTimeout( check_the_max_input_vars, 200 );
			}

			$( document ).on(
				"click",
				".vc-add-media",
				function (e) {
					e.preventDefault();
					var trigger = $( this );
					var uploader = wp.media(
						{
							title: 'Please set the picture',
							button: {
								text: "Select picture(s)"
							},
							multiple: false
						}
					)
						.on(
							'select',
							function () {
								var selection = uploader.state().get( 'selection' );
								selection.map(
									function (attachment) {
										attachment = attachment.toJSON();
										var url_without_root = attachment.url.replace( home_url, "" );
										trigger.parent().find( "input[type=hidden]" ).val( url_without_root );
										trigger.parent().find( ".media-preview" ).html( "<img src='" + attachment.url + "'>" );
										trigger.parent().find( ".media-name" ).html( attachment.filename );
										if (trigger.parent().hasClass( "trigger-change" )) {
											trigger.parent().find( "input[type=hidden]" ).trigger( "propertychange" );
										}
									}
								);
							}
						)
						.open();
				}
			);

			$( document ).on(
				"click",
				".vc-remove-media",
				function (e) {
					e.preventDefault();
					$( this ).parent().find( ".media-preview" ).html( "" );
					$( this ).parent().find( "input[type=hidden]" ).val( "" );
					$( this ).parent().find( ".media-name" ).html( "" );
					if ($( this ).parent().hasClass( "trigger-change" )) {
						$( this ).parent().find( "input[type=hidden]" ).trigger( "propertychange" );
					}
				}
			);

			// Modal resize
			// $('.o-modal-trigger').click(function() {
			// var TB_WIDTH = 100,
			// TB_HEIGHT = 100; // set the new width and height dimensions here..
			// $("#TB_window").animate({
			// marginLeft: '-' + parseInt((TB_WIDTH / 2), 10) + 'px',
			// width: TB_WIDTH + 'px',
			// height: TB_HEIGHT + 'px',
			// marginTop: '-' + parseInt((TB_HEIGHT / 2), 10) + 'px'
			// });
			//
			// $("#TB_window").css("background-color", "red");
			// });

			// load_color_picker();
			// function load_color_picker() {
			// $('.vc-color').each(function (index, element)
			// {
			// var e = $(this);
			// var initial_color = e.val();
			// e.css("border-left", "35px solid " + initial_color);
			// $(this).parent().find('.vc-color-btn').ColorPicker({
			// color: initial_color,
			// onShow: function (colpkr) {
			// $(colpkr).fadeIn(500);
			// return false;
			// },
			// onChange: function (hsb, hex, rgb) {
			// e.css("border-left", "35px solid #" + hex);
			// e.val("#" + hex);
			// e.trigger("input");
			// }
			// });
			// });
			// }

			function load_color_picker() {
				$( '.vc-color' ).each(
					function (index, element)
					{
							var e = $( this );
							var initial_color = e.val();
							e.css( "border-left", "50px solid " + initial_color );
							$( this ).iris(
								{
									// or in the data-default-color attribute on the input
									defaultColor: true,
									mode: 'hex',
									width: 120,
									// a callback to fire whenever the color changes to a valid color
									change: function(event, ui){
										var hex = ui.color.toString();
										e.css( "border-left", "50px solid " + hex );
										e.val( hex );
										e.trigger( "input" );
									},
									// a callback to fire when the input is emptied or an invalid color
									clear: function() {},
									// hide the color picker controls on load
									hide: true,
									// show a group of common colors beneath the square
									palettes: true
								}
							);

							// input blur function
							e.blur(
								function() {
									setTimeout(
										function() {
											if ( ! $( document.activeElement ).closest( ".iris-picker" ).length) {
												e.iris( 'hide' );
											} else {
												e.focus();
											}
										},
										0
									);
								}
							);

							// when input is focused
							e.focus(
								function() {
									// input iris show
									e.iris( 'show' );
								}
							);
					}
				);
			}

			load_color_picker();

			$( ".vc-google-font-selector" ).each(
				function ()
				{
						$( this ).select2( {allowClear: true} );
				}
			);

		}
	);

})( jQuery );

function is_json(data)
{
	if (/^[\],:{}\s]*$/.test(data.replace(/\\["\\\/bfnrtu]/g, '@').
            replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
            replace(/(?:^|:|,)(?:\s*\[)+/g, '')))
        return true;
    else
        return false;
}



function vc_uniqid(prefix, more_entropy) {
	// discuss at: http://phpjs.org/functions/uniqid/
	// original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// revised by: Kankrelune (http://www.webfaktory.info/)
	// note: Uses an internal counter (in php_js global) to avoid collision
	// test: skip
	// example 1: uniqid();
	// returns 1: 'a30285b160c14'
	// example 2: uniqid('foo');
	// returns 2: 'fooa30285b1cd361'
	// example 3: uniqid('bar', true);
	// returns 3: 'bara20285b23dfd1.31879087'

	if (typeof prefix === 'undefined') {
		prefix = '';
	}

	var retId;
	var formatSeed = function (seed, reqWidth) {
		seed = parseInt( seed, 10 )
				.toString( 16 ); // to hex str
		if (reqWidth < seed.length) {
			// so long we split
			return seed.slice( seed.length - reqWidth );
		}
		if (reqWidth > seed.length) {
			// so short we pad
			return Array( 1 + (reqWidth - seed.length) )
					.join( '0' ) + seed;
		}
		return seed;
	};

	// BEGIN REDUNDANT
	if ( ! vc_uids) {
		var vc_uids = {};
	}
	// END REDUNDANT
	if ( ! vc_uids.uniqidSeed) {
		// init seed with big random int
		vc_uids.uniqidSeed = Math.floor( Math.random() * 0x75bcd15 );
	}
	vc_uids.uniqidSeed++;

	// start with prefix, add current milliseconds hex string
	retId = prefix;
	retId += formatSeed(
		parseInt(
			new Date()
			.getTime() / 1000,
			10
		),
		8
	);
	// add seed hex string
	retId += formatSeed( vc_uids.uniqidSeed, 5 );
	if (more_entropy) {
		// for more entropy we add a float lower to 10
		retId += (Math.random() * 10)
				.toFixed( 8 )
				.toString();
	}

	return retId;
}
