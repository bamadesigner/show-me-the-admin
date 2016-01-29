(function( $ ) {
	'use strict';

	// When the document is ready...
	$(document).ready(function() {

		// Add our selector to the appropriate elements
		$('#wpadminbar,#show-me-the-admin-login').addClass('show-me-the-admin-bar');

		// Hide the admin bar
		$('.show-me-the-admin-bar').addClass('hidden').css({'top':(0-$('.show-me-the-admin-bar').height())+'px'});

		// We have to at least have a valid show phrase...
		if ( show_me_the_admin.show_phrase !== undefined && '' != show_me_the_admin.show_phrase ) {

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

	});

	///// FUNCTIONS /////

	// Show the admin bar
	function show_me_the_admin_show_bar() {
		$('.show-me-the-admin-bar').stop(true, true).removeClass('hidden').show().animate({'top':'0'},200);
		$('body').animate({'marginTop': $('.show-me-the-admin-bar').height()+'px'},200);
	}

	// Hide the admin bar
	function show_me_the_admin_hide_bar() {
		$('.show-me-the-admin-bar').stop(true, true).animate({top:(0-$('.show-me-the-admin-bar').height())+'px'},200, function(){$('.show-me-the-admin-bar').hide().addClass('hidden');});
		$('body').animate({'marginTop':'0'},200);
	}

})( jQuery );