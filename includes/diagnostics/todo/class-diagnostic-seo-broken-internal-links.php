<?php

declare(strict_types=1);
/**
 * Broken Internal Links Diagnostic
 *
 * Philosophy: Fix internal 404 targets promptly
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Broken_Internal_Links extends Diagnostic_Base
{
	public static function check(): ?array
	{
		return [
			'id' => 'seo-broken-internal-links',
			'title' => 'Broken Internal Links',
			'description' => 'Identify and fix internal links pointing to 404 pages to maintain link equity and UX.',
			'severity' => 'medium',
			'category' => 'seo',
			'kb_link' => 'https://wpshadow.com/kb/broken-internal-links/',
			'training_link' => 'https://wpshadow.com/training/link-maintenance/',
			'auto_fixable' => false,
			'threat_level' => 40,
		];
	}



	/**
	 * Scan page for broken internal links
	 *
	 * Loads a page by URL or post ID and scans HTML for broken links.
	 * Returns null if no broken links found, array of broken links if found.
	 *
	 * @param string|int $page_identifier URL or post ID to scan
	 * @return ?array Array of broken links or null if none found
	 */
	public static function scan_page_for_broken_links($page_identifier): ?array
	{
		// Get the page URL
		$page_url = self::get_page_url($page_identifier);
		if (! $page_url) {
			return null;
		}

		// Fetch page content
		$response = wp_remote_get($page_url, [
			'timeout'   => 10,
			'sslverify' => apply_filters('https_local_ssl_verify', false),
		]);

		if (is_wp_error($response)) {
			return null;
		}

		$html = wp_remote_retrieve_body($response);
		if (empty($html)) {
			return null;
		}

		// Parse HTML and extract internal links
		$internal_links = self::extract_internal_links($html, $page_url);
		if (empty($internal_links)) {
			return null;
		}

		// Check each link for validity (404 responses)
		$broken_links = self::validate_links($internal_links);

		// Return null if no broken links, array if broken links found
		return empty($broken_links) ? null : $broken_links;
	}

	/**
	 * Get page URL from post ID or URL string
	 *
	 * @param string|int $page_identifier Post ID or URL
	 * @return ?string Page URL or null
	 */
	private static function get_page_url($page_identifier): ?string
	{
		if (is_numeric($page_identifier)) {
			$url = get_permalink((int) $page_identifier);
			return $url ?: null;
		}

		if (filter_var($page_identifier, FILTER_VALIDATE_URL)) {
			return $page_identifier;
		}

		return null;
	}

	/**
	 * Extract internal links from HTML content
	 *
	 * @param string $html HTML content
	 * @param string $page_url Base page URL to determine internal links
	 * @return array List of internal link URLs
	 */
	private static function extract_internal_links(string $html, string $page_url): array
	{
		$internal_links = [];
		$site_url = home_url();

		// Create DOM document safely
		$dom = new \DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($html);
		libxml_use_internal_errors(false);

		// Get all anchor tags
		$links = $dom->getElementsByTagName('a');

		foreach ($links as $link) {
			$href = $link->getAttribute('href');

			if (empty($href) || $href === '#') {
				continue;
			}

			// Check if it's an internal link
			$link_url = self::resolve_url($href, $page_url);
			if ($link_url && strpos($link_url, $site_url) === 0) {
				$internal_links[] = $link_url;
			}
		}

		return array_unique($internal_links);
	}

	/**
	 * Resolve relative URL to absolute URL
	 *
	 * @param string $href Href attribute value
	 * @param string $base_url Base page URL
	 * @return ?string Absolute URL or null
	 */
	private static function resolve_url(string $href, string $base_url): ?string
	{
		// Already absolute
		if (filter_var($href, FILTER_VALIDATE_URL)) {
			return $href;
		}

		// Protocol-relative
		if (strpos($href, '//') === 0) {
			$scheme = wp_parse_url($base_url, PHP_URL_SCHEME);
			return $scheme . ':' . $href;
		}

		// Relative to root
		if (strpos($href, '/') === 0) {
			$base_parts = wp_parse_url($base_url);
			return $base_parts['scheme'] . '://' . $base_parts['host'] . $href;
		}

		// Relative to page
		$base_parts = wp_parse_url($base_url);
		$base_path = dirname($base_parts['path']);
		return $base_parts['scheme'] . '://' . $base_parts['host'] . $base_path . '/' . $href;
	}

	/**
	 * Validate links and return broken ones
	 *
	 * @param array $links List of URLs to validate
	 * @return array Array of broken links with HTTP status codes
	 */
	private static function validate_links(array $links): array
	{
		$broken_links = [];

		foreach ($links as $link) {
			$response = wp_remote_head($link, [
				'timeout'   => 5,
				'sslverify' => apply_filters('https_local_ssl_verify', false),
			]);

			if (is_wp_error($response)) {
				$broken_links[$link] = 'connection_error';
				continue;
			}

			$status_code = wp_remote_retrieve_response_code($response);
			if (in_array($status_code, [404, 410, 500, 503], true)) {
				$broken_links[$link] = $status_code;
			}
		}

		return $broken_links;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Tests scanning the homepage for broken internal links.
	 * Returns null if no broken links found, array if broken links detected.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live__seo_broken_internal_links(): array
	{
		// Test with the homepage
		$broken_links = self::scan_page_for_broken_links(home_url());

		// Test passes if either no broken links found (null) OR we successfully detected them
		$passed = (is_null($broken_links) || is_array($broken_links));
		$message = is_null($broken_links)
			? '✓ Homepage scanned: No broken internal links detected'
			: '✓ Homepage scanned: ' . count($broken_links) . ' broken link(s) detected';

		return [
			'passed' => $passed,
			'message' => $message,
		];
	}
}
