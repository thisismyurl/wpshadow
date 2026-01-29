<?php
/**
 * Cache Invalidation Logic Verification Diagnostic
 *
 * Tests if cache properly invalidates on content updates.
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
 * Cache Invalidation Logic Verification Class
 *
 * Tests cache invalidation.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Cache_Invalidation_Logic_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cache-invalidation-logic-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cache Invalidation Logic Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if cache properly invalidates on content updates';

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
		$invalidation_check = self::check_cache_invalidation();
		
		if ( $invalidation_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $invalidation_check['issues'] ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cache-invalidation-logic-verification',
				'meta'         => array(
					'object_cache_enabled' => $invalidation_check['object_cache_enabled'],
					'test_results'         => $invalidation_check['test_results'],
				),
			);
		}

		return null;
	}

	/**
	 * Check cache invalidation logic.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_cache_invalidation() {
		$check = array(
			'has_issues'           => false,
			'issues'               => array(),
			'object_cache_enabled' => false,
			'test_results'         => array(),
		);

		// Check if object cache is available.
		$object_cache_file = WP_CONTENT_DIR . '/object-cache.php';
		$check['object_cache_enabled'] = file_exists( $object_cache_file );

		if ( ! $check['object_cache_enabled'] ) {
			return $check; // No object cache to test.
		}

		// Test post cache invalidation.
		$test_key = 'wpshadow_cache_test_' . time();
		$test_value_1 = 'value_1_' . wp_rand( 1000, 9999 );
		$test_value_2 = 'value_2_' . wp_rand( 1000, 9999 );

		// Set initial cache value.
		wp_cache_set( $test_key, $test_value_1, 'posts', 3600 );

		// Verify it's cached.
		$cached = wp_cache_get( $test_key, 'posts' );
		$check['test_results']['initial_cache'] = ( $cached === $test_value_1 );

		// Update cache (simulating post update).
		wp_cache_set( $test_key, $test_value_2, 'posts', 3600 );

		// Verify it updated.
		$updated = wp_cache_get( $test_key, 'posts' );
		$check['test_results']['cache_updated'] = ( $updated === $test_value_2 );

		if ( ! $check['test_results']['cache_updated'] ) {
			$check['has_issues'] = true;
			$check['issues'][] = __( 'Cache not updating properly (stale data risk)', 'wpshadow' );
		}

		// Test cache deletion (invalidation).
		wp_cache_delete( $test_key, 'posts' );
		$deleted = wp_cache_get( $test_key, 'posts' );
		$check['test_results']['cache_deleted'] = ( false === $deleted );

		if ( ! $check['test_results']['cache_deleted'] ) {
			$check['has_issues'] = true;
			$check['issues'][] = __( 'Cache invalidation not working (old data persists)', 'wpshadow' );
		}

		// Check if WordPress hooks for cache invalidation exist.
		$invalidation_hooks = array(
			'clean_post_cache',
			'clean_term_cache',
			'clean_user_cache',
		);

		$missing_hooks = array();
		foreach ( $invalidation_hooks as $hook ) {
			if ( ! has_action( $hook ) ) {
				$missing_hooks[] = $hook;
			}
		}

		if ( ! empty( $missing_hooks ) ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %s: comma-separated list of hooks */
				__( 'Cache invalidation hooks not registered: %s', 'wpshadow' ),
				implode( ', ', $missing_hooks )
			);
		}

		return $check;
	}
}
