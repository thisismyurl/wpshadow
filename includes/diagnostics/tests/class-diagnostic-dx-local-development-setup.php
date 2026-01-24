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
 * Question: Is local dev easy?
 * Slug: dx-local-development-setup
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
class Diagnostic_Dx_Local_Development_Setup extends Diagnostic_Base {
	protected static $slug = 'dx-local-development-setup';

	protected static $title = 'Dx Local Development Setup';

	protected static $description = 'Automatically initialized lean diagnostic for Dx Local Development Setup. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'dx-local-development-setup';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Is local dev easy?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Is local dev easy?. Part of Developer Experience analysis.', 'wpshadow' );
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
		// Implement: Is local dev easy? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 45;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/dx-local-development-setup/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/dx-local-development-setup/';
	}

	public static function check(): ?array {
		// Check if local development environment is configured
		$has_local_setup = false;

		// Check for common local dev indicators
		$local_indicators = [
			// Docker
			ABSPATH . 'docker-compose.yml',
			ABSPATH . 'Dockerfile',
			// Vagrant
			ABSPATH . 'Vagrantfile',
			// Local by Flywheel / Local
			ABSPATH . '.wp-env.json',
			// WP CLI config
			ABSPATH . 'wp-cli.yml',
			// Dev environment markers
			ABSPATH . '.env',
		];

		foreach ( $local_indicators as $file ) {
			if ( file_exists( $file ) ) {
				$has_local_setup = true;
				break;
			}
		}

		// Check for WP_DEBUG
		if ( ! $has_local_setup && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$has_local_setup = true;
		}

		if ( ! $has_local_setup ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'dx-local-development-setup',
				'Dx Local Development Setup',
				'No local development environment configuration found. Set up Docker, Vagrant, or Local by Flywheel for consistent development.',
				'dx',
				'low',
				20,
				'dx-local-development-setup'
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Dx Local Development Setup
	 * Slug: dx-local-development-setup
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Dx Local Development Setup. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_dx_local_development_setup(): array {
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

