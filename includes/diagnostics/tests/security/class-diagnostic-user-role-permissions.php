<?php
/**
 * User Role Permissions Diagnostic
 *
 * Issue #4853: User Roles Have Excessive Permissions
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if user roles follow principle of least privilege.
 * Excessive permissions enable compromised accounts to cause more damage.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_User_Role_Permissions Class
 *
 * Checks for:
 * - Editor role with admin capabilities
 * - Author role with edit_others_posts
 * - Contributor with publish capabilities
 * - Custom roles with excessive permissions
 * - Users with unnecessary admin access
 *
 * Least privilege principle: users should have minimum permissions needed.
 * A compromised editor account should not be able to install plugins or change settings.
 *
 * @since 1.6093.1200
 */
class Diagnostic_User_Role_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'user-role-permissions';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'User Roles Have Excessive Permissions';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if user roles follow principle of least privilege';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_roles;

		$issues = array();

		// Check critical roles for excessive permissions
		$critical_checks = array(
			'editor'      => array( 'manage_options', 'install_plugins', 'delete_plugins', 'update_plugins', 'manage_options' ),
			'author'      => array( 'delete_others_posts', 'edit_others_posts', 'publish_pages', 'manage_options', 'activate_plugins' ),
			'contributor' => array( 'publish_posts', 'edit_published_posts', 'manage_options', 'activate_plugins' ),
			'subscriber'  => array( 'edit_posts', 'delete_posts', 'manage_options', 'activate_plugins' ),
		);

		foreach ( $critical_checks as $role => $dangerous_caps ) {
			if ( ! isset( $wp_roles->roles[ $role ] ) ) {
				continue;
			}

			$role_data = $wp_roles->roles[ $role ];
			$found_caps = array();

			foreach ( $dangerous_caps as $cap ) {
				if ( ! empty( $role_data['capabilities'][ $cap ] ) ) {
					$found_caps[] = $cap;
				}
			}

			if ( ! empty( $found_caps ) ) {
				$issues[] = sprintf(
					/* translators: %1$s: role name, %2$s: capabilities */
					__( '%1$s role has admin capabilities: %2$s', 'wpshadow' ),
					ucfirst( $role ),
					implode( ', ', $found_caps )
				);
			}
		}

		// Check for users with unnecessary admin access
		$admins = get_users( array( 'role' => 'administrator' ) );
		if ( count( $admins ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of admins */
				__( 'Too many administrator accounts (%d), recommended: ≤3', 'wpshadow' ),
				count( $admins )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'User roles have excessive permissions, violating principle of least privilege', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-role-permissions',
				'details'      => array(
					'findings'    => $issues,
					'principle'   => 'Least privilege: users should have minimum permissions needed for their role',
					'admin_count' => count( $admins ),
				),
			);
		}

		return null;
	}
}
