<?php
/**
 * Main Dashboard Page
 *
 * Renders the primary WPShadow dashboard with Quick Scan check and health gauges.
 *
 * @package WPShadow
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\Form_Param_Helper;

// Load category metadata functions (global aliases)
require_once WPSHADOW_PATH . 'includes/systems/core/functions-category-metadata.php';

/**
 * Build scheduler run key from fully-qualified diagnostic class name.
 *
 * @param string $class_name Diagnostic class name.
 * @return string
 */
function wpshadow_get_diagnostic_run_key_from_class( string $class_name ): string {
	$short_name = str_replace( 'WPShadow\\Diagnostics\\', '', $class_name );
	$short_name = strtolower( str_replace( '_', '-', $short_name ) );

	return sanitize_key( $short_name );
}

/**
 * Format a timestamp in human-friendly relative text with precise tooltip.
 *
 * @param int $timestamp Unix timestamp.
 * @return string
 */
function wpshadow_format_human_time_with_tooltip( int $timestamp ): string {
	if ( $timestamp <= 0 ) {
		return esc_html__( 'Never', 'wpshadow' );
	}

	$now      = time();
	$relative = $timestamp > $now
		? sprintf(
			/* translators: %s: human time difference */
			esc_html__( 'in %s', 'wpshadow' ),
			human_time_diff( $now, $timestamp )
		)
		: sprintf(
			/* translators: %s: human time difference */
			esc_html__( '%s ago', 'wpshadow' ),
			human_time_diff( $timestamp, $now )
		);

	$precise = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );

	return sprintf(
		'<span title="%s">%s</span>',
		esc_attr( $precise ),
		esc_html( $relative )
	);
}

/**
 * Build diagnostics activity rows for dashboard display.
 *
 * @return array<int, array<string, mixed>>
 */
function wpshadow_get_diagnostics_activity_rows(): array {
	if ( ! class_exists( '\\WPShadow\\Diagnostics\\Diagnostic_Registry' ) ) {
		return array();
	}

	$file_map = \WPShadow\Diagnostics\Diagnostic_Registry::get_diagnostic_file_map();
	if ( empty( $file_map ) || ! is_array( $file_map ) ) {
		return array();
	}

	$rows = array();
	$now  = time();

	foreach ( $file_map as $short_class => $diagnostic_data ) {
		if ( ! is_string( $short_class ) || '' === $short_class ) {
			continue;
		}

		$class_name = 0 === strpos( $short_class, 'WPShadow\\Diagnostics\\' )
			? $short_class
			: 'WPShadow\\Diagnostics\\' . $short_class;

		$file = isset( $diagnostic_data['file'] ) ? (string) $diagnostic_data['file'] : '';
		if ( ! class_exists( $class_name ) && '' !== $file && file_exists( $file ) ) {
			require_once $file;
		}

		$friendly_name = str_replace( '_', ' ', str_replace( 'Diagnostic_', '', $short_class ) );
		$friendly_name = ucwords( strtolower( $friendly_name ) );
		if ( class_exists( $class_name ) && method_exists( $class_name, 'get_title' ) ) {
			$title = (string) call_user_func( array( $class_name, 'get_title' ) );
			if ( '' !== trim( $title ) ) {
				$friendly_name = $title;
			}
		}

		$run_key      = wpshadow_get_diagnostic_run_key_from_class( $class_name );
		$last_run_raw = (int) get_option( 'wpshadow_last_run_' . $run_key, 0 );

		$frequency = DAY_IN_SECONDS;
		if ( class_exists( '\\WPShadow\\Core\\Diagnostic_Scheduler' ) ) {
			$schedule = \WPShadow\Core\Diagnostic_Scheduler::get_schedule( $run_key );
			if ( is_array( $schedule ) && isset( $schedule['frequency'] ) ) {
				$frequency = (int) $schedule['frequency'];
			}
		}

		$next_run_label = esc_html__( 'On first run', 'wpshadow' );
		if ( $last_run_raw > 0 ) {
			if ( 0 === $frequency ) {
				$next_run_label = esc_html__( 'On every request', 'wpshadow' );
			} else {
				$next_run_label = wpshadow_format_human_time_with_tooltip( $last_run_raw + $frequency );
			}
		}

		$status_label = esc_html__( 'Unknown', 'wpshadow' );
		if ( function_exists( 'wpshadow_get_valid_diagnostic_test_state' ) ) {
			$state = wpshadow_get_valid_diagnostic_test_state( $class_name, $now );
			if ( is_array( $state ) && isset( $state['status'] ) ) {
				$status = (string) $state['status'];
				if ( 'passed' === $status ) {
					$status_label = esc_html__( 'Passed', 'wpshadow' );
				} elseif ( 'failed' === $status ) {
					$status_label = esc_html__( 'Failed', 'wpshadow' );
				}
			}
		}

		$rows[] = array(
			'name'      => $friendly_name,
			'last_run'  => $last_run_raw > 0 ? wpshadow_format_human_time_with_tooltip( $last_run_raw ) : esc_html__( 'Never', 'wpshadow' ),
			'next_run'  => $next_run_label,
			'status'    => $status_label,
		);
	}

	usort(
		$rows,
		static function ( array $a, array $b ): int {
			return strcasecmp( (string) $a['name'], (string) $b['name'] );
		}
	);

	return $rows;
}

