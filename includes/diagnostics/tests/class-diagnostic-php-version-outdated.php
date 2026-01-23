<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: PHP Version Outdated (SERVER-004)
 * 
 * Detects PHP <8.0.
 * Philosophy: Show value (#9) with speed and security improvements.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Php_Version_Outdated extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check if PHP version is outdated
        $php_version = phpversion();
        
        // Parse version
        $version_parts = explode('.', $php_version);
        $major = (int)$version_parts[0];
        $minor = (int)$version_parts[1];
        
        // PHP 8.1+ is recommended (8.2+ preferred)
        if ($major < 8 || ($major === 8 && $minor < 1)) {
            return array(
                'id' => 'php-version-outdated',
                'title' => sprintf(__('PHP Version %s - Upgrade Recommended', 'wpshadow'), $php_version),
                'description' => __('PHP 8.2+ is recommended for security, performance, and compatibility. Contact your hosting provider about upgrading.', 'wpshadow'),
                'severity' => 'high',
                'category' => 'code-quality',
                'kb_link' => 'https://wpshadow.com/kb/php-version-upgrade/',
                'training_link' => 'https://wpshadow.com/training/php-compatibility/',
                'auto_fixable' => false,
                'threat_level' => 80,
            );
        }
        return null;
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Php Version Outdated
	 * Slug: -php-version-outdated
	 * File: class-diagnostic-php-version-outdated.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Php Version Outdated
	 * Slug: -php-version-outdated
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
	public static function test_live__php_version_outdated(): array {
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
			'message' => 'Test not yet implemented',
		);
	}

}
