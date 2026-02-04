<?php
/**
 * Admin User Role Assignment Security Diagnostic
 *
 * Monitors how administrator roles are assigned to ensure privilege changes
 * are intentional, auditable, and limited to trusted users. Role assignment
 * is one of the highest-impact security actions in WordPress: a single accidental
 * promotion can grant full control of the site.
 *
 * **What This Check Does:**
 * - Counts administrator accounts and flags unusually high totals
 * - Detects inactive administrators who haven't logged in recently
 * - Highlights role assignment patterns that increase risk
 * - Encourages least-privilege access for day-to-day work
 * - Provides guidance on role auditing and cleanup
 *
 * **Why This Matters:**
 * Administrator accounts can install plugins, edit files, and delete users.
 * If too many admins exist, it's easier for compromised credentials to cause
 * damage and harder to identify who made critical changes. Inactive admins are
 * especially risky because they often use weak, stale passwords.
 *
 * **Real-World Security Scenario:**
 * - A former contractor still has an admin account.
 * - Their email is breached months later.
 * - Attacker uses reset password flow and logs in.
 * - Attacker installs a backdoor plugin and disappears.
 *
 * Result: Full site compromise from a forgotten admin account.
 *
 * **Best-Practice Guidance:**
 * - Keep admin count low (ideally 1–3 active admins)
 * - Use Editor role for content publishing (not Admin)
 * - Review admin list quarterly
 * - Remove inactive users or downgrade roles
 * - Require MFA for all admin accounts
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Reduces privileged account exposure
 * - #10 Beyond Pure: Protects against credential misuse and account takeover
 * - Helpful Neighbor: Encourages safer, simpler role management
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/admin-role-management for role audit guidance
 * or https://wpshadow.com/training/wordpress-access-control
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0636
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin User Role Assignment Security
 *
 * Uses WordPress user queries to evaluate administrator count and activity.
 * The diagnostic focuses on risk signals (too many admins, inactive admins)
 * rather than enforcing a single “correct” number for every site.
 *
 * **Implementation Pattern:**
 * 1. Query users with the Administrator role
 * 2. Count total admins and flag if above threshold
 * 3. Inspect last login metadata for inactivity
 * 4. Return findings with actionable guidance
 *
 * **Detection Logic:**
 * - >5 admins: Increased attack surface
 * - Last login >90 days: Potentially stale account
 * - Missing last_login metadata: Requires audit
 *
 * **Related Diagnostics:**
 * - Capability Map Consistency: Ensures roles map correctly
 * - Admin Menu Visibility: Validates role-appropriate UI
 * - REST API Authentication: Checks privileged endpoint access
 *
 * @since 1.6033.0636
 */
class Diagnostic_Admin_User_Role_Assignment_Security extends Diagnostic_Base {

	protected static $slug = 'admin-user-role-assignment-security';
	protected static $title = 'Admin User Role Assignment Security';
	protected static $description = 'Verifies user role assignments are properly controlled';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Get all users with admin role
		$admin_users = get_users( array(
			'role' => 'administrator',
		) );

		$admin_count = count( $admin_users );
		if ( $admin_count > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of admins */
				__( 'High number of administrators (%d) - verify all are necessary', 'wpshadow' ),
				$admin_count
			);
		}

		// Check for inactive admins
		$inactive_count = 0;
		foreach ( $admin_users as $user ) {
			$last_login = get_user_meta( $user->ID, 'last_login', true );
			if ( empty( $last_login ) || ( time() - strtotime( $last_login ) ) > ( 90 * DAY_IN_SECONDS ) ) {
				$inactive_count++;
			}
		}

		if ( $inactive_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of inactive admins */
				__( '%d administrator(s) have not logged in for 90+ days', 'wpshadow' ),
				$inactive_count
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-user-role-assignment-security',
			);
		}

		return null;
	}
}
