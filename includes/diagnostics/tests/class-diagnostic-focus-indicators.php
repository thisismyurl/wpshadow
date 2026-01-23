<?php

/**
 * Diagnostic: Focus Indicators for Keyboard Navigation
 *
 * Checks if the site has visible focus indicators for keyboard navigation.
 * Focus indicators show keyboard users which element currently has focus.
 *
 * Philosophy: Commandment #8 (Inspire Confidence - Accessibility)
 *
 * @package WPShadow
 * @subpackage Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Focus Indicators Diagnostic
 */
class Diagnostic_Focus_Indicators extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Null if no issues, array with details if issues found
	 */
	public static function run(): ?array
	{
		$issues = array();

		// Check theme stylesheet for focus indicator removal
		$stylesheet_path = get_stylesheet_directory() . '/style.css';
		$parent_path     = get_template_directory() . '/style.css';

		$stylesheets = array();
		if (file_exists($stylesheet_path)) {
			$stylesheets['child'] = $stylesheet_path;
		}
		if (file_exists($parent_path) && $parent_path !== $stylesheet_path) {
			$stylesheets['parent'] = $parent_path;
		}

		$has_outline_none = false;
		$has_focus_styles = false;

		foreach ($stylesheets as $type => $path) {
			$css = file_get_contents($path);

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
			if (file_exists($path)) {
				$css = file_get_contents($path);
				if (strpos($css, ':focus-visible') !== false) {
					$uses_focus_visible = true;
					break;
				}
			}
		}

		if (! $uses_focus_visible) {
			$issues[] = __('Theme doesn\'t use modern :focus-visible selector (recommended)', 'wpshadow');
		}

		if (empty($issues)) {
			return null; // No issues found
		}

		return array(
			'title'       => __('Focus Indicators May Be Insufficient', 'wpshadow'),
			'description' => __('Your theme may not provide clear visual indicators when keyboard users navigate through elements. This makes it difficult for keyboard-only users to know where they are on the page.', 'wpshadow'),
			'severity'    => 'medium',
			'category'    => 'accessibility',
			'impact'      => __('Keyboard users (including those with motor disabilities) can\'t see which element has focus, making navigation confusing or impossible.', 'wpshadow'),
			'details'     => array(
				'issues_found'    => $issues,
				'recommendation'  => 'Use :focus-visible for modern focus styles that don\'t appear on mouse clicks',
				'stylesheets'     => array_keys($stylesheets),
			),
			'kb_link'     => 'https://wpshadow.com/kb/focus-indicators',
			'training'    => 'https://wpshadow.com/training/accessibility-keyboard-navigation',
		);
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Metadata about this diagnostic
	 */
	public static function get_meta(): array
	{
		return array(
			'id'          => 'focus_indicators',
			'title'       => __('Focus Indicators', 'wpshadow'),
			'description' => __('Checks if visible focus indicators exist for keyboard navigation', 'wpshadow'),
			'category'    => 'accessibility',
			'severity'    => 'medium',
		);
	}
}
