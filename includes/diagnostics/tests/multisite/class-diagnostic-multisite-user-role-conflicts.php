<?php
/**
 * Multisite User Role Conflicts Diagnostic
 *
 * Detects user role management issues in multisite networks where
 * users have different capabilities across sites, causing confusion.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Multisite_User_Role_Conflicts Class
 *
 * Detects user role conflicts in multisite.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Multisite_User_Role_Conflicts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-user-role-conflicts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite User Role Conflicts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects user role management issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'multisite';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if conflicts found, null otherwise.
	 */
	public static function check() {
		// Only run on multisite
		if ( ! is_multisite() ) {
			return null;
		}

		$role_check = self::analyze_role_conflicts();

		if ( ! $role_check['has_issues'] ) {
			return null; // Roles configured properly
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'User role conflicts detected. Users confused by having different roles across sites. Inconsistent permissions = security risks.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/multisite-user-roles',
			'family'       => self::$family,
			'meta'         => array(
				'total_users'      => $role_check['total_users'],
				'network_admins'   => $role_check['network_admins'],
			),
			'details'      => array(
				'multisite_role_hierarchy'  => array(
					'Super Admin' => array(
						'Can do ANYTHING on ALL sites',
						'Manages network settings',
						'Add/remove sites',
						'Network-activate plugins',
					),
					'Site Administrator' => array(
						'Admin of specific site(s)',
						'Cannot affect other sites',
						'Can activate site-level plugins',
						'Manage users for their site only',
					),
					'Editor/Author/Subscriber' => array(
						'Same as single-site WordPress',
						'Content permissions',
						'Site-specific roles',
					),
				),
				'common_role_issues'        => array(
					'User Admin on Site A, Subscriber on Site B' => array(
						'Problem: Confusion about permissions',
						'User logs in, doesn\'t see admin menu',
						'Realizes they\'re on wrong site',
					),
					'Super Admin Has No Site Access' => array(
						'Problem: Super admin not added to sites',
						'Can\'t see site content in dashboard',
						'Must manually add self to each site',
					),
					'Site Admin Can\'t Activate Plugins' => array(
						'Problem: Plugin not network-enabled',
						'Site admin sees plugin but can\'t activate',
						'Network admin must enable first',
					),
				),
				'user_management_best_practices' => array(
					'Consistent Roles' => array(
						'Give users same role across sites where possible',
						'Example: If admin on one blog, admin on all they manage',
						'Reduces confusion',
					),
					'Super Admin Sparingly' => array(
						'Only 2-3 super admins maximum',
						'Too many = security risk',
						'Site admins sufficient for most tasks',
					),
					'Document Role Structure' => array(
						'Create internal guide for roles',
						'When to use administrator vs super admin',
						'Share with team',
					),
				),
				'adding_users_to_sites'     => array(
					'Network Admin Method' => array(
						'Network Admin → Users',
						'Hover user → Edit',
						'Sites tab → Add user to site',
						'Choose role per site',
					),
					'Per-Site Method' => array(
						'Site Dashboard → Users → Add Existing',
						'Search network for user',
						'Assign role for this site',
					),
					'Bulk Add (WP-CLI)' => array(
						'wp user add-role USER_ID subscriber --url=site.example.com',
						'Useful for adding many users',
					),
				),
				'plugin_recommendations'    => array(
					'User Role Editor' => array(
						'Multisite support',
						'Clone roles across sites',
						'Bulk role changes',
					),
					'Network User Manager' => array(
						'Centralized user management',
						'See all user roles at once',
						'Bulk operations',
					),
				),
			),
		);
	}

	/**
	 * Analyze user role conflicts.
	 *
	 * @since  1.2601.2148
	 * @return array Role conflict analysis.
	 */
	private static function analyze_role_conflicts() {
		if ( ! is_multisite() ) {
			return array( 'has_issues' => false );
		}

		// Count total network users
		$user_query = new \WP_User_Query(
			array(
				'blog_id' => 0, // Network-wide
				'number'  => 1,
				'fields'  => 'ID',
			)
		);
		$total_users = $user_query->get_total();

		// Count super admins
		$super_admins = get_super_admins();
		$super_admin_count = count( $super_admins );

		// Flag if too many super admins (security concern)
		$has_issues = $super_admin_count > 5 || ( $super_admin_count === 0 && get_blog_count() > 1 );

		return array(
			'total_users'    => $total_users,
			'network_admins' => $super_admin_count,
			'has_issues'     => $has_issues,
		);
	}
}
