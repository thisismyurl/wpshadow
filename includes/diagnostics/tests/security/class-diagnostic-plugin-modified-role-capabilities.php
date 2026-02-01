<?php
/**
 * Plugin-Modified Role Capabilities Diagnostic
 *
 * Detects when plugins modify default WordPress role capabilities in ways
 * that could create security vulnerabilities or unexpected behavior.
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
 * Plugin-Modified Role Capabilities Diagnostic Class
 *
 * Identifies plugin modifications to default role capabilities.
 *
 * @since 1.6032.1230
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
	 * @since  1.6032.1230
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
