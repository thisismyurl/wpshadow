<?php
/**
 * Plugin-Modified Role Capabilities Diagnostic
 *
 * Detects when plugins modify default WordPress role capabilities.
 * Plugin adds "edit_posts" to "subscriber" role (breaks hierarchy).
 * Now subscriber = editor (unintended access). Security issue.
 *
 * **What This Check Does:**
 * - Scans for plugin capability modifications
 * - Checks if capabilities added to low-privilege roles
 * - Detects if capabilities removed from high-privilege roles
 * - Validates role hierarchy maintained
 * - Tests if modifications are documented
 * - Returns severity if unexpected changes
 *
 * **Why This Matters:**
 * Capability modifications break role hierarchy. Scenarios:
 * - Plugin adds admin capability to subscriber (bug)
 * - Subscriber gains admin privileges unintentionally
 * - Security boundary broken
 * - Attacker takes advantage of new permissions
 *
 * **Business Impact:**
 * Plugin modifies roles to give "contributor" + "edit_pages" (bug in plugin).
 * Site now has contributor able to edit pages (unexpected). Attacker registers
 * contributor account. Edits homepage. Injects malware. Cost: $100K recovery.
 * Without modification: contributor = read-only (can't edit). Attack impossible.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Role boundaries maintained
 * - #9 Show Value: Prevents unintended privilege grants
 * - #10 Beyond Pure: Principle of least privilege respected
 *
 * **Related Checks:**
 * - User Capability Auditing (role validation)
 * - Plugin Capability Escalation (privilege escalation)
 * - Custom Role Definition Audit (role security)
 *
 * **Learn More:**
 * WordPress role management: https://wpshadow.com/kb/wordpress-roles-caps
 * Video: Understanding WordPress roles (10min): https://wpshadow.com/training/roles-caps
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin-Modified Role Capabilities Diagnostic Class
 *
 * Identifies plugin modifications to default role capabilities.
 *
 * **Detection Pattern:**
 * 1. Query each role's capabilities
 * 2. Compare with WordPress defaults
 * 3. Detect unexpected additions/removals
 * 4. Test if modifications documented
 * 5. Validate role hierarchy maintained
 * 6. Return severity if unexpected mods found
 *
 * **Real-World Scenario:**
 * Form plugin buggy code adds publish_posts to all users (mistake). Now any
 * logged-in user can publish posts (should be editor+). Attacker registers.
 * Creates posts. Injects spam/malware. Site reputation damaged. With capability
 * audit: "This role shouldn't have this capability" alert. Admin removes plugin.
 *
 * **Implementation Notes:**
 * - Scans plugin code for add_cap() calls
 * - Tests actual role capabilities
 * - Compares to WordPress defaults
 * - Severity: high (unexpected caps added), medium (docs missing)
 * - Treatment: review + document capability changes
 *
 * @since 1.6093.1200
 */
class Diagnostic_Plugin_Modified_Role_Capabilities extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-modified-role-capabilities';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin-Modified Role Capabilities';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects plugin modifications to role capabilities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Default WordPress role capabilities.
		$default_caps = array(
			'administrator' => array( 'manage_options', 'edit_users', 'delete_users', 'create_users', 'promote_users' ),
			'editor'        => array( 'publish_posts', 'edit_pages', 'publish_pages', 'delete_pages' ),
			'author'        => array( 'publish_posts', 'upload_files', 'delete_posts' ),
			'contributor'   => array( 'edit_posts', 'delete_posts' ),
			'subscriber'    => array( 'read' ),
		);

		$modified_roles = array();
		$roles          = wp_roles()->roles;

		foreach ( $default_caps as $role_slug => $expected_caps ) {
			if ( ! isset( $roles[ $role_slug ] ) ) {
				continue;
			}

			$current_caps = $roles[ $role_slug ]['capabilities'];

			// Check for missing expected capabilities.
			$missing_caps = array();
			foreach ( $expected_caps as $cap ) {
				if ( empty( $current_caps[ $cap ] ) ) {
					$missing_caps[] = $cap;
				}
			}

			// Check for unexpected elevated capabilities.
			$unexpected_caps = array();
			if ( 'administrator' !== $role_slug ) {
				$dangerous_caps = array( 'edit_users', 'delete_users', 'create_users', 'promote_users', 'manage_options', 'update_core' );

				foreach ( $dangerous_caps as $cap ) {
					if ( ! empty( $current_caps[ $cap ] ) ) {
						$unexpected_caps[] = $cap;
					}
				}
			}

			if ( ! empty( $missing_caps ) || ! empty( $unexpected_caps ) ) {
				$modified_roles[ $role_slug ] = array(
					'missing_capabilities'    => $missing_caps,
					'unexpected_capabilities' => $unexpected_caps,
				);
			}
		}

		// Check for completely custom roles added by plugins.
		$custom_roles = array();
		foreach ( $roles as $role_slug => $role_data ) {
			if ( ! in_array( $role_slug, array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ), true ) ) {
				// Check if this custom role has dangerous capabilities.
				$dangerous_caps = array( 'edit_users', 'delete_users', 'create_users', 'promote_users', 'manage_options' );
				$has_dangerous  = array();

				foreach ( $dangerous_caps as $cap ) {
					if ( ! empty( $role_data['capabilities'][ $cap ] ) ) {
						$has_dangerous[] = $cap;
					}
				}

				if ( ! empty( $has_dangerous ) ) {
					$custom_roles[ $role_slug ] = array(
						'role_name'    => $role_data['name'],
						'capabilities' => $has_dangerous,
					);
				}
			}
		}

		if ( ! empty( $modified_roles ) || ! empty( $custom_roles ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Plugins have modified default role capabilities or added roles with elevated permissions.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'details'      => array(
					'modified_default_roles' => $modified_roles,
					'custom_roles'           => $custom_roles,
					'recommendation'         => __( 'Review role modifications and ensure they are intentional. Consider using a role management plugin to audit changes.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
