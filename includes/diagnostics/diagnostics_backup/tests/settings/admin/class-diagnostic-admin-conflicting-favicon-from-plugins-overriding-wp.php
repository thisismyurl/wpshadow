<?php
/**
 * Admin Conflicting Favicon from Plugins Diagnostic
 *
 * Detects when plugins are overriding the WordPress admin favicon with their own.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Conflicting Favicon from Plugins Diagnostic Class
 *
 * Identifies plugins that inject custom favicons into admin pages,
 * potentially causing conflicts or branding confusion.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Conflicting_Favicon_From_Plugins_Overriding_Wp extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-conflicting-favicon-from-plugins-overriding-wp';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Conflicting Favicon from Plugins';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when plugins override the WordPress admin favicon';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only run in admin context.
		if ( ! is_admin() ) {
			return null;
		}

		// Check for hooks that modify admin head (where favicon would be injected).
		global $wp_filter;

		$conflicting_plugins = array();

		// Check admin_head action for favicon-related callbacks.
		if ( isset( $wp_filter['admin_head'] ) ) {
			foreach ( $wp_filter['admin_head']->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback_id => $callback ) {
					// Try to get plugin/theme info from callback.
					$callback_function = null;

					if ( is_array( $callback['function'] ) && is_object( $callback['function'][0] ) ) {
						$callback_function = get_class( $callback['function'][0] ) . '::' . $callback['function'][1];
					} elseif ( is_string( $callback['function'] ) ) {
						$callback_function = $callback['function'];
					}

					// Check if callback name suggests favicon manipulation.
					if ( $callback_function && (
						stripos( $callback_function, 'favicon' ) !== false ||
						stripos( $callback_function, 'icon' ) !== false ||
						stripos( $callback_function, 'site_icon' ) !== false
					) ) {
						// Try to identify plugin.
						$plugin_info = self::identify_plugin_from_callback( $callback['function'] );
						if ( $plugin_info ) {
							$conflicting_plugins[] = $plugin_info;
						}
					}
				}
			}
		}

		// Check wp_head action as well (some plugins hook here even in admin).
		if ( isset( $wp_filter['wp_head'] ) && is_admin() ) {
			foreach ( $wp_filter['wp_head']->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback_id => $callback ) {
					$callback_function = null;

					if ( is_array( $callback['function'] ) && is_object( $callback['function'][0] ) ) {
						$callback_function = get_class( $callback['function'][0] ) . '::' . $callback['function'][1];
					} elseif ( is_string( $callback['function'] ) ) {
						$callback_function = $callback['function'];
					}

					if ( $callback_function && (
						stripos( $callback_function, 'favicon' ) !== false ||
						stripos( $callback_function, 'icon' ) !== false
					) ) {
						$plugin_info = self::identify_plugin_from_callback( $callback['function'] );
						if ( $plugin_info ) {
							$conflicting_plugins[] = $plugin_info;
						}
					}
				}
			}
		}

		// Remove duplicates.
		$conflicting_plugins = array_unique( $conflicting_plugins );

		if ( ! empty( $conflicting_plugins ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of plugins, %s: list of plugins */
					_n(
						'%d plugin is overriding the WordPress admin favicon: %s. This may cause branding confusion or conflicts.',
						'%d plugins are overriding the WordPress admin favicon: %s. This may cause branding confusion or conflicts.',
						count( $conflicting_plugins ),
						'wpshadow'
					),
					count( $conflicting_plugins ),
					implode( ', ', $conflicting_plugins )
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
				'meta'         => array(
					'plugins' => $conflicting_plugins,
				),
			);
		}

		return null; // No conflicting favicon overrides detected.
	}

	/**
	 * Identify plugin from callback function.
	 *
	 * @since  1.2601.2148
	 * @param  mixed $callback Callback function.
	 * @return string|null Plugin identifier or null.
	 */
	private static function identify_plugin_from_callback( $callback ) {
		if ( is_array( $callback ) && is_object( $callback[0] ) ) {
			$class_name = get_class( $callback[0] );
			// Extract plugin name from class namespace.
			if ( preg_match( '/^([A-Za-z_]+)/', $class_name, $matches ) ) {
				return $matches[1];
			}
			return $class_name;
		} elseif ( is_string( $callback ) ) {
			// Extract plugin name from function name.
			if ( preg_match( '/^([a-z_]+)_/', $callback, $matches ) ) {
				return $matches[1];
			}
			return $callback;
		}

		return null;
	}
}
