jQuery( document ).ready(
	function ($) {
		const cfg     = window.wpshadowReportBuilder || {};
		const strings = cfg.strings || {};
		const ajaxUrl = cfg.ajax_url || window.ajaxurl;

		$( '.preset-btn' ).on(
			'click',
			function (e) {
				e.preventDefault();
				const preset = $( this ).data( 'preset' );
				const today  = new Date();
				let startDate;
				const endDate = new Date( today );
				endDate.setHours( 23, 59, 59, 999 );

				switch (preset) {
					case 'today':
						startDate = new Date( today );
						startDate.setHours( 0, 0, 0, 0 );
						break;
					case 'week':
						startDate = new Date( today );
						startDate.setDate( startDate.getDate() - 7 );
						break;
					case 'month':
						startDate = new Date( today );
						startDate.setDate( startDate.getDate() - 30 );
						break;
					case 'quarter':
						startDate = new Date( today );
						startDate.setDate( startDate.getDate() - 90 );
						break;
					default:
						return;
				}

				$( '#report_start_date' ).val( startDate.toISOString().split( 'T' )[0] );
				$( '#report_end_date' ).val( endDate.toISOString().split( 'T' )[0] );
			}
		);

		$( '#email_report' ).on(
			'change',
			function () {
				$( '#email-options' ).toggle( this.checked );
			}
		);

		$( '#wpshadow-report-builder' ).on(
			'submit',
			function (e) {
				e.preventDefault();

				const formData = {
					date_from: $( '#report_start_date' ).val(),
					date_to: $( '#report_end_date' ).val(),
					category: $( '#report_category' ).val(),
					type: $( '#report_type' ).val(),
					format: $( '#report_format' ).val(),
					action: 'wpshadow_generate_report',
					nonce: $( 'input[name="report_nonce"]' ).val()
				};

				$( '#report-content' ).html( '<p class="wps-p-40">' + (strings.loading || 'Loading...') + '</p>' );

				$.ajax(
					{
						url: ajaxUrl,
						type: 'POST',
						data: formData,
						success: function (response) {
							if (response.success) {
								$( '#report-content' ).html( response.data.html );
							} else {
								$( '#report-content' ).html( '<p class="wps-report-builder-error">' + (response.data.message || (strings.error || 'Error generating report')) + '</p>' );
							}
						},
						error: function () {
							$( '#report-content' ).html( '<p class="wps-report-builder-error">' + (strings.error || 'Error generating report') + '</p>' );
						}
					}
				);
			}
		);
	}
);
