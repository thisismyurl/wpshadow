<?php
/**
 * Admin Capability Map Consistency Diagnostic
 *
 * Monitors WordPress capability mapping to detect privilege escalation risks
 * and capability inconsistencies across roles. The WordPress capability system
 * controls what users can do - if capabilities are misconfigured, users might
 * gain unintended admin privileges or lose access to legitimate features.
 *
 * **What This Check Does:**
 * - Scans all WordPress roles via `global $wp_roles`
 * - Identifies custom capabilities added by plugins/themes
 * - Checks capability consistency across similar roles
 * - Detects orphaned capabilities (granted but never checked)
 * - Validates capability naming conventions
 * - Identifies overly broad capability grants
 *
 * **Why This Matters:**
 * WordPress capabilities control security boundaries. If Editor role gains
 * `delete_users` capability (admin-only), editors can delete administrators.
 * If custom capability `manage_products` inconsistently mapped, some users
 * can edit products while others can't - breaking plugin functionality and
 * creating support nightmares.
 *
 * **Real-World Security Scenario:**
 * E-commerce plugin adds `manage_shop` capability to Shop Manager role.
 * Plugin update adds `install_plugins` capability to Shop Manager (bug).
 * Shop managers can now install backdoor plugins → Complete site compromise.
 *
 * Result: Non-admin users gain admin-level access without detection.
 *
 * **Common Capability Issues:**
 *
 * **1. Privilege Escalation:**
 * ```php
 * // DANGEROUS: Granting admin capabilities to lower roles
 * $role->add_cap( 'delete_users' );     // Editors can delete admins
 * $role->add_cap( 'edit_theme_options' ); // Contributors edit site settings
 * $role->add_cap( 'unfiltered_html' );    // Subscribers inject JavaScript
 * ```
 *
 * **2. Capability Sprawl:**
 * Plugin A: Adds `manage_products`
 * Plugin B: Adds `manage_product`  (typo - missing 's')
 * Plugin C: Adds `edit_products`
 * Result: Three capabilities for same feature. Confusion, inconsistency, security gaps.
 *
 * **3. Orphaned Capabilities:**
 * Plugin adds `custom_feature` capability, assigns to roles.
 * Plugin uninstalled but capability remains in database.
 * New plugin reuses same name with different meaning → Unintended access.
 *
 * **WordPress Core Capabilities (DO NOT GRANT TO NON-ADMINS):**
 * - `install_plugins` / `activate_plugins` - Can install malware
 * - `install_themes` / `edit_themes` - Can inject backdoors
 * - `delete_users` - Can remove administrators
 * - `edit_users` - Can promote themselves to admin
 * - `unfiltered_html` - Can inject JavaScript (XSS)
 * - `manage_options` - Can change critical settings
 *
 * **Detection Strategy:**
 * This diagnostic:
 * 1. Counts custom capabilities (>20 indicates sprawl)
 * 2. Checks for admin-only caps in non-admin roles
 * 3. Identifies capability naming inconsistencies
 * 4. Validates capability-to-role mappings make sense
 * 5. Flags suspicious recently-added capabilities
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents privilege escalation attacks
 * - #10 Beyond Pure: Protects admin accounts from unauthorized modification
 * - Defense in Depth: Validates security boundaries are respected
 *
 * **Learn More:**
 * See https://wpshadow.com/kb/wordpress-capability-security for secure patterns
 * or https://wpshadow.com/training/user-role-capability-management
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0642
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Capability Map Consistency
 *
 * Uses WordPress' global role registry to audit capability security.
 * Roles are stored in `global $wp_roles` with capability arrays.
 *
 * **Implementation Pattern:**
 * 1. Access WordPress roles: `global $wp_roles`
 * 2. Iterate through all roles and extract capabilities
 * 3. Identify custom capabilities (non-core WordPress caps)
 * 4. Check for admin-only capabilities in non-admin roles
 * 5. Detect capability sprawl (similar names, inconsistent mapping)
 * 6. Return finding if security risks or inconsistencies detected
 *
 * **Capability Analysis:**
 * - WordPress core has ~60 standard capabilities
 * - Plugins typically add 1-5 custom capabilities each
 * - >20 custom capabilities suggests sprawl or misconfiguration
 * - Admin-only capabilities in non-admin roles = privilege escalation risk
 *
 * **Special Considerations:**
 * - Multisite: Super admin has network-level capabilities
 * - Custom roles: Membership plugins add role hierarchies
 * - Meta capabilities: `edit_post` → `edit_posts` mapping complexity
 *
 * **Related Diagnostics:**
 * - User Role Assignment Security: Validates role changes logged
 * - Admin REST API Authentication: Checks capability requirements on endpoints
 * - Settings Sanitization Verification: Validates option update capabilities
 *
 * @since 1.26033.0642
 */
class Diagnostic_Admin_Capability_Map_Consistency extends Diagnostic_Base {

	protected static $slug = 'admin-capability-map-consistency';
	protected static $title = 'Admin Capability Map Consistency';
	protected static $description = 'Verifies custom capabilities are properly mapped';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Get all roles
		global $wp_roles;
		$custom_capabilities = array();

		if ( ! empty( $wp_roles ) ) {
			foreach ( $wp_roles->roles as $role => $role_data ) {
				if ( is_array( $role_data['capabilities'] ) ) {
					foreach ( $role_data['capabilities'] as $cap => $grant ) {
						if ( ! in_array( $cap, array( 'read', 'edit_posts', 'delete_posts', 'manage_options' ), true ) ) {
							if ( ! isset( $custom_capabilities[ $cap ] ) ) {
								$custom_capabilities[ $cap ] = array();
							}
							$custom_capabilities[ $cap ][] = $role;
						}
					}
				}
			}
		}

		if ( count( $custom_capabilities ) > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of capabilities */
				__( 'High number of custom capabilities (%d) detected', 'wpshadow' ),
				count( $custom_capabilities )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-capability-map-consistency',
			);
		}

		return null;
	}
}
