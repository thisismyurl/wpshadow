<?php
/**
 * General Settings Page
 *
 * Provides general plugin configuration options including cache settings,
 * visual comparison dimensions, and default behavior preferences.
 *
 * @package    WPShadow
 * @subpackage Settings
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Settings;

use WPShadow\Core\Options_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * General Settings Page
 *
 * @since 1.2601.2148
 */
class General_Settings_Page {

	/**
	 * Render the general settings page
	 *
	 * @since  1.2601.2148
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}

		?>
		<div class="wps-page-container">
			<div class="wps-page-header">
				<h1 class="wps-page-title">
					<span class="dashicons dashicons-admin-generic"></span>
					<?php esc_html_e( 'General Settings', 'wpshadow' ); ?>
				</h1>
				<p class="wps-page-subtitle">
					<?php esc_html_e( 'Configure general plugin behavior and preferences.', 'wpshadow' ); ?>
				</p>
			</div>

			<form method="post" action="options.php" class="wps-settings-form">
				<?php settings_fields( 'wpshadow_settings' ); ?>

				<!-- Caching Section -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-performance"></span>
							<?php esc_html_e( 'Caching', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'Control how diagnostic results are cached to improve performance.', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="wps-card-body">
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
							<div class="wps-range-group">
								<div class="wps-range-header">
									<label for="wpshadow_cache_duration" class="wps-form-label">
										<?php esc_html_e( 'Cache Duration', 'wpshadow' ); ?>
									</label>
									<span class="wps-range-value" id="wpshadow_cache_duration_display">
										<?php echo esc_html( get_option( 'wpshadow_cache_duration', 3600 ) ); ?> <?php esc_html_e( 'seconds', 'wpshadow' ); ?>
									</span>
								</div>
								<div class="wps-range-wrapper">
									<input
										type="range"
										id="wpshadow_cache_duration"
										name="wpshadow_cache_duration"
										value="<?php echo esc_attr( get_option( 'wpshadow_cache_duration', 3600 ) ); ?>"
										min="60"
										max="86400"
										step="60"
										class="wps-range"
										data-suffix=" seconds"
										aria-valuemin="60"
										aria-valuemax="86400"
										aria-valuenow="<?php echo esc_attr( get_option( 'wpshadow_cache_duration', 3600 ) ); ?>"
										aria-valuetext="<?php echo esc_attr( get_option( 'wpshadow_cache_duration', 3600 ) ); ?> seconds"
									/>
								</div>
								<span class="wps-help-text">
									<?php esc_html_e( 'How long to keep cached results before refreshing. Default is 1 hour (3600 seconds).', 'wpshadow' ); ?>
								</span>
							</div>
						</div>
					</div>
				</div>

				<!-- Visual Comparison Section -->
				<div class="wps-card">
					<div class="wps-card-header">
						<h3 class="wps-card-title">
							<span class="dashicons dashicons-format-image"></span>
							<?php esc_html_e( 'Visual Comparisons', 'wpshadow' ); ?>
						</h3>
						<p class="wps-card-description">
							<?php esc_html_e( 'Configure screenshot dimensions for visual before/after comparisons.', 'wpshadow' ); ?>
						</p>
					</div>
					<div class="wps-card-body">
						<div class="wps-form-group">
							<div class="wps-range-group">
								<div class="wps-range-header">
									<label for="wpshadow_visual_comparison_width" class="wps-form-label">
										<?php esc_html_e( 'Screenshot Width', 'wpshadow' ); ?>
									</label>
									<span class="wps-range-value" id="wpshadow_visual_comparison_width_display">
										<?php echo esc_html( get_option( 'wpshadow_visual_comparison_width', 1200 ) ); ?>px
									</span>
								</div>
								<div class="wps-range-wrapper">
									<input
										type="range"
										id="wpshadow_visual_comparison_width"
										name="wpshadow_visual_comparison_width"
										value="<?php echo esc_attr( get_option( 'wpshadow_visual_comparison_width', 1200 ) ); ?>"
										min="640"
										max="2560"
										step="1"
										class="wps-range"
										data-suffix="px"
										aria-valuemin="640"
										aria-valuemax="2560"
										aria-valuenow="<?php echo esc_attr( get_option( 'wpshadow_visual_comparison_width', 1200 ) ); ?>"
										aria-valuetext="<?php echo esc_attr( get_option( 'wpshadow_visual_comparison_width', 1200 ) ); ?>px"
									/>
								</div>
								<span class="wps-help-text">
									<?php esc_html_e( 'Recommended: 1200px for desktop layouts. Range: 640-2560px.', 'wpshadow' ); ?>
								</span>
							</div>
						</div>

						<div class="wps-form-group wps-mt-4">
							<div class="wps-range-group">
								<div class="wps-range-header">
									<label for="wpshadow_visual_comparison_height" class="wps-form-label">
										<?php esc_html_e( 'Screenshot Height', 'wpshadow' ); ?>
									</label>
									<span class="wps-range-value" id="wpshadow_visual_comparison_height_display">
										<?php echo esc_html( get_option( 'wpshadow_visual_comparison_height', 800 ) ); ?>px
									</span>
								</div>
								<div class="wps-range-wrapper">
									<input
										type="range"
										id="wpshadow_visual_comparison_height"
										name="wpshadow_visual_comparison_height"
										value="<?php echo esc_attr( get_option( 'wpshadow_visual_comparison_height', 800 ) ); ?>"
										min="480"
										max="2160"
										step="1"
										class="wps-range"
										data-suffix="px"
										aria-valuemin="480"
										aria-valuemax="2160"
										aria-valuenow="<?php echo esc_attr( get_option( 'wpshadow_visual_comparison_height', 800 ) ); ?>"
										aria-valuetext="<?php echo esc_attr( get_option( 'wpshadow_visual_comparison_height', 800 ) ); ?>px"
									/>
								</div>
								<span class="wps-help-text">
									<?php esc_html_e( 'Recommended: 800px for good detail. Range: 480-2160px.', 'wpshadow' ); ?>
								</span>
							</div>
						</div>
					</div>
				</div>

				<!-- Action Buttons -->
				<div class="wps-card wps-card--action">
					<div class="wps-card-body wps-flex wps-gap-3">
						<?php submit_button( __( 'Save Changes', 'wpshadow' ), 'primary', 'submit', false ); ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings' ) ); ?>" class="wps-btn wps-btn--secondary">
							<?php esc_html_e( 'Cancel', 'wpshadow' ); ?>
						</a>
					</div>
				</div>
			</form>

			<script>
			jQuery(function($) {
				var rangeMap = [
					{ input: '#wpshadow_cache_duration', display: '#wpshadow_cache_duration_display', suffix: ' <?php echo esc_js( __( 'seconds', 'wpshadow' ) ); ?>' },
					{ input: '#wpshadow_visual_comparison_width', display: '#wpshadow_visual_comparison_width_display', suffix: 'px' },
					{ input: '#wpshadow_visual_comparison_height', display: '#wpshadow_visual_comparison_height_display', suffix: 'px' }
				];

				rangeMap.forEach(function(item) {
					var $input = $(item.input);
					var $display = $(item.display);
					if (!$input.length || !$display.length) {
						return;
					}
					var update = function() {
						$display.text($input.val() + item.suffix);
						$input.attr('aria-valuenow', $input.val());
						$input.attr('aria-valuetext', $input.val() + item.suffix);
					};
					$input.on('input change', update);
					update();
				});
			});
			</script>

			<!-- Page-Specific Activity History Section -->
			<div class="wps-section-divider">
				<?php
				if ( function_exists( 'wpshadow_render_page_activities' ) ) {
					wpshadow_render_page_activities( 'settings', 10 );
				}
				?>
			</div>
		</div>
		<?php
	}
}
