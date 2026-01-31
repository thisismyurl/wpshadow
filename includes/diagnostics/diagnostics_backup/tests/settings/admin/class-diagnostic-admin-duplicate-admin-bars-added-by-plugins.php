<?php
/**
 * Admin Duplicate Admin Bars Added by Plugins Diagnostic
 *
 * Detects when plugins are adding duplicate admin bars to admin pages.
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
 * Admin Duplicate Admin Bars Added by Plugins Diagnostic Class
 *
 * Identifies when multiple admin bars are rendered on the same page,
 * typically due to plugin conflicts or incorrect theme/plugin implementations.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Duplicate_Admin_Bars_Added_By_Plugins extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-duplicate-admin-bars-added-by-plugins';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Admin Bars from Plugins';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when plugins add duplicate admin bars to admin pages';

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
		// Only run in admin context with admin bar showing.
		if ( ! is_admin() || ! is_admin_bar_showing() ) {
			return null;
		}

		global $wp_filter;
		$render_callbacks = array();
		$suspicious_callbacks = array();

		// Check wp_before_admin_bar_render action (where duplicate bars might be added).
		if ( isset( $wp_filter['wp_before_admin_bar_render'] ) ) {
			foreach ( $wp_filter['wp_before_admin_bar_render']->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback_id => $callback ) {
					$render_callbacks[] = array(
						'callback' => $callback['function'],
						'priority' => $priority,
					);
				}
			}
		}

		// Check wp_after_admin_bar_render as well.
		if ( isset( $wp_filter['wp_after_admin_bar_render'] ) ) {
			foreach ( $wp_filter['wp_after_admin_bar_render']->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback_id => $callback ) {
					$render_callbacks[] = array(
						'callback' => $callback['function'],
						'priority' => $priority,
					);
				}
			}
		}

		// Check for callbacks that might be creating custom admin bars.
		foreach ( $render_callbacks as $callback_info ) {
			$callback_function = self::get_callback_name( $callback_info['callback'] );

			// Look for suspicious patterns in callback names.
			if ( $callback_function && (
				stripos( $callback_function, 'admin_bar' ) !== false ||
				stripos( $callback_function, 'toolbar' ) !== false ||
				stripos( $callback_function, 'navigation' ) !== false
			) ) {
				$plugin_info = self::identify_plugin_from_callback( $callback_info['callback'] );
				if ( $plugin_info && ! in_array( $plugin_info, array( 'WordPress', 'WP_Admin_Bar' ), true ) ) {
					$suspicious_callbacks[] = $plugin_info;
				}
			}
		}

		// Also check for multiple calls to wp_admin_bar()->render().
		global $wp_admin_bar;
		if ( isset( $wp_admin_bar ) ) {
			// Check if render method has been called multiple times.
			// This is harder to detect programmatically, so we check for multiple hooks.
			$admin_bar_render_count = 0;
			if ( isset( $wp_filter['admin_bar_menu'] ) ) {
				$admin_bar_render_count = count( $wp_filter['admin_bar_menu']->callbacks );
			}

			// If there are suspiciously many callbacks, it might indicate duplication.
			if ( $admin_bar_render_count > 50 ) {
				// Too many admin bar menu items - possible duplication.
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %d: number of callbacks */
						__( 'Detected %d callbacks on admin_bar_menu action, which is unusually high. This may indicate plugins are duplicating admin bar elements or adding excessive menu items.', 'wpshadow' ),
						$admin_bar_render_count
					),
					'severity'     => 'medium',
					'threat_level' => 35,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
					'meta'         => array(
						'callback_count' => $admin_bar_render_count,
					),
				);
			}
		}

		// Check for suspicious callbacks from non-core plugins.
		$suspicious_callbacks = array_unique( $suspicious_callbacks );
		if ( count( $suspicious_callbacks ) > 1 ) {
			// Multiple plugins modifying admin bar render process.
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of plugins, %s: list of plugins */
					_n(
						'%d plugin is modifying the admin bar rendering process: %s. Multiple plugins hooking into admin bar render may cause duplication.',
						'%d plugins are modifying the admin bar rendering process: %s. Multiple plugins hooking into admin bar render may cause duplication.',
						count( $suspicious_callbacks ),
						'wpshadow'
					),
					count( $suspicious_callbacks ),
					implode( ', ', $suspicious_callbacks )
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/' . self::$slug,
				'meta'         => array(
					'plugins' => $suspicious_callbacks,
				),
			);
		}

		return null; // No duplicate admin bars detected.
	}

	/**
	 * Get callback name from callback function.
	 *
	 * @since  1.2601.2148
	 * @param  mixed $callback Callback function.
	 * @return string|null Callback name or null.
	 */
	private static function get_callback_name( $callback ) {
		if ( is_array( $callback ) && is_object( $callback[0] ) ) {
			return get_class( $callback[0] ) . '::' . $callback[1];
		} elseif ( is_string( $callback ) ) {
			return $callback;
		}

		return null;
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
