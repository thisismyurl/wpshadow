<?php
/**
 * Plugin Conflict Detection Diagnostic
 *
 * Detects potential conflicts between active plugins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1331
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
 * Scans active plugins for duplicate function/class declarations.
 *
 * @since 1.5049.1331
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
	protected static $description = 'Checks for conflicts between active plugins';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1331
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active_plugins = get_option( 'active_plugins', array() );
		$all_plugins = get_plugins();

		$function_map = array();
		$class_map = array();
		$conflicts = array();

		foreach ( $active_plugins as $plugin_file ) {
			$path = WP_PLUGIN_DIR . '/' . $plugin_file;
			if ( ! file_exists( $path ) ) {
				continue;
			}

			// Read only the first 50KB for performance.
			$content = file_get_contents( $path, false, null, 0, 50000 );
			if ( false === $content ) {
				continue;
			}

			// Match function declarations.
			if ( preg_match_all( '/function\s+([a-zA-Z0-9_]+)\s*\(/', $content, $matches ) ) {
				foreach ( $matches[1] as $function_name ) {
					$function_name = strtolower( $function_name );
					if ( isset( $function_map[ $function_name ] ) ) {
						$conflicts[] = array(
							'type'     => 'function',
							'name'     => $function_name,
							'plugins'  => array( $function_map[ $function_name ], $plugin_file ),
						);
					} else {
						$function_map[ $function_name ] = $plugin_file;
					}
				}
			}

			// Match class declarations.
			if ( preg_match_all( '/class\s+([a-zA-Z0-9_]+)\s*/', $content, $matches ) ) {
				foreach ( $matches[1] as $class_name ) {
					$class_name = strtolower( $class_name );
					if ( isset( $class_map[ $class_name ] ) ) {
						$conflicts[] = array(
							'type'     => 'class',
							'name'     => $class_name,
							'plugins'  => array( $class_map[ $class_name ], $plugin_file ),
						);
					} else {
						$class_map[ $class_name ] = $plugin_file;
					}
				}
			}

			if ( count( $conflicts ) > 20 ) {
				break;
			}
		}

		if ( ! empty( $conflicts ) ) {
			$formatted = array();
			foreach ( $conflicts as $conflict ) {
				$formatted[] = array(
					'type'    => $conflict['type'],
					'name'    => $conflict['name'],
					'plugins' => array_map(
						function( $plugin_file ) use ( $all_plugins ) {
							return $all_plugins[ $plugin_file ]['Name'] ?? $plugin_file;
						},
						$conflict['plugins']
					),
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Potential conflicts detected between active plugins', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'details'      => array(
					'conflicts' => array_slice( $formatted, 0, 10 ),
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-conflict-detection',
			);
		}

		return null;
	}
}
