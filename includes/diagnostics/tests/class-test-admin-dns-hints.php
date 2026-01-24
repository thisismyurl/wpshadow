<?php

/**
 * WPShadow Admin Diagnostic Test: Excessive DNS Prefetch Hints
 *
 * Tests if admin has too many DNS prefetch/preconnect hints, which causes:
 * - Wasted DNS lookups (browser overhead)
 * - Diminishing returns (browsers limit parallel lookups)
 * - Privacy concerns (reveals third-party services used)
 * - No actual performance benefit beyond 3-4 hints
 *
 * Pattern: Buffers admin_head and counts dns-prefetch/preconnect links
 * Context: Requires admin context
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance
 * @philosophy  #10 Beyond Pure - Minimal external connections
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Excessive DNS Hints
 *
 * Detects too many dns-prefetch/preconnect hints (> 4)
 *
 * @verified Not yet tested
 */
class Test_Admin_DNS_Hints extends Diagnostic_Base
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

		$dns_hints = array();

		// Buffer admin_head output to capture hints
		ob_start();
		do_action('admin_head');
		$head_content = ob_get_clean();

		// Find dns-prefetch links
		preg_match_all('/<link[^>]+rel=["\']dns-prefetch["\'][^>]+href=["\']([^"\']+)["\'][^>]*>/i', $head_content, $prefetch_matches);
		foreach ($prefetch_matches[1] ?? array() as $href) {
			$dns_hints[] = array(
				'type' => 'dns-prefetch',
				'href' => $href,
			);
		}

		// Find preconnect links
		preg_match_all('/<link[^>]+rel=["\']preconnect["\'][^>]+href=["\']([^"\']+)["\'][^>]*>/i', $head_content, $preconnect_matches);
		foreach ($preconnect_matches[1] ?? array() as $href) {
			$dns_hints[] = array(
				'type' => 'preconnect',
				'href' => $href,
			);
		}

		// Also check for hints added via wp_resource_hints filter
		$resource_hints = apply_filters('wp_resource_hints', array(), 'dns-prefetch');
		foreach ($resource_hints as $hint) {
			if (is_string($hint)) {
				$dns_hints[] = array(
					'type' => 'dns-prefetch (filter)',
					'href' => $hint,
				);
			}
		}

		$hint_count = count($dns_hints);

		// Threshold: More than 4 hints has diminishing returns
		// Browsers typically limit to 6 parallel DNS lookups
		$threshold = 4;

		if ($hint_count <= $threshold) {
			return null; // Pass
		}

		// Identify unique domains
		$domains = array();
		foreach ($dns_hints as $hint) {
			$parsed = wp_parse_url($hint['href']);
			if (! empty($parsed['host'])) {
				$domains[] = $parsed['host'];
			}
		}
		$unique_domains = array_unique($domains);

		return array(
			'id'           => 'admin-dns-hints',
			'title'        => 'Excessive DNS Prefetch/Preconnect Hints',
			'description'  => sprintf(
				'WordPress admin <head> contains %d DNS prefetch or preconnect hints pointing to %d unique domains. Browsers limit parallel DNS lookups, so hints beyond 3-4 provide no benefit and waste browser resources. Recommended: Keep hints to 3-4 critical external domains only.',
				$hint_count,
				count($unique_domains)
			)
			'kb_link'      => 'https://wpshadow.com/kb/optimize-dns-hints',
			'training_link' => 'https://wpshadow.com/training/resource-hints-best-practices',
			'auto_fixable' => false,
			'threat_level' => 32,
			'module'       => 'admin-performance',
			'priority'     => 22,
			'meta'         => array(
				'hint_count'      => $hint_count,
				'threshold'       => $threshold,
				'unique_domains'  => count($unique_domains),
				'domains'         => array_slice($unique_domains, 0, 10),
				'sample_hints'    => array_slice($dns_hints, 0, 5),
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
			'name'        => 'Excessive DNS Hints',
			'category'    => 'admin-performance',
			'severity'    => 'low',
			'description' => 'Detects too many dns-prefetch/preconnect hints',
		);
	}
}
