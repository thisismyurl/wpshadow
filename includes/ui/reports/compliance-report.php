<?php
/**
 * Compliance & Privacy Report
 *
 * Comprehensive GDPR, CCPA, and PIPEDA compliance audit covering data protection,
 * privacy policies, cookie consent, and regulatory requirements.
 *
 * @package    WPShadow
 * @subpackage Reports
 * @since      1.603.0145
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
Tool_View_Base::enqueue_assets( 'compliance-report' );

// Render header
Tool_View_Base::render_header( __( 'Compliance & Privacy Report', 'wpshadow' ) );

// Get privacy and compliance diagnostics
$all_diagnostics = Diagnostic_Registry::get_all();
$privacy_diagnostics = array();
$compliance_diagnostics = array();

foreach ( $all_diagnostics as $slug => $class ) {
	if ( ! class_exists( $class ) ) {
		continue;
	}

	$family = method_exists( $class, 'get_family' ) ? $class::get_family() : '';
	if ( 'privacy' === $family ) {
		$privacy_diagnostics[ $slug ] = $class;
	} elseif ( 'compliance' === $family ) {
		$compliance_diagnostics[ $slug ] = $class;
	}
}

?>

<div class="wpshadow-tool compliance-report-tool">
	
	<div class="wps-card wps-mb-4">
		<div class="wps-card-body">
			<h2 class="wps-text-xl wps-mb-3">
				<span class="dashicons dashicons-shield wps-text-primary"></span>
				<?php esc_html_e( 'Compliance Overview', 'wpshadow' ); ?>
			</h2>
			<p class="wps-text-muted wps-mb-3">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: number of privacy diagnostics, 2: number of compliance diagnostics */
						__( 'Analyzing privacy and compliance across %1$d privacy checks and %2$d compliance regulations including GDPR, CCPA, and PIPEDA requirements.', 'wpshadow' ),
						count( $privacy_diagnostics ),
						count( $compliance_diagnostics )
					)
				);
				?>
			</p>

			<div class="wps-grid wps-grid-cols-4 wps-gap-3 wps-mb-4">
				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-privacy wps-text-2xl wps-text-primary"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'GDPR Compliance', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="compliance-gdpr">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-admin-users wps-text-2xl wps-text-success"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'CCPA Compliance', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="compliance-ccpa">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-admin-site wps-text-2xl wps-text-info"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Cookie Consent', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="compliance-cookies">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-warning wps-text-2xl wps-text-error"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Issues Found', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="compliance-issues-count">-</div>
						</div>
					</div>
				</div>
			</div>

			<button type="button" 
				class="wps-btn wps-btn-primary wps-btn-icon-left wpshadow-run-compliance-scan" 
				id="run-compliance-scan-btn"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_security_scan' ) ); ?>"
				aria-label="<?php esc_attr_e( 'Run comprehensive compliance and privacy analysis now', 'wpshadow' ); ?>">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Audit Compliance', 'wpshadow' ); ?>
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
	<div class="scan-results" id="compliance-scan-results"></div>

	<!-- Compliance Checklist -->
	<div class="wps-card wps-mt-4">
		<div class="wps-card-body">
			<h3 class="wps-text-lg wps-mb-3">
				<?php esc_html_e( 'What This Audit Covers', 'wpshadow' ); ?>
			</h3>
			<div class="wps-grid wps-grid-cols-2 wps-gap-4">
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-admin-site"></span>
						<?php esc_html_e( 'GDPR (EU) Requirements', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Privacy policy accessibility', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Cookie consent implementation', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Data export functionality', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Right to be forgotten', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Data retention policies', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-admin-users"></span>
						<?php esc_html_e( 'CCPA (California) Requirements', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Right to know what data is collected', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Right to delete personal information', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Opt-out of data sale', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Non-discrimination for exercising rights', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Cookie consent compliance', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-privacy"></span>
						<?php esc_html_e( 'PIPEDA (Canada) Requirements', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Consent requirements', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Privacy policy requirements', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Data accuracy and retention', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Safeguarding personal information', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Accountability measures', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-admin-tools"></span>
						<?php esc_html_e( 'Technical Compliance', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Database encoding consistency', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Foreign key integrity', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Orphaned data detection', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Google Analytics GDPR compliance', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Cookie banner implementation', 'wpshadow' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- Risk Assessment -->
			<div class="wps-mt-4 wps-p-4 wps-bg-error-light wps-rounded">
				<h4 class="wps-font-semibold wps-mb-2">
					<span class="dashicons dashicons-warning"></span>
					<?php esc_html_e( 'Non-Compliance Risks', 'wpshadow' ); ?>
				</h4>
				<div class="wps-grid wps-grid-cols-2 wps-gap-3">
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( 'Financial Penalties:', 'wpshadow' ); ?></p>
						<ul class="wps-list-disc wps-ml-5 wps-text-sm">
							<li><?php esc_html_e( 'GDPR: Up to €20M or 4% of annual revenue', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'CCPA: $2,500-$7,500 per violation', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'PIPEDA: Up to $100K per violation', 'wpshadow' ); ?></li>
						</ul>
					</div>
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( 'Business Impact:', 'wpshadow' ); ?></p>
						<ul class="wps-list-disc wps-ml-5 wps-text-sm">
							<li><?php esc_html_e( 'Loss of customer trust and brand damage', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Legal costs and regulatory investigations', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Mandatory breach notifications', 'wpshadow' ); ?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('#run-compliance-scan-btn').on('click', function() {
		const $btn = $(this);
		const $progress = $('.scan-progress');
		const $results = $('#compliance-scan-results');
		
		$btn.prop('disabled', true).addClass('wps-loading');
		$progress.removeClass('hidden');
		$results.empty();
		
		// Run both privacy and compliance diagnostics
		Promise.all([
			wp.ajax.post('wpshadow_run_family_diagnostics', {
				family: 'privacy',
				nonce: $btn.data('nonce')
			}),
			wp.ajax.post('wpshadow_run_family_diagnostics', {
				family: 'compliance',
				nonce: $btn.data('nonce')
			})
		]).then(function(responses) {
			const allFindings = [
				...(responses[0].findings || []),
				...(responses[1].findings || [])
			];
			displayComplianceResults({ findings: allFindings });
		}).catch(function(error) {
			$results.html('<div class="notice notice-error"><p>' + error.message + '</p></div>');
		}).always(function() {
			$btn.prop('disabled', false).removeClass('wps-loading');
			$progress.addClass('hidden');
		});
	});

	function displayComplianceResults(data) {
		const $results = $('#compliance-scan-results');
		const findings = data.findings || [];
		
		$('#compliance-issues-count').text(findings.length);
		
		// Count by regulation
		let gdprCount = 0, ccpaCount = 0, cookieCount = 0;
		findings.forEach(function(finding) {
			const title = finding.title.toLowerCase();
			if (title.includes('gdpr')) gdprCount++;
			if (title.includes('ccpa')) ccpaCount++;
			if (title.includes('cookie')) cookieCount++;
		});
		
		$('#compliance-gdpr').text(gdprCount > 0 ? gdprCount + ' <?php echo esc_js( __( 'issues', 'wpshadow' ) ); ?>' : '✓');
		$('#compliance-ccpa').text(ccpaCount > 0 ? ccpaCount + ' <?php echo esc_js( __( 'issues', 'wpshadow' ) ); ?>' : '✓');
		$('#compliance-cookies').text(cookieCount > 0 ? cookieCount + ' <?php echo esc_js( __( 'issues', 'wpshadow' ) ); ?>' : '✓');
		
		if (findings.length === 0) {
			$results.html('<div class="notice notice-success wps-card"><p><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_js( __( 'Excellent! Your site meets major privacy and compliance requirements.', 'wpshadow' ) ); ?></p></div>');
			return;
		}
		
		// Group by regulation
		const byRegulation = {
			'GDPR': [],
			'CCPA': [],
			'PIPEDA': [],
			'Cookie Consent': [],
			'Other': []
		};
		
		findings.forEach(function(finding) {
			const title = finding.title;
			if (title.includes('GDPR')) {
				byRegulation['GDPR'].push(finding);
			} else if (title.includes('CCPA')) {
				byRegulation['CCPA'].push(finding);
			} else if (title.includes('PIPEDA')) {
				byRegulation['PIPEDA'].push(finding);
			} else if (title.includes('Cookie') || title.includes('cookie')) {
				byRegulation['Cookie Consent'].push(finding);
			} else {
				byRegulation['Other'].push(finding);
			}
		});
		
		let html = '<div class="wps-card"><div class="wps-card-body">';
		html += '<h3 class="wps-text-lg wps-mb-3"><?php echo esc_js( __( 'Compliance Issues Found', 'wpshadow' ) ); ?> (' + findings.length + ')</h3>';
		
		Object.keys(byRegulation).forEach(function(regulation) {
			const regulationFindings = byRegulation[regulation];
			if (regulationFindings.length === 0) return;
			
			html += '<div class="wps-mb-4">';
			html += '<h4 class="wps-font-semibold wps-mb-2">' + regulation + ' (' + regulationFindings.length + ' <?php echo esc_js( __( 'issues', 'wpshadow' ) ); ?>)</h4>';
			
			regulationFindings.forEach(function(finding) {
				const severityClass = finding.severity === 'high' ? 'error' : (finding.severity === 'medium' ? 'warning' : 'info');
				html += '<div class="wps-mb-2 wps-p-3 wps-border wps-border-' + severityClass + ' wps-rounded">';
				html += '<div class="wps-flex wps-items-start wps-gap-3">';
				html += '<span class="dashicons dashicons-shield wps-text-' + severityClass + '"></span>';
				html += '<div class="wps-flex-1">';
				html += '<h5 class="wps-font-semibold wps-text-sm">' + finding.title + '</h5>';
				html += '<p class="wps-text-muted wps-text-xs">' + finding.description + '</p>';
				if (finding.auto_fixable) {
					html += '<button class="wps-btn wps-btn-sm wps-btn-success wps-mt-1" data-finding="' + finding.id + '"><?php echo esc_js( __( 'Fix', 'wpshadow' ) ); ?></button>';
				}
				html += '</div></div></div>';
			});
			
			html += '</div>';
		});
		
		html += '</div></div>';
		$results.html(html);
	}
});
</script>

<?php
// Load and render sales widget
require_once WPSHADOW_PATH . 'includes/ui/components/sales-widget.php';

wpshadow_render_sales_widget(
	array(
		'title'       => __( 'Need help with compliance?', 'wpshadow' ),
		'description' => __( 'WPShadow Pro includes automated compliance monitoring, GDPR consent management, and expert legal guidance.', 'wpshadow' ),
		'features'    => array(
			__( 'Automated regulatory updates', 'wpshadow' ),
			__( 'Custom privacy policy generator', 'wpshadow' ),
			__( 'Data breach notification system', 'wpshadow' ),
			__( 'Compliance documentation', 'wpshadow' ),
		),
		'cta_text'    => __( 'Upgrade to Pro Compliance Manager', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-privacy',
		'style'       => 'default',
	)
);
?>

<?php Tool_View_Base::render_footer(); ?>
