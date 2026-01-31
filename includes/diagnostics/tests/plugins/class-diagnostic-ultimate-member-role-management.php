<?php
/**
 * Ultimate Member Role Management Diagnostic
 *
 * Ultimate Member roles misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.522.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ultimate Member Role Management Diagnostic Class
 *
 * @since 1.522.0000
 */
class Diagnostic_UltimateMemberRoleManagement extends Diagnostic_Base {

	protected static $slug = 'ultimate-member-role-management';
	protected static $title = 'Ultimate Member Role Management';
	protected static $description = 'Ultimate Member roles misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'ultimatemember_version' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Custom roles defined
		$roles = get_option( 'um_custom_roles_count', 0 );
		if ( $roles <= 0 ) {
			$issues[] = 'Custom roles not configured';
		}

		// Check 2: Role capabilities
		$caps = get_option( 'um_role_capabilities_configured', 0 );
		if ( ! $caps ) {
			$issues[] = 'Role capabilities not properly configured';
		}

		// Check 3: Permission inheritance
		$inherit = get_option( 'um_permission_inheritance_enabled', 0 );
		if ( ! $inherit ) {
			$issues[] = 'Permission inheritance not enabled';
		}

		// Check 4: Role-based access control
		$rbac = get_option( 'um_rbac_enabled', 0 );
		if ( ! $rbac ) {
			$issues[] = 'Role-based access control not enabled';
		}

		// Check 5: Privilege escalation protection
		$priv = get_option( 'um_privilege_escalation_protection_enabled', 0 );
		if ( ! $priv ) {
			$issues[] = 'Privilege escalation protection not enabled';
		}

		// Check 6: Audit logging
		$audit = get_option( 'um_role_change_audit_logging_enabled', 0 );
		if ( ! $audit ) {
			$issues[] = 'Role change audit logging not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 50;
			$threat_multiplier = 6;
			$max_threat = 80;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d role management issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/ultimate-member-role-management',
			);
		}

		return null;
	}
}
