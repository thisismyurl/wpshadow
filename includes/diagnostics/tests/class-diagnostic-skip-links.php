<?php

/**
 * Diagnostic: Skip Links for Accessibility
 *
 * Checks if the site has skip links for keyboard navigation.
 * Skip links allow keyboard users to jump directly to main content,
 * bypassing repetitive navigation elements.
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
 * Skip Links Diagnostic
 */
class Diagnostic_Skip_Links extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Null if no issues, array with details if issues found
	 */
	public static function run(): ?array
	{
		// Get the home page HTML
		$home_url = home_url('/');
		$response = wp_remote_get($home_url, array('timeout' => 10));

		if (is_wp_error($response)) {
			return null; // Can't check, skip diagnostic
		}

		$html = wp_remote_retrieve_body($response);

		// Check for common skip link patterns
		$has_skip_link = false;

		// Pattern 1: Standard skip link with #content or #main
		if (preg_match('/<a[^>]+href=["\']#(content|main|skip)["\'][^>]*>.*skip.*<\/a>/i', $html)) {
			$has_skip_link = true;
		}

		// Pattern 2: Skip to content with various IDs
		if (preg_match('/<a[^>]+class=["\'][^"\']*skip-link[^"\']*["\'][^>]*>/i', $html)) {
			$has_skip_link = true;
		}

		// Pattern 3: WordPress default skip link
		if (preg_match('/<a[^>]+class=["\']skip-link screen-reader-text["\'][^>]*>/i', $html)) {
			$has_skip_link = true;
		}

		if ($has_skip_link) {
			return null; // Skip links present, all good!
		}

		// No skip links found
		return array(
			'title'       => __('Missing Skip Links for Keyboard Navigation', 'wpshadow'),
			'description' => __('Your site doesn\'t have skip links, making it harder for keyboard users to navigate. Skip links allow users to bypass repetitive navigation and jump straight to main content.', 'wpshadow'),
			'severity'    => 'medium',
			'category'    => 'accessibility',
			'impact'      => __('Keyboard users (including those with motor disabilities) must tab through all navigation links to reach main content on every page.', 'wpshadow'),
			'details'     => array(
				'check_performed' => 'Scanned homepage HTML for skip link patterns',
				'patterns_checked' => array(
					'href="#content" or href="#main"',
					'class="skip-link"',
					'WordPress default skip-link screen-reader-text',
				),
				'found'           => false,
			),
			'kb_link'     => 'https://wpshadow.com/kb/skip-links',
			'training'    => 'https://wpshadow.com/training/accessibility-skip-links',
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
			'id'          => 'skip_links',
			'title'       => __('Skip Links', 'wpshadow'),
			'description' => __('Checks if keyboard navigation skip links are present', 'wpshadow'),
			'category'    => 'accessibility',
			'severity'    => 'medium',
		);
	}
}
