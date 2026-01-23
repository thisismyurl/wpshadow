<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Keyboard Navigation Missing
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-a11y-keyboard-nav
 * Training: https://wpshadow.com/training/code-a11y-keyboard-nav
 */
class Diagnostic_Code_CODE_A11Y_KEYBOARD_NAV extends Diagnostic_Base {
    public static function check(): ?array {
        // Get the home page HTML
        $home_url = \home_url('/');
        $response = \wp_remote_get($home_url, array('timeout' => 10));

        if (\is_wp_error($response)) {
            return null; // Can't check, skip diagnostic
        }

        $html = \wp_remote_retrieve_body($response);

        // Find elements with onclick on non-interactive elements
        $issues = array();

        // Check for divs/spans with onclick but no tabindex or role
        if (preg_match_all('/<(div|span)[^>]+onclick=[^>]*>/i', $html, $matches)) {
            foreach ($matches[0] as $element) {
                // Check if element has tabindex or role="button"
                $has_tabindex = preg_match('/tabindex=["\']?-?\d+["\']?/i', $element);
                $has_button_role = preg_match('/role=["\']?(button|link)["\']?/i', $element);

                if (! $has_tabindex && ! $has_button_role) {
                    $issues[] = 'Found non-interactive element with onclick without tabindex/role';
                    break; // Only report once
                }
            }
        }

        // Check for elements with click handlers but no keyboard handler
        // This is a heuristic - look for onclick without onkeypress/onkeydown
        if (preg_match('/<[^>]+onclick=[^>]+>/i', $html, $onclick_matches)) {
            if (! preg_match('/<[^>]+onkey(press|down|up)=/i', $html)) {
                $issues[] = 'Found click handlers without corresponding keyboard handlers';
            }
        }

        if (empty($issues)) {
            return null; // No issues found
        }

        return [
            'id' => 'code-a11y-keyboard-nav',
            'title' => __('Keyboard Navigation Missing', 'wpshadow'),
            'description' => __('Detects interactive components not keyboard accessible. ' . implode('. ', $issues), 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-a11y-keyboard-nav',
            'training_link' => 'https://wpshadow.com/training/code-a11y-keyboard-nav',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE A11Y KEYBOARD NAV
	 * Slug: -code-code-a11y-keyboard-nav
	 * File: class-diagnostic-code-code-a11y-keyboard-nav.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE A11Y KEYBOARD NAV
	 * Slug: -code-code-a11y-keyboard-nav
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
	public static function test_live__code_code_a11y_keyboard_nav(): array {
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
