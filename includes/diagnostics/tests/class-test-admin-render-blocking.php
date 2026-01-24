<?php

/**
 * WPShadow Admin Diagnostic Test: Render-Blocking Resources
 *
 * Tests if wp-admin loads CSS/JS in head without async/defer, which causes:
 * - Delayed First Contentful Paint (FCP)
 * - Blocked page rendering until resources download
 * - Poor perceived performance
 * - Slow initial admin page load
 *
 * Pattern: Checks enqueued scripts/styles for render-blocking attributes
 * Context: Requires admin context, uses $wp_styles and $wp_scripts
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance
 * @philosophy  #7 Ridiculously Good - Fast initial page loads
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin Render-Blocking Resources
 *
 * Checks for CSS/JS loaded in head without optimization
 *
 * @verified Not yet tested
 */
class Test_Admin_Render_Blocking extends Diagnostic_Base
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

		global $wp_styles, $wp_scripts;

		$blocking_resources = array();

		// Check JavaScript files for defer/async
		if (isset($wp_scripts) && is_object($wp_scripts)) {
			foreach ($wp_scripts->queue ?? array() as $handle) {
				if (! isset($wp_scripts->registered[$handle])) {
					continue;
				}

				$script = $wp_scripts->registered[$handle];

				// Check if script is in footer (non-blocking)
				$in_footer = ! empty($script->extra['group']) && $script->extra['group'] === 1;

				// Check for async/defer strategy
				$has_strategy = ! empty($script->extra['strategy']) &&
					in_array($script->extra['strategy'], array('async', 'defer'), true);

				// If script is in head without async/defer, it's blocking
				if (! $in_footer && ! $has_strategy) {
					$blocking_resources[] = array(
						'type'   => 'script',
						'handle' => $handle,
						'src'    => $script->src ?? '',
					);
				}
			}
		}

		// Check CSS files
		// Note: CSS is generally render-blocking unless using media="print" or preload
		if (isset($wp_styles) && is_object($wp_styles)) {
			foreach ($wp_styles->queue ?? array() as $handle) {
				if (! isset($wp_styles->registered[$handle])) {
					continue;
				}

				$style = $wp_styles->registered[$handle];
				$media = $style->args ?? 'all';

				// Only count styles that block rendering (media="all" or "screen")
				if (in_array($media, array('all', 'screen'), true)) {
					$blocking_resources[] = array(
						'type'   => 'stylesheet',
						'handle' => $handle,
						'src'    => $style->src ?? '',
						'media'  => $media,
					);
				}
			}
		}

		$blocking_count = count($blocking_resources);
		$script_count = count(array_filter($blocking_resources, fn($r) => $r['type'] === 'script'));
		$style_count = count(array_filter($blocking_resources, fn($r) => $r['type'] === 'stylesheet'));

		// Threshold: More than 8 render-blocking resources is concerning
		// (WordPress core typically has 4-6)
		$threshold = 8;

		if ($blocking_count <= $threshold) {
			return null; // Pass
		}

		return array(
			'id'           => 'admin-render-blocking',
			'title'        => 'Render-Blocking Resources in Admin',
			'description'  => sprintf(
				'WordPress admin has %d render-blocking resources (%d scripts, %d stylesheets) that delay page rendering. Recommended: Use async/defer for scripts, load non-critical CSS asynchronously.',
				$blocking_count,
				$script_count,
				$style_count
			)
			'kb_link'      => 'https://wpshadow.com/kb/reduce-render-blocking',
			'training_link' => 'https://wpshadow.com/training/optimize-asset-loading',
			'auto_fixable' => false,
			'threat_level' => 42,
			'module'       => 'admin-performance',
			'priority'     => 14,
			'meta'         => array(
				'blocking_count' => $blocking_count,
				'script_count'   => $script_count,
				'style_count'    => $style_count,
				'threshold'      => $threshold,
				'sample_scripts' => array_slice(
					array_filter($blocking_resources, fn($r) => $r['type'] === 'script'),
					0,
					3
				),
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
			'name'        => 'Admin Render-Blocking Resources',
			'category'    => 'admin-performance',
			'severity'    => 'medium',
			'description' => 'Detects scripts/styles blocking initial page render',
		);
	}
}
