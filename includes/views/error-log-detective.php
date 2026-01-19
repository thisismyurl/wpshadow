<?php
/**
 * Error Log Detective Feature View
 *
 * Displays parsed and translated WordPress error logs in a friendly,
 * Activity History-style timeline interface.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wpshadow-error-log-detective">
	<div class="wpshadow-error-log-header">
		<div class="wpshadow-error-log-actions">
			<button type="button" class="button button-secondary" id="wpshadow-refresh-error-logs">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Refresh Logs', 'wpshadow' ); ?>
			</button>
			<button type="button" class="button button-destructive" id="wpshadow-clear-error-logs">
				<span class="dashicons dashicons-trash"></span>
				<?php esc_html_e( 'Clear Logs', 'wpshadow' ); ?>
			</button>
		</div>
		<div class="wpshadow-error-log-status">
			<span id="wpshadow-error-count" class="wpshadow-error-count">
				<?php esc_html_e( 'Loading errors...', 'wpshadow' ); ?>
			</span>
		</div>
	</div>

	<div class="wpshadow-error-log-container">
		<div id="wpshadow-error-log-timeline" class="wpshadow-error-log-timeline">
			<div class="wpshadow-loading">
				<span class="spinner is-active"></span>
				<?php esc_html_e( 'Loading error logs...', 'wpshadow' ); ?>
			</div>
		</div>
	</div>

	<div class="wpshadow-error-log-empty" style="display: none;">
		<div class="wpshadow-empty-state">
			<span class="dashicons dashicons-yes-alt"></span>
			<h3><?php esc_html_e( 'No Errors Found', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Your WordPress error log is clean. Great job!', 'wpshadow' ); ?></p>
		</div>
	</div>

	<div class="wpshadow-error-log-no-debug" style="display: none;">
		<div class="wpshadow-empty-state">
			<span class="dashicons dashicons-warning"></span>
			<h3><?php esc_html_e( 'Debug Logging Not Enabled', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'To see WordPress errors, enable debug logging by adding the following to your wp-config.php:', 'wpshadow' ); ?></p>
			<pre><code>define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );</code></pre>
			<p class="description">
				<?php esc_html_e( 'This creates a debug.log file in wp-content/ without showing errors on the front-end.', 'wpshadow' ); ?>
			</p>
		</div>
	</div>
</div>

<style>
	.wpshadow-error-log-detective {
		background: #fff;
		border-radius: 8px;
		padding: 20px;
	}

	.wpshadow-error-log-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 20px;
		padding-bottom: 15px;
		border-bottom: 1px solid #dcdcde;
	}

	.wpshadow-error-log-actions {
		display: flex;
		gap: 10px;
	}

	.wpshadow-error-log-actions .button {
		display: inline-flex;
		align-items: center;
		gap: 6px;
	}

	.wpshadow-error-log-actions .dashicons {
		width: 16px;
		height: 16px;
		font-size: 16px;
	}

	.wpshadow-error-count {
		font-weight: 600;
		color: #646970;
	}

	.wpshadow-error-log-container {
		max-height: 600px;
		overflow-y: auto;
	}

	.wpshadow-error-log-timeline {
		position: relative;
		padding: 10px 0;
	}

	.wpshadow-error-entry {
		display: flex;
		gap: 12px;
		padding: 12px 0;
		border-left: 2px solid #dcdcde;
		padding-left: 16px;
		margin-left: 8px;
		position: relative;
	}

	.wpshadow-error-entry:last-child {
		border-left-color: transparent;
	}

	.wpshadow-error-entry::before {
		content: '';
		position: absolute;
		left: -8px;
		top: 18px;
		width: 12px;
		height: 12px;
		border-radius: 50%;
		background: #dcdcde;
		border: 2px solid #fff;
		box-shadow: 0 0 0 2px #dcdcde;
	}

	.wpshadow-error-entry[data-type="fatal"]::before {
		background: #d32f2f;
		box-shadow: 0 0 0 2px #d32f2f;
	}

	.wpshadow-error-entry[data-type="parse-error"]::before {
		background: #f57c00;
		box-shadow: 0 0 0 2px #f57c00;
	}

	.wpshadow-error-entry[data-type="warning"]::before {
		background: #fbc02d;
		box-shadow: 0 0 0 2px #fbc02d;
	}

	.wpshadow-error-entry[data-type="notice"]::before {
		background: #1976d2;
		box-shadow: 0 0 0 2px #1976d2;
	}

	.wpshadow-error-entry[data-type="deprecated"]::before {
		background: #7b1fa2;
		box-shadow: 0 0 0 2px #7b1fa2;
	}

	.wpshadow-error-entry:hover {
		background: #f8f9fa;
		border-radius: 4px;
		padding-left: 12px;
		margin-left: 12px;
	}

	.wpshadow-error-content {
		flex: 1;
	}

	.wpshadow-error-header {
		display: flex;
		justify-content: space-between;
		align-items: baseline;
		margin-bottom: 6px;
		gap: 12px;
	}

	.wpshadow-error-type {
		font-weight: 600;
		font-size: 13px;
		color: #2c3338;
		text-transform: capitalize;
	}

	.wpshadow-error-time {
		font-size: 12px;
		color: #646970;
		font-style: italic;
		cursor: help;
	}

	.wpshadow-error-message {
		font-family: 'Courier New', monospace;
		font-size: 12px;
		color: #2c3338;
		background: #f6f7f7;
		padding: 8px 10px;
		border-radius: 3px;
		margin-bottom: 8px;
		word-break: break-word;
		max-height: 60px;
		overflow: auto;
	}

	.wpshadow-error-explanation {
		font-size: 13px;
		color: #1e1e1e;
		line-height: 1.4;
		margin-bottom: 8px;
		padding: 8px 10px;
		background: #e8f5e9;
		border-left: 3px solid #4caf50;
		border-radius: 3px;
	}

	.wpshadow-error-suggestion {
		font-size: 13px;
		color: #424242;
		line-height: 1.4;
		padding: 8px 10px;
		background: #fff3e0;
		border-left: 3px solid #ff9800;
		border-radius: 3px;
	}

	.wpshadow-error-location {
		font-size: 12px;
		color: #646970;
		margin-top: 8px;
		font-family: 'Courier New', monospace;
	}

	.wpshadow-loading {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 10px;
		padding: 40px 20px;
		color: #646970;
	}

	.wpshadow-empty-state {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 60px 20px;
		text-align: center;
		color: #646970;
	}

	.wpshadow-empty-state .dashicons {
		font-size: 48px;
		width: 48px;
		height: 48px;
		color: #dcdcde;
		margin-bottom: 16px;
	}

	.wpshadow-empty-state h3 {
		margin: 0 0 8px;
		font-size: 16px;
		color: #2c3338;
	}

	.wpshadow-empty-state p {
		margin: 0 0 12px;
		font-size: 13px;
	}

	.wpshadow-empty-state pre {
		background: #f6f7f7;
		border: 1px solid #dcdcde;
		border-radius: 3px;
		padding: 12px;
		font-size: 12px;
		text-align: left;
		max-width: 500px;
		overflow-x: auto;
		margin: 12px 0;
	}

	.wpshadow-empty-state code {
		font-family: 'Courier New', monospace;
	}

	@media (max-width: 600px) {
		.wpshadow-error-log-header {
			flex-direction: column;
			align-items: flex-start;
			gap: 12px;
		}

		.wpshadow-error-header {
			flex-direction: column;
			align-items: flex-start;
		}

		.wpshadow-error-log-actions {
			width: 100%;
		}

		.wpshadow-error-log-actions .button {
			flex: 1;
			justify-content: center;
		}
	}
</style>

<script>
	(function($) {
		'use strict';

		const AJAX_NONCE = '<?php echo wp_create_nonce( 'wpshadow_nonce' ); ?>';

		/**
		 * Load and display error logs
		 */
		function loadErrorLogs() {
			const $timeline = $('#wpshadow-error-log-timeline');
			const $emptyState = $('.wpshadow-error-log-empty');
			const $noDebugState = $('.wpshadow-error-log-no-debug');
			const $count = $('#wpshadow-error-count');

			$timeline.html(
				'<div class="wpshadow-loading"><span class="spinner is-active"></span>' +
				'<?php esc_html_e( 'Loading error logs...', 'wpshadow' ); ?></div>'
			);

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_get_error_logs',
					nonce: AJAX_NONCE,
					limit: 50,
				},
				dataType: 'json',
				success: function(response) {
					if (!response.success) {
						$timeline.html('<p style="color: #d32f2f; padding: 20px;">Error loading logs: ' + 
							(response.data.message || 'Unknown error') + '</p>');
						return;
					}

					const data = response.data;

					if (!data.has_debug_log) {
						$timeline.hide();
						$emptyState.hide();
						$noDebugState.show();
						$count.text('<?php esc_html_e( 'Debug logging disabled', 'wpshadow' ); ?>');
						return;
					}

					if (data.errors.length === 0) {
						$timeline.hide();
						$emptyState.show();
						$noDebugState.hide();
						$count.text('<?php esc_html_e( 'No errors found', 'wpshadow' ); ?>');
						return;
					}

					$timeline.show();
					$emptyState.hide();
					$noDebugState.hide();

					// Build HTML for error entries
					let html = '';
					data.errors.forEach(function(error) {
						html += buildErrorEntry(error);
					});

					$timeline.fadeOut(200, function() {
						$timeline.html(html);
						$timeline.fadeIn(200);
					});

					const errorLabel = data.count === 1 ? 
						'<?php esc_html_e( 'error found', 'wpshadow' ); ?>' :
						'<?php esc_html_e( 'errors found', 'wpshadow' ); ?>';
					$count.text(data.count + ' ' + errorLabel);
				},
				error: function() {
					$timeline.html(
						'<p style="color: #d32f2f; padding: 20px;">' +
						'<?php esc_html_e( 'Failed to load error logs.', 'wpshadow' ); ?></p>'
					);
				},
			});
		}

		/**
		 * Build HTML for a single error entry
		 */
		function buildErrorEntry(error) {
			let html = '<div class="wpshadow-error-entry" data-type="' + escapeHtml(error.type) + '">';
			html += '<div class="wpshadow-error-content">';
			
			// Header with type and time
			html += '<div class="wpshadow-error-header">';
			html += '<span class="wpshadow-error-type">' + escapeHtml(error.type_label) + '</span>';
			html += '<span class="wpshadow-error-time" title="' + escapeHtml(error.timestamp_full) + '">';
			html += escapeHtml(error.timestamp_human);
			html += '</span>';
			html += '</div>';
			
			// Error message
			html += '<div class="wpshadow-error-message">' + escapeHtml(error.message) + '</div>';
			
			// Plain English explanation
			html += '<div class="wpshadow-error-explanation">' + escapeHtml(error.explanation) + '</div>';
			
			// Suggestion
			html += '<div class="wpshadow-error-suggestion">' + escapeHtml(error.suggestion) + '</div>';
			
			// File and line info if available
			if (error.file) {
				html += '<div class="wpshadow-error-location">';
				html += '📁 ' + escapeHtml(error.file);
				if (error.line) {
					html += ' : <strong>line ' + error.line + '</strong>';
				}
				html += '</div>';
			}
			
			html += '</div>'; // .wpshadow-error-content
			html += '</div>'; // .wpshadow-error-entry
			
			return html;
		}

		/**
		 * Escape HTML string
		 */
		function escapeHtml(text) {
			const div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		}

		/**
		 * Handle Refresh button
		 */
		$('#wpshadow-refresh-error-logs').on('click', function() {
			loadErrorLogs();
		});

		/**
		 * Handle Clear button
		 */
		$('#wpshadow-clear-error-logs').on('click', function() {
			if (!confirm('<?php esc_js( _e( 'Are you sure you want to clear all error logs? This cannot be undone.', 'wpshadow' ) ); ?>')) {
				return;
			}

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_clear_error_logs',
					nonce: AJAX_NONCE,
				},
				dataType: 'json',
				success: function(response) {
					if (response.success) {
						alert('<?php esc_js( _e( 'Error logs cleared successfully.', 'wpshadow' ) ); ?>');
						loadErrorLogs();
					} else {
						alert('<?php esc_js( _e( 'Failed to clear error logs: ', 'wpshadow' ) ); ?>' + 
							(response.data.message || 'Unknown error'));
					}
				},
				error: function() {
					alert('<?php esc_js( _e( 'An error occurred while clearing the logs.', 'wpshadow' ) ); ?>');
				},
			});
		});

		// Load logs on page load
		$(function() {
			loadErrorLogs();
		});
	})(jQuery);
</script>
