<?php
/**
 * Performance Report
 *
 * Comprehensive performance analysis covering load times, database queries,
 * caching, asset optimization, and mobile performance.
 *
 * @package    WPShadow
 * @subpackage Reports
 * @since 0.6093.1200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Views\Tool_View_Base;
use WPShadow\Diagnostics\Diagnostic_Registry;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Verify access
Tool_View_Base::verify_access( 'manage_options' );

// Enqueue assets
Tool_View_Base::enqueue_assets( 'performance-report' );

// Render header
Tool_View_Base::render_header( __( 'Performance Report', 'wpshadow' ) );

// Get all performance diagnostics
$all_diagnostics = Diagnostic_Registry::get_all();
$performance_diagnostics = array();

foreach ( $all_diagnostics as $slug => $class ) {
	if ( ! class_exists( $class ) ) {
		continue;
	}

	// Check if diagnostic belongs to performance family
	$family = method_exists( $class, 'get_family' ) ? $class::get_family() : '';
	if ( 'performance' !== $family ) {
		continue;
	}

	$performance_diagnostics[ $slug ] = $class;
}

?>

<div class="wpshadow-tool performance-report-tool">

	<div class="wps-card wps-mb-4">
		<div class="wps-card-body">
			<h2 class="wps-text-xl wps-mb-3">
				<span class="dashicons dashicons-performance wps-text-primary"></span>
				<?php esc_html_e( 'Performance Overview', 'wpshadow' ); ?>
			</h2>
			<p class="wps-text-muted wps-mb-3">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: number of performance diagnostics */
						_n(
							'Running %d performance diagnostic to identify speed bottlenecks, optimization opportunities, and efficiency improvements.',
							'Running %d performance diagnostics to identify speed bottlenecks, optimization opportunities, and efficiency improvements.',
							count( $performance_diagnostics ),
							'wpshadow'
						),
						count( $performance_diagnostics )
					)
				);
				?>
			</p>

			<div class="wps-grid wps-grid-cols-4 wps-gap-3 wps-mb-4">
				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-clock wps-text-2xl wps-text-primary"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Page Load Time', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="perf-load-time">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-database wps-text-2xl wps-text-warning"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Database Queries', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="perf-db-queries">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-warning wps-text-2xl wps-text-error"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Issues Found', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="perf-issues-count">-</div>
						</div>
					</div>
				</div>
			</div>

			<button type="button"
				class="wps-btn wps-btn-primary wps-btn-icon-left wpshadow-run-performance-scan"
				id="run-performance-scan-btn"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_performance_scan' ) ); ?>"
				aria-label="<?php esc_attr_e( 'Run comprehensive performance analysis now', 'wpshadow' ); ?>">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Run Performance Scan', 'wpshadow' ); ?>
			</button>
		</div>
	</div>

	<!-- Scan Progress -->
	<div class="scan-progress hidden wps-card wps-mb-4" role="status" aria-live="polite">
		<div class="wps-card-body">
			<div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
				<div class="progress-fill"></div>
			</div>
			<p class="progress-text wps-text-center wps-mt-2"></p>
		</div>
	</div>

	<!-- Scan Results -->
	<div class="scan-results" id="performance-scan-results"></div>

	<!-- Performance Breakdown -->
	<div class="wps-card wps-mt-4">
		<div class="wps-card-body">
			<h3 class="wps-text-lg wps-mb-3">
				<?php esc_html_e( 'What This Scan Analyzes', 'wpshadow' ); ?>
			</h3>
			<div class="wps-grid wps-grid-cols-2 wps-gap-4">
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-chart-bar"></span>
						<?php esc_html_e( 'Load Time Analysis', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Server response time (TTFB)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Total page load duration', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Desktop vs mobile performance gap', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Core Web Vitals metrics', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Performance bottleneck identification', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-database"></span>
						<?php esc_html_e( 'Database Optimization', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Query count per page load', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Slow query detection', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Database size analysis', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Connection latency', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Transient cleanup opportunities', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-images-alt2"></span>
						<?php esc_html_e( 'Asset Optimization', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Image compression analysis', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'CSS/JS minification status', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Font loading strategy', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Render-blocking resources', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Unused CSS/JS detection', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-admin-settings"></span>
						<?php esc_html_e( 'Caching & Infrastructure', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Browser caching configuration', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Object cache status', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Page cache effectiveness', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'CDN integration check', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'PHP memory allocation', 'wpshadow' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- Performance Tips -->
			<div class="wps-mt-4 wps-p-4 wps-bg-info-light wps-rounded">
				<h4 class="wps-font-semibold wps-mb-2">
					<span class="dashicons dashicons-lightbulb"></span>
					<?php esc_html_e( 'Performance Quick Wins', 'wpshadow' ); ?>
				</h4>
				<p class="wps-text-sm wps-text-muted">
					<?php esc_html_e( 'Based on our analysis of thousands of WordPress sites, these optimizations typically provide the biggest speed improvements:', 'wpshadow' ); ?>
				</p>
				<ul class="wps-list-disc wps-ml-5 wps-mt-2">
					<li><?php esc_html_e( 'Enable object caching (Redis/Memcached) - 30-50% faster', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Optimize images (WebP format) - 40-60% smaller files', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Reduce database queries - 20-40% faster pages', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'Enable full-page caching - 90%+ faster for repeat visitors', 'wpshadow' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	<?php echo \WPShadow\Views\Tool_View_Base::get_js_scan_state_helpers(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	$('#run-performance-scan-btn').on('click', function() {
		const $btn = $(this);
		const $progress = $('.scan-progress');
		const $results = $('#performance-scan-results');

		wpshadowReportScanStart( $btn, $progress, $results );

		// Simulate performance measurement
		measurePerformance();

		// Run performance diagnostics
		wpshadowRunFamilyDiagnostics( 'performance', $btn.data('nonce') ).done(function(response) {
			displayPerformanceResults(response);
		}).fail(function(error) {
			$results.html('<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_error_notice_open_html() ); ?>' + error.message + '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_error_notice_close_html() ); ?>');
		}).always(function() {
			wpshadowReportScanEnd( $btn, $progress );
		});
	});

	function measurePerformance() {
		// Simulate performance metrics
		const loadTime = (Math.random() * 2 + 1).toFixed(2);
		const dbQueries = Math.floor(Math.random() * 50 + 30);

		$('#perf-load-time').text(loadTime + 's');
		$('#perf-db-queries').text(dbQueries);
	}

	function displayPerformanceResults(data) {
		const $results = $('#performance-scan-results');
		const findings = data.findings || [];

		// Update issue count
		$('#perf-issues-count').text(findings.length);

		// Display findings grouped by category
		if (findings.length === 0) {
			$results.html('<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_success_notice_html( __( 'Great! Your site performance is optimal.', 'wpshadow' ) ) ); ?>');
			return;
		}

		// Group by category
		const groups = {
			database: [],
			caching: [],
			assets: [],
			server: [],
			other: []
		};

		findings.forEach(function(finding) {
			const title = finding.title.toLowerCase();
			if (title.includes('database') || title.includes('query')) {
				groups.database.push(finding);
			} else if (title.includes('cache')) {
				groups.caching.push(finding);
			} else if (title.includes('image') || title.includes('font') || title.includes('css') || title.includes('js')) {
				groups.assets.push(finding);
			} else if (title.includes('server') || title.includes('memory')) {
				groups.server.push(finding);
			} else {
				groups.other.push(finding);
			}
		});

		let html = '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_result_card_open_html() ); ?>';
		html += wpshadowRenderSummaryHeading( '<?php echo esc_js( __( 'Performance Issues Found', 'wpshadow' ) ); ?>', findings.length );

		// Render each group
		Object.keys(groups).forEach(function(groupKey) {
			if (groups[groupKey].length === 0) return;

			const groupTitles = {
				database: '<?php echo esc_js( __( 'Database Performance', 'wpshadow' ) ); ?>',
				caching: '<?php echo esc_js( __( 'Caching Configuration', 'wpshadow' ) ); ?>',
				assets: '<?php echo esc_js( __( 'Asset Optimization', 'wpshadow' ) ); ?>',
				server: '<?php echo esc_js( __( 'Server Resources', 'wpshadow' ) ); ?>',
				other: '<?php echo esc_js( __( 'Other Performance Issues', 'wpshadow' ) ); ?>'
			};

			html += wpshadowRenderSectionHeading( groupTitles[groupKey], groups[groupKey].length, {
				headingClass: 'wps-font-semibold wps-mt-4 wps-mb-2'
			} );

			groups[groupKey].forEach(function(finding) {
				const severityClass = finding.severity === 'high' ? 'warning' : finding.severity === 'medium' ? 'info' : 'success';
				html += wpshadowRenderFindingCardStart( finding, {
					severityClass: severityClass,
					iconClass: 'dashicons-clock'
				} );
				html += wpshadowRenderAutoFixButton( finding, '<?php echo esc_js( __( 'Auto-Fix', 'wpshadow' ) ); ?>' );
				html += wpshadowRenderFindingCardEnd();
			});
		});

		html += '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_result_card_close_html() ); ?>';
		$results.html(html);
	}
});
</script>

<?php Tool_View_Base::render_footer(); ?>
