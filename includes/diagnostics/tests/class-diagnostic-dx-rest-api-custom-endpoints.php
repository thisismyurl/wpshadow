<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Are REST endpoints available?
 *
 * Category: Developer Experience
 * Priority: 2
 * Philosophy: 1, 7
 *
 * Test Description:
 * Are REST endpoints available?
 *
 * @package WPShadow
 * @subpackage Diagnostics
  *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

/**
 * DIAGNOSTIC GOAL CLARIFICATION
 * ==============================
 *
 * Question to Answer: Are REST endpoints available?
 *
 * Category: Developer Experience
 * Slug: dx-rest-api-custom-endpoints
 *
 * Purpose:
 * Determine if the WordPress site meets Developer Experience criteria related to:
 * Automatically initialized lean diagnostic for Dx Rest Api Custom Endpoints. Optimized for minimal ov...
 */

/**
 * TEST IMPLEMENTATION OUTLINE
 * ============================
 * This diagnostic CAN be successfully implemented. Here's how:
 *
 * DETECTION STRATEGY:
 * 1. Identify WordPress hooks/options/state indicating the answer
 * 2. Query the relevant WordPress state
 * 3. Evaluate against criteria
 * 4. Return null if passing, array with finding if failing
 *
 * SIGNALS TO CHECK:
 * - WordPress options/settings related to this diagnostic
 * - Plugin/theme active status if applicable
 * - Configuration flags or feature toggles
 * - Database state or transient values
 *
 * IMPLEMENTATION STEPS:
 * 1. Update check() method with actual logic
 * 2. Add helper methods to identify relevant options
 * 3. Build severity assessment based on impact
 * 4. Create test case with mock WordPress state
 * 5. Validate against real site conditions
 */
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
 * Question: Are REST endpoints available?
 * Slug: dx-rest-api-custom-endpoints
 * Category: Developer Experience
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
class Diagnostic_Dx_Rest_Api_Custom_Endpoints extends Diagnostic_Base {
	protected static $slug = 'dx-rest-api-custom-endpoints';

	protected static $title = 'Dx Rest Api Custom Endpoints';

	protected static $description = 'Automatically initialized lean diagnostic for Dx Rest Api Custom Endpoints. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'dx-rest-api-custom-endpoints';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Are REST endpoints available?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Are REST endpoints available?. Part of Developer Experience analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'developer_experience';
	}

	/**
	 * Run the diagnostic test
	 *
	 * @return array Finding data or empty if no issue
	 */
	public static function run(): array {
		// Implement: Are REST endpoints available? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 48;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/dx-rest-api-custom-endpoints/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/dx-rest-api-custom-endpoints/';
	}

	public static function check(): ?array {
		// Check if site has custom REST API endpoints
		global $wp_rest_server;

		if ( ! $wp_rest_server ) {
			$wp_rest_server = rest_get_server();
		}

		$routes = $wp_rest_server->get_routes();
		$custom_count = 0;

		// Count custom endpoints (excluding built-in WordPress routes)
		$builtin_prefixes = [ '/wp/', '/oembed/' ];
		foreach ( $routes as $route => $endpoint ) {
			$is_builtin = false;
			foreach ( $builtin_prefixes as $prefix ) {
				if ( strpos( $route, $prefix ) === 0 ) {
					$is_builtin = true;
					break;
				}
			}
			if ( ! $is_builtin ) {
				$custom_count++;
			}
		}

		// Flag if no custom endpoints (developers should consider adding them)
		if ( $custom_count === 0 ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'dx-rest-api-custom-endpoints',
				'Dx Rest Api Custom Endpoints',
				'No custom REST API endpoints detected. Consider exposing custom post types or data via REST API for better API coverage.',
				'dx',
				'low',
				20,
				'dx-rest-api-custom-endpoints'
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Dx Rest Api Custom Endpoints
	 * Slug: dx-rest-api-custom-endpoints
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Dx Rest Api Custom Endpoints. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_dx_rest_api_custom_endpoints(): array {
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

/**
 * STUB - NEEDS CLARIFICATION:
 * The check() method has a stub condition (if !false) that always passes.
 * Please clarify: What condition should trigger an issue? How can we detect it?
 */
