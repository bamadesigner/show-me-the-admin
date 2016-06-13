(function( $ ) {
	'use strict';

	// When the document is ready...
	$(document).ready(function() {

		// Clear the users setting notice
		$('#smta-users-setting-notice .notice-dismiss').on('click',function($event) {

			// Send an AJAX call to test the URL
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				async: true,
				cache: false,
				data: { action: 'smta_add_users_setting_notice' }
			});

		});

		// Clear the user notice
		$('#smta-user-notice .notice-dismiss').on('click',function($event) {

			// Send an AJAX call to test the URL
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				async: true,
				cache: false,
				data: { action: 'smta_add_user_notice' }
			});

		});

	});

})( jQuery );