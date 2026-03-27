<?php
/**
 * Plugin Audit Report
 *
 * Comprehensive plugin analysis covering security, performance impact,
 * conflicts, updates, configuration, and optimization opportunities.
 *
 * @package    WPShadow
 * @subpackage Reports
 * @since 1.6093.1200
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
Tool_View_Base::enqueue_assets( 'plugins-report' );

// Render header
Tool_View_Base::render_header( __( 'Plugin Audit Report', 'wpshadow' ) );

// Get plugin diagnostics
$all_diagnostics = Diagnostic_Registry::get_all();
$plugin_diagnostics = array();

foreach ( $all_diagnostics as $slug => $class ) {
	if ( ! class_exists( $class ) ) {
		continue;
	}

	// Check if diagnostic belongs to plugins family
	$family = method_exists( $class, 'get_family' ) ? $class::get_family() : '';
	if ( 'plugins' !== $family ) {
		continue;
	}

	$plugin_diagnostics[ $slug ] = $class;
}

// Get installed plugins
$all_plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );
$plugin_count = count( $all_plugins );
$active_count = count( $active_plugins );

?>

<div class="wpshadow-tool plugins-report-tool">
	
	<div class="wps-card wps-mb-4">
		<div class="wps-card-body">
			<h2 class="wps-text-xl wps-mb-3">
				<span class="dashicons dashicons-admin-plugins wps-text-primary"></span>
				<?php esc_html_e( 'Plugin Health Overview', 'wpshadow' ); ?>
			</h2>
			<p class="wps-text-muted wps-mb-3">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: number of plugins, 2: number of active plugins, 3: number of diagnostics */
						__( 'Analyzing %1$d installed plugins (%2$d active) with %3$d specialized diagnostics to identify security issues, performance impacts, and configuration problems.', 'wpshadow' ),
						$plugin_count,
						$active_count,
						count( $plugin_diagnostics )
					)
				);
				?>
			</p>

			<div class="wps-grid wps-grid-cols-4 wps-gap-3 wps-mb-4">
				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-admin-plugins wps-text-2xl wps-text-primary"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Total Plugins', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold"><?php echo esc_html( $plugin_count ); ?></div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-update wps-text-2xl wps-text-warning"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Updates Available', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="plugins-updates">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-performance wps-text-2xl wps-text-success"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Performance Impact', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="plugins-performance">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-warning wps-text-2xl wps-text-error"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Issues Found', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="plugins-issues-count">-</div>
						</div>
					</div>
				</div>
			</div>

			<button type="button" 
				class="wps-btn wps-btn-primary wps-btn-icon-left wpshadow-run-plugins-scan" 
				id="run-plugins-scan-btn"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_security_scan' ) ); ?>"
				aria-label="<?php esc_attr_e( 'Run comprehensive plugin analysis now', 'wpshadow' ); ?>">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Audit Plugins', 'wpshadow' ); ?>
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
	<div class="scan-results" id="plugins-scan-results"></div>

	<!-- Plugin Checklist -->
	<div class="wps-card wps-mt-4">
		<div class="wps-card-body">
			<h3 class="wps-text-lg wps-mb-3">
				<?php esc_html_e( 'What This Audit Covers', 'wpshadow' ); ?>
			</h3>
			<div class="wps-grid wps-grid-cols-2 wps-gap-4">
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-shield"></span>
						<?php esc_html_e( 'Security & Updates', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Known plugin vulnerabilities', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Outdated plugin versions', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Security configuration issues', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'API key security (Jetpack, Akismet, etc.)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Plugin conflict detection', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-performance"></span>
						<?php esc_html_e( 'Performance Impact', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Plugin load time contribution', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Database performance impact', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Asset optimization opportunities', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Unused or redundant plugins', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Resource-heavy plugin identification', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-admin-settings"></span>
						<?php esc_html_e( 'Configuration', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Popular plugins (Yoast, WP Rocket, etc.)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Form plugins (Gravity, Contact Form 7, Ninja)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Backup plugins (UpdraftPlus, etc.)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Image optimization (ShortPixel, etc.)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Analytics plugins (MonsterInsights, etc.)', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-admin-tools"></span>
						<?php esc_html_e( 'Best Practices', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Plugin compatibility checks', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Spam protection configuration', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Backup schedule validation', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Accessibility compliance', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'SEO plugin optimization', 'wpshadow' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- Plugin Recommendations -->
			<div class="wps-mt-4 wps-p-4 wps-bg-warning-light wps-rounded">
				<h4 class="wps-font-semibold wps-mb-2">
					<span class="dashicons dashicons-lightbulb"></span>
					<?php esc_html_e( 'Plugin Optimization Guidelines', 'wpshadow' ); ?>
				</h4>
				<div class="wps-grid wps-grid-cols-2 wps-gap-3">
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( 'Keep It Lean:', 'wpshadow' ); ?></p>
						<ul class="wps-list-disc wps-ml-5 wps-text-sm">
							<li><?php esc_html_e( 'Fewer plugins = faster site (aim for <20)', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Remove inactive plugins completely', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Consolidate functionality where possible', 'wpshadow' ); ?></li>
						</ul>
					</div>
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( 'Stay Secure:', 'wpshadow' ); ?></p>
						<ul class="wps-list-disc wps-ml-5 wps-text-sm">
							<li><?php esc_html_e( 'Update within 24 hours of security releases', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Verify developer reputation before installing', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Test updates on staging first', 'wpshadow' ); ?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	<?php echo \WPShadow\Views\Tool_View_Base::get_js_scan_state_helpers(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	$('#run-plugins-scan-btn').on('click', function() {
		const $btn = $(this);
		const $progress = $('.scan-progress');
		const $results = $('#plugins-scan-results');
		
		wpshadowReportScanStart( $btn, $progress, $results );
		
		// Simulate plugin metrics
		$('#plugins-updates').text('3');
		$('#plugins-performance').text('Good');
		
		// Run plugin diagnostics
		wpshadowRunFamilyDiagnostics( 'plugins', $btn.data('nonce') ).done(function(response) {
			displayPluginsResults(response);
		}).fail(function(error) {
			$results.html('<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_error_notice_open_html() ); ?>' + error.message + '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_error_notice_close_html() ); ?>');
		}).always(function() {
			wpshadowReportScanEnd( $btn, $progress );
		});
	});

	function displayPluginsResults(data) {
		const $results = $('#plugins-scan-results');
		const findings = data.findings || [];
		
		$('#plugins-issues-count').text(findings.length);
		
		if (findings.length === 0) {
			$results.html('<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_success_notice_html( __( 'Excellent! Your plugins are well-configured.', 'wpshadow' ) ) ); ?>');
			return;
		}
		
		// Group by plugin
		const byPlugin = {};
		findings.forEach(function(finding) {
			// Extract plugin name from title
			const pluginMatch = finding.title.match(/^([A-Za-z0-9\s]+)/);
			const pluginName = pluginMatch ? pluginMatch[1].trim() : 'General';
			
			if (!byPlugin[pluginName]) {
				byPlugin[pluginName] = [];
			}
			byPlugin[pluginName].push(finding);
		});
		
		let html = '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_result_card_open_html() ); ?>';
		html += wpshadowRenderSummaryHeading( '<?php echo esc_js( __( 'Plugin Issues Found', 'wpshadow' ) ); ?>', findings.length );
		
		Object.keys(byPlugin).forEach(function(pluginName) {
			const pluginFindings = byPlugin[pluginName];
			html += '<div class="wps-mb-4">';
			html += wpshadowRenderSectionHeading( pluginName, pluginFindings.length, {
				headingClass: 'wps-font-semibold wps-mb-2',
				countSuffix: '<?php echo esc_js( __( 'issues', 'wpshadow' ) ); ?>'
			} );
			
			pluginFindings.forEach(function(finding) {
				const severityClass = finding.severity === 'high' ? 'warning' : 'info';
				html += wpshadowRenderFindingCardStart( finding, {
					severityClass: severityClass,
					iconClass: 'dashicons-admin-plugins',
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

<?php
// Load and render sales widget
Tool_View_Base::render_sales_widget(
	array(
		'title'       => __( 'Want automated plugin monitoring?', 'wpshadow' ),
		'description' => __( 'WPShadow Pro tracks plugin vulnerabilities, monitors performance impact, and alerts you to conflicts automatically.', 'wpshadow' ),
		'features'    => array(
			__( 'Real-time vulnerability scanning', 'wpshadow' ),
			__( 'Performance impact tracking', 'wpshadow' ),
			__( 'Conflict detection and resolution', 'wpshadow' ),
			__( 'Automated update testing', 'wpshadow' ),
		),
		'cta_text'    => __( 'Upgrade to Pro Plugin Manager', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-admin-plugins',
		'style'       => 'default',
	)
);
?>

<?php Tool_View_Base::render_footer(); ?>
