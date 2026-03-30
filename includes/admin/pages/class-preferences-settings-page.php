<?php
/**
 * Preferences Settings Page
 *
 * Combines KPI tracking, learning style, regional/language, and
 * reliability preferences into a single organised settings page.
 *
 * @package    WPShadow
 * @subpackage Settings
 * @since      1.6089.0001
 */

declare(strict_types=1);

namespace WPShadow\Admin\Pages;

use WPShadow\Core\Settings_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Preferences_Settings_Page Class
 *
 * Renders four grouped preference sections – Impact & Value Tracking,
 * Learning Style, Regional & Language, and Reliability – each saved
 * independently through its own settings group.
 *
 * @since 1.6089.0001
 */
class Preferences_Settings_Page {

	/**
	 * Render the preferences settings page.
	 *
	 * @since  1.6089.0001
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
		}

		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Preferences', 'wpshadow' ),
				__( 'Personalise how WPShadow tracks value, presents information, and behaves under load.', 'wpshadow' ),
				'dashicons-admin-users'
			);
			?>

			<!-- ── Section 1: Impact & Value Tracking ──────────────────── -->
			<form method="post" action="options.php" class="wps-settings-form">
				<?php settings_fields( 'wpshadow_kpi_settings' ); ?>

				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Impact & Value Tracking', 'wpshadow' ),
						'description' => __( 'See the measurable difference WPShadow makes on your site.', 'wpshadow' ),
						'icon'        => 'dashicons-chart-bar',
						'body'        => function() {
							?>
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'wpshadow_track_feature_usage',
									'name'        => 'wpshadow_track_feature_usage',
									'label'       => __( 'Track Which Features Help You Most', 'wpshadow' ),
									'helper_text' => __( 'Log which diagnostics, treatments, and reports you use. Helps us focus on the features that matter most to you (anonymous).', 'wpshadow' ),
									'checked'     => (bool) get_option( 'wpshadow_track_feature_usage', true ),
								)
							);

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'wpshadow_show_impact_metrics',
									'name'        => 'wpshadow_show_impact_metrics',
									'label'       => __( 'Show Time Saved & Performance Gains', 'wpshadow' ),
									'helper_text' => __( 'Display metrics like "This fix saved you 2 hours" or "Your site is loading 30% faster." Great for demonstrating results to clients.', 'wpshadow' ),
									'checked'     => (bool) get_option( 'wpshadow_show_impact_metrics', true ),
								)
							);

							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'wpshadow_enable_value_tracking',
									'name'        => 'wpshadow_enable_value_tracking',
									'label'       => __( 'Calculate Value Delivered', 'wpshadow' ),
									'helper_text' => __( 'Estimate money saved and issues prevented — like avoided developer costs or security incidents. Useful for ROI reporting.', 'wpshadow' ),
									'checked'     => (bool) get_option( 'wpshadow_enable_value_tracking', true ),
								)
							);
							?>
							<?php
						},
					)
				);
				?>

				<?php
				wpshadow_render_card(
					array(
						'card_class' => 'wps-card--action',
						'body_class' => 'wps-card-body wps-flex wps-gap-3',
						'body'       => function() {
							submit_button( __( 'Save Impact & Value Settings', 'wpshadow' ), 'primary', 'submit', false );
						},
					)
				);
				?>
			</form>

			<!-- ── Section 2: Learning Style ──────────────────────────── -->
			<form method="post" action="options.php" class="wps-settings-form">
				<?php settings_fields( 'wpshadow_learning_settings' ); ?>

				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Learning Style', 'wpshadow' ),
						'description' => __( 'Tell us how you prefer to learn and we\'ll adapt WPShadow\'s guidance to match your style.', 'wpshadow' ),
						'icon'        => 'dashicons-welcome-learn-more',
						'body'        => function() {
							?>
							<div class="wps-form-group">
								<label for="wpshadow_preferred_learning_style" class="wps-form-label">
									<?php esc_html_e( 'Preferred Learning Format', 'wpshadow' ); ?>
								</label>
								<select
									id="wpshadow_preferred_learning_style"
									name="wpshadow_preferred_learning_style"
									class="wps-input"
								>
									<option value="mixed" <?php selected( get_option( 'wpshadow_preferred_learning_style', 'mixed' ), 'mixed' ); ?>>
										<?php esc_html_e( 'Show Me Everything (text, videos, examples)', 'wpshadow' ); ?>
									</option>
									<option value="text" <?php selected( get_option( 'wpshadow_preferred_learning_style', 'mixed' ), 'text' ); ?>>
										<?php esc_html_e( 'Text & Written Guides (I prefer reading)', 'wpshadow' ); ?>
									</option>
									<option value="video" <?php selected( get_option( 'wpshadow_preferred_learning_style', 'mixed' ), 'video' ); ?>>
										<?php esc_html_e( 'Videos & Visual Learning (I learn by watching)', 'wpshadow' ); ?>
									</option>
									<option value="interactive" <?php selected( get_option( 'wpshadow_preferred_learning_style', 'mixed' ), 'interactive' ); ?>>
										<?php esc_html_e( 'Hands-On Practice (I learn by doing)', 'wpshadow' ); ?>
									</option>
								</select>
								<p class="wps-form-description">
									<?php esc_html_e( 'WPShadow will prioritise this format when showing help content, documentation, and tutorials.', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-mt-4">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo \WPShadow\Helpers\Form_Controls::toggle_switch(
									array(
										'id'          => 'wpshadow_step_by_step_mode',
										'name'        => 'wpshadow_step_by_step_mode',
										'label'       => __( 'Step-by-Step Guidance', 'wpshadow' ),
										'helper_text' => __( 'Break complex tasks into smaller steps with progress tracking. Lets you pause and resume long operations.', 'wpshadow' ),
										'checked'     => (bool) get_option( 'wpshadow_step_by_step_mode', false ),
									)
								);

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo \WPShadow\Helpers\Form_Controls::toggle_switch(
									array(
										'id'          => 'wpshadow_show_examples',
										'name'        => 'wpshadow_show_examples',
										'label'       => __( 'Show Real-World Examples', 'wpshadow' ),
										'helper_text' => __( 'Include practical examples alongside explanations — like "This diagnostic helped a restaurant site load 3× faster."', 'wpshadow' ),
										'checked'     => (bool) get_option( 'wpshadow_show_examples', true ),
									)
								);

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo \WPShadow\Helpers\Form_Controls::toggle_switch(
									array(
										'id'          => 'wpshadow_adhd_friendly_mode',
										'name'        => 'wpshadow_adhd_friendly_mode',
										'label'       => __( 'ADHD-Friendly Mode', 'wpshadow' ),
										'helper_text' => __( 'Reduces visual clutter, highlights the most important action, shows progress bars, and auto-saves your work every 30 seconds.', 'wpshadow' ),
										'checked'     => (bool) get_option( 'wpshadow_adhd_friendly_mode', false ),
									)
								);

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo \WPShadow\Helpers\Form_Controls::toggle_switch(
									array(
										'id'          => 'wpshadow_dyslexia_friendly_font',
										'name'        => 'wpshadow_dyslexia_friendly_font',
										'label'       => __( 'Dyslexia-Friendly Font', 'wpshadow' ),
										'helper_text' => __( 'Switches the WPShadow interface to OpenDyslexic — a font designed to make letters easier to distinguish and reduce reading errors.', 'wpshadow' ),
										'checked'     => (bool) get_option( 'wpshadow_dyslexia_friendly_font', false ),
									)
								);
								?>
							</div>
							<?php
						},
					)
				);
				?>

				<?php
				wpshadow_render_card(
					array(
						'card_class' => 'wps-card--action',
						'body_class' => 'wps-card-body wps-flex wps-gap-3',
						'body'       => function() {
							submit_button( __( 'Save Learning Settings', 'wpshadow' ), 'primary', 'submit', false );
						},
					)
				);
				?>
			</form>

			<!-- ── Section 3: Regional & Language ─────────────────────── -->
			<form method="post" action="options.php" class="wps-settings-form">
				<?php settings_fields( 'wpshadow_cultural_settings' ); ?>

				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Regional & Language', 'wpshadow' ),
						'description' => __( 'Set your preferred date, time, and number formats so WPShadow feels natural wherever you are.', 'wpshadow' ),
						'icon'        => 'dashicons-translation',
						'body'        => function() {
							?>
							<div class="wps-form-group">
								<label for="wpshadow_date_format_preference" class="wps-form-label">
									<?php esc_html_e( 'Date Format', 'wpshadow' ); ?>
								</label>
								<select
									id="wpshadow_date_format_preference"
									name="wpshadow_date_format_preference"
									class="wps-input"
								>
									<option value="wordpress" <?php selected( get_option( 'wpshadow_date_format_preference', 'wordpress' ), 'wordpress' ); ?>>
										<?php
										printf(
											/* translators: %s: date formatted with current WordPress setting */
											esc_html__( 'Use WordPress Setting (%s)', 'wpshadow' ),
											esc_html( date_i18n( get_option( 'date_format' ), time() ) )
										);
										?>
									</option>
									<option value="iso8601" <?php selected( get_option( 'wpshadow_date_format_preference', 'wordpress' ), 'iso8601' ); ?>>
										<?php
										printf(
											/* translators: %s: example ISO 8601 date */
											esc_html__( 'International Standard — ISO 8601 (%s)', 'wpshadow' ),
											esc_html( gmdate( 'Y-m-d', time() ) )
										);
										?>
									</option>
									<option value="us" <?php selected( get_option( 'wpshadow_date_format_preference', 'wordpress' ), 'us' ); ?>>
										<?php
										printf(
											/* translators: %s: example US date */
											esc_html__( 'US Format (%s)', 'wpshadow' ),
											esc_html( gmdate( 'm/d/Y', time() ) )
										);
										?>
									</option>
									<option value="eu" <?php selected( get_option( 'wpshadow_date_format_preference', 'wordpress' ), 'eu' ); ?>>
										<?php
										printf(
											/* translators: %s: example European date */
											esc_html__( 'European Format (%s)', 'wpshadow' ),
											esc_html( gmdate( 'd/m/Y', time() ) )
										);
										?>
									</option>
								</select>
							</div>

							<div class="wps-form-group wps-mt-4">
								<label for="wpshadow_time_format_preference" class="wps-form-label">
									<?php esc_html_e( 'Time Format', 'wpshadow' ); ?>
								</label>
								<select
									id="wpshadow_time_format_preference"
									name="wpshadow_time_format_preference"
									class="wps-input"
								>
									<option value="wordpress" <?php selected( get_option( 'wpshadow_time_format_preference', 'wordpress' ), 'wordpress' ); ?>>
										<?php
										printf(
											/* translators: %s: time formatted with current WordPress setting */
											esc_html__( 'Use WordPress Setting (%s)', 'wpshadow' ),
											esc_html( date_i18n( get_option( 'time_format' ), time() ) )
										);
										?>
									</option>
									<option value="12h" <?php selected( get_option( 'wpshadow_time_format_preference', 'wordpress' ), '12h' ); ?>>
										<?php
										printf(
											/* translators: %s: example 12-hour time */
											esc_html__( '12-Hour Clock (%s)', 'wpshadow' ),
											esc_html( gmdate( 'g:i a', time() ) )
										);
										?>
									</option>
									<option value="24h" <?php selected( get_option( 'wpshadow_time_format_preference', 'wordpress' ), '24h' ); ?>>
										<?php
										printf(
											/* translators: %s: example 24-hour time */
											esc_html__( '24-Hour Clock (%s)', 'wpshadow' ),
											esc_html( gmdate( 'H:i', time() ) )
										);
										?>
									</option>
								</select>
							</div>

							<div class="wps-form-group wps-mt-4">
								<label for="wpshadow_number_format_preference" class="wps-form-label">
									<?php esc_html_e( 'Number Format', 'wpshadow' ); ?>
								</label>
								<select
									id="wpshadow_number_format_preference"
									name="wpshadow_number_format_preference"
									class="wps-input"
								>
									<option value="locale" <?php selected( get_option( 'wpshadow_number_format_preference', 'locale' ), 'locale' ); ?>>
										<?php esc_html_e( 'Auto-Detect from Your Language', 'wpshadow' ); ?>
									</option>
									<option value="us" <?php selected( get_option( 'wpshadow_number_format_preference', 'locale' ), 'us' ); ?>>
										<?php esc_html_e( 'US/UK Style — 1,000.50', 'wpshadow' ); ?>
									</option>
									<option value="eu" <?php selected( get_option( 'wpshadow_number_format_preference', 'locale' ), 'eu' ); ?>>
										<?php esc_html_e( 'European Style — 1.000,50', 'wpshadow' ); ?>
									</option>
								</select>
							</div>

							<div class="wps-form-group wps-mt-4">
								<label for="wpshadow_rtl_interface" class="wps-form-label">
									<?php esc_html_e( 'Text Direction', 'wpshadow' ); ?>
								</label>
								<select
									id="wpshadow_rtl_interface"
									name="wpshadow_rtl_interface"
									class="wps-input"
								>
									<option value="auto" <?php selected( get_option( 'wpshadow_rtl_interface', 'auto' ), 'auto' ); ?>>
										<?php esc_html_e( 'Auto-Detect from Language', 'wpshadow' ); ?>
									</option>
									<option value="force_ltr" <?php selected( get_option( 'wpshadow_rtl_interface', 'auto' ), 'force_ltr' ); ?>>
										<?php esc_html_e( 'Always Left-to-Right (English, Spanish, etc.)', 'wpshadow' ); ?>
									</option>
									<option value="force_rtl" <?php selected( get_option( 'wpshadow_rtl_interface', 'auto' ), 'force_rtl' ); ?>>
										<?php esc_html_e( 'Always Right-to-Left (Arabic, Hebrew, etc.)', 'wpshadow' ); ?>
									</option>
								</select>
							</div>

							<div class="wps-form-group wps-mt-4">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo \WPShadow\Helpers\Form_Controls::toggle_switch(
									array(
										'id'          => 'wpshadow_avoid_idioms',
										'name'        => 'wpshadow_avoid_idioms',
										'label'       => __( 'Plain Language Mode', 'wpshadow' ),
										'helper_text' => __( 'Avoid culture-specific phrases and idioms (like "piece of cake") that may not translate well. Uses clear, direct language instead.', 'wpshadow' ),
										'checked'     => (bool) get_option( 'wpshadow_avoid_idioms', false ),
									)
								);
								?>
							</div>
							<?php
						},
					)
				);
				?>

				<?php
				wpshadow_render_card(
					array(
						'card_class' => 'wps-card--action',
						'body_class' => 'wps-card-body wps-flex wps-gap-3',
						'body'       => function() {
							submit_button( __( 'Save Regional Settings', 'wpshadow' ), 'primary', 'submit', false );
						},
					)
				);
				?>
			</form>

			<!-- ── Section 4: Reliability & Error Handling ─────────────── -->
			<form method="post" action="options.php" class="wps-settings-form">
				<?php settings_fields( 'wpshadow_defensive_settings' ); ?>

				<?php
				wpshadow_render_card(
					array(
						'title'       => __( 'Reliability & Error Handling', 'wpshadow' ),
						'description' => __( 'Configure how WPShadow handles network failures, timeouts, and unexpected errors so your work is always protected.', 'wpshadow' ),
						'icon'        => 'dashicons-shield',
						'body'        => function() {
							?>
							<div class="wps-form-group">
								<label for="wpshadow_autosave_frequency" class="wps-form-label">
									<?php esc_html_e( 'Auto-Save Frequency', 'wpshadow' ); ?>
								</label>
								<div class="wps-input-group">
									<input
										type="number"
										id="wpshadow_autosave_frequency"
										name="wpshadow_autosave_frequency"
										value="<?php echo esc_attr( get_option( 'wpshadow_autosave_frequency', 30 ) ); ?>"
										min="10"
										max="300"
										step="5"
										class="wps-input wps-w-32"
									/>
									<span class="wps-input-addon"><?php esc_html_e( 'seconds', 'wpshadow' ); ?></span>
								</div>
								<p class="wps-form-description">
									<?php esc_html_e( 'How often WPShadow saves your work automatically to prevent data loss if your browser closes unexpectedly. Range: 10–300 seconds.', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-mt-4">
								<label for="wpshadow_operation_timeout" class="wps-form-label">
									<?php esc_html_e( 'Operation Timeout', 'wpshadow' ); ?>
								</label>
								<div class="wps-input-group">
									<input
										type="number"
										id="wpshadow_operation_timeout"
										name="wpshadow_operation_timeout"
										value="<?php echo esc_attr( get_option( 'wpshadow_operation_timeout', 30 ) ); ?>"
										min="5"
										max="300"
										step="5"
										class="wps-input wps-w-32"
									/>
									<span class="wps-input-addon"><?php esc_html_e( 'seconds', 'wpshadow' ); ?></span>
								</div>
								<p class="wps-form-description">
									<?php esc_html_e( 'Maximum time to wait for any operation before giving up. Increase on slower servers to prevent timeouts. Range: 5–300 seconds.', 'wpshadow' ); ?>
								</p>
							</div>

							<div class="wps-form-group wps-mt-4">
								<?php
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo \WPShadow\Helpers\Form_Controls::toggle_switch(
									array(
										'id'          => 'wpshadow_retry_failed_operations',
										'name'        => 'wpshadow_retry_failed_operations',
										'label'       => __( 'Auto-Retry Failed Operations', 'wpshadow' ),
										'helper_text' => __( 'Automatically retry failed network requests up to 3 times (with short pauses between attempts) before giving up.', 'wpshadow' ),
										'checked'     => (bool) get_option( 'wpshadow_retry_failed_operations', true ),
									)
								);

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo \WPShadow\Helpers\Form_Controls::toggle_switch(
									array(
										'id'          => 'wpshadow_use_stale_cache',
										'name'        => 'wpshadow_use_stale_cache',
										'label'       => __( 'Show Cached Data When Fresh Data is Unavailable', 'wpshadow' ),
										'helper_text' => __( 'If WPShadow can\'t fetch the latest data (e.g., network issue), show the last known data with a timestamp rather than an error.', 'wpshadow' ),
										'checked'     => (bool) get_option( 'wpshadow_use_stale_cache', true ),
									)
								);

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo \WPShadow\Helpers\Form_Controls::toggle_switch(
									array(
										'id'          => 'wpshadow_enable_offline_mode',
										'name'        => 'wpshadow_enable_offline_mode',
										'label'       => __( 'Offline Mode', 'wpshadow' ),
										'helper_text' => __( 'Queue operations locally when offline and automatically sync them once your connection returns. Local diagnostics continue to work.', 'wpshadow' ),
										'checked'     => (bool) get_option( 'wpshadow_enable_offline_mode', false ),
									)
								);

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo \WPShadow\Helpers\Form_Controls::toggle_switch(
									array(
										'id'          => 'wpshadow_graceful_error_display',
										'name'        => 'wpshadow_graceful_error_display',
										'label'       => __( 'Show User-Friendly Error Messages', 'wpshadow' ),
										'helper_text' => __( 'Translate technical errors into plain-English explanations with suggested next steps. Technical details are still logged for developers.', 'wpshadow' ),
										'checked'     => (bool) get_option( 'wpshadow_graceful_error_display', true ),
									)
								);
								?>
							</div>
							<?php
						},
					)
				);
				?>

				<?php
				wpshadow_render_card(
					array(
						'card_class' => 'wps-card--action',
						'body_class' => 'wps-card-body wps-flex wps-gap-3',
						'body'       => function() {
							submit_button( __( 'Save Reliability Settings', 'wpshadow' ), 'primary', 'submit', false );
						},
					)
				);
				?>
			</form>

		</div>
		<?php
	}
}
