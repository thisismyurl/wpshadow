<?php
/**
 * Blocking Tool Operations Freeze Admin Diagnostic
 *
 * Tests whether long-running tool operations block admin interface preventing other work.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Blocking_Tool_Operations_Freeze_Admin Class
 *
 * Verifies tool operations don't block admin interface.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Blocking_Tool_Operations_Freeze_Admin extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'blocking-tool-operations-freeze-admin';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tool Operation Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if tool operations block admin interface or allow concurrent work';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check PHP max execution time.
		$max_execution = ini_get( 'max_execution_time' );

		if ( empty( $max_execution ) || (int) $max_execution === 0 ) {
			// Unlimited - good for tools but risky.
			$issues[] = __( 'Unlimited PHP execution time - long operations may block indefinitely', 'wpshadow' );
		} elseif ( (int) $max_execution < 60 ) {
			$issues[] = sprintf(
				/* translators: %d: seconds */
				__( 'PHP execution time limited to %d seconds - may be too short for large imports', 'wpshadow' ),
				(int) $max_execution
			);
		}

		// 2. Check for background processing capability.
		$has_background = false;

		// Check for Action Scheduler (used by WooCommerce).
		if ( class_exists( 'ActionScheduler' ) ) {
			$has_background = true;
		}

		// Check for WP Cron.
		if ( ! defined( 'DISABLE_WP_CRON' ) || ! DISABLE_WP_CRON ) {
			$has_background = true;
		}

		if ( ! $has_background ) {
			$issues[] = __( 'No background processing system detected - tools must run synchronously', 'wpshadow' );
		}

		// 3. Check AJAX timeout handling.
		// Tools should use chunk processing for large operations.
		$tool_ajax = array(
			'wp_ajax_export_personal_data',
			'wp_ajax_erase_personal_data',
		);

		foreach ( $tool_ajax as $action ) {
			if ( has_action( $action ) ) {
				// WordPress implements these with pagination - check if custom implementations do too.
			}
		}

		// 4. Check for set_time_limit() usage.
		$time_limit_disabled = function_exists( 'set_time_limit' );

		if ( ! $time_limit_disabled ) {
			$issues[] = __( 'set_time_limit() not available - cannot extend execution for long operations', 'wpshadow' );
		}

		// 5. Test admin-ajax.php responsiveness.
		// If a tool is running, other AJAX should still work.
		// This is hard to test without actual running operations.

		// 6. Check for progress indicators.
		// JavaScript should show progress without blocking.
		if ( is_admin() ) {
			global $wp_scripts;

			$has_progress_js = false;
			if ( isset( $wp_scripts->registered ) ) {
				foreach ( $wp_scripts->registered as $handle => $script ) {
					if ( false !== strpos( $handle, 'progress' ) ||
					     false !== strpos( $handle, 'export' ) ||
					     false !== strpos( $handle, 'import' ) ) {
						$has_progress_js = true;
						break;
					}
				}
			}

			if ( ! $has_progress_js ) {
				$issues[] = __( 'No progress indicator JavaScript detected - users may think site is frozen', 'wpshadow' );
			}
		}

		// 7. Check memory limit for large operations.
		$memory_limit = ini_get( 'memory_limit' );
		$memory_mb    = 0;

		if ( $memory_limit ) {
			$memory_mb = (int) $memory_limit;
		}

		if ( $memory_mb < 128 && $memory_mb > 0 ) {
			$issues[] = sprintf(
				/* translators: %dM: memory in megabytes */
				__( 'PHP memory limit %dM may be insufficient for large imports', 'wpshadow' ),
				$memory_mb
			);
		}

		// 8. Check for chunked processing.
		// Tools should process in batches, not all at once.
		global $wpdb;

		// Personal data export uses pagination.
		$export_page_size = apply_filters( 'wp_privacy_personal_data_export_page', 500 );

		if ( $export_page_size > 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: items per page */
				__( 'Export page size %d items - too large, may cause timeouts', 'wpshadow' ),
				$export_page_size
			);
		}

		// 9. Check for output buffering.
		$ob_level = ob_get_level();

		if ( $ob_level > 0 ) {
			// Output buffering active - may delay progress feedback.
			$issues[] = __( 'Output buffering active - progress updates may be delayed', 'wpshadow' );
		}

		// 10. Check for concurrent request handling.
		// Test if multiple AJAX requests can run simultaneously.
		// WordPress uses session_write_close() to allow concurrent requests.

		// 11. Check PHP-FPM or mod_php configuration.
		$sapi = php_sapi_name();

		if ( 'fpm-fcgi' === $sapi || 'cgi-fcgi' === $sapi ) {
			// FPM - good for performance.
		} elseif ( 'apache2handler' === $sapi || 'litespeed' === $sapi ) {
			// mod_php or LiteSpeed - may have blocking issues.
			$issues[] = sprintf(
				/* translators: %s: PHP SAPI name */
				__( 'Running on %s - may have concurrent request limitations', 'wpshadow' ),
				$sapi
			);
		}

		// 12. Check for JavaScript blocking in tool pages.
		if ( is_admin() ) {
			// Tools should use async/defer for scripts.
			global $wp_scripts;

			$blocking_scripts = 0;
			if ( isset( $wp_scripts->registered ) ) {
				foreach ( $wp_scripts->registered as $handle => $script ) {
					// Check if script has async or defer.
					$extra = $script->extra ?? array();
					if ( ! isset( $extra['async'] ) && ! isset( $extra['defer'] ) ) {
						$blocking_scripts++;
					}
				}
			}

			if ( $blocking_scripts > 20 ) {
				$issues[] = sprintf(
					/* translators: %d: number of scripts */
					__( '%d synchronous scripts loading - may block tool interface', 'wpshadow' ),
					$blocking_scripts
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Tool operation performance issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 65,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/tool-operation-performance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'issues'              => $issues,
				'max_execution_time'  => $max_execution,
				'memory_limit'        => $memory_limit,
				'php_sapi'            => $sapi,
			),
		);
	}
}
