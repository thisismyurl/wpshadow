jQuery( document ).ready(
	function ( $ ) {
		$( '.wpshadow-retention-form' ).on(
			'submit',
			function ( event ) {
				event.preventDefault();

				var $form   = $( this );
				var $button = $form.find( 'button[type="submit"]' );
				var $status = $( '#wpshadow-retention-status' );
				var data    = {
					action: 'wpshadow_update_retention_settings',
					nonce: $form.find( 'input[name="_wpnonce"]' ).val(),
					activity_log_days: $form.find( 'input[name="activity_log_days"]' ).val(),
					finding_log_days: $form.find( 'input[name="finding_log_days"]' ).val(),
					workflow_log_days: $form.find( 'input[name="workflow_log_days"]' ).val(),
					auto_cleanup_enabled: $form.find( 'input[name="auto_cleanup_enabled"]' ).prop( 'checked' ),
					cleanup_time: $form.find( 'input[name="cleanup_time"]' ).val()
				};

				$button.prop( 'disabled', true ).text( wpsDataRetentionManager.savingText );
				$status.html( '' );

				$.post(
					wpsDataRetentionManager.ajaxUrl,
					data,
					function ( response ) {
						if ( response.success ) {
							$status.html( '<span class="wps-status-success">✓ ' + wpsDataRetentionManager.savedText + '</span>' );
						} else {
							var errorMessage = response && response.data && response.data.message ? response.data.message : wpsDataRetentionManager.errorText;
							$status.html( '<span class="wps-status-error">✗ ' + errorMessage + '</span>' );
						}

						$button.prop( 'disabled', false ).text( wpsDataRetentionManager.saveButtonText );
					}
				);
			}
		);

		$( '#wpshadow-cleanup-now-btn' ).on(
			'click',
			function () {
				var $button = $( this );
				var $result = $( '#wpshadow-cleanup-result' );

				$button.prop( 'disabled', true ).text( wpsDataRetentionManager.runningText );
				$result.html( '' );

				$.post(
					wpsDataRetentionManager.ajaxUrl,
					{
						action: 'wpshadow_run_data_cleanup_now',
						nonce: wpsDataRetentionManager.cleanupNonce
					},
					function ( response ) {
						if ( response.success && response.data && response.data.results ) {
							var results = response.data.results;
							var html    = '<div class="wps-p-12-rounded-4">' +
							'<strong>✓ ' + response.data.message + '</strong><br/>' +
							'Activity logs: ' + results.activity_logs + ' removed<br/>' +
							'Finding logs: ' + results.finding_logs + ' removed<br/>' +
							'Workflow logs: ' + results.workflow_logs + ' removed' +
							'</div>';
							$result.html( html );
						} else {
							var message = response && response.data && response.data.message ? response.data.message : wpsDataRetentionManager.cleanupErrorText;
							$result.html( '<div class="wps-p-12-rounded-4">✗ ' + message + '</div>' );
						}

						$button.prop( 'disabled', false ).text( wpsDataRetentionManager.runNowText );
					}
				);
			}
		);
	}
);
