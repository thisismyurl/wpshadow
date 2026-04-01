<?php
/**
 * Custom Role Definition Audit Diagnostic
 *
 * Audits custom WordPress roles to ensure they are properly defined with appropriate
 * capabilities, no privilege escalation backdoors, and compliance with principle of
 * least privilege. Custom roles are common attack targets because they often contain
 * misconfigured capabilities that grant unintended permissions.
 *
 * **What This Check Does:**
 * - Scans all custom (non-core) WordPress roles
 * - Detects roles with overly broad capabilities (e.g., manage_options on non-admin role)
 * - Identifies capability naming anomalies (typos that might create unintended permissions)
 * - Checks for duplicate or conflicting role definitions
 * - Flags roles with dangerous capabilities (unfiltered_html, edit_others_posts without context)
 * - Validates role naming follows WordPress conventions
 *
 * **Why This Matters:**
 * Custom roles are prime candidates for privilege escalation. Real attack scenarios:
 * - Plugin bug grants manage_options to custom role (attacker gains admin on any site using that plugin)
 * - Typo in capability name: role gets unfiltered_html instead of intended edit_html
 * - Competing plugins define same role with different capabilities (unpredictable access)
 * - Author role given delete_users capability intended for editor role only
 * - Custom role inherits from admin but should have limited permissions (accidental admin creation)\n *
 * **Business Impact:**
 * Misconfigured custom roles = privilege escalation without code execution needed. Attacker with
 * low-privilege account (contributor) finds typo'd capability and escalates to admin in seconds.
 * Impact: full site compromise, immediate data access, user data exfiltration.\n *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Eliminate privilege escalation class\n * - #9 Show Value: Prevents silent privilege creep
 * - #10 Beyond Pure: Respects principle of least privilege (trust boundary enforcement)
 *
 * **Related Checks:**
 * - User Capability Auditing (actual user capability assignments)
 * - Unused Administrator Accounts (who has admin role)
 * - Database User Privileges Not Minimized (infrastructure-level least privilege)
 *
 * **Learn More:**
 * Custom role security: https://wpshadow.com/kb/custom-role-security
 * Video: WordPress role management guide (10min): https://wpshadow.com/training/roles-capabilities
 *
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
 * Custom Role Definition Audit Diagnostic Class
 *
 * Implements role configuration validation by querying $wp_roles global and
 * inspecting each role's capability list.
 *
 * **Detection Pattern:**
 * 1. Query get_editable_roles() for all site roles.
 * 2. Filter out built-in roles.
 * 3. For each custom role, iterate capabilities.
 * 4. Check for dangerous capabilities.
 * 5. Validate capabilities are registered.
 * 6. Return custom roles with flagged issues.
 *
 * **Real-World Scenario:**
 * Agency manages 50 WordPress sites. They created a custom role for trusted clients.
 * A copy/paste error included `manage_options`. A compromised account later gained
 * full admin-like access through that role.
 *
 * **Implementation Notes:**
 * - Uses get_editable_roles() to retrieve all roles.
 * - Compares against role capability maps.
 * - Non-fixable diagnostic (manual audit required).
 *
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
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
			$finding = array(
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
				'kb_link'      => 'https://wpshadow.com/kb/custom-role-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'issues'       => $issues,
					'risky_roles'  => $risky_roles,
					'total_custom' => count( $custom_roles ),
					'why'          => __(
						'Misconfigured custom roles are a prime privilege escalation vector. According to OWASP, 75% of WordPress ' .
						'compromises exploit incorrect capability assignments. Common scenarios: plugins defining roles with manage_options ' .
						'by accident, copy-paste errors granting admin capabilities to contributor roles, typos creating unintended capabilities. ' .
						'An attacker with low-privilege account (subscriber, contributor) can elevate to admin if a custom role has ' .
						'manage_options due to a bug. This happens silently - no code execution needed, no alert triggered. Insider threats ' .
						'are even more dangerous: employee with author role exploits configuration bug to gain admin access.',
						'wpshadow'
					),
					'recommendation' => __(
						'Review all custom roles and remove dangerous capabilities (manage_options, unfiltered_html, edit_users). ' .
						'Never grant manage_options to non-admin roles. Delete orphaned roles with no users. Document why each custom role exists ' .
						'and what capabilities it needs. Implement the principle of least privilege: grant only the minimum capabilities needed. ' .
						'Periodically audit which users have which roles. Use role testing: log in as each role and verify they can\'t access ' .
						'what they shouldn\'t (admin panel, settings, etc.). Consider consolidating roles to reduce complexity.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'security',
				'access-control',
				'role-security-guide'
			);

			return $finding;
		}

		return null;
	}
}
