<?php
/**
 * Multisite Network Report
 *
 * Comprehensive multisite network analysis covering site health, plugin conflicts,
 * user role synchronization, disk usage, and network-wide configuration.
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
Tool_View_Base::enqueue_assets( 'multisite-report' );

// Render header
Tool_View_Base::render_header( __( 'Multisite Network Report', 'wpshadow' ) );

// Check if this is a multisite installation
$is_multisite = is_multisite();

// Get multisite diagnostics
$all_diagnostics = Diagnostic_Registry::get_all();
$multisite_diagnostics = array();

foreach ( $all_diagnostics as $slug => $class ) {
	if ( ! class_exists( $class ) ) {
		continue;
	}

	$family = method_exists( $class, 'get_family' ) ? $class::get_family() : '';
	if ( 'multisite' === $family ) {
		$multisite_diagnostics[ $slug ] = $class;
	}
}

// Get network stats if multisite
$site_count = 0;
$user_count = 0;
if ( $is_multisite ) {
	$site_count = get_blog_count();
	$user_count = get_user_count();
}

?>

<div class="wpshadow-tool multisite-report-tool">

	<?php if ( ! $is_multisite ) : ?>
		<div class="wps-card wps-mb-4">
			<div class="wps-card-body">
				<h2 class="wps-text-xl wps-mb-3">
					<span class="dashicons dashicons-admin-multisite wps-text-muted"></span>
					<?php esc_html_e( 'Not a Multisite Installation', 'wpshadow' ); ?>
				</h2>
				<p class="wps-text-muted">
					<?php esc_html_e( 'This report is designed for WordPress Multisite networks. Your installation is a single-site setup.', 'wpshadow' ); ?>
				</p>
				<p class="wps-text-muted wps-mt-2">
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: link to WordPress Multisite documentation */
							__( 'Want to convert to multisite? Learn more in the <a href="%s" target="_blank" rel="noopener noreferrer">WordPress Multisite documentation</a>.', 'wpshadow' ),
							'https://wordpress.org/documentation/article/create-a-network/'
						)
					);
					?>
				</p>
			</div>
		</div>
	<?php else : ?>

	<div class="wps-card wps-mb-4">
		<div class="wps-card-body">
			<h2 class="wps-text-xl wps-mb-3">
				<span class="dashicons dashicons-admin-multisite wps-text-primary"></span>
				<?php esc_html_e( 'Network Overview', 'wpshadow' ); ?>
			</h2>
			<p class="wps-text-muted wps-mb-3">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: number of sites, 2: number of users, 3: number of diagnostics */
						__( 'Analyzing network with %1$d sites and %2$d users across %3$d specialized multisite diagnostics to identify conflicts, synchronization issues, and optimization opportunities.', 'wpshadow' ),
						$site_count,
						$user_count,
						count( $multisite_diagnostics )
					)
				);
				?>
			</p>

			<div class="wps-grid wps-grid-cols-4 wps-gap-3 wps-mb-4">
				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-admin-multisite wps-text-2xl wps-text-primary"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Total Sites', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold"><?php echo esc_html( $site_count ); ?></div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-admin-plugins wps-text-2xl wps-text-warning"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Plugin Conflicts', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="multisite-conflicts">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-admin-generic wps-text-2xl wps-text-info"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Disk Usage', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="multisite-disk">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-warning wps-text-2xl wps-text-error"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Issues Found', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="multisite-issues-count">-</div>
						</div>
					</div>
				</div>
			</div>

			<button type="button"
				class="wps-btn wps-btn-primary wps-btn-icon-left wpshadow-run-multisite-scan"
				id="run-multisite-scan-btn"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_security_scan' ) ); ?>"
				aria-label="<?php esc_attr_e( 'Run comprehensive multisite network analysis now', 'wpshadow' ); ?>">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Audit Network', 'wpshadow' ); ?>
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
	<div class="scan-results" id="multisite-scan-results"></div>

	<!-- Multisite Best Practices -->
	<div class="wps-card wps-mt-4">
		<div class="wps-card-body">
			<h3 class="wps-text-lg wps-mb-3">
				<?php esc_html_e( 'What This Audit Covers', 'wpshadow' ); ?>
			</h3>
			<div class="wps-grid wps-grid-cols-2 wps-gap-4">
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-admin-multisite"></span>
						<?php esc_html_e( 'Network Configuration', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Subdomain vs subdirectory setup', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Network-wide plugin activation', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Site creation permissions', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Upload space quotas', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Network health score', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-admin-plugins"></span>
						<?php esc_html_e( 'Plugin Management', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Plugin conflicts between sites', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Network-activated vs site-activated', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Multisite compatibility issues', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Resource usage per plugin', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Deprecated or unmaintained plugins', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-admin-users"></span>
						<?php esc_html_e( 'User Management', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'User role conflicts across sites', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Capability synchronization issues', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Super admin vs site admin roles', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'User registration settings', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Spam user detection', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-admin-generic"></span>
						<?php esc_html_e( 'Performance & Storage', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Per-site disk usage', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Upload directory organization', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Database table bloat', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Shared vs isolated resources', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Network-wide caching', 'wpshadow' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- Multisite Best Practices -->
			<div class="wps-mt-4 wps-p-4 wps-bg-success-light wps-rounded">
				<h4 class="wps-font-semibold wps-mb-2">
					<span class="dashicons dashicons-yes-alt"></span>
					<?php esc_html_e( 'Multisite Best Practices', 'wpshadow' ); ?>
				</h4>
				<div class="wps-grid wps-grid-cols-3 wps-gap-3">
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( 'Plugin Strategy:', 'wpshadow' ); ?></p>
						<ul class="wps-list-disc wps-ml-5 wps-text-sm">
							<li><?php esc_html_e( 'Network-activate core plugins', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Allow site-specific plugins carefully', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Test plugins on staging site first', 'wpshadow' ); ?></li>
						</ul>
					</div>
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( 'User Management:', 'wpshadow' ); ?></p>
						<ul class="wps-list-disc wps-ml-5 wps-text-sm">
							<li><?php esc_html_e( 'Regular super admin audits', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Consistent role naming across sites', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Remove spam users promptly', 'wpshadow' ); ?></li>
						</ul>
					</div>
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( 'Performance:', 'wpshadow' ); ?></p>
						<ul class="wps-list-disc wps-ml-5 wps-text-sm">
							<li><?php esc_html_e( 'Implement network-wide caching', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Monitor disk usage per site', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Optimize shared database tables', 'wpshadow' ); ?></li>
						</ul>
					</div>
				</div>
			</div>

			<!-- Common Multisite Issues -->
			<div class="wps-mt-4 wps-p-4 wps-bg-warning-light wps-rounded">
				<h4 class="wps-font-semibold wps-mb-2">
					<span class="dashicons dashicons-warning"></span>
					<?php esc_html_e( 'Common Multisite Pitfalls', 'wpshadow' ); ?>
				</h4>
				<div class="wps-grid wps-grid-cols-2 wps-gap-3">
					<div>
						<p class="wps-text-sm"><strong><?php esc_html_e( 'Plugin conflicts:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Plugin works on Site A, breaks Site B. Always test network-wide changes on staging first.', 'wpshadow' ); ?></p>
					</div>
					<div>
						<p class="wps-text-sm"><strong><?php esc_html_e( 'Role confusion:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'User is admin on Site A, has no access on Site B. Roles must be set per-site.', 'wpshadow' ); ?></p>
					</div>
					<div>
						<p class="wps-text-sm"><strong><?php esc_html_e( 'Disk quotas:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'One site uploads huge files, hits network quota. Monitor and set per-site limits.', 'wpshadow' ); ?></p>
					</div>
					<div>
						<p class="wps-text-sm"><strong><?php esc_html_e( 'Database bloat:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Each site adds tables. Regular cleanup of unused sites prevents database explosion.', 'wpshadow' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php endif; ?>
</div>

<?php if ( $is_multisite ) : ?>
<script>
jQuery(document).ready(function($) {
	<?php echo \WPShadow\Views\Tool_View_Base::get_js_scan_state_helpers(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	$('#run-multisite-scan-btn').on('click', function() {
		const $btn = $(this);
		const $progress = $('.scan-progress');
		const $results = $('#multisite-scan-results');

		wpshadowReportScanStart( $btn, $progress, $results );

		// Run multisite diagnostics
		wpshadowRunFamilyDiagnostics( 'multisite', $btn.data('nonce') ).done(function(response) {
			displayMultisiteResults(response);
		}).fail(function(error) {
			$results.html('<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_error_notice_open_html() ); ?>' + error.message + '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_error_notice_close_html() ); ?>');
		}).always(function() {
			wpshadowReportScanEnd( $btn, $progress );
		});
	});

	function displayMultisiteResults(data) {
		const $results = $('#multisite-scan-results');
		const findings = data.findings || [];

		$('#multisite-issues-count').text(findings.length);

		// Count specific issue types
		let conflictCount = 0, diskCount = 0;
		findings.forEach(function(finding) {
			const title = finding.title.toLowerCase();
			if (title.includes('conflict') || title.includes('plugin')) conflictCount++;
			if (title.includes('disk') || title.includes('usage')) diskCount++;
		});

		$('#multisite-conflicts').text(conflictCount > 0 ? conflictCount : '<?php echo esc_js( __( 'None', 'wpshadow' ) ); ?>');
		$('#multisite-disk').text(diskCount > 0 ? '<?php echo esc_js( __( 'Check', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Normal', 'wpshadow' ) ); ?>');

		if (findings.length === 0) {
			$results.html('<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_success_notice_html( __( 'Excellent! Your multisite network is healthy.', 'wpshadow' ) ) ); ?>');
			return;
		}

		// Group by category
		const byCategory = {
			'Configuration': [],
			'Plugin Conflicts': [],
			'User Roles': [],
			'Performance': [],
			'Other': []
		};

		findings.forEach(function(finding) {
			const title = finding.title.toLowerCase();
			if (title.includes('config') || title.includes('subdomain')) {
				byCategory['Configuration'].push(finding);
			} else if (title.includes('plugin') || title.includes('conflict')) {
				byCategory['Plugin Conflicts'].push(finding);
			} else if (title.includes('user') || title.includes('role')) {
				byCategory['User Roles'].push(finding);
			} else if (title.includes('disk') || title.includes('performance')) {
				byCategory['Performance'].push(finding);
			} else {
				byCategory['Other'].push(finding);
			}
		});

		let html = '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_result_card_open_html() ); ?>';
		html += wpshadowRenderSummaryHeading( '<?php echo esc_js( __( 'Network Issues Found', 'wpshadow' ) ); ?>', findings.length );

		Object.keys(byCategory).forEach(function(category) {
			const categoryFindings = byCategory[category];
			if (categoryFindings.length === 0) return;

			html += '<div class="wps-mb-4">';
			html += wpshadowRenderSectionHeading( category, categoryFindings.length, {
				headingClass: 'wps-font-semibold wps-mb-2',
				countSuffix: '<?php echo esc_js( __( 'issues', 'wpshadow' ) ); ?>'
			} );

			categoryFindings.forEach(function(finding) {
				const severityClass = finding.severity === 'high' ? 'error' : (finding.severity === 'medium' ? 'warning' : 'info');
				html += wpshadowRenderFindingCardStart( finding, {
					severityClass: severityClass,
					iconClass: 'dashicons-admin-multisite',
					containerClass: 'wps-mb-2 wps-p-3 wps-border wps-border-' + severityClass + ' wps-rounded',
					titleClass: 'wps-font-semibold wps-text-sm',
					descriptionClass: 'wps-text-muted wps-text-xs'
				} );
				html += wpshadowRenderAutoFixButton( finding, '<?php echo esc_js( __( 'Fix', 'wpshadow' ) ); ?>', 'wps-btn wps-btn-sm wps-btn-success wps-mt-1' );
				html += wpshadowRenderFindingCardEnd();
			});

			html += '</div>';
		});

		html += '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_result_card_close_html() ); ?>';
		$results.html(html);
	}
});
</script>
<?php endif; ?>

<?php
// Load and render sales widget
Tool_View_Base::render_sales_widget(
	array(
		'title'       => __( 'Managing a large network?', 'wpshadow' ),
		'description' => __( 'WPShadow Pro includes advanced multisite tools, network-wide monitoring, and automated conflict resolution.', 'wpshadow' ),
		'features'    => array(
			__( 'Cross-site analytics dashboard', 'wpshadow' ),
			__( 'Automated plugin compatibility testing', 'wpshadow' ),
			__( 'Bulk site management', 'wpshadow' ),
			__( 'Network-wide security policies', 'wpshadow' ),
		),
		'cta_text'    => __( 'Upgrade to Pro Network Manager', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-admin-multisite',
		'style'       => 'default',
	)
);
?>

<?php Tool_View_Base::render_footer(); ?>
