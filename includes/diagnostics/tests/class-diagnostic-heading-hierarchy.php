<?php

/**
 * Diagnostic: Heading Hierarchy for Screen Readers
 *
 * Checks if pages have proper heading hierarchy (h1, h2, h3, etc).
 * Proper heading structure helps screen reader users navigate content.
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
 * Heading Hierarchy Diagnostic
 */
class Diagnostic_Heading_Hierarchy extends Diagnostic_Base
{

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Null if no issues, array with details if issues found
	 */
	public static function run(): ?array
	{
		// Get home page HTML
		$home_url = home_url('/');
		$response = wp_remote_get($home_url, array('timeout' => 10));

		if (is_wp_error($response)) {
			return null; // Can't check, skip diagnostic
		}

		$html = wp_remote_retrieve_body($response);

		// Extract headings with regex (simple approach)
		preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h\1>/is', $html, $matches, PREG_SET_ORDER);

		if (empty($matches)) {
			return array(
				'title'       => __('No Headings Found on Homepage', 'wpshadow'),
				'description' => __('Your homepage doesn\'t have any heading tags (h1-h6). This makes it difficult for screen reader users to navigate and understand page structure.', 'wpshadow'),
				'severity'    => 'high',
				'category'    => 'accessibility',
				'impact'      => __('Screen reader users can\'t navigate by headings, and SEO is negatively impacted.', 'wpshadow'),
				'kb_link'     => 'https://wpshadow.com/kb/heading-hierarchy',
				'training'    => 'https://wpshadow.com/training/accessibility-semantic-html',
			);
		}

		// Check for issues
		$issues       = array();
		$h1_count     = 0;
		$prev_level   = 0;
		$heading_text = array();

		foreach ($matches as $match) {
			$level = (int) $match[1];
			$text  = wp_strip_all_tags($match[2]);

			$heading_text[] = "h{$level}: " . substr($text, 0, 50);

			// Count H1s
			if ($level === 1) {
				$h1_count++;
			}

			// Check for skipped levels (h2 → h4)
			if ($prev_level > 0 && $level > $prev_level + 1) {
				$issues[] = sprintf(
					/* translators: 1: previous heading level, 2: current heading level */
					__('Heading jumps from h%1$d to h%2$d (skipped h%3$d)', 'wpshadow'),
					$prev_level,
					$level,
					$prev_level + 1
				);
			}

			$prev_level = $level;
		}

		// Check H1 count
		if ($h1_count === 0) {
			$issues[] = __('No h1 heading found (every page should have exactly one h1)', 'wpshadow');
		} elseif ($h1_count > 1) {
			$issues[] = sprintf(
				/* translators: %d: number of h1 headings */
				__('Multiple h1 headings found (%d) - should only have one per page', 'wpshadow'),
				$h1_count
			);
		}

		if (empty($issues)) {
			return null; // Heading hierarchy looks good!
		}

		return array(
			'title'       => __('Heading Hierarchy Issues Found', 'wpshadow'),
			'description' => __('Your homepage has heading structure issues. Screen readers rely on proper heading hierarchy to help users navigate and understand content structure.', 'wpshadow'),
			'severity'    => 'medium',
			'category'    => 'accessibility',
			'impact'      => __('Screen reader users may struggle to navigate content effectively. SEO may also be negatively impacted.', 'wpshadow'),
			'details'     => array(
				'issues'        => $issues,
				'h1_count'      => $h1_count,
				'total_headings' => count($matches),
				'headings'      => array_slice($heading_text, 0, 10), // First 10 headings
			),
			'kb_link'     => 'https://wpshadow.com/kb/heading-hierarchy',
			'training'    => 'https://wpshadow.com/training/accessibility-semantic-html',
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
			'id'          => 'heading_hierarchy',
			'title'       => __('Heading Hierarchy', 'wpshadow'),
			'description' => __('Checks if pages follow proper heading structure (h1→h2→h3)', 'wpshadow'),
			'category'    => 'accessibility',
			'severity'    => 'medium',
		);
	}
}
