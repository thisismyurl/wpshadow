jQuery( document ).ready(
	function ( $ ) {
		$( 'input[name="frequency"]' ).on(
			'change',
			function () {
				var frequency = $( this ).val();
				$( '#scan-time-container' ).toggleClass( 'wps-scan-time-hidden', frequency === 'manual' );
			}
		);

		$( '.wpshadow-scan-frequency-form' ).on(
			'submit',
			function ( event ) {
				event.preventDefault();

				var $form   = $( this );
				var $button = $form.find( 'button[type="submit"]' );
				var $status = $( '#wpshadow-scan-status' );
				var data    = {
					action: 'wpshadow_update_scan_frequency',
					nonce: $form.find( 'input[name="_wpnonce"]' ).val(),
					frequency: $form.find( 'input[name="frequency"]:checked' ).val(),
					scan_time: $form.find( 'input[name="scan_time"]' ).val(),
					run_diagnostics: $form.find( 'input[name="run_diagnostics"]' ).prop( 'checked' ),
					run_treatments: $form.find( 'input[name="run_treatments"]' ).prop( 'checked' ),
					email_results: $form.find( 'input[name="email_results"]' ).prop( 'checked' ),
					scan_on_plugin_update: $form.find( 'input[name="scan_on_plugin_update"]' ).prop( 'checked' ),
					scan_on_theme_update: $form.find( 'input[name="scan_on_theme_update"]' ).prop( 'checked' )
				};

				$button.prop( 'disabled', true ).text( wpsScanFrequencyManager.savingText );
				$status.html( '' );

				$.post(
					wpsScanFrequencyManager.ajaxUrl,
					data,
					function ( response ) {
						if ( response.success ) {
							$status.html( '<span class="wps-scan-status-success">✓ ' + wpsScanFrequencyManager.savedText + '</span>' );
							if ( response.data && response.data.next_scan_time ) {
								$( '#next-scan-time' ).text( response.data.next_scan_time );
							}
						} else {
							var errorMessage = response && response.data && response.data.message ? response.data.message : wpsScanFrequencyManager.errorText;
							$status.html( '<span class="wps-scan-status-error">✗ ' + errorMessage + '</span>' );
						}

						$button.prop( 'disabled', false ).text( wpsScanFrequencyManager.saveButtonText );
					}
				);
			}
		);

		$( '#wpshadow-scan-now-btn' ).on(
			'click',
			function () {
				var $button = $( this );
				var $result = $( '#wpshadow-scan-result' );

				$button.prop( 'disabled', true ).text( wpsScanFrequencyManager.scanningText );
				$result.html( '' );

				$.post(
					wpsScanFrequencyManager.ajaxUrl,
					{
						action: 'wpshadow_run_scan_now',
						nonce: wpsScanFrequencyManager.runNowNonce
					},
					function ( response ) {
						if ( response.success && response.data && response.data.results ) {
							var results = response.data.results;
							var html    = '<div class="wps-p-12-rounded-4">' +
							'<strong>✓ ' + wpsScanFrequencyManager.scanCompleteText + '</strong><br/>' +
							'Diagnostics run: ' + results.diagnostics_run + '<br/>' +
							'Findings: ' + results.findings +
							'</div>';
							$result.html( html );
						} else {
							var errorMessage = response && response.data && response.data.message ? response.data.message : wpsScanFrequencyManager.scanErrorText;
							$result.html( '<div class="wps-p-12-rounded-4">✗ ' + errorMessage + '</div>' );
						}

						$button.prop( 'disabled', false ).text( wpsScanFrequencyManager.startScanText );
					}
				);
			}
		);
	}
);
