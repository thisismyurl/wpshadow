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
		return __( 'Enable Guardian', 'wpshadow' );
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
	 * @param array $parameters Command parameters
	 * 
	 * @return array Result with status and message
	 */
	public static function execute( array $parameters ): array {
		try {
			$auto_fix = (bool) ( $parameters['auto_fix_enabled'] ?? false );
			$notify = (bool) ( $parameters['notification_enabled'] ?? true );
			
			// Update settings
			Guardian_Manager::update_settings( [
				'enabled'              => true,
				'auto_fix_enabled'     => $auto_fix,
				'notification_enabled' => $notify,
			] );
			
			// Enable Guardian
			Guardian_Manager::enable();
			
			return [
				'success' => true,
				'message' => __( 'Guardian enabled successfully', 'wpshadow' ),
				'auto_fix_enabled' => $auto_fix,
			];
		} catch ( \Exception $e ) {
			return [
				'success' => false,
				'message' => sprintf(
					__( 'Failed to enable Guardian: %s', 'wpshadow' ),
					$e->getMessage()
				),
			];
		}
	}
}
