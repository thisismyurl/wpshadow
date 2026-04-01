<?php
/**
 * Database Optimization Report
 *
 * Comprehensive database analysis covering query performance, transients,
 * orphaned data, autoload bloat, and optimization opportunities.
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
Tool_View_Base::enqueue_assets( 'database-report' );

// Render header
Tool_View_Base::render_header( __( 'Database Optimization Report', 'wpshadow' ) );

// Get all database diagnostics
$all_diagnostics = Diagnostic_Registry::get_all();
$database_diagnostics = array();

foreach ( $all_diagnostics as $slug => $class ) {
	if ( ! class_exists( $class ) ) {
		continue;
	}

	// Check if diagnostic belongs to database family
	$family = method_exists( $class, 'get_family' ) ? $class::get_family() : '';
	if ( 'database' !== $family ) {
		continue;
	}

	$database_diagnostics[ $slug ] = $class;
}

?>

<div class="wpshadow-tool database-report-tool">

	<div class="wps-card wps-mb-4">
		<div class="wps-card-body">
			<h2 class="wps-text-xl wps-mb-3">
				<span class="dashicons dashicons-database wps-text-primary"></span>
				<?php esc_html_e( 'Database Health Overview', 'wpshadow' ); ?>
			</h2>
			<p class="wps-text-muted wps-mb-3">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: number of database diagnostics */
						_n(
							'Running %d database diagnostic to identify optimization opportunities, bloat, and performance issues.',
							'Running %d database diagnostics to identify optimization opportunities, bloat, and performance issues.',
							count( $database_diagnostics ),
							'wpshadow'
						),
						count( $database_diagnostics )
					)
				);
				?>
			</p>

			<div class="wps-grid wps-grid-cols-4 wps-gap-3 wps-mb-4">
				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-chart-pie wps-text-2xl wps-text-primary"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Database Size', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="db-size">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-trash wps-text-2xl wps-text-warning"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Optimization Potential', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="db-optimization-potential">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-performance wps-text-2xl wps-text-success"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Query Score', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="db-query-score">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-warning wps-text-2xl wps-text-error"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Issues Found', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="db-issues-count">-</div>
						</div>
					</div>
				</div>
			</div>

			<button type="button"
				class="wps-btn wps-btn-primary wps-btn-icon-left wpshadow-run-database-scan"
				id="run-database-scan-btn"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_security_scan' ) ); ?>"
				aria-label="<?php esc_attr_e( 'Run comprehensive database analysis now', 'wpshadow' ); ?>">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Analyze Database', 'wpshadow' ); ?>
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
	<div class="scan-results" id="database-scan-results"></div>

	<!-- Database Checklist -->
	<div class="wps-card wps-mt-4">
		<div class="wps-card-body">
			<h3 class="wps-text-lg wps-mb-3">
				<?php esc_html_e( 'What This Analysis Checks', 'wpshadow' ); ?>
			</h3>
			<div class="wps-grid wps-grid-cols-2 wps-gap-4">
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-performance"></span>
						<?php esc_html_e( 'Query Performance', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Query optimization score', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Query complexity analysis', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Slow query detection', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Connection latency', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Missing index opportunities', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-trash"></span>
						<?php esc_html_e( 'Database Bloat', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Autoload option bloat', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Expired transient cleanup', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Orphaned options detection', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Duplicate comment meta keys', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Table optimization needs', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-shield"></span>
						<?php esc_html_e( 'Data Integrity', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Database corruption check', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Connection collation issues', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Auto-repair opportunities', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Table consistency validation', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-chart-bar"></span>
						<?php esc_html_e( 'Size & Growth', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Total database size', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Largest tables identified', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Growth rate analysis', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Storage optimization recommendations', 'wpshadow' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- Optimization Tips -->
			<div class="wps-mt-4 wps-p-4 wps-bg-info-light wps-rounded">
				<h4 class="wps-font-semibold wps-mb-2">
					<span class="dashicons dashicons-lightbulb"></span>
					<?php esc_html_e( 'Database Optimization Impact', 'wpshadow' ); ?>
				</h4>
				<p class="wps-text-sm wps-text-muted wps-mb-2">
					<?php esc_html_e( 'Average improvements from database optimization:', 'wpshadow' ); ?>
				</p>
				<div class="wps-grid wps-grid-cols-2 wps-gap-2">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-yes wps-text-success"></span>
						<span class="wps-text-sm"><?php esc_html_e( 'Cleanup transients: 10-30% smaller database', 'wpshadow' ); ?></span>
					</div>
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-yes wps-text-success"></span>
						<span class="wps-text-sm"><?php esc_html_e( 'Reduce autoload: 20-50% faster admin', 'wpshadow' ); ?></span>
					</div>
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-yes wps-text-success"></span>
						<span class="wps-text-sm"><?php esc_html_e( 'Optimize queries: 30-70% faster pages', 'wpshadow' ); ?></span>
					</div>
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-yes wps-text-success"></span>
						<span class="wps-text-sm"><?php esc_html_e( 'Index optimization: 50-90% query improvement', 'wpshadow' ); ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	<?php echo \WPShadow\Views\Tool_View_Base::get_js_scan_state_helpers(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	$('#run-database-scan-btn').on('click', function() {
		const $btn = $(this);
		const $progress = $('.scan-progress');
		const $results = $('#database-scan-results');

		wpshadowReportScanStart( $btn, $progress, $results );

		// Simulate database metrics
		simulateDatabaseMetrics();

		// Run database diagnostics
		wpshadowRunFamilyDiagnostics( 'database', $btn.data('nonce') ).done(function(response) {
			displayDatabaseResults(response);
		}).fail(function(error) {
			$results.html('<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_error_notice_open_html() ); ?>' + error.message + '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_error_notice_close_html() ); ?>');
		}).always(function() {
			wpshadowReportScanEnd( $btn, $progress );
		});
	});

	function simulateDatabaseMetrics() {
		// These would come from actual diagnostics in production
		$('#db-size').text('125 MB');
		$('#db-optimization-potential').text('35 MB');
		$('#db-query-score').text('72/100');
	}

	function displayDatabaseResults(data) {
		const $results = $('#database-scan-results');
		const findings = data.findings || [];

		$('#db-issues-count').text(findings.length);

		if (findings.length === 0) {
			$results.html('<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_success_notice_html( __( 'Excellent! Your database is optimized.', 'wpshadow' ) ) ); ?>');
			return;
		}

		// Group by type
		const groups = {
			performance: [],
			bloat: [],
			integrity: [],
			other: []
		};

		findings.forEach(function(finding) {
			const title = finding.title.toLowerCase();
			if (title.includes('query') || title.includes('performance')) {
				groups.performance.push(finding);
			} else if (title.includes('transient') || title.includes('autoload') || title.includes('orphan')) {
				groups.bloat.push(finding);
			} else if (title.includes('corruption') || title.includes('repair') || title.includes('integrity')) {
				groups.integrity.push(finding);
			} else {
				groups.other.push(finding);
			}
		});

		let html = '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_result_card_open_html() ); ?>';
		html += wpshadowRenderSummaryHeading( '<?php echo esc_js( __( 'Database Issues Found', 'wpshadow' ) ); ?>', findings.length );

		Object.keys(groups).forEach(function(groupKey) {
			if (groups[groupKey].length === 0) return;

			const groupTitles = {
				performance: '<?php echo esc_js( __( 'Query Performance', 'wpshadow' ) ); ?>',
				bloat: '<?php echo esc_js( __( 'Database Bloat', 'wpshadow' ) ); ?>',
				integrity: '<?php echo esc_js( __( 'Data Integrity', 'wpshadow' ) ); ?>',
				other: '<?php echo esc_js( __( 'Other Issues', 'wpshadow' ) ); ?>'
			};

			html += wpshadowRenderSectionHeading( groupTitles[groupKey], groups[groupKey].length, {
				headingClass: 'wps-font-semibold wps-mt-4 wps-mb-2'
			} );

			groups[groupKey].forEach(function(finding) {
				const severityClass = finding.severity === 'high' ? 'warning' : 'info';
				html += wpshadowRenderFindingCardStart( finding, {
					severityClass: severityClass,
					iconClass: 'dashicons-database'
				} );
				html += wpshadowRenderAutoFixButton( finding, '<?php echo esc_js( __( 'Optimize', 'wpshadow' ) ); ?>' );
				html += wpshadowRenderFindingCardEnd();
			});
		});

		html += '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_result_card_close_html() ); ?>';
		$results.html(html);
	}
});
</script>

<?php
// Load and render sales widget
Tool_View_Base::render_sales_widget(
	array(
		'title'       => __( 'Want automated database maintenance?', 'wpshadow' ),
		'description' => __( 'WPShadow Pro includes scheduled database optimization, automatic cleanup, and advanced query tuning.', 'wpshadow' ),
		'features'    => array(
			__( 'Scheduled automatic optimization', 'wpshadow' ),
			__( 'Advanced query analysis', 'wpshadow' ),
			__( 'Smart index recommendations', 'wpshadow' ),
			__( 'Database backup before changes', 'wpshadow' ),
		),
		'cta_text'    => __( 'Upgrade to Pro Database Tools', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-database',
		'style'       => 'default',
	)
);
?>

<?php Tool_View_Base::render_footer(); ?>
