<?php
/**
 * Database Optimization Report View
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
		__( 'Database Optimization Report', 'wpshadow' ),
		__( 'Clean up and optimize your database for faster queries and better performance.', 'wpshadow' ),
		'dashicons-database'
	);
	?>

	<div class="wps-card">
		<div class="wps-card-body">
			<div style="text-align: center; padding: 60px 20px;">
				<div style="font-size: 64px; margin-bottom: 20px;">🗄️</div>
				<h2><?php esc_html_e( 'Database Analysis Running...', 'wpshadow' ); ?></h2>
				<p style="font-size: 16px; color: #666; max-width: 600px; margin: 20px auto;">
					<?php esc_html_e( 'Scanning your database for clutter, checking table optimization, and identifying opportunities to speed up data retrieval.', 'wpshadow' ); ?>
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

	<!-- Recent Activity Section -->
	<?php
	if ( function_exists( 'wpshadow_render_page_activities' ) ) {
		wpshadow_render_page_activities( 'reports', 10 );
	}
	?>
</div>
