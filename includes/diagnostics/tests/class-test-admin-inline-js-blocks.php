<?php

/**
 * WPShadow Admin Diagnostic Test: Excessive Inline JavaScript Blocks in Admin
 *
 * Tests if wp-admin has too many inline JavaScript blocks, which can cause:
 * - HTML bloat (no browser caching)
 * - Slower parsing and execution
 * - Maintenance issues
 * - Memory overhead
 *
 * Pattern: Collects inline scripts from $wp_scripts
 * Context: Requires admin context, uses global $wp_scripts
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance
 * @philosophy  #7 Ridiculously Good - Optimize everything, even admin
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin Inline JavaScript Block Count
 *
 * Checks if admin has too many inline JS blocks (> 10)
 *
 * @verified Not yet tested
 */
class Test_Admin_Inline_JS_Blocks extends Diagnostic_Base
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

		global $wp_scripts;

		// Ensure wp_scripts is initialized
		if (! isset($wp_scripts) || ! is_object($wp_scripts)) {
			return null;
		}

		$inline_block_count = 0;
		$total_inline_size = 0;
		$handles_with_inline = array();
		$large_blocks = array(); // Blocks > 10KB

		// Check inline scripts added to enqueued scripts
		foreach ($wp_scripts->queue ?? array() as $handle) {
			if (! isset($wp_scripts->registered[$handle])) {
				continue;
			}

			$script_obj = $wp_scripts->registered[$handle];

			// Check for inline scripts (extra data)
			if (! empty($script_obj->extra['after'])) {
				$inline_scripts = is_array($script_obj->extra['after'])
					? implode('', $script_obj->extra['after'])
					: $script_obj->extra['after'];

				$size = strlen($inline_scripts);
				if ($size > 0) {
					$total_inline_size += $size;
					$inline_block_count++;
					$handles_with_inline[] = $handle;

					// Track particularly large blocks
					if ($size > 10240) { // > 10KB
						$large_blocks[] = array(
							'handle' => $handle,
							'size_kb' => round($size / 1024, 2),
						);
					}
				}
			}

			// Also check 'before' inline scripts
			if (! empty($script_obj->extra['before'])) {
				$inline_scripts = is_array($script_obj->extra['before'])
					? implode('', $script_obj->extra['before'])
					: $script_obj->extra['before'];

				$size = strlen($inline_scripts);
				if ($size > 0) {
					$total_inline_size += $size;
					$inline_block_count++;

					if ($size > 10240 && ! in_array($handle, array_column($large_blocks, 'handle'), true)) {
						$large_blocks[] = array(
							'handle' => $handle,
							'size_kb' => round($size / 1024, 2),
						);
					}
				}
			}

			// Check for localized scripts (wp_localize_script data)
			if (! empty($script_obj->extra['data'])) {
				$size = strlen($script_obj->extra['data']);
				if ($size > 0) {
					$total_inline_size += $size;
					$inline_block_count++;
				}
			}
		}

		// Threshold: More than 10 inline JS blocks is excessive
		$threshold = 10;

		if ($inline_block_count <= $threshold) {
			return null; // Pass
		}

		// Convert total size to human-readable
		$size_kb = round($total_inline_size / 1024, 2);

		return array(
			'id'           => 'admin-inline-js-blocks',
			'title'        => 'Too Many Inline JavaScript Blocks in Admin',
			'description'  => sprintf(
				'WordPress admin has %d inline JavaScript blocks totaling %s KB. Inline scripts cannot be cached by browsers and slow parsing. Recommended: Under %d blocks. Consider consolidating or moving to external JS files.',
				$inline_block_count,
				$size_kb,
				$threshold
			)
			'kb_link'      => 'https://wpshadow.com/kb/reduce-inline-javascript',
			'training_link' => 'https://wpshadow.com/training/optimize-js-delivery',
			'auto_fixable' => false,
			'threat_level' => 40, // Medium priority
			'module'       => 'admin-performance',
			'priority'     => 8,
			'meta'         => array(
				'block_count'      => $inline_block_count,
				'total_size_bytes' => $total_inline_size,
				'total_size_kb'    => $size_kb,
				'threshold'        => $threshold,
				'large_blocks'     => $large_blocks, // Blocks > 10KB
				'top_handles'      => array_slice($handles_with_inline, 0, 5),
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
			'name'        => 'Admin Inline JavaScript Blocks',
			'category'    => 'admin-performance',
			'severity'    => 'medium',
			'description' => 'Detects excessive inline JavaScript that cannot be cached',
		);
	}
}
