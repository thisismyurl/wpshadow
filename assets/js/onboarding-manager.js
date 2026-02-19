jQuery( document ).ready( function( $ ) {
	$( '#wpshadow-graduate-btn' ).on( 'click', function() {
		$.post(
			wpsOnboardingManager.ajaxUrl,
			{
				action: 'wpshadow_show_all_features',
				nonce: wpsOnboardingManager.nonce,
			},
			function() {
				window.location.reload();
			}
		);
	} );

	$( '#wpshadow-graduate-later' ).on( 'click', function() {
		$.post(
			wpsOnboardingManager.ajaxUrl,
			{
				action: 'wpshadow_dismiss_graduation',
				nonce: wpsOnboardingManager.nonce,
			},
			function() {
				$( '.wpshadow-graduation-notice' ).fadeOut();
			}
		);
	} );
} );
