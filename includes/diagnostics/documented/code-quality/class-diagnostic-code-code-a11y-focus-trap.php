<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Keyboard Focus Trap
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-a11y-focus-trap
 * Training: https://wpshadow.com/training/code-a11y-focus-trap
 */
class Diagnostic_Code_CODE_A11Y_FOCUS_TRAP extends Diagnostic_Base {
    public static function check(): ?array {
        // Get the home page HTML
        $home_url = \home_url('/');
        $response = \wp_remote_get($home_url, array('timeout' => 10));

        if (\is_wp_error($response)) {
            return null; // Can't check, skip diagnostic
        }

        $html = \wp_remote_retrieve_body($response);

        $issues = array();

        // Check for modal/dialog elements
        $modal_patterns = array('modal', 'dialog', 'overlay', 'popup', 'lightbox');
        $has_modal = false;

        foreach ($modal_patterns as $pattern) {
            if (preg_match('/<[^>]*(class|id)=["\'][^"\']*' . $pattern . '[^"\']*["\'][^>]*>/i', $html)) {
                $has_modal = true;
                break;
            }
        }

        if (! $has_modal) {
            return null; // No modals detected, can't check
        }

        // Check for escape key handler
        $has_escape_handler = preg_match('/(escape|esc|keycode.*27)/i', $html);

        // Check for close button in modal
        $has_close_button = preg_match('/<[^>]*class=["\'][^"\']*(close|dismiss|cancel)[^"\']*["\'][^>]*>/i', $html);

        // Check for aria-modal attribute
        $has_aria_modal = preg_match('/aria-modal=["\']?true["\']?/i', $html);

        // Check for role="dialog"
        $has_dialog_role = preg_match('/role=["\']?dialog["\']?/i', $html);

        if (! $has_escape_handler) {
            $issues[] = 'Modal detected without Escape key handler';
        }

        if (! $has_close_button) {
            $issues[] = 'Modal detected without visible close button';
        }

        if (! $has_aria_modal) {
            $issues[] = 'Modal detected without aria-modal="true"';
        }

        if (! $has_dialog_role) {
            $issues[] = 'Modal detected without role="dialog"';
        }

        if (empty($issues)) {
            return null; // No focus trap issues
        }

        return [
            'id' => 'code-a11y-focus-trap',
            'title' => __('Keyboard Focus Trap', 'wpshadow'),
            'description' => __('Detects modals/overlays trapping keyboard navigation. ' . implode('. ', $issues), 'wpshadow'),
            'severity' => 'medium',
            'category' => 'code-quality',
            'kb_link' => 'https://wpshadow.com/kb/code-a11y-focus-trap',
            'training_link' => 'https://wpshadow.com/training/code-a11y-focus-trap',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE A11Y FOCUS TRAP
	 * Slug: -code-code-a11y-focus-trap
	 * File: class-diagnostic-code-code-a11y-focus-trap.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE A11Y FOCUS TRAP
	 * Slug: -code-code-a11y-focus-trap
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
	public static function test_live__code_code_a11y_focus_trap(): array {
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
