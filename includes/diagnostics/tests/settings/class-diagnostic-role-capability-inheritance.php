<?php
/**
 * Role Capability Inheritance Diagnostic
 *
 * Validates that role hierarchy and capability inheritance is properly
 * configured, ensuring lower roles don't have more permissions than
 * higher roles.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Role Capability Inheritance Diagnostic Class
 *
 * Checks role hierarchy for capability inconsistencies.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Role_Capability_Inheritance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'role-capability-inheritance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Role Capability Inheritance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates role hierarchy and capability inheritance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$roles  = wp_roles()->roles;

		// Expected hierarchy (lower number = more permissions).
		$hierarchy = array(
			'administrator' => 5,
			'editor'        => 4,
			'author'        => 3,
			'contributor'   => 2,
			'subscriber'    => 1,
		);

		// Critical capabilities that should respect hierarchy.
		$critical_caps = array(
			'manage_options',
			'edit_users',
			'delete_users',
			'create_users',
			'promote_users',
			'edit_pages',
			'delete_pages',
			'publish_pages',
			'edit_others_posts',
			'delete_others_posts',
			'publish_posts',
		);

		// Check each pair of roles for hierarchy violations.
		foreach ( $hierarchy as $higher_role => $higher_level ) {
			if ( ! isset( $roles[ $higher_role ] ) ) {
				continue;
			}

			$higher_caps = $roles[ $higher_role ]['capabilities'];

			foreach ( $hierarchy as $lower_role => $lower_level ) {
				if ( $lower_level >= $higher_level || ! isset( $roles[ $lower_role ] ) ) {
					continue;
				}

				$lower_caps = $roles[ $lower_role ]['capabilities'];

				// Check if lower role has capabilities that higher role doesn't.
				$violations = array();
				foreach ( $critical_caps as $cap ) {
					if ( ! empty( $lower_caps[ $cap ] ) && empty( $higher_caps[ $cap ] ) ) {
						$violations[] = $cap;
					}
				}

				if ( ! empty( $violations ) ) {
					$issues[] = array(
						'higher_role'  => $higher_role,
						'lower_role'   => $lower_role,
						'capabilities' => $violations,
						'message'      => sprintf(
							/* translators: 1: lower role, 2: higher role, 3: capabilities */
							__( '%1$s has capabilities that %2$s lacks: %3$s', 'wpshadow' ),
							$lower_role,
							$higher_role,
							implode( ', ', $violations )
						),
					);
				}
			}
		}

		// Check for custom roles that break hierarchy.
		foreach ( $roles as $role_slug => $role_data ) {
			if ( isset( $hierarchy[ $role_slug ] ) ) {
				continue; // Skip default roles.
			}

			$custom_caps = $role_data['capabilities'];

			// Check if custom role has more permissions than administrator.
			if ( isset( $roles['administrator'] ) ) {
				$admin_caps = $roles['administrator']['capabilities'];
				$extra_caps = array();

				foreach ( $critical_caps as $cap ) {
					if ( ! empty( $custom_caps[ $cap ] ) && empty( $admin_caps[ $cap ] ) ) {
						$extra_caps[] = $cap;
					}
				}

				if ( ! empty( $extra_caps ) ) {
					$issues[] = array(
						'custom_role'  => $role_slug,
						'role_name'    => $role_data['name'],
						'capabilities' => $extra_caps,
						'message'      => sprintf(
							/* translators: 1: role name, 2: capabilities */
							__( 'Custom role "%1$s" has capabilities beyond administrator: %2$s', 'wpshadow' ),
							$role_data['name'],
							implode( ', ', $extra_caps )
						),
					);
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of hierarchy violations */
					__( 'Found %d role capability hierarchy violations.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'recommendation' => __( 'Review role capabilities to ensure proper hierarchy. Lower roles should not have more permissions than higher roles.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
