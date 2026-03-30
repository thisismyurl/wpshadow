<?php
/**
 * Cultural Settings Page
 *
 * Provides UI for cultural and language preferences to support
 * global users (Pillar 🌐: Culturally Respectful)
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
 * Cultural Settings Class
 *
 * Manages cultural and language preference settings to make WPShadow
 * work for users around the world with different date formats, number
 * systems, reading directions, and language expectations.
 *
 * @since 1.6093.1200
 */
class Cultural_Settings {

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
	 * Enqueue cultural settings assets.
	 *
	 * @since 1.6093.1200
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		if ( 'wpshadow_page_wpshadow-settings' !== $hook || 'cultural' !== $tab ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-cultural-settings',
			WPSHADOW_URL . 'assets/css/cultural-settings.css',
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
		// This screen is rendered as a tab card in wpshadow-settings.
		// Kept for backwards compatibility with existing init() flow.
	}

	/**
	 * Render the cultural settings page
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
		$date_format = Settings_Registry::get_date_format_preference();
		$time_format = Settings_Registry::get_time_format_preference();
		$number_format = Settings_Registry::get_number_format_preference();
		$rtl_preference = Settings_Registry::get_rtl_preference();
		$avoid_idioms = Settings_Registry::should_avoid_idioms();

		?>
		<div class="wrap wpshadow-settings-page">
			<h1>🌐 <?php esc_html_e( 'Cultural Settings', 'wpshadow' ); ?></h1>

			<?php do_action( 'wpshadow_after_page_header' ); ?>
			
			<div class="wpshadow-settings-intro">
				<h2><?php esc_html_e( 'WPShadow Speaks Your Language', 'wpshadow' ); ?></h2>
				<p><?php esc_html_e( 'Tell us how you prefer to see dates, numbers, and text. We\'ll adjust WPShadow to match your expectations, whether you\'re in Tokyo, Cairo, Berlin, or anywhere else.', 'wpshadow' ); ?></p>
			</div>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'wpshadow_cultural_settings' );
				do_settings_sections( 'wpshadow_cultural_settings' );
				?>

				<table class="form-table" role="presentation">
					<!-- Date Format Preference -->
					<tr>
						<th scope="row">
							<label for="wpshadow_date_format_preference">
								<?php esc_html_e( 'Date Format', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<select name="wpshadow_date_format_preference" id="wpshadow_date_format_preference">
								<option value="wordpress" <?php selected( $date_format, 'wordpress' ); ?>>
									<?php esc_html_e( 'Use WordPress Setting', 'wpshadow' ); ?>
									(<?php echo esc_html( date_i18n( get_option( 'date_format' ), time() ) ); ?>)
								</option>
								<option value="iso8601" <?php selected( $date_format, 'iso8601' ); ?>>
									<?php esc_html_e( 'International Standard (ISO 8601)', 'wpshadow' ); ?>
									(<?php echo esc_html( gmdate( 'Y-m-d', time() ) ); ?>)
								</option>
								<option value="us" <?php selected( $date_format, 'us' ); ?>>
									<?php esc_html_e( 'US Format', 'wpshadow' ); ?>
									(<?php echo esc_html( gmdate( 'm/d/Y', time() ) ); ?>)
								</option>
								<option value="eu" <?php selected( $date_format, 'eu' ); ?>>
									<?php esc_html_e( 'European Format', 'wpshadow' ); ?>
									(<?php echo esc_html( gmdate( 'd/m/Y', time() ) ); ?>)
								</option>
							</select>
							<p class="description">
								<?php esc_html_e( 'Choose how you want to see dates throughout WPShadow (like today\'s date in reports)', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<!-- Time Format Preference -->
					<tr>
						<th scope="row">
							<label for="wpshadow_time_format_preference">
								<?php esc_html_e( 'Time Format', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<select name="wpshadow_time_format_preference" id="wpshadow_time_format_preference">
								<option value="wordpress" <?php selected( $time_format, 'wordpress' ); ?>>
									<?php esc_html_e( 'Use WordPress Setting', 'wpshadow' ); ?>
									(<?php echo esc_html( date_i18n( get_option( 'time_format' ), time() ) ); ?>)
								</option>
								<option value="12h" <?php selected( $time_format, '12h' ); ?>>
									<?php esc_html_e( '12-Hour Clock', 'wpshadow' ); ?>
									(<?php echo esc_html( gmdate( 'g:i a', time() ) ); ?>)
								</option>
								<option value="24h" <?php selected( $time_format, '24h' ); ?>>
									<?php esc_html_e( '24-Hour Clock', 'wpshadow' ); ?>
									(<?php echo esc_html( gmdate( 'H:i', time() ) ); ?>)
								</option>
							</select>
							<p class="description">
								<?php esc_html_e( 'Choose 12-hour (2:30 PM) or 24-hour (14:30) time display', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<!-- Number Format Preference -->
					<tr>
						<th scope="row">
							<label for="wpshadow_number_format_preference">
								<?php esc_html_e( 'Number Format', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<select name="wpshadow_number_format_preference" id="wpshadow_number_format_preference">
								<option value="locale" <?php selected( $number_format, 'locale' ); ?>>
									<?php esc_html_e( 'Auto-Detect from Your Language', 'wpshadow' ); ?>
								</option>
								<option value="us" <?php selected( $number_format, 'us' ); ?>>
									<?php esc_html_e( 'US/UK Style', 'wpshadow' ); ?>
									(1,000.50)
								</option>
								<option value="eu" <?php selected( $number_format, 'eu' ); ?>>
									<?php esc_html_e( 'European Style', 'wpshadow' ); ?>
									(1.000,50)
								</option>
							</select>
							<p class="description">
								<?php esc_html_e( 'Choose how you want commas and dots to appear in numbers (like showing file sizes or performance metrics)', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<!-- RTL Interface -->
					<tr>
						<th scope="row">
							<label for="wpshadow_rtl_interface">
								<?php esc_html_e( 'Text Direction', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<select name="wpshadow_rtl_interface" id="wpshadow_rtl_interface">
								<option value="auto" <?php selected( $rtl_preference, 'auto' ); ?>>
									<?php esc_html_e( 'Auto-Detect from Language', 'wpshadow' ); ?>
								</option>
								<option value="force_ltr" <?php selected( $rtl_preference, 'force_ltr' ); ?>>
									<?php esc_html_e( 'Always Left-to-Right', 'wpshadow' ); ?>
									(English, Spanish, etc.)
								</option>
								<option value="force_rtl" <?php selected( $rtl_preference, 'force_rtl' ); ?>>
									<?php esc_html_e( 'Always Right-to-Left', 'wpshadow' ); ?>
									(العربية, עברית)
								</option>
							</select>
							<p class="description">
								<?php esc_html_e( 'For languages like Arabic or Hebrew, this flips the interface to read right-to-left', 'wpshadow' ); ?>
							</p>
						</td>
					</tr>

					<!-- Avoid Idioms -->
					<tr>
						<th scope="row">
							<label for="wpshadow_avoid_idioms">
								<?php esc_html_e( 'Simple Language', 'wpshadow' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="wpshadow_avoid_idioms" 
									id="wpshadow_avoid_idioms" 
									value="1" 
									<?php checked( $avoid_idioms, true ); ?>
								/>
								<?php esc_html_e( 'Avoid idioms and culture-specific phrases', 'wpshadow' ); ?>
							</label>
							<p class="description">
								<?php
								esc_html_e(
									'When enabled, we\'ll avoid phrases like "piece of cake" or "break a leg" that might not translate well. Instead, we\'ll use clear, direct language that works globally.',
									'wpshadow'
								);
								?>
							</p>
							<div class="wpshadow-example-box">
								<strong><?php esc_html_e( 'Example:', 'wpshadow' ); ?></strong><br>
								<span class="wpshadow-example-old-text">
									<?php esc_html_e( '"This feature is a piece of cake"', 'wpshadow' ); ?>
								</span><br>
								<span class="wpshadow-example-new-text">
									<?php esc_html_e( '"This feature is easy to use"', 'wpshadow' ); ?>
								</span>
							</div>
						</td>
					</tr>
				</table>

				<?php submit_button( __( 'Save Cultural Settings', 'wpshadow' ) ); ?>
			</form>

			<div class="wpshadow-settings-footer">
				<h3><?php esc_html_e( 'Why This Matters', 'wpshadow' ); ?></h3>
				<p>
					<?php
					esc_html_e(
						'WPShadow is used by people in 180+ countries. These settings help us adapt to your cultural context so the plugin feels natural to you, not like it was designed only for English-speaking countries.',
						'wpshadow'
					);
					?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Philosophy Alignment:', 'wpshadow' ); ?></strong>
					<?php esc_html_e( '🌐 Culturally Respectful (CANON Pillar)', 'wpshadow' ); ?>
				</p>
			</div>
		</div>

		<?php
	}
}
