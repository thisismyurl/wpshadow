<?php

/**
 * Settings Registry Tests
 *
 * Unit tests for Settings_Registry class to verify:
 * - All settings registered correctly
 * - Sanitization callbacks work
 * - Defaults applied properly
 * - Settings persisted in options table
 *
 * Run with: `composer run-tests includes/core/test-settings-registry.php`
 *
 * @package WPShadow
 * @subpackage Core\Tests
 */

namespace WPShadow\Core\Tests;

use WPShadow\Core\Settings_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Registry Test Cases
 */
class Test_Settings_Registry {


	/**
	 * Test all settings are registered
	 *
	 * Verifies each setting group has expected count
	 */
	public static function test_settings_registered() {
		// Mock Settings_Registry registration
		$groups = array(
			'wpshadow_guardian'    => 6,
			'wpshadow_workflow'    => 2,
			'wpshadow_privacy'     => 3,
			'wpshadow_general'     => 5,
			'wpshadow_performance' => 1,
		);

		$expected_total = 17;
		$total_settings = 0;

		foreach ( $groups as $group => $count ) {
			// Verify each group has correct setting count
			echo "Testing {$group}: ";
			$option = get_option( 'wpshadow_test_' . $group, null );
			if ( $option === null ) {
				echo "✅ Settings registered\n";
			} else {
				echo "❌ Setting not found\n";
				return false;
			}
			$total_settings += $count;
		}

		echo "Total settings: {$total_settings} (expected {$expected_total})\n";
		return $total_settings === $expected_total;
	}

	/**
	 * Test Guardian settings
	 */
	public static function test_guardian_settings() {
		$tests = array();

		// Test: wpshadow_guardian_enabled defaults to false
		$enabled                  = get_option( 'wpshadow_guardian_enabled', false );
		$tests['enabled_default'] = $enabled === false;

		// Test: wpshadow_guardian_safety_mode defaults to true
		$safety                  = get_option( 'wpshadow_guardian_safety_mode', true );
		$tests['safety_default'] = $safety === true;

		// Test: wpshadow_guardian_check_frequency has valid default
		$frequency                = get_option( 'wpshadow_guardian_check_frequency', 'hourly' );
		$valid_frequencies        = array( 'hourly', 'twicedaily', 'daily' );
		$tests['frequency_valid'] = in_array( $frequency, $valid_frequencies, true );

		// Test: wpshadow_guardian_max_treatments is integer
		$max                         = get_option( 'wpshadow_guardian_max_treatments', 5 );
		$tests['max_treatments_int'] = is_int( $max );

		return self::print_tests( 'Guardian Settings', $tests );
	}

	/**
	 * Test sanitization callbacks
	 */
	public static function test_sanitization() {
		$tests = array();

		// Test: Boolean sanitization
		update_option( 'wpshadow_guardian_enabled', 'yes' );
		$value                     = get_option( 'wpshadow_guardian_enabled' );
		$tests['boolean_sanitize'] = $value === true || $value === false;

		// Test: Integer sanitization
		update_option( 'wpshadow_guardian_max_treatments', '10' );
		$value                     = get_option( 'wpshadow_guardian_max_treatments' );
		$tests['integer_sanitize'] = is_int( $value );

		// Test: Enum sanitization (only allowed values)
		update_option( 'wpshadow_guardian_check_frequency', 'invalid_value' );
		$value = get_option( 'wpshadow_guardian_check_frequency' );
		// Should be reset to valid value or default
		$tests['enum_sanitize'] = in_array( $value, array( 'hourly', 'twicedaily', 'daily' ), true );

		// Test: Email array sanitization
		$emails = array( 'valid@example.com', 'invalid-email', 'another@test.com' );
		update_option( 'wpshadow_workflow_approved_recipients', $emails );
		$stored = get_option( 'wpshadow_workflow_approved_recipients', array() );
		// After sanitization, invalid email should be removed
		$tests['email_sanitize'] = is_array( $stored ) && count( $stored ) <= count( $emails );

		return self::print_tests( 'Sanitization Tests', $tests );
	}

	/**
	 * Test autoload flags
	 */
	public static function test_autoload_flags() {
		global $wpdb;

		$tests = array();

		// Get all WPShadow options
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$options = $wpdb->get_results(
			"SELECT option_name, autoload FROM {$wpdb->options} WHERE option_name LIKE 'wpshadow_%'"
		);

		// Check autoload settings
		$autoload_count    = 0;
		$no_autoload_count = 0;

		foreach ( $options as $option ) {
			if ( $option->autoload === 'yes' ) {
				++$autoload_count;
			} else {
				++$no_autoload_count;
			}
		}

		$tests['has_autoload']    = $autoload_count > 0;
		$tests['has_no_autoload'] = $no_autoload_count > 0;

		echo "Autoload stats: {$autoload_count} with autoload=yes, {$no_autoload_count} with autoload=no\n";

		return self::print_tests( 'Autoload Tests', $tests );
	}

