<?php
/**
 * Broken Links Report View
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
		__( 'Broken Links Report', 'wpshadow' ),
		__( 'Find links that don\'t work anymore and need fixing or updating.', 'wpshadow' ),
		'dashicons-admin-links'
	);
	?>

	<div class="wps-card">
		<div class="wps-card-body">
			<div style="text-align: center; padding: 60px 20px;">
				<div style="font-size: 64px; margin-bottom: 20px;">🔗</div>
				<h2><?php esc_html_e( 'Link Checking in Progress...', 'wpshadow' ); ?></h2>
				<p style="font-size: 16px; color: #666; max-width: 600px; margin: 20px auto;">
					<?php esc_html_e( 'Scanning all pages for broken links, checking internal and external URLs, and identifying which links need to be fixed or removed.', 'wpshadow' ); ?>
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
