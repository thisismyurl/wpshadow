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
		
		$html = '<table class="form-table guardian-settings-table">
			<tr>
				<th scope="row">
						<label for="guardian_enabled">' . esc_html__( 'Enable WPShadow Guardian', 'wpshadow' ) . '</label>
				</th>
				<td>
					<label>
						<input type="checkbox" id="guardian_enabled" name="guardian_enabled" value="1" ' . checked( $is_enabled, true, false ) . ' />
							' . esc_html__( 'Activate WPShadow Guardian monitoring and auto-fixes', 'wpshadow' ) . '
					</label>
					<p class="description">' . esc_html__( 'Enables automated health monitoring and intelligent fix suggestions', 'wpshadow' ) . '</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>' . esc_html__( 'Safety Mode', 'wpshadow' ) . '</label>
				</th>
				<td>
					<label>
						<input type="checkbox" name="guardian_safety_mode" value="1" checked />
						' . esc_html__( 'Require manual confirmation for auto-fixes', 'wpshadow' ) . '
					</label>
					<p class="description">' . esc_html__( 'When enabled, all auto-fixes require user approval before execution', 'wpshadow' ) . '</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label>' . esc_html__( 'Activity Logging', 'wpshadow' ) . '</label>
				</th>
				<td>
					<label>
						<input type="checkbox" name="guardian_activity_logging" value="1" checked />
							' . esc_html__( 'Log all WPShadow Guardian actions and events', 'wpshadow' ) . '
					</label>
					<p class="description">' . esc_html__( 'Comprehensive audit trail for all system actions', 'wpshadow' ) . '</p>
				</td>
			</tr>
		</table>';
		
		return $html;
	}
	
	/**
	 * Render policies tab
	 * 
	 * @return string HTML
	 */
	private static function render_policies_tab(): string {
		$safe_fixes = Auto_Fix_Policy_Manager::get_safe_fixes();
		
		$html = '<div class="guardian-policies-section">
			<p>' . esc_html__( 'Select which treatments are safe for automatic execution:', 'wpshadow' ) . '</p>
			<div class="policies-list">';
		
		// Get available treatments
		$treatments = [
			'WPShadow\Treatments\Treatment_SSL' => __( 'Enable SSL', 'wpshadow' ),
			'WPShadow\Treatments\Treatment_Outdated_Plugins' => __( 'Update Plugins', 'wpshadow' ),
			'WPShadow\Treatments\Treatment_Debug_Mode' => __( 'Disable Debug Mode', 'wpshadow' ),
			'WPShadow\Treatments\Treatment_Memory_Limit' => __( 'Increase Memory', 'wpshadow' ),
		];
		
		foreach ( $treatments as $class => $label ) {
			$is_approved = in_array( $class, $safe_fixes, true );
			$html .= sprintf(
				'<label class="policy-checkbox">
					<input type="checkbox" name="guardian_policies[]" value="%s" %s />
					<span class="policy-label">%s</span>
				</label>',
				esc_attr( $class ),
				checked( $is_approved, true, false ),
				esc_html( $label )
			);
		}
		
		$html .= '</div></div>';
		
		return $html;
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
					<input type="number" id="memory_threshold" name="memory_threshold" min="50" max="95" value="85" />
					<span class="unit">%</span>
					<p class="description">' . esc_html__( 'Pause auto-fixes if memory usage exceeds this percentage', 'wpshadow' ) . '</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="change_detection_window">' . esc_html__( 'Change Detection Window', 'wpshadow' ) . '</label>
				</th>
				<td>
					<input type="number" id="change_detection_window" name="change_detection_window" min="5" max="60" value="30" />
					<span class="unit">' . esc_html__( 'minutes', 'wpshadow' ) . '</span>
					<p class="description">' . esc_html__( 'Detect plugin/theme changes within this time window', 'wpshadow' ) . '</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="error_spike_threshold">' . esc_html__( 'Error Log Growth Threshold', 'wpshadow' ) . '</label>
				</th>
				<td>
					<input type="number" id="error_spike_threshold" name="error_spike_threshold" min="10" max="500" value="100" />
					<span class="unit">KB</span>
					<p class="description">' . esc_html__( 'Pause auto-fixes if error log grows this much in 5 minutes', 'wpshadow' ) . '</p>
				</td>
			</tr>
		</table>';
		
		return $html;
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
					<select id="execution_frequency" name="execution_frequency">
						<option value="manual">' . esc_html__( 'Manual Only', 'wpshadow' ) . '</option>
						<option value="cron_hourly">' . esc_html__( 'Hourly', 'wpshadow' ) . '</option>
						<option value="cron_daily" selected>' . esc_html__( 'Daily', 'wpshadow' ) . '</option>
					</select>
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
					<select id="continue_on_error" name="continue_on_error">
						<option value="stop">' . esc_html__( 'Stop on First Error', 'wpshadow' ) . '</option>
						<option value="continue" selected>' . esc_html__( 'Skip Failed, Continue', 'wpshadow' ) . '</option>
					</select>
					<p class="description">' . esc_html__( 'How to handle errors during batch execution', 'wpshadow' ) . '</p>
				</td>
			</tr>
		</table>';
		
		return $html;
	}
	
	/**
	 * Render notifications tab
	 * 
	 * @return string HTML
	 */
	private static function render_notifications_tab(): string {
		$html = '<table class="form-table guardian-settings-table">
			<tr>
				<th scope="row">
					<label>' . esc_html__( 'Alert Types', 'wpshadow' ) . '</label>
				</th>
				<td>
					<label>
						<input type="checkbox" name="notify_critical_issues" value="1" checked />
						' . esc_html__( 'Critical Issues', 'wpshadow' ) . '
					</label><br>
					<label>
						<input type="checkbox" name="notify_auto_fix_failed" value="1" checked />
						' . esc_html__( 'Auto-Fix Failures', 'wpshadow' ) . '
					</label><br>
					<label>
						<input type="checkbox" name="notify_anomalies" value="1" />
						' . esc_html__( 'Anomaly Detection', 'wpshadow' ) . '
					</label><br>
					<label>
						<input type="checkbox" name="notify_daily_report" value="1" />
						' . esc_html__( 'Daily Reports', 'wpshadow' ) . '
					</label>
					<p class="description">' . esc_html__( 'Select which events should trigger notifications', 'wpshadow' ) . '</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="notification_email">' . esc_html__( 'Notification Email', 'wpshadow' ) . '</label>
				</th>
				<td>
					<input type="email" id="notification_email" name="notification_email" value="' . esc_attr( get_option( 'admin_email' ) ) . '" />
						<p class="description">' . esc_html__( 'Email address to receive WPShadow Guardian notifications', 'wpshadow' ) . '</p>
				</td>
			</tr>
		</table>';
		
		return $html;
	}
}
