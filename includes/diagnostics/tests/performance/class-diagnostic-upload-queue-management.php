<?php
/**
 * Upload Queue Management Diagnostic
 *
 * Tests multiple simultaneous uploads. Detects queue failures and race conditions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Upload_Queue_Management Class
 *
 * Validates upload queue handling for simultaneous uploads. WordPress/Plupload
 * manages upload queues client-side. Server configuration and race conditions
 * can cause queue failures when multiple files upload concurrently.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Upload_Queue_Management extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'upload-queue-management';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Upload Queue Management';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Tests multiple simultaneous uploads';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - max_file_uploads limit
	 * - Concurrent request handling
	 * - Database deadlock detection
	 * - Race condition patterns
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check max_file_uploads limit.
		$max_file_uploads = (int) ini_get( 'max_file_uploads' );
		if ( $max_file_uploads > 0 && $max_file_uploads < 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of files */
				__( 'max_file_uploads (%d) is low - limits simultaneous uploads', 'wpshadow' ),
				$max_file_uploads
			);
		}

		// Check Plupload queue configuration.
		$plupload_settings = wp_plupload_default_settings();

		// Check max connections (Plupload default is 3).
		if ( isset( $plupload_settings['max_retries'] ) ) {
			$max_retries = (int) $plupload_settings['max_retries'];
			if ( $max_retries < 1 ) {
				$issues[] = __( 'Plupload max_retries is 0 - queue failures won\'t retry', 'wpshadow' );
			}
		}

		// Check for database race conditions in uploads.
		global $wpdb;

		// Find duplicate post_name values created within same second (race condition).
		$race_conditions = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM (
				SELECT post_name, post_date, COUNT(*) as cnt
				FROM {$wpdb->posts}
				WHERE post_type = 'attachment'
				AND post_status = 'inherit'
				AND post_date > DATE_SUB(NOW(), INTERVAL 7 DAY)
				GROUP BY post_name, DATE_FORMAT(post_date, '%Y-%m-%d %H:%i:%s')
				HAVING cnt > 1
			) AS dupes"
		);

		if ( $race_conditions > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of race conditions */
				_n(
					'%d potential race condition detected in upload queue',
					'%d potential race conditions detected in upload queue',
					$race_conditions,
					'wpshadow'
				),
				$race_conditions
			);
		}

		// Check for database deadlocks (common with simultaneous uploads).
		$deadlock_errors = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND option_value LIKE %s",
				$wpdb->esc_like( '_transient_upload_error_' ) . '%',
				'%deadlock%'
			)
		);

		if ( $deadlock_errors > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of deadlocks */
				_n(
					'%d database deadlock detected during uploads',
					'%d database deadlocks detected during uploads',
					$deadlock_errors,
					'wpshadow'
				),
				$deadlock_errors
			);
		}

		// Check for failed simultaneous uploads.
		$failed_simultaneous = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts} p1
				INNER JOIN {$wpdb->posts} p2
					ON DATE_FORMAT(p1.post_date, '%%Y-%%m-%%d %%H:%%i:%%s') = DATE_FORMAT(p2.post_date, '%%Y-%%m-%%d %%H:%%i:%%s')
					AND p1.ID != p2.ID
				WHERE p1.post_type = 'attachment'
				AND p1.post_status = 'auto-draft'
				AND p2.post_type = 'attachment'
				AND p1.post_date > %s",
				gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
			)
		);

		if ( $failed_simultaneous > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of failures */
				_n(
					'%d failed upload during simultaneous upload attempt',
					'%d failed uploads during simultaneous upload attempts',
					$failed_simultaneous,
					'wpshadow'
				),
				$failed_simultaneous
			);
		}

		// Check web server concurrent connection limit.
		if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			$server_software = $_SERVER['SERVER_SOFTWARE'];

			// Apache with mpm_prefork has lower limits.
			if ( false !== stripos( $server_software, 'apache' ) ) {
				if ( function_exists( 'apache_get_modules' ) ) {
					$modules = apache_get_modules();
					if ( in_array( 'mpm_prefork_module', $modules, true ) ) {
						$issues[] = __( 'Apache using mpm_prefork - may limit concurrent uploads', 'wpshadow' );
					}
				}
			}

			// Nginx worker_connections check (can't check directly, but note).
			if ( false !== stripos( $server_software, 'nginx' ) ) {
				$issues[] = __( 'Nginx detected - ensure worker_connections is adequate for concurrent uploads', 'wpshadow' );
			}
		}

		// Check for max_connections limit in PHP-FPM.
		if ( function_exists( 'php_sapi_name' ) && 'fpm-fcgi' === php_sapi_name() ) {
			// Can't directly check pm.max_children, but warn about it.
			$issues[] = __( 'PHP-FPM detected - ensure pm.max_children is adequate for concurrent uploads', 'wpshadow' );
		}

		// Check for file locking issues.
		$upload_dir = wp_upload_dir();
		$test_file  = $upload_dir['path'] . '/wpshadow-lock-test.txt';

		// Test file locking.
		$lock_works = true;
		if ( wp_is_writable( $upload_dir['path'] ) ) {
			$fp = @fopen( $test_file, 'w' );
			if ( $fp ) {
				if ( ! @flock( $fp, LOCK_EX ) ) {
					$lock_works = false;
				}
				@flock( $fp, LOCK_UN );
				fclose( $fp );
				@unlink( $test_file );
			}
		}

		if ( ! $lock_works ) {
			$issues[] = __( 'File locking not working - simultaneous uploads may corrupt files', 'wpshadow' );
		}

		// Check for session locking (blocks concurrent AJAX).
		if ( function_exists( 'session_status' ) && PHP_SESSION_ACTIVE === session_status() ) {
			$issues[] = __( 'PHP session active - may block concurrent upload requests', 'wpshadow' );
		}

		// Query cache was removed in MySQL 8.0, so skip this check there.
		$db_version = $wpdb->db_version();
		if ( version_compare( $db_version, '8.0', '<' ) ) {
			$query_cache_size = $wpdb->get_var( 'SELECT @@query_cache_size' );
			if ( is_numeric( $query_cache_size ) && (int) $query_cache_size > 0 ) {
				$issues[] = __( 'MySQL query cache enabled - may cause race conditions (deprecated in MySQL 5.7.20+)', 'wpshadow' );
			}
		}

		// Check for InnoDB lock wait timeout.
		$lock_wait_timeout = $wpdb->get_var( "SELECT @@innodb_lock_wait_timeout" );
		if ( $lock_wait_timeout && $lock_wait_timeout < 10 ) {
			$issues[] = sprintf(
				/* translators: %d: seconds */
				__( 'innodb_lock_wait_timeout (%d seconds) is low - uploads may fail during contention', 'wpshadow' ),
				$lock_wait_timeout
			);
		}

		// information_schema.INNODB_LOCKS does not exist on newer MySQL versions,
		// so only run this check for older environments where it is available.
		if ( version_compare( $db_version, '8.0', '<' ) ) {
			$table_locks = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM information_schema.INNODB_LOCKS
					WHERE lock_table = %s",
					$wpdb->dbname . '/' . $wpdb->postmeta
				)
			);

			if ( is_numeric( $table_locks ) && (int) $table_locks > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of locks */
					_n(
						'%d table lock on postmeta - affecting upload queue',
						'%d table locks on postmeta - affecting upload queue',
						(int) $table_locks,
						'wpshadow'
					),
					(int) $table_locks
				);
			}
		}

		// Check for auto-increment lock mode (affects concurrent inserts).
		$auto_increment_mode = $wpdb->get_var( "SELECT @@innodb_autoinc_lock_mode" );
		if ( 0 == $auto_increment_mode ) {
			$issues[] = __( 'innodb_autoinc_lock_mode is 0 (traditional) - reduces concurrent insert performance', 'wpshadow' );
		}

		// Check for max_execution_time (queue processing needs time).
		$max_execution = (int) ini_get( 'max_execution_time' );
		if ( $max_execution > 0 && $max_execution < 60 ) {
			$issues[] = sprintf(
				/* translators: %d: seconds */
				__( 'max_execution_time (%d seconds) is low - queue processing may timeout', 'wpshadow' ),
				$max_execution
			);
		}

		// Check for memory_limit (queue processing memory usage).
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
		$min_memory   = 128 * 1024 * 1024; // 128MB minimum.

		if ( $memory_limit > 0 && $memory_limit < $min_memory ) {
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'memory_limit (%s) is low - queue processing may fail', 'wpshadow' ),
				size_format( $memory_limit )
			);
		}

		// Return finding if issues detected.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d issue detected with upload queue management',
						'%d issues detected with upload queue management',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/upload-queue-management?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issues'              => $issues,
					'max_file_uploads'    => $max_file_uploads,
					'race_conditions'     => $race_conditions,
					'deadlock_errors'     => $deadlock_errors,
					'failed_simultaneous' => $failed_simultaneous,
					'file_locking'        => $lock_works ? 'Working' : 'Failed',
				),
			);
		}

		return null;
	}
}
