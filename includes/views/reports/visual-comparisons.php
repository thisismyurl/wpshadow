<?php
/**
 * Visual Comparison Report View
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
		__( 'Visual Comparison Report', 'wpshadow' ),
		__( 'See before-and-after pictures of your pages to track design changes over time.', 'wpshadow' ),
		'dashicons-images-alt2'
	);
	?>

	<div class="wps-card">
		<div class="wps-card-body">
			<div style="text-align: center; padding: 60px 20px;">
				<div style="font-size: 64px; margin-bottom: 20px;">📸</div>
				<h2><?php esc_html_e( 'Visual Analysis Running...', 'wpshadow' ); ?></h2>
				<p style="font-size: 16px; color: #666; max-width: 600px; margin: 20px auto;">
					<?php esc_html_e( 'Capturing screenshots of your pages and comparing them with previous versions to show how your design has evolved. Perfect for reviewing changes.', 'wpshadow' ); ?>
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
