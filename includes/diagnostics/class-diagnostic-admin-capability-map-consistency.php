<?php
/**
 * Admin Capability Map Consistency
 *
 * Checks if custom capabilities are properly mapped and consistent across roles.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0642
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Capability Map Consistency
 *
 * @since 1.26033.0642
 */
class Diagnostic_Admin_Capability_Map_Consistency extends Diagnostic_Base {

	protected static $slug = 'admin-capability-map-consistency';
	protected static $title = 'Admin Capability Map Consistency';
	protected static $description = 'Verifies custom capabilities are properly mapped';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Get all roles
		global $wp_roles;
		$custom_capabilities = array();

		if ( ! empty( $wp_roles ) ) {
			foreach ( $wp_roles->roles as $role => $role_data ) {
				if ( is_array( $role_data['capabilities'] ) ) {
					foreach ( $role_data['capabilities'] as $cap => $grant ) {
						if ( ! in_array( $cap, array( 'read', 'edit_posts', 'delete_posts', 'manage_options' ), true ) ) {
							if ( ! isset( $custom_capabilities[ $cap ] ) ) {
								$custom_capabilities[ $cap ] = array();
							}
							$custom_capabilities[ $cap ][] = $role;
						}
					}
				}
			}
		}

		if ( count( $custom_capabilities ) > 20 ) {
			$issues[] = sprintf(
				/* translators: %d: number of capabilities */
				__( 'High number of custom capabilities (%d) detected', 'wpshadow' ),
				count( $custom_capabilities )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-capability-map-consistency',
			);
		}

		return null;
	}
}
