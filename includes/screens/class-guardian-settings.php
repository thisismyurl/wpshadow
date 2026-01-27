<?php
declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Guardian\Guardian_Manager;
use WPShadow\Guardian\Auto_Fix_Policy_Manager;

/**
 * WPShadow Guardian Settings Panel
 *
 * Settings UI for WPShadow Guardian system configuration.
 * Manage auto-fix policies, anomaly thresholds, schedules.
 *
 * Features:
 * - Enable/disable WPShadow Guardian
 * - Treatment whitelist management
 * - Execution frequency control
 * - Max treatments per run
 * - Notification settings
 */
class Guardian_Settings {

	/**
	 * Render settings page
	 *
	 * @return string HTML output
	 */
	public static function render(): string {
		ob_start();
		?>
		<div class="wrap wpshadow-guardian-settings">
			<?php wpshadow_render_page_header(
				__( 'WPShadow Guardian Settings', 'wpshadow' ),
				__( 'Configure automated health monitoring and intelligent fix suggestions.', 'wpshadow' )
			); ?>
			
			<form method="post" action="options.php" class="guardian-settings-form">
				<?php settings_fields( 'wpshadow_guardian_settings' ); ?>
				
				<!-- Main Settings Tab -->
				<div class="guardian-settings-tabs">
					<nav class="wps-tabs" role="tablist">
						<a href="#general" class="wps-tab wps-tab-active" role="tab" aria-selected="true" data-tab="general"><?php esc_html_e( 'General', 'wpshadow' ); ?></a>
						<a href="#policies" class="wps-tab" role="tab" aria-selected="false" data-tab="policies"><?php esc_html_e( 'Auto-Fix Policies', 'wpshadow' ); ?></a>
						<a href="#anomalies" class="wps-tab" role="tab" aria-selected="false" data-tab="anomalies"><?php esc_html_e( 'Anomaly Detection', 'wpshadow' ); ?></a>
						<a href="#schedule" class="wps-tab" role="tab" aria-selected="false" data-tab="schedule"><?php esc_html_e( 'Schedule', 'wpshadow' ); ?></a>
						<a href="#notifications" class="wps-tab" role="tab" aria-selected="false" data-tab="notifications"><?php esc_html_e( 'Notifications', 'wpshadow' ); ?></a>
					</nav>
					
					<!-- General Tab -->
					<div id="general" class="tab-content active">
						<h2><?php esc_html_e( 'General Settings', 'wpshadow' ); ?></h2>
						<?php echo wp_kses_post( self::render_general_tab() ); ?>
					</div>
					
					<!-- Policies Tab -->
					<div id="policies" class="tab-content">
						<h2><?php esc_html_e( 'Auto-Fix Policies', 'wpshadow' ); ?></h2>
						<?php echo wp_kses_post( self::render_policies_tab() ); ?>
					</div>
					
					<!-- Anomalies Tab -->
					<div id="anomalies" class="tab-content">
						<h2><?php esc_html_e( 'Anomaly Detection', 'wpshadow' ); ?></h2>
						<?php echo wp_kses_post( self::render_anomalies_tab() ); ?>
					</div>
					
					<!-- Schedule Tab -->
					<div id="schedule" class="tab-content">
						<h2><?php esc_html_e( 'Execution Schedule', 'wpshadow' ); ?></h2>
						<?php echo wp_kses_post( self::render_schedule_tab() ); ?>
					</div>
					
					<!-- Notifications Tab -->
					<div id="notifications" class="tab-content">
						<h2><?php esc_html_e( 'Notification Settings', 'wpshadow' ); ?></h2>
						<?php echo wp_kses_post( self::render_notifications_tab() ); ?>
					</div>
				</div>
				
				<?php submit_button(); ?>
			</form>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render general settings tab
	 *
	 * @return string HTML
	 */
	private static function render_general_tab(): string {
		$is_enabled = Guardian_Manager::is_enabled();

		$html = '<div class="wps-settings-section">
			<div class="wps-settings-section-header">
				<h3 class="wps-settings-section-title">' . esc_html__( 'General Settings', 'wpshadow' ) . '</h3>
				<p class="wps-settings-section-description">' . esc_html__( 'Configure core WPShadow Guardian monitoring features', 'wpshadow' ) . '</p>
			</div>

			<div class="wps-form-group-inline">
				<div>
					<label class="wps-label" for="guardian_enabled">
						' . esc_html__( 'Enable WPShadow Guardian', 'wpshadow' ) . '
					</label>
					<span class="wps-help-text">
						' . esc_html__( 'Activate automated health monitoring and intelligent fix suggestions', 'wpshadow' ) . '
					</span>
				</div>
				<div>
					<label>
						<input type="checkbox" id="guardian_enabled" name="guardian_enabled" value="1" ' . checked( $is_enabled, true, false ) . ' />
						' . esc_html__( 'Activate monitoring', 'wpshadow' ) . '
					</label>
				</div>
			</div>

			<hr class="wps-form-divider" />

			<div class="wps-form-group-inline">
				<div>
					<label class="wps-label">
						' . esc_html__( 'Safety Mode', 'wpshadow' ) . '
					</label>
					<span class="wps-help-text">
						' . esc_html__( 'When enabled, all auto-fixes require user approval before execution', 'wpshadow' ) . '
					</span>
				</div>
				<div>
					<label>
						<input type="checkbox" name="guardian_safety_mode" value="1" checked />
						' . esc_html__( 'Require manual confirmation', 'wpshadow' ) . '
					</label>
				</div>
			</div>

			<hr class="wps-form-divider" />

			<div class="wps-form-group-inline">
				<div>
					<label class="wps-label">
						' . esc_html__( 'Activity Logging', 'wpshadow' ) . '
					</label>
					<span class="wps-help-text">
						' . esc_html__( 'Comprehensive audit trail for all system actions', 'wpshadow' ) . '
					</span>
				</div>
				<div>
					<label>
						<input type="checkbox" name="guardian_activity_logging" value="1" checked />
						' . esc_html__( 'Log all actions', 'wpshadow' ) . '
					</label>
				</div>
			</div>
		</div>';
		ob_start();
		?>
		<div class="wps-form-section">
			<?php
			// Enable Guardian toggle
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
			echo \WPShadow\Helpers\Form_Controls::toggle_switch(
				array(
					'id'          => 'guardian-enabled',
					'name'        => 'guardian_enabled',
					'label'       => __( 'Enable WPShadow Guardian', 'wpshadow' ),
					'helper_text' => __( 'Enables automated health monitoring and intelligent fix suggestions', 'wpshadow' ),
					'checked'     => $is_enabled,
				)
			);

			// Safety Mode toggle
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
			echo \WPShadow\Helpers\Form_Controls::toggle_switch(
				array(
					'id'          => 'guardian-safety-mode',
					'name'        => 'guardian_safety_mode',
					'label'       => __( 'Safety Mode', 'wpshadow' ),
					'helper_text' => __( 'When enabled, all auto-fixes require user approval before execution', 'wpshadow' ),
					'checked'     => true,
				)
			);

			// Activity Logging toggle
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
			echo \WPShadow\Helpers\Form_Controls::toggle_switch(
				array(
					'id'          => 'guardian-activity-logging',
					'name'        => 'guardian_activity_logging',
					'label'       => __( 'Activity Logging', 'wpshadow' ),
					'helper_text' => __( 'Comprehensive audit trail for all system actions', 'wpshadow' ),
					'checked'     => true,
				)
			);
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render policies tab
	 *
	 * @return string HTML
	 */
	private static function render_policies_tab(): string {
		$safe_fixes = Auto_Fix_Policy_Manager::get_safe_fixes();

		// Get available treatments
		$treatments = array(
			'WPShadow\Treatments\Treatment_SSL'          => __( 'Enable SSL', 'wpshadow' ),
			'WPShadow\Treatments\Treatment_Outdated_Plugins' => __( 'Update Plugins', 'wpshadow' ),
			'WPShadow\Treatments\Treatment_Debug_Mode'   => __( 'Disable Debug Mode', 'wpshadow' ),
			'WPShadow\Treatments\Treatment_Memory_Limit' => __( 'Increase Memory', 'wpshadow' ),
		);

		ob_start();
		?>
		<div class="wps-form-section">
			<p><?php esc_html_e( 'Select which treatments are safe for automatic execution:', 'wpshadow' ); ?></p>
			<?php foreach ( $treatments as $class => $label ) : ?>
				<?php
				$is_approved           = in_array( $class, $safe_fixes, true );
				$sanitized_class_id    = sanitize_key( str_replace( '\\', '-', $class ) );

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
				echo \WPShadow\Helpers\Form_Controls::toggle_switch(
					array(
						'id'      => 'policy-' . $sanitized_class_id,
						'name'    => 'guardian_policies[]',
						'label'   => $label,
						'checked' => $is_approved,
					)
				);
				?>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render anomalies tab
	 *
	 * @return string HTML
	 */
	private static function render_anomalies_tab(): string {
		$html = '<table class="form-table guardian-settings-table">
			<tr>
				<th scope="row">
					<label for="memory_threshold">' . esc_html__( 'Memory Usage Threshold', 'wpshadow' ) . '</label>
				</th>
				<td>
					<div class="wps-range-group">
						<div class="wps-range-header">
							<span class="wps-range-value" id="memory_threshold_display">85%</span>
						</div>
						<div class="wps-range-wrapper">
							<input 
								type="range" 
								id="memory_threshold" 
								name="memory_threshold" 
								class="wps-range"
								min="50" 
								max="95" 
								value="85" 
								step="5"
								data-suffix="%"
								aria-valuemin="50"
								aria-valuemax="95"
								aria-valuenow="85"
								aria-valuetext="85 percent"
							/>
						</div>
						<p class="description wps-help-text">' . esc_html__( 'Pause auto-fixes if memory usage exceeds this percentage', 'wpshadow' ) . '</p>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="change_detection_window">' . esc_html__( 'Change Detection Window', 'wpshadow' ) . '</label>
				</th>
				<td>
					<div class="wps-range-group">
						<div class="wps-range-header">
							<span class="wps-range-value" id="change_detection_window_display">30 ' . esc_html__( 'minutes', 'wpshadow' ) . '</span>
						</div>
						<div class="wps-range-wrapper">
							<input 
								type="range" 
								id="change_detection_window" 
								name="change_detection_window" 
								class="wps-range"
								min="5" 
								max="60" 
								value="30" 
								step="5"
								data-suffix=" ' . esc_attr__( 'minutes', 'wpshadow' ) . '"
								aria-valuemin="5"
								aria-valuemax="60"
								aria-valuenow="30"
								aria-valuetext="30 minutes"
							/>
						</div>
						<p class="description wps-help-text">' . esc_html__( 'Detect plugin/theme changes within this time window', 'wpshadow' ) . '</p>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="error_spike_threshold">' . esc_html__( 'Error Log Growth Threshold', 'wpshadow' ) . '</label>
				</th>
				<td>
					<div class="wps-range-group">
						<div class="wps-range-header">
							<span class="wps-range-value" id="error_spike_threshold_display">100 KB</span>
						</div>
						<div class="wps-range-wrapper">
							<input 
								type="range" 
								id="error_spike_threshold" 
								name="error_spike_threshold" 
								class="wps-range"
								min="10" 
								max="500" 
								value="100" 
								step="10"
								data-suffix=" KB"
								aria-valuemin="10"
								aria-valuemax="500"
								aria-valuenow="100"
								aria-valuetext="100 kilobytes"
							/>
						</div>
						<p class="description wps-help-text">' . esc_html__( 'Pause auto-fixes if error log grows this much in 5 minutes', 'wpshadow' ) . '</p>
					</div>
				</td>
			</tr>
		</table>';
		$html = '<div class="wps-settings-section">
			<div class="wps-settings-section-header">
				<h3 class="wps-settings-section-title">' . esc_html__( 'Anomaly Detection', 'wpshadow' ) . '</h3>
				<p class="wps-settings-section-description">' . esc_html__( 'Configure thresholds for detecting unusual system behavior', 'wpshadow' ) . '</p>
			</div>

			<div class="wps-form-group">
				<label class="wps-label" for="memory_threshold">
					' . esc_html__( 'Memory Usage Threshold', 'wpshadow' ) . '
				</label>
				<div class="wps-input-group-inline">
					<input type="number" id="memory_threshold" name="memory_threshold" min="50" max="95" value="85" class="wps-input-sm" />
					<span class="unit">%</span>
				</div>
				<span class="wps-help-text">
					' . esc_html__( 'Pause auto-fixes if memory usage exceeds this percentage', 'wpshadow' ) . '
				</span>
			</div>

			<div class="wps-form-group">
				<label class="wps-label" for="change_detection_window">
					' . esc_html__( 'Change Detection Window', 'wpshadow' ) . '
				</label>
				<div class="wps-input-group-inline">
					<input type="number" id="change_detection_window" name="change_detection_window" min="5" max="60" value="30" class="wps-input-sm" />
					<span class="unit">' . esc_html__( 'minutes', 'wpshadow' ) . '</span>
				</div>
				<span class="wps-help-text">
					' . esc_html__( 'Detect plugin/theme changes within this time window', 'wpshadow' ) . '
				</span>
			</div>

			<div class="wps-form-group">
				<label class="wps-label" for="error_spike_threshold">
					' . esc_html__( 'Error Log Growth Threshold', 'wpshadow' ) . '
				</label>
				<div class="wps-input-group-inline">
					<input type="number" id="error_spike_threshold" name="error_spike_threshold" min="10" max="500" value="100" class="wps-input-sm" />
					<span class="unit">KB</span>
				</div>
				<span class="wps-help-text">
					' . esc_html__( 'Pause auto-fixes if error log grows this much in 5 minutes', 'wpshadow' ) . '
				</span>
			</div>
		</div>';
		ob_start();
		?>
		<div class="wps-form-section">
			<?php
			// Memory usage threshold slider
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
			echo \WPShadow\Helpers\Form_Controls::slider(
				array(
					'id'          => 'memory-threshold',
					'name'        => 'memory_threshold',
					'label'       => __( 'Memory Usage Threshold', 'wpshadow' ),
					'helper_text' => __( 'Pause auto-fixes if memory usage exceeds this percentage', 'wpshadow' ),
					'min'         => 50,
					'max'         => 95,
					'step'        => 5,
					'value'       => 85,
					'unit'        => '%',
					'ticks'       => array( 50, 65, 80, 95 ),
				)
			);

			// Change detection window slider
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
			echo \WPShadow\Helpers\Form_Controls::slider(
				array(
					'id'          => 'change-detection-window',
					'name'        => 'change_detection_window',
					'label'       => __( 'Change Detection Window', 'wpshadow' ),
					'helper_text' => __( 'Detect plugin/theme changes within this time window', 'wpshadow' ),
					'min'         => 5,
					'max'         => 60,
					'step'        => 5,
					'value'       => 30,
					'unit'        => __( 'minutes', 'wpshadow' ),
					'ticks'       => array( 5, 20, 35, 50, 60 ),
				)
			);

			// Error log growth threshold slider
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
			echo \WPShadow\Helpers\Form_Controls::slider(
				array(
					'id'          => 'error-spike-threshold',
					'name'        => 'error_spike_threshold',
					'label'       => __( 'Error Log Growth Threshold', 'wpshadow' ),
					'helper_text' => __( 'Pause auto-fixes if error log grows this much in 5 minutes', 'wpshadow' ),
					'min'         => 10,
					'max'         => 500,
					'step'        => 10,
					'value'       => 100,
					'unit'        => 'KB',
					'ticks'       => array( 10, 100, 250, 500 ),
				)
			);
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render schedule tab
	 *
	 * @return string HTML
	 */
	private static function render_schedule_tab(): string {
		$html = '<table class="form-table guardian-settings-table">
			<tr>
				<th scope="row">
					<label for="execution_frequency">' . esc_html__( 'Auto-Fix Execution Frequency', 'wpshadow' ) . '</label>
				</th>
				<td>
					<div class="wps-select-wrapper">
						<select id="execution_frequency" name="execution_frequency" class="wps-select">
							<option value="manual">' . esc_html__( 'Manual Only', 'wpshadow' ) . '</option>
							<option value="cron_hourly">' . esc_html__( 'Hourly', 'wpshadow' ) . '</option>
							<option value="cron_daily" selected>' . esc_html__( 'Daily', 'wpshadow' ) . '</option>
						</select>
					</div>
					<p class="description">' . esc_html__( 'How often to automatically execute approved fixes', 'wpshadow' ) . '</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="execution_time">' . esc_html__( 'Preferred Execution Time', 'wpshadow' ) . '</label>
				</th>
				<td>
					<input type="time" id="execution_time" name="execution_time" value="02:00" />
					<p class="description">' . esc_html__( 'Time of day to run auto-fixes (server timezone)', 'wpshadow' ) . '</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="max_treatments">' . esc_html__( 'Max Treatments Per Run', 'wpshadow' ) . '</label>
				</th>
				<td>
					<input type="range" id="max_treatments" name="max_treatments" min="1" max="20" value="5" />
					<span id="max_treatments_value">5</span>
					<p class="description">' . esc_html__( 'Maximum number of treatments to apply in a single execution', 'wpshadow' ) . '</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="continue_on_error">' . esc_html__( 'Error Handling', 'wpshadow' ) . '</label>
				</th>
				<td>
					<div class="wps-select-wrapper">
						<select id="continue_on_error" name="continue_on_error" class="wps-select">
							<option value="stop">' . esc_html__( 'Stop on First Error', 'wpshadow' ) . '</option>
							<option value="continue" selected>' . esc_html__( 'Skip Failed, Continue', 'wpshadow' ) . '</option>
						</select>
					</div>
					<p class="description">' . esc_html__( 'How to handle errors during batch execution', 'wpshadow' ) . '</p>
				</td>
			</tr>
		</table>';

		return $html;
		$html = '<div class="wps-settings-section">
			<div class="wps-settings-section-header">
				<h3 class="wps-settings-section-title">' . esc_html__( 'Execution Schedule', 'wpshadow' ) . '</h3>
				<p class="wps-settings-section-description">' . esc_html__( 'Configure when and how often auto-fixes are executed', 'wpshadow' ) . '</p>
			</div>

			<div class="wps-form-group">
				<label class="wps-label" for="execution_frequency">
					' . esc_html__( 'Auto-Fix Execution Frequency', 'wpshadow' ) . '
				</label>
				<select id="execution_frequency" name="execution_frequency">
					<option value="manual">' . esc_html__( 'Manual Only', 'wpshadow' ) . '</option>
					<option value="cron_hourly">' . esc_html__( 'Hourly', 'wpshadow' ) . '</option>
					<option value="cron_daily" selected>' . esc_html__( 'Daily', 'wpshadow' ) . '</option>
				</select>
				<span class="wps-help-text">
					' . esc_html__( 'How often to automatically execute approved fixes', 'wpshadow' ) . '
				</span>
			</div>

			<div class="wps-form-group">
				<label class="wps-label" for="execution_time">
					' . esc_html__( 'Preferred Execution Time', 'wpshadow' ) . '
				</label>
				<input type="time" id="execution_time" name="execution_time" value="02:00" />
				<span class="wps-help-text">
					' . esc_html__( 'Time of day to run auto-fixes (server timezone)', 'wpshadow' ) . '
				</span>
			</div>

			<div class="wps-form-group">
				<label class="wps-label" for="max_treatments">
					' . esc_html__( 'Max Treatments Per Run', 'wpshadow' ) . '
				</label>
				<div class="wps-range-group">
					<div class="wps-range-header">
						<input type="range" id="max_treatments" name="max_treatments" min="1" max="20" value="5" class="wps-range" />
						<span id="max_treatments_value" class="wps-range-value">5</span>
					</div>
				</div>
				<span class="wps-help-text">
					' . esc_html__( 'Maximum number of treatments to apply in a single execution', 'wpshadow' ) . '
				</span>
			</div>

			<div class="wps-form-group">
				<label class="wps-label" for="continue_on_error">
					' . esc_html__( 'Error Handling', 'wpshadow' ) . '
				</label>
				<select id="continue_on_error" name="continue_on_error">
					<option value="stop">' . esc_html__( 'Stop on First Error', 'wpshadow' ) . '</option>
					<option value="continue" selected>' . esc_html__( 'Skip Failed, Continue', 'wpshadow' ) . '</option>
				</select>
				<span class="wps-help-text">
					' . esc_html__( 'How to handle errors during batch execution', 'wpshadow' ) . '
				</span>
			</div>
		</div>';
		ob_start();
		?>
		<div class="wps-form-section">
			<?php
			// Execution frequency dropdown
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
			echo \WPShadow\Helpers\Form_Controls::dropdown(
				array(
					'id'       => 'execution-frequency',
					'name'     => 'execution_frequency',
					'label'    => __( 'Auto-Fix Execution Frequency', 'wpshadow' ),
					'options'  => array(
						'manual'      => __( 'Manual Only', 'wpshadow' ),
						'cron_hourly' => __( 'Hourly', 'wpshadow' ),
						'cron_daily'  => __( 'Daily', 'wpshadow' ),
					),
					'selected' => 'cron_daily',
				)
			);
			?>

			<div class="wps-form-field">
				<label for="execution-time" class="wps-field-label">
					<?php esc_html_e( 'Preferred Execution Time', 'wpshadow' ); ?>
					<span class="wps-helper-text"><?php esc_html_e( 'Time of day to run auto-fixes (server timezone)', 'wpshadow' ); ?></span>
				</label>
				<input type="time" id="execution_time" name="execution_time" value="02:00" class="wps-input-time" />
			</div>

			<?php
			// Max treatments slider
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
			echo \WPShadow\Helpers\Form_Controls::slider(
				array(
					'id'          => 'max-treatments',
					'name'        => 'max_treatments',
					'label'       => __( 'Max Treatments Per Run', 'wpshadow' ),
					'helper_text' => __( 'Maximum number of treatments to apply in a single execution', 'wpshadow' ),
					'min'         => 1,
					'max'         => 20,
					'step'        => 1,
					'value'       => 5,
					'unit'        => '',
					'ticks'       => array( 1, 5, 10, 15, 20 ),
				)
			);

			// Error handling dropdown
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
			echo \WPShadow\Helpers\Form_Controls::dropdown(
				array(
					'id'       => 'continue-on-error',
					'name'     => 'continue_on_error',
					'label'    => __( 'Error Handling', 'wpshadow' ),
					'options'  => array(
						'stop'     => __( 'Stop on First Error', 'wpshadow' ),
						'continue' => __( 'Skip Failed, Continue', 'wpshadow' ),
					),
					'selected' => 'continue',
				)
			);
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render notifications tab
	 *
	 * @return string HTML
	 */
	private static function render_notifications_tab(): string {
		$html = '<div class="wps-settings-section">
			<div class="wps-settings-section-header">
				<h3 class="wps-settings-section-title">' . esc_html__( 'Notification Settings', 'wpshadow' ) . '</h3>
				<p class="wps-settings-section-description">' . esc_html__( 'Configure which events should trigger notifications', 'wpshadow' ) . '</p>
			</div>

			<div class="wps-form-group">
				<label class="wps-label">
					' . esc_html__( 'Alert Types', 'wpshadow' ) . '
				</label>
				<div class="wps-checkbox-group">
					<label>
						<input type="checkbox" name="notify_critical_issues" value="1" checked />
						' . esc_html__( 'Critical Issues', 'wpshadow' ) . '
					</label>
					<label>
						<input type="checkbox" name="notify_auto_fix_failed" value="1" checked />
						' . esc_html__( 'Auto-Fix Failures', 'wpshadow' ) . '
					</label>
					<label>
						<input type="checkbox" name="notify_anomalies" value="1" />
						' . esc_html__( 'Anomaly Detection', 'wpshadow' ) . '
					</label>
					<label>
						<input type="checkbox" name="notify_daily_report" value="1" />
						' . esc_html__( 'Daily Reports', 'wpshadow' ) . '
					</label>
				</div>
				<span class="wps-help-text">
					' . esc_html__( 'Select which events should trigger notifications', 'wpshadow' ) . '
				</span>
			</div>

			<hr class="wps-form-divider" />

			<div class="wps-form-group">
				<label class="wps-label" for="notification_email">
					' . esc_html__( 'Notification Email', 'wpshadow' ) . '
				</label>
				<input type="email" id="notification_email" name="notification_email" value="' . esc_attr( get_option( 'admin_email', '' ) ) . '" />
				<span class="wps-help-text">
					' . esc_html__( 'Email address to receive WPShadow Guardian notifications', 'wpshadow' ) . '
				</span>
			</div>
		</div>';
		ob_start();
		?>
		<div class="wps-form-section">
			<div class="wps-form-field">
				<label class="wps-field-label"><?php esc_html_e( 'Alert Types', 'wpshadow' ); ?></label>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
				echo \WPShadow\Helpers\Form_Controls::toggle_switch(
					array(
						'id'      => 'notify-critical-issues',
						'name'    => 'notify_critical_issues',
						'label'   => __( 'Critical Issues', 'wpshadow' ),
						'checked' => true,
					)
				);

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
				echo \WPShadow\Helpers\Form_Controls::toggle_switch(
					array(
						'id'      => 'notify-auto-fix-failed',
						'name'    => 'notify_auto_fix_failed',
						'label'   => __( 'Auto-Fix Failures', 'wpshadow' ),
						'checked' => true,
					)
				);

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
				echo \WPShadow\Helpers\Form_Controls::toggle_switch(
					array(
						'id'      => 'notify-anomalies',
						'name'    => 'notify_anomalies',
						'label'   => __( 'Anomaly Detection', 'wpshadow' ),
						'checked' => false,
					)
				);

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
				echo \WPShadow\Helpers\Form_Controls::toggle_switch(
					array(
						'id'      => 'notify-daily-report',
						'name'    => 'notify_daily_report',
						'label'   => __( 'Daily Reports', 'wpshadow' ),
						'checked' => false,
					)
				);
				?>
			</div>

			<div class="wps-form-field">
				<label for="notification-email" class="wps-field-label">
					<?php esc_html_e( 'Notification Email', 'wpshadow' ); ?>
					<span class="wps-helper-text"><?php esc_html_e( 'Email address to receive WPShadow Guardian notifications', 'wpshadow' ); ?></span>
				</label>
				<input 
					type="email" 
					id="notification-email" 
					name="notification_email" 
					class="wps-textarea" 
					value="<?php echo esc_attr( get_option( 'admin_email', '' ) ); ?>"
					style="width: 100%; max-width: 400px;"
				/>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
