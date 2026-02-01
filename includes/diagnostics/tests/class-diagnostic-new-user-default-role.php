<?php
/**
 * New User Default Role Diagnostic
 *
 * Verifies that new users are assigned an appropriate default role that
 * matches the site's permission structure and security requirements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * New User Default Role Diagnostic Class
 *
 * Ensures default user role is appropriately configured.
 *
 * @since 1.26032.1800
 */
class Diagnostic_New_User_Default_Role extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'new-user-default-role';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'New User Default Role';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies new users get appropriate default role';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Default role is set to a valid role
	 * - Default role is not overly permissive
	 * - Role matches site's permission structure
	 *
	 * @since  1.26032.1800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get default role.
		$default_role = get_option( 'default_role', 'subscriber' );

		// Get all available roles.
		global $wp_roles;
		if ( isset( $wp_roles ) ) {
			$available_roles = $wp_roles->roles;

			// Check if default role exists.
			if ( ! isset( $available_roles[ $default_role ] ) ) {
				$issues[] = sprintf(
					/* translators: %s: role name */
					__( 'Default role (%s) does not exist; users may not have valid permissions', 'wpshadow' ),
					$default_role
				);
			}

			// Check if default role is overly permissive.
			$dangerous_roles = array( 'administrator', 'editor' );
			if ( in_array( $default_role, $dangerous_roles, true ) ) {
				$issues[] = sprintf(
					/* translators: %s: role name */
					__( 'New users are assigned the %s role by default; this is a significant security risk', 'wpshadow' ),
					ucfirst( $default_role )
				);
			}

			// Check if role is too restrictive (e.g., no publish capability for a blog).
			if ( 'subscriber' === $default_role ) {
				$role_object = $available_roles[ $default_role ];
				if ( ! isset( $role_object['capabilities']['read'] ) || ! $role_object['capabilities']['read'] ) {
					$issues[] = __( 'Default subscriber role may not have read permissions', 'wpshadow' );
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/new-user-default-role',
			);
		}

		return null;
	}
}
