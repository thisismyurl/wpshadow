<?php
/**
 * Default User Role Security Diagnostic
 *
 * Validates that the default user role assigned to new registrations
 * is appropriately restrictive to prevent privilege escalation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default User Role Security Diagnostic Class
 *
 * Checks default user role configuration.
 *
 * @since 1.6032.1340
 */
class Diagnostic_Default_User_Role_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'default-user-role-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Default User Role Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates default user role assignments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get default role for new users.
		$default_role = get_option( 'default_role', 'subscriber' );

		// WooCommerce might set to 'customer'.
		if ( class_exists( 'WooCommerce' ) ) {
			if ( ! in_array( $default_role, array( 'subscriber', 'customer' ), true ) ) {
				$issues[] = sprintf(
					/* translators: %s: default role */
					__( 'Default role is "%s" (should be subscriber or customer with WooCommerce)', 'wpshadow' ),
					$default_role
				);
			}
		} else {
			// Not WooCommerce - check standard WordPress roles.
			if ( 'subscriber' !== $default_role ) {
				$issues[] = sprintf(
					/* translators: %s: default role */
					__( 'Default role is "%s" (should be subscriber for security)', 'wpshadow' ),
					$default_role
				);
			}
		}

		// Check if user registration is enabled.
		$users_can_register = get_option( 'users_can_register', 0 );

		if ( $users_can_register ) {
			// Registration is enabled - verify safe defaults.

			// Get the actual role object.
			$roles = wp_roles()->roles;
			if ( ! isset( $roles[ $default_role ] ) ) {
				$issues[] = sprintf(
					/* translators: %s: role name */
					__( 'Default role "%s" does not exist', 'wpshadow' ),
					$default_role
				);
			} else {
				// Check the capabilities of the default role.
				$role_caps = $roles[ $default_role ]['capabilities'];

				// Dangerous capabilities that should NOT be in default role.
				$dangerous_caps = array(
					'edit_posts',
					'edit_others_posts',
					'publish_posts',
					'delete_posts',
					'manage_options',
					'edit_users',
					'promote_users',
					'edit_pages',
					'publish_pages',
					'delete_pages',
					'delete_others_pages',
					'install_plugins',
					'activate_plugins',
				);

				$has_dangerous = array();
				foreach ( $dangerous_caps as $cap ) {
					if ( ! empty( $role_caps[ $cap ] ) ) {
						$has_dangerous[] = $cap;
					}
				}

				if ( ! empty( $has_dangerous ) ) {
					$issues[] = sprintf(
						/* translators: 1: default role, 2: dangerous capabilities */
						__( 'Default role "%1$s" has dangerous capabilities: %2$s', 'wpshadow' ),
						$default_role,
						implode( ', ', $has_dangerous )
					);
				}
			}
		}

		// Check for users assigned to unexpected default role.
		$users_with_default = get_users(
			array(
				'role'   => $default_role,
				'fields' => 'ID',
			)
		);

		// Check multisite default role.
		if ( is_multisite() ) {
			$site_default = get_option( 'default_role', 'subscriber' );

			// In multisite, check if it's appropriately restrictive.
			if ( ! in_array( $site_default, array( 'subscriber', 'customer' ), true ) ) {
				$issues[] = sprintf(
					/* translators: %s: default role */
					__( 'Multisite default role is "%s" (should be subscriber)', 'wpshadow' ),
					$site_default
				);
			}
		}

		// Check for custom roles being set as default.
		if ( ! in_array( $default_role, array( 'subscriber', 'contributor', 'author', 'editor', 'administrator', 'customer', 'shop_manager' ), true ) ) {
			$issues[] = sprintf(
				/* translators: %s: role name */
				__( 'Default role is custom role "%s" (may have unexpected permissions)', 'wpshadow' ),
				$default_role
			);
		}

		// Check if new user email verification is required.
		$new_user_approve = false;
		if ( is_plugin_active( 'new-user-approve/new-user-approve.php' ) ) {
			$new_user_approve = true;
		}

		if ( $users_can_register && ! $new_user_approve ) {
			// Registration enabled without new user approval - make sure default role is safe.
			if ( 'subscriber' !== $default_role && 'customer' !== $default_role ) {
				$issues[] = __( 'Registration is open without approval, and default role is not appropriately restrictive', 'wpshadow' );
			}
		}

		// Check for elevated permissions on default role in network settings.
		if ( is_multisite() && is_network_admin() ) {
			$site_default = get_option( 'default_role', 'subscriber' );

			// Network should enforce minimum permissions.
			// Check if individual sites are overriding with less secure defaults.
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of default role issues */
					__( 'Found %d default user role security issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'details'      => array(
					'issues'              => $issues,
					'default_role'        => $default_role,
					'registration_enabled' => $users_can_register,
					'recommendation'      => __( 'Set default role to subscriber (or customer for WooCommerce). Remove dangerous capabilities from default role.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
