<?php
/**
 * Security Report
 *
 * Comprehensive security audit covering vulnerabilities, hardening,
 * authentication, file permissions, and threat analysis.
 *
 * @package    WPShadow
 * @subpackage Reports
 * @since      1.6030.1200
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
Tool_View_Base::enqueue_assets( 'security-report' );

// Render header
Tool_View_Base::render_header( __( 'Security Report', 'wpshadow' ) );

// Get all security diagnostics
$all_diagnostics = Diagnostic_Registry::get_all();
$security_diagnostics = array();

foreach ( $all_diagnostics as $slug => $class ) {
	if ( ! class_exists( $class ) ) {
		continue;
	}

	// Check if diagnostic belongs to security family
	$family = method_exists( $class, 'get_family' ) ? $class::get_family() : '';
	if ( 'security' !== $family && 'protection' !== $family ) {
		continue;
	}

	$security_diagnostics[ $slug ] = $class;
}

?>

<div class="wpshadow-tool security-report-tool">
	
	<div class="wps-card wps-mb-4">
		<div class="wps-card-body">
			<h2 class="wps-text-xl wps-mb-3">
				<span class="dashicons dashicons-shield-alt wps-text-primary"></span>
				<?php esc_html_e( 'Security Overview', 'wpshadow' ); ?>
			</h2>
			<p class="wps-text-muted wps-mb-3">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: number of security diagnostics */
						_n(
							'Running %d comprehensive security check to identify vulnerabilities, hardening opportunities, and protection gaps.',
							'Running %d comprehensive security checks to identify vulnerabilities, hardening opportunities, and protection gaps.',
							count( $security_diagnostics ),
							'wpshadow'
						),
						count( $security_diagnostics )
					)
				);
				?>
			</p>

			<div class="wps-flex wps-gap-3 wps-mb-4">
				<div class="wps-flex-1 wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-lock wps-text-2xl wps-text-success"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Authentication', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="security-auth-count">-</div>
						</div>
					</div>
				</div>

				<div class="wps-flex-1 wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-admin-network wps-text-2xl wps-text-warning"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Network Security', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="security-network-count">-</div>
						</div>
					</div>
				</div>

				<div class="wps-flex-1 wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-warning wps-text-2xl wps-text-error"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Critical Threats', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="security-critical-count">-</div>
						</div>
					</div>
				</div>

				<div class="wps-flex-1 wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-yes-alt wps-text-2xl wps-text-success"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Passed Checks', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="security-passed-count">-</div>
						</div>
					</div>
				</div>
			</div>

			<button type="button" 
				class="wps-btn wps-btn-primary wps-btn-icon-left wpshadow-run-security-scan" 
				id="run-security-scan-btn"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_security_scan' ) ); ?>"
				aria-label="<?php esc_attr_e( 'Run comprehensive security scan now', 'wpshadow' ); ?>">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Run Security Scan', 'wpshadow' ); ?>
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
	<div class="scan-results" id="security-scan-results"></div>

	<!-- Security Checklist -->
	<div class="wps-card wps-mt-4">
		<div class="wps-card-body">
			<h3 class="wps-text-lg wps-mb-3">
				<?php esc_html_e( 'What This Scan Checks', 'wpshadow' ); ?>
			</h3>
			<div class="wps-grid wps-grid-cols-2 wps-gap-3">
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-shield"></span>
						<?php esc_html_e( 'Core Security', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'SSL certificate validity', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Security headers configuration', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Debug mode in production', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'File permissions audit', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Directory listing exposure', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-admin-users"></span>
						<?php esc_html_e( 'Authentication & Users', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'User enumeration vulnerabilities', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Admin username exposure', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Password strength requirements', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Two-factor authentication status', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Login attempt hardening', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Updates & Patches', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Unpatched plugin vulnerabilities', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Outdated theme versions', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'WordPress core version', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'PHP version EOL status', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-database"></span>
						<?php esc_html_e( 'Data Protection', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Database backup currency', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'File backup status', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Backup integrity validation', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Sensitive file exposure', 'wpshadow' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	<?php echo \WPShadow\Views\Tool_View_Base::get_js_scan_state_helpers(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	$('#run-security-scan-btn').on('click', function() {
		const $btn = $(this);
		const $progress = $('.scan-progress');
		const $results = $('#security-scan-results');
		
		wpshadowReportScanStart( $btn, $progress, $results );
		
		// Run security diagnostics
		wpshadowRunFamilyDiagnostics( 'security', $btn.data('nonce') ).done(function(response) {
			displaySecurityResults(response);
		}).fail(function(error) {
			$results.html('<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_error_notice_open_html() ); ?>' + error.message + '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_error_notice_close_html() ); ?>');
		}).always(function() {
			wpshadowReportScanEnd( $btn, $progress );
		});
	});

	function displaySecurityResults(data) {
		const $results = $('#security-scan-results');
		const findings = data.findings || [];
		
		// Update summary counts
		let authCount = 0, networkCount = 0, criticalCount = 0, passedCount = 0;
		let totalChecks = data.total_diagnostics || <?php echo count( $security_diagnostics ); ?>;
		
		findings.forEach(function(finding) {
			if (finding.severity === 'critical') criticalCount++;
			if (finding.title && finding.title.toLowerCase().includes('auth')) authCount++;
			if (finding.title && (finding.title.toLowerCase().includes('ssl') || finding.title.toLowerCase().includes('header'))) networkCount++;
		});
		
		passedCount = totalChecks - findings.length;
		
		$('#security-auth-count').text(authCount + ' ' + '<?php echo esc_js( __( 'issues', 'wpshadow' ) ); ?>');
		$('#security-network-count').text(networkCount + ' ' + '<?php echo esc_js( __( 'issues', 'wpshadow' ) ); ?>');
		$('#security-critical-count').text(criticalCount);
		$('#security-passed-count').text(passedCount);
		
		// Display findings
		if (findings.length === 0) {
			$results.html('<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_success_notice_html( __( 'Excellent! No security issues found.', 'wpshadow' ) ) ); ?>');
			return;
		}
		
		let html = '<?php echo esc_js( \WPShadow\Views\Tool_View_Base::get_js_result_card_open_html() ); ?>';
		html += wpshadowRenderSummaryHeading( '<?php echo esc_js( __( 'Security Issues Found', 'wpshadow' ) ); ?>', findings.length );
		
		findings.forEach(function(finding) {
			const severityClass = finding.severity === 'critical' ? 'error' : finding.severity === 'high' ? 'warning' : 'info';
			html += wpshadowRenderFindingCardStart( finding, {
				severityClass: severityClass,
				iconClass: 'dashicons-warning',
				titleTag: 'h4',
				descriptionClass: 'wps-text-muted'
			} );
			html += wpshadowRenderAutoFixButton( finding, '<?php echo esc_js( __( 'Auto-Fix', 'wpshadow' ) ); ?>' );
			html += wpshadowRenderFindingCardEnd();
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
		'title'       => __( 'Want advanced security monitoring?', 'wpshadow' ),
		'description' => __( 'WPShadow Pro includes real-time threat detection, firewall protection, and automated security hardening.', 'wpshadow' ),
		'features'    => array(
			__( 'Real-time malware scanning', 'wpshadow' ),
			__( 'Web application firewall', 'wpshadow' ),
			__( 'Automated security hardening', 'wpshadow' ),
			__( 'Instant security alerts', 'wpshadow' ),
		),
		'cta_text'    => __( 'Upgrade to Pro Security', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-shield-alt',
		'style'       => 'default',
	)
);
?>

<?php Tool_View_Base::render_footer(); ?>
