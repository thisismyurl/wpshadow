<?php
declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Core\Command_Base;
use WPShadow\Reporting\Notification_Manager;
use WPShadow\Core\KPI_Tracker;

/**
 * Workflow Command: Manage Notifications
 * 
 * Set notification preferences and manage subscriptions.
 * 
 * POST Parameters:
 * - action (required): 'set_preferences', 'unsubscribe', or 'get_preferences'
 * - email (optional): Email address
 * - preferences (optional): Array of alert type => enabled
 * - alert_type (optional): Specific alert to manage
 */
class Manage_Notifications_Command extends Command_Base {
	/**
	 * Get command name
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'manage_notifications';
	}
	
	/**
	 * Execute the command
	 * 
	 * @return array Result
	 */
	protected function execute(): array {
		$action = sanitize_key( $this->get_param( 'action' ) );
		
		switch ( $action ) {
			case 'set_preferences':
				return $this->handle_set_preferences();
			
			case 'get_preferences':
				return $this->handle_get_preferences();
			
			case 'unsubscribe':
				return $this->handle_unsubscribe();
			
			case 'get_statistics':
				return $this->handle_get_statistics();
			
			default:
				return $this->error( 'Unknown action: ' . $action );
		}
	}
	
	/**
	 * Set notification preferences
	 * 
	 * @return array Result
	 */
	private function handle_set_preferences(): array {
		$email        = sanitize_email( $this->get_param( 'email' ) );
		$preferences  = (array) $this->get_param( 'preferences' );
		
		if ( ! is_email( $email ) ) {
			return $this->error( 'Invalid email address' );
		}
		
		// Sanitize preferences
		$sanitized = [];
		foreach ( $preferences as $key => $value ) {
			$key = sanitize_key( $key );
			$sanitized[ $key ] = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		}
		
		Notification_Manager::set_preferences( $email, $sanitized );
		
		KPI_Tracker::record_action( 'notification_preferences_updated', 1 );
		
		return $this->success( [
			'message' => 'Notification preferences updated',
			'email' => $email,
		] );
	}
	
	/**
	 * Get notification preferences
	 * 
	 * @return array Result
	 */
	private function handle_get_preferences(): array {
		$email = sanitize_email( $this->get_param( 'email' ) );
		
		if ( ! is_email( $email ) ) {
			return $this->error( 'Invalid email address' );
		}
		
		$preferences = Notification_Manager::get_preferences( $email );
		
		return $this->success( [
			'email' => $email,
			'preferences' => $preferences,
		] );
	}
	
	/**
	 * Unsubscribe from notifications
	 * 
	 * @return array Result
	 */
	private function handle_unsubscribe(): array {
		$email = sanitize_email( $this->get_param( 'email' ) );
		$alert_type = sanitize_key( $this->get_param( 'alert_type' ) ) ?: '';
		
		if ( ! is_email( $email ) ) {
			return $this->error( 'Invalid email address' );
		}
		
		Notification_Manager::unsubscribe_report( $email, $alert_type );
		
		KPI_Tracker::record_action( 'unsubscribed_notification', 1 );
		
		return $this->success( [
			'message' => 'Unsubscribed from notifications',
			'email' => $email,
		] );
	}
	
	/**
	 * Get notification statistics
	 * 
	 * @return array Result
	 */
	private function handle_get_statistics(): array {
		$stats = Notification_Manager::get_statistics();
		
		return $this->success( $stats );
	}
	
	/**
	 * Get command description
	 * 
	 * @return string
	 */
	public function get_description(): string {
		return 'Manage notification preferences and subscriptions';
	}
}
