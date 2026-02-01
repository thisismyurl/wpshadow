<?php
/**
 * Role Creation Plugin Security Diagnostic
 *
 * Checks for plugins that allow role creation and ensures proper security
 * controls are in place to prevent unauthorized capability escalation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1230
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Role Creation Plugin Security Diagnostic Class
 *
 * Validates security of role creation functionality.
 *
 * @since 1.6032.1230
 */
class Diagnostic_Role_Creation_Plugin_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'role-creation-plugin-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Role Creation Plugin Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates security of role management plugins';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for role management plugins.
		$role_plugins = array(
			'user-role-editor/user-role-editor.php'   => 'User Role Editor',
			'members/members.php'                     => 'Members',
			'capability-manager-enhanced/capsman-enhanced.php' => 'PublishPress Capabilities',
		);

		$active_role_plugin = false;
		$plugin_name        = '';

		foreach ( $role_plugins as $plugin_path => $name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$active_role_plugin = true;
				$plugin_name        = $name;
				break;
			}
		}

		if ( ! $active_role_plugin ) {
			return null; // No role management plugin active.
		}

		// Check for custom roles with dangerous capabilities.
		$dangerous_caps = array( 'edit_users', 'delete_users', 'create_users', 'promote_users', 'manage_options' );
		$roles          = wp_roles()->roles;

		foreach ( $roles as $role_slug => $role_data ) {
			// Skip default admin role.
			if ( 'administrator' === $role_slug ) {
				continue;
			}

			// Check if custom role has dangerous capabilities.
			$has_dangerous_caps = array();
			foreach ( $dangerous_caps as $cap ) {
				if ( ! empty( $role_data['capabilities'][ $cap ] ) ) {
					$has_dangerous_caps[] = $cap;
				}
			}

			if ( ! empty( $has_dangerous_caps ) ) {
				$issues[] = array(
					'role'         => $role_slug,
					'role_name'    => $role_data['name'],
					'capabilities' => $has_dangerous_caps,
				);
			}
		}

		// Check for users who can edit roles.
		$users_with_role_edit = get_users(
			array(
				'capability__in' => array( 'edit_roles', 'promote_users' ),
				'fields'         => array( 'ID', 'user_login' ),
			)
		);

		$non_admin_editors = array();
		foreach ( $users_with_role_edit as $user ) {
			$user_obj = new \WP_User( $user->ID );
			if ( ! in_array( 'administrator', $user_obj->roles, true ) ) {
				$non_admin_editors[] = $user->user_login;
			}
		}

		if ( ! empty( $non_admin_editors ) ) {
			$issues[] = array(
				'issue'           => 'non_admin_role_editors',
				'users'           => $non_admin_editors,
				'user_count'      => count( $non_admin_editors ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: plugin name, 2: number of issues */
					__( '%1$s is active. Found %2$d role security concerns.', 'wpshadow' ),
					$plugin_name,
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'details'      => array(
					'plugin'         => $plugin_name,
					'issues'         => $issues,
					'recommendation' => __( 'Review custom roles with elevated capabilities and limit role editing to administrators only.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
