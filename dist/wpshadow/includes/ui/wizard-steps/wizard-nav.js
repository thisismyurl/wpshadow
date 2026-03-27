/**
 * Wizard Navigation Handler
 *
 * Handles navigation between wizard steps, form validation, and data persistence.
 *
 * @package WPShadow
 * @since   1.6030.2148
 */

( function( $ ) {
	'use strict';

	function showAlert( message, title, type ) {
		if ( window.WPShadowModal && typeof window.WPShadowModal.alert === 'function' ) {
			window.WPShadowModal.alert( {
				title: title || 'Notice',
				message: message,
				type: type || 'warning',
			} );
			return;
		}
		window.alert( message );
	}

	const WPShadowWizard = {
		currentStep: 'trigger-selection',
		steps: [ 'trigger-selection', 'trigger-config', 'action-selection', 'action-config', 'review' ],
		formData: {},

		/**
		 * Initialize wizard
		 */
		init: function() {
			// Get current step from URL
			const params = new URLSearchParams( window.location.search );
			this.currentStep = params.get( 'step' ) || 'trigger-selection';

			// Store form data from URL params
			this.formData = {
				trigger_id: params.get( 'trigger_id' ) || '',
				action_id: params.get( 'action_id' ) || '',
				frequency: params.get( 'frequency' ) || 'daily',
				time: params.get( 'time' ) || '02:00',
				day: params.get( 'day' ) || 'monday',
				month_day: params.get( 'month_day' ) || '1',
				use_offpeak: params.get( 'use_offpeak' ) || '',
				email_recipients: params.get( 'email_recipients' ) || '',
				email_subject: params.get( 'email_subject' ) || '',
				automation_name: params.get( 'automation_name' ) || '',
			};

			this.bindEvents();
			this.updateProgressBar();
		},

		/**
		 * Bind event handlers
		 */
		bindEvents: function() {
			const self = this;

			// Next button
			$( '.wpshadow-wizard-next-btn' ).on( 'click', function( e ) {
				e.preventDefault();
				self.handleNext();
			} );

			// Back button
			$( '.wpshadow-wizard-back-btn' ).on( 'click', function( e ) {
				e.preventDefault();
				self.handleBack();
			} );

			// Save button (on review step)
			$( '.wpshadow-wizard-save-btn' ).on( 'click', function( e ) {
				e.preventDefault();
				self.handleSave();
			} );

			// Cancel button
			$( '.wpshadow-wizard-cancel-btn' ).on( 'click', function( e ) {
				e.preventDefault();
				window.location.href = '?page=wpshadow-automations';
			} );
		},

		/**
		 * Handle Next button click
		 */
		handleNext: function() {
			if ( ! this.validateCurrentStep() ) {
				return;
			}

			// Collect form data for current step
			this.collectStepData();

			// Navigate to next step
			const currentIndex = this.steps.indexOf( this.currentStep );
			if ( currentIndex < this.steps.length - 1 ) {
				const nextStep = this.steps[ currentIndex + 1 ];
				this.navigateToStep( nextStep );
			}
		},

		/**
		 * Handle Back button click
		 */
		handleBack: function() {
			// Collect form data for current step
			this.collectStepData();

			// Navigate to previous step
			const currentIndex = this.steps.indexOf( this.currentStep );
			if ( currentIndex > 0 ) {
				const prevStep = this.steps[ currentIndex - 1 ];
				this.navigateToStep( prevStep );
			}
		},

		/**
		 * Handle Save button click
		 */
		handleSave: function() {
			if ( ! this.validateCurrentStep() ) {
				return;
			}

			// Collect final form data
			this.collectStepData();

			// Show loading state
			const $saveBtn = $( '.wpshadow-wizard-save-btn' );
			$saveBtn.prop( 'disabled', true ).text( 'Saving...' );

			// Send AJAX request to save automation
			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wpshadow_save_automation',
					nonce: wpShadowWizard.nonce,
					...this.formData,
				},
				success: ( response ) => {
					if ( response.success ) {
						// Redirect to automations page with success message
						window.location.href = '?page=wpshadow-automations&saved=true';
					} else {
						showAlert( response.data.message || 'Error saving automation', 'Save Failed', 'danger' );
						$saveBtn.prop( 'disabled', false ).text( 'Save Automation' );
					}
				},
				error: ( xhr, status, error ) => {
					console.error( 'AJAX Error:', error );
					showAlert( 'Error saving automation. Please try again.', 'Save Failed', 'danger' );
					$saveBtn.prop( 'disabled', false ).text( 'Save Automation' );
				},
			} );
		},

		/**
		 * Validate current step form
		 */
		validateCurrentStep: function() {
			const $form = $( '.wpshadow-wizard-step' );

			// Validate required fields
			let isValid = true;

			if ( this.currentStep === 'trigger-selection' ) {
				if ( ! $form.find( 'input[name="trigger_id"]:checked' ).val() ) {
					showAlert( 'Please select a trigger', 'Missing Information', 'warning' );
					isValid = false;
				}
			}

			if ( this.currentStep === 'trigger-config' ) {
				if ( ! $form.find( 'input[name="time"]' ).val() ) {
					showAlert( 'Please select a time', 'Missing Information', 'warning' );
					isValid = false;
				}
			}

			if ( this.currentStep === 'action-selection' ) {
				if ( ! $form.find( 'input[name="action_id"]:checked' ).val() ) {
					showAlert( 'Please select an action', 'Missing Information', 'warning' );
					isValid = false;
				}
			}

			if ( this.currentStep === 'action-config' ) {
				if ( $form.find( 'input[name="email_recipients"]' ).length ) {
					if ( ! $form.find( 'input[name="email_recipients"]' ).val() ) {
						showAlert( 'Please enter email recipients', 'Missing Information', 'warning' );
						isValid = false;
					}
				}
			}

			if ( this.currentStep === 'review' ) {
				if ( ! $form.find( 'input[name="automation_name"]' ).val() ) {
					showAlert( 'Please enter an automation name', 'Missing Information', 'warning' );
					isValid = false;
				}
			}

			return isValid;
		},

		/**
		 * Collect form data from current step
		 */
		collectStepData: function() {
			const $form = $( '.wpshadow-wizard-step' );

			if ( this.currentStep === 'trigger-selection' ) {
				this.formData.trigger_id = $form.find( 'input[name="trigger_id"]:checked' ).val() || this.formData.trigger_id;
			}

			if ( this.currentStep === 'trigger-config' ) {
				this.formData.frequency = $form.find( 'input[name="frequency"]:checked' ).val() || 'daily';
				this.formData.time = $form.find( 'input[name="time"]' ).val() || '02:00';
				this.formData.day = $form.find( 'input[name="day"]:checked' ).val() || 'monday';
				this.formData.month_day = $form.find( 'input[name="month_day"]' ).val() || '1';
				this.formData.use_offpeak = $form.find( 'input[name="use_offpeak"]' ).is( ':checked' ) ? '1' : '';
			}

			if ( this.currentStep === 'action-selection' ) {
				this.formData.action_id = $form.find( 'input[name="action_id"]:checked' ).val() || this.formData.action_id;
			}

			if ( this.currentStep === 'action-config' ) {
				this.formData.email_recipients = $form.find( 'input[name="email_recipients"]' ).val() || '';
				this.formData.email_subject = $form.find( 'input[name="email_subject"]' ).val() || '';
			}

			if ( this.currentStep === 'review' ) {
				this.formData.automation_name = $form.find( 'input[name="automation_name"]' ).val() || '';
			}
		},

		/**
		 * Navigate to a specific step
		 */
		navigateToStep: function( step ) {
			const params = new URLSearchParams( window.location.search );

			// Update form data in URL
			Object.keys( this.formData ).forEach( ( key ) => {
				if ( this.formData[ key ] ) {
					params.set( key, this.formData[ key ] );
				}
			} );

			// Set step parameter
			params.set( 'step', step );

			// Navigate
			window.location.href = '?' + params.toString();
		},

		/**
		 * Update progress bar visibility and styling
		 */
		updateProgressBar: function() {
			const currentIndex = this.steps.indexOf( this.currentStep );

			// Update all step indicators
			this.steps.forEach( ( step, index ) => {
				const $step = $( `.wpshadow-progress-step[data-step="${step}"]` );

				if ( index < currentIndex ) {
					$step.addClass( 'completed' ).removeClass( 'active' );
				} else if ( index === currentIndex ) {
					$step.addClass( 'active' ).removeClass( 'completed' );
				} else {
					$step.removeClass( 'active completed' );
				}
			} );

			// Update Back button visibility
			if ( currentIndex > 0 ) {
				$( '.wpshadow-wizard-back-btn' ).show();
			} else {
				$( '.wpshadow-wizard-back-btn' ).hide();
			}

			// Update Next/Save button visibility
			if ( currentIndex === this.steps.length - 1 ) {
				$( '.wpshadow-wizard-next-btn' ).hide();
				$( '.wpshadow-wizard-save-btn' ).show();
			} else {
				$( '.wpshadow-wizard-next-btn' ).show();
				$( '.wpshadow-wizard-save-btn' ).hide();
			}
		},
	};

	// Initialize when document is ready
	$( document ).ready( function() {
		if ( $( '.wpshadow-wizard-container' ).length ) {
			WPShadowWizard.init();
		}
	} );

	// Export for testing
	window.WPShadowWizard = WPShadowWizard;
} )( jQuery );
