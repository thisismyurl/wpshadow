<?php
/**
 * Diagnostic: Memcached Connectivity
 *
 * Checks if Memcached server is configured and accessible.
 * Memcached is a high-performance distributed memory caching system.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Infrastructure
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Memcached_Connectivity
 *
 * Tests Memcached server connectivity.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Memcached_Connectivity extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'memcached-connectivity';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Memcached Connectivity';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Memcached server is configured and accessible';

	/**
	 * Check Memcached connectivity.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if Memcached extension is loaded.
		if ( ! class_exists( 'Memcached' ) ) {
			return null; // Not applicable if Memcached not available.
		}

		// Check if object cache is using Memcached.
		$using_memcached = false;

		// Check for object-cache.php drop-in.
		$object_cache_file = WP_CONTENT_DIR . '/object-cache.php';
		if ( file_exists( $object_cache_file ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$object_cache_contents = file_get_contents( $object_cache_file );
			if ( false !== strpos( $object_cache_contents, 'Memcached' ) ) {
				$using_memcached = true;
			}
		}

		if ( ! $using_memcached ) {
			return null; // Not using Memcached, no need to check connectivity.
		}

		// Test Memcached connectivity.
		try {
			$memcached = new \Memcached();

			// Get Memcached server configuration.
			$servers = array();

			// Check for defined constants.
			if ( defined( 'MEMCACHED_SERVERS' ) ) {
				$servers = unserialize( MEMCACHED_SERVERS ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
			} else {
				// Default server.
				$servers = array( array( '127.0.0.1', 11211 ) );
			}

			// Add servers.
			foreach ( $servers as $server ) {
				$host = $server[0] ?? '127.0.0.1';
				$port = $server[1] ?? 11211;
				$memcached->addServer( $host, $port );
			}

			// Test connectivity with a set/get operation.
			$test_key   = 'wpshadow_memcached_test_' . wp_rand();
			$test_value = 'test_' . time();

			$set_result = $memcached->set( $test_key, $test_value, 10 );
			$get_result = $memcached->get( $test_key );

			// Clean up test key.
			$memcached->delete( $test_key );

			if ( ! $set_result || $get_result !== $test_value ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'Memcached server is not responding correctly. Cache operations may be failing.', 'wpshadow' ),
					'severity'    => 'medium',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/memcached_connectivity',
					'meta'        => array(
						'servers'    => $servers,
						'set_result' => $set_result,
						'get_result' => $get_result,
					),
				);
			}

		} catch ( \Exception $e ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Exception message */
					__( 'Memcached connectivity test failed: %s', 'wpshadow' ),
					$e->getMessage()
				),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/memcached_connectivity',
				'meta'        => array(
					'exception' => $e->getMessage(),
				),
			);
		}

		// Memcached is connected and functional.
		return null;
	}
}
