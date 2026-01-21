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
		return 'configure_guardian';
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
	 * Execute the command
	 * 
	 * @return void JSON response
	 */
	public function execute() {
		if ( ! $this->verify_request() ) {
			return;
		}

		try {
			$current = Guardian_Manager::get_settings();
			$updates = [];

			$enabled = $this->get_post_var( 'enabled', null );
			if ( null !== $enabled ) {
				$enabled_bool = rest_sanitize_boolean( $enabled );
				if ( $enabled_bool ) {
					Guardian_Manager::enable();
				} else {
					Guardian_Manager::disable();
				}
				$updates['enabled'] = $enabled_bool;
			}

			$health = $this->get_post_var( 'health_check_interval', '' );
			if ( $health !== '' ) {
				$updates['health_check_interval'] = sanitize_key( $health );
			}

			$auto_fix = $this->get_post_var( 'auto_fix_enabled', null );
			if ( null !== $auto_fix ) {
				$updates['auto_fix_enabled'] = rest_sanitize_boolean( $auto_fix );
			}

			$backup_before_fix = $this->get_post_var( 'backup_before_fix', null );
			if ( null !== $backup_before_fix ) {
				$updates['backup_before_fix'] = rest_sanitize_boolean( $backup_before_fix );
			}

			$notification_level = $this->get_post_var( 'notification_level', '' );
			if ( $notification_level !== '' ) {
				$updates['notification_level'] = sanitize_key( $notification_level );
			}

			$payload = array_merge( $current, $updates );
			Guardian_Manager::update_settings( $payload );

			$this->success( [
				'settings' => $payload,
				'message'  => __( 'Guardian settings updated', 'wpshadow' ),
			] );
		} catch ( \Exception $e ) {
			$this->error( sprintf( __( 'Failed to update Guardian settings: %s', 'wpshadow' ), $e->getMessage() ) );
		}
	}
}
