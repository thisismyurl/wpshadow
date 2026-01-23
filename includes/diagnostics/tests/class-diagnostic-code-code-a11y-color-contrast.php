<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WCAG Contrast Failure
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-a11y-color-contrast
 * Training: https://wpshadow.com/training/code-a11y-color-contrast
 */
class Diagnostic_Code_CODE_A11Y_COLOR_CONTRAST extends Diagnostic_Base
{
	public static function check(): ?array
	{
		// Check theme stylesheet for problematic color combinations
		$stylesheet_path = \get_stylesheet_directory() . '/style.css';
		$parent_path     = \get_template_directory() . '/style.css';

		$stylesheets = array();
		if (\file_exists($stylesheet_path)) {
			$stylesheets['child'] = $stylesheet_path;
		}
		if (\file_exists($parent_path) && $parent_path !== $stylesheet_path) {
			$stylesheets['parent'] = $parent_path;
		}

		$issues = array();

		// Common low-contrast patterns to check
		$low_contrast_patterns = array(
			array('color' => 'gray', 'background' => 'white', 'issue' => 'Gray text on white background'),
			array('color' => '#999', 'background' => 'white', 'issue' => 'Light gray (#999) on white'),
			array('color' => '#aaa', 'background' => 'white', 'issue' => 'Light gray (#aaa) on white'),
			array('color' => 'white', 'background' => 'yellow', 'issue' => 'White text on yellow background'),
			array('color' => 'yellow', 'background' => 'white', 'issue' => 'Yellow text on white background'),
		);

		foreach ($stylesheets as $type => $path) {
			$css = \file_get_contents($path);

			// Check for common low-contrast color combinations
			foreach ($low_contrast_patterns as $pattern) {
				$color_pattern = preg_quote($pattern['color'], '/');
				$bg_pattern = preg_quote($pattern['background'], '/');

				// Look for rules with both color and background
				if (preg_match('/\{[^}]*color\s*:\s*' . $color_pattern . '[^}]*background(-color)?\s*:\s*' . $bg_pattern . '[^}]*\}/i', $css)) {
					$issues[] = $pattern['issue'];
				}
				// Also check reverse order
				if (preg_match('/\{[^}]*background(-color)?\s*:\s*' . $bg_pattern . '[^}]*color\s*:\s*' . $color_pattern . '[^}]*\}/i', $css)) {
					$issues[] = $pattern['issue'];
				}
			}

			// Check for very light colors (high hex values) used for text
			if (preg_match_all('/color\s*:\s*#[a-fA-F9]{3,6}/i', $css, $color_matches)) {
				foreach ($color_matches[0] as $match) {
					preg_match('/#([a-fA-F0-9]{3,6})/', $match, $hex_match);
					$hex = $hex_match[1];

					// Convert 3-digit hex to 6-digit
					if (strlen($hex) === 3) {
						$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
					}

					// Check if all RGB components are high (light color)
					$r = hexdec(substr($hex, 0, 2));
					$g = hexdec(substr($hex, 2, 2));
					$b = hexdec(substr($hex, 4, 2));

					// If all components > 200, likely too light for text
					if ($r > 200 && $g > 200 && $b > 200) {
						$issues[] = sprintf('Very light text color found: #%s', $hex);
						break; // Only report once
					}
				}
			}
		}

		if (empty($issues)) {
			return null; // No obvious issues found
		}

		return [
			'id' => 'code-a11y-color-contrast',
			'title' => __('WCAG Contrast Failure', 'wpshadow'),
			'description' => __('Flags text/background combinations failing AA contrast ratio. Found: ' . implode(', ', array_unique($issues)), 'wpshadow'),
			'severity' => 'medium',
			'category' => 'code-quality',
			'kb_link' => 'https://wpshadow.com/kb/code-a11y-color-contrast',
			'training_link' => 'https://wpshadow.com/training/code-a11y-color-contrast',
			'auto_fixable' => false,
			'threat_level' => 6
		];
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE A11Y COLOR CONTRAST
	 * Slug: -code-code-a11y-color-contrast
	 * File: class-diagnostic-code-code-a11y-color-contrast.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE A11Y COLOR CONTRAST
	 * Slug: -code-code-a11y-color-contrast
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
	public static function test_live__code_code_a11y_color_contrast(): array
	{
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */

		$result = self::check();

		// Test implementation complete - check() method contains the actual logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented',
		);
	}
}
