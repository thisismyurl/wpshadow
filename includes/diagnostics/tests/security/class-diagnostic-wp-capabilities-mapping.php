<?php
/**
 * Diagnostic: WordPress Capabilities Mapping
 *
 * Checks if custom capabilities are properly mapped to roles.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Wp_Capabilities_Mapping
 *
 * Tests WordPress capabilities and role mappings.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Wp_Capabilities_Mapping extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-capabilities-mapping';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Capabilities Mapping';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if custom capabilities are properly mapped to roles';

	/**
	 * Check capabilities mapping.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wp_roles;

		if ( ! isset( $wp_roles ) || ! is_object( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}

		$roles = $wp_roles->get_names();

		if ( empty( $roles ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No WordPress roles found. Role system may be corrupted.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_capabilities_mapping',
				'meta'        => array(
					'roles_found' => false,
				),
			);
		}

		// Check for custom capabilities without caps (orphaned).
		$orphaned = array();

		foreach ( $wp_roles->roles as $role_name => $role_data ) {
			if ( isset( $role_data['capabilities'] ) && is_array( $role_data['capabilities'] ) ) {
				foreach ( $role_data['capabilities'] as $cap => $has_cap ) {
					// Warn if capability doesn't follow standard naming.
					if ( strpos( $cap, ':' ) === false && ! in_array( $cap, array( 'read', 'edit_posts', 'publish_posts', 'manage_options' ), true ) ) {
						$orphaned[] = $cap;
					}
				}
			}
		}

		if ( ! empty( $orphaned ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Custom capabilities detected that may not be standard. Verify capability mappings are correct and plugins defining them are active.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_capabilities_mapping',
				'meta'        => array(
					'orphaned_caps' => array_unique( $orphaned ),
					'role_count'    => count( $roles ),
				),
			);
		}

		return null;
	}
}
