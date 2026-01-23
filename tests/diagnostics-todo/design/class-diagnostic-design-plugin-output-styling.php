<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Plugin Output Styling
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-plugin-output-styling
 * Training: https://wpshadow.com/training/design-plugin-output-styling
 */
class Diagnostic_Design_PLUGIN_OUTPUT_STYLING extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-plugin-output-styling',
            'title' => __('Plugin Output Styling', 'wpshadow'),
            'description' => __('Detects unstyled plugin output, CSS conflicts.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-plugin-output-styling',
            'training_link' => 'https://wpshadow.com/training/design-plugin-output-styling',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design PLUGIN OUTPUT STYLING
	 * Slug: -design-plugin-output-styling
	 * File: class-diagnostic-design-plugin-output-styling.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design PLUGIN OUTPUT STYLING
	 * Slug: -design-plugin-output-styling
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
	public static function test_live__design_plugin_output_styling(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Plugin output is properly styled and integrated'];
		}
		$message = $result['description'] ?? 'Plugin styling integration issue detected';
		return ['passed' => false, 'message' => $message];
	}

}
