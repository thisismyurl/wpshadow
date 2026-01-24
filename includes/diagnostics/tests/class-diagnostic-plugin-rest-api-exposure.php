<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


/**
 * ⚠️ STUB - NEEDS IMPLEMENTATION
 *
 * This diagnostic is a placeholder with stub implementation (if !false pattern).
 * Before writing tests, we need to clarify:
 *
 * 1. What is the actual diagnostic question/goal?
 * 2. What WordPress state indicates pass/fail?
 * 3. Are there specific plugins, options, or settings to check?
 * 4. What should trigger an issue vs pass?
 * 5. What is the threat/priority level?
 *
 * Once clarified, implement the check() method and we can create the test.
 */

/**
 * WORDPRESS STATE CHECK - READY FOR IMPLEMENTATION
 * ================================================
 *
 * Question: Are plugins exposing private data via REST?
 * Slug: plugin-rest-api-exposure
 * Category: WordPress Ecosystem Health
 *
 * This diagnostic checks WordPress configuration and state.
 *
 * IMPLEMENTATION PATTERN:
 *
 * public static function check(): ?array {
 *     // Check WordPress state/options
 *     // Examples:
 *
 *     // 1. Check option value
 *     $option_value = get_option('option_key');
 *     if ($option_value !== 'expected_value') {
 *         return array(
 *             'finding_id' => self::$slug,
 *             'title' => self::$title,
 *             'description' => 'Description of issue',
 *             'severity' => 'high',
 *             'threat_level' => 75
 *         );
 *     }
 *
 *     // 2. Check plugin status
 *     if (!is_plugin_active('plugin-name/plugin.php')) {
 *         return array(/* finding */);
 *     }
 *
 *     // 3. Check WordPress constant
 *     if (!defined('WP_AUTO_UPDATE_CORE') || !WP_AUTO_UPDATE_CORE) {
 *         return array(/* finding */);
 *     }
 *
 *     // 4. Check database value
 *     $blog_public = get_option('blog_public');
 *
 *     // Return null if all checks pass
 *     return null;
 * }
 *
 * NEXT STEPS:
 * 1. Identify the exact WordPress option/setting to check
 * 2. Determine what values indicate pass vs fail
 * 3. Implement check() method with logic
 * 4. Add unit test with mocked WordPress options
 * 5. Add integration test on real WordPress
 *
 * WORDPRESS FUNCTIONS TO USE:
 * - get_option() - Query WordPress options
 * - get_site_option() - Query network options
 * - is_plugin_active() - Check plugin status
 * - defined() / constant() - Check PHP constants
 * - wp_cache_get() / wp_cache_set() - Use caching
 * - WP_Query, get_posts() - Query posts/pages
 * - get_users() - Query user data
 *
 * Current Status: READY FOR IMPLEMENTATION
 */
class Diagnostic_Plugin_Rest_Api_Exposure extends Diagnostic_Base {
	protected static $slug = 'plugin-rest-api-exposure';

	protected static $title = 'Plugin Rest Api Exposure';

	protected static $description = 'Automatically initialized lean diagnostic for Plugin Rest Api Exposure. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'plugin-rest-api-exposure';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Are plugins exposing private data via REST?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Are plugins exposing private data via REST?. Part of WordPress Ecosystem Health analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'wordpress_ecosystem';
	}

	/**
	 * Run the diagnostic test
	 *
	 * @return array Finding data or empty if no issue
	 */
	public static function run(): array {
		// Implement: Are plugins exposing private data via REST? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 50;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/plugin-rest-api-exposure/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/plugin-rest-api-exposure/';
	}

	public static function check(): ?array {
		// Check if plugin REST endpoints are exposing data that should be private
		// Simple heuristic: check if REST API is disabled or has proper auth
		global $wp_rest_server;

		if ( ! $wp_rest_server ) {
			$wp_rest_server = rest_get_server();
		}

		// Check if REST API shows user data publicly
		$user_route = '/wp/v2/users';
		$routes = $wp_rest_server->get_routes();

		$has_public_user_exposure = false;
		if ( isset( $routes[ $user_route ] ) ) {
			$route_obj = $routes[ $user_route ][0];
			if ( isset( $route_obj['permission_callback'] ) ) {
				// If permission callback is empty or '__return_true', it's public
				$permission = $route_obj['permission_callback'];
				if ( ! $permission || $permission === '__return_true' ) {
					$has_public_user_exposure = true;
				}
			}
		}

		if ( $has_public_user_exposure ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'plugin-rest-api-exposure',
				'Plugin Rest Api Exposure',
				'REST API endpoints may be exposing sensitive user data publicly. Ensure proper permission callbacks are set on all REST routes.',
				'security',
				'medium',
				55,
				'plugin-rest-api-exposure'
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Plugin Rest Api Exposure
	 * Slug: plugin-rest-api-exposure
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Plugin Rest Api Exposure. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_plugin_rest_api_exposure(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */

		$result = self::check();

		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}

}

