<?php declare(strict_types=1);
/**
 * Feature: Maintenance Cleanup
 *
 * Monitor and auto-cleanup maintenance mode, upgrade temp files, and cache bloat.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class WPSHADOW_Feature_Maintenance_Cleanup extends WPSHADOW_Abstract_Feature {

	const MAX_MAINT_DURATION = 120; // 2 hours

	public function __construct() {
		parent::__construct( array(
			'id'          => 'maintenance-cleanup',
			'name'        => __( 'Fix Stuck Updates', 'wpshadow' ),
			'description' => __( 'Watch for and fix problems when updates get stuck, leaving your site showing a "maintenance mode" message.', 'wpshadow' ),
			'aliases'     => array( 'maintenance mode', 'stuck update', 'update stuck', 'maintenance file', 'update problems', 'upgrade temp', 'stuck maintenance', 'update cleanup', 'cache cleanup', 'update errors', 'maintenance alert', 'update monitoring' ),
			'sub_features' => array(
				'cleanup_maintenance'  => array(
					'name'               => __( 'Auto-Cleanup Maintenance Mode', 'wpshadow' ),
					'description_short'  => __( 'Automatically remove stuck maintenance files', 'wpshadow' ),
					'description_long'   => __( 'Monitors for and automatically removes stuck WordPress maintenance files that prevent your site from being accessible. When WordPress updates, it creates a .maintenance file. If the update process fails or crashes, this file isn\'t deleted, leaving your site showing a "maintenance mode" message to visitors. This feature automatically cleans it up after 2+ hours of being stuck.', 'wpshadow' ),
					'description_wizard' => __( 'If updates fail and leave your site stuck in maintenance mode, this automatically fixes it. Very helpful for preventing downtime from stuck updates.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'cleanup_upgrade_temp' => array(
					'name'               => __( 'Remove Upgrade Temp Files', 'wpshadow' ),
					'description_short'  => __( 'Clean up leftover update files', 'wpshadow' ),
					'description_long'   => __( 'Removes temporary files and directories left behind by WordPress update processes. Upgrades create temp files in wp-content/upgrade directory that should be cleaned up automatically but sometimes aren\'t. This leaves old backup files and temporary upgrade directories cluttering your server. This feature cleans them up to free disk space and improve security.', 'wpshadow' ),
					'description_wizard' => __( 'Upgrade temp files can accumulate and waste disk space. This automatically cleans them up after a few hours.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'cleanup_cache'        => array(
					'name'               => __( 'Remove Old Cache Files', 'wpshadow' ),
					'description_short'  => __( 'Delete expired temporary files', 'wpshadow' ),
					'description_long'   => __( 'Removes expired cache files and old temporary files that accumulate on your server over time. WordPress and plugins create temporary files for caching, backup generation, and other processes. If these aren\'t cleaned up, they can waste significant disk space and slow down your server. This runs periodically to keep your server clean.', 'wpshadow' ),
					'description_wizard' => __( 'Cache and temp files build up over time and waste disk space. This automatically removes old, expired files.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'auto_alerts'          => array(
					'name'               => __( 'Auto-Alerts for Stuck Updates', 'wpshadow' ),
					'description_short'  => __( 'Alert when updates get stuck', 'wpshadow' ),
					'description_long'   => __( 'Sends email and site notifications when the maintenance mode file is stuck, indicating an update process failed to complete. This early warning lets you address the issue quickly before it causes too much downtime. The notification includes guidance on how to fix the issue and how long the file will be left before auto-cleanup occurs.', 'wpshadow' ),
					'description_wizard' => __( 'Get alerted immediately when updates get stuck, so you can investigate and fix issues quickly.', 'wpshadow' ),
					'default_enabled'    => true,
				),
			),
		) );

		$this->register_default_settings( array(
			'cleanup_maintenance'  => true,
			'cleanup_upgrade_temp' => true,
			'cleanup_cache'        => true,
			'auto_alerts'          => true,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'admin_init', array( $this, 'check_maintenance_mode' ) );
		add_action( 'wp_scheduled_delete', array( $this, 'run_cleanup' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Check maintenance mode status.
	 */
	public function check_maintenance_mode(): void {
		if ( ! current_user_can( 'update_core' ) ) {
			return;
		}

		$maint_file = ABSPATH . '.maintenance';
		if ( ! file_exists( $maint_file ) ) {
			return;
		}

		$mtime = filemtime( $maint_file );
		$age_hours = ( time() - $mtime ) / 3600;

		if ( $age_hours > 2 && $this->is_sub_feature_enabled( 'cleanup_maintenance', true ) ) {
			if ( $this->is_sub_feature_enabled( 'auto_alerts', true ) ) {
				set_transient( 'wpshadow_maint_stuck_alert', true, HOUR_IN_SECONDS );

				// Send email notification (once per stuck event)
				if ( ! get_transient( 'wpshadow_maint_email_sent' ) ) {
					$admin_email = get_option( 'admin_email' );
					$subject = sprintf(
						'[%s] Maintenance Mode Stuck',
						get_bloginfo( 'name' )
					);
					$message = sprintf(
						"Your WordPress site has been in maintenance mode for %.1f hours.\n\nThis usually means an update process didn't complete properly.\n\nWPShadow will automatically remove the maintenance file if it remains stuck for more than 6 hours.\n\nSite: %s\nTime Detected: %s",
						$age_hours,
						home_url(),
						wp_date( 'Y-m-d H:i:s' )
					);
					wp_mail( $admin_email, $subject, $message );
					set_transient( 'wpshadow_maint_email_sent', true, 6 * HOUR_IN_SECONDS );
				}
			}

			// Auto-cleanup if stuck too long
			if ( $age_hours > 6 ) {
				@unlink( $maint_file );
				// Clear email sent flag after cleanup
				delete_transient( 'wpshadow_maint_email_sent' );
			}
		}
	}

	/**
	 * Run maintenance cleanup.
	 */
	public function run_cleanup(): void {
		if ( $this->is_sub_feature_enabled( 'cleanup_maintenance', true ) ) {
			$this->cleanup_maintenance_files();
		}

		if ( $this->is_sub_feature_enabled( 'cleanup_upgrade_temp', true ) ) {
			$this->cleanup_upgrade_temp();
		}

		if ( $this->is_sub_feature_enabled( 'cleanup_cache', true ) ) {
			$this->cleanup_expired_cache();
		}
	}

	/**
	 * Cleanup maintenance files.
	 */
	private function cleanup_maintenance_files(): void {
		$maint_file = ABSPATH . '.maintenance';
		if ( file_exists( $maint_file ) ) {
			$mtime = filemtime( $maint_file );
			if ( ( time() - $mtime ) > self::MAX_MAINT_DURATION * 60 ) {
				@unlink( $maint_file );
				$this->log_activity( 'Maintenance Cleanup', 'Removed stuck maintenance file', 'info' );
			}
		}
	}

	/**
	 * Cleanup upgrade temp directories.
	 */
	private function cleanup_upgrade_temp(): void {
		$wp_content = WP_CONTENT_DIR;
		$upgrade_dir = $wp_content . '/upgrade';
		$backup_dir = $wp_content . '/upgrade-temp-backup';

		foreach ( array( $upgrade_dir, $backup_dir ) as $dir ) {
			if ( is_dir( $dir ) ) {
				$mtime = filemtime( $dir );
				if ( ( time() - $mtime ) > self::MAX_MAINT_DURATION * 60 ) {
					$this->remove_dir_recursive( $dir );
				}
			}
		}
	}

	/**
	 * Cleanup expired transients.
	 */
	private function cleanup_expired_cache(): void {
		global $wpdb;

		// Delete expired transients
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d LIMIT 100",
				'%_transient_timeout_%',
				time()
			)
		);
	}

	/**
	 * Remove directory recursively.
	 */
	private function remove_dir_recursive( string $dir ): bool {
		if ( ! is_dir( $dir ) ) {
			return false;
		}

		$files = glob( $dir . '/*' );
		foreach ( $files as $file ) {
			if ( is_dir( $file ) ) {
				$this->remove_dir_recursive( $file );
			} else {
				@unlink( $file );
			}
		}

		return @rmdir( $dir );
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['maintenance_cleanup'] = array(
			'label'  => __( 'Maintenance Cleanup', 'wpshadow' ),
			'test'   => array( $this, 'test_maintenance' ),
		);

		return $tests;
	}

	public function test_maintenance(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Maintenance Cleanup', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable maintenance cleanup monitoring.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'maintenance_cleanup',
			);
		}

		$maint_file = ABSPATH . '.maintenance';
		$status = 'good';
		$desc = __( 'No maintenance issues detected.', 'wpshadow' );

		if ( file_exists( $maint_file ) ) {
			$mtime = filemtime( $maint_file );
			$age_hours = ( time() - $mtime ) / 3600;

			if ( $age_hours > 2 ) {
				$status = 'critical';
				$desc = sprintf( __( 'Maintenance mode stuck for %d hours.', 'wpshadow' ), (int) $age_hours );
			} else {
				$status = 'recommended';
				$desc = __( 'Site in maintenance mode.', 'wpshadow' );
			}
		}

		return array(
			'label'       => __( 'Maintenance Cleanup', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => $desc,
			'actions'     => '',
			'test'        => 'maintenance_cleanup',
		);
	}
}
