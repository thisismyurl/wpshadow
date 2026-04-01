<?php
/**
 * Admin User Activity Logging Diagnostic
 *
 * Tests if admin user actions are properly logged for security auditing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin User Activity Logging Diagnostic Class
 *
 * Validates that admin actions (login, post edits, settings changes) are
 * logged for security auditing and compliance requirements.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Admin_User_Activity_Logging extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-user-activity-logging';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin User Activity Logging';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if admin user actions are logged for security auditing';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if WordPress has activity logging enabled for admin
	 * actions including logins, post edits, and settings changes.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check for activity logging plugins.
		$has_activity_log = is_plugin_active( 'wpshadow/wpshadow.php' ) ||
						  is_plugin_active( 'simple-history/simple-history.php' ) ||
						  is_plugin_active( 'stream/stream.php' );

		// Check for login logging hooks.
		$has_login_logging = has_action( 'wp_login' ) || has_action( 'wp_authenticate' );

		// Check for post edit hooks.
		$has_post_logging = has_action( 'post_updated' ) || has_action( 'edit_post' );

		// Check for settings hooks.
		$has_settings_logging = has_action( 'update_option' ) || has_action( 'added_option' );

		// Check if any logging is happening.
		$has_logging_plugin = is_plugin_active( 'stream/stream.php' ) ||
							 is_plugin_active( 'simple-history/simple-history.php' ) ||
							 is_plugin_active( 'wpshadow/wpshadow.php' );

		// Check for debug.log usage (basic logging).
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			$has_logging_plugin = true;
		}

		// Check database for activity log tables.
		global $wpdb;
		$activity_tables = $wpdb->get_var(
			"SELECT COUNT(*) FROM information_schema.TABLES
			 WHERE TABLE_SCHEMA = '" . DB_NAME . "'
			 AND (TABLE_NAME LIKE '%stream%' OR TABLE_NAME LIKE '%activity%' OR TABLE_NAME LIKE '%log%')"
		);

		$has_logging_table = absint( $activity_tables ) > 0;

		// Get admin user count.
		$admin_users = count_users();
		$admin_count = $admin_users['avail_roles']['administrator'] ?? 0;

		// Check last login time of admins.
		$last_login_log = $wpdb->get_var(
			"SELECT MAX(user_login) FROM {$wpdb->users}
			 WHERE ID IN (SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'wp_capabilities' AND meta_value LIKE '%administrator%')"
		);

		// Check if logs are being retained.
		$log_retention = 0;
		if ( is_plugin_active( 'stream/stream.php' ) ) {
			$log_retention = absint( get_option( 'stream_records_ttl', 90 ) );
		}

		// Check for security-related activity filters.
		$has_security_monitoring = has_filter( 'wp_authenticate' ) ||
								  has_action( 'wp_login_failed' );

		// Check for issues.
		$issues = array();

		// Issue 1: No activity logging plugin or setup.
		if ( ! $has_logging_plugin && ! $has_logging_table ) {
			$issues[] = array(
				'type'        => 'no_activity_logging',
				'description' => __( 'No activity logging detected; admin actions are not being recorded', 'wpshadow' ),
			);
		}

		// Issue 2: No login logging.
		if ( ! $has_login_logging ) {
			$issues[] = array(
				'type'        => 'no_login_logging',
				'description' => __( 'Login attempts are not being logged; unauthorized access cannot be detected', 'wpshadow' ),
			);
		}

		// Issue 3: No post/content edit logging.
		if ( ! $has_post_logging ) {
			$issues[] = array(
				'type'        => 'no_post_logging',
				'description' => __( 'Post edits and deletions are not being logged; cannot track content changes', 'wpshadow' ),
			);
		}

		// Issue 4: No settings change logging.
		if ( ! $has_settings_logging ) {
			$issues[] = array(
				'type'        => 'no_settings_logging',
				'description' => __( 'Settings changes are not being logged; configuration modifications cannot be audited', 'wpshadow' ),
			);
		}

		// Issue 5: No security event logging (failed logins, etc).
		if ( ! $has_security_monitoring ) {
			$issues[] = array(
				'type'        => 'no_security_logging',
				'description' => __( 'Failed login attempts and security events are not being logged', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Admin user activity is not being logged, which prevents security auditing and compliance requirements', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-user-activity-logging?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'has_activity_log_plugin' => $has_logging_plugin,
					'has_logging_table'       => $has_logging_table,
					'has_login_logging'       => $has_login_logging,
					'has_post_logging'        => $has_post_logging,
					'has_settings_logging'    => $has_settings_logging,
					'has_security_monitoring' => $has_security_monitoring,
					'admin_user_count'        => absint( $admin_count ),
					'activity_log_tables'     => absint( $activity_tables ),
					'log_retention_days'      => $log_retention,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Install WPStream or WPShadow for comprehensive activity logging', 'wpshadow' ),
					'auditable_events'        => array(
						'User logins'             => 'Track who logs in and when',
						'Post edits'              => 'Log post/page changes',
						'Post deletions'          => 'Track deleted content',
						'Settings changes'        => 'Monitor configuration changes',
						'Plugin activation'       => 'Log plugin changes',
						'Theme changes'           => 'Track theme updates',
						'User role changes'       => 'Monitor permission changes',
						'Failed login attempts'   => 'Security threat detection',
					),
					'compliance_benefits'     => array(
						'GDPR'   => 'User action tracking and audit trails',
						'PCI-DSS' => 'Activity logging for payment systems',
						'HIPAA'   => 'Audit trail for healthcare data',
						'SOC 2'   => 'Security event tracking',
					),
				),
			);
		}

		return null;
	}
}
