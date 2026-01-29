<?php
/**
 * Object Cache Drop-In Detection and Health Diagnostic
 *
 * Checks if object cache is installed (Redis/Memcached) and functioning.
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
 * Object Cache Drop-In Detection and Health Class
 *
 * Tests object cache setup.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Object_Cache_Drop_In_Detection_And_Health extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'object-cache-drop-in-detection-and-health';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Object Cache Drop-In Detection and Health';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if object cache is installed (Redis/Memcached) and functioning';

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
		$cache_check = self::check_object_cache();
		
		if ( ! $cache_check['cache_enabled'] || ! $cache_check['cache_functional'] ) {
			$description = '';
			
			if ( ! $cache_check['cache_enabled'] ) {
				$description = __( 'No object cache detected (missing 70-90% database query reduction)', 'wpshadow' );
			} elseif ( ! $cache_check['cache_functional'] ) {
				$description = __( 'Object cache installed but not functional (backend not connected)', 'wpshadow' );
			}
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/object-cache-drop-in-detection-and-health',
				'meta'         => array(
					'cache_enabled'     => $cache_check['cache_enabled'],
					'cache_functional'  => $cache_check['cache_functional'],
					'cache_type'        => $cache_check['cache_type'],
					'wp_cache_constant' => $cache_check['wp_cache_constant'],
				),
			);
		}

		return null;
	}

	/**
	 * Check object cache status.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_object_cache() {
		$check = array(
			'cache_enabled'     => false,
			'cache_functional'  => false,
			'cache_type'        => 'none',
			'wp_cache_constant' => defined( 'WP_CACHE' ) && WP_CACHE,
		);

		// Check for object-cache.php drop-in.
		$object_cache_file = WP_CONTENT_DIR . '/object-cache.php';
		
		if ( file_exists( $object_cache_file ) ) {
			$check['cache_enabled'] = true;

			// Read file to detect cache type.
			$cache_content = file_get_contents( $object_cache_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			if ( false !== strpos( $cache_content, 'Redis' ) || false !== strpos( $cache_content, 'redis' ) ) {
				$check['cache_type'] = 'redis';
			} elseif ( false !== strpos( $cache_content, 'Memcached' ) || false !== strpos( $cache_content, 'memcached' ) ) {
				$check['cache_type'] = 'memcached';
			} elseif ( false !== strpos( $cache_content, 'APCu' ) || false !== strpos( $cache_content, 'apcu' ) ) {
				$check['cache_type'] = 'apcu';
			}

			// Test if cache is actually working.
			$test_key = 'wpshadow_cache_test_' . time();
			$test_value = 'test_' . wp_rand( 1000, 9999 );

			wp_cache_set( $test_key, $test_value, 'wpshadow_test', 300 );
			$retrieved = wp_cache_get( $test_key, 'wpshadow_test' );

			if ( $retrieved === $test_value ) {
				$check['cache_functional'] = true;
			}

			// Clean up test.
			wp_cache_delete( $test_key, 'wpshadow_test' );
		}

		return $check;
	}
}
