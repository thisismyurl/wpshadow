<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Plugin Conflicts
 *
 * Detects common plugin conflicts and incompatibilities.
 * Conflicting plugins can cause fatal errors or unpredictable behavior.
 *
 * @since 1.2.0
 */
class Test_Plugin_Conflicts extends Diagnostic_Base {


	/**
	 * Check for plugin conflicts
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$conflicts = self::detect_conflicts();

		if ( empty( $conflicts ) ) {
			return null;
		}

		$threat = count( $conflicts ) * 20;
		$threat = min( 85, $threat );

		return array(
			'threat_level'  => $threat,
			'threat_color'  => 'orange',
			'passed'        => false,
			'issue'         => sprintf(
				'Detected %d potential plugin conflicts',
				count( $conflicts )
			),
			'metadata'      => array(
				'conflict_count' => count( $conflicts ),
				'conflicts'      => array_slice( $conflicts, 0, 5 ),
			),
			'kb_link'       => 'https://wpshadow.com/kb/plugin-conflicts/',
			'training_link' => 'https://wpshadow.com/training/wordpress-plugin-troubleshooting/',
		);
	}

	/**
	 * Guardian Sub-Test: Known conflict detection
	 *
	 * @return array Test result
	 */
	public static function test_known_conflicts(): array {
		$conflicts = self::detect_conflicts();

		return array(
			'test_name'      => 'Known Plugin Conflicts',
			'conflict_count' => count( $conflicts ),
			'conflicts'      => $conflicts,
			'passed'         => empty( $conflicts ),
			'description'    => empty( $conflicts ) ? 'No known conflicts detected' : sprintf( '%d conflicts detected', count( $conflicts ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Critical hooks overlap
	 *
	 * @return array Test result
	 */
	public static function test_hook_conflicts(): array {
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_hooks   = array();

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
			if ( file_exists( $plugin_file ) ) {
				$content = file_get_contents( $plugin_file, false, null, 0, 1000 );
				preg_match_all( '/add_(action|filter)\(\s["\']([^"\']+)["\']/', $content, $matches );
				if ( ! empty( $matches[2] ) ) {
					$plugin_hooks[ $plugin ] = array_unique( $matches[2] );
				}
			}
		}

		return array(
			'test_name'          => 'Hook Conflicts',
			'plugins_with_hooks' => count( $plugin_hooks ),
			'description'        => sprintf( '%d plugins register hooks', count( $plugin_hooks ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Class name collisions
	 *
	 * @return array Test result
	 */
	public static function test_class_collisions(): array {
		$active_plugins = get_option( 'active_plugins', array() );
		$classes_found  = array();
		$collisions     = array();

		foreach ( $active_plugins as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin );
			$php_files  = glob( $plugin_dir . '/**/*.php', GLOB_RECURSIVE );

			if ( $php_files ) {
				foreach ( array_slice( $php_files, 0, 5 ) as $file ) {
					$content = file_get_contents( $file );
					preg_match_all( '/class\s+(\w+)/', $content, $matches );
					if ( ! empty( $matches[1] ) ) {
						foreach ( $matches[1] as $class ) {
							if ( isset( $classes_found[ $class ] ) ) {
								$collisions[] = array(
									'class'   => $class,
									'plugins' => array( $classes_found[ $class ], $plugin ),
								);
							} else {
								$classes_found[ $class ] = $plugin;
							}
						}
					}
				}
			}
		}

		return array(
			'test_name'       => 'Class Name Collisions',
			'collision_count' => count( $collisions ),
			'collisions'      => $collisions,
			'passed'          => empty( $collisions ),
			'description'     => empty( $collisions ) ? 'No class name collisions' : sprintf( '%d class collisions found', count( $collisions ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Deprecated function usage
	 *
	 * @return array Test result
	 */
	public static function test_deprecated_functions(): array {
		$active_plugins   = get_option( 'active_plugins', array() );
		$deprecated_funcs = array(
			'add_custom_image_header',
			'add_custom_background',
			'screen_icon',
			'wp_list_categories',
		);

		$usage = array();
		foreach ( $active_plugins as $plugin ) {
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
			if ( file_exists( $plugin_file ) ) {
				$content = file_get_contents( $plugin_file );
				foreach ( $deprecated_funcs as $func ) {
					if ( strpos( $content, $func ) !== false ) {
						$usage[] = array(
							'plugin'   => $plugin,
							'function' => $func,
						);
					}
				}
			}
		}

		return array(
			'test_name'        => 'Deprecated Function Usage',
			'usage_count'      => count( $usage ),
			'deprecated_calls' => $usage,
			'passed'           => empty( $usage ),
			'description'      => empty( $usage ) ? 'No deprecated functions' : sprintf( '%d deprecated function calls', count( $usage ) ),
		);
	}

	/**
	 * Detect plugin conflicts
	 *
	 * @return array List of conflicts
	 */
	private static function detect_conflicts(): array {
		$conflicts = array();

		// Known conflicting plugin pairs
		$conflict_pairs = array(
			array( 'jetpack/jetpack.php', 'vaultpress/vaultpress.php' ),
			array( 'yoast-seo/wp-seo.php', 'rank-math/rank-math.php' ),
			array( 'elementor/elementor.php', 'beaver-builder-lite-version/fl-builder.php' ),
			array( 'wpforms-lite/wpforms.php', 'ninja-forms/ninja-forms.php' ),
			array( 'wordfence/wordfence.php', 'sucuri-scanner/sucuri.php' ),
		);

		$active_plugins = array_map( 'strtolower', get_option( 'active_plugins', array() ) );

		foreach ( $conflict_pairs as $pair ) {
			$pair_lower     = array_map( 'strtolower', $pair );
			$active_in_pair = array_intersect( $pair_lower, $active_plugins );

			if ( count( $active_in_pair ) > 1 ) {
				$conflicts[] = array(
					'type'    => 'Known conflict pair',
					'plugins' => array_values( $active_in_pair ),
				);
			}
		}

		return $conflicts;
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Plugin Conflicts';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Detects common plugin incompatibilities and conflicts';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Compatibility';
	}
}
