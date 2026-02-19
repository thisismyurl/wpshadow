jQuery( document ).ready( function( $ ) {
	$( '#wpshadow-cron-disabled-notice' ).on( 'click', '.notice-dismiss', function() {
		$.ajax( {
			url: wpsScanSchedulerNotice.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpshadow_dismiss_cron_disabled_notice',
				nonce: wpsScanSchedulerNotice.nonce,
			},
		} );
	} );
} );
