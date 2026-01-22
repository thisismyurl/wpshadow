<?php
/**
 * Treatment: Optimize Heartbeat API
 *
 * Slows down or disables Heartbeat API on specific pages.
 *
 * Philosophy: Ridiculously Good (#7) - Free server resource optimization
 * KB Link: https://wpshadow.com/kb/heartbeat-api-overhead
 * Training: https://wpshadow.com/training/heartbeat-api-overhead
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Optimize Heartbeat API treatment
 */
class Treatment_Optimize_Heartbeat_API extends Treatment_Base {

	/**
	 * Apply the treatment
	 *
	 * @param array $options Treatment options
	 * @return bool Success status
	 */
	public static function apply( array $options = [] ): bool {
		// Default settings: slow down heartbeat
		$settings = wp_parse_args(
			$options,
			[
				'dashboard'  => 60,   // 60 seconds on dashboard
				'frontend'   => 'disabled', // Disable on frontend
				'post_edit'  => 30,   // 30 seconds on post editor (for autosave)
			]
		);

		// Create backup
		$backup = [
			'previous_settings' => get_option( 'wpshadow_heartbeat_settings', [] ),
			'timestamp'         => time(),
		];
		self::create_backup( $backup );

		// Store settings
		update_option( 'wpshadow_heartbeat_settings', $settings );

		// Hook to modify heartbeat
		add_filter( 'heartbeat_settings', [ __CLASS__, 'modify_heartbeat_settings' ] );

		// Track KPI
		KPI_Tracker::record_treatment_applied( __CLASS__, 3 );

		return true;
	}

	/**
	 * Modify heartbeat settings
	 *
	 * @param array $settings Current heartbeat settings
	 * @return array Modified settings
	 */
	public static function modify_heartbeat_settings( array $settings ): array {
		$wpshadow_settings = get_option( 'wpshadow_heartbeat_settings', [] );

		if ( empty( $wpshadow_settings ) ) {
			return $settings;
		}

		// Determine context
		if ( is_admin() ) {
			global $pagenow;
			
			if ( 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
				// Post editor
				if ( isset( $wpshadow_settings['post_edit'] ) ) {
					if ( $wpshadow_settings['post_edit'] === 'disabled' ) {
						wp_deregister_script( 'heartbeat' );
					} else {
						$settings['interval'] = (int) $wpshadow_settings['post_edit'];
					}
				}
			} else {
				// Dashboard
				if ( isset( $wpshadow_settings['dashboard'] ) ) {
					if ( $wpshadow_settings['dashboard'] === 'disabled' ) {
						wp_deregister_script( 'heartbeat' );
					} else {
						$settings['interval'] = (int) $wpshadow_settings['dashboard'];
					}
				}
			}
		} else {
			// Frontend
			if ( isset( $wpshadow_settings['frontend'] ) && $wpshadow_settings['frontend'] === 'disabled' ) {
				wp_deregister_script( 'heartbeat' );
			}
		}

		return $settings;
	}

	/**
	 * Undo the treatment
	 *
	 * @return bool Success status
	 */
	public static function undo(): bool {
		delete_option( 'wpshadow_heartbeat_settings' );
		remove_filter( 'heartbeat_settings', [ __CLASS__, 'modify_heartbeat_settings' ] );
		return true;
	}

	/**
	 * Get display name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Optimize Heartbeat API', 'wpshadow' );
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return sprintf(
			__( 'Slows down or disables WordPress Heartbeat API to reduce server load. Heartbeat polls your server constantly for autosave, post locking, and notifications. <a href="%s" target="_blank">Learn about heartbeat optimization</a>', 'wpshadow' ),
			'https://wpshadow.com/kb/heartbeat-api-overhead'
		);
	}
}
