(function( $ ) {
	'use strict';

	// When the document is ready...
	$(document).ready(function() {

		// Hide the admin bar
		$('#wpadminbar').addClass('hidden').css({'top':(0-$('#wpadminbar').height())+'px'});

		// We have to at least have a valid showkey...
		if ( show_me_the_admin.showkey !== undefined && '' != show_me_the_admin.showkey ) {

			// Will hold the string being typed
			var $user_key_string = '';

			// Get the keys
			var $smta_showkey = show_me_the_admin.showkey;
			var $smta_hidekey = show_me_the_admin.hidekey !== undefined && '' != show_me_the_admin.hidekey ? show_me_the_admin.hidekey : null;

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

				// If the admin bar is hidden and the user key string equals the showkey
				if ($('#wpadminbar').hasClass('hidden') && $user_key_string == $smta_showkey) {
					$user_key_string = '';
					show_me_the_admin_show_bar();
				}

				// If the admin bar is not hidden and the user key string equals the showkey
				else if ($smta_hidekey && ! $('#wpadminbar').hasClass('hidden') && $user_key_string == $smta_hidekey) {
					$user_key_string = '';
					show_me_the_admin_hide_bar();
				}

				// It it doesn't match either key starting from the beginning, then start over
				else if ( 0 != $smta_showkey.search($user_key_string) && 0 != $smta_hidekey.search($user_key_string) ) {
					$user_key_string = '';
				}

			});
		}

	});

	///// FUNCTIONS /////

	// Show the admin bar
	function show_me_the_admin_show_bar() {
		$('#wpadminbar').stop(true, true).removeClass('hidden').show().animate({'top':'0'},200);
		$('body').animate({'marginTop': $('#wpadminbar').height()+'px'},200);
	}

	// Hide the admin bar
	function show_me_the_admin_hide_bar() {
		$('#wpadminbar').stop(true, true).animate({top:(0-$('#wpadminbar').height())+'px'},200, function(){$('#wpadminbar').hide().addClass('hidden');});
		$('body').animate({'marginTop':'0'},200);
	}

})( jQuery );