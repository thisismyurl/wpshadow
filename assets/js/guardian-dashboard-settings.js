/**
 * WPShadow Guardian Dashboard & Settings JavaScript
 * Handles AJAX interactions, form submissions, and UI updates
 */

(function($) {
	'use strict';
	
	// Guardian Dashboard & Settings Manager
	const GuardianAdmin = {
		
		/**
		 * Initialize all event handlers
		 */
		init: function() {
			this.setupEventHandlers();
			this.setupModalHandlers();
			this.setupFormValidation();
		},
		
		/**
		 * Setup event handlers for all components
		 */
		setupEventHandlers: function() {
			// Quick Action Buttons
			$(document).on('click', '.guardian-quick-actions .button', this.handleQuickAction.bind(this));
			
			// Preset Date Buttons
			$(document).on('click', '.preset-btn', this.handlePresetClick.bind(this));
			
			// Form Submissions
			$(document).on('submit', '.guardian-settings-form', this.handleSettingsSave.bind(this));
			$(document).on('submit', '.guardian-report-form', this.handleReportGenerate.bind(this));
			$(document).on('submit', '.guardian-notification-form', this.handleNotificationSave.bind(this));
			
			// Recovery Point Actions
			$(document).on('click', '.recovery-item .button', this.handleRecoveryRestore.bind(this));
			
			// Test Email Button
			$(document).on('click', '.btn-test-email', this.handleTestEmail.bind(this));
			
		// Settings Tab Navigation (keyboard accessible)
		$(document).on('click keydown', '.nav-tab', this.handleTabSwitch.bind(this));
			
			// Email Modal Actions
			$(document).on('click', '.btn-send-email', this.handleEmailSend.bind(this));
			$(document).on('click', '.btn-email-cancel', this.handleEmailCancel.bind(this));
			$(document).on('click', '.close', this.handleEmailCancel.bind(this));
			
			// Subscription Management
			$(document).on('click', '.btn-add-subscription', this.handleAddSubscription.bind(this));
			$(document).on('click', '.btn-remove-subscription', this.handleRemoveSubscription.bind(this));
		},
		
		/**
		 * Handle quick action buttons (Run Diagnostics, Preview Fixes, Settings)
		 */
		handleQuickAction: function(e) {
			const $button = $(e.currentTarget);
			const action = $button.data('action');
			
			$button.prop('disabled', true);
			$button.append(' <span class="spinner"></span>');
			
			const request = {
				action: 'wpshadow_' + action,
				nonce: wpshadow.nonce
			};
			
			$.post(wpshadow.ajax_url, request, function(response) {
				if (response.success) {
					GuardianAdmin.showNotification('Action completed successfully', 'success');
					
					// Refresh dashboard if needed
					if (action === 'run_diagnostics') {
						location.reload();
					}
				} else {
					GuardianAdmin.showNotification(response.data || 'Action failed', 'error');
				}
			}).always(function() {
				$button.prop('disabled', false);
				$button.find('.spinner').remove();
			});
		},
		
		/**
		 * Handle preset date button clicks
		 */
		handlePresetClick: function(e) {
			e.preventDefault();
			
			const $button = $(e.currentTarget);
			const preset = $button.data('preset');
			const dates = GuardianAdmin.calculateDateRange(preset);
			
			// Update form fields
			$('.report-start-date').val(dates.start);
			$('.report-end-date').val(dates.end);
			
			// Update active button
			$('.preset-btn').removeClass('active');
			$button.addClass('active');
		},
		
		/**
		 * Calculate date range for preset
		 */
		calculateDateRange: function(preset) {
			const today = new Date();
			let start = new Date();
			
			switch(preset) {
				case 'today':
					start = new Date(today);
					break;
				case '7days':
					start.setDate(today.getDate() - 7);
					break;
				case '30days':
					start.setDate(today.getDate() - 30);
					break;
				case '90days':
					start.setDate(today.getDate() - 90);
					break;
			}
			
			return {
				start: GuardianAdmin.formatDate(start),
				end: GuardianAdmin.formatDate(today)
			};
		},
		
		/**
		 * Format date for input field (YYYY-MM-DD)
		 */
		formatDate: function(date) {
			const year = date.getFullYear();
			const month = String(date.getMonth() + 1).padStart(2, '0');
			const day = String(date.getDate()).padStart(2, '0');
			return year + '-' + month + '-' + day;
		},
		
		/**
		 * Handle settings form submission
		 */
		handleSettingsSave: function(e) {
			e.preventDefault();
			
			const $form = $(e.currentTarget);
			const $button = $form.find('button[type="submit"]');
			
			// Collect form data
			const formData = new FormData($form[0]);
			formData.append('action', 'wpshadow_save_guardian_settings');
			formData.append('nonce', wpshadow.nonce);
			
			$button.prop('disabled', true);
			$button.append(' <span class="spinner"></span>');
			
			$.ajax({
				url: wpshadow.ajax_url,
				type: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				success: function(response) {
					if (response.success) {
						GuardianAdmin.showNotification('Settings saved successfully', 'success');
					} else {
						GuardianAdmin.showNotification(response.data || 'Failed to save settings', 'error');
					}
				},
				error: function() {
					GuardianAdmin.showNotification('An error occurred while saving settings', 'error');
				}
			}).always(function() {
				$button.prop('disabled', false);
				$button.find('.spinner').remove();
			});
		},
		
		/**
		 * Handle report generation
		 */
		handleReportGenerate: function(e) {
			e.preventDefault();
			
			const $form = $(e.currentTarget);
			const $button = $form.find('button[name="generate"]');
			
			const data = {
				action: 'wpshadow_generate_report',
				nonce: wpshadow.nonce,
				start_date: $form.find('.report-start-date').val(),
				end_date: $form.find('.report-end-date').val(),
				report_type: $form.find('.report-type').val(),
				export_format: $form.find('.export-format').val()
			};
			
			$button.prop('disabled', true);
			$button.append(' <span class="spinner"></span>');
			
			$.post(wpshadow.ajax_url, data, function(response) {
				if (response.success) {
					// Display report preview
					$('.report-preview-section').html(response.data.preview);
					GuardianAdmin.showNotification('Report generated successfully', 'success');
					
					// Enable email button
					$form.find('button[name="email"]').prop('disabled', false);
				} else {
					GuardianAdmin.showNotification(response.data || 'Failed to generate report', 'error');
				}
			}).always(function() {
				$button.prop('disabled', false);
				$button.find('.spinner').remove();
			});
		},
		
		/**
		 * Handle notification preferences save
		 */
		handleNotificationSave: function(e) {
			e.preventDefault();
			
			const $form = $(e.currentTarget);
			const $button = $form.find('button[type="submit"]');
			
			const data = {
				action: 'wpshadow_save_notifications',
				nonce: wpshadow.nonce,
				preferences: {}
			};
			
			// Collect alert type preferences
			$form.find('.alert-type-item input[type="checkbox"]').each(function() {
				data.preferences[$(this).data('type')] = this.checked;
			});
			
			// Collect email settings
			data.default_email = $form.find('input[name="default_email"]').val();
			data.digest_mode = $form.find('input[name="digest_mode"]').prop('checked');
			
			$button.prop('disabled', true);
			$button.append(' <span class="spinner"></span>');
			
			$.post(wpshadow.ajax_url, data, function(response) {
				if (response.success) {
					GuardianAdmin.showNotification('Notification preferences saved', 'success');
				} else {
					GuardianAdmin.showNotification(response.data || 'Failed to save preferences', 'error');
				}
			}).always(function() {
				$button.prop('disabled', false);
				$button.find('.spinner').remove();
			});
		},
		
		/**
		 * Handle recovery point restoration
		 */
		handleRecoveryRestore: function(e) {
			e.preventDefault();
			
			const $button = $(e.currentTarget);
			const recoveryId = $button.data('recovery-id');
			
			const restoreAction = function() {
				$button.prop('disabled', true);
				$button.append(' <span class="spinner"></span>');
				
				const data = {
					action: 'wpshadow_restore_recovery',
					nonce: wpshadow.nonce,
					recovery_id: recoveryId
				};
				
				$.post(wpshadow.ajax_url, data, function(response) {
					if (response.success) {
						GuardianAdmin.showNotification('Recovery point restored successfully', 'success');
						location.reload();
					} else {
						GuardianAdmin.showNotification(response.data || 'Failed to restore recovery point', 'error');
					}
				}).always(function() {
					$button.prop('disabled', false);
					$button.find('.spinner').remove();
				});
			};

			if (window.WPShadowDesign && typeof window.WPShadowDesign.confirm === 'function') {
				window.WPShadowDesign.confirm('Are you sure you want to restore from this recovery point?', restoreAction);
				return;
			}

			if (!confirm('Are you sure you want to restore from this recovery point?')) {
				return;
			}

			restoreAction();
		},
		
		/**
		 * Handle test email button
		 */
		handleTestEmail: function(e) {
			e.preventDefault();
			
			const $button = $(e.currentTarget);
			const email = $('input[name="default_email"]').val();
			
			if (!email) {
				GuardianAdmin.showNotification('Please enter an email address', 'warning');
				return;
			}
			
			$button.prop('disabled', true);
			$button.append(' <span class="spinner"></span>');
			
			const data = {
				action: 'wpshadow_send_test_email',
				nonce: wpshadow.nonce,
				email: email
			};
			
			$.post(wpshadow.ajax_url, data, function(response) {
				if (response.success) {
					GuardianAdmin.showNotification('Test email sent successfully', 'success');
				} else {
					GuardianAdmin.showNotification(response.data || 'Failed to send test email', 'error');
				}
			}).always(function() {
				$button.prop('disabled', false);
				$button.find('.spinner').remove();
			});
		},
		
		/**
		 * Handle settings tab switching
		 */
		handleTabSwitch: function(e) {
			const $tab = $(e.currentTarget);
			const tabName = $tab.data('tab');
			
			// Hide all tabs
			$('.tab-content').removeClass('active');
			
			// Show selected tab
			$('[data-tab-pane="' + tabName + '"]').addClass('active');
			
			// Update active tab indicator
			$('.nav-tab').removeClass('nav-tab-active');
			$tab.addClass('nav-tab-active');
		},
		
		/**
		 * Handle policy checkbox changes
		 */
		handlePolicyChange: function(e) {
			const $checkbox = $(e.currentTarget);
			const treatmentId = $checkbox.data('treatment');
			const isChecked = $checkbox.prop('checked');
			
			// Store preference (can be done via hidden field or directly)
			console.log('Policy changed:', treatmentId, isChecked);
		},
		
		/**
		 * Setup modal handlers
		 */
		setupModalHandlers: function() {
			$(document).on('click', '.btn-email-report', this.openEmailModal.bind(this));
			$(document).on('click', '.email-modal-backdrop', this.handleEmailCancel.bind(this));
		},
		
		/**
		 * Open email modal
		 */
		openEmailModal: function(e) {
			e.preventDefault();
			$('.email-modal').addClass('active');
		},
		
		/**
		 * Handle email send
		 */
		handleEmailSend: function(e) {
			e.preventDefault();
			
			const $modal = $('.email-modal');
			const recipient = $modal.find('input[name="email_recipient"]').val();
			const frequency = $modal.find('select[name="email_frequency"]').val();
			
			if (!recipient) {
				GuardianAdmin.showNotification('Please enter a recipient email', 'warning');
				return;
			}
			
			const $button = $(e.currentTarget);
			$button.prop('disabled', true);
			$button.append(' <span class="spinner"></span>');
			
			const data = {
				action: 'wpshadow_send_report_email',
				nonce: wpshadow.nonce,
				recipient: recipient,
				frequency: frequency,
				report_data: $modal.find('input[name="report_data"]').val()
			};
			
			$.post(wpshadow.ajax_url, data, function(response) {
				if (response.success) {
					GuardianAdmin.showNotification('Report emailed successfully', 'success');
					GuardianAdmin.handleEmailCancel();
				} else {
					GuardianAdmin.showNotification(response.data || 'Failed to email report', 'error');
				}
			}).always(function() {
				$button.prop('disabled', false);
				$button.find('.spinner').remove();
			});
		},
		
		/**
		 * Handle email modal cancel
		 */
		handleEmailCancel: function(e) {
			if (e) {
				e.preventDefault();
			}
			$('.email-modal').removeClass('active');
		},
		
		/**
		 * Handle adding subscription
		 */
		handleAddSubscription: function(e) {
			e.preventDefault();
			
			const $form = $(e.currentTarget).closest('.preferences-card');
			const email = $form.find('input[name="subscription_email"]').val();
			const frequency = $form.find('select[name="subscription_frequency"]').val();
			
			if (!email) {
				GuardianAdmin.showNotification('Please enter an email address', 'warning');
				return;
			}
			
			const $button = $(e.currentTarget);
			$button.prop('disabled', true);
			$button.append(' <span class="spinner"></span>');
			
			const data = {
				action: 'wpshadow_add_subscription',
				nonce: wpshadow.nonce,
				email: email,
				frequency: frequency
			};
			
			$.post(wpshadow.ajax_url, data, function(response) {
				if (response.success) {
					// Add new row to table
					$form.find('.subscriptions-table tbody').append(response.data.html);
					
					// Clear form
					$form.find('input[name="subscription_email"]').val('');
					$form.find('select[name="subscription_frequency"]').val('daily');
					
					GuardianAdmin.showNotification('Subscription added successfully', 'success');
				} else {
					GuardianAdmin.showNotification(response.data || 'Failed to add subscription', 'error');
				}
			}).always(function() {
				$button.prop('disabled', false);
				$button.find('.spinner').remove();
			});
		},
		
		/**
		 * Handle removing subscription
		 */
		handleRemoveSubscription: function(e) {
			e.preventDefault();
			
			const $button = $(e.currentTarget);
			const subscriptionId = $button.data('subscription-id');
			
			const removeSubscription = function() {
				$button.prop('disabled', true);
				$button.append(' <span class="spinner"></span>');
				
				const data = {
					action: 'wpshadow_remove_subscription',
					nonce: wpshadow.nonce,
					subscription_id: subscriptionId
				};
				
				$.post(wpshadow.ajax_url, data, function(response) {
					if (response.success) {
						$button.closest('tr').fadeOut(function() {
							$(this).remove();
						});
						GuardianAdmin.showNotification('Subscription removed', 'success');
					} else {
						GuardianAdmin.showNotification(response.data || 'Failed to remove subscription', 'error');
					}
				}).always(function() {
					$button.prop('disabled', false);
					$button.find('.spinner').remove();
				});
			};

			if (window.WPShadowDesign && typeof window.WPShadowDesign.confirm === 'function') {
				window.WPShadowDesign.confirm('Are you sure you want to remove this subscription?', removeSubscription);
				return;
			}

			if (!confirm('Are you sure you want to remove this subscription?')) {
				return;
			}

			removeSubscription();
		},
		
		/**
		 * Setup form validation
		 */
		setupFormValidation: function() {
			// Add custom validation as needed
		},
		
		/**
		 * Show notification message
		 */
		showNotification: function(message, type) {
			type = type || 'info';
			
			const $notification = $('<div>')
				.addClass('notice notice-' + type)
				.html('<p>' + message + '</p>')
				.appendTo('.wp-header-end');
			
			setTimeout(function() {
				$notification.fadeOut(function() {
					$(this).remove();
				});
			}, 5000);
		}
	};
	
	// Initialize on document ready
	$(document).ready(function() {
		GuardianAdmin.init();
	});
	
})(jQuery);
