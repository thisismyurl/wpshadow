<?php
/**
 * Admin User Role Separation Not Implemented Diagnostic
 *
 * Checks if admin roles are separated.
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
 * Admin User Role Separation Not Implemented Diagnostic Class
 *
 * Detects missing role separation.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Admin_User_Role_Separation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-user-role-separation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin User Role Separation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin roles are separated';

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
		// Get all users with administrator role.
		$admin_users = get_users( array( 'role' => 'administrator' ) );
		$admin_count = count( $admin_users );

		// Get all roles.
		$roles = wp_roles();
		$all_roles = $roles->get_names();

		// Default WordPress roles.
		$default_roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );

		// Count custom roles.
		$custom_roles = array();
		foreach ( $all_roles as $role_slug => $role_name ) {
			if ( ! in_array( $role_slug, $default_roles, true ) ) {
				$custom_roles[] = $role_name;
			}
		}

		$custom_role_count = count( $custom_roles );

		// Get total user count.
		$total_users = count_users();
		$user_count = $total_users['total_users'];

		// Check for role management plugins.
		$role_plugins = array(
			'members/members.php'                      => 'Members',
			'user-role-editor/user-role-editor.php'    => 'User Role Editor',
			'advanced-access-manager/aam.php'          => 'Advanced Access Manager',
			'capability-manager-enhanced/capsman-enhanced.php' => 'PublishPress Capabilities',
		);

		$role_plugin_detected = false;
		$role_plugin_name     = '';

		foreach ( $role_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$role_plugin_detected = true;
				$role_plugin_name     = $name;
				break;
			}
		}

		// High risk: Many admins, no custom roles.
		if ( $admin_count > 3 && $custom_role_count === 0 && $user_count > 10 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: number of administrators, 2: total users */
					__( 'Role separation not implemented. %1$d users have administrator access out of %2$d total users. Following principle of least privilege, create custom roles with limited capabilities. Too many administrators increases attack surface.', 'wpshadow' ),
					$admin_count,
					$user_count
				),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/user-role-separation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'admin_count'         => $admin_count,
					'total_users'         => $user_count,
					'custom_role_count'   => 0,
					'role_plugin'         => $role_plugin_detected ? $role_plugin_name : 'None',
					'recommendation'      => __( 'Install User Role Editor (free, 1M+ installs) or Members plugin. Create roles like "Content Manager" (can publish posts but not install plugins) or "Shop Manager" (WooCommerce only).', 'wpshadow' ),
					'security_impact'     => array(
						'without_separation' => 'All admins can install plugins, delete site, access all data',
						'with_separation'    => 'Limited roles reduce damage from compromised accounts',
						'principle'          => 'Least Privilege: Grant minimum permissions needed',
					),
					'example_roles'       => array(
						'content_manager' => 'Publish posts, upload media (no plugin install)',
						'shop_manager'    => 'Manage products, orders (no theme/plugin access)',
						'support_agent'   => 'Read-only access, reply to comments',
					),
				),
			);
		}

		// Medium: Only default roles but site is complex.
		if ( $custom_role_count === 0 && $user_count > 20 ) {
			return array(
				'id'          => self::$slug,
				'title'       => __( 'Custom Roles Recommended', 'wpshadow' ),
				'description' => sprintf(
					/* translators: %d: number of users */
					__( 'No custom roles defined for %d users. Consider creating specialized roles for better access control and security. Default WordPress roles may be too broad for your needs.', 'wpshadow' ),
					$user_count
				),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/user-role-separation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'admin_count'       => $admin_count,
					'total_users'       => $user_count,
					'custom_role_count' => 0,
					'recommendation'    => __( 'Review user access needs and create appropriate custom roles.', 'wpshadow' ),
				),
			);
		}

		// No issues - custom roles exist or site is small.
		return null;
	}
}
