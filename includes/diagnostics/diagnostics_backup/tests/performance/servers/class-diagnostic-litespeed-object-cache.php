<?php
/**
 * Diagnostic: LiteSpeed Object Cache for WordPress
 *
 * Detects if LiteSpeed Object Cache is configured for WordPress transients.
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
 * Class Diagnostic_Litespeed_Object_Cache
 *
 * Checks if persistent object cache is configured, which provides
 * significant performance benefits for high-traffic WordPress sites.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Litespeed_Object_Cache extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'litespeed-object-cache';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'LiteSpeed Object Cache for WordPress';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect if LiteSpeed Object Cache is configured for WordPress transients';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests if persistent object cache is available and functioning.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if not configured, null otherwise.
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

		// Check if object cache is available
		if ( ! wp_using_ext_object_cache() ) {
			// No persistent object cache
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'LiteSpeed server detected, but persistent object cache is not configured. Object cache stores frequently-accessed data in memory for faster retrieval. This is an advanced optimization recommended for high-traffic sites.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/server-litespeed-object-cache',
				'meta'        => array(
					'object_cache_enabled' => false,
					'using_persistent_cache' => false,
				),
			);
		}

		// Object cache is enabled - verify it's actually working
		$test_key = 'wpshadow_object_cache_test_' . time();
		$test_value = 'test_value_' . wp_generate_password( 12, false );
		
		wp_cache_set( $test_key, $test_value, 'wpshadow_test', 60 );
		$retrieved_value = wp_cache_get( $test_key, 'wpshadow_test' );
		
		if ( $retrieved_value !== $test_value ) {
			// Object cache not functioning properly
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Object cache appears to be configured but is not responding correctly. Verify your object cache configuration (Redis, Memcached, or LiteSpeed LSCache) is working properly.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/server-litespeed-object-cache',
				'meta'        => array(
					'object_cache_enabled' => true,
					'using_persistent_cache' => true,
					'test_passed' => false,
				),
			);
		}

		// Clean up test data
		wp_cache_delete( $test_key, 'wpshadow_test' );

		// Object cache is working correctly
		return null;
	}
}
