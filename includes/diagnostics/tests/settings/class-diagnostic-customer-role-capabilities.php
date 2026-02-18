<?php
/**
 * Customer Role Capabilities Diagnostic (WooCommerce)
 *
 * Validates WooCommerce customer role capabilities to ensure proper
 * security and functionality for shop customers.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Role Capabilities Diagnostic Class
 *
 * Checks WooCommerce customer role configuration.
 *
 * @since 1.6032.1300
 */
class Diagnostic_Customer_Role_Capabilities extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'customer-role-capabilities';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Role Capabilities (WooCommerce)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates WooCommerce customer role security';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1300
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only run if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$issues = array();
		$roles  = wp_roles()->roles;

		// Check if customer role exists.
		if ( ! isset( $roles['customer'] ) ) {
			$issues[] = __( 'WooCommerce customer role missing (may cause checkout issues)', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Critical: WooCommerce customer role is missing.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'recommendation' => __( 'Reinstall WooCommerce or manually recreate the customer role.', 'wpshadow' ),
				),
			);
		}

		$customer_caps = $roles['customer']['capabilities'];

		// Expected customer capabilities.
		$expected_caps = array(
			'read'           => true,
		);

		// Capabilities customers should NOT have.
		$forbidden_caps = array(
			'edit_posts',
			'delete_posts',
			'publish_posts',
			'upload_files',
			'edit_pages',
			'edit_others_posts',
			'manage_categories',
			'moderate_comments',
			'manage_options',
		);

		// Check for missing expected capabilities.
		foreach ( $expected_caps as $cap => $should_have ) {
			if ( empty( $customer_caps[ $cap ] ) ) {
				$issues[] = sprintf(
					/* translators: %s: capability name */
					__( 'Customer role lacks "%s" capability', 'wpshadow' ),
					$cap
				);
			}
		}

		// Check for forbidden capabilities.
		$has_forbidden = array();
		foreach ( $forbidden_caps as $cap ) {
			if ( ! empty( $customer_caps[ $cap ] ) ) {
				$has_forbidden[] = $cap;
			}
		}

		if ( ! empty( $has_forbidden ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of capabilities */
				__( 'Customer role has elevated capabilities: %s', 'wpshadow' ),
				implode( ', ', $has_forbidden )
			);
		}

		// Check for customers with multiple roles.
		$customers_with_extra_roles = get_users(
			array(
				'role__in' => array( 'customer' ),
				'fields'   => array( 'ID', 'user_login' ),
			)
		);

		$multi_role_customers = array();
		foreach ( $customers_with_extra_roles as $user ) {
			$user_obj = new \WP_User( $user->ID );
			if ( count( $user_obj->roles ) > 1 ) {
				$multi_role_customers[] = array(
					'user_login' => $user->user_login,
					'roles'      => $user_obj->roles,
				);
			}
		}

		if ( count( $multi_role_customers ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of customers with multiple roles */
				__( '%d customers have multiple roles (security review recommended)', 'wpshadow' ),
				count( $multi_role_customers )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of customer role issues */
					__( 'Found %d WooCommerce customer role configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'details'      => array(
					'issues'                 => $issues,
					'multi_role_customers'   => array_slice( $multi_role_customers, 0, 10 ),
					'recommendation'         => __( 'Review customer role capabilities and ensure they are appropriate for shop customers.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
