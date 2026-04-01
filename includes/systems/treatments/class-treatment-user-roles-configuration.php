<?php
/**
 * Treatment for User Roles Configuration - Standard Roles Verification
 *
 * Verifies and repairs WordPress standard user roles.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_User_Roles_Configuration Class
 *
 * Ensures all WordPress standard roles are properly configured.
 *
 * @since 0.6093.1200
 */
class Treatment_User_Roles_Configuration extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 0.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'user-roles-configuration';
	}

	/**
	 * Apply the treatment.
	 *
	 * Verifies and repairs WordPress user roles.
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $data    Additional data about the operation.
	 * }
	 */
	public static function apply() {
		global $wp_roles;

		if ( ! isset( $wp_roles ) || ! is_object( $wp_roles ) ) {
			return array(
				'success' => false,
				'message' => __( 'Unable to access WordPress roles object', 'wpshadow' ),
			);
		}

		$repaired = array();

		// Standard WordPress roles that should exist
		$standard_roles = array(
			'administrator' => array(
				'display_name' => __( 'Administrator' ),
				'capabilities' => array( 'manage_options' => true ),
			),
			'editor' => array(
				'display_name' => __( 'Editor' ),
				'capabilities' => array( 'edit_posts' => true ),
			),
			'author' => array(
				'display_name' => __( 'Author' ),
				'capabilities' => array( 'edit_own_posts' => true ),
			),
			'contributor' => array(
				'display_name' => __( 'Contributor' ),
				'capabilities' => array( 'edit_own_posts' => true ),
			),
			'subscriber' => array(
				'display_name' => __( 'Subscriber' ),
				'capabilities' => array( 'read' => true ),
			),
		);

		foreach ( $standard_roles as $role_slug => $role_data ) {
			$role = $wp_roles->get_role( $role_slug );

			if ( null === $role ) {
				// Role doesn't exist, create it
				$wp_roles->add_role( $role_slug, $role_data['display_name'], $role_data['capabilities'] );
				$repaired[] = $role_slug;
			}
		}

		if ( empty( $repaired ) ) {
			return array(
				'success' => true,
				'message' => __( 'All standard user roles are properly configured', 'wpshadow' ),
				'data'    => array(
					'repaired_count' => 0,
				),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %d: count */
				__( 'Repaired %d user role(s)', 'wpshadow' ),
				count( $repaired )
			),
			'data'    => array(
				'repaired_count' => count( $repaired ),
				'repaired_roles' => $repaired,
			),
		);
	}

	/**
	 * Undo the treatment.
	 *
	 * Note: Cannot reliably undo role modifications.
	 *
	 * @since 0.6093.1200
	 * @return array Result array.
	 */
	public static function undo() {
		return array(
			'success' => false,
			'message' => __( 'User role modifications cannot be automatically reverted', 'wpshadow' ),
			'data'    => array(
				'note' => __( 'Please manually verify or restore from backup if needed', 'wpshadow' ),
			),
		);
	}
}
