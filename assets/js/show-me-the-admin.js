(function( $ ) {
	'use strict';

	// When the document is ready...
	$(document).ready(function() {

		// Add our selector to the appropriate elements.
		$('#wpadminbar,#show-me-the-admin-login').addClass('show-me-the-admin-bar');

		var $show_me_admin_bar = $('.show-me-the-admin-bar'),
			show_me_admin_bar_height = $show_me_admin_bar.height();

		// Hide the toolbar.
		$show_me_admin_bar.addClass('hidden').css({
			'top': (0 - show_me_admin_bar_height) + 'px'
		});

		// Make sure features is an array.
		var smta_features = show_me_the_admin.features || {};
		smta_features = $.map(smta_features, function(value, index) {
			return [value];
		});

		// If keyphrase is enabled, we have to at least have a valid show phrase...
		if ( $.inArray( 'keyphrase', smta_features ) > -1 && undefined !== show_me_the_admin.show_phrase && null != show_me_the_admin.show_phrase ) {
			var user_key_string = '',
				smta_show_phrase = show_me_the_admin.show_phrase,
				smta_hide_phrase = show_me_the_admin.hide_phrase !== undefined && '' != show_me_the_admin.hide_phrase ? show_me_the_admin.hide_phrase : null;

			// Track when the user types.
			$(document).keyup(function(event) {

				// Make sure we have a keycode from their keystroke.
				if (!( event.which !== undefined && event.which > 0 )) {
					return false;
				}

				// Add to the test string.
				user_key_string += event.which;

				// If the toolbar is hidden and the user phrase string equals the show phrase.
				if ( user_key_string == smta_show_phrase ) {
					if ( $show_me_admin_bar.hasClass('hidden') ) {
						user_key_string = '';
						show_me_the_admin_show_bar( true );
					}
				}

				// If the toolbar is not hidden and the user phrase string equals the show phrase.
				else if ( smta_hide_phrase && user_key_string == smta_hide_phrase ) {
					if ( ! $show_me_admin_bar.hasClass('hidden') ) {
						user_key_string = '';
						show_me_the_admin_hide_bar();
					}
				}

				// It it doesn't match either phrase starting from the beginning, then start over.
				else if ( 0 != smta_show_phrase.search(user_key_string) && 0 != smta_hide_phrase.search(user_key_string) ) {
					user_key_string = '';
				}
			});
		}

		// Will be true if we should initiate the mouseleave functionality.
		var init_admin_bar_mouseleave = false;

		/*
		 * Will hold the mouseleave delay (in seconds) to display the toolbar.
		 *
		 * Default is 2 seconds.
		 */
		var admin_bar_mouseleave = 2000;

		/*
		 * Will be true when we want mouseleave functionality to work.
		 *
		 * Need this in case 1 feature w/mouseleave is
		 * enabled along with a feature without mouseleave.
		 */
		var enable_admin_bar_mouseleave = false;

		// If hover feature is enabled.
		if ( $.inArray( 'hover', smta_features ) > -1 ) {

			// When the mouse hovers over this area, the toolbar will appear.
			$('#show-me-the-admin-hover').mouseenter(function() {

				enable_admin_bar_mouseleave = true;

				show_me_the_admin_show_bar();

				// Define the mouseleave delay.
				var hover_mouseleave_delay = show_me_the_admin.hover_mouseleave_delay;
				if ( hover_mouseleave_delay !== undefined && hover_mouseleave_delay > 0 ) {
					admin_bar_mouseleave = parseInt( hover_mouseleave_delay );
				}
			});

			// Initiate toolbar mouseleave functionality.
			init_admin_bar_mouseleave = true;

		}

		// If button feature is enabled.
		if ( $.inArray( 'button', smta_features ) > -1 ) {

			$('#show-me-the-admin-button').on('click keyup', function(event) {

				var focus = false;

				if ( event.type === 'keyup' ) {

					// We're only worried about the enter button.
					if ( event.keyCode !== 13 ) {
						event.preventDefault();
						return;
					}

					focus = true;

				} else {

					enable_admin_bar_mouseleave = true;

					// Define the mouseleave delay.
					var button_mouseleave_delay = show_me_the_admin.button_mouseleave_delay;
					if ( button_mouseleave_delay !== undefined && button_mouseleave_delay > 0 ) {
						admin_bar_mouseleave = parseInt( button_mouseleave_delay );
					}
				}

				show_me_the_admin_show_bar( focus );

			});

			// Initiate toolbar mouseleave functionality.
			init_admin_bar_mouseleave = true;

		}

		// Setup toolbar mouseleave functionality.
		if ( init_admin_bar_mouseleave ) {

			// When the mouse leaves the toolbar, the toolbar will disappear after 2 seconds.
			$('#wpadminbar,#show-me-the-admin-login').mouseleave(function() {

				// Only initiate if enabled.
				if ( enable_admin_bar_mouseleave ) {
					setTimeout( show_me_the_admin_hide_bar, admin_bar_mouseleave );
				}

				// Reset the setting.
				enable_admin_bar_mouseleave = false;

			});
		}
	});

	function show_me_the_admin_show_bar( focus ) {
		var $bar = $('.show-me-the-admin-bar');

		$bar.stop(true,true).removeClass('hidden').show().animate({'top':'0'},200);

		if ( focus ) {

			// Store current active element.
			$bar.data('returnFocus',document.activeElement);
			$bar.on('keyup.showmetheadmin',show_me_the_admin_hide_bar_esc);

			if ( $bar.attr('id') == 'wpadminbar' ) {
				$bar.find('a:not(.screen-reader-shortcut)').first().focus();
			} else if ( $bar.is('a') ) {
				$bar.focus();
			}
		}
	}

	function show_me_the_admin_hide_bar() {
		var $bar = $('.show-me-the-admin-bar').off('keyup.showmetheadmin');

		// Should we return focus?
		var returnFocus = $bar.data('returnFocus');
		if (returnFocus) {
			var $returnFocus = $(returnFocus);
			if ($returnFocus.length > 0 && ! $returnFocus.is('body')) {
				$returnFocus.focus();
			}
		}
		$bar.removeData('returnFocus');

		$bar.stop(true,true).animate({
			top: ( 0 - $bar.height() ) + 'px'
		}, 200, function(){
			$bar.hide().addClass('hidden');
		});
	}

	function show_me_the_admin_hide_bar_esc(event) {
		if ( event.keyCode === 27 ) {
			show_me_the_admin_hide_bar();
		}
	}
})( jQuery );
