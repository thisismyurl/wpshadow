<?php
/**
 * Mobile Friendliness Report View
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
		__( 'Mobile Friendliness Report', 'wpshadow' ),
		__( 'Check if your pages work well on phones and tablets of all sizes.', 'wpshadow' ),
		'dashicons-smartphone'
	);
	?>

	<div class="wps-card">
		<div class="wps-card-body">
			<div style="text-align: center; padding: 60px 20px;">
				<div style="font-size: 64px; margin-bottom: 20px;">📱</div>
				<h2><?php esc_html_e( 'Mobile Testing in Progress...', 'wpshadow' ); ?></h2>
				<p style="font-size: 16px; color: #666; max-width: 600px; margin: 20px auto;">
					<?php esc_html_e( 'Testing how your pages look on different screen sizes, checking touch targets, and ensuring buttons and links are easy to tap on mobile devices.', 'wpshadow' ); ?>
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
