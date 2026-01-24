<?php

/**
 * WPShadow Admin Diagnostic Test: Excessive External Dependencies in Admin
 *
 * Tests if wp-admin loads resources from too many external domains, which can cause:
 * - Single Point of Failure (SPOF) - if external site is down
 * - Privacy concerns - data leakage to third parties
 * - Slower load times due to DNS lookups
 * - GDPR compliance issues
 *
 * Pattern: Analyzes enqueued asset sources for external domains
 * Context: Requires admin context, uses $wp_styles and $wp_scripts
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance & Privacy
 * @philosophy  #10 Beyond Pure - Privacy-first, limit external dependencies
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin External Dependencies
 *
 * Checks if admin loads from too many external domains (> 5)
 *
 * @verified Not yet tested
 */
class Test_Admin_External_Dependencies extends Diagnostic_Base
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

		$external_domains = array();
		$local_domain = wp_parse_url(home_url(), PHP_URL_HOST);

		// Check CSS dependencies
		if (isset($wp_styles) && is_object($wp_styles)) {
			foreach ($wp_styles->queue ?? array() as $handle) {
				if (! isset($wp_styles->registered[$handle])) {
					continue;
				}

				$src = $wp_styles->registered[$handle]->src ?? '';
				$domain = $this->extract_domain($src, $local_domain);

				if ($domain && ! in_array($domain, $external_domains, true)) {
					$external_domains[] = $domain;
				}
			}
		}

		// Check JavaScript dependencies
		if (isset($wp_scripts) && is_object($wp_scripts)) {
			foreach ($wp_scripts->queue ?? array() as $handle) {
				if (! isset($wp_scripts->registered[$handle])) {
					continue;
				}

				$src = $wp_scripts->registered[$handle]->src ?? '';
				$domain = $this->extract_domain($src, $local_domain);

				if ($domain && ! in_array($domain, $external_domains, true)) {
					$external_domains[] = $domain;
				}
			}
		}

		$external_count = count($external_domains);

		// Threshold: More than 5 external domains is excessive
		$threshold = 5;

		if ($external_count <= $threshold) {
			return null; // Pass
		}

		// Identify common external services
		$categorized = $this->categorize_domains($external_domains);

		return array(
			'id'           => 'admin-external-dependencies',
			'title'        => 'Too Many External Dependencies in Admin',
			'description'  => sprintf(
				'WordPress admin loads resources from %d external domains. This creates privacy concerns and Single Points of Failure (SPOF). Recommended: Under %d external domains. Consider self-hosting critical assets.',
				$external_count,
				$threshold
			)
			'kb_link'      => 'https://wpshadow.com/kb/reduce-external-dependencies',
			'training_link' => 'https://wpshadow.com/training/self-host-assets',
			'auto_fixable' => false,
			'threat_level' => 42, // Medium priority - privacy + performance
			'module'       => 'admin-performance',
			'priority'     => 10,
			'meta'         => array(
				'external_count' => $external_count,
				'threshold'      => $threshold,
				'domains'        => $external_domains,
				'categorized'    => $categorized,
			),
		);
	}

	/**
	 * Extract domain from URL if it's external
	 *
	 * @param string $url URL to check
	 * @param string $local_domain Local site domain
	 * @return string|null External domain or null if local/invalid
	 */
	private function extract_domain(string $url, string $local_domain): ?string
	{
		// Skip empty URLs
		if (empty($url)) {
			return null;
		}

		// Handle protocol-relative URLs
		if (strpos($url, '//') === 0) {
			$url = 'https:' . $url;
		}

		// Parse URL
		$parsed = wp_parse_url($url);

		if (empty($parsed['host'])) {
			return null; // Relative URL, not external
		}

		$domain = $parsed['host'];

		// Skip if it's the local domain
		if ($domain === $local_domain || strpos($domain, $local_domain) !== false) {
			return null;
		}

		return $domain;
	}

	/**
	 * Categorize external domains by type
	 *
	 * @param array $domains List of domains
	 * @return array Categorized domains
	 */
	private function categorize_domains(array $domains): array
	{
		$categories = array(
			'fonts'       => array(),
			'analytics'   => array(),
			'cdn'         => array(),
			'social'      => array(),
			'advertising' => array(),
			'other'       => array(),
		);

		foreach ($domains as $domain) {
			if (strpos($domain, 'fonts.g') !== false || strpos($domain, 'typekit') !== false) {
				$categories['fonts'][] = $domain;
			} elseif (strpos($domain, 'google-analytics') !== false || strpos($domain, 'googletagmanager') !== false) {
				$categories['analytics'][] = $domain;
			} elseif (strpos($domain, 'cloudflare') !== false || strpos($domain, 'jsdelivr') !== false || strpos($domain, 'cdnjs') !== false) {
				$categories['cdn'][] = $domain;
			} elseif (strpos($domain, 'facebook') !== false || strpos($domain, 'twitter') !== false || strpos($domain, 'gravatar') !== false) {
				$categories['social'][] = $domain;
			} elseif (strpos($domain, 'doubleclick') !== false || strpos($domain, 'adsense') !== false) {
				$categories['advertising'][] = $domain;
			} else {
				$categories['other'][] = $domain;
			}
		}

		// Remove empty categories
		return array_filter($categories);
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Diagnostic information
	 */
	public static function get_info(): array
	{
		return array(
			'name'        => 'Admin External Dependencies',
			'category'    => 'admin-performance',
			'severity'    => 'medium',
			'description' => 'Detects excessive external domain dependencies causing SPOF and privacy concerns',
		);
	}
}
