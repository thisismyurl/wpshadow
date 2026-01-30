<?php
/**
 * wpForo Member Roles Diagnostic
 *
 * wpForo member roles misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.532.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wpForo Member Roles Diagnostic Class
 *
 * @since 1.532.0000
 */
class Diagnostic_WpforoMemberRoles extends Diagnostic_Base {

	protected static $slug = 'wpforo-member-roles';
	protected static $title = 'wpForo Member Roles';
	protected static $description = 'wpForo member roles misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WPFORO_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Custom usergroups configured
		$usergroups = get_option( 'wpforo_usergroups', array() );
		if ( empty( $usergroups ) ) {
			$issues[] = 'no member roles configured';
		} elseif ( count( $usergroups ) > 20 ) {
			$issues[] = count( $usergroups ) . ' member roles (complex permission management)';
		}
		
		// Check 2: Guest permissions
		if ( ! empty( $usergroups ) ) {
			foreach ( $usergroups as $group ) {
				if ( isset( $group['name'] ) && 'Guest' === $group['name'] ) {
					if ( ! empty( $group['cans']['vpst'] ) ) {
						$issues[] = 'guests can create posts (spam risk)';
					}
				}
			}
		}
		
		// Check 3: Role hierarchy conflicts
		$role_conflicts = get_transient( 'wpforo_role_conflicts' );
		if ( ! empty( $role_conflicts ) ) {
			$issues[] = 'role hierarchy conflicts detected';
		}
		
		// Check 4: WordPress role synchronization
		$sync_enabled = get_option( 'wpforo_role_sync', '1' );
		if ( '0' === $sync_enabled ) {
			$issues[] = 'wpForo roles not synced with WordPress roles';
		}
		
		// Check 5: Admin capabilities
		if ( ! empty( $usergroups ) ) {
			$admin_groups = array_filter( $usergroups, function( $group ) {
				return isset( $group['cans']['modr'] ) && ! empty( $group['cans']['modr'] );
			} );
			if ( count( $admin_groups ) > 5 ) {
				$issues[] = count( $admin_groups ) . ' groups with moderation powers (security concern)';
			}
		}
		
		// Check 6: Default usergroup
		$default_group = get_option( 'wpforo_default_groupid', 0 );
		if ( empty( $default_group ) ) {
			$issues[] = 'no default usergroup set (registration may fail)';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'wpForo member role security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wpforo-member-roles',
			);
		}
		
		return null;
	}
}
