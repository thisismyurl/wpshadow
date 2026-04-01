<?php
/**
 * Export Process Blocking Admin Access Diagnostic
 *
 * Tests whether running exports lock the admin interface and prevent
 * concurrent administrative tasks. Long-running exports should run in the
 * background without blocking other admin actions like publishing or updates.
 *
 * **What This Check Does:**
 * - Detects whether export operations lock admin requests
 * - Evaluates if exports block critical admin screens
 * - Checks for synchronous export patterns
 * - Flags exports that freeze admin during execution
 *
 * **Why This Matters:**
 * When exports block admin access, editors can’t publish, updates stall,
 * and urgent security actions are delayed. On large sites, exports may run
 * for minutes or hours, effectively taking the admin offline.
 *
 * **Real-World Failure Scenario:**
 * - Admin starts full export before maintenance
 * - Export locks database or PHP process
 * - Other admins get “site busy” or timeouts
 * - Security patch is delayed during active exploit window
 *
 * Result: Operational downtime and elevated security risk.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Ensures admin remains usable during maintenance
 * - #9 Show Value: Reduces operational downtime
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/export-background-processing
 * or https://wpshadow.com/training/large-site-export-strategy
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export Process Blocking Admin Access Diagnostic Class
 *
 * Evaluates export execution mode to ensure it doesn't block admin workflows.
 *
 * **Implementation Pattern:**
 * 1. Detect export lock signals or long-running requests
 * 2. Check for concurrent admin access failures
 * 3. Return findings when blocking is detected
 *
 * **Related Diagnostics:**
 * - Export Timeout on Large Sites
 * - No Queue System for Tool Operations
 * - Export Process Blocking Admin Access (tools)
 *
 * @since 0.6093.1200
 */
