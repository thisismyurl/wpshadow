<?php
/**
 * Email Deliverability Report View
 *
 * @package WPShadow
 * @subpackage Reports
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Diagnostics\Diagnostic_Registry;

$all_diagnostics = Diagnostic_Registry::get_all();
$email_diagnostics = array();

foreach ( $all_diagnostics as $slug => $class ) {
	if ( ! class_exists( $class ) ) {
		continue;
	}

	$family = method_exists( $class, 'get_family' ) ? $class::get_family() : '';
	if ( 'email' !== $family ) {
		continue;
	}

	$email_diagnostics[ $slug ] = $class;
}

$past_reports = array();
$upload_dir   = wp_upload_dir();
$reports_dir  = trailingslashit( $upload_dir['basedir'] ) . 'wpshadow-reports/';

if ( is_dir( $reports_dir ) ) {
	$report_files = glob( $reports_dir . 'email-report-*' );
	if ( $report_files ) {
		usort(
			$report_files,
			function ( $a, $b ) {
				return filemtime( $b ) <=> filemtime( $a );
			}
		);

		foreach ( $report_files as $file ) {
			if ( ! is_file( $file ) ) {
				continue;
			}

			$filename = basename( $file );
			$past_reports[] = array(
				'name' => $filename,
				'url'  => str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $file ),
				'time' => filemtime( $file ),
				'type' => pathinfo( $filename, PATHINFO_EXTENSION ),
			);
		}
	}
}

$last_report_time = 0;
if ( ! empty( $past_reports ) ) {
	$last_report_time = (int) ( $past_reports[0]['time'] ?? 0 );
}

$admin_email = get_option( 'admin_email', '' );
$site_domain = wp_parse_url( get_site_url(), PHP_URL_HOST );
$email_diagnostic_items = array();

foreach ( $email_diagnostics as $slug => $class ) {
	$title = $slug;
	if ( method_exists( $class, 'get_title' ) ) {
		$title = $class::get_title();
	}

	$email_diagnostic_items[] = array(
		'slug'  => $slug,
		'title' => $title,
	);
}

$past_reports_per_page = 10;
$past_reports_total    = count( $past_reports );
$past_reports_pages    = max( 1, (int) ceil( $past_reports_total / $past_reports_per_page ) );
$past_reports_page     = isset( $_GET['email_past_page'] ) ? absint( $_GET['email_past_page'] ) : 1;
$past_reports_page     = max( 1, min( $past_reports_page, $past_reports_pages ) );
$past_reports_offset   = ( $past_reports_page - 1 ) * $past_reports_per_page;
$past_reports          = array_slice( $past_reports, $past_reports_offset, $past_reports_per_page );

$past_reports_items = array();
foreach ( $past_reports as $report ) {
	$past_reports_items[] = array(
		'title' => $report['name'] ?? '',
		'url'   => $report['url'] ?? '',
		'time'  => $report['time'] ?? 0,
		'type'  => $report['type'] ?? '',
	);
}

// Calculate email health score based on cached findings.
$findings = function_exists( 'wpshadow_get_cached_findings' )
	? wpshadow_get_cached_findings()
	: get_option( 'wpshadow_site_findings', array() );
if ( ! is_array( $findings ) ) {
	$findings = array();
}

$email_findings = array_filter(
	$findings,
	function ( $finding ) use ( $email_diagnostics ) {
		$finding_id = is_array( $finding ) && isset( $finding['id'] ) ? $finding['id'] : '';
		return array_key_exists( $finding_id, $email_diagnostics );
	}
);

$email_issue_count = count( $email_findings );
$total_email_checks = count( $email_diagnostics );
$email_score = $total_email_checks > 0
	? (int) round( ( ( $total_email_checks - $email_issue_count ) / $total_email_checks ) * 100 )
	: 100;
$email_score = max( 0, min( 100, $email_score ) );

if ( $email_score >= 80 ) {
	$email_label = __( 'Excellent', 'wpshadow' );
	$email_message = __( 'Your email configuration looks great. Messages should deliver reliably.', 'wpshadow' );
	$email_color = '#10b981';
} elseif ( $email_score >= 60 ) {
	$email_label = __( 'Good', 'wpshadow' );
	$email_message = __( 'Email delivery is working, but a few improvements would help.', 'wpshadow' );
	$email_color = '#f59e0b';
} else {
	$email_label = __( 'Needs attention', 'wpshadow' );
	$email_message = __( 'Several email configuration issues need fixing to ensure delivery.', 'wpshadow' );
	$email_color = '#ef4444';
}

$last_report_label = $last_report_time
	? date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_report_time )
	: __( 'Not run yet', 'wpshadow' );

require_once WPSHADOW_PATH . 'includes/views/reports/partials/past-reports.php';
?>

<div class="wrap wps-page-container">
	<?php
	wpshadow_render_page_header(
		__( 'Email Deliverability Report', 'wpshadow' ),
		__( 'Check if your emails are actually reaching people and not ending up in spam.', 'wpshadow' ),
		'dashicons-email-alt'
	);
	?>

	<style>
		.wps-email-report-columns {
			display: flex;
			gap: var(--wps-space-4);
			align-items: stretch;
			flex-wrap: wrap;
		}

		.wps-email-report-main {
			flex: 2 1 520px;
		}

		.wps-email-report-side {
			flex: 1 1 320px;
		}

		.wps-email-report-covers {
			display: grid;
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: var(--wps-space-4);
		}

		@media (max-width: 900px) {
			.wps-email-report-covers {
				grid-template-columns: 1fr;
			}
		}

		.wps-report-progress {
			border: 1px solid #e5e7eb;
			border-radius: 12px;
			padding: 16px;
			background: #f8fafc;
		}

		.wps-report-progress.hidden {
			display: none;
		}

		.wps-report-progress-bar {
			height: 10px;
			background: #e5e7eb;
			border-radius: 999px;
			overflow: hidden;
		}

		.wps-report-progress-fill {
			height: 100%;
			width: 0;
			background: #3b82f6;
			transition: width 0.4s ease;
		}

		.wps-report-progress-list {
			list-style: none;
			padding: 0;
			margin: 12px 0 0;
		}

		.wps-report-progress-list li {
			display: flex;
			gap: 8px;
			align-items: center;
			font-size: 13px;
			color: #4b5563;
			margin-bottom: 6px;
		}

		.wps-report-progress-status {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 18px;
			height: 18px;
			border-radius: 999px;
			font-size: 11px;
			background: #e5e7eb;
			color: #6b7280;
		}

		.wps-report-progress-status.is-running {
			background: #dbeafe;
			color: #1d4ed8;
		}

		.wps-report-progress-status.is-done {
			background: #dcfce7;
			color: #15803d;
		}

		.wps-report-progress-status.is-warning {
			background: #fef3c7;
			color: #b45309;
		}

		.wps-report-progress-status.is-error {
			background: #fee2e2;
			color: #b91c1c;
		}
	</style>

	<div class="wps-email-report-columns wps-mb-4">
		<div class="wps-email-report-main">
			<div class="wps-card">
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

					<div class="wps-grid wps-grid-cols-2 wps-gap-3 wps-mb-4">
						<div class="wps-card">
							<div class="wps-card-body">
								<div class="wps-flex wps-items-center wps-gap-2">
									<span class="dashicons dashicons-email-alt wps-text-2xl wps-text-primary"></span>
									<div>
										<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Domain Status', 'wpshadow' ); ?></div>
										<div class="wps-text-lg wps-font-semibold" id="email-domain-status">-</div>
									</div>
								</div>
							</div>
						</div>

						<div class="wps-card">
							<div class="wps-card-body">
								<div class="wps-flex wps-items-center wps-gap-2">
									<span class="dashicons dashicons-admin-settings wps-text-2xl wps-text-success"></span>
									<div>
										<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'SMTP Config', 'wpshadow' ); ?></div>
										<div class="wps-text-lg wps-font-semibold" id="email-smtp-status">-</div>
									</div>
								</div>
							</div>
						</div>

						<div class="wps-card">
							<div class="wps-card-body">
								<div class="wps-flex wps-items-center wps-gap-2">
									<span class="dashicons dashicons-shield wps-text-2xl wps-text-info"></span>
									<div>
										<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Blacklist Status', 'wpshadow' ); ?></div>
										<div class="wps-text-lg wps-font-semibold" id="email-blacklist-status">-</div>
									</div>
								</div>
							</div>
						</div>

						<div class="wps-card">
							<div class="wps-card-body">
								<div class="wps-flex wps-items-center wps-gap-2">
									<span class="dashicons dashicons-warning wps-text-2xl wps-text-error"></span>
									<div>
										<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Issues Found', 'wpshadow' ); ?></div>
										<div class="wps-text-lg wps-font-semibold" id="email-issues-count">-</div>
									</div>
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
						<?php esc_html_e( 'Run Tests', 'wpshadow' ); ?>
					</button>

					<div class="scan-progress hidden wps-card wps-mt-4" role="status" aria-live="polite">
						<div class="wps-card-body">
							<h3 class="wps-text-lg wps-mb-2">
								<?php esc_html_e( 'Email Check Progress', 'wpshadow' ); ?>
							</h3>
							<p class="wps-text-sm wps-text-muted wps-mb-3" id="email-progress-message">
								<?php esc_html_e( 'Running email diagnostics...', 'wpshadow' ); ?>
							</p>
							<div class="wps-report-progress">
								<div class="wps-report-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
									<div class="wps-report-progress-fill" id="email-progress-fill"></div>
								</div>
								<ul class="wps-report-progress-list" id="email-progress-steps">
									<?php
									$step_num = 1;
									foreach ( $email_diagnostic_items as $diagnostic ) :
										?>
										<li data-step="<?php echo esc_attr( $diagnostic['slug'] ); ?>">
											<span class="wps-report-progress-status"><?php echo esc_html( $step_num ); ?></span>
											<?php echo esc_html( $diagnostic['title'] ); ?>
										</li>
										<?php
										++$step_num;
									endforeach;
									?>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>

		<div class="wps-email-report-side">
			<div class="wps-card">
				<div class="wps-card-body">
					<h3 class="wps-text-lg wps-mb-2">
						<?php esc_html_e( 'Email Health Score', 'wpshadow' ); ?>
					</h3>
					<p class="wps-text-sm wps-text-muted wps-mb-3">
						<?php esc_html_e( 'This gauge summarizes email deliverability for your site.', 'wpshadow' ); ?>
					</p>
					<svg width="200" height="200" viewBox="0 0 200 200" class="wps-health-gauge-svg" aria-labelledby="email-score-title" role="img">
						<title id="email-score-title">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %d: email health score percentage */
									__( 'Email health score: %d%%', 'wpshadow' ),
									$email_score
								)
							);
							?>
						</title>
						<!-- Outer decorative circle -->
						<circle cx="100" cy="100" r="95" fill="none" stroke="<?php echo esc_attr( $email_color ); ?>" stroke-width="2" opacity="0.2" />
						<!-- Gauge background -->
						<circle cx="100" cy="100" r="85" fill="none" stroke="#e0e0e0" stroke-width="16" />
						<!-- Gauge progress -->
						<circle cx="100" cy="100" r="85" fill="none" stroke="<?php echo esc_attr( $email_color ); ?>" stroke-width="16"
							stroke-dasharray="<?php echo (int) ( $email_score / 100 * 534 ); ?> 534"
							stroke-linecap="round" transform="rotate(-90 100 100)"
							class="wps-gauge-progress" />
						<!-- Center text -->
					<text x="100" y="110" text-anchor="middle" font-size="48" font-weight="bold" fill="<?php echo esc_attr( $email_color ); ?>"><?php echo esc_html( (int) $email_score ); ?>%</text>
					<text x="100" y="135" text-anchor="middle" font-size="16" fill="#666"><?php echo esc_html( $email_label ); ?></text>
					</svg>
					<p class="wps-text-xs wps-text-muted wps-mt-2">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: date/time of last report run */
								__( 'Last run: %s', 'wpshadow' ),
								$last_report_label
							)
						);
						?>
					</p>
					<p class="wps-health-gauge-message wps-mt-4"><?php echo esc_html( $email_message ); ?></p>
				</div>
			</div>

			<div class="wps-card">
				<div class="wps-card-body">
					<h3 class="wps-text-lg wps-mb-3">
						<?php esc_html_e( 'What This Analysis Covers', 'wpshadow' ); ?>
					</h3>
					<div class="wps-grid wps-grid-cols-2 wps-gap-4 wps-email-report-covers">
						<div>
							<h4 class="wps-font-semibold wps-mb-2">
								<span class="dashicons dashicons-admin-settings"></span>
								<?php esc_html_e( 'Configuration', 'wpshadow' ); ?>
							</h4>
							<ul class="wps-list-disc wps-ml-5">
								<li><?php esc_html_e( 'SMTP server settings', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'From address configuration', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Return-path configuration', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Email domain vs website domain match', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Default sender name clarity', 'wpshadow' ); ?></li>
							</ul>
						</div>
						<div>
							<h4 class="wps-font-semibold wps-mb-2">
								<span class="dashicons dashicons-shield"></span>
								<?php esc_html_e( 'Domain Authentication', 'wpshadow' ); ?>
							</h4>
							<ul class="wps-list-disc wps-ml-5">
								<li><?php esc_html_e( 'SPF record checks', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'DKIM signing status', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'DMARC policy alignment', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Subdomain alignment', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Authentication visibility', 'wpshadow' ); ?></li>
							</ul>
						</div>
						<div>
							<h4 class="wps-font-semibold wps-mb-2">
								<span class="dashicons dashicons-visibility"></span>
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
								<?php esc_html_e( 'Headers & Content', 'wpshadow' ); ?>
							</h4>
							<ul class="wps-list-disc wps-ml-5">
								<li><?php esc_html_e( 'Email header validation', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Content-Type configuration', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Character encoding (UTF-8)', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Proper MIME formatting', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Unsubscribe header presence', 'wpshadow' ); ?></li>
							</ul>
						</div>
					</div>
				</div>
			</div>

			<?php
			if ( function_exists( 'wpshadow_render_past_reports_card' ) ) {
				wpshadow_render_past_reports_card(
					array(
						'title'         => __( 'Past Reports', 'wpshadow' ),
						'description'   => __( 'Download previous email deliverability exports for reference or sharing.', 'wpshadow' ),
						'empty_message' => __( 'No past email reports yet. Run a report and export it to see it listed here.', 'wpshadow' ),
						'items'         => $past_reports_items,
						'pagination'    => array(
							'current'  => $past_reports_page,
							'total'    => $past_reports_pages,
							'param'    => 'email_past_page',
							'base_url' => add_query_arg(
								array(
									'page'   => 'wpshadow-reports',
									'report' => 'email-report',
								),
								admin_url( 'admin.php' )
							),
						),
						'delete_action' => array(
						'action_url'   => admin_url( 'admin-post.php' ),
						'nonce_action' => 'wpshadow_delete_email_reports',
						'nonce_name'   => 'wpshadow_delete_email_reports_nonce',
						'fields'       => array(
							'action' => 'wpshadow_delete_email_reports',
							),
							'label'        => __( 'Delete All Reports', 'wpshadow' ),
							'confirm'      => __( 'Delete all past email reports? This cannot be undone.', 'wpshadow' ),
						),
					)
				);
			}
			?>




		</div>
	</div>

	<div class="scan-results" id="email-scan-results"></div>

	<?php
	if ( function_exists( 'wpshadow_render_page_activities' ) ) {
		wpshadow_render_page_activities( 'reports', 10, 'email-report' );
	}
	?>
</div>

<script>
jQuery(document).ready(function($) {
	$('#run-email-scan-btn').on('click', function() {
		const $btn = $(this);
		const $progress = $('.scan-progress');
		const $progressFill = $('#email-progress-fill');
		const $progressBar = $progressFill.parent();
		const $progressMessage = $('#email-progress-message');
		const $progressSteps = $('#email-progress-steps');
		const $results = $('#email-scan-results');
		const diagnostics = <?php echo wp_json_encode( $email_diagnostic_items ); ?>;
		const totalDiagnostics = diagnostics.length || 1;
		let currentStepIndex = 0;
		let progressValue = 5;

		const markStep = (index, status) => {
			const $steps = $progressSteps.find('li');
			if (!$steps[index]) {
				return;
			}
			const $statusEl = $($steps[index]).find('.wps-report-progress-status');
			if (!$statusEl.length) {
				return;
			}
			$statusEl.removeClass('is-running is-done');
			if (status === 'running') {
				$statusEl.addClass('is-running');
			} else if (status === 'done') {
				$statusEl.addClass('is-done');
				$statusEl.text('✓');
			}
		};

		const updateProgress = (value) => {
			const clamped = Math.max(0, Math.min(100, value));
			$progressFill.css('width', clamped + '%');
			$progressBar.attr('aria-valuenow', clamped);
		};

		// Initialize
		$btn.prop('disabled', true);
		$progress.removeClass('hidden');
		$results.html('');
		
		// Start animation
		markStep(0, 'running');
		updateProgress(progressValue);
		$progressMessage.text('<?php echo esc_js( __( 'Running email diagnostics...', 'wpshadow' ) ); ?>');

		const stepInterval = window.setInterval(() => {
			markStep(currentStepIndex, 'done');
			currentStepIndex = Math.min(totalDiagnostics - 1, currentStepIndex + 1);
			markStep(currentStepIndex, 'running');
			progressValue = Math.min(95, progressValue + (90 / totalDiagnostics));
			updateProgress(progressValue);
			
			if (currentStepIndex >= totalDiagnostics - 1) {
				window.clearInterval(stepInterval);
			}
		}, 600);

		// Run actual scan
		wp.ajax.post('wpshadow_run_family_diagnostics', {
			family: 'email',
			nonce: $btn.data('nonce')
		}).done(function(response) {
			// Mark all steps as complete
			for (let i = 0; i < totalDiagnostics; i++) {
				markStep(i, 'done');
			}
			updateProgress(100);
			$progressMessage.text('<?php echo esc_js( __( 'Email check complete!', 'wpshadow' ) ); ?>');
			
			// Display results
			displayEmailResults(response, diagnostics);
		}).fail(function(error) {
			$results.html('<div class="notice notice-error"><p>' + (error.message || '<?php echo esc_js( __( 'Scan failed. Please try again.', 'wpshadow' ) ); ?>') + '</p></div>');
			updateProgress(100);
			$progressMessage.text('<?php echo esc_js( __( 'Scan failed.', 'wpshadow' ) ); ?>');
		}).always(function() {
			window.clearInterval(stepInterval);
			$btn.prop('disabled', false);
		});
	});

	function displayEmailResults(data, diagnostics) {
		const $results = $('#email-scan-results');
		const findings = Array.isArray(data.findings) ? data.findings : [];
		const checks = Array.isArray(diagnostics) ? diagnostics : [];
		const totalChecks = Number(data.total_diagnostics || checks.length || 0);
		const issuesCount = findings.length;
		const okLabel = '<?php echo esc_js( __( 'OK', 'wpshadow' ) ); ?>';
		const notAvailableLabel = '<?php echo esc_js( __( 'Not available', 'wpshadow' ) ); ?>';

		$('#email-issues-count').text(issuesCount);

		const matchesFinding = (finding, slug, title) => {
			const id = (finding.id || '').toString();
			if (id && slug && 0 === id.indexOf(slug)) {
				return true;
			}
			if (title && finding.title) {
				return finding.title.toLowerCase() === title.toLowerCase();
			}
			return false;
		};

		const hasDomainIssue = findings.some(finding => {
			const title = (finding.title || '').toLowerCase();
			return title.includes('domain') || title.includes('dmarc') || title.includes('spf') || title.includes('dkim') || title.includes('mx');
		});
		const hasSmtpIssue = findings.some(finding => {
			const title = (finding.title || '').toLowerCase();
			return title.includes('smtp') || title.includes('mail');
		});
		const hasBlacklistIssue = findings.some(finding => {
			const title = (finding.title || '').toLowerCase();
			return title.includes('blacklist') || title.includes('reputation') || title.includes('spam');
		});

		const domainStatus = totalChecks === 0 ? notAvailableLabel : (hasDomainIssue ? '<?php echo esc_js( __( 'Issues', 'wpshadow' ) ); ?>' : okLabel);
		const smtpStatus = totalChecks === 0 ? notAvailableLabel : (hasSmtpIssue ? '<?php echo esc_js( __( 'Check', 'wpshadow' ) ); ?>' : okLabel);
		const blacklistStatus = totalChecks === 0 ? notAvailableLabel : (hasBlacklistIssue ? '<?php echo esc_js( __( 'Listed', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Clear', 'wpshadow' ) ); ?>');

		$('#email-domain-status').text(domainStatus);
		$('#email-smtp-status').text(smtpStatus);
		$('#email-blacklist-status').text(blacklistStatus);

		let html = '<div class="wps-card"><div class="wps-card-body">';
		html += '<h3 class="wps-text-lg wps-mb-3"><?php echo esc_js( __( 'Email Check Results', 'wpshadow' ) ); ?></h3>';
		html += '<ul class="wps-report-progress-list">';
		checks.forEach(function(check, index) {
			const hasIssue = findings.some(function(finding) {
				return matchesFinding(finding, check.slug, check.title);
			});
			const statusClass = hasIssue ? 'is-warning' : 'is-done';
			const statusText = hasIssue ? '<?php echo esc_js( __( 'Needs review', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Looks good', 'wpshadow' ) ); ?>';
			const statusIcon = hasIssue ? '!' : '✓';
			html += '<li>';
			html += '<span class="wps-report-progress-status ' + statusClass + '">' + statusIcon + '</span>';
			html += '<span>' + (check.title || check.slug || ('<?php echo esc_js( __( 'Email Check', 'wpshadow' ) ); ?> ' + (index + 1))) + '</span>';
			html += '<span class="wps-text-xs wps-text-muted">' + statusText + '</span>';
			html += '</li>';
		});
		html += '</ul>';

		if (findings.length === 0) {
			html += '<div class="notice notice-success wps-mt-4"><p><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_js( __( 'Excellent! Your email configuration is optimized for delivery.', 'wpshadow' ) ); ?></p></div>';
			html += '</div></div>';
			$results.html(html);
			return;
		}
		
		const byCategory = {
			'Configuration': [],
			'Domain & DNS': [],
			'Reputation': [],
			'Other': []
		};
		
		findings.forEach(function(finding) {
			const title = (finding.title || '').toLowerCase();
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
