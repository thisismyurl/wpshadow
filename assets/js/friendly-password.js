/**
 * WPShadow Friendly Password Generator
 *
 * Overrides WordPress default password generation with friendly memorable passwords
 */

(function ($) {
	'use strict';

	$( document ).ready(
		function () {
			// Wait for WordPress password generation to be ready
			if (typeof wp === 'undefined' || typeof wp.passwordStrength === 'undefined') {
				return;
			}

			// Override the generate password button click
			var $generateButton = $( '.wp-generate-pw' );
			var $passwordInput  = $( '#pass1' );

			if ($generateButton.length && $passwordInput.length && typeof wpshadowPassword !== 'undefined') {
				// Replace the default password on page load if field is visible
				if ($passwordInput.is( ':visible' ) && $passwordInput.val() === '') {
					$passwordInput.val( wpshadowPassword.password );
					$passwordInput.trigger( 'pwupdate' );
				}

				// Hook into generate button to use our password
				$generateButton.on(
					'click.wpshadow',
					function (e) {
						// Let WordPress handle the UI, but override the password value
						setTimeout(
							function () {
								if ($passwordInput.is( ':visible' )) {
									// Generate a new friendly password
									$.ajax(
										{
											url: ajaxurl,
											type: 'POST',
											async: false,
											data: {
												action: 'wpshadow_generate_password',
												nonce: wpshadowPassword.nonce || ''
											},
											success: function (response) {
												if (response.success && response.data.password) {
													$passwordInput.val( response.data.password );
													$passwordInput.trigger( 'pwupdate' );
												}
											}
										}
									);
								}
							},
							10
						);
					}
				);
			}
		}
	);

})( jQuery );
