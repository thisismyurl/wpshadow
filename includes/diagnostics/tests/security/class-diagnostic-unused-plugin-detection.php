<?php
/**
 * Unused Plugin Detection Diagnostic
 *
 * Issue #4908: Inactive Plugins Still Installed (Attack Surface)
 * Pillar: 🛡️ Safe by Default
 *
 * Checks for inactive plugins that should be deleted.
 * Inactive plugins can still have exploitable vulnerabilities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Unused_Plugin_Detection Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Unused_Plugin_Detection extends Diagnostic_Base {

	protected static $slug = 'unused-plugin-detection';
	protected static $title = 'Inactive Plugins Still Installed (Attack Surface)';
	protected static $description = 'Checks for inactive plugins that increase attack surface';
	protected static $family = 'security';

	public static function check() {
		// Get all plugins and active plugins
		$all_plugins = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		$inactive_plugins = array();

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( ! in_array( $plugin_file, $active_plugins, true ) ) {
				$inactive_plugins[] = $plugin_data['Name'];
			}
		}

		if ( ! empty( $inactive_plugins ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Inactive plugins can still be exploited by attackers. Delete plugins you don\'t use to reduce attack surface.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/unused-plugins',
				'details'      => array(
					'inactive_plugins'        => $inactive_plugins,
					'count'                   => count( $inactive_plugins ),
					'security_principle'      => 'Minimize attack surface - remove unused code',
					'exploit_possibility'     => 'Direct file access can exploit inactive plugins',
				),
			);
		}

		return null;
	}
}
