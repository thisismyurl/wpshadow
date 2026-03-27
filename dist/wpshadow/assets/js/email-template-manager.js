jQuery( document ).ready(
	function ($) {
		const cfg             = window.wpshadowEmailTemplateManager || {};
		const strings         = cfg.strings || {};
		const templateBaseUrl = cfg.template_base_url || '';

		$( '.wps-email-template-radio' ).on(
			'change',
			function () {
				if ( ! templateBaseUrl) {
					return;
				}

				window.location.href = templateBaseUrl + this.value;
			}
		);

		$( '#wpshadow-email-template-form' ).on(
			'submit',
			function (e) {
				e.preventDefault();

				const $form   = $( this );
				const $btn    = $form.find( 'button[type="submit"]' );
				const $status = $( '#wpshadow-template-status' );

				$btn.prop( 'disabled', true ).text( strings.saving || 'Saving...' );
				$status.html( '' );

				$.ajax(
					{
						type: 'POST',
						url: ajaxurl,
						data: $form.serialize(),
						dataType: 'json',
						success: function (response) {
							if (response.success) {
								$status.html( '<div class="wps-p-10-rounded-4">✓ ' + response.data.message + '</div>' );
							} else {
								$status.html( '<div class="wps-p-10-rounded-4">✗ ' + (response.data.message || (strings.error_saving || 'Error saving template')) + '</div>' );
							}

							$btn.prop( 'disabled', false ).text( strings.save_template || 'Save Template' );
						},
						error: function () {
							$status.html( '<div class="wps-p-10-rounded-4">✗ ' + (strings.network_error || 'Network error') + '</div>' );
							$btn.prop( 'disabled', false ).text( strings.save_template || 'Save Template' );
						}
					}
				);
			}
		);

		$( '#wpshadow-email-template-form textarea' ).on(
			'input change',
			function () {
				const html  = $( 'textarea[name="template_html"]' ).val();
				let preview = html
				.replace( '{title}', strings.preview_title || 'WPShadow Report' )
				.replace( '{content}', strings.preview_content || 'Your site health report is ready.' )
				.replace( '{footer}', strings.preview_footer || 'Powered by WPShadow' );

				if (preview.indexOf( '<' ) === -1) {
					preview = preview.replace( /\n/g, '<br />' );
				}

				$( '#wpshadow-template-preview' ).html( preview );
			}
		);

		$( '.wps-reset-email-template' ).on(
			'click',
			function () {
				const templateKey = $( this ).data( 'template-key' );

				if ( ! window.confirm( strings.reset_confirm || 'Reset this template to default?' )) {
					return;
				}

				$.post(
					ajaxurl,
					{
						action: 'wpshadow_reset_email_template',
						template_key: templateKey,
						nonce: cfg.nonce || ''
					},
					function (response) {
						if (response.success) {
							window.location.reload();
						}
					}
				);
			}
		);

		$( '#wpshadow-email-template-form textarea:first' ).trigger( 'change' );
	}
);
