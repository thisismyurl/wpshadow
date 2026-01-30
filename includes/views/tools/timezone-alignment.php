<?php
/**
 * Timezone Alignment Tool
 *
 * Helps admins detect and align their timezone with WordPress.
 * Shows browser timezone vs server timezone vs current WordPress setting.
 * Can be rerun anytime to resync.
 *
 * @package WPShadow
 */

declare(strict_types=1);

use WPShadow\Views\Tool_View_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Verify access
Tool_View_Base::verify_access( 'manage_options' );

// Enqueue assets
Tool_View_Base::enqueue_assets( 'timezone-alignment' );

// Render header
Tool_View_Base::render_header( __( 'Timezone Alignment', 'wpshadow' ) );

use WPShadow\Core\Timezone_Manager;

// Get current timezones
$current_tz   = Timezone_Manager::get_admin_timezone();
$abbr         = Timezone_Manager::get_timezone_abbreviation( $current_tz );
$offset       = Timezone_Manager::get_timezone_offset( $current_tz );
$server_tz    = date_default_timezone_get();
$suggestion   = Timezone_Manager::get_timezone_suggestion();
$us_timezones = Timezone_Manager::get_us_timezones();
?>

<div class="wpshadow-tool-container timezone-alignment-tool">
		<h2><?php esc_html_e( 'Timezone Alignment', 'wpshadow' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Automatically detect and align your timezone with WordPress. Ensures all timestamps reflect your actual location, not the server\'s timezone.', 'wpshadow' ); ?>
		</p>
	</div>

	<div class="tool-content">
		<?php if ( $suggestion['needs_adjustment'] ) : ?>
			<div class="notice notice-warning inline">
				<p>
					<strong><?php esc_html_e( 'Timezone Mismatch Detected', 'wpshadow' ); ?></strong><br>
					<?php echo esc_html( $suggestion['message'] ); ?>
				</p>
			</div>
		<?php endif; ?>

		<!-- Current Status Section -->
		<div class="status-section">
			<h3><?php esc_html_e( 'Current Status', 'wpshadow' ); ?></h3>
			<table class="widefat striped">
				<tbody>
					<tr>
						<th><?php esc_html_e( 'WordPress Timezone', 'wpshadow' ); ?></th>
						<td>
							<code><?php echo esc_html( $current_tz ); ?></code>
							<span class="timezone-info">
								<?php
								printf(
									esc_html( '%s %s' ),
									esc_html( $abbr ),
									esc_html( $offset )
								);
								?>
							</span>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Server Timezone', 'wpshadow' ); ?></th>
						<td>
							<code><?php echo esc_html( $server_tz ); ?></code>
							<span class="timezone-info">
								<?php echo esc_html( Timezone_Manager::get_timezone_abbreviation( $server_tz ) . ' ' . Timezone_Manager::get_timezone_offset( $server_tz ) ); ?>
							</span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- Auto-Detect Section -->
		<div class="detection-section">
			<h3><?php esc_html_e( 'Browser Timezone Detection', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Click below to automatically detect your timezone from your browser and apply it to WordPress.', 'wpshadow' ); ?></p>

			<button type="button" id="wpshadow-detect-tz-btn" class="wps-btn wps-btn-primary wps-btn-icon-left">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Detect & Apply My Timezone', 'wpshadow' ); ?>
			</button>

			<div id="wpshadow-detect-status" class="wps-none">
				<p id="wpshadow-detect-result"></p>
			</div>
		</div>

		<!-- Manual Selection Section -->
		<div class="manual-section">
			<h3><?php esc_html_e( 'Manual Timezone Selection', 'wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Or manually select your timezone (uses the same list as WordPress):', 'wpshadow' ); ?></p>

			<div class="timezone-selector">
				<select id="wpshadow-timezone-select" name="wpshadow_timezone" class="wpshadow-timezone-select">
					<?php echo Timezone_Manager::get_wordpress_timezone_list( $current_tz ); ?>
				</select>
			</div>

			<button id="wpshadow-save-tz-btn" class="wps-btn wps-btn-primary" type="button" style="margin-top: 15px;">
				<?php esc_html_e( 'Apply Selected Timezone', 'wpshadow' ); ?>
			</button>

			<div id="wpshadow-save-status" class="wps-none">
				<p id="wpshadow-save-result"></p>
			</div>
		</div>

		<!-- Info Section -->
		<div class="info-section wps-p-15-rounded-4">
			<h4><?php esc_html_e( 'Why Timezone Matters', 'wpshadow' ); ?></h4>
			<ul class="wps-m-10">
				<li><?php esc_html_e( 'All blog post times reflect your timezone', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Scheduled posts publish at correct local time', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Admin displays use your timezone, not server\'s', 'wpshadow' ); ?></li>
				<li><?php esc_html_e( 'Diagnostic timestamps match your time', 'wpshadow' ); ?></li>
			</ul>
		</div>
	</div>
</div>

<style>
	.timezone-alignment-tool {
		max-width: 800px;
	}

	.tool-header {
		border-bottom: 1px solid #e0e0e0;
		padding-bottom: 15px;
		margin-bottom: 20px;
	}

	.tool-header h2 {
		margin: 0 0 8px 0;
	}

	.status-section,
	.detection-section,
	.manual-section,
	.info-section {
		margin-bottom: 25px;
	}

	.status-section h3,
	.detection-section h3,
	.manual-section h3,
	.info-section h4 {
		margin: 0 0 15px 0;
		font-weight: 600;
	}

	.timezone-info {
		margin-left: 10px;
		font-size: 0.9em;
		color: #666;
	}

	.timezone-selector {
		margin-bottom: 15px;
	}

	.wpshadow-timezone-select {
		width: 100%;
		max-width: 600px;
		padding: 8px 12px;
		font-size: 14px;
		border: 1px solid #8c8f94;
		border-radius: 4px;
		background-color: #fff;
		box-shadow: 0 0 0 transparent;
		transition: border-color 0.1s ease-in-out, box-shadow 0.1s ease-in-out;
	}

	.wpshadow-timezone-select:focus {
		border-color: #2271b1;
		box-shadow: 0 0 0 1px #2271b1;
		outline: 2px solid transparent;
	}

	#wpshadow-detect-status,
	#wpshadow-save-status {
		padding: 10px 12px;
		border-radius: 4px;
		background: #f0f6fc;
		border-left: 4px solid #0073aa;
	}

	#wpshadow-detect-status.success,
	#wpshadow-save-status.success {
		background: #f0fdf4;
		border-left-color: #16a34a;
	}

	#wpshadow-detect-status.error,
	#wpshadow-save-status.error {
		background: #fef2f2;
		border-left-color: #dc2626;
	}

	#wpshadow-detect-result,
	#wpshadow-save-result {
		margin: 0;
	}

	.button {
		transition: all 0.2s ease;
	}

	.button:disabled {
		opacity: 0.6;
		cursor: not-allowed;
	}
