(function ($) {
	'use strict';

	var config             = window.wpshadowTipsCoach || {};
	var nonce              = String( config.nonce || '' );
	var msgEnabled         = String( config.msgEnabled || '' );
	var msgDisabled        = String( config.msgDisabled || '' );
	var msgSaveError       = String( config.msgSaveError || '' );
	var msgConnectionError = String( config.msgConnectionError || '' );

	function showMessage(message, type) {
		var $message    = $( '#wpshadow-tips-message' );
		var bgColor     = 'success' === type ? '#d4edda' : '#f8d7da';
		var textColor   = 'success' === type ? '#155724' : '#721c24';
		var borderColor = 'success' === type ? '#c3e6cb' : '#f5c6cb';

		$message.css(
			{
				background: bgColor,
				color: textColor,
				borderColor: borderColor
			}
		).text( message ).show();

		setTimeout(
			function () {
				$message.fadeOut();
			},
			3000
		);
	}

	function saveCategories(isEnabled) {
		var disabledCategories = [];
		$( '.wpshadow-category-toggle:not(:checked)' ).each(
			function () {
				disabledCategories.push( $( this ).data( 'category' ) );
			}
		);

		$.ajax(
			{
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_save_tip_prefs',
					nonce: nonce,
					disabled_categories: disabledCategories
				},
				success: function (response) {
					if (response.success) {
						showMessage( isEnabled ? msgEnabled : msgDisabled, 'success' );
						return;
					}

					showMessage( msgSaveError, 'error' );
				},
				error: function () {
					showMessage( msgConnectionError, 'error' );
				}
			}
		);
	}

	$(
		function () {
			$( '.wpshadow-category-toggle' ).on(
				'change',
				function () {
					saveCategories( $( this ).is( ':checked' ) );
				}
			);

			$( '#wpshadow-enable-all-tips' ).on(
				'click',
				function (e) {
					e.preventDefault();
					$( '.wpshadow-category-toggle' ).prop( 'checked', true ).first().trigger( 'change' );
				}
			);

			$( '#wpshadow-disable-all-tips' ).on(
				'click',
				function (e) {
					e.preventDefault();
					$( '.wpshadow-category-toggle' ).prop( 'checked', false ).first().trigger( 'change' );
				}
			);
		}
	);
})( jQuery );
