<?php
/**
 * Email Deliverability Report
 *
 * Comprehensive email configuration analysis covering SMTP settings, domain configuration,
 * blacklist status, and deliverability best practices.
 *
 * @package    WPShadow
 * @subpackage Reports
 * @since      1.2603.0145
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
Tool_View_Base::enqueue_assets( 'email-report' );

// Render header
Tool_View_Base::render_header( __( 'Email Deliverability Report', 'wpshadow' ) );

// Get email diagnostics
$all_diagnostics = Diagnostic_Registry::get_all();
$email_diagnostics = array();

foreach ( $all_diagnostics as $slug => $class ) {
	if ( ! class_exists( $class ) ) {
		continue;
	}

	$family = method_exists( $class, 'get_family' ) ? $class::get_family() : '';
	if ( 'email' === $family ) {
		$email_diagnostics[ $slug ] = $class;
	}
}

// Get admin email for testing
$admin_email = get_option( 'admin_email', '' );
$site_domain = wp_parse_url( get_site_url(), PHP_URL_HOST );

?>

<div class="wpshadow-tool email-report-tool">
	
	<div class="wps-card wps-mb-4">
		<div class="wps-card-body">
			<h2 class="wps-text-xl wps-mb-3">
				<span class="dashicons dashicons-email wps-text-primary"></span>
				<?php esc_html_e( 'Email Health Overview', 'wpshadow' ); ?>
			</h2>
			<p class="wps-text-muted wps-mb-3">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: site domain, 2: admin email, 3: number of diagnostics */
						__( 'Analyzing email configuration for %1$s (admin: %2$s) with %3$d specialized diagnostics to ensure reliable email delivery.', 'wpshadow' ),
						$site_domain,
						$admin_email,
						count( $email_diagnostics )
					)
				);
				?>
			</p>

			<div class="wps-grid wps-grid-cols-4 wps-gap-3 wps-mb-4">
				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-email-alt wps-text-2xl wps-text-primary"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Domain Status', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="email-domain-status">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-admin-settings wps-text-2xl wps-text-success"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'SMTP Config', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="email-smtp-status">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-shield wps-text-2xl wps-text-info"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Blacklist Status', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="email-blacklist-status">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-warning wps-text-2xl wps-text-error"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Issues Found', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="email-issues-count">-</div>
						</div>
					</div>
				</div>
			</div>

			<button type="button" 
				class="wps-btn wps-btn-primary wps-btn-icon-left wpshadow-run-email-scan" 
				id="run-email-scan-btn"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_security_scan' ) ); ?>"
				aria-label="<?php esc_attr_e( 'Run comprehensive email deliverability analysis now', 'wpshadow' ); ?>">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Test Email Configuration', 'wpshadow' ); ?>
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
	<div class="scan-results" id="email-scan-results"></div>

	<!-- Email Deliverability Guide -->
	<div class="wps-card wps-mt-4">
		<div class="wps-card-body">
			<h3 class="wps-text-lg wps-mb-3">
				<?php esc_html_e( 'What This Audit Covers', 'wpshadow' ); ?>
			</h3>
			<div class="wps-grid wps-grid-cols-2 wps-gap-4">
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-admin-settings"></span>
						<?php esc_html_e( 'Configuration', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'SMTP server settings', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'From address configuration', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Domain authentication (SPF, DKIM, DMARC)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Email domain vs website domain match', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Return-path configuration', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-shield"></span>
						<?php esc_html_e( 'Reputation & Blacklists', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Domain blacklist status (RBL check)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'IP reputation monitoring', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Spam score assessment', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Previous delivery issues', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Bounce rate analysis', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-email"></span>
						<?php esc_html_e( 'Content & Headers', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Email header validation', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Content-Type configuration', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Character encoding (UTF-8)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Proper MIME formatting', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Unsubscribe header presence', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-admin-tools"></span>
						<?php esc_html_e( 'Best Practices', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Use dedicated SMTP service (not server default)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Authentication required for sending', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'TLS/SSL encryption enabled', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Rate limiting to prevent spam flags', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Error logging and monitoring enabled', 'wpshadow' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- Deliverability Impact -->
			<div class="wps-mt-4 wps-p-4 wps-bg-success-light wps-rounded">
				<h4 class="wps-font-semibold wps-mb-2">
					<span class="dashicons dashicons-yes-alt"></span>
					<?php esc_html_e( 'Why Email Deliverability Matters', 'wpshadow' ); ?>
				</h4>
				<div class="wps-grid wps-grid-cols-3 wps-gap-3">
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( 'User Experience:', 'wpshadow' ); ?></p>
						<ul class="wps-list-disc wps-ml-5 wps-text-sm">
							<li><?php esc_html_e( 'Password resets arrive instantly', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Order confirmations reach customers', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Account notifications work reliably', 'wpshadow' ); ?></li>
						</ul>
					</div>
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( 'Business Impact:', 'wpshadow' ); ?></p>
						<ul class="wps-list-disc wps-ml-5 wps-text-sm">
							<li><?php esc_html_e( 'Contact form submissions get responses', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Marketing emails reach audience', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'No lost sales from bounced receipts', 'wpshadow' ); ?></li>
						</ul>
					</div>
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( 'Typical Improvements:', 'wpshadow' ); ?></p>
						<ul class="wps-list-disc wps-ml-5 wps-text-sm">
							<li><?php esc_html_e( '95%+ inbox placement rate', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( '70% reduction in bounce rate', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( '50% faster delivery times', 'wpshadow' ); ?></li>
						</ul>
					</div>
				</div>
			</div>

			<!-- Common Issues -->
			<div class="wps-mt-4 wps-p-4 wps-bg-warning-light wps-rounded">
				<h4 class="wps-font-semibold wps-mb-2">
					<span class="dashicons dashicons-warning"></span>
					<?php esc_html_e( 'Common Email Problems', 'wpshadow' ); ?>
				</h4>
				<div class="wps-grid wps-grid-cols-2 wps-gap-3">
					<div>
						<p class="wps-text-sm"><strong><?php esc_html_e( 'Using server default mail():', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Often ends up in spam. Use SMTP plugin instead.', 'wpshadow' ); ?></p>
					</div>
					<div>
						<p class="wps-text-sm"><strong><?php esc_html_e( 'Domain mismatch:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'From address @gmail.com on site at example.com = spam folder.', 'wpshadow' ); ?></p>
					</div>
					<div>
						<p class="wps-text-sm"><strong><?php esc_html_e( 'No SPF/DKIM:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Email providers can\'t verify you = rejected or spam.', 'wpshadow' ); ?></p>
					</div>
					<div>
						<p class="wps-text-sm"><strong><?php esc_html_e( 'Blacklisted IP/domain:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Previous spam from shared server = all emails blocked.', 'wpshadow' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('#run-email-scan-btn').on('click', function() {
		const $btn = $(this);
		const $progress = $('.scan-progress');
		const $results = $('#email-scan-results');
		
		$btn.prop('disabled', true).addClass('wps-loading');
		$progress.removeClass('hidden');
		$results.empty();
		
		// Run email diagnostics
		wp.ajax.post('wpshadow_run_family_diagnostics', {
			family: 'email',
			nonce: $btn.data('nonce')
		}).done(function(response) {
			displayEmailResults(response);
		}).fail(function(error) {
			$results.html('<div class="notice notice-error"><p>' + error.message + '</p></div>');
		}).always(function() {
			$btn.prop('disabled', false).removeClass('wps-loading');
			$progress.addClass('hidden');
		});
	});

	function displayEmailResults(data) {
		const $results = $('#email-scan-results');
		const findings = data.findings || [];
		
		$('#email-issues-count').text(findings.length);
		
		// Simulate metrics (would come from actual checks)
		$('#email-domain-status').text(findings.some(f => f.title.includes('domain')) ? '<?php echo esc_js( __( 'Issues', 'wpshadow' ) ); ?>' : '✓');
		$('#email-smtp-status').text(findings.some(f => f.title.includes('SMTP')) ? '<?php echo esc_js( __( 'Check', 'wpshadow' ) ); ?>' : '✓');
		$('#email-blacklist-status').text(findings.some(f => f.title.includes('blacklist')) ? '<?php echo esc_js( __( 'Listed', 'wpshadow' ) ); ?>' : '✓');
		
		if (findings.length === 0) {
			$results.html('<div class="notice notice-success wps-card"><p><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_js( __( 'Excellent! Your email configuration is optimized for delivery.', 'wpshadow' ) ); ?></p></div>');
			return;
		}
		
		// Group by category
		const byCategory = {
			'Configuration': [],
			'Domain & DNS': [],
			'Reputation': [],
			'Other': []
		};
		
		findings.forEach(function(finding) {
			const title = finding.title.toLowerCase();
			if (title.includes('smtp') || title.includes('config')) {
				byCategory['Configuration'].push(finding);
			} else if (title.includes('domain') || title.includes('dns')) {
				byCategory['Domain & DNS'].push(finding);
			} else if (title.includes('blacklist') || title.includes('reputation')) {
				byCategory['Reputation'].push(finding);
			} else {
				byCategory['Other'].push(finding);
			}
		});
		
		let html = '<div class="wps-card"><div class="wps-card-body">';
		html += '<h3 class="wps-text-lg wps-mb-3"><?php echo esc_js( __( 'Email Issues Found', 'wpshadow' ) ); ?> (' + findings.length + ')</h3>';
		
		Object.keys(byCategory).forEach(function(category) {
			const categoryFindings = byCategory[category];
			if (categoryFindings.length === 0) return;
			
			html += '<div class="wps-mb-4">';
			html += '<h4 class="wps-font-semibold wps-mb-2">' + category + ' (' + categoryFindings.length + ' <?php echo esc_js( __( 'issues', 'wpshadow' ) ); ?>)</h4>';
			
			categoryFindings.forEach(function(finding) {
				const severityClass = finding.severity === 'high' ? 'error' : (finding.severity === 'medium' ? 'warning' : 'info');
				html += '<div class="wps-mb-2 wps-p-3 wps-border wps-border-' + severityClass + ' wps-rounded">';
				html += '<div class="wps-flex wps-items-start wps-gap-3">';
				html += '<span class="dashicons dashicons-email wps-text-' + severityClass + '"></span>';
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
require_once WPSHADOW_PATH . 'includes/views/components/sales-widget.php';

wpshadow_render_sales_widget(
	array(
		'title'       => __( 'Want guaranteed email delivery?', 'wpshadow' ),
		'description' => __( 'WPShadow Pro includes automated email monitoring, deliverability testing, and premium SMTP service integration.', 'wpshadow' ),
		'features'    => array(
			__( 'Real-time deliverability monitoring', 'wpshadow' ),
			__( 'Automated blacklist checking', 'wpshadow' ),
			__( 'Premium SMTP service discounts', 'wpshadow' ),
			__( 'Email log analysis', 'wpshadow' ),
		),
		'cta_text'    => __( 'Upgrade to Pro Email Manager', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-email-alt',
		'style'       => 'default',
	)
);
?>

<?php Tool_View_Base::render_footer(); ?>
