<?php
/**
 * Unassigned User Accounts Diagnostic
 *
 * Identifies user accounts without proper role assignments or with
 * orphaned/legacy roles that could pose security risks.
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
