<?php
/**
 * Diagnostic: LiteSpeed Cache Configuration
 *
 * Detects LiteSpeed cache plugin availability and configuration status.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Litespeed_Cache_Config
 *
 * Checks if LiteSpeed web server is detected and whether the LiteSpeed Cache
 * plugin is properly configured for optimal performance.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Litespeed_Cache_Config extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'litespeed-cache-config';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'LiteSpeed Cache Configuration';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect LiteSpeed cache plugin availability and configuration status';

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies LiteSpeed server is present and cache plugin is configured.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if misconfigured, null otherwise.
	 */
	public static function check() {
		// Check if LiteSpeed server
		if ( ! isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return null;
		}

		$server_software = sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) );
		
		if ( false === stripos( $server_software, 'litespeed' ) ) {
			// Not LiteSpeed server
			return null;
		}

		// LiteSpeed detected - check for cache plugin
		$litespeed_plugin = 'litespeed-cache/litespeed-cache.php';
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active( $litespeed_plugin ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'LiteSpeed web server detected, but LiteSpeed Cache plugin is not active. This free plugin dramatically improves performance on LiteSpeed hosting. Installing and configuring it is highly recommended.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/server-litespeed-cache-config',
				'meta'        => array(
					'server_software' => $server_software,
					'plugin_installed' => false,
				),
			);
		}

		// Plugin is active - check configuration
		$cache_enabled = get_option( 'litespeed.conf.cache', false );
		
		if ( ! $cache_enabled ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'LiteSpeed Cache plugin is installed but caching is not enabled. Enable caching in the LiteSpeed Cache settings to see performance improvements.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/server-litespeed-cache-config',
				'meta'        => array(
					'server_software' => $server_software,
					'plugin_installed' => true,
					'cache_enabled' => false,
				),
			);
		}

		// All good - LiteSpeed with cache enabled
		return null;
	}
}
