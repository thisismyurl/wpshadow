<?php
/**
 * Plugin Using Deprecated Functions Diagnostic
 *
 * Detects plugins using deprecated WordPress functions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1315
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Using Deprecated Functions Diagnostic Class
 *
 * Scans plugins for use of deprecated WordPress functions.
 *
 * @since 1.5049.1315
 */
class Diagnostic_Plugin_Using_Deprecated_Functions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-using-deprecated-functions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Using Deprecated Functions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for deprecated function usage in plugins';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1315
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$plugins_with_deprecated = array();

		// Common deprecated functions to check.
		$deprecated_functions = array(
			'wp_specialchars'          => '2.8.0',
			'attribute_escape'         => '2.8.0',
			'get_bloginfo("url")'      => '2.2.0',
			'get_settings'             => '2.1.0',
			'user_pass_ok'             => '2.5.0',
			'get_usernumwposts'        => '3.0.0',
			'get_theme_data'           => '3.4.0',
			'get_themes'               => '3.4.0',
			'get_current_theme'        => '3.4.0',
			'clean_url'                => '3.0.0',
			'sanitize_url'             => '3.0.0',
			'wp_filter_kses'           => '3.0.0',
			'wp_filter_post_kses'      => '3.0.0',
			'wp_filter_nohtml_kses'    => '3.0.0',
		);

		// Get all active plugins.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		// Check active plugins only (checking all would be too slow).
		foreach ( $active_plugins as $plugin_file ) {
			if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
				continue;
			}

			$plugin_content = file_get_contents( WP_PLUGIN_DIR . '/' . $plugin_file, false, null, 0, 50000 ); // Read first 50KB.
			$found_deprecated = array();

			foreach ( $deprecated_functions as $function => $since_version ) {
				if ( stripos( $plugin_content, $function ) !== false ) {
					$found_deprecated[] = array(
						'function' => $function,
						'deprecated_since' => $since_version,
					);
				}
			}

			if ( ! empty( $found_deprecated ) ) {
				$plugin_data = isset( $all_plugins[ $plugin_file ] ) ? $all_plugins[ $plugin_file ] : array();
				$plugins_with_deprecated[] = array(
					'plugin'             => $plugin_data['Name'] ?? $plugin_file,
					'version'            => $plugin_data['Version'] ?? 'unknown',
					'deprecated_count'   => count( $found_deprecated ),
					'deprecated_functions' => array_slice( $found_deprecated, 0, 5 ),
				);
			}

			// Limit to checking 20 plugins for performance.
			if ( count( $plugins_with_deprecated ) > 20 ) {
				break;
			}
		}

		if ( ! empty( $plugins_with_deprecated ) ) {
			$total_deprecated = array_sum( array_column( $plugins_with_deprecated, 'deprecated_count' ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: number of plugins, 2: number of deprecated functions */
					__( '%1$d plugins using %2$d deprecated WordPress functions', 'wpshadow' ),
					count( $plugins_with_deprecated ),
					$total_deprecated
				),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'details'     => array(
					'plugins'            => array_slice( $plugins_with_deprecated, 0, 10 ),
					'total_plugins'      => count( $plugins_with_deprecated ),
					'total_deprecated'   => $total_deprecated,
				),
				'kb_link'     => 'https://wpshadow.com/kb/plugin-using-deprecated-functions',
			);
		}

		return null;
	}
}
