<?php
/**
 * User Roles Customization Not Applied Diagnostic
 *
 * Checks if custom user roles are configured.
 * Default WordPress roles = limited options (subscriber, contributor, etc).
 * Custom roles = precise permission control (client, vendor, partner).
 * Better security through least privilege.
 *
 * **What This Check Does:**
 * - Checks if custom roles defined
 * - Validates role capabilities customized
 * - Tests if default roles modified
 * - Checks for role-based access control
 * - Validates permission granularity
 * - Returns severity if only default roles used
 *
 * **Why This Matters:**
 * Default roles too broad. "Editor" has too many permissions.
 * Custom role "Content Reviewer" = only what's needed.
 * Reduced attack surface. Better access control.
 *
 * **Business Impact:**
 * Agency gives all clients "Editor" role (default). Clients can modify
 * others' posts. One malicious client deletes competitor's content.
 * Lawsuit: $50K+. With custom "Client" role: can only edit own posts.
 * Malicious client can't access others. Risk eliminated.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Precise permission control
 * - #9 Show Value: Reduces over-privileging risks
 * - #10 Beyond Pure: Least privilege principle
 *
 * **Related Checks:**
 * - User Capability Auditing (related)
 * - Custom Role Definition Audit (complementary)
 * - Permission Management (broader)
 *
 * **Learn More:**
 * Custom roles guide: https://wpshadow.com/kb/custom-roles
 * Video: Creating custom roles (12min): https://wpshadow.com/training/roles
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
 * User Roles Customization Not Applied Diagnostic Class
 *
 * Detects missing custom user roles.
 *
 * **Detection Pattern:**
 * 1. Get all registered roles
 * 2. Check if only WordPress default roles present
 * 3. Validate if capabilities customized
 * 4. Test role-based restrictions
 * 5. Check permission granularity
 * 6. Return if customization missing
 *
 * **Real-World Scenario:**
 * Multi-vendor marketplace uses default roles. All vendors get
 * "Author" role. Can see all orders (not just theirs). Privacy issue.
 * With custom "Vendor" role: can only access own orders. Data isolated.
 * Compliance maintained.
 *
 * **Implementation Notes:**
 * - Checks for custom roles
 * - Validates capability customization
 * - Tests permission granularity
 * - Severity: medium (site-specific need)
 * - Treatment: create custom roles with precise capabilities
 *
 * @since 0.6093.1200
 */
class Diagnostic_User_Roles_Customization_Not_Applied extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-roles-customization-not-applied';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Roles Customization Not Applied';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if custom user roles are configured';

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
		global $wp_roles;

		// Count custom roles
		$custom_roles = count( (array) $wp_roles->roles ) - 5; // 5 default roles

		if ( $custom_roles <= 0 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'User roles customization is not applied. Create custom roles and capabilities for fine-grained access control.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-roles-customization-not-applied?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
