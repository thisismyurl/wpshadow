<?php
/**
 * Script Utility Functions
 *
 * Shared utilities for script optimization features.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

/**
 * WPS_Script_Utils
 *
 * Utility functions for script analysis and optimization.
 */
final class WPS_Script_Utils {

	/**
	 * Known plugin prefixes and their display names.
	 *
	 * @var array<string, string>
	 */
	private static array $known_plugins = array(
		'contact-form-7' => 'Contact Form 7',
		'wpcf7'          => 'Contact Form 7',
		'woocommerce'    => 'WooCommerce',
		'wc-'            => 'WooCommerce',
		'elementor'      => 'Elementor',
		'jetpack'        => 'Jetpack',
		'wpforms'        => 'WPForms',
		'gravityforms'   => 'Gravity Forms',
		'yoast'          => 'Yoast SEO',
		'wp-rocket'      => 'WP Rocket',
		'autoptimize'    => 'Autoptimize',
	);

	/**
	 * Detect plugin name from script handle.
	 *
	 * @param string $handle Script handle.
	 * @return string|null Plugin name or null if not detected.
	 */
	public static function detect_plugin_from_handle( string $handle ): ?string {
		// Check for exact matches first (most efficient).
		if ( isset( self::$known_plugins[ $handle ] ) ) {
			return self::$known_plugins[ $handle ];
		}

		// Check for prefix matches.
		foreach ( self::$known_plugins as $prefix => $name ) {
			if ( strpos( $handle, $prefix ) === 0 ) {
				return $name;
			}
		}

		return null;
	}

	/**
	 * Add a custom plugin mapping.
	 *
	 * @param string $prefix Plugin prefix or handle.
	 * @param string $name   Plugin display name.
	 * @return void
	 */
	public static function register_plugin_mapping( string $prefix, string $name ): void {
		self::$known_plugins[ $prefix ] = $name;
	}

	/**
	 * Get all known plugin mappings.
	 *
	 * @return array<string, string> Plugin mappings.
	 */
	public static function get_known_plugins(): array {
		return self::$known_plugins;
	}
}
