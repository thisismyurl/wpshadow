<?php
/**
 * Diagnostics Fix Rate Report View
 *
 * @package WPShadow
 * @subpackage Reports
 */

declare(strict_types=1);

use WPShadow\Core\Activity_Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$diagnostics_run = 0;
$auto_fixes      = 0;
$user_fixes      = 0;

if ( class_exists( 'WPShadow\\Core\\Activity_Logger' ) ) {
	$action_counts  = Activity_Logger::get_action_counts();
	$diagnostics_run = (int) ( $action_counts['diagnostic_run'] ?? 0 );
	$auto_fixes      = (int) ( $action_counts['treatment_applied'] ?? 0 );
	$user_fixes      = (int) ( $action_counts['finding_resolved'] ?? 0 ) + (int) ( $action_counts['finding_fixed'] ?? 0 );
}

$total_fixes   = $auto_fixes + $user_fixes;
$auto_percent  = $total_fixes > 0 ? round( ( $auto_fixes / $total_fixes ) * 100 ) : 0;
$user_percent  = $total_fixes > 0 ? 100 - $auto_percent : 0;
$fix_rate      = $diagnostics_run > 0 ? round( ( $total_fixes / $diagnostics_run ) * 100 ) : 0;
?>

<div class="wrap wps-page-container">
	<?php
	wpshadow_render_page_header(
		__( 'Diagnostics Fix Rate Report', 'wpshadow' ),
		__( 'Track how many diagnostics have run and how fixes are being completed over time.', 'wpshadow' ),
		'dashicons-yes-alt'
	);
	?>

	<div class="wps-card">
		<div class="wps-card-body">
			<h2 class="wps-text-xl wps-mb-3">
				<span class="dashicons dashicons-yes-alt wps-text-success"></span>
				<?php esc_html_e( 'Fix Progress Overview', 'wpshadow' ); ?>
			</h2>
			<p class="wps-text-muted wps-mb-4">
				<?php esc_html_e( 'These numbers summarize how often WPShadow diagnostics are running and how fixes are being completed.', 'wpshadow' ); ?>
			</p>

			<div class="wps-grid wps-grid-cols-2 wps-gap-3 wps-mb-4">
				<div class="wps-card">
					<div class="wps-card-body">
						<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Diagnostics Run', 'wpshadow' ); ?></div>
						<div class="wps-text-2xl wps-font-semibold"><?php echo esc_html( number_format_i18n( $diagnostics_run ) ); ?></div>
					</div>
				</div>
				<div class="wps-card">
					<div class="wps-card-body">
						<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Total Fixes Logged', 'wpshadow' ); ?></div>
						<div class="wps-text-2xl wps-font-semibold"><?php echo esc_html( number_format_i18n( $total_fixes ) ); ?></div>
					</div>
				</div>
				<div class="wps-card">
					<div class="wps-card-body">
						<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Automatic Fixes', 'wpshadow' ); ?></div>
						<div class="wps-text-2xl wps-font-semibold"><?php echo esc_html( number_format_i18n( $auto_fixes ) ); ?></div>
					</div>
				</div>
				<div class="wps-card">
					<div class="wps-card-body">
						<div class="wps-text-sm wps-text-muted"><?php esc_html_e( 'User Fixes', 'wpshadow' ); ?></div>
						<div class="wps-text-2xl wps-font-semibold"><?php echo esc_html( number_format_i18n( $user_fixes ) ); ?></div>
					</div>
				</div>
			</div>

			<div class="wps-grid wps-grid-cols-2 wps-gap-4">
				<div>
					<h3 class="wps-text-lg wps-mb-2"><?php esc_html_e( 'Fix Breakdown', 'wpshadow' ); ?></h3>
					<div class="wps-mb-3">
						<div class="wps-flex wps-items-center wps-justify-between wps-mb-2">
							<span class="wps-text-sm wps-text-muted"><?php esc_html_e( 'Automatic treatments', 'wpshadow' ); ?></span>
							<span class="wps-text-sm wps-font-semibold"><?php echo esc_html( $auto_percent ); ?>%</span>
						</div>
						<div class="wps-progress">
							<div class="wps-progress-bar" style="width: <?php echo esc_attr( (string) $auto_percent ); ?>%;"></div>
						</div>
					</div>
					<div>
						<div class="wps-flex wps-items-center wps-justify-between wps-mb-2">
							<span class="wps-text-sm wps-text-muted"><?php esc_html_e( 'User fixes', 'wpshadow' ); ?></span>
							<span class="wps-text-sm wps-font-semibold"><?php echo esc_html( $user_percent ); ?>%</span>
						</div>
						<div class="wps-progress">
							<div class="wps-progress-bar" style="width: <?php echo esc_attr( (string) $user_percent ); ?>%;"></div>
						</div>
					</div>
				</div>
				<div>
					<h3 class="wps-text-lg wps-mb-2"><?php esc_html_e( 'Overall Fix Rate', 'wpshadow' ); ?></h3>
					<p class="wps-text-sm wps-text-muted wps-mb-2">
						<?php esc_html_e( 'This shows how many fixes have been logged compared to diagnostics that have run.', 'wpshadow' ); ?>
					</p>
					<div class="wps-progress">
						<div class="wps-progress-bar" style="width: <?php echo esc_attr( (string) $fix_rate ); ?>%;"></div>
					</div>
					<div class="wps-progress-text">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: percentage */
								__( '%d%% of diagnostics have logged a fix.', 'wpshadow' ),
								$fix_rate
							)
						);
						?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php
	if ( function_exists( 'wpshadow_render_page_activities' ) ) {
		wpshadow_render_page_activities( 'reports', 10 );
	}
	?>
</div>
