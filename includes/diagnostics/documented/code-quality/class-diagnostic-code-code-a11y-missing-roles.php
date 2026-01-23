<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing ARIA Roles
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-a11y-missing-roles
 * Training: https://wpshadow.com/training/code-a11y-missing-roles
 */
class Diagnostic_Code_CODE_A11Y_MISSING_ROLES extends Diagnostic_Base {
    public static function check(): ?array {
        // Get the home page HTML
        $home_url = \home_url('/');
        $response = \wp_remote_get($home_url, array('timeout' => 10));

        if (\is_wp_error($response)) {
            return null; // Can't check, skip diagnostic
        }

        $html = \wp_remote_retrieve_body($response);

        $issues = array();
        $missing_roles_count = 0;

        // Check for divs/spans with button-like classes but no role
        $button_classes = array('btn', 'button', 'cta', 'submit', 'click');
        foreach ($button_classes as $class) {
            if (preg_match_all('/<(div|span)[^>]*class=["\'][^"\']*' . $class . '[^"\']*["\'][^>]*>/i', $html, $matches)) {
                foreach ($matches[0] as $element) {
                    // Check if element has role attribute
                    if (! preg_match('/role=["\']?[^"\'\']+["\']?/i', $element)) {
                        $missing_roles_count++;
                        if ($missing_roles_count <= 3) {
                            $issues[] = sprintf('Found element with class="%s" without role attribute', $class);
                        }
                    }
                }
            }
        }

        // Check for divs/spans with onclick but no role
        if (preg_match_all('/<(div|span)[^>]+onclick=[^>]*>/i', $html, $matches)) {
            foreach ($matches[0] as $element) {
                if (! preg_match('/role=["\']?[^"\'\']+["\']?/i', $element)) {
                    $missing_roles_count++;
                    if (count($issues) < 3) {
                        $issues[] = 'Found clickable div/span without role attribute';
                    }
                }
            }
        }

        if ($missing_roles_count === 0) {
            return null; // No issues found
        }

        $description = sprintf(
            \__('Found %d interactive element(s) missing ARIA role attributes. %s', 'wpshadow'),
            $missing_roles_count,
            implode('. ', array_unique($issues))
        );

        return [
            'id' => 'code-a11y-missing-roles',
            'title' => __('Missing ARIA Roles', 'wpshadow'),
            'description' => $description,
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-a11y-missing-roles',
            'training_link' => 'https://wpshadow.com/training/code-a11y-missing-roles',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE A11Y MISSING ROLES
	 * Slug: -code-code-a11y-missing-roles
	 * File: class-diagnostic-code-code-a11y-missing-roles.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE A11Y MISSING ROLES
	 * Slug: -code-code-a11y-missing-roles
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
	public static function test_live__code_code_a11y_missing_roles(): array {
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
