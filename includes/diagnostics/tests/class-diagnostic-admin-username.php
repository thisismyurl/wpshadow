<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Admin_Username extends Diagnostic_Base {


	protected static $slug        = 'admin-username';
	protected static $title       = 'Default Admin Username';
	protected static $description = 'Checks if the default "admin" username exists, which is a security vulnerability.';

	public static function check(): ?array {
		$admin_user = get_user_by( 'login', 'admin' );

		if ( ! $admin_user ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Default "admin" username exists. This is a major security vulnerability as it\'s a primary brute-force target. Create a new admin account with a unique username and delete this one.', 'wpshadow' ),
			'category'     => 'security',
			'severity'     => 'high',
			'threat_level' => 85,
			'auto_fixable' => false,
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Default Admin Username
	 * Slug: admin-username
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if the default
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_admin_username(): array {
		$admin_user = get_user_by( 'login', 'admin' );
		$has_issue  = (bool) $admin_user;

		$result                 = self::check();
		$diagnostic_found_issue = is_array( $result );

		$test_passes = ( $has_issue === $diagnostic_found_issue );

		$message = $test_passes
			? 'Admin username check matches site state'
			: sprintf(
				'Mismatch: expected %s but diagnostic returned %s (admin user exists: %s)',
				$has_issue ? 'issue' : 'no issue',
				$diagnostic_found_issue ? 'issue' : 'no issue',
				$admin_user ? 'yes' : 'no'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