</style>

<script>
	jQuery(document).ready(function($) {
		'use strict';

		const nonce = '<?php echo esc_js( wp_create_nonce( 'wpshadow_timezone_nonce' ) ); ?>';

		// Detect timezone button
		$('#wpshadow-detect-tz-btn').on('click', function() {
			const $btn = $(this);
			const $status = $('#wpshadow-detect-status');
			const $result = $('#wpshadow-detect-result');

			// Disable button
			$btn.prop('disabled', true).text('<?php esc_attr_e( 'Detecting...', 'wpshadow' ); ?>');

			// Detect timezone from browser using Intl API
			let detected = null;
			try {
				const format = new Intl.DateTimeFormat().resolvedOptions();
				detected = format.timeZone;
			} catch (e) {
				console.error('Timezone detection failed:', e);
			}

			if (!detected) {
				$result.text('<?php esc_attr_e( 'Error: Could not detect timezone from browser.', 'wpshadow' ); ?>');
				$status.addClass('error').show();
				$btn.prop('disabled', false).text('<?php esc_attr_e( 'Detect & Apply My Timezone', 'wpshadow' ); ?>');
				return;
			}

			// Send to server
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_set_timezone',
					nonce: nonce,
					timezone: detected,
				},
				success: function(response) {
					if (response.success) {
						$result.text('<?php esc_attr_e( 'Success! Timezone set to:', 'wpshadow' ); ?> ' + response.data.timezone);
						$status.removeClass('error').addClass('success').show();

						// Update display
						setTimeout(function() {
							location.reload();
						}, 1500);
					} else {
						$result.text('Error: ' + (response.data?.message || 'Unknown error'));
						$status.addClass('error').show();
					}
				},
				error: function() {
					$result.text('<?php esc_attr_e( 'Error: Failed to save timezone.', 'wpshadow' ); ?>');
					$status.addClass('error').show();
				},
				complete: function() {
					$btn.prop('disabled', false).text('<?php esc_attr_e( 'Detect & Apply My Timezone', 'wpshadow' ); ?>');
				},
			});
		});

		// Manual selection button
		$('#wpshadow-save-tz-btn').on('click', function() {
			const $btn = $(this);
			const $status = $('#wpshadow-save-status');
			const $result = $('#wpshadow-save-result');
			const selected = $('#wpshadow-timezone-select').val();

			if (!selected) {
				$result.text('<?php esc_attr_e( 'Error: Please select a timezone.', 'wpshadow' ); ?>');
				$status.addClass('error').show();
				return;
			}

			// Disable button
			$btn.prop('disabled', true).text('<?php esc_attr_e( 'Saving...', 'wpshadow' ); ?>');

			// Send to server
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_set_timezone',
					nonce: nonce,
					timezone: selected,
				},
				success: function(response) {
					if (response.success) {
						$result.text('<?php esc_attr_e( 'Success! Timezone set to:', 'wpshadow' ); ?> ' + response.data.timezone);
						$status.removeClass('error').addClass('success').show();

						// Update display
						setTimeout(function() {
							location.reload();
						}, 1500);
					} else {
						$result.text('Error: ' + (response.data?.message || 'Unknown error'));
						$status.removeClass('success').addClass('error').show();
					}
				},
				error: function() {
					$result.text('<?php esc_attr_e( 'Error: Failed to save timezone.', 'wpshadow' ); ?>');
					$status.removeClass('success').addClass('error').show();
				},
				complete: function() {
					$btn.prop('disabled', false).text('<?php esc_attr_e( 'Apply Selected Timezone', 'wpshadow' ); ?>');
				},
			});
		});
	});
</script>
<?php Tool_View_Base::render_footer(); ?>
