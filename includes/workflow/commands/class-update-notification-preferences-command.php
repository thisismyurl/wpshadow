<?php
declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Workflow\Command;
use WPShadow\Cloud\Notification_Manager;
use WPShadow\Cloud\Registration_Manager;

/**
 * Update Notification Preferences Command
 * 
 * AJAX endpoint to save notification settings.
 * Validates tier before applying pro-only features.
 * 
 * Parameters:
 * - email_on_critical: bool
 * - email_on_findings: bool (pro)
 * - daily_digest: bool (pro)
 * - weekly_summary: bool
 * - scan_completion: bool
 * - anomaly_alerts: bool (pro)
 * 
 * Response: { success: bool, preferences?: {...} }
 */
class Update_Notification_Preferences_Command extends Command {
	
	/**
	 * Get command name
	 * 
	 * @return string
	 */
	public static function get_name(): string {
		return 'update_notification_preferences';
	}
	
	/**
	 * Get command description
	 * 
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Update cloud notification preferences', 'wpshadow' );
	}
	
	/**
	 * Execute the command (AJAX handler)
	 * 
	 * @return void Issues JSON response
	 */
	public function execute(): void {
		// Verify request security
		if ( ! $this->verify_request( 'manage_options' ) ) {
			return;
		}
		
		// Get current preferences
		$current = Notification_Manager::get_preferences();
		
		// Build new preferences from POST
		$new_preferences = [
			'email_on_critical'     => (bool) $this->get_post_var( 'email_on_critical', '' ),
			'email_on_findings'     => (bool) $this->get_post_var( 'email_on_findings', '' ),
			'daily_digest'          => (bool) $this->get_post_var( 'daily_digest', '' ),
			'weekly_summary'        => (bool) $this->get_post_var( 'weekly_summary', '' ),
			'scan_completion'       => (bool) $this->get_post_var( 'scan_completion', '' ),
			'anomaly_alerts'        => (bool) $this->get_post_var( 'anomaly_alerts', '' ),
		];
		
		// Check tier for pro features
		$status = Registration_Manager::get_registration_status();
		if ( $status['tier'] === 'free' ) {
			// Reset pro-only features for free tier
			$new_preferences['email_on_findings'] = false;
			$new_preferences['daily_digest'] = false;
			$new_preferences['anomaly_alerts'] = false;
		}
		
		// Update preferences
		$result = Notification_Manager::set_preferences( $new_preferences );
		
		if ( ! $result ) {
			$this->error( __( 'Failed to update preferences', 'wpshadow' ) );
			return;
		}
		
		// Success
		$this->success( [
			'message'     => __( 'Notification preferences updated', 'wpshadow' ),
			'preferences' => $new_preferences,
			'tier'        => $status['tier'],
		] );
	}
	
	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		$hook = 'wp_ajax_wpshadow_' . static::get_name();
		add_action( $hook, [ new static(), 'execute' ] );
	}
}
