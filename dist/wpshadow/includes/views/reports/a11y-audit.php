<?php
/**
 * Accessibility Audit Report View
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
		__( 'Accessibility Audit Report', 'wpshadow' ),
		__( 'Ensure everyone can use your site, including people with disabilities.', 'wpshadow' ),
		'dashicons-universal-access'
	);
	?>

	<div class="wps-card">
		<div class="wps-card-body">
			<div style="text-align: center; padding: 60px 20px;">
				<div style="font-size: 64px; margin-bottom: 20px;">♿</div>
				<h2><?php esc_html_e( 'Accessibility Check Running...', 'wpshadow' ); ?></h2>
				<p style="font-size: 16px; color: #666; max-width: 600px; margin: 20px auto;">
					<?php esc_html_e( 'Checking color contrast, keyboard navigation, screen reader compatibility, and WCAG compliance to ensure your site is accessible to everyone.', 'wpshadow' ); ?>
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
