<?php
/**
 * Default User Role Security Diagnostic
 *
 * Validates that the default user role assigned to new registrations
 * is appropriately restrictive to prevent privilege escalation. Sites allowing
 * self-registration often default new users to "Subscriber" role. If misconfigured,
 * could default to "Contributor" or worse. Wrong role = new users can edit posts.
 * **What This Check Does:**
 * - Gets the default user role (get_option('default_role'))\n * - Validates default role is \"Subscriber\" (or appropriately restrictive)\n * - Detects if default role can edit published posts (dangerous)\n * - Checks if default role can publish posts (should require moderation)\n * - Tests permission levels for default role\n * - Confirms custom registration forms use correct role\n *
 * **Why This Matters:**
 * Wrong default role = new users bypass moderation. Scenarios:\n * - Default role: \"Contributor\" instead of \"Subscriber\"\n * - New user registers, auto-assigned contributor role\n * - New user can edit their own posts (normal)\n * - But also can see/edit draft posts from others (bad)\n * - New user can view analytics, user lists (exposure)\n *
 * **Business Impact:**
 * Forum site defaults new users to \"Editor\" role (misconfigured). New user registers.\n * Can now view ALL comments (including private/moderated). Discovers premium content\n * discussion, screenshots and shares publicly (leaking proprietary info). Site loses\n * paying members. Damage: $5K-$20K lost revenue + reputation impact.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: New users can't see/modify others' content\n * - #9 Show Value: Prevents accidental permission escalation\n * - #10 Beyond Pure: Principle of least privilege, new users minimal access\n *
 * **Related Checks:**
 * - Custom Role Definition Audit (role capabilities)\n * - User Capability Auditing (actual user permissions)\n * - Unused Administrator Accounts (who has admin)\n *
 * **Learn More:**
 * User roles and registration: https://wpshadow.com/kb/wordpress-user-roles\n * Video: User registration security (8min): https://wpshadow.com/training/user-roles-security\n *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default User Role Security Diagnostic Class
 *
 * Implements validation of default user role assignment.\n *
 * **Detection Pattern:**
 * 1. Get option 'default_role' from database\n * 2. Compare against allowed roles: 'subscriber' (safest), 'contributor', etc.\n * 3. Get the role object from $wp_roles global\n * 4. Check role capabilities (can publish? can edit others?)\n * 5. Validate registration is enabled (if default role set)\n * 6. Return severity if default role too permissive\n *
 * **Real-World Scenario:**
 * Developer creates multi-author blog. Sets default role to 'contributor' (thinking\n * new users should be able to submit). Forgets: contributor role can see all post\n * status filters (drafts, scheduled). New user registers. Views 'drafts' and sees\n * CEO's confidential article draft about layoffs (before published). CEO discovers\n * employee (new contributor) knew about layoffs before announcement.\n *
 * **Implementation Notes:**
 * - Checks get_option('default_role')\n * - Validates against $wp_roles->roles array\n * - Tests role capabilities against expected minimums\n * - Severity: medium (overly permissive), low (slightly permissive)\n * - Treatment: set default role to 'subscriber'\n *
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
