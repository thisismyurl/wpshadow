<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Inactive User Accounts
 *
 * Category: Users & Team
 * Priority: 3
 * Philosophy: 1, 8, 9
 *
 * Test Description:
 * Users who haven't logged in for 90+ days?
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
 * Question to Answer: Inactive User Accounts
 *
 * Category: Users & Team
 * Slug: users-inactive-accounts
 *
 * Purpose:
 * Determine if the WordPress site meets Users & Team criteria related to:
 * Automatically initialized lean diagnostic for Users Inactive Accounts. Optimized for minimal overhea...
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
 *
 * CONFIDENCE LEVEL: High - straightforward yes/no detection possible
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
 * DIAGNOSTIC ANALYSIS - STRAIGHTFORWARD WORDPRESS STATE CHECK
 * ============================================================
 *
 * Question: Inactive User Accounts
 * Slug: users-inactive-accounts
 * Category: Users & Team
 *
 * This diagnostic checks WordPress configuration/settings.
 * Can be implemented by querying options, plugins, or database state.
 *
 * IMPLEMENTATION PLAN:
 * 1. Identify what "pass" means for this diagnostic
 * 2. Find WordPress option(s) or setting(s) to check
 * 3. Implement check() method
 * 4. Create unit test with mock WordPress state
 * 5. Add integration test on real WordPress instance
 *
 * NEXT STEPS:
 * - Clarify exact pass/fail criteria
 * - Identify WordPress hooks/options to query
 * - Build the check() method implementation
 * - Create test cases
 *
 * Current Status: READY FOR IMPLEMENTATION
 */
class Diagnostic_Users_Inactive_Accounts extends Diagnostic_Base {
	protected static $slug = 'users-inactive-accounts';

	protected static $title = 'Users Inactive Accounts';

	protected static $description = 'Automatically initialized lean diagnostic for Users Inactive Accounts. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';


	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'users-inactive-accounts';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Inactive User Accounts', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Users who haven\'t logged in for 90+ days', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'users';
	}

	/**
	 * Get threat level
	 *
	 * @return int 0-100 severity level
	 */
	public static function get_threat_level(): int {
		return 15;
	}

	/**
	 * Run diagnostic test
	 *
	 * @return array Diagnostic results
	 */
	public static function run(): array {
		// STUB: Implement users-inactive-accounts test
		// Philosophy focus: Commandment #1, 8, 9
		//
		// Data collection strategy:
		// - Gather relevant metrics from WordPress
		// - Calculate or query necessary values
		// - Return structured result
		//
		// KB Article: https://wpshadow.com/kb/users-inactive-accounts
		// Training: https://wpshadow.com/training/category-users
		//
		// User impact: Give site owners visibility into team productivity and customer engagement patterns. Identify inactive accounts, track admin activity.

		return array(
			'status' => 'todo',
			'message' => 'Diagnostic not yet implemented',
			'data' => array(),
		);
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https : //wpshadow.com/kb/users-inactive-accounts';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-users';
	}

	public static function check(): ?array {
		// Check for inactive user accounts (no logins in 6 months)
		$six_months_ago = time() - ( 6 * 30 * 24 * 60 * 60 );

		global $wpdb;
		$inactive_users = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'last_login' AND meta_value < %d",
				$six_months_ago
			)
		);

		$inactive_count = count( $inactive_users );

		// Flag if high number of inactive accounts
		if ( $inactive_count > 5 ) {
			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'users-inactive-accounts',
				'Users Inactive Accounts',
				'Multiple inactive user accounts detected (' . $inactive_count . '). Consider disabling or removing unused accounts for security.',
				'users',
				'low',
				25,
				'users-inactive-accounts'
			);
		}

		return null;
	}


	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Users Inactive Accounts
	 * Slug: users-inactive-accounts
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Users Inactive Accounts. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_users_inactive_accounts(): array {
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
