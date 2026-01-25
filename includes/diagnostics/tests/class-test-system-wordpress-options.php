<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test: WordPress Options Integrity
 *
 * Validates that critical WordPress options are properly configured.
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    System
 * @philosophy  #9 Show Value - Ensure WordPress configuration is sound
 */
class Test_System_WordPress_Options extends Diagnostic_Base {


	/**
	 * Check WordPress options
	 *
	 * @return array|null Issues found or null if options OK
	 */
	public static function check(): ?array {
		$issues = array();

		// Check required options
		$siteurl     = get_option( 'siteurl' );
		$home        = get_option( 'home' );
		$admin_email = get_option( 'admin_email' );

		if ( empty( $siteurl ) ) {
			$issues[] = 'siteurl';
		}
		if ( empty( $home ) ) {
			$issues[] = 'home';
		}
		if ( empty( $admin_email ) ) {
			$issues[] = 'admin_email';
		}
		if ( $siteurl !== $home ) {
			$issues[] = 'siteurl/home mismatch';
		}

		if ( ! is_email( $admin_email ) ) {
			$issues[] = 'invalid admin_email';
		}

		if ( empty( $issues ) ) {
			return null; // All options OK
		}

		return array(
			'id'           => 'wordpress-options-integrity',
			'title'        => 'WordPress Options Issues',
			'description'  => 'Some WordPress options are misconfigured: ' . implode( ', ', $issues ),
			'threat_level' => 60,
		);
	}

	/**
	 * Live test for WordPress options diagnostic
	 *
	 * @return array Test result with 'passed' and 'message' keys
	 */
	public static function test_live_wordpress_options(): array {
		$result = self::check();

		// Test 1: Check options manually
		$siteurl     = get_option( 'siteurl' );
		$home        = get_option( 'home' );
		$admin_email = get_option( 'admin_email' );

		$actually_missing = array();

		if ( empty( $siteurl ) ) {
			$actually_missing[] = 'siteurl';
		}
		if ( empty( $home ) ) {
			$actually_missing[] = 'home';
		}
		if ( empty( $admin_email ) ) {
			$actually_missing[] = 'admin_email';
		}
		if ( $siteurl !== $home ) {
			$actually_missing[] = 'siteurl/home mismatch';
		}

		if ( ! is_email( $admin_email ) ) {
			$actually_missing[] = 'invalid admin_email';
		}

		// Test 2: Compare results
		if ( ! empty( $actually_missing ) ) {
			// Should return an issue
			if ( is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'Options issues: ' . implode( ', ', $actually_missing ) . ', but check() returned null.',
				);
			}
		} else {
			// All options OK
			if ( ! is_null( $result ) ) {
				return array(
					'passed'  => false,
					'message' => 'All options OK, but check() returned: ' . wp_json_encode( $result ),
				);
			}
		}

		// All tests passed
		return array(
			'passed'  => true,
			'message' => 'WordPress options check passed. All critical options are properly configured.',
		);
	}
}
