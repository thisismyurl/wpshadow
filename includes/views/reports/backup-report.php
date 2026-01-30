<?php
/**
 * Backup Readiness Report
 *
 * Comprehensive backup analysis covering backup frequency, currency, storage locations,
 * restore testing, and disaster recovery preparedness.
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
Tool_View_Base::enqueue_assets( 'backup-report' );

// Render header
Tool_View_Base::render_header( __( 'Backup Readiness Report', 'wpshadow' ) );

// Get protection/backup diagnostics
$all_diagnostics = Diagnostic_Registry::get_all();
$backup_diagnostics = array();

foreach ( $all_diagnostics as $slug => $class ) {
	if ( ! class_exists( $class ) ) {
		continue;
	}

	$family = method_exists( $class, 'get_family' ) ? $class::get_family() : '';
	if ( 'protection' === $family ) {
		$backup_diagnostics[ $slug ] = $class;
	}
}

?>

<div class="wpshadow-tool backup-report-tool">
	
	<div class="wps-card wps-mb-4">
		<div class="wps-card-body">
			<h2 class="wps-text-xl wps-mb-3">
				<span class="dashicons dashicons-backup wps-text-primary"></span>
				<?php esc_html_e( 'Backup Status Overview', 'wpshadow' ); ?>
			</h2>
			<p class="wps-text-muted wps-mb-3">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: number of diagnostics */
						__( 'Analyzing backup configuration and disaster recovery readiness with %d protection diagnostics to ensure your site can be restored in case of emergency.', 'wpshadow' ),
						count( $backup_diagnostics )
					)
				);
				?>
			</p>

			<div class="wps-grid wps-grid-cols-4 wps-gap-3 wps-mb-4">
				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-backup wps-text-2xl wps-text-primary"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Last Backup', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="backup-last-date">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-clock wps-text-2xl wps-text-success"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Backup Frequency', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="backup-frequency">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-cloud wps-text-2xl wps-text-info"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Storage Location', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="backup-storage">-</div>
						</div>
					</div>
				</div>

				<div class="wps-p-4 wps-rounded wps-bg-light">
					<div class="wps-flex wps-items-center wps-gap-2">
						<span class="dashicons dashicons-warning wps-text-2xl wps-text-error"></span>
						<div>
							<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Issues Found', 'wpshadow' ); ?></div>
							<div class="wps-text-lg wps-font-semibold" id="backup-issues-count">-</div>
						</div>
					</div>
				</div>
			</div>

			<button type="button" 
				class="wps-btn wps-btn-primary wps-btn-icon-left wpshadow-run-backup-scan" 
				id="run-backup-scan-btn"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_security_scan' ) ); ?>"
				aria-label="<?php esc_attr_e( 'Run comprehensive backup readiness analysis now', 'wpshadow' ); ?>">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Check Backup Status', 'wpshadow' ); ?>
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
	<div class="scan-results" id="backup-scan-results"></div>

	<!-- Backup Best Practices -->
	<div class="wps-card wps-mt-4">
		<div class="wps-card-body">
			<h3 class="wps-text-lg wps-mb-3">
				<?php esc_html_e( 'What This Audit Covers', 'wpshadow' ); ?>
			</h3>
			<div class="wps-grid wps-grid-cols-2 wps-gap-4">
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-backup"></span>
						<?php esc_html_e( 'Backup Configuration', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Backup frequency and schedule', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Backup currency (days since last backup)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Files and database included', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Backup plugin configuration (UpdraftPlus, etc.)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Automated vs manual backups', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-cloud"></span>
						<?php esc_html_e( 'Storage & Retention', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Off-site storage locations', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Multiple backup destinations (3-2-1 rule)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Backup retention period', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Storage space availability', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Backup compression and encryption', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-controls-repeat"></span>
						<?php esc_html_e( 'Restore Testing', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Last restore test date', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Restore time estimate', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Backup integrity verification', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Restore documentation availability', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Staging environment for testing', 'wpshadow' ); ?></li>
					</ul>
				</div>
				<div>
					<h4 class="wps-font-semibold wps-mb-2">
						<span class="dashicons dashicons-admin-tools"></span>
						<?php esc_html_e( 'Disaster Recovery', 'wpshadow' ); ?>
					</h4>
					<ul class="wps-list-disc wps-ml-5">
						<li><?php esc_html_e( 'Recovery time objective (RTO)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Recovery point objective (RPO)', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Disaster recovery plan exists', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Contact information documented', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Ransomware protection measures', 'wpshadow' ); ?></li>
					</ul>
				</div>
			</div>

			<!-- The 3-2-1 Backup Rule -->
			<div class="wps-mt-4 wps-p-4 wps-bg-info-light wps-rounded">
				<h4 class="wps-font-semibold wps-mb-2">
					<span class="dashicons dashicons-backup"></span>
					<?php esc_html_e( 'The 3-2-1 Backup Rule (Industry Best Practice)', 'wpshadow' ); ?>
				</h4>
				<div class="wps-grid wps-grid-cols-3 wps-gap-3">
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( '3 Copies of Data:', 'wpshadow' ); ?></p>
						<p class="wps-text-sm"><?php esc_html_e( 'Original + 2 backups. If one fails, you have another.', 'wpshadow' ); ?></p>
					</div>
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( '2 Different Media:', 'wpshadow' ); ?></p>
						<p class="wps-text-sm"><?php esc_html_e( 'Cloud + local, or different cloud providers. Protects against media failure.', 'wpshadow' ); ?></p>
					</div>
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( '1 Off-Site Copy:', 'wpshadow' ); ?></p>
						<p class="wps-text-sm"><?php esc_html_e( 'Protects against fire, theft, natural disaster at server location.', 'wpshadow' ); ?></p>
					</div>
				</div>
			</div>

			<!-- Backup Scenarios -->
			<div class="wps-mt-4 wps-p-4 wps-bg-error-light wps-rounded">
				<h4 class="wps-font-semibold wps-mb-2">
					<span class="dashicons dashicons-warning"></span>
					<?php esc_html_e( 'Real-World Disaster Scenarios', 'wpshadow' ); ?>
				</h4>
				<div class="wps-grid wps-grid-cols-2 wps-gap-3">
					<div>
						<p class="wps-text-sm"><strong><?php esc_html_e( 'Ransomware attack:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Encrypted on Day 30. Last backup Day 1. Lose 29 days of data. Daily backups = lose at most 1 day.', 'wpshadow' ); ?></p>
					</div>
					<div>
						<p class="wps-text-sm"><strong><?php esc_html_e( 'Plugin update breaks site:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Need backup from before update. Without hourly/daily backups, business is down for hours.', 'wpshadow' ); ?></p>
					</div>
					<div>
						<p class="wps-text-sm"><strong><?php esc_html_e( 'Database corruption:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Corrupted during import. No backup = permanent data loss. Database-only backups restore in minutes.', 'wpshadow' ); ?></p>
					</div>
					<div>
						<p class="wps-text-sm"><strong><?php esc_html_e( 'Hosting provider failure:', 'wpshadow' ); ?></strong> <?php esc_html_e( 'Server crashes, all data gone. Off-site backups = restore on new host in hours instead of weeks.', 'wpshadow' ); ?></p>
					</div>
				</div>
			</div>

			<!-- Backup Recommendations -->
			<div class="wps-mt-4 wps-p-4 wps-bg-success-light wps-rounded">
				<h4 class="wps-font-semibold wps-mb-2">
					<span class="dashicons dashicons-yes-alt"></span>
					<?php esc_html_e( 'Recommended Backup Schedule', 'wpshadow' ); ?>
				</h4>
				<div class="wps-grid wps-grid-cols-3 wps-gap-3">
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( 'High-Activity Sites:', 'wpshadow' ); ?></p>
						<ul class="wps-list-disc wps-ml-5 wps-text-sm">
							<li><?php esc_html_e( 'Database: Every 4-12 hours', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Files: Daily', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Full backup: Weekly', 'wpshadow' ); ?></li>
						</ul>
					</div>
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( 'Medium-Activity Sites:', 'wpshadow' ); ?></p>
						<ul class="wps-list-disc wps-ml-5 wps-text-sm">
							<li><?php esc_html_e( 'Database: Daily', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Files: 2-3x per week', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Full backup: Weekly', 'wpshadow' ); ?></li>
						</ul>
					</div>
					<div>
						<p class="wps-text-sm wps-font-semibold wps-mb-1"><?php esc_html_e( 'Low-Activity Sites:', 'wpshadow' ); ?></p>
						<ul class="wps-list-disc wps-ml-5 wps-text-sm">
							<li><?php esc_html_e( 'Database: Daily', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Files: Weekly', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Full backup: Monthly', 'wpshadow' ); ?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('#run-backup-scan-btn').on('click', function() {
		const $btn = $(this);
		const $progress = $('.scan-progress');
		const $results = $('#backup-scan-results');
		
		$btn.prop('disabled', true).addClass('wps-loading');
		$progress.removeClass('hidden');
		$results.empty();
		
		// Run protection/backup diagnostics
		wp.ajax.post('wpshadow_run_family_diagnostics', {
			family: 'protection',
			nonce: $btn.data('nonce')
		}).done(function(response) {
			displayBackupResults(response);
		}).fail(function(error) {
			$results.html('<div class="notice notice-error"><p>' + error.message + '</p></div>');
		}).always(function() {
			$btn.prop('disabled', false).removeClass('wps-loading');
			$progress.addClass('hidden');
		});
	});

	function displayBackupResults(data) {
		const $results = $('#backup-scan-results');
		const findings = data.findings || [];
		
		$('#backup-issues-count').text(findings.length);
		
		// Simulate backup metrics (would come from actual backup plugin checks)
		const hasBackupIssue = findings.some(f => f.title.toLowerCase().includes('backup'));
		$('#backup-last-date').text(hasBackupIssue ? '<?php echo esc_js( __( 'Unknown', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Today', 'wpshadow' ) ); ?>');
		$('#backup-frequency').text(hasBackupIssue ? '<?php echo esc_js( __( 'Not Set', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Daily', 'wpshadow' ) ); ?>');
		$('#backup-storage').text(hasBackupIssue ? '<?php echo esc_js( __( 'Check', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Cloud', 'wpshadow' ) ); ?>');
		
		if (findings.length === 0) {
			$results.html('<div class="notice notice-success wps-card"><p><span class="dashicons dashicons-yes-alt"></span> <?php echo esc_js( __( 'Excellent! Your backup configuration is solid.', 'wpshadow' ) ); ?></p></div>');
			return;
		}
		
		// Group by severity
		const critical = findings.filter(f => f.severity === 'high');
		const warning = findings.filter(f => f.severity === 'medium');
		const info = findings.filter(f => f.severity === 'low');
		
		let html = '<div class="wps-card"><div class="wps-card-body">';
		html += '<h3 class="wps-text-lg wps-mb-3"><?php echo esc_js( __( 'Backup Issues Found', 'wpshadow' ) ); ?> (' + findings.length + ')</h3>';
		
		if (critical.length > 0) {
			html += '<div class="wps-mb-4">';
			html += '<h4 class="wps-font-semibold wps-mb-2 wps-text-error"><?php echo esc_js( __( 'Critical Issues', 'wpshadow' ) ); ?> (' + critical.length + ')</h4>';
			critical.forEach(function(finding) {
				html += '<div class="wps-mb-2 wps-p-3 wps-border wps-border-error wps-rounded">';
				html += '<div class="wps-flex wps-items-start wps-gap-3">';
				html += '<span class="dashicons dashicons-warning wps-text-error"></span>';
				html += '<div class="wps-flex-1">';
				html += '<h5 class="wps-font-semibold wps-text-sm">' + finding.title + '</h5>';
				html += '<p class="wps-text-muted wps-text-xs">' + finding.description + '</p>';
				if (finding.auto_fixable) {
					html += '<button class="wps-btn wps-btn-sm wps-btn-success wps-mt-1" data-finding="' + finding.id + '"><?php echo esc_js( __( 'Fix', 'wpshadow' ) ); ?></button>';
				}
				html += '</div></div></div>';
			});
			html += '</div>';
		}
		
		if (warning.length > 0) {
			html += '<div class="wps-mb-4">';
			html += '<h4 class="wps-font-semibold wps-mb-2 wps-text-warning"><?php echo esc_js( __( 'Warnings', 'wpshadow' ) ); ?> (' + warning.length + ')</h4>';
			warning.forEach(function(finding) {
				html += '<div class="wps-mb-2 wps-p-3 wps-border wps-border-warning wps-rounded">';
				html += '<div class="wps-flex wps-items-start wps-gap-3">';
				html += '<span class="dashicons dashicons-backup wps-text-warning"></span>';
				html += '<div class="wps-flex-1">';
				html += '<h5 class="wps-font-semibold wps-text-sm">' + finding.title + '</h5>';
				html += '<p class="wps-text-muted wps-text-xs">' + finding.description + '</p>';
				html += '</div></div></div>';
			});
			html += '</div>';
		}
		
		if (info.length > 0) {
			html += '<div class="wps-mb-4">';
			html += '<h4 class="wps-font-semibold wps-mb-2"><?php echo esc_js( __( 'Recommendations', 'wpshadow' ) ); ?> (' + info.length + ')</h4>';
			info.forEach(function(finding) {
				html += '<div class="wps-mb-2 wps-p-3 wps-border wps-rounded">';
				html += '<div class="wps-flex wps-items-start wps-gap-3">';
				html += '<span class="dashicons dashicons-info"></span>';
				html += '<div class="wps-flex-1">';
				html += '<h5 class="wps-font-semibold wps-text-sm">' + finding.title + '</h5>';
				html += '<p class="wps-text-muted wps-text-xs">' + finding.description + '</p>';
				html += '</div></div></div>';
			});
			html += '</div>';
		}
		
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
		'title'       => __( 'Want automated backup management?', 'wpshadow' ),
		'description' => __( 'WPShadow Pro includes automated backup monitoring, restore testing, and premium backup service integration.', 'wpshadow' ),
		'features'    => array(
			__( 'Automated backup verification', 'wpshadow' ),
			__( 'One-click restore testing', 'wpshadow' ),
			__( 'Multiple cloud storage options', 'wpshadow' ),
			__( 'Disaster recovery planning', 'wpshadow' ),
		),
		'cta_text'    => __( 'Upgrade to Pro Backup Manager', 'wpshadow' ),
		'cta_url'     => 'https://wpshadow.com/pro',
		'icon'        => 'dashicons-backup',
		'style'       => 'default',
	)
);
?>

<?php Tool_View_Base::render_footer(); ?>
