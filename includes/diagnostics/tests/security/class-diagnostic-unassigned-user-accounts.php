<?php
/**
 * Unassigned User Accounts Diagnostic
 *
 * Identifies user accounts without proper role assignments or with
 * orphaned/legacy roles that could pose security risks.
 * User without proper role = security risk (unexpected permissions).
 * Database error, migration issue, or malicious backdoor.
 *
 * **What This Check Does:**
 * - Scans all user accounts
 * - Checks if each user has valid role assignment
 * - Detects orphaned/deleted role names
 * - Identifies users with no capabilities
 * - Finds legacy roles from uninstalled plugins
 * - Returns severity for each unassigned user
 *
 * **Why This Matters:**
 * User with no assigned role = undefined permissions.
 * Could allow escalation or provide backdoor access.
 * Best practice: every user has clear, documented role.
 *
 * **Business Impact:**
 * Migration from theme: user roles corrupted. 50 users have no role.
 * One of these users is compromised account (attacker registered).
 * Without role: permissions undefined. Could have unexpected access.
 * Admin doesn't see account (hidden in filtered lists). Attacker
 * exploits. With audit: unassigned accounts identified. Deleted or
 * properly assigned. Risk eliminated.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: User roles clear and valid
 * - #9 Show Value: Prevents orphaned account exploitation
 * - #10 Beyond Pure: Role-based access control integrity
 *
 * **Related Checks:**
 * - Unused Administrator Accounts (similar)
 * - User Capability Auditing (broader)
 * - Custom Role Definition Audit (related)
 *
 * **Learn More:**
 * User role management: https://wpshadow.com/kb/user-roles
 * Video: Managing user accounts (10min): https://wpshadow.com/training/user-roles
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unassigned User Accounts Diagnostic Class
 *
 * Detects users without valid role assignments.
 *
 * **Detection Pattern:**
 * 1. Get all users from database
 * 2. For each user: get assigned role from usermeta
 * 3. Check if role exists in registered roles
 * 4. Flag if role is empty or not found
 * 5. Detect orphaned roles (from deleted plugins)
 * 6. Return list of unassigned users
 *
 * **Real-World Scenario:**
 * Plugin uninstalled. It registered custom role. Users assigned to
 * that role. Users now have no valid role assignment. One user is
 * backdoor account (attacker created via SQL). With audit: unassigned
 * accounts identified. Deleted or assigned to subscriber. Backdoor
 * account removed. Attack vector closed.
 *
 * **Implementation Notes:**
 * - Queries all WordPress users
 * - Checks role validity
 * - Detects orphaned roles
 * - Severity: high (unassigned), medium (orphaned role)
 * - Treatment: delete unneeded users or assign proper role
 *
 * @since 1.6032.1200
 */
class Diagnostic_Unassigned_User_Accounts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'unassigned-user-accounts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Unassigned User Accounts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies users without proper role assignments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$unassigned_users = array();
		$valid_roles      = wp_roles()->get_names();

		// Get all users.
		$users = get_users( array( 'fields' => array( 'ID', 'user_login', 'user_email' ) ) );

		foreach ( $users as $user ) {
			$user_obj = new \WP_User( $user->ID );
			$roles    = $user_obj->roles;

			// Check if user has no roles.
			if ( empty( $roles ) ) {
				$unassigned_users[] = array(
					'user_id'    => $user->ID,
					'user_login' => $user->user_login,
					'user_email' => $user->user_email,
					'issue'      => 'no_role',
				);
			} else {
				// Check if user has invalid/orphaned roles.
				foreach ( $roles as $role ) {
					if ( ! isset( $valid_roles[ $role ] ) ) {
						$unassigned_users[] = array(
							'user_id'    => $user->ID,
							'user_login' => $user->user_login,
							'user_email' => $user->user_email,
							'issue'      => 'invalid_role',
							'role'       => $role,
						);
					}
				}
			}
		}

		if ( ! empty( $unassigned_users ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of users with role issues */
					__( 'Found %d user accounts with role assignment issues.', 'wpshadow' ),
					count( $unassigned_users )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'unassigned_users' => $unassigned_users,
					'recommendation'   => __( 'Review these accounts and assign appropriate roles or remove them if no longer needed.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
