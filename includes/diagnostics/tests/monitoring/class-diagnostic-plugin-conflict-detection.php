<?php
/**
 * Plugin Conflict Detection Diagnostic
 *
 * Checks for known plugin conflicts that could cause downtime.
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
 * Plugin Conflict Detection Diagnostic Class
 *
 * Detects known problematic plugin combinations.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugin_Conflict_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-conflict-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Conflict Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for known plugin conflicts that could cause downtime';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'downtime-prevention';

	/**
	 * Run the plugin conflict diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if conflicts detected, null otherwise.
	 */
	public static function check() {
		$conflicts = self::detect_conflicts();

		if ( ! empty( $conflicts ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: list of conflicting plugins */
					__( 'Potential plugin conflicts detected: %s. These may cause performance issues or downtime.', 'wpshadow' ),
					implode( ', ', $conflicts )
				),
				'severity'    => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/resolve-plugin-conflicts?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'        => array(
					'conflicts' => $conflicts,
				),
			);
		}

		return null;
	}

	/**
	 * Detect known plugin conflicts.
	 *
	 * @since 0.6093.1200
	 * @return array List of conflicting plugins.
	 */
	private static function detect_conflicts(): array {
		$active_plugins = get_option( 'active_plugins', array() );
		$conflicts = array();

		// Known conflicting plugin pairs.
		$conflict_pairs = array(
			array( 'wordfence/wordfence.php', 'jetpack/jetpack.php' ),
			array( 'w3-total-cache/w3-total-cache.php', 'wp-super-cache/wp-cache.php' ),
			array( 'akismet/akismet.php', 'wphb/wp-hummingbird.php' ),
			array( 'divi-engine/divi-engine.php', 'elementor/elementor.php' ),
			array( 'wpmlv3/wpml.php', 'polylang/polylang.php' ),
			array( 'backwpup/backwpup.php', 'updraftplus/updraftplus.php' ),
		);

		foreach ( $conflict_pairs as $pair ) {
			$count = 0;
			$names = array();

			foreach ( $pair as $plugin ) {
				if ( in_array( $plugin, $active_plugins, true ) ) {
					$count++;
					$names[] = self::get_plugin_name( $plugin );
				}
			}

			if ( $count >= 2 ) {
				$conflicts[] = implode( ' + ', $names );
			}
		}

		return array_unique( $conflicts );
	}

	/**
	 * Get plugin name from plugin file.
	 *
	 * @since 0.6093.1200
	 * @param  string $plugin Plugin file path.
	 * @return string Plugin name.
	 */
	private static function get_plugin_name( string $plugin ): string {
		$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;

		if ( is_readable( $plugin_file ) ) {
			$plugin_data = get_plugin_data( $plugin_file );
			if ( ! empty( $plugin_data['Name'] ) ) {
				return $plugin_data['Name'];
			}
		}

		return basename( dirname( $plugin ) );
	}
}
