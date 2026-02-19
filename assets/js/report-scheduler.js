jQuery( document ).ready( function( $ ) {
	$( '.wpshadow-schedule-form' ).on( 'submit', function( event ) {
		event.preventDefault();

		var $form = $( this );
		var $button = $form.find( 'button[type="submit"]' );
		var $status = $form.find( '.schedule-status' );
		var reportType = $form.data( 'report-type' );
		var recipientsText = $form.find( 'input[name="recipients"]' ).val();
		var recipients = recipientsText
			.split( ',' )
			.map( function( recipient ) {
				return $.trim( recipient );
			} )
			.filter( function( recipient ) {
				return recipient.length > 0;
			} );
		var data = {
			action: 'wpshadow_update_report_schedule',
			nonce: $form.find( 'input[name="_wpnonce"]' ).val(),
			report_type: reportType,
			enabled: $form.find( 'input[name="enabled"]' ).prop( 'checked' ),
			frequency: $form.find( 'select[name="frequency"]' ).val(),
			recipients: recipients,
		};

		$button.prop( 'disabled', true ).text( wpsReportScheduler.savingText );
		$status.html( '' );

		$.post( wpsReportScheduler.ajaxUrl, data, function( response ) {
			if ( response.success ) {
				$status.html( '<span class="wps-schedule-success">✓ ' + wpsReportScheduler.savedText + '</span>' );
			} else {
				var errorMessage = response && response.data && response.data.message ? response.data.message : wpsReportScheduler.errorText;
				$status.html( '<span class="wps-schedule-error">✗ ' + errorMessage + '</span>' );
			}

			$button.prop( 'disabled', false ).text( wpsReportScheduler.saveButton );
		} );
	} );
} );