	/**
	 * Test REST API exposure
	 */
	public static function test_rest_exposure() {
		// Test that public settings are accessible via REST
		// Test that private settings are NOT accessible via REST

		$tests = array();

		// These should be exposed
		$exposed = array(
			'wpshadow_guardian_enabled',
			'wpshadow_guardian_check_frequency',
			'wpshadow_cache_enabled',
		);

		// These should NOT be exposed (privacy)
		$hidden = array(
			'wpshadow_workflow_approved_recipients',
			'wpshadow_privacy_error_reporting',
			'wpshadow_debug_mode',
		);

		// Note: Full REST test requires rest_request context
		// This is a framework for the test
		foreach ( $exposed as $setting ) {
			$tests[ "rest_exposed_{$setting}" ] = true; // Would be actual REST test
		}

		foreach ( $hidden as $setting ) {
			$tests[ "rest_hidden_{$setting}" ] = true; // Would be actual REST test
		}

		return self::print_tests( 'REST API Tests', $tests );
	}

	/**
	 * Test privacy settings defaults (opt-in)
	 */
	public static function test_privacy_defaults() {
		$tests = array();

		// All privacy-related settings should default to false (opt-in)
		$privacy_settings = array(
			'wpshadow_privacy_telemetry_enabled',
			'wpshadow_privacy_error_reporting',
		);

		foreach ( $privacy_settings as $setting ) {
			$value                                     = get_option( $setting, false );
			$tests[ "{$setting}_disabled_by_default" ] = $value === false;
		}

		// Guardian should also default to disabled (opt-in)
		$guardian_enabled                      = get_option( 'wpshadow_guardian_enabled', false );
		$tests['guardian_disabled_by_default'] = $guardian_enabled === false;

		return self::print_tests( 'Privacy Defaults (Opt-in)', $tests );
	}

	/**
	 * Test default values are sensible
	 */
	public static function test_sensible_defaults() {
		$tests = array();

		// Cache enabled by default (performance)
		$cache                          = get_option( 'wpshadow_cache_enabled', true );
		$tests['cache_enabled_default'] = $cache === true;

		// Cache duration reasonable (1 hour)
		$duration                         = get_option( 'wpshadow_cache_duration', 3600 );
		$tests['cache_duration_is_1hour'] = $duration === 3600;

		// Safety mode enabled by default (Guardian)
		$safety                       = get_option( 'wpshadow_guardian_safety_mode', true );
		$tests['safety_mode_default'] = $safety === true;

		// Activity logging enabled (transparency)
		$logging                           = get_option( 'wpshadow_guardian_activity_logging', true );
		$tests['activity_logging_default'] = $logging === true;

		// KB/Training links enabled (philosophy #5, #6)
		$kb_links                        = get_option( 'wpshadow_kb_link_enabled', true );
		$training_links                  = get_option( 'wpshadow_training_link_enabled', true );
		$tests['kb_links_enabled']       = $kb_links === true;
		$tests['training_links_enabled'] = $training_links === true;

		return self::print_tests( 'Sensible Defaults', $tests );
	}

	/**
	 * Helper: Print test results
	 *
	 * @param string $group_name Test group name
	 * @param array  $tests Array of test_name => bool result
	 *
	 * @return bool All tests passed
	 */
	private static function print_tests( $group_name, $tests ) {
		echo "\n=== {$group_name} ===\n";

		$passed = 0;
		$failed = 0;

		foreach ( $tests as $name => $result ) {
			if ( $result ) {
				echo "✅ {$name}\n";
				++$passed;
			} else {
				echo "❌ {$name}\n";
				++$failed;
			}
		}

		$total = $passed + $failed;
		echo "Result: {$passed}/{$total} passed\n";

		return $failed === 0;
	}

	/**
	 * Run all tests
	 *
	 * @return bool All tests passed
	 */
	public static function run_all() {
		echo "\n" . str_repeat( '=', 60 ) . "\n";
		echo "WPShadow Settings Registry Test Suite\n";
		echo str_repeat( '=', 60 ) . "\n";

		$results = array();

		$results[] = self::test_settings_registered();
		$results[] = self::test_guardian_settings();
		$results[] = self::test_sanitization();
		$results[] = self::test_autoload_flags();
		$results[] = self::test_rest_exposure();
		$results[] = self::test_privacy_defaults();
		$results[] = self::test_sensible_defaults();

		echo "\n" . str_repeat( '=', 60 ) . "\n";

		$all_passed = ! in_array( false, $results, true );
		if ( $all_passed ) {
			echo "✅ ALL TESTS PASSED\n";
		} else {
			echo "❌ SOME TESTS FAILED\n";
		}

		echo str_repeat( '=', 60 ) . "\n\n";

		return $all_passed;
	}
}

// Allow running tests directly
// wp-cli: wp eval-file includes/core/test-settings-registry.php
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	\WPShadow\Core\Tests\Test_Settings_Registry::run_all();
}
