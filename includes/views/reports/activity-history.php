<?php
/**
 * Activity History Report View
 *
 * @package WPShadow
 * @subpackage Reports
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap wps-page-container">
	<?php
	wpshadow_render_page_header(
		__( 'Activity History Report', 'wpshadow' ),
		__( 'Complete timeline of everything WPShadow has done for your site.', 'wpshadow' ),
		'dashicons-backup'
	);
	?>

	<div class="wps-card">
		<div class="wps-card-body">
			<div style="text-align: center; padding: 60px 20px;">
				<div style="font-size: 64px; margin-bottom: 20px;">📋</div>
				<h2><?php esc_html_e( 'Activity Log Being Compiled...', 'wpshadow' ); ?></h2>
				<p style="font-size: 16px; color: #666; max-width: 600px; margin: 20px auto;">
					<?php esc_html_e( 'Gathering all maintenance activities, fixes applied, scans run, and automations executed to show you a complete history of improvements.', 'wpshadow' ); ?>
				</p>
				
				<div style="margin-top: 40px;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>" class="wps-btn wps-btn-primary">
						<?php esc_html_e( 'View Guardian Dashboard', 'wpshadow' ); ?>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-reports' ) ); ?>" class="wps-btn wps-btn-secondary">
						<?php esc_html_e( 'Back to Reports', 'wpshadow' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</div>
