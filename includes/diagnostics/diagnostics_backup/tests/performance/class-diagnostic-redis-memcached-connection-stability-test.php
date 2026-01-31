<?php
/**
 * Redis/Memcached Connection Stability Test Diagnostic
 *
 * Tests object cache backend connection reliability and failover.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redis/Memcached Connection Stability Test Class
 *
 * Tests cache connection stability.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Redis_Memcached_Connection_Stability_Test extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'redis-memcached-connection-stability-test';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Redis/Memcached Connection Stability Test';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests object cache backend connection reliability and failover';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$connection_check = self::check_cache_connection();
		
		if ( $connection_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $connection_check['issues'] ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/redis-memcached-connection-stability-test',
				'meta'         => array(
					'cache_type'       => $connection_check['cache_type'],
					'connection_works' => $connection_check['connection_works'],
				),
			);
		}

		return null;
	}

	/**
	 * Check cache connection stability.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_cache_connection() {
		$check = array(
			'has_issues'       => false,
			'issues'           => array(),
			'cache_type'       => 'none',
			'connection_works' => false,
		);

		// Check if object cache is available.
		$object_cache_file = WP_CONTENT_DIR . '/object-cache.php';
		
		if ( ! file_exists( $object_cache_file ) ) {
			return $check; // No object cache.
		}

		// Detect cache type.
		$cache_content = file_get_contents( $object_cache_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( false !== strpos( $cache_content, 'Redis' ) || false !== strpos( $cache_content, 'redis' ) ) {
			$check['cache_type'] = 'redis';
		} elseif ( false !== strpos( $cache_content, 'Memcached' ) || false !== strpos( $cache_content, 'memcached' ) ) {
			$check['cache_type'] = 'memcached';
		}

		// Test connection with multiple attempts.
		$test_key = 'wpshadow_connection_test_' . time();
		$test_value = 'test_' . wp_rand( 1000, 9999 );
		$connection_attempts = 3;
		$successful_attempts = 0;

		for ( $i = 0; $i < $connection_attempts; $i++ ) {
			wp_cache_set( $test_key . '_' . $i, $test_value, 'wpshadow_test', 60 );
			$retrieved = wp_cache_get( $test_key . '_' . $i, 'wpshadow_test' );

			if ( $retrieved === $test_value ) {
				$successful_attempts++;
			}

			// Clean up.
			wp_cache_delete( $test_key . '_' . $i, 'wpshadow_test' );
		}

		// Check connection success rate.
		if ( $successful_attempts === $connection_attempts ) {
			$check['connection_works'] = true;
		} elseif ( $successful_attempts > 0 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: successful attempts, %d: total attempts */
				__( 'Cache connection unstable (%d/%d attempts successful)', 'wpshadow' ),
				$successful_attempts,
				$connection_attempts
			);
		} else {
			$check['has_issues'] = true;
			$check['issues'][] = __( 'Cache backend not responding (all connection attempts failed)', 'wpshadow' );
		}

		return $check;
	}
}
