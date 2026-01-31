<?php
/**
 * User Role Permission Inconsistency Diagnostic
 *
 * Checks for permission mismatches in user roles.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Role Permission Inconsistency Diagnostic Class
 *
 * Detects user role and permission issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_User_Role_Permission_Inconsistency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-role-permission-inconsistency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Role Permission Inconsistency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for permission mismatches';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_roles;

		// Check if custom roles exist with missing standard capabilities
		if ( ! empty( $wp_roles ) && ! empty( $wp_roles->roles ) ) {
			foreach ( $wp_roles->roles as $role_name => $role_data ) {
				$capabilities = isset( $role_data['capabilities'] ) ? $role_data['capabilities'] : array();

				// Check for roles with no capabilities (corrupted)
				if ( empty( $capabilities ) && ! in_array( $role_name, array( 'subscriber', 'contributor' ), true ) ) {
					return array(
						'id'            => self::$slug,
						'title'         => self::$title,
						'description'   => sprintf(
							__( 'User role "%s" has no capabilities assigned. This may indicate a corrupted role or permissions issue.', 'wpshadow' ),
							esc_html( $role_name )
						),
						'severity'      => 'high',
						'threat_level'  => 60,
						'auto_fixable'  => false,
						'kb_link'       => 'https://wpshadow.com/kb/user-role-permission-inconsistency',
					);
				}
			}
		}

		return null;
	}
}
