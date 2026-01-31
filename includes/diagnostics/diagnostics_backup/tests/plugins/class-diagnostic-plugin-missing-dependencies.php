<?php
/**
 * Plugin Missing Dependencies Diagnostic
 *
 * Detects plugins missing required PHP extensions or libraries.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1700
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Missing Dependencies Class
 *
 * Scans plugin requirements for missing PHP extensions.
 * Missing dependencies cause plugin failures and errors.
 *
 * @since 1.5029.1700
 */
class Diagnostic_Plugin_Missing_Dependencies extends Diagnostic_Base {

	protected static $slug        = 'plugin-missing-dependencies';
	protected static $title       = 'Plugin Missing Dependencies';
	protected static $description = 'Detects plugins with missing required dependencies';
	protected static $family      = 'plugins';

	public static function check() {
		$cache_key = 'wpshadow_plugin_missing_deps';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		$missing_deps = array();

		// Common PHP extensions plugins may require.
		$common_extensions = array(
			'gd' => 'Image manipulation',
			'curl' => 'HTTP requests',
			'mbstring' => 'Multibyte string support',
			'zip' => 'Archive handling',
			'xml' => 'XML processing',
			'simplexml' => 'Simple XML',
			'dom' => 'DOM manipulation',
			'json' => 'JSON encoding',
			'openssl' => 'SSL/TLS',
			'mysqli' => 'MySQL database',
		);

		foreach ( $all_plugins as $plugin_path => $plugin_data ) {
			$is_active = in_array( $plugin_path, $active_plugins, true );
			
			if ( ! $is_active ) {
				continue;
			}

			// Check plugin readme/headers for requirements.
			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin_path );
			$readme_file = $plugin_dir . '/readme.txt';
			$required_missing = array();

			if ( file_exists( $readme_file ) ) {
				$readme_content = file_get_contents( $readme_file );
				
				// Parse requirements section.
				foreach ( $common_extensions as $ext => $desc ) {
					if ( stripos( $readme_content, $ext ) !== false ) {
						if ( ! extension_loaded( $ext ) ) {
							$required_missing[] = array(
								'extension' => $ext,
								'description' => $desc,
							);
						}
					}
				}
			}

			if ( ! empty( $required_missing ) ) {
				$missing_deps[] = array(
					'name' => $plugin_data['Name'],
					'slug' => dirname( $plugin_path ),
					'missing' => $required_missing,
				);
			}
		}

		if ( ! empty( $missing_deps ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of plugins */
					__( '%d active plugins have missing dependencies. Install required PHP extensions.', 'wpshadow' ),
					count( $missing_deps )
				),
				'severity'     => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-missing-dependencies',
				'data'         => array(
					'plugins_with_missing_deps' => $missing_deps,
					'total_affected' => count( $missing_deps ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
