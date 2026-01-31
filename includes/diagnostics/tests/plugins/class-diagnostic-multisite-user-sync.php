<?php
/**
 * Multisite User Sync Diagnostic
 *
 * Multisite User Sync misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.967.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite User Sync Diagnostic Class
 *
 * @since 1.967.0000
 */
class Diagnostic_MultisiteUserSync extends Diagnostic_Base {

	protected static $slug = 'multisite-user-sync';
	protected static $title = 'Multisite User Sync';
	protected static $description = 'Multisite User Sync misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();

		// Check 1: User sync enabled
		$sync = get_site_option( 'multisite_user_sync_enabled', 0 );
		if ( ! $sync ) {
			$issues[] = 'User sync not enabled';
		}

		// Check 2: Role sync configured
		$role_sync = get_option( 'multisite_role_sync_enabled', 0 );
		if ( ! $role_sync ) {
			$issues[] = 'Role synchronization not configured';
		}

		// Check 3: Profile sync
		$profile_sync = get_option( 'multisite_profile_sync_enabled', 0 );
		if ( ! $profile_sync ) {
			$issues[] = 'Profile data sync not enabled';
		}

		// Check 4: User creation sync
		$create_sync = get_option( 'multisite_user_create_sync_enabled', 0 );
		if ( ! $create_sync ) {
			$issues[] = 'User creation sync not configured';
		}

		// Check 5: Conflict resolution
		$conflict = get_option( 'multisite_user_sync_conflict_resolution', '' );
		if ( empty( $conflict ) ) {
			$issues[] = 'Conflict resolution strategy not set';
		}

		// Check 6: Audit logging
		$audit = get_option( 'multisite_user_sync_audit_logging', 0 );
		if ( ! $audit ) {
			$issues[] = 'User sync audit logging not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d user sync issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-user-sync',
			);
		}

		return null;
	}
}
