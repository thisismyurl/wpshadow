<?php
/**
 * Defensive Engineering Settings Page
 *
 * Provides UI for Murphy's Law defensive engineering preferences
 * (Pillar ⚙️: Murphy's Law - If it can fail, it will)
 *
 * @package    WPShadow
 * @subpackage Admin\Settings
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\Settings_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defensive Settings Class
 *
 * Manages defensive engineering settings to protect users from
 * system failures, network issues, and data loss.
 *
 * @since 1.6093.1200
 */
class Defensive_Settings {

	/**
	 * Initialize the settings page
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue defensive settings assets.
	 *
	 * @since 1.6093.1200
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( 'settings_page_wpshadow-defensive' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-defensive-settings',
			WPSHADOW_URL . 'assets/css/defensive-settings.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Register the settings page in WordPress admin
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function register_menu_page(): void {
		add_options_page(
			__( 'Defensive Engineering', 'wpshadow' ),
			__( 'Defensive', 'wpshadow' ),
			'manage_options',
			'wpshadow-defensive',
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Render the defensive settings page
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_page(): void {
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}

		// Get current settings
		$autosave_freq = Settings_Registry::get_autosave_frequency();
		$retry_operations = Settings_Registry::should_retry_failed_operations();
		$use_stale = Settings_Registry::use_stale_cache();
		$offline_mode = Settings_Registry::enable_offline_mode();
		$graceful_errors = Settings_Registry::graceful_error_display();
		$timeout = Settings_Registry::get_operation_timeout();

		?>
		<div class="wrap wpshadow-settings-page">
			<h1>⚙️ <?php esc_html_e( 'Defensive Engineering', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>
			
			<div class="wpshadow-settings-intro">
				<h2><?php esc_html_e( 'Protect Your Work from System Failures', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'These settings follow Murphy\'s Law: "If something can go wrong, it will." WPShadow protects you from network failures, browser crashes, database issues, and other problems that inevitably happen.', 'wpshadow' ); ?></p>
			</div>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpshadow_defensive_settings' );
				do_settings_sections( 'wpshadow_defensive_settings' );
				?>

				<table class="form-table" role="presentation">
					<!-- Auto-Save Frequency -->
					<tr>
						<th scope="row">
							<label for="wpshadow_autosave_frequency">
								<?php esc_html_e( 'Auto-Save Frequency', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<input 
								type="number" 
								name="wpshadow_autosave_frequency" 
								id="wpshadow_autosave_frequency" 
								value="<?php echo esc_attr( $autosave_freq ); ?>"
								min="10"
								max="300"
								step="5"
								class="small-text"
							/>
							<?php esc_html_e( 'seconds', 'wpshadow' ); ?>
							<p class="description">
								<?php
								esc_html_e(
									'How often WPShadow automatically saves your work to prevent data loss if your browser crashes or you accidentally close the tab. Range: 10-300 seconds.',
									'wpshadow'
								);
								?>
							</p>
							<div class="wpshadow-recommendation">
								<strong>💡 <?php esc_html_e( 'Recommended:', 'wpshadow' ); ?></strong><br>
								<?php esc_html_e( '30 seconds (balances protection vs. server load)', 'wpshadow' ); ?>
							</div>
						</td>
					</tr>

					<!-- Retry Failed Operations -->
					<tr>
						<th scope="row">
							<label for="wpshadow_retry_failed_operations">
								<?php esc_html_e( 'Auto-Retry Failed Operations', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="wpshadow_retry_failed_operations" 
									id="wpshadow_retry_failed_operations" 
									value="1" 
									<?php checked( $retry_operations, true ); ?>
								/>
								<?php esc_html_e( 'Automatically retry operations that fail', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php
								esc_html_e(
									'When network requests fail or databases timeout, WPShadow will automatically retry up to 3 times before giving up. Uses exponential backoff (waits 1s, then 2s, then 4s between attempts).',
									'wpshadow'
								);
								?>
							</p>
							<div class="wpshadow-example-box">
								<strong><?php esc_html_e( 'Example:', 'wpshadow' ); ?></strong><br>
								<?php esc_html_e( 'Cloud scan fails due to network issue → WPShadow waits 1 second and tries again → Success! You never knew there was a problem.', 'wpshadow' ); ?>
							</div>
						</td>
					</tr>

					<!-- Use Stale Cache -->
					<tr>
						<th scope="row">
							<label for="wpshadow_use_stale_cache">
								<?php esc_html_e( 'Use Stale Cache', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="wpshadow_use_stale_cache" 
									id="wpshadow_use_stale_cache" 
									value="1" 
									<?php checked( $use_stale, true ); ?>
								/>
								<?php esc_html_e( 'Show old data when fresh data unavailable', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php
								esc_html_e(
									'If WPShadow can\'t get fresh data (network down, API offline), it will show the last cached data with a timestamp. Old information is better than no information.',
									'wpshadow'
								);
								?>
							</p>
							<div class="wpshadow-analogy">
								<strong>📖 <?php esc_html_e( 'Analogy:', 'wpshadow' ); ?></strong><br>
								<?php esc_html_e( 'Like reading yesterday\'s newspaper when today\'s delivery doesn\'t arrive. The news is slightly old, but you still learn something useful.', 'wpshadow' ); ?>
							</div>
						</td>
					</tr>

					<!-- Enable Offline Mode -->
					<tr>
						<th scope="row">
							<label for="wpshadow_enable_offline_mode">
								<?php esc_html_e( 'Offline Mode', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="wpshadow_enable_offline_mode" 
									id="wpshadow_enable_offline_mode" 
									value="1" 
									<?php checked( $offline_mode, true ); ?>
								/>
								<?php esc_html_e( 'Work offline and sync when network returns', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php
								esc_html_e(
									'When your internet connection drops, WPShadow queues operations locally and automatically syncs them when the connection returns. You can keep working without interruption.',
									'wpshadow'
								);
								?>
							</p>
							<div class="wpshadow-feature-list">
								<strong><?php esc_html_e( 'What Works Offline:', 'wpshadow' ); ?></strong>
								<ul class="wpshadow-feature-list-items">
									<li>✅ <?php esc_html_e( 'Local diagnostics (security, performance)', 'wpshadow' ); ?></li>
									<li>✅ <?php esc_html_e( 'Treatment applications (fixes)', 'wpshadow' ); ?></li>
									<li>✅ <?php esc_html_e( 'Report generation', 'wpshadow' ); ?></li>
									<li>❌ <?php esc_html_e( 'Cloud diagnostics (queued until online)', 'wpshadow' ); ?></li>
								</ul>
							</div>
						</td>
					</tr>

					<!-- Graceful Error Display -->
					<tr>
						<th scope="row">
							<label for="wpshadow_graceful_error_display">
								<?php esc_html_e( 'User-Friendly Errors', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="wpshadow_graceful_error_display" 
									id="wpshadow_graceful_error_display" 
									value="1" 
									<?php checked( $graceful_errors, true ); ?>
								/>
								<?php esc_html_e( 'Show helpful error messages (hide technical jargon)', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php
								esc_html_e(
									'When errors happen, WPShadow shows you what went wrong in plain English with suggested fixes—not scary technical error codes. Technical details are still logged for developers.',
									'wpshadow'
								);
								?>
							</p>
							<div class="wpshadow-comparison">
								<table class="wpshadow-comparison-table">
									<tr>
										<th class="wpshadow-comparison-head wpshadow-comparison-head-error">
											❌ <?php esc_html_e( 'Technical Error', 'wpshadow' ); ?>
										</th>
										<th class="wpshadow-comparison-head wpshadow-comparison-head-friendly">
											✅ <?php esc_html_e( 'User-Friendly Error', 'wpshadow' ); ?>
										</th>
									</tr>
									<tr>
										<td class="wpshadow-comparison-cell wpshadow-comparison-cell-code">
											<code>Fatal error: Uncaught PDOException in /var/www/html/wp-content/plugins/wpshadow/includes/core/class-database.php:47</code>
										</td>
										<td class="wpshadow-comparison-cell">
											<?php esc_html_e( 'We couldn\'t connect to the database. This usually means your hosting server is temporarily busy. Try again in a minute?', 'wpshadow' ); ?>
										</td>
									</tr>
								</table>
							</div>
						</td>
					</tr>

					<!-- Operation Timeout -->
					<tr>
						<th scope="row">
							<label for="wpshadow_operation_timeout">
								<?php esc_html_e( 'Operation Timeout', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<input 
								type="number" 
								name="wpshadow_operation_timeout" 
								id="wpshadow_operation_timeout" 
								value="<?php echo esc_attr( $timeout ); ?>"
								min="5"
								max="300"
								step="5"
								class="small-text"
							/>
							<?php esc_html_e( 'seconds', 'wpshadow' ); ?>
							<p class="description">
								<?php
								esc_html_e(
									'Maximum time to wait for operations before timing out. Prevents WPShadow from freezing if external services are slow or unresponsive. Range: 5-300 seconds.',
									'wpshadow'
								);
								?>
							</p>
							<div class="wpshadow-recommendation">
								<strong>💡 <?php esc_html_e( 'Recommended:', 'wpshadow' ); ?></strong><br>
								<?php esc_html_e( '30 seconds (most operations complete within 10s)', 'wpshadow' ); ?>
							</div>
						</td>
					</tr>
				</table>

				<?php submit_button( __( 'Save Defensive Settings', 'wpshadow' ) ); ?>
			</form>

			<div class="wpshadow-settings-footer">
				<h3><?php esc_html_e( 'Why This Matters', 'wpshadow' ); ?></h3>
				<p>
					<?php
					esc_html_e(
						'Edward A. Murphy Jr. (aerospace engineer, 1949) observed: "If there are two ways to do something, and one of those ways will result in disaster, someone will do it that way." The solution isn\'t hoping things work—it\'s designing systems where failure is expected and handled gracefully.',
						'wpshadow'
					);
					?>
				</p>
				<p>
					<?php
					esc_html_e(
						'These defensive engineering settings protect you from inevitable failures: network outages, browser crashes, database timeouts, and other problems that WILL happen eventually. WPShadow assumes everything will fail and plans accordingly.',
						'wpshadow'
					);
					?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Philosophy Alignment:', 'wpshadow' ); ?></strong>
					<?php esc_html_e( '⚙️ Murphy\'s Law (CANON Pillar)', 'wpshadow' ); ?>
				</p>
				<p>
					<a href="https://wpshadow.com/kb/defensive-engineering" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Learn more about defensive engineering principles', 'wpshadow' ); ?>
					</a>
				</p>
			</div>
		</div>

		<?php
	}
}
