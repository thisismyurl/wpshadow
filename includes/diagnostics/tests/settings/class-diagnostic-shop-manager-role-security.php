<?php
/**
 * Shop Manager Role Security Diagnostic (WooCommerce)
 *
 * Validates WooCommerce shop manager role capabilities to ensure
 * appropriate access without overly broad permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shop Manager Role Security Diagnostic Class
 *
 * Checks WooCommerce shop manager role security.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Shop_Manager_Role_Security extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'shop-manager-role-security';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Shop Manager Role Security (WooCommerce)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates WooCommerce shop manager role security';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only run if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$issues = array();
		$roles  = wp_roles()->roles;

		// Check if shop_manager role exists.
		if ( ! isset( $roles['shop_manager'] ) ) {
			return null; // Shop manager not configured, which is fine.
		}

		$manager_caps = $roles['shop_manager']['capabilities'];

		// Capabilities shop managers should NOT have.
		$dangerous_caps = array(
			'edit_users',
			'delete_users',
			'create_users',
			'promote_users',
			'install_plugins',
			'activate_plugins',
			'update_plugins',
			'delete_plugins',
			'install_themes',
			'update_themes',
			'delete_themes',
			'edit_themes',
			'edit_plugins',
			'update_core',
		);

		$has_dangerous = array();
		foreach ( $dangerous_caps as $cap ) {
			if ( ! empty( $manager_caps[ $cap ] ) ) {
				$has_dangerous[] = $cap;
			}
		}

		if ( ! empty( $has_dangerous ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of capabilities */
				__( 'Shop manager role has dangerous capabilities: %s', 'wpshadow' ),
				implode( ', ', $has_dangerous )
			);
		}

		// Check if shop managers can manage_options.
		if ( ! empty( $manager_caps['manage_options'] ) ) {
			$issues[] = __( 'Shop manager can manage site options (security risk)', 'wpshadow' );
		}

		// Check for shop managers.
		$shop_managers = get_users(
			array(
				'role'   => 'shop_manager',
				'fields' => array( 'ID', 'user_login', 'user_email' ),
			)
		);

		if ( empty( $shop_managers ) && isset( $roles['shop_manager'] ) ) {
			return null; // Role exists but no users, which is fine.
		}

		// Check for shop managers with multiple roles.
		$multi_role_managers = array();
		foreach ( $shop_managers as $user ) {
			$user_obj = new \WP_User( $user->ID );
			if ( count( $user_obj->roles ) > 1 ) {
				$multi_role_managers[] = array(
					'user_login' => $user->user_login,
					'roles'      => $user_obj->roles,
				);
			}
		}

		if ( ! empty( $multi_role_managers ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of shop managers with multiple roles */
				__( '%d shop managers have multiple roles (verify intentional)', 'wpshadow' ),
				count( $multi_role_managers )
			);
		}

		// Check if shop managers can edit other users' orders.
		if ( ! empty( $manager_caps['edit_others_shop_orders'] ) ) {
			// This is expected, but verify they can't delete.
			if ( ! empty( $manager_caps['delete_others_shop_orders'] ) ) {
				$issues[] = __( 'Shop managers can delete other managers\' orders (consider restricting)', 'wpshadow' );
			}
		}

		// Check for excessive shop managers.
		if ( count( $shop_managers ) > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of shop managers */
				__( '%d shop manager accounts (consider limiting to essential personnel)', 'wpshadow' ),
				count( $shop_managers )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of shop manager security issues */
					__( 'Found %d WooCommerce shop manager security concerns.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'details'      => array(
					'issues'              => $issues,
					'shop_manager_count'  => count( $shop_managers ),
					'multi_role_managers' => array_slice( $multi_role_managers, 0, 10 ),
					'recommendation'      => __( 'Review shop manager capabilities and limit access to essential functions only.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