/**
 * Render diagnostics recent activities table at the bottom of dashboard.
 *
 * @return void
 */
function wpshadow_render_diagnostics_recent_activities(): void {
	$rows = wpshadow_get_diagnostics_activity_rows();

	if ( empty( $rows ) ) {
		return;
	}
	?>
	<div class="wps-card wps-mt-8">
		<div class="wps-card-header">
			<h2 class="wps-card-title"><?php esc_html_e( 'Recent Activities', 'wpshadow' ); ?></h2>
		</div>
		<div class="wps-card-body">
			<p><?php esc_html_e( 'Diagnostics run history and scheduling status.', 'wpshadow' ); ?></p>
			<div style="max-height: 480px; overflow: auto; border: 1px solid #dcdcde; border-radius: 6px;">
				<table class="widefat striped" style="margin:0;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Diagnostic', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Last Run', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Next Run', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Result', 'wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $row ) : ?>
							<tr>
								<td><?php echo esc_html( (string) $row['name'] ); ?></td>
								<td><?php echo wp_kses_post( (string) $row['last_run'] ); ?></td>
								<td><?php echo wp_kses_post( (string) $row['next_run'] ); ?></td>
								<td><?php echo esc_html( (string) $row['status'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render the main WPShadow dashboard
 *
 * Requirements (Issue #562):
 * 1. Remove "Category Health" title
 * 2. Quick Scan check on page load:
 *    - If never run: Ask permission to run
 *    - If last run >5 minutes: Show progress bar with real-time updates
 *
 * @return void
 */
function wpshadow_render_dashboard() {
	// Check if onboarding wizard should be shown
	$onboarding_action = Form_Param_Helper::get( 'onboarding', 'key', '' );
	$show_onboarding   = in_array( $onboarding_action, array( 'start', 'restart' ), true );

	if ( $show_onboarding ) {
		// Load and display the onboarding wizard
		$wizard_file = WPSHADOW_PATH . 'includes/views/onboarding/wizard.php';
		if ( file_exists( $wizard_file ) ) {
			include $wizard_file;
			return;
		}
	}

	// Check for category drill-down (Issue #564)
	$category_filter = Form_Param_Helper::get( 'category', 'key', '' );
	$is_drilldown    = ! empty( $category_filter );

	// Get category metadata for title/details
	$category_meta    = wpshadow_get_category_metadata();
	$current_category = $is_drilldown && isset( $category_meta[ $category_filter ] )
		? $category_meta[ $category_filter ]
		: null;

	$last_scan        = get_option( 'wpshadow_last_quick_scan', 0 );
	$never_run        = empty( $last_scan );
	$five_minutes_ago = time() - ( 5 * MINUTE_IN_SECONDS );
	$needs_refresh    = ( $never_run || ( $last_scan > 0 && $last_scan < $five_minutes_ago ) );

	?>
	<div class="wrap wpshadow-dashboard wps-page-container">
		<?php if ( $is_drilldown && $current_category ) : ?>
			<div class="wps-page-header-actions">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>" class="button wps-btn wps-btn--secondary wps-mr-3" aria-label="<?php esc_attr_e( 'Return to main dashboard', 'wpshadow' ); ?>">
					&larr; <?php esc_html_e( 'Back to Dashboard', 'wpshadow' ); ?>
				</a>
			</div>
			<?php wpshadow_render_page_header(
				sprintf(
					__( '%s Health', 'wpshadow' ),
					$current_category['label']
				),
				$current_category['description'],
				$current_category['icon'],
				$current_category['color']
			); ?>
		<?php else : ?>
			<?php wpshadow_render_page_header(
				__( 'WPShadow Dashboard', 'wpshadow' ),
				'',
				'dashicons-dashboard'
			); ?>
		<?php endif; ?>


		<div class="wpshadow-dashboard-content">
			<?php
			/**
			 * Health Gauges Section
			 *
			 * Renders all health category gauges
			 */
			do_action( 'wpshadow_dashboard_gauges', $category_filter );
			?>

			<?php
			/**
			 * Content After Gauges
			 *
			 * Used for Kanban board and other dashboard widgets
			 */
			do_action( 'wpshadow_dashboard_after_content', $category_filter );
			?>

			<?php
			/**
			 * Activity History Section
			 *
			 * Shows recent activity and actions
			 */
			do_action( 'wpshadow_dashboard_activity', $category_filter );
			?>


			<!-- Dashboard Activity Log -->
			<?php
			if ( function_exists( 'wpshadow_render_page_activities' ) ) {
				wpshadow_render_page_activities( 'general', 3 );
			}

			wpshadow_render_diagnostics_recent_activities();
			?>
		</div>
	</div>

	<script type="text/javascript">
		jQuery(document).ready(function($) {

		var needsRefresh = <?php echo wp_json_encode( $needs_refresh ); ?>;
		var neverRun = <?php echo wp_json_encode( $never_run ); ?>;

		// Auto-run Quick Scan if data is stale
		if (needsRefresh) {
			runQuickScanWithProgress();
		}

		/**
		 * Run Quick Scan with real-time progress updates
		 */
		function runQuickScanWithProgress() {
			var $overlay = $('#wpshadow-refresh-overlay');
			var $dashboardContent = $('.wpshadow-dashboard-content');
			var $progressFill = $('#wpshadow-progress-fill');
			var $progressText = $('#wpshadow-progress-text');
			var $progressDetails = $('#wpshadow-progress-details');
			var $currentDiagnostic = $('#wpshadow-current-diagnostic');
			var $overlayActions = $('#wpshadow-overlay-actions');
			var $overlayDismiss = $('#wpshadow-overlay-dismiss');
			var $progressBar = $('.wps-progress-bar');

			$overlayActions.prop('hidden', true);
			$overlay.attr('aria-busy', 'true');
			$overlay.show();
			$(document).trigger('wpshadow:scan:start');

			function escapeHtml(text) {
				return String(text).replace(/[&<>\"']/g, function(character) {
					return {
						'&': '&amp;',
						'<': '&lt;',
						'>': '&gt;',
						'"': '&quot;',
						"'": '&#39;'
					}[character];
				});
			}

			function dismissOverlay() {
				$overlay.attr('aria-busy', 'false').fadeOut();
				$dashboardContent.removeClass('wps-loading');
				$(document).trigger('wpshadow:scan:complete');
			}

			function showOverlayError(errorMsg) {
				var safeMessage = escapeHtml(errorMsg || '<?php echo esc_js( __( 'Unable to complete scan.', 'wpshadow' ) ); ?>');
				$progressText.html('<span class="wps-progress-error"><?php echo esc_js( __( 'Error:', 'wpshadow' ) ); ?> ' + safeMessage + '</span>');
				$progressDetails.text('<?php echo esc_js( __( 'You can close this message and continue using your dashboard.', 'wpshadow' ) ); ?>');
				$overlayActions.prop('hidden', false);
				$overlay.attr('aria-busy', 'false');
				$progressFill.css('width', '100%');
				$progressBar.attr('aria-valuenow', 100);
			}

			$overlayDismiss.on('click', function() {
				dismissOverlay();
			});

			$overlay.on('click', function(event) {
				if (! $overlayActions.prop('hidden') && $(event.target).is($overlay)) {
					dismissOverlay();
				}
			});

			$(document).on('keydown.wpshadowDashboardOverlay', function(event) {
				if ('Escape' === event.key && ! $overlayActions.prop('hidden')) {
					dismissOverlay();
				}
			});

			// Start Quick Scan
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'wpshadow_quick_scan',
					nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_scan_nonce' ) ); ?>',
					mode: 'now'
				},
				xhr: function() {
					var xhr = new window.XMLHttpRequest();
					// Track progress via response streaming (if supported)
					xhr.addEventListener("progress", function(evt){
						if (evt.lengthComputable) {
							var percentComplete = (evt.loaded / evt.total) * 100;
							$progressFill.css('width', percentComplete + '%');
							$progressBar.attr('aria-valuenow', Math.round(percentComplete));
						}
					});
					return xhr;
				},
				success: function(response) {
					if (response.success) {
						var data = response.data;
						var completed = data.completed || 0;
						var total = data.total || 1;
						var percent = Math.round((completed / total) * 100);

						$progressFill.css('width', percent + '%');
						$progressBar.attr('aria-valuenow', percent);
						$progressText.text('<?php esc_html_e( 'Scan complete! Found', 'wpshadow' ); ?> ' + data.findings_count + ' <?php esc_html_e( 'items.', 'wpshadow' ); ?>');

						// Refresh dashboard data in place (no full page reload).
						setTimeout(function() {
							if (window.WPShadowDashboard && typeof window.WPShadowDashboard.updateDashboardData === 'function') {
								window.WPShadowDashboard.updateDashboardData();
							}
							$(document).trigger('wpshadow_dashboard_refresh');
							dismissOverlay();
						}, 900);
					} else {
						var errorMsg = '<?php esc_html_e( 'Unknown error occurred during scan', 'wpshadow' ); ?>';
						if (response.data) {
							// Properly handle error message from response
							if (typeof response.data === 'string') {
								errorMsg = response.data;
							} else if (response.data.message) {
								errorMsg = response.data.message;
							} else if (typeof response.data === 'object') {
								// Convert object to readable string
								try {
									errorMsg = JSON.stringify(response.data);
								} catch(e) {
									errorMsg = '<?php esc_html_e( 'An error occurred. Please check your error logs.', 'wpshadow' ); ?>';
								}
							}
						}
						showOverlayError(errorMsg);
					}
				},
				error: function() {
						$(document).trigger('wpshadow:scan:complete');
					showOverlayError('<?php echo esc_js( __( 'Unable to complete scan.', 'wpshadow' ) ); ?>');
				}
			});

			// Simulate progress updates (since real-time streaming may not be available)
			var simulatedProgress = 0;
			var progressInterval = setInterval(function() {
				if (simulatedProgress < 90) {
					simulatedProgress += Math.random() * 15;
					var currentProgress = Math.min(simulatedProgress, 90);
					$progressFill.css('width', currentProgress + '%');
					$progressBar.attr('aria-valuenow', Math.round(currentProgress));

					// Rotate through common diagnostic names for UX
					var diagnosticNames = [
						'<?php esc_html_e( 'Checking site health...', 'wpshadow' ); ?>',
						'<?php esc_html_e( 'Analyzing performance...', 'wpshadow' ); ?>',
						'<?php esc_html_e( 'Scanning security...', 'wpshadow' ); ?>',
						'<?php esc_html_e( 'Reviewing accessibility...', 'wpshadow' ); ?>',
						'<?php esc_html_e( 'Testing SEO configuration...', 'wpshadow' ); ?>',
						'<?php esc_html_e( 'Examining code quality...', 'wpshadow' ); ?>',
						'<?php esc_html_e( 'Validating theme setup...', 'wpshadow' ); ?>',
						'<?php esc_html_e( 'Inspecting plugins...', 'wpshadow' ); ?>'
					];
					var randomDiagnostic = diagnosticNames[Math.floor(Math.random() * diagnosticNames.length)];
					$currentDiagnostic.text(randomDiagnostic);
				}
			}, 800);

			// Clear interval when AJAX completes
			$(document).one('ajaxComplete', function() {
				clearInterval(progressInterval);
			});
		}
		});
	</script>
	<?php
}
