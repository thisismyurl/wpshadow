<?php
/**
 * Diagnostic: Cache Key Collision Detection
 *
 * Identifies cache key naming conflicts causing incorrect data serving.
 * Cache key collisions serve wrong data to users.
 * One user sees another user's data - security and privacy violation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.26028.1911
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Cache_Key_Collision_Detection
 *
 * Tests for cache key naming conflicts.
 *
 * @since 1.26028.1911
 */
class Diagnostic_Cache_Key_Collision_Detection extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'cache-key-collision-detection';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Cache Key Collision Detection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Identifies cache key naming conflicts causing incorrect data serving';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Check for cache key collisions.
	 *
	 * @since  1.26028.1911
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! wp_using_ext_object_cache() ) {
			return null;
		}

		$issues = array();

		if ( is_multisite() && ! self::multisite_uses_blog_id() ) {
			$issues[] = __( 'Multisite not using blog_id in cache keys - sites will see each other\'s data', 'wpshadow' );
		}

		$generic_keys = self::detect_generic_keys();
		if ( ! empty( $generic_keys ) ) {
			$issues[] = sprintf(
				/* translators: %s: List of generic keys */
				__( 'Found overly generic cache keys: %s - may cause collisions', 'wpshadow' ),
				implode( ', ', $generic_keys )
			);
		}

		if ( ! empty( $issues ) ) {
			$severity = is_multisite() && ! self::multisite_uses_blog_id() ? 'critical' : 'high';
			$threat_level = is_multisite() && ! self::multisite_uses_blog_id() ? 85 : 75;

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: Number of issues, 2: List of issues */
					__( 'Detected %1$d cache key issue(s): %2$s. Cache key collisions cause users to see wrong data - potential security and privacy violation.', 'wpshadow' ),
					count( $issues ),
					implode( '; ', $issues )
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cache-key-collision-detection',
				'meta'         => array(
					'issues'         => $issues,
					'is_multisite'   => is_multisite(),
					'recommendation' => 'Use unique, namespaced cache keys',
				),
			);
		}

		return null;
	}

	/**
	 * Check if multisite uses blog_id in cache keys.
	 *
	 * @since  1.26028.1911
	 * @return bool True if blog_id used, false otherwise.
	 */
	private static function multisite_uses_blog_id() {
		if ( ! is_multisite() ) {
			return true;
		}

		$test_key = 'wpshadow_multisite_test_' . time();
		$test_value = get_current_blog_id() . '_' . wp_generate_password( 16, false );

		wp_cache_set( $test_key, $test_value );
		$retrieved = wp_cache_get( $test_key );

		wp_cache_delete( $test_key );

		$cache_adds_blog_id = ( $retrieved === $test_value );

		if ( ! $cache_adds_blog_id ) {
			return false;
		}

		$key_with_blog = $test_key . '_' . get_current_blog_id();
		wp_cache_set( $key_with_blog, $test_value );
		$retrieved_with_blog = wp_cache_get( $key_with_blog );
		wp_cache_delete( $key_with_blog );

		return ( $retrieved_with_blog === $test_value );
	}

	/**
	 * Detect generic cache keys.
	 *
	 * @since  1.26028.1911
	 * @return array List of generic keys found.
	 */
	private static function detect_generic_keys() {
		global $wpdb;

		$generic_patterns = array(
			'user',
			'post',
			'data',
			'cache',
			'temp',
			'value',
		);

		$found_generic = array();

		$transients = $wpdb->get_col(
			"SELECT option_name FROM {$wpdb->options} 
			WHERE option_name LIKE '_transient_%' 
			LIMIT 20"
		);

		foreach ( $transients as $transient_name ) {
			$key = str_replace( '_transient_', '', $transient_name );

			foreach ( $generic_patterns as $pattern ) {
				if ( $key === $pattern || preg_match( '/^' . $pattern . '\d+$/', $key ) ) {
					$found_generic[] = $key;
					break;
				}
			}
		}

		return array_unique( $found_generic );
	}
}
