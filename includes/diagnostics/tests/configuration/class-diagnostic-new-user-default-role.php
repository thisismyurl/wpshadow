<?php
/**
 * New User Default Role
 *
 * Checks if default user role is appropriately configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_New_User_Default_Role Class
 *
 * Validates default role assignment for new users.
 *
 * @since 1.2601.2148
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
	protected static $description = 'Validates default role assignment for newly registered users';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'configuration';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests default user role configuration.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$default_role = get_option( 'default_role', 'subscriber' );

		// Check 1: Default role is not empty
		if ( empty( $default_role ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No default role configured for new users', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/default-user-role',
				'recommendations' => array(
					__( 'Set appropriate default role for new users', 'wpshadow' ),
					__( 'Use "Subscriber" for most sites', 'wpshadow' ),
					__( 'Avoid making all users "Editor" or higher', 'wpshadow' ),
				),
			);
		}

		// Check 2: Default role is not too privileged
		$dangerous_roles = array( 'administrator', 'editor' );
		if ( in_array( $default_role, $dangerous_roles, true ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: role name */
					__( 'Default role is set to %s - too privileged for new users', 'wpshadow' ),
					$default_role
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/default-role-security',
				'recommendations' => array(
					__( 'Change default role to "Subscriber" or "Author"', 'wpshadow' ),
					__( 'Administrators and Editors should be manually assigned', 'wpshadow' ),
					__( 'This prevents accidental elevated permissions', 'wpshadow' ),
				),
			);
		}

		// Check 3: For multisite, check if role exists in subsite
		if ( is_multisite() ) {
			if ( ! self::role_exists_in_blog( $default_role ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %s: role name */
						__( 'Default role %s does not exist in current blog', 'wpshadow' ),
						$default_role
					),
					'severity'     => 'high',
					'threat_level' => 60,
					'auto_fixable' => true,
					'kb_link'      => 'https://wpshadow.com/kb/multisite-default-role',
					'recommendations' => array(
						__( 'Verify role exists in this blog', 'wpshadow' ),
						__( 'Use standard role or create custom role in all blogs', 'wpshadow' ),
					),
				);
			}
		}

		// Check 4: For WordPress.com integrated sites
		if ( ! is_multisite() && self::users_can_register() ) {
			if ( $default_role === 'administrator' ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'User registration enabled with administrator role - major security risk', 'wpshadow' ),
					'severity'     => 'critical',
					'threat_level' => 95,
					'auto_fixable' => true,
					'kb_link'      => 'https://wpshadow.com/kb/user-registration-admin-role',
					'recommendations' => array(
						__( 'Change default role immediately', 'wpshadow' ),
						__( 'New users should not be admins', 'wpshadow' ),
						__( 'Requires admin approval or manual role assignment', 'wpshadow' ),
					),
				);
			}
		}

		return null;
	}

	/**
	 * Check if role exists in current blog.
	 *
	 * @since  1.2601.2148
	 * @param  string $role Role name.
	 * @return bool True if role exists.
	 */
	private static function role_exists_in_blog( $role ) {
		$role_obj = get_role( $role );
		return $role_obj !== null;
	}

	/**
	 * Check if user registration is enabled.
	 *
	 * @since  1.2601.2148
	 * @return bool True if registration enabled.
	 */
	private static function users_can_register() {
		return (bool) get_option( 'users_can_register' );
	}
}
