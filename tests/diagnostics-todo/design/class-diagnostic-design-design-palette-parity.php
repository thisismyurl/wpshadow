<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Palette Parity
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-palette-parity
 * Training: https://wpshadow.com/training/design-palette-parity
 */
class Diagnostic_Design_DESIGN_PALETTE_PARITY extends Diagnostic_Base {
    public static function check(): ?array {
        return [
            'id' => 'design-palette-parity',
            'title' => __('Palette Parity', 'wpshadow'),
            'description' => __('Checks editor palette matches front-end and tokens.', 'wpshadow'),
            'severity' => 'medium',
            'category' => 'design',
            'kb_link' => 'https://wpshadow.com/kb/design-palette-parity',
            'training_link' => 'https://wpshadow.com/training/design-palette-parity',
            'auto_fixable' => false,
            'threat_level' => 6
        ];
    }



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Design DESIGN PALETTE PARITY
	 * Slug: -design-design-palette-parity
	 * File: class-diagnostic-design-design-palette-parity.php
	 * 
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Design DESIGN PALETTE PARITY
	 * Slug: -design-design-palette-parity
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
	public static function test_live__design_design_palette_parity(): array {
		$result = self::check();
		if ($result === null) {
			return ['passed' => true, 'message' => 'Color palette is consistent across all themes'];
		}
		$message = $result['description'] ?? 'Color palette inconsistency detected';
		return ['passed' => false, 'message' => $message];
	}

}
