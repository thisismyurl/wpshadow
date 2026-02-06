<?php
/**
 * General Settings Page
 *
 * Provides general plugin configuration options including cache settings
 * and default behavior preferences.
 *
 * @package    WPShadow
 * @subpackage Settings
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Admin\Pages;

use WPShadow\Core\Options_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * General Settings Page
 *
 * @since 1.6030.2148
 */
class General_Settings_Page {

	/**
	 * Render the general settings page
	 *
	 * @since  1.6030.2148
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}

		?>
		<div class="wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'General Settings', 'wpshadow' ),
				__( 'Configure general plugin behavior and preferences.', 'wpshadow' ),
				'dashicons-admin-generic'
			);
			?>

			<form method="post" action="options.php" class="wps-settings-form">
				<?php settings_fields( 'wpshadow_settings' ); ?>

				<!-- Caching Section -->
				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Caching', 'wpshadow' ),
						'description' => __( 'Control how diagnostic results are cached to improve performance.', 'wpshadow' ),
						'icon'        => 'dashicons-performance',
						'body'        => function() {
							?>
							<div class="wps-form-group">
								<div class="wps-flex wps-gap-6 wps-items-start wps-justify-between">
									<label class="wps-toggle" for="wpshadow_cache_enabled">
										<input
											type="checkbox"
											id="wpshadow_cache_enabled"
											name="wpshadow_cache_enabled"
											value="1"
											<?php checked( get_option( 'wpshadow_cache_enabled', true ) ); ?>
										/>
										<span class="wps-toggle-slider"></span>
										<?php esc_html_e( 'Enable Result Caching', 'wpshadow' ); ?>
									</label>
									<p class="wps-form-description wps-m-0">
										<?php esc_html_e( 'Cache diagnostic results to reduce server load. Cache is automatically cleared when settings change.', 'wpshadow' ); ?>
									</p>
								</div>
							</div>

							<div class="wps-form-group wps-mt-4">
								<label for="wpshadow_cache_duration" class="wps-form-label">
									<?php esc_html_e( 'Cache Duration', 'wpshadow' ); ?>
								</label>
								<select
									id="wpshadow_cache_duration"
									name="wpshadow_cache_duration"
									class="wps-form-control"
								>
									<option value="300" <?php selected( get_option( 'wpshadow_cache_duration', 3600 ), 300 ); ?>>
										<?php esc_html_e( '5 minutes', 'wpshadow' ); ?>
									</option>
									<option value="900" <?php selected( get_option( 'wpshadow_cache_duration', 3600 ), 900 ); ?>>
										<?php esc_html_e( '15 minutes', 'wpshadow' ); ?>
									</option>
									<option value="1800" <?php selected( get_option( 'wpshadow_cache_duration', 3600 ), 1800 ); ?>>
										<?php esc_html_e( '30 minutes', 'wpshadow' ); ?>
									</option>
									<option value="3600" <?php selected( get_option( 'wpshadow_cache_duration', 3600 ), 3600 ); ?>>
										<?php esc_html_e( '1 hour (default)', 'wpshadow' ); ?>
									</option>
									<option value="7200" <?php selected( get_option( 'wpshadow_cache_duration', 3600 ), 7200 ); ?>>
										<?php esc_html_e( '2 hours', 'wpshadow' ); ?>
									</option>
									<option value="14400" <?php selected( get_option( 'wpshadow_cache_duration', 3600 ), 14400 ); ?>>
										<?php esc_html_e( '4 hours', 'wpshadow' ); ?>
									</option>
									<option value="28800" <?php selected( get_option( 'wpshadow_cache_duration', 3600 ), 28800 ); ?>>
										<?php esc_html_e( '8 hours', 'wpshadow' ); ?>
									</option>
									<option value="86400" <?php selected( get_option( 'wpshadow_cache_duration', 3600 ), 86400 ); ?>>
										<?php esc_html_e( '24 hours (1 day)', 'wpshadow' ); ?>
									</option>
								</select>
								<span class="wps-help-text">
									<?php esc_html_e( 'How long to keep cached results before refreshing. Choose a shorter duration for more frequent updates, or longer for better performance.', 'wpshadow' ); ?>
								</span>
							</div>
							<?php
						},
					)
				);
				?>

				<?php
				// Note: Visual Comparison settings have been removed as the feature is not yet implemented.
				// When the visual comparison feature is added to Reports, the settings should be co-located there.
				?>

				<!-- Action Buttons -->
				<?php
				wpshadow_render_card(
					array(
						'card_class' => 'wps-card--action',
						'body_class' => 'wps-card-body wps-flex wps-gap-3',
						'body'       => function() {
							?>
							<?php submit_button( __( 'Save Changes', 'wpshadow' ), 'primary', 'submit', false ); ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings' ) ); ?>" class="wps-btn wps-btn--secondary">
								<?php esc_html_e( 'Cancel', 'wpshadow' ); ?>
							</a>
							<?php
						},
					)
				);
				?>
			</form>

			<!-- Page-Specific Activity History Section -->
			<?php wpshadow_render_activity_log( 'settings', 10 ); ?>
		</div>
		<?php
	}
}
