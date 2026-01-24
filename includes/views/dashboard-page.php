<?php
/**
 * Main Dashboard Page
 *
 * Renders the primary WPShadow dashboard with Quick Scan check and health gauges.
 *
 * @package WPShadow
 * @subpackage Views
 */

namespace WPShadow\Views;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
	// Check for category drill-down (Issue #564)
	$category_filter = isset( $_GET['category'] ) ? sanitize_key( $_GET['category'] ) : '';
	$is_drilldown = ! empty( $category_filter );

	// Get category metadata for title/details
	$category_meta = \WPShadow\Core\wpshadow_get_category_metadata();
	$current_category = $is_drilldown && isset( $category_meta[ $category_filter ] ) 
		? $category_meta[ $category_filter ] 
		: null;

	$last_scan = get_option( 'wpshadow_last_quick_scan', 0 );
	$never_run = empty( $last_scan );
	$five_minutes_ago = time() - ( 5 * MINUTE_IN_SECONDS );
	$needs_refresh = ( $last_scan > 0 && $last_scan < $five_minutes_ago );

	?>
	<div class="wrap wpshadow-dashboard">
		<?php if ( $is_drilldown && $current_category ) : ?>
			<!-- Category Drill-Down Header -->
			<div class="wpshadow-drilldown-header" style="margin-bottom: 20px;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>" class="button" style="margin-right: 10px;">
					&larr; <?php esc_html_e( 'Back to Dashboard', 'wpshadow' ); ?>
				</a>
				<h1 style="display: inline-block; margin: 0; vertical-align: middle;">
					<span class="dashicons <?php echo esc_attr( $current_category['icon'] ); ?>" style="color: <?php echo esc_attr( $current_category['color'] ); ?>; font-size: 32px; vertical-align: middle;"></span>
					<?php echo esc_html( $current_category['label'] ); ?> <?php esc_html_e( 'Health', 'wpshadow' ); ?>
				</h1>
				<p style="margin: 10px 0 0 0; color: #646970; font-size: 14px;">
					<?php echo esc_html( $current_category['description'] ); ?>
				</p>
			</div>
		<?php else : ?>
			<h1><?php esc_html_e( 'WPShadow Dashboard', 'wpshadow' ); ?></h1>
		<?php endif; ?>

		<?php if ( $never_run ) : ?>
			<!-- First-time Quick Scan prompt -->
			<div class="notice notice-info wpshadow-quick-scan-prompt" id="wpshadow-first-scan-prompt">
				<p>
					<strong><?php esc_html_e( 'Welcome to WPShadow!', 'wpshadow' ); ?></strong>
				</p>
				<p>
					<?php esc_html_e( 'To get started, WPShadow needs to run a Quick Scan of your site. This scan is completely safe and won\'t make any changes to your website.', 'wpshadow' ); ?>
				</p>
				<p>
					<button type="button" class="button button-primary" id="wpshadow-run-first-scan">
						<?php esc_html_e( 'Run Quick Scan', 'wpshadow' ); ?>
					</button>
					<button type="button" class="button" id="wpshadow-dismiss-first-scan">
						<?php esc_html_e( 'Maybe Later', 'wpshadow' ); ?>
					</button>
				</p>
			</div>
		<?php endif; ?>

		<?php if ( $needs_refresh ) : ?>
			<!-- Stale data overlay with progress -->
			<div class="wpshadow-scan-overlay" id="wpshadow-refresh-overlay">
				<div class="wpshadow-scan-overlay-content">
					<h2><?php esc_html_e( 'Refreshing Dashboard Data', 'wpshadow' ); ?></h2>
					<div class="wpshadow-progress-bar">
						<div class="wpshadow-progress-fill" id="wpshadow-progress-fill"></div>
					</div>
					<p class="wpshadow-progress-text" id="wpshadow-progress-text">
						<?php esc_html_e( 'Preparing scan...', 'wpshadow' ); ?>
					</p>
					<p class="wpshadow-progress-details" id="wpshadow-progress-details">
						<span id="wpshadow-current-diagnostic"></span>
					</p>
				</div>
			</div>
			<style>
				.wpshadow-dashboard-content { opacity: 0.3; pointer-events: none; }
			</style>
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
						$progressText.text('Scan complete! Found ' + data.findings_count + ' items.');

						// Reload page after short delay
						setTimeout(function() {
							location.reload();
						}, 1500);
					} else {
						$progressText.text('Error: ' + (response.data || 'Unknown error'));
					}
				},
				error: function() {
					$progressText.text('Error: Unable to complete scan');
				}
			});

			// Simulate progress updates (since real-time streaming may not be available)
			var simulatedProgress = 0;
			var progressInterval = setInterval(function() {
				if (simulatedProgress < 90) {
					simulatedProgress += Math.random() * 15;
					$progressFill.css('width', Math.min(simulatedProgress, 90) + '%');

					// Rotate through common diagnostic names for UX
					var diagnosticNames = [
						'Checking site health...',
						'Analyzing performance...',
						'Scanning security...',
						'Reviewing accessibility...',
						'Testing SEO configuration...',
						'Examining code quality...',
						'Validating theme setup...',
						'Inspecting plugins...'
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

	<style>
	.wpshadow-scan-overlay {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: rgba(255, 255, 255, 0.95);
		z-index: 999999;
		display: none;
		align-items: center;
		justify-content: center;
	}

	.wpshadow-scan-overlay-content {
		text-align: center;
		max-width: 600px;
		padding: 40px;
	}

	.wpshadow-progress-bar {
		width: 100%;
		height: 30px;
		background: #f0f0f1;
		border-radius: 15px;
		overflow: hidden;
		margin: 20px 0;
	}

	.wpshadow-progress-fill {
		height: 100%;
		background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
		width: 0%;
		transition: width 0.3s ease;
	}

	.wpshadow-progress-text {
		font-size: 16px;
		font-weight: 600;
		color: #1d2327;
		margin: 10px 0;
	}

	.wpshadow-progress-details {
		font-size: 14px;
		color: #646970;
		font-style: italic;
	}

	.wpshadow-quick-scan-prompt {
		padding: 20px;
		margin: 20px 0;
	}

	.wpshadow-quick-scan-prompt p:last-child {
		margin-bottom: 0;
	}
	</style>
	<?php
}
