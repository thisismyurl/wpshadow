<?php
/**
 * MemberPress Membership Rules Diagnostic
 *
 * MemberPress access rules misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.320.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Membership Rules Diagnostic Class
 *
 * @since 1.320.0000
 */
class Diagnostic_MemberpressMembershipRules extends Diagnostic_Base {

	protected static $slug = 'memberpress-membership-rules';
	protected static $title = 'MemberPress Membership Rules';
	protected static $description = 'MemberPress access rules misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'MEPR_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Access rules enabled
		$rules_enabled = get_option( 'mepr_access_rules_enabled', 0 );
		if ( ! $rules_enabled ) {
			$issues[] = 'Access rules not enabled';
		}
		
		// Check 2: Membership tier restrictions
		$tier_restrict = get_option( 'mepr_membership_tier_restrictions', 0 );
		if ( ! $tier_restrict ) {
			$issues[] = 'Membership tier restrictions not configured';
		}
		
		// Check 3: Drip content enabled
		$drip_enabled = get_option( 'mepr_drip_content_enabled', 0 );
		if ( ! $drip_enabled ) {
			$issues[] = 'Drip content delivery not enabled';
		}
		
		// Check 4: Expiration rules
		$expiration = get_option( 'mepr_membership_expiration_rules', '' );
		if ( empty( $expiration ) ) {
			$issues[] = 'Membership expiration rules not configured';
		}
		
		// Check 5: Role assignment
		$role_assign = get_option( 'mepr_automatic_role_assignment', 0 );
		if ( ! $role_assign ) {
			$issues[] = 'Automatic role assignment not enabled';
		}
		
		// Check 6: Audit logging
		$audit_log = get_option( 'mepr_access_audit_logging', 0 );
		if ( ! $audit_log ) {
			$issues[] = 'Access audit logging not enabled';
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
					'Found %d membership rule issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/memberpress-membership-rules',
			);
		}
		
		return null;
	}
}
