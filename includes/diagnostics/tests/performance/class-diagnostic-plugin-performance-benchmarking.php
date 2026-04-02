<?php
/**
 * Plugin Performance Benchmarking Diagnostic
 *
 * Identifies slow plugins that are impacting overall WordPress performance
 * and recommends optimization or replacement.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Performance Benchmarking Diagnostic Class
 *
 * Analyzes plugin performance:
 * - Plugin count correlation with performance
 * - Heavy plugin identification
 * - Performance plugin presence
 * - Optimization recommendations
 *
 * @since 1.6093.1200
 */
class Diagnostic_Plugin_Performance_Benchmarking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-performance-benchmarking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Performance Benchmarking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies slow plugins affecting WordPress performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_count   = count( $active_plugins );

		// Known heavy plugins
		$heavy_plugins = array(
			'elementor/elementor.php'             => 'Elementor',
			'divi/divi.php'                       => 'Divi',
			'wp-smush/wp-smush.php'               => 'WP Smush',
			'wordfence/wordfence.php'             => 'Wordfence',
			'jetpack/jetpack.php'                 => 'Jetpack',
			'all-in-one-wp-security-and-firewall/all_in_one_wp_security.php' => 'All In One Security',
		);

		$heavy_active = 0;
		foreach ( $heavy_plugins as $plugin_path => $plugin_name ) {
			if ( in_array( $plugin_path, $active_plugins, true ) ) {
				$heavy_active++;
			}
		}

		// Flag if many plugins or many heavy plugins
		if ( $plugin_count > 20 || $heavy_active >= 3 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: plugin count, %d: heavy plugins */
					__( 'Site has %d active plugins with %d known heavy plugins. Every plugin adds load time (avg 5-10ms per plugin).', 'wpshadow' ),
					$plugin_count,
					$heavy_active
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-performance',
				'meta'          => array(
					'total_plugins'        => $plugin_count,
					'heavy_plugins'        => $heavy_active,
					'recommendation'       => 'Deactivate unused plugins. Merge functionality: use one page builder instead of multiple.',
					'impact'               => 'Each plugin adds 5-10ms. Removing 10 plugins could save 50-100ms per request.',
					'optimization'         => array(
						'Delete unused plugins',
						'Consolidate functionality',
						'Use lightweight alternatives',
						'Lazy-load plugin scripts',
					),
				),
			);
		}

		return null;
	}
}
