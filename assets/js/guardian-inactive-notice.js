jQuery( document ).ready(
	function ( $ ) {
		$( '#wpshadow-guardian-notice' ).on(
			'click',
			'#wpshadow-activate-guardian-btn',
			function ( event ) {
				event.preventDefault();
				var $button      = $( this );
				var originalText = $button.text();

				$button.prop( 'disabled', true ).text( wpsGuardianInactiveNotice.activatingText );

				$.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'wpshadow_activate_guardian_from_notice',
							_wpnonce: $button.attr( 'data-nonce' )
						},
						success: function ( response ) {
							if ( response.success ) {
								$button.text( wpsGuardianInactiveNotice.activatedText ).addClass( 'disabled' );
								setTimeout(
									function () {
										$( '#wpshadow-guardian-notice' ).fadeOut(
											300,
											function () {
												$( this ).remove();
											}
										);
									},
									1500
								);
							} else {
								var failedMessage = response && response.data && response.data.message ? response.data.message : wpsGuardianInactiveNotice.activateFailedText;
								$button.prop( 'disabled', false ).text( originalText );
								window.alert( failedMessage );
							}
						},
						error: function () {
							$button.prop( 'disabled', false ).text( originalText );
							window.alert( wpsGuardianInactiveNotice.activateErrorText );
						}
					}
				);
			}
		);

		$( '#wpshadow-guardian-notice' ).on(
			'click',
			'.notice-dismiss, #wpshadow-dismiss-guardian-notice',
			function ( event ) {
				if ( event.target.id === 'wpshadow-dismiss-guardian-notice' ) {
						event.preventDefault();
				}

				$.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'wpshadow_dismiss_guardian_notice',
							_wpnonce: wpsGuardianInactiveNotice.dismissNonce
						}
					}
				);
			}
		);
	}
);
