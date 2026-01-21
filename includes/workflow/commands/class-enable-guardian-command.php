<?php
declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Workflow\Command;
use WPShadow\Guardian\Guardian_Manager;

/**
 * Enable Guardian Command
 * 
 * Workflow action to enable Guardian automated health management.
 * Can be triggered manually or in automated workflows.
 * 
 * Parameters:
 * - auto_fix_enabled: bool (default: false) - Enable auto-fixes
 * - notification_enabled: bool (default: true) - Enable notifications
 * 
 * Usage in workflow:
 * {
 *   "action": "enable_guardian",
 *   "auto_fix_enabled": true,
 *   "notification_enabled": true
 * }
 */
class Enable_Guardian_Command extends Command {
	
	/**
	 * Get command name
	 * 
	 * @return string
	 */
	public static function get_name(): string {
		return 'enable_guardian';
	}
	
	/**
	 * Get command description
	 * 
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Activate Guardian automated health monitoring and optional auto-fixes', 'wpshadow' );
	}
	
	/**
	 * Get command icon
	 * 
	 * @return string
	 */
	public static function get_icon(): string {
		return 'dashicons-shield-alt';
	}
	
	/**
	 * Get command parameters schema
	 * 
	 * @return array
	 */
	public static function get_parameters(): array {
		return [
			'auto_fix_enabled' => [
				'label'       => __( 'Enable Auto-Fixes', 'wpshadow' ),
				'type'        => 'boolean',
				'default'     => false,
				'description' => __( 'Automatically apply safe treatments to critical issues', 'wpshadow' ),
			],
			'notification_enabled' => [
				'label'       => __( 'Enable Notifications', 'wpshadow' ),
				'type'        => 'boolean',
				'default'     => true,
				'description' => __( 'Notify when critical issues detected', 'wpshadow' ),
			],
		];
	}
	
	/**
	 * Execute the command
	 * 
	 * @return void JSON response
	 */
	public function execute() {
		if ( ! $this->verify_request() ) {
			return;
		}

		$auto_fix = rest_sanitize_boolean( $this->get_post_var( 'auto_fix_enabled', false ) );
		$notify   = rest_sanitize_boolean( $this->get_post_var( 'notification_enabled', true ) );

		try {
			Guardian_Manager::update_settings( [
				'enabled'              => true,
				'auto_fix_enabled'     => $auto_fix,
				'notification_enabled' => $notify,
			] );

			Guardian_Manager::enable();

			$this->success( [
				'auto_fix_enabled' => $auto_fix,
				'notification_enabled' => $notify,
				'message' => __( 'Guardian enabled successfully', 'wpshadow' ),
			] );
		} catch ( \Exception $e ) {
			$this->error( sprintf( __( 'Failed to enable Guardian: %s', 'wpshadow' ), $e->getMessage() ) );
		}
	}
}
