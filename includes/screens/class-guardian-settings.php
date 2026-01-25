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
			<h1><?php esc_html_e( 'WPShadow Guardian Settings', 'wpshadow' ); ?></h1>
			
			<form method="post" action="options.php" class="guardian-settings-form">
				<?php settings_fields( 'wpshadow_guardian_settings' ); ?>
				
				<!-- Main Settings Tab -->
				<div class="guardian-settings-tabs">
					<nav class="nav-tab-wrapper">
						<a href="#general" class="nav-tab nav-tab-active"><?php esc_html_e( 'General', 'wpshadow' ); ?></a>
						<a href="#policies" class="nav-tab"><?php esc_html_e( 'Auto-Fix Policies', 'wpshadow' ); ?></a>
						<a href="#anomalies" class="nav-tab"><?php esc_html_e( 'Anomaly Detection', 'wpshadow' ); ?></a>
						<a href="#schedule" class="nav-tab"><?php esc_html_e( 'Schedule', 'wpshadow' ); ?></a>
						<a href="#notifications" class="nav-tab"><?php esc_html_e( 'Notifications', 'wpshadow' ); ?></a>
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
				$is_approved = in_array( $class, $safe_fixes, true );
				$safe_id     = sanitize_key( str_replace( '\\', '-', $class ) );

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by Form_Controls helper
				echo \WPShadow\Helpers\Form_Controls::toggle_switch(
					array(
						'id'      => 'policy-' . $safe_id,
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
				<input type="time" id="execution-time" name="execution_time" value="02:00" class="wps-textarea" style="width: 200px;" />
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
