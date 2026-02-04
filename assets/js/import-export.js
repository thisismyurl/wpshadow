/**
 * Import/Export Settings JavaScript
 *
 * Handles all import/export and cloud sync operations from the UI
 *
 * @package    WPShadow
 * @subpackage Assets
 * @since      1.7035.1500
 */

(function ($) {
	'use strict';

	const ImportExport = {
		/**
		 * Initialize
		 */
		init: function () {
			this.bindEvents();
			this.checkFileInput();
		},

		/**
		 * Bind event handlers
		 */
		bindEvents: function () {
			// Export settings
			$('#wpshadow-export-settings').on('click', this.exportSettings.bind(this));

			// Import settings - file selection
			$('#wpshadow-import-file').on('change', this.handleFileSelect.bind(this));

			// Import settings - trigger import
			$('#wpshadow-import-settings').on('click', this.importSettings.bind(this));

			// Cloud sync toggle
			$('#wpshadow_cloud_sync_enabled').on('change', this.toggleCloudSync.bind(this));

			// Sync to cloud
			$('#wpshadow-sync-to-cloud').on('click', this.syncToCloud.bind(this));

			// Restore from cloud
			$('#wpshadow-restore-from-cloud').on('click', this.restoreFromCloud.bind(this));
		},

		/**
		 * Export settings
		 */
		exportSettings: function (e) {
			e.preventDefault();

			const $button = $(e.currentTarget);
			const originalText = $button.html();
			const nonce = $button.data('nonce');

			// Show loading state
			$button.prop('disabled', true).html(
				'<span class="dashicons dashicons-update-alt dashicons-spin"></span> ' +
					wpShadowImportExport.i18n.exporting
			);

			// Make AJAX request
			$.ajax({
				url: wpShadowImportExport.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_export_settings',
					nonce: nonce,
				},
				success: (response) => {
					if (response.success) {
						// Create downloadable JSON file
						const dataStr = JSON.stringify(response.data, null, 2);
						const dataBlob = new Blob([dataStr], { type: 'application/json' });
						const url = URL.createObjectURL(dataBlob);
						const link = document.createElement('a');
						const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
						link.href = url;
						link.download = `wpshadow-settings-${timestamp}.json`;
						document.body.appendChild(link);
						link.click();
						document.body.removeChild(link);
						URL.revokeObjectURL(url);

						this.showMessage('success', wpShadowImportExport.i18n.exportSuccess);
					} else {
						this.showMessage('error', response.data.message || wpShadowImportExport.i18n.exportError);
					}
				},
				error: () => {
					this.showMessage('error', wpShadowImportExport.i18n.exportError);
				},
				complete: () => {
					$button.prop('disabled', false).html(originalText);
				},
			});
		},

		/**
		 * Handle file selection
		 */
		handleFileSelect: function (e) {
			const file = e.target.files[0];
			const $button = $('#wpshadow-import-settings');

			if (file && file.type === 'application/json') {
				$button.prop('disabled', false);
			} else {
				$button.prop('disabled', true);
				if (file) {
					this.showMessage('error', wpShadowImportExport.i18n.invalidFile);
				}
			}
		},

		/**
		 * Import settings
		 */
		importSettings: function (e) {
			e.preventDefault();

			// Confirm with user
			if (!confirm(wpShadowImportExport.i18n.confirmImport)) {
				return;
			}

			const $button = $(e.currentTarget);
			const originalText = $button.html();
			const nonce = $button.data('nonce');
			const file = $('#wpshadow-import-file')[0].files[0];

			if (!file) {
				this.showMessage('error', wpShadowImportExport.i18n.invalidFile);
				return;
			}

			// Show loading state
			$button.prop('disabled', true).html(
				'<span class="dashicons dashicons-update-alt dashicons-spin"></span> ' +
					wpShadowImportExport.i18n.importing
			);

			// Read file
			const reader = new FileReader();
			reader.onload = (event) => {
				const settings = event.target.result;

				const maxRetries = 2;
				const attemptImport = (attempt) => {
					$.ajax({
						url: wpShadowImportExport.ajaxUrl,
						type: 'POST',
						data: {
							action: 'wpshadow_import_settings',
							nonce: nonce,
							settings: settings,
						},
						success: (response) => {
							if (response.success) {
								this.showMessage('success', wpShadowImportExport.i18n.importSuccess);
								// Reload page after 2 seconds
								setTimeout(() => {
									location.reload();
								}, 2000);
							} else {
								this.showMessage('error', response.data.message || wpShadowImportExport.i18n.importError);
								$button.prop('disabled', false).html(originalText);
							}
						},
						error: () => {
							if (attempt < maxRetries) {
								const nextAttempt = attempt + 1;
								const delay = Math.pow(2, attempt) * 1000;
								const retryMessage = wpShadowImportExport.i18n.retrying
									.replace('%1$d', nextAttempt)
									.replace('%2$d', maxRetries + 1);
								this.showMessage('info', retryMessage);
								$button.html(
									'<span class="dashicons dashicons-update-alt dashicons-spin"></span> ' +
										wpShadowImportExport.i18n.retryingShort
								);
								setTimeout(() => {
									attemptImport(nextAttempt);
								}, delay);
								return;
							}

							this.showMessage('error', wpShadowImportExport.i18n.retryFailed);
							$button.prop('disabled', false).html(originalText);
						},
					});
				};

				attemptImport(0);
			};

			reader.onerror = () => {
				this.showMessage('error', wpShadowImportExport.i18n.invalidFile);
				$button.prop('disabled', false).html(originalText);
			};

			reader.readAsText(file);
		},

		/**
		 * Toggle cloud sync
		 */
		toggleCloudSync: function (e) {
			const $checkbox = $(e.currentTarget);
			const enabled = $checkbox.is(':checked');
			const nonce = $checkbox.data('nonce');

			// Make AJAX request
			$.ajax({
				url: wpShadowImportExport.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_toggle_cloud_sync',
					nonce: nonce,
					enabled: enabled,
				},
				success: (response) => {
					if (response.success) {
						// Enable/disable sync buttons
						$('#wpshadow-sync-to-cloud, #wpshadow-restore-from-cloud').prop('disabled', !enabled);
						this.showMessage('success', response.data.message);
					} else {
						// Revert checkbox state
						$checkbox.prop('checked', !enabled);
						this.showMessage('error', response.data.message);
					}
				},
				error: () => {
					// Revert checkbox state
					$checkbox.prop('checked', !enabled);
					this.showMessage('error', 'Failed to toggle cloud sync');
				},
			});
		},

		/**
		 * Sync to cloud
		 */
		syncToCloud: function (e) {
			e.preventDefault();

			const $button = $(e.currentTarget);
			const originalText = $button.html();
			const nonce = $button.data('nonce');

			// Show loading state
			$button.prop('disabled', true).html(
				'<span class="dashicons dashicons-update-alt dashicons-spin"></span> ' +
					wpShadowImportExport.i18n.syncing
			);

			// Make AJAX request
			$.ajax({
				url: wpShadowImportExport.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_sync_to_cloud',
					nonce: nonce,
				},
				success: (response) => {
					if (response.success) {
						this.showMessage('success', wpShadowImportExport.i18n.syncSuccess);
					} else {
						this.showMessage('error', response.data.message || wpShadowImportExport.i18n.syncError);
					}
				},
				error: () => {
					this.showMessage('error', wpShadowImportExport.i18n.syncError);
				},
				complete: () => {
					$button.prop('disabled', false).html(originalText);
				},
			});
		},

		/**
		 * Restore from cloud
		 */
		restoreFromCloud: function (e) {
			e.preventDefault();

			// Confirm with user
			if (!confirm(wpShadowImportExport.i18n.confirmRestore)) {
				return;
			}

			const $button = $(e.currentTarget);
			const originalText = $button.html();
			const nonce = $button.data('nonce');

			// Show loading state
			$button.prop('disabled', true).html(
				'<span class="dashicons dashicons-update-alt dashicons-spin"></span> ' +
					wpShadowImportExport.i18n.restoring
			);

			// Make AJAX request
			$.ajax({
				url: wpShadowImportExport.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_restore_from_cloud',
					nonce: nonce,
				},
				success: (response) => {
					if (response.success) {
						this.showMessage('success', wpShadowImportExport.i18n.restoreSuccess);
						// Reload page after 2 seconds
						setTimeout(() => {
							location.reload();
						}, 2000);
					} else {
						this.showMessage('error', response.data.message || wpShadowImportExport.i18n.restoreError);
						$button.prop('disabled', false).html(originalText);
					}
				},
				error: () => {
					this.showMessage('error', wpShadowImportExport.i18n.restoreError);
					$button.prop('disabled', false).html(originalText);
				},
			});
		},

		/**
		 * Show status message
		 */
		showMessage: function (type, message) {
			const $container = $('#wpshadow-import-export-messages');
			const icon = type === 'success' ? 'yes' : type === 'error' ? 'warning' : 'info';

			const messageHtml = `
				<div class="wps-alert wps-alert--${type}" style="animation: slideIn 0.3s ease;">
					<span class="dashicons dashicons-${icon}"></span>
					<div>${message}</div>
				</div>
			`;

			$container.html(messageHtml);

			// Auto-dismiss after 5 seconds (except for errors)
			if (type !== 'error') {
				setTimeout(() => {
					$container.find('.wps-alert').fadeOut(300, function () {
						$(this).remove();
					});
				}, 5000);
			}
		},

		/**
		 * Check if file input is supported
		 */
		checkFileInput: function () {
			const fileInput = document.getElementById('wpshadow-import-file');
			if (fileInput && !fileInput.files) {
				// Fallback for older browsers
				console.warn('File input not fully supported');
			}
		},
	};

	// Initialize when DOM is ready
	$(document).ready(function () {
		ImportExport.init();
	});
})(jQuery);