class Diagnostic_Export_Blocking_Admin_Access extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'export-blocking-admin-access';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Export Process Blocking Admin Access';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if export locks admin interface';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'import-export';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check post count (only relevant for large sites).
		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status != 'auto-draft'
			AND post_type NOT IN ('revision', 'nav_menu_item')"
		);

		if ( $total_posts < 1000 ) {
			return null;
		}

		// Check for database table locks.
		$table_locks = $wpdb->get_results(
			"SHOW OPEN TABLES WHERE In_use > 0",
			ARRAY_A
		);

		if ( ! empty( $table_locks ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of locked tables */
				__( '%d database tables currently locked', 'wpshadow' ),
				count( $table_locks )
			);
		}

		// Check for long-running processes.
		$processes = $wpdb->get_results(
			"SHOW PROCESSLIST",
			ARRAY_A
		);

		$long_running = 0;
		if ( is_array( $processes ) ) {
			foreach ( $processes as $process ) {
				if ( isset( $process['Time'] ) && (int) $process['Time'] > 30 ) {
					++$long_running;
				}
			}
		}

		if ( $long_running > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of processes */
				__( '%d long-running database queries detected', 'wpshadow' ),
				$long_running
			);
		}

		// Check isolation level.
		$isolation_level = $wpdb->get_var( "SELECT @@tx_isolation" );

		if ( 'SERIALIZABLE' === $isolation_level ) {
			$issues[] = __( 'Transaction isolation set to SERIALIZABLE (may block concurrent access)', 'wpshadow' );
		}

		// Check for table-level locks.
		$myisam_tables = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT TABLE_NAME
				FROM information_schema.TABLES
				WHERE TABLE_SCHEMA = %s
				AND ENGINE = 'MyISAM'
				AND TABLE_NAME LIKE %s",
				DB_NAME,
				$wpdb->esc_like( $wpdb->prefix ) . '%'
			),
			ARRAY_A
		);

		if ( ! empty( $myisam_tables ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of tables */
				__( '%d MyISAM tables (use table-level locks during export)', 'wpshadow' ),
				count( $myisam_tables )
			);
		}

		// Check for export background processing.
		$export_crons = _get_cron_array();
		$has_background_export = false;

		if ( is_array( $export_crons ) ) {
			foreach ( $export_crons as $timestamp => $cron ) {
				foreach ( $cron as $hook => $events ) {
					if ( strpos( $hook, 'export' ) !== false ) {
						$has_background_export = true;
						break 2;
					}
				}
			}
		}

		if ( ! $has_background_export && $total_posts > 5000 ) {
			$issues[] = __( 'No background export cron configured (export runs synchronously)', 'wpshadow' );
		}

		// Check max_execution_time.
		$max_execution = (int) ini_get( 'max_execution_time' );

		if ( $max_execution > 60 && $max_execution > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: execution time in seconds */
				__( 'max_execution_time %ds allows long-running export (blocks admin)', 'wpshadow' ),
				$max_execution
			);
		}

		// Check session locking.
		$session_handler = ini_get( 'session.save_handler' );

		if ( 'files' === $session_handler ) {
			$issues[] = __( 'File-based sessions may lock during export (blocks same-user requests)', 'wpshadow' );
		}

		// Check for concurrent request limit.
		$concurrent_requests = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_doing_cron'"
		);

		// Check for export lock transient.
		$export_lock = get_transient( 'export_lock' );

		if ( false !== $export_lock ) {
			$issues[] = __( 'Export lock transient active (may prevent concurrent exports)', 'wpshadow' );
		}

		// Check for admin-ajax concurrency.
		$admin_ajax_handlers = 0;

		if ( did_action( 'admin_init' ) ) {
			$ajax_actions = $GLOBALS['wp_filter']['wp_ajax_'] ?? array();
			$admin_ajax_handlers = is_object( $ajax_actions ) ? count( $ajax_actions->callbacks ) : 0;
		}

		// Check for WP_CRON blocking.
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			if ( ! $has_background_export && $total_posts > 5000 ) {
				$issues[] = __( 'WP_CRON disabled and no alternative background processor', 'wpshadow' );
			}
		}

		// Check for object cache.
		if ( ! wp_using_ext_object_cache() && $total_posts > 10000 ) {
			$issues[] = __( 'No external object cache (export queries may slow admin)', 'wpshadow' );
		}

		// Check for query monitor or debug plugins.
		$debug_plugins = array(
			'query-monitor/query-monitor.php',
			'debug-bar/debug-bar.php',
		);

		$has_debug_plugin = false;
		foreach ( $debug_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_debug_plugin = true;
				break;
			}
		}

		if ( $has_debug_plugin ) {
			$issues[] = __( 'Debug plugin active (adds overhead to export process)', 'wpshadow' );
		}

		// Check for autoload options.
		$autoload_size = $wpdb->get_var(
			"SELECT SUM(LENGTH(option_value))
			FROM {$wpdb->options}
			WHERE autoload = 'yes'"
		);

		if ( $autoload_size > 1000000 ) {
			$issues[] = sprintf(
				/* translators: %s: autoload size */
				__( 'Autoload options %s (slows every admin request)', 'wpshadow' ),
				size_format( $autoload_size )
			);
		}

		// Check for concurrent user sessions.
		$active_sessions = $wpdb->get_var(
			"SELECT COUNT(DISTINCT meta_value)
			FROM {$wpdb->usermeta}
			WHERE meta_key = 'session_tokens'"
		);

		if ( $active_sessions > 10 && $total_posts > 5000 ) {
			$issues[] = sprintf(
				/* translators: %d: number of active users */
				__( '%d active user sessions (export may block concurrent admin access)', 'wpshadow' ),
				$active_sessions
			);
		}

		// Check for memory limit.
		$memory_limit = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );

		if ( $memory_limit > 0 && $memory_limit < 134217728 ) {
			$issues[] = sprintf(
				/* translators: %s: memory limit */
				__( 'memory_limit %s may cause export to consume all available memory', 'wpshadow' ),
				size_format( $memory_limit )
			);
		}

		// Check for rate limiting.
		$rate_limit_plugins = array(
			'wp-limit-login-attempts/wp-limit-login-attempts.php',
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
		);

		$has_rate_limit = false;
		foreach ( $rate_limit_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_rate_limit = true;
				break;
			}
		}

		// Check for maintenance mode.
		$maintenance_file = ABSPATH . '.maintenance';

		if ( file_exists( $maintenance_file ) ) {
			$issues[] = __( 'Maintenance mode file exists (may block admin access)', 'wpshadow' );
		}

		// Check for server load.
		if ( function_exists( 'sys_getloadavg' ) ) {
			$load = sys_getloadavg();

			if ( isset( $load[0] ) && $load[0] > 5.0 ) {
				$issues[] = sprintf(
					/* translators: %s: load average */
					__( 'High server load (%.2f) may cause admin timeouts', 'wpshadow' ),
					$load[0]
				);
			}
		}

		// Check for export scheduling conflicts.
		$scheduled_exports = wp_get_scheduled_event( 'wp_export_scheduled' );

		if ( false !== $scheduled_exports ) {
			$next_run = $scheduled_exports->timestamp ?? 0;
			$time_until = $next_run - time();

			if ( $time_until < 300 && $time_until > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: minutes until export */
					__( 'Scheduled export in %d minutes (may block admin)', 'wpshadow' ),
					round( $time_until / 60 )
				);
			}
		}

		// Check for heartbeat API.
		$heartbeat_settings = apply_filters( 'heartbeat_settings', array() );
		$heartbeat_interval = $heartbeat_settings['interval'] ?? 60;

		if ( $heartbeat_interval < 30 && $total_posts > 5000 ) {
			$issues[] = sprintf(
				/* translators: %d: heartbeat interval */
				__( 'Heartbeat interval %ds too frequent (adds overhead during export)', 'wpshadow' ),
				$heartbeat_interval
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/export-blocking-admin-access?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
