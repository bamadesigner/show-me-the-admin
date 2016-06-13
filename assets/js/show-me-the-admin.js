(function( $ ) {
	'use strict';

	// When the document is ready...
	$(document).ready(function() {

		// Add our selector to the appropriate elements
		$('#wpadminbar,#show-me-the-admin-login').addClass('show-me-the-admin-bar');

		// Hide the admin bar
		$('.show-me-the-admin-bar').addClass('hidden').css({'top':(0-$('.show-me-the-admin-bar').height())+'px'});

		// If keyphrase is enabled, we have to at least have a valid show phrase...
		if ( jQuery.inArray( 'keyphrase', show_me_the_admin.features ) > -1 && undefined !== show_me_the_admin.show_phrase && null != show_me_the_admin.show_phrase ) {

			// Will hold the string being typed
			var $user_key_string = '';

			// Get the phrases
			var $smta_show_phrase = show_me_the_admin.show_phrase;
			var $smta_hide_phrase = show_me_the_admin.hide_phrase !== undefined && '' != show_me_the_admin.hide_phrase ? show_me_the_admin.hide_phrase : null;

			// Track when the user types
			$(document).keyup(function ($event) {

				// Only do it on the body element
				if ( 'body' != $event.target.tagName.toLowerCase() ) {
					return false;
				}

				// Make sure we have a keycode from their keystroke
				if (!( $event.which !== undefined && $event.which > 0 )) {
					return false;
				}

				// Add to the test string
				$user_key_string += $event.which;

				// If the admin bar is hidden and the user phrase string equals the show phrase
				if ($('.show-me-the-admin-bar').hasClass('hidden') && $user_key_string == $smta_show_phrase) {
					$user_key_string = '';
					show_me_the_admin_show_bar();
				}

				// If the admin bar is not hidden and the user phrase string equals the show phrase
				else if ($smta_hide_phrase && ! $('.show-me-the-admin-bar').hasClass('hidden') && $user_key_string == $smta_hide_phrase) {
					$user_key_string = '';
					show_me_the_admin_hide_bar();
				}

				// It it doesn't match either phrase starting from the beginning, then start over
				else if ( 0 != $smta_show_phrase.search($user_key_string) && 0 != $smta_hide_phrase.search($user_key_string) ) {
					$user_key_string = '';
				}

			});
		}

		// Will be true if we should initiate the mouseleave functionality
		var $init_admin_bar_mouseleave = false;

		// Will hold the mouseleave delay (in seconds) to display the admin bar
		// Default is 2 seconds
		var $admin_bar_mouseleave = 2000;

		// Will be true when we want mouseleave functionality to work
		// Need this in case 1 feature w/mouseleave is enabled along with a feature without mouseleave
		var $enable_admin_bar_mouseleave = false;

		// If hover feature is enabled
		if ( jQuery.inArray( 'hover', show_me_the_admin.features ) > -1 ) {

			// When the mouse hovers over this area, the admin bar will appear
			$('#show-me-the-admin-hover').mouseenter(function() {
				$enable_admin_bar_mouseleave = true;
				show_me_the_admin_show_bar();

				// Define the mouseleave delay
				if ( show_me_the_admin.hover_mouseleave_delay !== undefined ) {
					if ( show_me_the_admin.hover_mouseleave_delay > 0 ) {
						$admin_bar_mouseleave = parseInt( show_me_the_admin.hover_mouseleave_delay );
					}
				}

			});

			// Initiate admin bar mouseleave functionality
			$init_admin_bar_mouseleave = true;

		}

		// If button feature is enabled
		if ( jQuery.inArray( 'button', show_me_the_admin.features ) > -1 ) {

			$('#show-me-the-admin-button').click(function () {
				$enable_admin_bar_mouseleave = true;
				show_me_the_admin_show_bar();

				// Define the mouseleave delay
				if ( show_me_the_admin.button_mouseleave_delay !== undefined ) {
					if ( show_me_the_admin.button_mouseleave_delay > 0 ) {
						$admin_bar_mouseleave = parseInt( show_me_the_admin.button_mouseleave_delay );
					}
				}

			});

			// Initiate admin bar mouseleave functionality
			$init_admin_bar_mouseleave = true;

		}

		// Setup admin bar mouseleave functionality
		if ( $init_admin_bar_mouseleave ) {

			// When the mouse leaves the admin bar, the admin bar will disappear after 2 seconds
			$('#wpadminbar,#show-me-the-admin-login').mouseleave(function() {

				// Only initiate if enabled
				if ( $enable_admin_bar_mouseleave ) {
					setTimeout( show_me_the_admin_hide_bar, $admin_bar_mouseleave );
				}

				// Reset the setting
				$enable_admin_bar_mouseleave = false;

			});

		}

		// If speak feature is enabled...
		/*if ( jQuery.inArray( 'speak', show_me_the_admin.features ) > -1 && annyang ) {

			// Define our commands to show and hide the bar
			var $commands = {
				'show me': function() {
					console.log('hello');
					show_me_the_admin_show_bar();
					setTimeout(show_me_the_admin_hide_bar, 2000);
				}
			};

			// Add our commands to annyang
			annyang.addCommands($commands);

			// Start listening. You can call this here, or attach this call to an event, button, etc.
			annyang.start();

		}*/

	});

	///// FUNCTIONS /////

	// Show the admin bar
	function show_me_the_admin_show_bar() {
		$('.show-me-the-admin-bar').stop(true, true).removeClass('hidden').show().animate({'top':'0'},200);
	}

	// Hide the admin bar
	function show_me_the_admin_hide_bar() {
		$('.show-me-the-admin-bar').stop(true, true).animate({top:(0-$('.show-me-the-admin-bar').height())+'px'},200, function(){$('.show-me-the-admin-bar').hide().addClass('hidden');});
	}

})( jQuery );