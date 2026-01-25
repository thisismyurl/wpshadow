<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Discouraged Plugin Directory
 *
 * Detects plugins installed in wp-content/plugins directory that shouldn't be there (MU plugins, etc).
 * Some plugins are meant for different locations and may conflict or cause issues.
 *
 * @since 1.2.0
 */
class Test_Discouraged_Plugin_Directory extends Diagnostic_Base {


	/**
	 * Check for plugins in wrong directory
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$issues = self::find_misplaced_plugins();

		if ( empty( $issues ) ) {
			return null;
		}

		$threat = count( $issues ) * 8;
		$threat = min( 50, $threat );

		return array(
			'threat_level'  => $threat,
			'threat_color'  => 'yellow',
			'passed'        => false,
			'issue'         => sprintf(
				'Found %d plugins that may be in wrong location',
				count( $issues )
			),
			'metadata'      => array(
				'misplaced_count' => count( $issues ),
				'plugins'         => array_slice( $issues, 0, 5 ),
			),
			'kb_link'       => 'https://wpshadow.com/kb/plugin-directory-structure/',
			'training_link' => 'https://wpshadow.com/training/wordpress-plugin-management/',
		);
	}

	/**
	 * Guardian Sub-Test: Plugin directory structure
	 *
	 * @return array Test result
	 */
	public static function test_plugin_directory_structure(): array {
		$plugin_dir = WP_PLUGIN_DIR;
		$plugins    = get_plugins();
		$subdirs    = count( array_filter( array_keys( $plugins ), fn( $p ) => strpos( $p, '/' ) !== false ) );

		return array(
			'test_name'        => 'Plugin Directory Structure',
			'plugin_directory' => $plugin_dir,
			'total_plugins'    => count( $plugins ),
			'nested_plugins'   => $subdirs,
			'description'      => sprintf( '%d plugins in standard directory', count( $plugins ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Must-use plugins
	 *
	 * @return array Test result
	 */
	public static function test_mu_plugins(): array {
		$mu_dir     = WPMU_PLUGIN_DIR;
		$mu_plugins = array();

		if ( is_dir( $mu_dir ) ) {
			$files = glob( $mu_dir . '/*.php' );
			if ( $files ) {
				foreach ( $files as $file ) {
					$mu_plugins[] = basename( $file );
				}
			}
		}

		return array(
			'test_name'       => 'Must-Use Plugins',
			'mu_plugin_dir'   => $mu_dir,
			'mu_plugin_count' => count( $mu_plugins ),
			'mu_plugins'      => $mu_plugins,
			'description'     => sprintf( 'Found %d must-use plugins', count( $mu_plugins ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Misplaced plugins list
	 *
	 * @return array Test result
	 */
	public static function test_misplaced_plugins(): array {
		$issues = self::find_misplaced_plugins();

		return array(
			'test_name'         => 'Misplaced Plugins',
			'misplaced_count'   => count( $issues ),
			'misplaced_plugins' => $issues,
			'passed'            => empty( $issues ),
			'description'       => empty( $issues ) ? 'All plugins in correct location' : sprintf( 'Found %d plugins in wrong location', count( $issues ) ),
		);
	}

	/**
	 * Find plugins in wrong directories
	 *
	 * @return array Misplaced plugins
	 */
	private static function find_misplaced_plugins(): array {
		$issues = array();

		// Known directories where plugins should NOT be
		$wrong_locations = array(
			WP_CONTENT_DIR          => 'wp-content root (should be in /plugins)',
			ABSPATH . 'wp-admin'    => 'wp-admin (should be in /wp-content/plugins)',
			ABSPATH . 'wp-includes' => 'wp-includes (should be in /wp-content/plugins)',
		);

		foreach ( $wrong_locations as $dir => $description ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			$files = glob( $dir . '/*.php' );
			if ( ! $files ) {
				continue;
			}

			foreach ( $files as $file ) {
				$headers = get_file_data( $file, array( 'Plugin Name' => 'Plugin Name' ) );
				if ( ! empty( $headers['Plugin Name'] ) ) {
					$issues[] = array(
						'file'        => basename( $file ),
						'path'        => $file,
						'location'    => $description,
						'plugin_name' => $headers['Plugin Name'],
					);
				}
			}
		}

		return $issues;
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Discouraged Plugin Directory';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Identifies plugins in non-standard directories that may cause issues';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Configuration';
	}
}
