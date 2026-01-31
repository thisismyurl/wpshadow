<?php
/**
 * User Role Capabilities Inheritance Issue Diagnostic
 *
 * Checks for corrupted user role capabilities.
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
 * User Role Capabilities Inheritance Issue Diagnostic Class
 *
 * Detects user role capability inheritance problems.
 *
 * @since 1.2601.2310
 */
class Diagnostic_User_Role_Capabilities_Inheritance_Issue extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-role-capabilities-inheritance-issue';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Role Capabilities Inheritance Issue';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for user role capability inheritance';

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

		if ( ! isset( $wp_roles ) || empty( $wp_roles->roles ) ) {
			return null;
		}

		// Check for roles with no capabilities
		foreach ( $wp_roles->roles as $role_name => $role_data ) {
			if ( ! in_array( $role_name, array( 'administrator', 'editor', 'author' ), true ) ) {
				if ( empty( $role_data['capabilities'] ) ) {
					return array(
						'id'            => self::$slug,
						'title'         => self::$title,
						'description'   => sprintf(
							__( 'User role "%s" has no capabilities assigned. This may indicate corrupted role data.', 'wpshadow' ),
							esc_html( $role_name )
						),
						'severity'      => 'medium',
						'threat_level'  => 45,
						'auto_fixable'  => false,
						'kb_link'       => 'https://wpshadow.com/kb/user-role-capabilities-inheritance-issue',
					);
				}
			}
		}

		return null;
	}
}
