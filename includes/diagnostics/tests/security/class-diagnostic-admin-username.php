<?php
/**
 * Diagnostic: Default Admin Username Detection
 *
 * Checks for the presence of "admin" username, a primary brute force target.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Admin_Username Class
 *
 * Detects if any user account has the username "admin", which is a primary
 * target for brute force attacks. Attackers know that approximately 50% of
 * WordPress sites use the default "admin" username, making it the first
 * credential they attempt to compromise.
 *
 * By using a unique, non-predictable username for administrator accounts,
 * security can be significantly improved since attackers must guess both
 * the username AND password.
 *
 * Returns different threat levels based on the role of the "admin" user:
 * - Critical (75): "admin" user is an administrator
 * - High (50): "admin" user exists with lower role
 * - Good: No "admin" username found
 *
 * @since 1.2601.2200
 */
class Diagnostic_Admin_Username extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'admin-username';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'Default Admin Username Detected';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Detects if "admin" username is present, a primary brute force target';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Security';

	/**
	 * Run the diagnostic check.
	 *
	 * Queries the users table for any user with the login "admin" and checks
	 * their role. Critical if they're an administrator, high if they exist
	 * with any other role.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if "admin" user found, null otherwise.
	 */
	public static function check() {
		$admin_user = get_user_by( 'login', 'admin' );

		if ( ! $admin_user ) {
			// No "admin" user found - we're good
			return null;
		}

		// Check if this user is an administrator
		$user_meta = get_userdata( $admin_user->ID );
		$is_admin  = isset( $user_meta->wp_capabilities['administrator'] ) && $user_meta->wp_capabilities['administrator'];

		if ( $is_admin ) {
			// Critical: "admin" user is an administrator
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __(
					'Your site has a user account with the username "admin", which is a primary target for brute force attacks. Attackers automatically try this username with common passwords. Create a new administrator account with a unique username and delete the "admin" account.',
					'wpshadow'
				),
				'severity'           => 'critical',
				'threat_level'       => 75,
				'site_health_status' => 'critical',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/security-admin-username',
				'family'             => self::$family,
				'details'            => array(
					'admin_user_exists'  => true,
					'admin_is_admin'     => true,
					'admin_user_id'      => $admin_user->ID,
					'admin_email'        => $admin_user->user_email,
					'recommendation'     => 'Create new admin account, transfer posts/roles, delete "admin" account',
				),
			);
		} else {
			// High: "admin" user exists but not an administrator
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __(
					'Your site has a user account with the username "admin" (though not an administrator). This is still a brute force risk. Consider deleting this account and having users use unique usernames instead.',
					'wpshadow'
				),
				'severity'           => 'high',
				'threat_level'       => 50,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/security-admin-username',
				'family'             => self::$family,
				'details'            => array(
					'admin_user_exists' => true,
					'admin_is_admin'    => false,
					'admin_user_id'     => $admin_user->ID,
					'admin_role'        => self::get_user_role( $admin_user ),
					'recommendation'    => 'Delete "admin" account and use unique usernames',
			),
			);
		}
	}

	/**
	 * Get the primary role for a WordPress user.
	 *
	 * @since  1.2601.2200
	 * @param  \WP_User $user The user object.
	 * @return string The user's primary role (e.g., 'administrator', 'editor', etc).
	 */
	private static function get_user_role( \WP_User $user ): string {
		if ( is_multisite() ) {
			$roles = $user->roles;
		} else {
			$roles = isset( $user->wp_capabilities ) ? array_keys( $user->wp_capabilities ) : array();
		}

		return ! empty( $roles ) ? $roles[0] : 'unknown';
	}
}
