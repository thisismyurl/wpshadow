<?php
declare(strict_types=1);
/**
 * Security Plugin Diagnostic
 *
 * Philosophy: Helpful neighbor - recommend centralized security tool
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if a security plugin is installed.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Security_Plugin extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check for common security plugins
		$security_plugins = array(
			'wordfence/wordfence.php',
			'better-wp-security/better-wp-security.php',
			'sucuri-scanner/sucuri.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
			'jetpack/jetpack.php',
			'bulletproof-security/bulletproof-security.php',
			'security-ninja/security-ninja.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		foreach ( $security_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null; // Security plugin active
			}
		}
		
		return array(
			'id'          => 'security-plugin',
			'title'       => 'No Security Plugin Detected',
			'description' => 'Your site lacks a dedicated security plugin for centralized monitoring, hardening, and threat detection. Consider installing Wordfence, iThemes Security, or similar.',
			'severity'    => 'high',
			'category'    => 'security',
			'kb_link'     => 'https://wpshadow.com/kb/choose-security-plugin/',
			'training_link' => 'https://wpshadow.com/training/security-plugins/',
			'auto_fixable' => false,
			'threat_level' => 75,
		);
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Security Plugin
	 * Slug: -security-plugin
	 * File: class-diagnostic-security-plugin.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Security Plugin
	 * Slug: -security-plugin
	 * 
	 * TODO: Review the check() method to understand what constitutes a passing test.
	 * The test should verify that:
	 * - check() returns NULL when the diagnostic condition is NOT met (site is healthy)
	 * - check() returns an array when the diagnostic condition IS met (issue found)
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_security_plugin(): array {
		$result = self::check();
		
		$security_plugins = array(
			'wordfence/wordfence.php',
			'better-wp-security/better-wp-security.php',
			'sucuri-scanner/sucuri.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
			'jetpack/jetpack.php',
			'bulletproof-security/bulletproof-security.php',
			'security-ninja/security-ninja.php',
		);
		
		$active = get_option('active_plugins', array());
		$has_security = false;
		foreach ($security_plugins as $plugin) {
			if (in_array($plugin, $active, true)) {
				$has_security = true;
				break;
			}
		}
		
		$should_pass = $has_security;
		$diagnostic_passed = is_null($result);
		$test_passes = ($should_pass === $diagnostic_passed);
		
		return array(
			'passed' => $test_passes,
			'message' => $test_passes ? 'Security plugin check matches site state' : 
				"Mismatch: expected " . ($should_pass ? 'pass' : 'fail') . " but got " . 
				($diagnostic_passed ? 'pass' : 'fail'),
		);
	}

}
