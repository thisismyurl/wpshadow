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

// Ensure required functions are loaded.
require_once WPSHADOW_PATH . 'includes/core/class-category-metadata.php';

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
	$onboarding_action = isset( $_GET['onboarding'] ) ? sanitize_key( $_GET['onboarding'] ) : '';
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
	$category_filter = isset( $_GET['category'] ) ? sanitize_key( $_GET['category'] ) : '';
	$is_drilldown    = ! empty( $category_filter );

	// Get category metadata for title/details
	$category_meta    = wpshadow_get_category_metadata();
	$current_category = $is_drilldown && isset( $category_meta[ $category_filter ] )
		? $category_meta[ $category_filter ]
		: null;

	$last_scan        = get_option( 'wpshadow_last_quick_scan', 0 );
	$never_run        = empty( $last_scan );
	$five_minutes_ago = time() - ( 5 * MINUTE_IN_SECONDS );
	$needs_refresh    = ( $last_scan > 0 && $last_scan < $five_minutes_ago );

	?>
	<div class="wrap wpshadow-dashboard wps-page-container">
		<?php if ( $is_drilldown && $current_category ) : ?>
			<!-- Category Drill-Down Header -->
			<div class="wps-page-header wps-drilldown-header">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>" class="button wps-btn-secondary wps-mr-3" aria-label="<?php esc_attr_e( 'Return to main dashboard', 'wpshadow' ); ?>">
					&larr; <?php esc_html_e( 'Back to Dashboard', 'wpshadow' ); ?>
				</a>
				<h1 class="wps-page-title">
					<span class="dashicons <?php echo esc_attr( $current_category['icon'] ); ?>" style="color: <?php echo esc_attr( $current_category['color'] ); ?>;" aria-hidden="true"></span>
					<?php echo esc_html( $current_category['label'] ); ?> <?php esc_html_e( 'Health', 'wpshadow' ); ?>
				</h1>
				<p class="wps-page-subtitle">
					<?php echo esc_html( $current_category['description'] ); ?>
				</p>
			</div>
		<?php else : ?>
			<div class="wps-page-header">
				<h1 class="wps-page-title"><?php esc_html_e( 'WPShadow Dashboard', 'wpshadow' ); ?></h1>
			</div>
		<?php endif; ?>

		<?php if ( $never_run ) : ?>
			<!-- First-time Quick Scan prompt -->
			<div class="wps-cta-card wps-cta-info" id="wpshadow-first-scan-prompt" role="region" aria-labelledby="wpshadow-first-scan-heading">
				<button type="button" class="wps-cta-dismiss" id="wpshadow-dismiss-first-scan" aria-label="<?php esc_attr_e( 'Dismiss welcome message', 'wpshadow' ); ?>">
					<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
				</button>
				<div class="wps-cta-icon" aria-hidden="true">
					<span class="dashicons dashicons-yes-alt"></span>
				</div>
				<div class="wps-cta-content">
					<h2 id="wpshadow-first-scan-heading" class="wps-cta-heading"><?php esc_html_e( 'Welcome to WPShadow!', 'wpshadow' ); ?></h2>
					<p class="wps-cta-description">
						<?php esc_html_e( 'To get started, WPShadow needs to run a Quick Scan of your site. This scan is completely safe and won\'t make any changes to your website.', 'wpshadow' ); ?>
					</p>
					<div class="wps-cta-actions">
						<button type="button" class="button button-primary wps-btn-primary" id="wpshadow-run-first-scan">
							<?php esc_html_e( 'Run Quick Scan', 'wpshadow' ); ?>
						</button>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $needs_refresh ) : ?>
			<!-- Stale data overlay with progress -->
			<div class="wps-scan-overlay" id="wpshadow-refresh-overlay" role="dialog" aria-labelledby="wpshadow-scan-heading" aria-modal="true" aria-busy="true">
				<div class="wps-scan-overlay-content">
					<h2 id="wpshadow-scan-heading"><?php esc_html_e( 'Refreshing Dashboard Data', 'wpshadow' ); ?></h2>
					<div class="wps-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
						<div class="wps-progress-fill" id="wpshadow-progress-fill"></div>
					</div>
					<p class="wps-progress-text" id="wpshadow-progress-text" aria-live="polite">
						<?php esc_html_e( 'Preparing scan...', 'wpshadow' ); ?>
					</p>
					<p class="wps-progress-details" id="wpshadow-progress-details">
						<span id="wpshadow-current-diagnostic"></span>
					</p>
				</div>
			</div>
		<?php endif; ?>

		<div class="wpshadow-dashboard-content <?php echo $needs_refresh ? 'wps-loading' : ''; ?>">
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
		</div>
	</div>

	<script type="text/javascript">
	jQuery(document).ready(function($) {
		var needsRefresh = <?php echo $needs_refresh ? 'true' : 'false'; ?>;
		var neverRun = <?php echo $never_run ? 'true' : 'false'; ?>;

		// Auto-run Quick Scan if data is stale
		if (needsRefresh) {
			runQuickScanWithProgress();
		}

		// First-time scan prompt handlers
		$('#wpshadow-run-first-scan').on('click', function() {
			$('#wpshadow-first-scan-prompt').fadeOut();
			runQuickScanWithProgress();
		});

		$('#wpshadow-dismiss-first-scan').on('click', function() {
			$('#wpshadow-first-scan-prompt').fadeOut();
		});

		/**
		 * Run Quick Scan with real-time progress updates
		 */
		function runQuickScanWithProgress() {
			var $overlay = $('#wpshadow-refresh-overlay');
			var $progressFill = $('#wpshadow-progress-fill');
			var $progressText = $('#wpshadow-progress-text');
			var $progressDetails = $('#wpshadow-progress-details');
			var $currentDiagnostic = $('#wpshadow-current-diagnostic');
			var $progressBar = $('.wps-progress-bar');

			$overlay.show();

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

						// Reload page after short delay
						setTimeout(function() {
							location.reload();
						}, 1500);
					} else {
						$progressText.text('<?php esc_html_e( 'Error:', 'wpshadow' ); ?> ' + (response.data || '<?php esc_html_e( 'Unknown error', 'wpshadow' ); ?>'));
					}
				},
				error: function() {
					$progressText.text('<?php esc_html_e( 'Error: Unable to complete scan', 'wpshadow' ); ?>');
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
