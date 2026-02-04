<?php
/**
 * Export Process Blocking Admin Access
 *
 * Tests whether running export locks admin interface for other users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Export
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Export_Process_Blocking_Admin_Access Class
 *
 * Tests whether export processes block admin access for other users.
 * Checks for database locks, concurrent user handling, and background processing.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Export_Process_Blocking_Admin_Access extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'export-process-blocking-admin-access';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Export Blocking Admin Access';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that export does not lock admin interface for other users';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests for blocking behavior and suggests background processing.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if export uses blocking operations
		$issues = array();

		// 1. Check if background processing is enabled
		if ( ! self::has_background_processing() ) {
			$issues[] = __( 'Export runs synchronously (blocking)', 'wpshadow' );
		}

		// 2. Check for database lock risks
		$lock_risks = self::check_database_lock_risks();
		if ( $lock_risks ) {
			$issues[] = $lock_risks;
		}

		// 3. Check for concurrent user handling
		if ( ! self::has_concurrent_user_handling() ) {
			$issues[] = __( 'Export may prevent other users from accessing admin', 'wpshadow' );
		}

		// 4. Check for timeout risks
		$timeout_risk = self::check_timeout_risk();
		if ( $timeout_risk ) {
			$issues[] = $timeout_risk;
		}

		// 5. Check for multi-user support
		$user_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );
		if ( $user_count > 1 && ! self::has_multiuser_support() ) {
			$issues[] = sprintf(
				/* translators: %d: number of users */
				__( '%d users detected but no multi-user export handling', 'wpshadow' ),
				$user_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( '%d blocking issues detected that may impact admin access', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => true,
				'details'      => $issues,
				'kb_link'      => 'https://wpshadow.com/kb/export-blocking-admin-access',
				'recommendations' => array(
					__( 'Enable background processing for export tasks', 'wpshadow' ),
					__( 'Use async export with progress tracking', 'wpshadow' ),
					__( 'Implement admin locks that release on completion', 'wpshadow' ),
					__( 'Test concurrent admin access during export', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if background processing is enabled.
	 *
	 * @since  1.6030.2148
	 * @return bool True if background processing available.
	 */
	private static function has_background_processing() {
		// Check for async processing filters
		if ( has_filter( 'wxr_export_post_start' ) || has_filter( 'wxr_export_post_end' ) ) {
			return true;
		}

		// Check for background processing plugins
		$bg_plugins = array(
			'wp-background-tasks/wp-background-tasks.php',
			'async-tasks/async-tasks.php',
			'wp-crontrol/wp-crontrol.php',
		);

		foreach ( $bg_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for async export functionality
		if ( defined( 'WP_EXPORT_ASYNC' ) && WP_EXPORT_ASYNC ) {
			return true;
		}

		// Check if export is being run with WP-CLI (non-blocking)
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for database lock risks.
	 *
	 * @since  1.6030.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_database_lock_risks() {
		global $wpdb;

		// Check database type
		$db_version = $wpdb->get_var( 'SELECT VERSION()' );

		// MySQL/MariaDB with certain storage engines can have lock issues
		if ( ! $db_version ) {
			return null;
		}

		// Check for InnoDB usage (generally better for concurrent access)
		if ( strpos( $db_version, 'MariaDB' ) !== false ) {
			// MariaDB has better lock handling
			return null;
		}

		// Check if posts table uses InnoDB
		$engine = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
				DB_NAME,
				"{$wpdb->posts}"
			)
		);

		if ( 'InnoDB' !== $engine ) {
			return sprintf(
				/* translators: %s: storage engine */
				__( 'Posts table uses %s engine (lock issues with concurrent access)', 'wpshadow' ),
				esc_html( $engine )
			);
		}

		return null;
	}

	/**
	 * Check for concurrent user handling.
	 *
	 * @since  1.6030.2148
	 * @return bool True if concurrent access supported.
	 */
	private static function has_concurrent_user_handling() {
		// Check for session management
		if ( has_filter( 'wp_session_start' ) ) {
			return true;
		}

		// Check for user locking mechanism
		if ( has_filter( 'user_has_cap' ) || has_filter( 'wp_user_roles' ) ) {
			return true;
		}

		// Check if export is processed asynchronously
		if ( wp_doing_ajax() || wp_doing_cron() ) {
			return true;
		}

		return false;
	}

	/**
	 * Check for timeout risk during export.
	 *
	 * @since  1.6030.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_timeout_risk() {
		global $wpdb;

		$max_execution_time = (int) ini_get( 'max_execution_time' );
		$post_count         = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts}" );

		// Estimate: 10ms per post
		$estimated_time = ( $post_count * 10 ) / 1000;

		if ( $estimated_time > $max_execution_time && $max_execution_time > 0 ) {
			return sprintf(
				/* translators: %d: estimated time, %d: timeout limit */
				__( 'Export may timeout: estimated %ds needed but limit is %ds', 'wpshadow' ),
				(int) $estimated_time,
				$max_execution_time
			);
		}

		return null;
	}

	/**
	 * Check for multi-user export support.
	 *
	 * @since  1.6030.2148
	 * @return bool True if multi-user support available.
	 */
	private static function has_multiuser_support() {
		// Check for user role-based export restrictions
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		// Check for collaborative features
		if ( has_filter( 'wp_roles' ) || has_filter( 'user_roles' ) ) {
			return true;
		}

		// WordPress doesn't inherently support multi-user concurrent exports
		return false;
	}
}
