(function( $ ) {
	'use strict';

	// When the document is ready...
	$(document).ready(function() {

		// Sends an AJAX call to remove the notice.
		$('#smta-users-setting-notice .notice-dismiss').on('click',function() {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				async: true,
				cache: false,
				data: { action: 'smta_add_users_setting_notice' }
			});
		});

		// Sends an AJAX call to remove the notice.
		$('#smta-user-notice .notice-dismiss').on('click',function() {
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
