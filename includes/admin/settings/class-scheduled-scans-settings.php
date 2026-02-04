<?php
/**
 * Scheduled Scans Settings Page
 *
 * Admin page for configuring automated scan scheduling
 *
 * @since   1.6032.1021
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scheduled_Scans_Settings Class
 *
 * Renders the scheduled scans settings page
 *
 * @since 1.6032.1021
 */
class Scheduled_Scans_Settings {

	/**
	 * Initialize settings page
	 *
	 * @since 1.6032.1021
	 * @return void
	 */
	public static function init() {
		add_action( 'wpshadow_register_admin_pages', array( __CLASS__, 'register_settings_section' ) );
	}

	/**
	 * Render scheduled scans settings form
	 *
	 * @since 1.6032.1021
	 * @return void
	 */
	public static function render_form() {
		$enabled = get_option( 'wpshadow_scheduled_scans_enabled', false );
		$frequency = get_option( 'wpshadow_scheduled_scans_frequency', 'daily' );
		$time = get_option( 'wpshadow_scheduled_scans_time', '02:00' );
		$depth = get_option( 'wpshadow_scheduled_scans_depth', 'standard' );
		$max_time = get_option( 'wpshadow_scheduled_scans_max_execution_time', 300 );
		$email_results = get_option( 'wpshadow_scheduled_scans_email_results', false );
		$next_scan = get_option( 'wpshadow_scheduled_scans_next_scan', '' );
		$last_scan = get_option( 'wpshadow_scheduled_scans_last_scan', '' );
		?>
		<div class="wpshadow-settings-section">
			<h2><?php esc_html_e( 'Scheduled Scans', 'wpshadow' ); ?></h2>
			<p><?php esc_html_e( 'Automatically scan your site on a schedule to detect issues without manual intervention.', 'wpshadow' ); ?></p>

			<?php if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) : ?>
				<div class="notice notice-warning inline is-dismissible">
					<p>
						<?php 
						printf(
							/* translators: %s: documentation link */
							esc_html__( 'Note: WordPress cron is disabled on this site. Scheduled scans require a real cron job. %s', 'wpshadow' ),
							'<a href="https://wpshadow.com/kb/setup-wordpress-cron" target="_blank">' . esc_html__( 'Learn how to set up a real cron job', 'wpshadow' ) . '</a>'
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<form method="post" action="">
				<?php wp_nonce_field( 'wpshadow_scheduled_scans_nonce' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="scheduled-scans-enabled">
								<?php esc_html_e( 'Enable Scheduled Scans', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<input 
								type="checkbox" 
								id="scheduled-scans-enabled"
								name="wpshadow_scheduled_scans_enabled"
								value="1"
								<?php checked( $enabled ); ?>
								aria-describedby="scheduled-scans-enabled-description"
							/>
							<p id="scheduled-scans-enabled-description" class="description">
								<?php esc_html_e( 'Check this box to enable automatic site scanning on a schedule.', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="scheduled-scans-frequency">
								<?php esc_html_e( 'Scan Frequency', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<select 
								id="scheduled-scans-frequency"
								name="wpshadow_scheduled_scans_frequency"
								aria-describedby="scheduled-scans-frequency-description"
							>
								<option value="daily" <?php selected( $frequency, 'daily' ); ?>>
									<?php esc_html_e( 'Daily', 'wpshadow' ); ?>
								</option>
								<option value="twice-weekly" <?php selected( $frequency, 'twice-weekly' ); ?>>
									<?php esc_html_e( 'Twice Weekly (Mon & Thu)', 'wpshadow' ); ?>
								</option>
								<option value="weekly" <?php selected( $frequency, 'weekly' ); ?>>
									<?php esc_html_e( 'Weekly', 'wpshadow' ); ?>
								</option>
								<option value="bi-weekly" <?php selected( $frequency, 'bi-weekly' ); ?>>
									<?php esc_html_e( 'Bi-Weekly', 'wpshadow' ); ?>
								</option>
								<option value="monthly" <?php selected( $frequency, 'monthly' ); ?>>
									<?php esc_html_e( 'Monthly', 'wpshadow' ); ?>
								</option>
							</select>
							<p id="scheduled-scans-frequency-description" class="description">
								<?php esc_html_e( 'How often should the site be scanned for issues.', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="scheduled-scans-time">
								<?php esc_html_e( 'Scan Time (24-hour format)', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<input 
								type="time" 
								id="scheduled-scans-time"
								name="wpshadow_scheduled_scans_time"
								value="<?php echo esc_attr( $time ); ?>"
								aria-describedby="scheduled-scans-time-description"
							/>
							<p id="scheduled-scans-time-description" class="description">
								<?php esc_html_e( 'Time of day when scans should run (server timezone).', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="scheduled-scans-depth">
								<?php esc_html_e( 'Scan Depth', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<select 
								id="scheduled-scans-depth"
								name="wpshadow_scheduled_scans_depth"
								aria-describedby="scheduled-scans-depth-description"
							>
								<option value="quick" <?php selected( $depth, 'quick' ); ?>>
									<?php esc_html_e( 'Quick (Basic Security)', 'wpshadow' ); ?>
								</option>
								<option value="standard" <?php selected( $depth, 'standard' ); ?>>
									<?php esc_html_e( 'Standard (Security + Config)', 'wpshadow' ); ?>
								</option>
								<option value="deep" <?php selected( $depth, 'deep' ); ?>>
									<?php esc_html_e( 'Deep (Comprehensive)', 'wpshadow' ); ?>
								</option>
							</select>
							<p id="scheduled-scans-depth-description" class="description">
								<?php esc_html_e( 'Deeper scans are more thorough but may take longer.', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="scheduled-scans-max-time">
								<?php esc_html_e( 'Maximum Execution Time', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<input 
								type="number" 
								id="scheduled-scans-max-time"
								name="wpshadow_scheduled_scans_max_execution_time"
								value="<?php echo esc_attr( $max_time ); ?>"
								min="60"
								max="3600"
								step="60"
								aria-describedby="scheduled-scans-max-time-description"
							/>
							<p id="scheduled-scans-max-time-description" class="description">
								<?php esc_html_e( 'Maximum seconds the scan should run (60-3600). Default: 300 seconds (5 minutes).', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="scheduled-scans-email-results">
								<?php esc_html_e( 'Email Results', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<input 
								type="checkbox" 
								id="scheduled-scans-email-results"
								name="wpshadow_scheduled_scans_email_results"
								value="1"
								<?php checked( $email_results ); ?>
								aria-describedby="scheduled-scans-email-results-description"
							/>
							<p id="scheduled-scans-email-results-description" class="description">
								<?php esc_html_e( 'Email scan results after each scheduled scan completes.', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>
				</table>

				<?php if ( $next_scan ) : ?>
					<p class="description">
						<?php 
						printf(
							/* translators: %s: next scan time */
							esc_html__( 'Next scheduled scan: %s', 'wpshadow' ),
							esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $next_scan ) ) )
						);
						?>
					</p>
				<?php endif; ?>

				<?php if ( $last_scan ) : ?>
					<p class="description">
						<?php 
						printf(
							/* translators: %s: last scan time */
							esc_html__( 'Last scan completed: %s', 'wpshadow' ),
							esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_scan ) ) )
						);
						?>
					</p>
				<?php endif; ?>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
