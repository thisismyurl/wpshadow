<?php
/**
 * Limit Login Attempts Database Cleanup Diagnostic
 *
 * Limit Login Attempts Database Cleanup issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1456.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Limit Login Attempts Database Cleanup Diagnostic Class
 *
 * @since 1.1456.0000
 */
class Diagnostic_LimitLoginAttemptsDatabaseCleanup extends Diagnostic_Base {

	protected static $slug = 'limit-login-attempts-database-cleanup';
	protected static $title = 'Limit Login Attempts Database Cleanup';
	protected static $description = 'Limit Login Attempts Database Cleanup issue found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Limit_Login_Attempts' ) && ! get_option( 'limit_login_attempts_db_cleanup', '' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: DB cleanup enabled
		$cleanup_enabled = get_option( 'limit_login_attempts_db_cleanup', 0 );
		if ( ! $cleanup_enabled ) {
			$issues[] = 'Database cleanup not enabled';
		}

		// Check 2: Retention days configured
		$retention_days = absint( get_option( 'limit_login_attempts_retention_days', 0 ) );
		if ( $retention_days <= 0 ) {
			$issues[] = 'Retention period not configured';
		}

		// Check 3: Cleanup schedule configured
		$cleanup_schedule = get_option( 'limit_login_attempts_cleanup_schedule', '' );
		if ( empty( $cleanup_schedule ) ) {
			$issues[] = 'Cleanup schedule not configured';
		}

		// Check 4: Max log entries configured
		$max_entries = absint( get_option( 'limit_login_attempts_max_entries', 0 ) );
		if ( $max_entries <= 0 ) {
			$issues[] = 'Max log entries not configured';
		}

		// Check 5: Auto cleanup after lockout
		$auto_cleanup = get_option( 'limit_login_attempts_auto_cleanup', 0 );
		if ( ! $auto_cleanup ) {
			$issues[] = 'Automatic cleanup after lockouts not enabled';
		}

		// Check 6: Cleanup of expired lockouts
		$cleanup_expired = get_option( 'limit_login_attempts_cleanup_expired', 0 );
		if ( ! $cleanup_expired ) {
			$issues[] = 'Expired lockout cleanup not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d database cleanup issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/limit-login-attempts-database-cleanup',
			);
		}

		return null;
	}
}
