<?php
declare(strict_types=1);

namespace WPShadow\Guardian;

/**
 * A/B Test Overhead Analyzer
 *
 * Monitors A/B testing tools overhead and performance impact.
 * Identifies slow experimentation platforms affecting user experience.
 *
 * Philosophy: Show value (#9) - Balance testing with performance.
 *
 * @package WPShadow
 * @subpackage Guardian
 * @since 1.2601.2200
 */
class AB_Test_Overhead_Analyzer {

	/**
	 * Known A/B testing domains
	 *
	 * @var array
	 */
	private static $ab_test_domains = array(
		'optimizely.com',
		'cdn.optimizely.com',
		'googleoptimize.com',
		'vwo.com',
		'cdn.vwo.com',
		'abtasty.com',
		'cdn.abtasty.com',
		'convert.com',
		'cdn.convert.com',
		'adobe.com/target',
		'kameleoon.com',
		'cdn.kameleoon.com',
		'ab-tasty.com',
		'unbounce.com',
		'instapage.com',
	);

	/**
	 * Analyze A/B test overhead
	 *
	 * @return array Analysis results
	 */
	public static function analyze(): array {
		// Check cache first (hourly)
		$cached = \WPShadow\Core\Cache_Manager::get(
			'ab_test_overhead',
			'wpshadow_guardian'
		);
		if ( $cached && is_array( $cached ) ) {
			return $cached;
		}

		$results = array(
			'has_ab_testing'        => false,
			'test_platforms'        => array(),
			'total_scripts'         => 0,
			'estimated_overhead_ms' => 0,
			'is_blocking'           => false,
			'tests_detected'        => array(),
		);

		// Get enqueued scripts
		global $wp_scripts;

		if ( ! isset( $wp_scripts ) || ! ( $wp_scripts instanceof \WP_Scripts ) ) {
			\WPShadow\Core\Cache_Manager::set( 'ab_test_overhead', $results, HOUR_IN_SECONDS , 'wpshadow_guardian');
			return $results;
		}

		// Find A/B test scripts
		$test_scripts = array();
		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( ! is_string( $script->src ) || empty( $script->src ) ) {
				continue;
			}

			// Check if script is from an A/B test platform
			$is_test_platform = false;
			$platform         = '';

			foreach ( self::$ab_test_domains as $domain ) {
				if ( strpos( $script->src, $domain ) !== false ) {
					$is_test_platform = true;
					$platform         = $domain;
					break;
				}
			}

			if ( $is_test_platform ) {
				$test_scripts[] = array(
					'handle'    => $handle,
					'platform'  => $platform,
					'src'       => $script->src,
					'in_footer' => ! empty( $script->extra['group'] ) && $script->extra['group'] === 1,
				);
			}
		}

		if ( ! empty( $test_scripts ) ) {
			$results['has_ab_testing'] = true;
			$results['total_scripts']  = count( $test_scripts );

			// Group by platform
			$by_platform    = array();
			$blocking_count = 0;

			foreach ( $test_scripts as $script ) {
				$platform = $script['platform'];
				if ( ! isset( $by_platform[ $platform ] ) ) {
					$by_platform[ $platform ] = array(
						'scripts'      => array(),
						'count'        => 0,
						'has_blocking' => false,
					);
				}
				$by_platform[ $platform ]['scripts'][] = $script['handle'];
				++$by_platform[ $platform ]['count'];

				// Check if blocking (in head)
				if ( ! $script['in_footer'] ) {
					$by_platform[ $platform ]['has_blocking'] = true;
					++$blocking_count;
				}
			}

			$results['test_platforms'] = $by_platform;
			$results['is_blocking']    = $blocking_count > 0;

			// Estimate overhead (rough: 300ms per platform + 100ms per additional script)
			$platform_count                   = count( $by_platform );
			$results['estimated_overhead_ms'] = ( $platform_count * 300 ) +
												( ( $results['total_scripts'] - $platform_count ) * 100 );
		}

		// Check for active tests in options/transients
		$results['tests_detected'] = self::detect_active_tests();

		// Cache for 1 hour
		\WPShadow\Core\Cache_Manager::set(
			'ab_test_overhead',
			$results,
			HOUR_IN_SECONDS,
			'wpshadow_guardian'
			);

		return $results;
	}

	/**
	 * Detect active A/B tests
	 *
	 * @return array Active tests
	 */
	private static function detect_active_tests(): array {
		$tests = array();

		// Check for Optimizely experiments
		if ( get_option( 'optimizely_project_id' ) ) {
			$tests[] = array(
				'platform' => 'Optimizely',
				'status'   => 'configured',
			);
		}

		// Check for Google Optimize container
		if ( get_option( 'google_optimize_container_id' ) ) {
			$tests[] = array(
				'platform' => 'Google Optimize',
				'status'   => 'configured',
			);
		}

		// Check for VWO account
		if ( get_option( 'vwo_account_id' ) ) {
			$tests[] = array(
				'platform' => 'VWO',
				'status'   => 'configured',
			);
		}

		// Check for AB Tasty
		if ( get_option( 'abtasty_account_id' ) ) {
			$tests[] = array(
				'platform' => 'AB Tasty',
				'status'   => 'configured',
			);
		}

		return $tests;
	}

	/**
	 * Get summary
	 *
	 * @return array Summary data
	 */
	public static function get_summary(): array {
		$results = \WPShadow\Core\Cache_Manager::get( 'ab_test_overhead', 'wpshadow_guardian' );
		return is_array( $results ) ? $results : array(
			'has_ab_testing'        => false,
			'test_platforms'        => array(),
			'estimated_overhead_ms' => 0,
			'is_blocking'           => false,
		);
	}

	/**
	 * Clear cached data
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		\WPShadow\Core\Cache_Manager::delete( 'ab_test_overhead', 'wpshadow_guardian' );
	}
}
