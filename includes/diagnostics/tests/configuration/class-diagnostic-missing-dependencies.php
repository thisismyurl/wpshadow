<?php
/**
 * Diagnostic: Missing Plugin Dependencies
 *
 * Scans for plugins that depend on other inactive plugins.
 * Missing dependencies can cause fatal errors or broken functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Missing_Dependencies
 *
 * Detects plugins with missing dependencies.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Missing_Dependencies extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'missing-dependencies';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Missing Plugin Dependencies';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Scans for plugins with missing dependencies';

	/**
	 * Check for missing plugin dependencies.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		$missing_deps   = array();

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			// Only check active plugins.
			if ( ! in_array( $plugin_file, $active_plugins, true ) ) {
				continue;
			}

			// Check for Requires Plugins header (WordPress 6.5+).
			if ( ! empty( $plugin_data['RequiresPlugins'] ) ) {
				$required_plugins = array_map( 'trim', explode( ',', $plugin_data['RequiresPlugins'] ) );

				foreach ( $required_plugins as $required_slug ) {
					// Check if required plugin is active.
					$required_active = false;

					foreach ( $active_plugins as $active_plugin_file ) {
						if ( strpos( $active_plugin_file, $required_slug . '/' ) === 0 ) {
							$required_active = true;
							break;
						}
					}

					if ( ! $required_active ) {
						$missing_deps[] = sprintf(
							/* translators: 1: Plugin name, 2: Required plugin slug */
							__( '%1$s requires %2$s', 'wpshadow' ),
							$plugin_data['Name'],
							$required_slug
						);
					}
				}
			}

			// Check common dependency patterns in plugin description.
			$description = $plugin_data['Description'] ?? '';

			// Pattern: "Requires [PluginName]" or "Depends on [PluginName]".
			if ( preg_match( '/(requires|depends on)\s+([A-Z][A-Za-z\s]+)/i', $description, $matches ) ) {
				$dependency_name = trim( $matches[2] );

				// Check if mentioned plugin is active.
				$dependency_active = false;
				foreach ( $all_plugins as $check_file => $check_data ) {
					if ( stripos( $check_data['Name'], $dependency_name ) !== false && in_array( $check_file, $active_plugins, true ) ) {
						$dependency_active = true;
						break;
					}
				}

				if ( ! $dependency_active ) {
					$missing_deps[] = sprintf(
						/* translators: 1: Plugin name, 2: Dependency name */
						__( '%1$s may require %2$s (check plugin documentation)', 'wpshadow' ),
						$plugin_data['Name'],
						$dependency_name
					);
				}
			}
		}

		// Remove duplicates.
		$missing_deps = array_unique( $missing_deps );

		if ( ! empty( $missing_deps ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Number of missing dependencies */
					_n(
						'%d plugin has missing dependencies',
						'%d plugins have missing dependencies',
						count( $missing_deps ),
						'wpshadow'
					),
					count( $missing_deps )
				),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/missing_dependencies',
				'meta'        => array(
					'missing_dependencies' => $missing_deps,
				),
			);
		}

		// No missing dependencies detected.
		return null;
	}
}
