<?php
/**
 * Quick Scan Report
 *
 * Fast, lightweight scan for common issues and security concerns.
 * Typically completes in 30-60 seconds.
 *
 * @package WPShadow
 * @subpackage Reports
 * @since 0.6093.1200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\Options_Manager;
use WPShadow\Views\Tool_View_Base;

require WPSHADOW_PATH . 'includes/views/class-tool-view-base.php';

// Enqueue assets
Tool_View_Base::enqueue_assets( 'quick-scan' );

// Render header
Tool_View_Base::render_header( __( 'Quick Scan', 'wpshadow' ) );
?>

<div class="wpshadow-tool quick-scan-tool">

	<p class="description">
		<?php esc_html_e( 'Run a fast, lightweight scan of your site for common issues and security concerns. This typically completes in 30-60 seconds.', 'wpshadow' ); ?>
	</p>

	<div class="scan-info">
		<?php
		$last_run = Options_Manager::get_int( 'wpshadow_last_quick_checks', 0 );

		if ( ! empty( $last_run ) ) {
			$age     = time() - $last_run;
			$age_str = human_time_diff( $last_run, time() );
			?>
			<p class="last-run">
				<strong><?php esc_html_e( 'Last run:', 'wpshadow' ); ?></strong>
				<?php echo esc_html( $age_str ); ?> <?php esc_html_e( 'ago', 'wpshadow' ); ?>
			</p>
			<?php
		} else {
			?>
			<p class="never-run">
				<?php esc_html_e( 'Quick Scan has never been run on this site.', 'wpshadow' ); ?>
			</p>
			<?php
		}
		?>
	</div>

	<button type="button" class="wps-btn wps-btn-success wps-btn-icon-left wpshadow-run-scan"
		data-scan-type="quick"
		data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_scan_nonce' ) ); ?>"
		data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
		data-redirect-url="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>"
		aria-label="<?php esc_attr_e( 'Run a quick scan of your site now', 'wpshadow' ); ?>">
		<span class="dashicons dashicons-update"></span>
		<?php esc_html_e( 'Run Quick Scan Now', 'wpshadow' ); ?>
	</button>

	<div class="scan-progress hidden" role="status" aria-live="polite">
		<div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
			<div class="progress-fill"></div>
		</div>
		<p class="progress-text"></p>
	</div>

	<div class="scan-results"></div>
</div>

<?php Tool_View_Base::render_footer(); ?>

