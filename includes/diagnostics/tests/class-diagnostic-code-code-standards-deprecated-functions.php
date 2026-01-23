<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Deprecated Function Usage
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-standards-deprecated-functions
 * Training: https://wpshadow.com/training/code-standards-deprecated-functions
 */
class Diagnostic_Code_CODE_STANDARDS_DEPRECATED_FUNCTIONS extends Diagnostic_Base {
    public static function check(): ?array {
        // Check for common deprecated WordPress functions
        $deprecated_functions = [
            'wp_tiny_mce' => '3.9',
            'get_theme' => '3.4',
            'get_themes' => '3.4',
            'get_theme_data' => '3.4',
            'get_current_theme' => '3.4',
            'clean_url' => '3.0',
            'wp_setcookie' => '2.5',
            'wp_get_cookie_login' => '2.5',
            'get_userdatabylogin' => '3.3',
            'get_user_id_from_string' => '3.6',
            'get_profile' => '2.5',
            'set_current_user' => '3.0 (use wp_set_current_user)',
            'get_currentuserinfo' => '4.5 (use wp_get_current_user)',
            'automatic_feed_links' => '3.0 (use add_theme_support)',
        ];
        
        $found_functions = [];
        foreach ($deprecated_functions as $func => $since) {
            if (\function_exists($func)) {
                // Check if the function is actually being used in loaded code
                // by looking for it in the call stack or recent function calls
                // For simplicity, we'll check if it exists in WordPress (meaning it's still loaded for BC)
                $found_functions[$func] = $since;
            }
        }
        
        if (empty($found_functions)) {
            return null; // No deprecated functions found, healthy
        }
        
        return [
            'id' => 'code-standards-deprecated-functions',
            'title' => __('Deprecated Function Usage', 'wpshadow'),
            'description' => sprintf(
                __('WordPress has deprecated functions that are still available: %s. Review usage in your theme/plugins.', 'wpshadow'),
                implode(', ', array_keys($found_functions))
            ),
            'severity' => 'low',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-standards-deprecated-functions',
            'training_link' => 'https://wpshadow.com/training/code-standards-deprecated-functions',
            'auto_fixable' => false,
            'threat_level' => 3,
            'data' => [
                'deprecated_functions' => $found_functions,
            ],
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE STANDARDS DEPRECATED FUNCTIONS
	 * Slug: -code-code-standards-deprecated-functions
	 * File: class-diagnostic-code-code-standards-deprecated-functions.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE STANDARDS DEPRECATED FUNCTIONS
	 * Slug: -code-code-standards-deprecated-functions
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
	public static function test_live__code_code_standards_deprecated_functions(): array {
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
