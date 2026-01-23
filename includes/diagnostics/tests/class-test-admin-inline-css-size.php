<?php

/**
 * WPShadow Admin Diagnostic Test: Excessive Inline CSS in Admin
 *
 * Tests if wp-admin has too much inline CSS, which can cause:
 * - HTML bloat (no browser caching)
 * - Slower parsing and rendering
 * - Maintenance issues
 * - Render blocking
 *
 * Pattern: Collects inline styles from $wp_styles
 * Context: Requires admin context, uses global $wp_styles
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance
 * @philosophy  #7 Ridiculously Good - Even admin assets should be optimized
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin Inline CSS Size
 *
 * Checks if admin has excessive inline CSS (> 20KB total)
 *
 * @verified Not yet tested
 */
class Test_Admin_Inline_CSS_Size extends Diagnostic_Base
{

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		// Only run in admin context
		if (! is_admin()) {
			return null;
		}

		global $wp_styles;

		// Ensure wp_styles is initialized
		if (! isset($wp_styles) || ! is_object($wp_styles)) {
			return null;
		}

		$total_inline_size = 0;
		$inline_block_count = 0;
		$handles_with_inline = array();

		// Check inline styles added to enqueued stylesheets
		foreach ($wp_styles->queue ?? array() as $handle) {
			if (! isset($wp_styles->registered[$handle])) {
				continue;
			}

			$style_obj = $wp_styles->registered[$handle];

			// Check for inline styles (extra data)
			if (! empty($style_obj->extra['after'])) {
				$inline_styles = is_array($style_obj->extra['after'])
					? implode('', $style_obj->extra['after'])
					: $style_obj->extra['after'];

				$size = strlen($inline_styles);
				if ($size > 0) {
					$total_inline_size += $size;
					$inline_block_count++;
					$handles_with_inline[] = $handle;
				}
			}

			// Also check 'before' inline styles
			if (! empty($style_obj->extra['before'])) {
				$inline_styles = is_array($style_obj->extra['before'])
					? implode('', $style_obj->extra['before'])
					: $style_obj->extra['before'];

				$size = strlen($inline_styles);
				if ($size > 0) {
					$total_inline_size += $size;
					$inline_block_count++;
				}
			}
		}

		// Threshold: More than 20KB of inline CSS is excessive
		$threshold = 20480; // 20KB in bytes

		if ($total_inline_size <= $threshold) {
			return null; // Pass
		}

		// Convert to human-readable
		$size_kb = round($total_inline_size / 1024, 2);

		return array(
			'id'           => 'admin-inline-css-size',
			'title'        => 'Excessive Inline CSS in Admin',
			'description'  => sprintf(
				'WordPress admin has %s KB of inline CSS across %d blocks. Inline styles cannot be cached by browsers, increasing page size on every load. Recommended: Under 20 KB. Consider moving inline styles to external CSS files.',
				$size_kb,
				$inline_block_count
			),
			'color'        => '#FF7F50',
			'bg_color'     => '#FFF6F3',
			'kb_link'      => 'https://wpshadow.com/kb/reduce-inline-css',
			'training_link' => 'https://wpshadow.com/training/optimize-css-delivery',
			'auto_fixable' => false,
			'threat_level' => 38, // Medium priority
			'module'       => 'admin-performance',
			'priority'     => 7,
			'meta'         => array(
				'total_size_bytes' => $total_inline_size,
				'total_size_kb'    => $size_kb,
				'block_count'      => $inline_block_count,
				'threshold_kb'     => 20,
				'top_handles'      => array_slice($handles_with_inline, 0, 5), // Top 5 handles
			),
		);
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Diagnostic information
	 */
	public static function get_info(): array
	{
		return array(
			'name'        => 'Admin Inline CSS Size',
			'category'    => 'admin-performance',
			'severity'    => 'medium',
			'description' => 'Detects excessive inline CSS that cannot be cached',
		);
	}
}
