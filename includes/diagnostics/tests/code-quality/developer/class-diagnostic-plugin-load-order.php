<?php
/**
 * Plugin Load Order Diagnostic
 *
 * Checks if plugins are loading in the correct order without conflicts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Load Order Diagnostic Class
 *
 * Verifies that plugins are loading in the correct order and detects
 * potential conflicts or dependency issues.
 *
 * @since 1.6035.1300
 */
class Diagnostic_Plugin_Load_Order extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-load-order';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Load Order';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugins are loading in the correct order without conflicts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the plugin load order diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if load order issues detected, null otherwise.
	 */
	public static function check() {
		$issues      = array();
		$warnings    = array();
		$plugin_list = array();

		// Get list of active plugins.
		$active_plugins = get_option( 'active_plugins', array() );

		if ( empty( $active_plugins ) ) {
			return null; // No plugins to check.
		}

		// Get plugin headers to understand dependencies.
		$plugins_data = array();

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				$issues[] = sprintf(
					/* translators: %s: plugin file */
					__( 'Missing plugin file: %s', 'wpshadow' ),
					$plugin
				);
				continue;
			}

			$plugin_data             = get_plugin_data( $plugin_file, false, false );
			$plugins_data[ $plugin ] = $plugin_data;
			$plugin_list[]           = $plugin_data['Name'];
		}

		// Check for known dependency issues.
		$dependency_map = array(
			// If plugin A requires plugin B to load first.
			'wordpress-seo/wp-seo.php'          => array( 'jetpack/jetpack.php' ),
			'w3-total-cache/w3-total-cache.php' => array( 'query-monitor/query-monitor.php' ),
		);

		foreach ( $dependency_map as $plugin => $dependencies ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				foreach ( $dependencies as $dependency ) {
					if ( in_array( $dependency, $active_plugins, true ) ) {
						// Check load order.
						$plugin_pos = array_search( $plugin, $active_plugins, true );
						$dep_pos    = array_search( $dependency, $active_plugins, true );

						if ( $plugin_pos < $dep_pos ) {
							$warnings[] = sprintf(
								/* translators: 1: plugin name, 2: dependency name */
								__( '%1$s should load after %2$s', 'wpshadow' ),
								$plugins_data[ $plugin ]['Name'] ?? $plugin,
								$plugins_data[ $dependency ]['Name'] ?? $dependency
							);
						}
					}
				}
			}
		}

		// Check for must-use plugins.
		$mu_plugins_dir = WPMU_PLUGIN_DIR;
		if ( is_dir( $mu_plugins_dir ) ) {
			$mu_plugins = glob( $mu_plugins_dir . '/*.php' );

			if ( ! empty( $mu_plugins ) ) {
				$warnings[] = sprintf(
					/* translators: %d: number of mu-plugins */
					__( '%d must-use plugins detected - these load first', 'wpshadow' ),
					count( $mu_plugins )
				);
			}
		}

		// Check for plugin conflicts.
		$known_conflicts = array(
			// Caching plugins that conflict.
			array(
				'plugins' => array( 'wp-super-cache/wp-cache.php', 'w3-total-cache/w3-total-cache.php' ),
				'message' => __( 'Multiple caching plugins detected - can cause conflicts', 'wpshadow' ),
			),
			// SEO plugins that conflict.
			array(
				'plugins' => array( 'wordpress-seo/wp-seo.php', 'all-in-one-seo-pack/all_in_one_seo_pack.php' ),
				'message' => __( 'Multiple SEO plugins detected - can cause conflicts', 'wpshadow' ),
			),
			// Security plugins that conflict.
			array(
				'plugins' => array( 'wordfence/wordfence.php', 'all-in-one-wp-security-and-firewall/wp-security.php' ),
				'message' => __( 'Multiple security plugins detected - can cause conflicts', 'wpshadow' ),
			),
		);

		foreach ( $known_conflicts as $conflict ) {
			$active_conflicting = array_intersect( $conflict['plugins'], $active_plugins );
			if ( count( $active_conflicting ) > 1 ) {
				$issues[] = $conflict['message'];
			}
		}

		// Check plugin headers for missing required fields.
		foreach ( $plugins_data as $plugin => $data ) {
			if ( empty( $data['Name'] ) ) {
				$warnings[] = sprintf(
					/* translators: %s: plugin file */
					__( 'Plugin missing Name header: %s', 'wpshadow' ),
					$plugin
				);
			}

			if ( empty( $data['Author'] ) ) {
				$warnings[] = sprintf(
					/* translators: %s: plugin file */
					__( 'Plugin missing Author header: %s', 'wpshadow' ),
					$plugin
				);
			}
		}

		// Check for too many plugins (performance indicator).
		$plugin_count = count( $active_plugins );

		if ( $plugin_count > 50 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of plugins */
				__( 'High number of plugins (%d) - can impact performance', 'wpshadow' ),
				$plugin_count
			);
		}

		// Check for plugin activation logs.
		$activation_errors = get_option( 'plugin_activation_errors', array() );
		if ( ! empty( $activation_errors ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of errors */
				__( '%d plugin activation errors detected', 'wpshadow' ),
				count( $activation_errors )
			);
		}

		// Check for proper plugin initialization hooks.
		$plugins_with_init = 0;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
			if ( file_exists( $plugin_file ) ) {
				$content = file_get_contents( $plugin_file, false, null, 0, 1024 );

				if ( strpos( $content, 'do_action' ) !== false ||
					strpos( $content, 'add_action' ) !== false ) {
					++$plugins_with_init;
				}
			}
		}

		$stats = array(
			'total_plugins'      => $plugin_count,
			'plugins_with_hooks' => $plugins_with_init,
			'plugin_count'       => $plugin_count,
		);

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Plugin load order has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-load-order',
				'context'      => array(
					'stats'    => $stats,
					'plugins'  => $plugin_list,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Plugin load order has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-load-order',
				'context'      => array(
					'stats'    => $stats,
					'plugins'  => $plugin_list,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Plugin load order is good.
	}
}
