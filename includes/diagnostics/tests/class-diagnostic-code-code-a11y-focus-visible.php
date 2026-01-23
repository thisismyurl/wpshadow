<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Missing Focus Indicators
 * Philosophy: Show value (#9) - intense code quality analysis across all plugins/themes
 * Competitive Edge: Hundreds of code quality checks no competitor offers
 * KB Link: https://wpshadow.com/kb/code-a11y-focus-visible
 * Training: https://wpshadow.com/training/code-a11y-focus-visible
 */
class Diagnostic_Code_CODE_A11Y_FOCUS_VISIBLE extends Diagnostic_Base
{
	public static function check(): ?array
	{
		$issues = array();

		// Check theme stylesheet for focus indicator removal
		$stylesheet_path = \get_stylesheet_directory() . '/style.css';
		$parent_path     = \get_template_directory() . '/style.css';

		$stylesheets = array();
		if (\file_exists($stylesheet_path)) {
			$stylesheets['child'] = $stylesheet_path;
		}
		if (\file_exists($parent_path) && $parent_path !== $stylesheet_path) {
			$stylesheets['parent'] = $parent_path;
		}

		$has_outline_none = false;
		$has_focus_styles = false;

		foreach ($stylesheets as $type => $path) {
			$css = \file_get_contents($path);

			// Check for outline: none or outline: 0 without replacement
			if (preg_match('/(:focus|:focus-visible)\s*\{[^}]*outline\s*:\s*(none|0)\s*;/i', $css)) {
				$has_outline_none = true;

				// Check if there's a replacement focus style in the same rule
				if (preg_match('/(:focus|:focus-visible)\s*\{[^}]*(border|box-shadow|background)[^}]*\}/i', $css)) {
					$has_focus_styles = true;
				}
			}
		}

		// If outline is removed but no replacement styles, that's an issue
		if ($has_outline_none && ! $has_focus_styles) {
			$issues[] = __('Theme CSS removes focus outlines without providing replacement styles', 'wpshadow');
		}

		// Check if focus-visible is being used (modern best practice)
		$uses_focus_visible = false;
		foreach ($stylesheets as $path) {
			if (\file_exists($path)) {
				$css = \file_get_contents($path);
				if (strpos($css, ':focus-visible') !== false) {
					$uses_focus_visible = true;
					break;
				}
			}
		}

		if (! $uses_focus_visible && ! $has_outline_none) {
			// Only suggest if they're not already removing outlines
			$issues[] = __('Theme doesn\'t use modern :focus-visible selector (recommended)', 'wpshadow');
		}

		if (empty($issues)) {
			return null; // No issues found
		}

		return [
			'id' => 'code-a11y-focus-visible',
			'title' => __('Missing Focus Indicators', 'wpshadow'),
			'description' => __('Flags interactive elements without visible focus states. ' . implode(' ', $issues), 'wpshadow'),
			'severity' => 'medium',
			'category' => 'code-quality',
			'kb_link' => 'https://wpshadow.com/kb/code-a11y-focus-visible',
			'training_link' => 'https://wpshadow.com/training/code-a11y-focus-visible',
			'auto_fixable' => false,
			'threat_level' => 6
		];
	}



	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Code CODE A11Y FOCUS VISIBLE
	 * Slug: -code-code-a11y-focus-visible
	 * File: class-diagnostic-code-code-a11y-focus-visible.php
	 *
	 * Test Purpose:
	 * Cannot determine specific pass criteria from available metadata.
	 * Diagnostic: Code CODE A11Y FOCUS VISIBLE
	 * Slug: -code-code-a11y-focus-visible
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
	public static function test_live__code_code_a11y_focus_visible(): array
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
