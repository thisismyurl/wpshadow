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

final class WPSHADOW_Feature_Maintenance_Cleanup extends WPSHADOW_Abstract_Feature {

	const MAX_MAINT_DURATION = 120; // 2 hours

	public function __construct() {
		parent::__construct( array(
			'id'          => 'maintenance-cleanup',
			'name'        => __( 'Fix Stuck Updates', 'wpshadow' ),
			'description' => __( 'Watch for and fix problems when updates get stuck, leaving your site showing a "maintenance mode" message.', 'wpshadow' ),
			'sub_features' => array(
				'cleanup_maintenance'  => __( 'Remove stuck maintenance mode', 'wpshadow' ),
				'cleanup_upgrade_temp' => __( 'Remove leftover update files', 'wpshadow' ),
				'cleanup_cache'        => __( 'Remove old temporary files', 'wpshadow' ),
				'auto_alerts'          => __( 'Alert when updates get stuck', 'wpshadow' ),
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
			}

			// Auto-cleanup if stuck too long
			if ( $age_hours > 6 ) {
				@unlink( $maint_file );
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
