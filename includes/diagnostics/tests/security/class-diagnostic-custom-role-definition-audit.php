<?php
/**
 * Custom Role Definition Audit Diagnostic
 *
 * Audits custom WordPress roles to ensure they are properly defined
 * with appropriate capabilities and security considerations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1330
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Role Definition Audit Diagnostic Class
 *
 * Audits custom role definitions for security issues.
 *
 * @since 1.6032.1330
 */
class Diagnostic_Custom_Role_Definition_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-role-definition-audit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Role Definition Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Audits custom role definitions for security';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1330
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$roles  = wp_roles()->roles;

		// Default WordPress roles.
		$default_roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );

		// WooCommerce roles (if installed).
		if ( class_exists( 'WooCommerce' ) ) {
			$default_roles[] = 'customer';
			$default_roles[] = 'shop_manager';
		}

		// Find custom roles.
		$custom_roles = array();
		foreach ( $roles as $role_slug => $role_data ) {
			if ( ! in_array( $role_slug, $default_roles, true ) ) {
				$custom_roles[ $role_slug ] = $role_data;
			}
		}

		if ( empty( $custom_roles ) ) {
			return null; // No custom roles to audit.
		}

		// Dangerous capabilities that custom roles shouldn't typically have.
		$dangerous_caps = array(
			'manage_options'    => 'site settings',
			'edit_users'        => 'user editing',
			'delete_users'      => 'user deletion',
			'create_users'      => 'user creation',
			'promote_users'     => 'role assignment',
			'install_plugins'   => 'plugin installation',
			'activate_plugins'  => 'plugin activation',
			'update_plugins'    => 'plugin updates',
			'delete_plugins'    => 'plugin deletion',
			'install_themes'    => 'theme installation',
			'update_themes'     => 'theme updates',
			'delete_themes'     => 'theme deletion',
			'edit_themes'       => 'theme editing',
			'edit_plugins'      => 'plugin editing',
			'update_core'       => 'WordPress updates',
			'unfiltered_html'   => 'unfiltered HTML',
			'unfiltered_upload' => 'unfiltered file upload',
		);

		$risky_roles = array();
		foreach ( $custom_roles as $role_slug => $role_data ) {
			$role_issues = array();
			$role_caps   = $role_data['capabilities'];

			// Check for dangerous capabilities.
			foreach ( $dangerous_caps as $cap => $description ) {
				if ( ! empty( $role_caps[ $cap ] ) ) {
					$role_issues[] = sprintf(
						/* translators: %s: capability description */
						__( 'Has %s capability', 'wpshadow' ),
						$description
					);
				}
			}

			// Check if role has no capabilities.
			if ( empty( $role_caps ) || ( count( $role_caps ) === 1 && isset( $role_caps['read'] ) ) ) {
				$role_issues[] = __( 'Has minimal/no capabilities (orphaned role?)', 'wpshadow' );
			}

			// Check for poorly named roles.
			if ( preg_match( '/^(test|temp|old|backup)/i', $role_slug ) ) {
				$role_issues[] = __( 'Temporary/test role name (cleanup needed?)', 'wpshadow' );
			}

			if ( ! empty( $role_issues ) ) {
				$risky_roles[ $role_slug ] = array(
					'role_name'    => $role_data['name'],
					'issues'       => $role_issues,
					'capabilities' => array_keys( array_filter( $role_caps ) ),
				);
			}

			// Check users assigned to this role.
			$users_in_role = get_users(
				array(
					'role'   => $role_slug,
					'fields' => 'ID',
				)
			);

			if ( empty( $users_in_role ) && ! empty( $role_caps ) ) {
				$issues[] = sprintf(
					/* translators: %s: role name */
					__( 'Custom role "%s" has no users (orphaned role)', 'wpshadow' ),
					$role_data['name']
				);
			}
		}

		if ( ! empty( $risky_roles ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of risky custom roles */
				__( '%d custom roles have security concerns', 'wpshadow' ),
				count( $risky_roles )
			);
		}

		// Check total number of custom roles.
		if ( count( $custom_roles ) > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of custom roles */
				__( '%d custom roles defined (consider consolidating)', 'wpshadow' ),
				count( $custom_roles )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: custom role count, 2: issue count */
					__( 'Found %1$d custom roles with %2$d security concerns.', 'wpshadow' ),
					count( $custom_roles ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'details'      => array(
					'issues'       => $issues,
					'risky_roles'  => $risky_roles,
					'total_custom' => count( $custom_roles ),
					'recommendation' => __( 'Review custom roles and remove dangerous capabilities. Delete orphaned roles with no users.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
