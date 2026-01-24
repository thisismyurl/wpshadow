<?php
/**
 * Quick Scan Tool View
 *
 * @package WPShadow
 * @subpackage Tools
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPShadow\Core\Options_Manager;
?>

<div class="wpshadow-tool quick-scan-tool">
	<h2><?php esc_html_e( 'Quick Scan', 'wpshadow' ); ?></h2>
	
	<p class="description">
		<?php esc_html_e( 'Run a fast, lightweight scan of your site for common issues and security concerns. This typically completes in 30-60 seconds.', 'wpshadow' ); ?>
	</p>

	<div class="scan-info">
		<?php
		$last_run = Options_Manager::get_int( 'wpshadow_last_quick_checks', 0 );
		
		if ( ! empty( $last_run ) ) {
			$age = time() - $last_run;
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

	<button class="button button-primary wpshadow-run-scan" data-scan-type="quick">
		<?php esc_html_e( 'Run Quick Scan Now', 'wpshadow' ); ?>
	</button>

	<div class="scan-progress hidden">
		<div class="progress-bar">
			<div class="progress-fill"></div>
		</div>
		<p class="progress-text"></p>
	</div>
</div>
