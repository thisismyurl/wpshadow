<?php
/**
 * Deep Scan Tool View
 *
 * @package WPShadow
 * @subpackage Tools
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\Options_Manager;

// Enqueue scan tools assets
wp_enqueue_style( 'wpshadow-scan-tools', WPSHADOW_URL . 'assets/css/scan-tools.css', array(), WPSHADOW_VERSION );
wp_enqueue_script( 'wpshadow-scan-tools', WPSHADOW_URL . 'assets/js/scan-tools.js', array( 'jquery' ), WPSHADOW_VERSION, true );

// Localize script for AJAX URL
wp_localize_script(
	'wpshadow-scan-tools',
	'wpshadowScanTools',
	array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
	)
);
?>

<div class="wpshadow-tool deep-scan-tool">
	<h2><?php esc_html_e( 'Deep Scan', 'wpshadow' ); ?></h2>
	
	<p class="description">
		<?php esc_html_e( 'Run a comprehensive scan that checks database health, performance, and advanced compatibility issues. This may take several minutes to complete.', 'wpshadow' ); ?>
	</p>

	<div class="scan-info">
		<?php
		$last_run = Options_Manager::get_int( 'wpshadow_last_heavy_tests', 0 );

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
				<?php esc_html_e( 'Deep Scan has never been run on this site.', 'wpshadow' ); ?>
			</p>
			<?php
		}
		?>
	</div>

	<button class="button button-primary wpshadow-run-scan" 
		data-scan-type="deep"
		data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_scan_nonce' ) ); ?>"
		data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
		data-redirect-url="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>">
		<?php esc_html_e( 'Run Deep Scan Now', 'wpshadow' ); ?>
	</button>

	<div class="scan-progress hidden">
		<div class="progress-bar">
			<div class="progress-fill"></div>
		</div>
		<p class="progress-text"></p>
	</div>

	<div class="scan-results"></div>
</div>
