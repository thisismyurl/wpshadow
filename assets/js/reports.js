/**
 * WPShadow Report Scripts
 * Consolidated JavaScript for report builder and renderer functionality
 * Extracted from inline scripts in class-report-builder.php and class-report-renderer.php
 *
 * @package WPShadow
 */

(function ($) {
	'use strict';

	/**
	 * WPShadow Report Builder Module
	 */
	const WPShadowReportBuilder = {

		/**
		 * Initialize report builder
		 */
		init: function () {
			// Hide loading spinner on page load (Issue #1684)
			$( '#loading-spinner' ).hide();
			$( '#report-preview' ).hide();

			this.initPresetButtons();
			this.initDatePicker();
			this.initReportTypeSelection();
			this.initFormSubmission();
			this.initGenerateButton();
		},

		/**
		 * Initialize preset date buttons (Last 7 days, Last 30 days, etc.)
		 */
		initPresetButtons: function () {
			const self = this;

			$( document ).on(
				'click',
				'.wps-preset-btn',
				function (e) {
					e.preventDefault();
					const preset = $( this ).data( 'preset' );
					self.applyDatePreset( preset );

					// Update button states
					$( '.wps-preset-btn' ).removeClass( 'selected' );
					$( this ).addClass( 'selected' );
				}
			);
		},

		/**
		 * Apply date preset values
		 */
		applyDatePreset: function (preset) {
			const today   = new Date();
			const endDate = new Date();
			let startDate = new Date();

			switch (preset) {
				case 'today':
					startDate = new Date( today );
					break;
				case 'yesterday':
					startDate = new Date( today.getTime() - 24 * 60 * 60 * 1000 );
					endDate   = new Date( startDate );
					break;
				case 'last_7_days':
					startDate = new Date( today.getTime() - 7 * 24 * 60 * 60 * 1000 );
					break;
				case 'last_30_days':
					startDate = new Date( today.getTime() - 30 * 24 * 60 * 60 * 1000 );
					break;
				case 'last_90_days':
					startDate = new Date( today.getTime() - 90 * 24 * 60 * 60 * 1000 );
					break;
				case 'this_month':
					startDate = new Date( today.getFullYear(), today.getMonth(), 1 );
					break;
				case 'last_month':
					startDate = new Date( today.getFullYear(), today.getMonth() - 1, 1 );
					endDate   = new Date( today.getFullYear(), today.getMonth(), 0 );
					break;
				case 'this_year':
					startDate = new Date( today.getFullYear(), 0, 1 );
					break;
				case 'last_year':
					startDate = new Date( today.getFullYear() - 1, 0, 1 );
					endDate   = new Date( today.getFullYear() - 1, 11, 31 );
					break;
			}

			// Update input fields
			this.updateDateInputs( startDate, endDate );
		},

		/**
		 * Update date input fields
		 */
		updateDateInputs: function (startDate, endDate) {
			const startStr = this.formatDateForInput( startDate );
			const endStr   = this.formatDateForInput( endDate );

			$( 'input[name="date_from"]' ).val( startStr );
			$( 'input[name="date_to"]' ).val( endStr );

			// Trigger change event for any listeners
			$( 'input[name="date_from"], input[name="date_to"]' ).change();
		},

		/**
		 * Format date for input field (YYYY-MM-DD)
		 */
		formatDateForInput: function (date) {
			const month = String( date.getMonth() + 1 ).padStart( 2, '0' );
			const day   = String( date.getDate() ).padStart( 2, '0' );
			const year  = date.getFullYear();
			return year + '-' + month + '-' + day;
		},

		/**
		 * Initialize date picker if using HTML5 input type="date"
		 */
		initDatePicker: function () {
			const self = this;

			// Handle date input changes for validation
			$( document ).on(
				'change',
				'input[name="date_from"], input[name="date_to"]',
				function () {
					self.validateDateRange();
				}
			);
		},

		/**
		 * Validate date range (end date must be after start date)
		 */
		validateDateRange: function () {
			const startStr = $( 'input[name="date_from"]' ).val();
			const endStr   = $( 'input[name="date_to"]' ).val();

			if (startStr && endStr) {
				const startDate = new Date( startStr );
				const endDate   = new Date( endStr );

				if (startDate > endDate) {
					WPShadowAdmin.showNotice( 'error', wpshadowReportBuilder.i18n.invalidDateRange || 'End date must be after start date' );
					$( 'input[name="date_to"]' ).val( '' );
				}
			}
		},

		/**
		 * Initialize report type selection
		 */
		initReportTypeSelection: function () {
			const self = this;

			$( document ).on(
				'click',
				'.wps-report-type-option',
				function (e) {
					e.preventDefault();
					const type = $( this ).data( 'type' );
					self.selectReportType( type );
				}
			);
		},

		/**
		 * Select report type and update form
		 */
		selectReportType: function (type) {
			$( '.wps-report-type-option' ).removeClass( 'selected' );
			$( '[data-type="' + type + '"]' ).addClass( 'selected' );

			// Update hidden input or form state
			$( 'input[name="report_type"]' ).val( type );

			// Show/hide conditional form sections
			this.updateFormSections( type );
		},

		/**
		 * Show/hide form sections based on report type
		 */
		updateFormSections: function (type) {
			// Hide all conditional sections
			$( '[data-section-type]' ).hide();
			// Show sections for this report type
			$( '[data-section-type*="' + type + '"]' ).show();
		},

		/**
		 * Initialize form submission
		 */
		initFormSubmission: function () {
			const self = this;

			$( document ).on(
				'submit',
				'.wps-report-builder form',
				function (e) {
					e.preventDefault();
					self.submitReportForm( $( this ) );
				}
			);
		},

		/**
		 * Initialize generate button click handler (Issue #1684)
		 */
		initGenerateButton: function () {
			const self = this;

			$( document ).on(
				'click',
				'#generate-report-btn',
				function (e) {
					e.preventDefault();
					const form = $( '#wpshadow-report-form' );
					if (form.length) {
						self.submitReportForm( form );
					}
				}
			);
		},

		/**
		 * Submit report builder form
		 */
		submitReportForm: function (form) {
			const self         = this;
			const submitBtn    = $( '#generate-report-btn' );
			const originalText = submitBtn.length ? submitBtn.text() : '';

			// Validate required fields
			if ( ! this.validateForm( form )) {
				return;
			}

			// Show loading state
			if (submitBtn.length) {
				submitBtn.prop( 'disabled', true ).text( wpshadowReportBuilder.i18n.generating || 'Generating...' );
			}
			$( '#loading-spinner' ).show();
			$( '#report-preview' ).hide();

			const formData = new FormData( form[0] );
			formData.append( 'action', 'wpshadow_generate_report' );
			formData.append( 'nonce', wpshadowReportBuilder.nonce );

			$.ajax(
				{
					url: wpshadowReportBuilder.ajaxUrl,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					dataType: 'json',
					success: function (response) {
						if (response.success) {
							const data = response.data || {};
							// Display report output
							if (data.html) {
								$( '#wps-report-output' ).html( data.html ).show();
								WPShadowAdmin.showNotice( 'success', data.message || wpshadowReportBuilder.i18n.reportGenerated );
								// Scroll to report
								$( 'html, body' ).animate(
									{
										scrollTop: $( '#wps-report-output' ).offset().top - 100
									},
									500
								);
							}
						} else {
							const errorData = response.data || {};
							WPShadowAdmin.showNotice( 'error', errorData.message || wpshadowReportBuilder.i18n.error );
						}
					},
					error: function () {
						WPShadowAdmin.showNotice( 'error', wpshadowReportBuilder.i18n.error );
						$( '#loading-spinner' ).hide();
					},
					complete: function () {
						if (submitBtn.length) {
							submitBtn.prop( 'disabled', false ).text( originalText );
						}
						$( '#loading-spinner' ).hide();
					}
				}
			);
		},

		/**
		 * Validate report form
		 */
		validateForm: function (form) {
			let isValid = true;

			// Check required fields
			form.find( '[required]' ).each(
				function () {
					if ( ! $( this ).val()) {
						$( this ).addClass( 'error' );
						isValid = false;
					} else {
						$( this ).removeClass( 'error' );
					}
				}
			);

			if ( ! isValid) {
				WPShadowAdmin.showNotice( 'error', wpshadowReportBuilder.i18n.fillAllFields || 'Please fill all required fields' );
			}

			return isValid;
		}
	};

	/**
	 * WPShadow Report Display/Export Module
	 */
	const WPShadowReportDisplay = {

		/**
		 * Initialize report display functionality
		 */
		init: function () {
			this.initExportButtons();
			this.initEmailReport();
			this.initPrintButton();
			this.initShareOptions();
		},

		/**
		 * Initialize export buttons (PDF, CSV, etc.)
		 */
		initExportButtons: function () {
			const self = this;

			$( document ).on(
				'click',
				'[data-export-format]',
				function (e) {
					e.preventDefault();
					const format = $( this ).data( 'export-format' );
					self.exportReport( format );
				}
			);
		},

		/**
		 * Export report in specified format
		 */
		exportReport: function (format) {
			const reportData   = this.getReportData();
			const button       = $( '[data-export-format="' + format + '"]' );
			const originalText = button.text();

			button.prop( 'disabled', true ).text( wpshadowReportDisplay.i18n.exporting || 'Exporting...' );

			const formData = new FormData();
			formData.append( 'action', 'wps_export_report' );
			formData.append( 'format', format );
			formData.append( 'report_data', JSON.stringify( reportData ) );
			formData.append( 'nonce', wpshadowReportDisplay.nonce );

			$.ajax(
				{
					url: wpshadowReportDisplay.ajaxUrl,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					xhrFields: {
						responseType: 'blob'
					},
					success: function (response) {
						const url  = window.URL.createObjectURL( response );
						const a    = document.createElement( 'a' );
						a.href     = url;
						a.download = 'wpshadow-report.' + format;
						document.body.appendChild( a );
						a.click();
						window.URL.revokeObjectURL( url );
						document.body.removeChild( a );
						WPShadowAdmin.showNotice( 'success', wpshadowReportDisplay.i18n.exported );
					},
					error: function () {
						WPShadowAdmin.showNotice( 'error', wpshadowReportDisplay.i18n.exportError );
					},
					complete: function () {
						button.prop( 'disabled', false ).text( originalText );
					}
				}
			);
		},

		/**
		 * Initialize email report functionality
		 */
		initEmailReport: function () {
			const self = this;

			$( document ).on(
				'click',
				'[data-action="email-report"]',
				function (e) {
					e.preventDefault();
					WPShadowAdmin.openModal( 'email-report-modal' );
				}
			);

			$( document ).on(
				'submit',
				'#email-report-form',
				function (e) {
					e.preventDefault();
					self.sendReportEmail( $( this ) );
				}
			);
		},

		/**
		 * Send report via email
		 */
		sendReportEmail: function (form) {
			const self         = this;
			const submitBtn    = form.find( 'button[type="submit"]' );
			const originalText = submitBtn.text();

			submitBtn.prop( 'disabled', true ).text( wpshadowReportDisplay.i18n.sending || 'Sending...' );

			const formData = new FormData( form[0] );
			formData.append( 'action', 'wps_email_report' );
			formData.append( 'report_data', JSON.stringify( this.getReportData() ) );
			formData.append( 'nonce', wpshadowReportDisplay.nonce );

			$.ajax(
				{
					url: wpshadowReportDisplay.ajaxUrl,
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					dataType: 'json',
					success: function (response) {
						if (response.success) {
							WPShadowAdmin.showNotice( 'success', response.message || wpshadowReportDisplay.i18n.emailSent );
							WPShadowAdmin.closeModal( $( '#email-report-modal' ) );
							form[0].reset();
						} else {
							WPShadowAdmin.showNotice( 'error', response.message );
						}
					},
					error: function () {
						WPShadowAdmin.showNotice( 'error', wpshadowReportDisplay.i18n.emailError );
					},
					complete: function () {
						submitBtn.prop( 'disabled', false ).text( originalText );
					}
				}
			);
		},

		/**
		 * Initialize print button
		 */
		initPrintButton: function () {
			$( document ).on(
				'click',
				'[data-action="print-report"]',
				function (e) {
					e.preventDefault();
					window.print();
				}
			);
		},

		/**
		 * Initialize share options
		 */
		initShareOptions: function () {
			const self = this;

			$( document ).on(
				'click',
				'[data-share-platform]',
				function (e) {
					e.preventDefault();
					const platform = $( this ).data( 'share-platform' );
					const url      = $( this ).data( 'share-url' ) || window.location.href;
					const title    = $( this ).data( 'share-title' ) || document.title;

					self.shareReport( platform, url, title );
				}
			);
		},

		/**
		 * Share report to social platform
		 */
		shareReport: function (platform, url, title) {
			let shareUrl       = '';
			const encodedUrl   = encodeURIComponent( url );
			const encodedTitle = encodeURIComponent( title );

			switch (platform) {
				case 'facebook':
					shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl;
					break;
				case 'twitter':
					shareUrl = 'https://twitter.com/intent/tweet?url=' + encodedUrl + '&text=' + encodedTitle;
					break;
				case 'linkedin':
					shareUrl = 'https://www.linkedin.com/sharing/share-offsite/?url=' + encodedUrl;
					break;
				case 'email':
					shareUrl = 'mailto:?subject=' + encodedTitle + '&body=' + encodedUrl;
					break;
			}

			if (shareUrl) {
				if (platform === 'email') {
					window.location.href = shareUrl;
				} else {
					window.open( shareUrl, '_blank', 'width=600,height=400' );
				}
			}
		},

		/**
		 * Extract report data from the page
		 */
		getReportData: function () {
			return {
				title: $( '[data-report-title]' ).text(),
				generatedAt: $( '[data-report-generated]' ).text(),
				content: $( '#wps-report-output' ).html()
			};
		}
	};

	/**
	 * Initialize on document ready
	 */
	$( document ).ready(
		function () {
			if ($( '.wps-report-builder' ).length) {
				WPShadowReportBuilder.init();
				window.WPShadowReportBuilder = WPShadowReportBuilder;
			}

			if ($( '#wps-report-output' ).length) {
				WPShadowReportDisplay.init();
				window.WPShadowReportDisplay = WPShadowReportDisplay;
			}
		}
	);

})( jQuery );
