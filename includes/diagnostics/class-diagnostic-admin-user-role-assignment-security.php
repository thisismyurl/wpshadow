<?php
/**
 * Admin User Role Assignment Security
 *
 * Checks if user role assignments are properly audited and validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0636
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin User Role Assignment Security
 *
 * @since 1.26033.0636
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
