<?php
/**
 * Mobile-Friendliness Test Page View
 *
 * @package WPShadow
 * @since 1.2601.75001
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$options = $options ?? array();
?>

<div class="wrap wpshadow-mobile-test">
	<h1><?php esc_html_e( 'Mobile-Friendliness Test', 'plugin-wpshadow' ); ?></h1>
	
	<p class="description">
		<?php esc_html_e( 'Test your website\'s mobile-friendliness by analyzing viewport configuration, touch target sizes, text readability, and layout responsiveness.', 'plugin-wpshadow' ); ?>
	</p>

	<div class="wpshadow-mobile-test-container">
		<div class="wpshadow-test-controls">
			<h2><?php esc_html_e( 'Test Configuration', 'plugin-wpshadow' ); ?></h2>
			
			<form method="post" action="options.php" class="wpshadow-mobile-options-form">
				<?php settings_fields( 'wpshadow_mobile_test_options_group' ); ?>
				
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'URL to Test', 'plugin-wpshadow' ); ?></th>
							<td>
								<input type="url" id="wpshadow-test-url" class="regular-text" value="<?php echo esc_attr( home_url() ); ?>" placeholder="<?php echo esc_attr( home_url() ); ?>" />
								<p class="description"><?php esc_html_e( 'Enter a URL to test, or leave empty to test your homepage.', 'plugin-wpshadow' ); ?></p>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php esc_html_e( 'Test Options', 'plugin-wpshadow' ); ?></th>
							<td>
								<fieldset>
									<label>
										<input type="checkbox" name="wpshadow_mobile_test_options[check_viewport]" value="1" <?php checked( $options['check_viewport'] ?? true, true ); ?> />
										<?php esc_html_e( 'Check viewport configuration', 'plugin-wpshadow' ); ?>
									</label>
									<br />
									<label>
										<input type="checkbox" name="wpshadow_mobile_test_options[check_touch_targets]" value="1" <?php checked( $options['check_touch_targets'] ?? true, true ); ?> />
										<?php esc_html_e( 'Check touch target sizes', 'plugin-wpshadow' ); ?>
									</label>
									<br />
									<label>
										<input type="checkbox" name="wpshadow_mobile_test_options[check_font_sizes]" value="1" <?php checked( $options['check_font_sizes'] ?? true, true ); ?> />
										<?php esc_html_e( 'Check text readability', 'plugin-wpshadow' ); ?>
									</label>
									<br />
									<label>
										<input type="checkbox" name="wpshadow_mobile_test_options[check_tap_spacing]" value="1" <?php checked( $options['check_tap_spacing'] ?? true, true ); ?> />
										<?php esc_html_e( 'Check tap spacing', 'plugin-wpshadow' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php esc_html_e( 'Minimum Touch Target Size', 'plugin-wpshadow' ); ?></th>
							<td>
								<input type="number" name="wpshadow_mobile_test_options[min_touch_size]" value="<?php echo esc_attr( $options['min_touch_size'] ?? 44 ); ?>" min="32" max="64" class="small-text" />
								<span class="description"><?php esc_html_e( 'pixels (W3C recommends 44px minimum)', 'plugin-wpshadow' ); ?></span>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php esc_html_e( 'Minimum Font Size', 'plugin-wpshadow' ); ?></th>
							<td>
								<input type="number" name="wpshadow_mobile_test_options[min_font_size]" value="<?php echo esc_attr( $options['min_font_size'] ?? 12 ); ?>" min="10" max="16" class="small-text" />
								<span class="description"><?php esc_html_e( 'pixels', 'plugin-wpshadow' ); ?></span>
							</td>
						</tr>
						
						<tr>
							<th scope="row"><?php esc_html_e( 'Auto-check on Save', 'plugin-wpshadow' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="wpshadow_mobile_test_options[auto_check_on_save]" value="1" <?php checked( $options['auto_check_on_save'] ?? false, true ); ?> />
									<?php esc_html_e( 'Automatically run mobile-friendliness test when posts are saved', 'plugin-wpshadow' ); ?>
								</label>
							</td>
						</tr>
					</tbody>
				</table>
				
				<?php submit_button( __( 'Save Settings', 'plugin-wpshadow' ), 'secondary' ); ?>
			</form>
			
			<div class="wpshadow-test-actions">
				<button type="button" id="wpshadow-run-mobile-test" class="button button-primary button-large">
					<span class="dashicons dashicons-smartphone"></span>
					<?php esc_html_e( 'Run Mobile Test', 'plugin-wpshadow' ); ?>
				</button>
			</div>
		</div>

		<div class="wpshadow-test-results" id="wpshadow-test-results" style="display: none;">
			<h2><?php esc_html_e( 'Test Results', 'plugin-wpshadow' ); ?></h2>
			
			<div class="wpshadow-test-loading" id="wpshadow-test-loading">
				<div class="spinner is-active"></div>
				<p><?php esc_html_e( 'Running mobile-friendliness test...', 'plugin-wpshadow' ); ?></p>
			</div>
			
			<div class="wpshadow-test-report" id="wpshadow-test-report" style="display: none;">
				<div class="wpshadow-mobile-score-large">
					<div class="score-circle" id="score-circle">
						<svg viewBox="0 0 100 100">
							<circle class="score-bg" cx="50" cy="50" r="45" fill="none" stroke="#eee" stroke-width="10"></circle>
							<circle class="score-fill" id="score-fill" cx="50" cy="50" r="45" fill="none" stroke="#0073aa" stroke-width="10" stroke-dasharray="283" stroke-dashoffset="283"></circle>
						</svg>
						<div class="score-text">
							<span class="score-value" id="score-value">0</span>
							<span class="score-label">/100</span>
						</div>
					</div>
					<p class="score-status" id="score-status"></p>
				</div>
				
				<div class="wpshadow-test-details">
					<div class="test-section" id="test-issues" style="display: none;">
						<h3 class="test-section-title">
							<span class="dashicons dashicons-warning"></span>
							<?php esc_html_e( 'Issues Found', 'plugin-wpshadow' ); ?>
						</h3>
						<ul class="test-list issues-list"></ul>
					</div>
					
					<div class="test-section" id="test-warnings" style="display: none;">
						<h3 class="test-section-title">
							<span class="dashicons dashicons-info"></span>
							<?php esc_html_e( 'Warnings', 'plugin-wpshadow' ); ?>
						</h3>
						<ul class="test-list warnings-list"></ul>
					</div>
					
					<div class="test-section" id="test-passes" style="display: none;">
						<h3 class="test-section-title">
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'Passed Checks', 'plugin-wpshadow' ); ?>
						</h3>
						<ul class="test-list passes-list"></ul>
					</div>
					
					<div class="test-section" id="test-recommendations" style="display: none;">
						<h3 class="test-section-title">
							<span class="dashicons dashicons-lightbulb"></span>
							<?php esc_html_e( 'Recommendations', 'plugin-wpshadow' ); ?>
						</h3>
						<ul class="test-list recommendations-list"></ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
