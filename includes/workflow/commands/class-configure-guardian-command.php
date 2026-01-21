<?php
declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Workflow\Command;
use WPShadow\Guardian\Guardian_Manager;

/**
 * Configure Guardian Command
 * 
 * Workflow action to configure Guardian settings.
 * Update health check frequency, auto-fix behavior, notifications, etc.
 * 
 * Parameters:
 * - enabled: bool - Enable/disable Guardian
 * - auto_fix_enabled: bool - Enable auto-fixes
 * - health_check_interval: string - 'hourly', 'daily', 'weekly'
 * - notification_level: string - 'all', 'warnings', 'critical'
 * - backup_before_fix: bool - Create backup before fixes
 * 
 * Usage in workflow:
 * {
 *   "action": "configure_guardian",
 *   "enabled": true,
 *   "health_check_interval": "daily",
 *   "auto_fix_enabled": false
 * }
 */
class Configure_Guardian_Command extends Command {
	
	/**
	 * Get command name
	 * 
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Configure Guardian', 'wpshadow' );
	}
	
	/**
	 * Get command description
	 * 
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Update Guardian health monitoring and auto-fix settings', 'wpshadow' );
	}
	
	/**
	 * Get command icon
	 * 
	 * @return string
	 */
	public static function get_icon(): string {
		return 'dashicons-admin-generic';
	}
	
	/**
	 * Get command parameters schema
	 * 
	 * @return array
	 */
	public static function get_parameters(): array {
		return [
			'enabled' => [
				'label'       => __( 'Guardian Status', 'wpshadow' ),
				'type'        => 'boolean',
				'description' => __( 'Enable or disable Guardian monitoring', 'wpshadow' ),
			],
			'health_check_interval' => [
				'label'       => __( 'Health Check Frequency', 'wpshadow' ),
				'type'        => 'select',
				'options'     => [
					'hourly'  => __( 'Hourly', 'wpshadow' ),
					'daily'   => __( 'Daily', 'wpshadow' ),
					'weekly'  => __( 'Weekly', 'wpshadow' ),
				],
				'description' => __( 'How often to check site health', 'wpshadow' ),
			],
			'auto_fix_enabled' => [
				'label'       => __( 'Auto-Fix Critical Issues', 'wpshadow' ),
				'type'        => 'boolean',
				'description' => __( 'Automatically apply safe treatments', 'wpshadow' ),
			],
			'backup_before_fix' => [
				'label'       => __( 'Backup Before Fixes', 'wpshadow' ),
				'type'        => 'boolean',
				'default'     => true,
				'description' => __( 'Create backup before applying auto-fixes', 'wpshadow' ),
			],
			'notification_level' => [
				'label'       => __( 'Notification Level', 'wpshadow' ),
				'type'        => 'select',
				'options'     => [
					'all'      => __( 'All Issues', 'wpshadow' ),
					'warnings' => __( 'Warnings & Critical', 'wpshadow' ),
					'critical' => __( 'Critical Only', 'wpshadow' ),
				],
				'description' => __( 'Which issues trigger notifications', 'wpshadow' ),
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
			// Get current settings
			$current = Guardian_Manager::get_settings();
			
			// Build update payload
			$updates = [];
			
			if ( isset( $parameters['enabled'] ) ) {
				if ( $parameters['enabled'] ) {
					Guardian_Manager::enable();
				} else {
					Guardian_Manager::disable();
				}
				$updates['enabled'] = (bool) $parameters['enabled'];
			}
			
			if ( isset( $parameters['health_check_interval'] ) ) {
				$updates['health_check_interval'] = sanitize_key( $parameters['health_check_interval'] );
			}
			
			if ( isset( $parameters['auto_fix_enabled'] ) ) {
				$updates['auto_fix_enabled'] = (bool) $parameters['auto_fix_enabled'];
			}
			
			if ( isset( $parameters['backup_before_fix'] ) ) {
				$updates['backup_before_fix'] = (bool) $parameters['backup_before_fix'];
			}
			
			if ( isset( $parameters['notification_level'] ) ) {
				$updates['notification_level'] = sanitize_key( $parameters['notification_level'] );
			}
			
			// Apply updates
			if ( ! empty( $updates ) ) {
				Guardian_Manager::update_settings( $updates );
			}
			
			return [
				'success' => true,
				'message' => __( 'Guardian settings updated successfully', 'wpshadow' ),
				'updated_settings' => $updates,
			];
		} catch ( \Exception $e ) {
			return [
				'success' => false,
				'message' => sprintf(
					__( 'Failed to configure Guardian: %s', 'wpshadow' ),
					$e->getMessage()
				),
			];
		}
	}
}
