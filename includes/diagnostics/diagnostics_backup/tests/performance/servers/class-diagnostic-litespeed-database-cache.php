<?php
/**
 * Diagnostic: LiteSpeed Database Cache
 *
 * Detects if LiteSpeed database caching is enabled for WordPress.
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
 * Class Diagnostic_Litespeed_Database_Cache
 *
 * Checks if database caching is configured in LiteSpeed Cache plugin,
 * which can significantly reduce database load.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Litespeed_Database_Cache extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'litespeed-database-cache';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'LiteSpeed Database Cache';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect if LiteSpeed database caching is enabled for WordPress';

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies LiteSpeed Cache plugin is active and database cache is enabled.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if not enabled, null otherwise.
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

		// Check if LiteSpeed Cache plugin is active
		$litespeed_plugin = 'litespeed-cache/litespeed-cache.php';
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active( $litespeed_plugin ) ) {
			// Plugin not active - not applicable
			return null;
		}

		// Check if database cache is enabled
		$db_cache_enabled = get_option( 'litespeed.conf.cache-db', false );
		
		if ( ! $db_cache_enabled ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'LiteSpeed Cache plugin is active, but database caching is not enabled. Database cache can significantly reduce database queries and improve performance. Enable it in LiteSpeed Cache settings for better performance.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/server-litespeed-database-cache',
				'meta'        => array(
					'plugin_active' => true,
					'db_cache_enabled' => false,
				),
			);
		}

		// Database cache is enabled
		return null;
	}
}
