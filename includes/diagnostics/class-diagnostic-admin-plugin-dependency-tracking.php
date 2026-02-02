<?php
/**
 * Admin Plugin Dependency Tracking
 *
 * Checks if plugins have unmet dependencies and are properly tracked.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0640
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin Plugin Dependency Tracking
 *
 * @since 1.26033.0640
 */
class Diagnostic_Admin_Plugin_Dependency_Tracking extends Diagnostic_Base {

	protected static $slug = 'admin-plugin-dependency-tracking';
	protected static $title = 'Admin Plugin Dependency Tracking';
	protected static $description = 'Verifies plugins don\'t have unmet dependencies';
	protected static $family = 'admin-security';

	public static function check() {
		$issues = array();

		// Get all active plugins
		$plugins = get_plugins();
		$unmet_dependencies = 0;

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			// Check for requires_php and requires_wp
			$requires_php = $plugin_data['RequiresPHP'] ?? '';
			$requires_wp  = $plugin_data['RequiresWP'] ?? '';

			if ( $requires_php ) {
				if ( version_compare( PHP_VERSION, $requires_php, '<' ) ) {
					$unmet_dependencies++;
				}
			}

			if ( $requires_wp ) {
				if ( version_compare( get_bloginfo( 'version' ), $requires_wp, '<' ) ) {
					$unmet_dependencies++;
				}
			}
		}

		if ( $unmet_dependencies > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of plugins */
				__( '%d plugin(s) have unmet version requirements', 'wpshadow' ),
				$unmet_dependencies
			);
		}

		// Check for inactive plugins consuming resources
		$inactive_plugins = get_option( 'wpshadow_inactive_plugins', array() );
		if ( count( $inactive_plugins ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of plugins */
				__( '%d inactive plugins may be consuming resources', 'wpshadow' ),
				count( $inactive_plugins )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-plugin-dependency-tracking',
			);
		}

		return null;
	}
}
