<?php

declare(strict_types=1);
/**
 * Test: Broken Internal Links Check
 *
 * Tests if HTML contains broken internal links (basic check).
 *
 * Philosophy: Inspire confidence (#8) - Working links build trust and professionalism
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Broken_Links extends Diagnostic_Base
{

	protected static $slug = 'test-seo-broken-links';
	protected static $title = 'Broken Links Test';
	protected static $description = 'Tests for potentially broken internal links';

	/**
	 * Maximum links to check (performance limit)
	 */
	const MAX_LINKS_TO_CHECK = 20;

	/**
	 * Run the diagnostic check
	 *
	 * PASS (returns null): No obviously broken internal links
	 * FAIL (returns array): Found potentially broken links
	 *
	 * @param string|null $url URL to test (defaults to homepage)
	 * @param string|null $html Pre-fetched HTML to analyze
	 * @return array|null Finding data or null if no issue
	 */
	public static function check(?string $url = null, ?string $html = null): ?array
	{
		if ($html !== null) {
			return self::analyze_html($html, $url ?? 'provided-html');
		}

		$site_url = $url ?? home_url('/');

		if ($url !== null && !self::is_internal_url($url)) {
			return self::error_result('Invalid URL', 'URL must be from this WordPress site');
		}

		$html = self::fetch_html($site_url);
		if ($html === false) {
			return self::error_result('Fetch Failed', 'Could not retrieve page HTML');
		}

		return self::analyze_html($html, $site_url);
	}

	/**
	 * Run comprehensive broken link tests
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test results
	 */
	public static function run_broken_link_tests(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));

		if ($html === false) {
			return [
				'success' => false,
				'error' => 'Could not fetch HTML',
				'url' => $url ?? home_url('/'),
			];
		}

		$links = self::extract_internal_links($html);
		$suspicious = self::find_suspicious_links($links);

		return [
			'success' => true,
			'url' => $url ?? home_url('/'),
			'total_internal_links' => count($links),
			'suspicious_links' => $suspicious,
			'tests' => [
				'no_empty_hrefs' => self::test_no_empty_hrefs($html),
				'no_javascript_void' => self::test_no_javascript_void($html),
				'no_hash_only' => self::test_no_hash_only($html),
				'valid_anchors' => self::test_valid_anchors($html),
			],
			'summary' => [
				'passed' => empty($suspicious),
				'suspicious_count' => count($suspicious),
			],
		];
	}

	/**
	 * Test for empty href attributes
	 */
	public static function test_no_empty_hrefs(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$empty = self::find_empty_hrefs($html);

		return [
			'test' => 'no_empty_hrefs',
			'passed' => empty($empty),
			'count' => count($empty),
			'message' => empty($empty)
				? 'No empty href attributes'
				: sprintf('%d links with empty href', count($empty)),
			'impact' => 'Empty hrefs create non-functional links',
		];
	}

	/**
	 * Test for javascript:void links
	 */
	public static function test_no_javascript_void(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));

		preg_match_all('/href\s*=\s*["\']javascript:void\(/i', $html, $matches);
		$count = count($matches[0]);

		return [
			'test' => 'no_javascript_void',
			'passed' => $count === 0,
			'count' => $count,
			'message' => $count === 0
				? 'No javascript:void links'
				: sprintf('%d javascript:void links (use buttons instead)', $count),
			'impact' => 'javascript:void links are inaccessible and bad for SEO',
		];
	}

	/**
	 * Test for hash-only links (without id target)
	 */
	public static function test_no_hash_only(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$orphan_hashes = self::find_orphan_hash_links($html);

		return [
			'test' => 'no_hash_only',
			'passed' => empty($orphan_hashes),
			'count' => count($orphan_hashes),
			'orphan_links' => array_slice($orphan_hashes, 0, 5),
			'message' => empty($orphan_hashes)
				? 'All anchor links have targets'
				: sprintf('%d anchor links without matching IDs', count($orphan_hashes)),
			'impact' => 'Anchor links without targets do nothing when clicked',
		];
	}

	/**
	 * Test for valid anchor targets
	 */
	public static function test_valid_anchors(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$orphan_hashes = self::find_orphan_hash_links($html);

		return [
			'test' => 'valid_anchors',
			'passed' => empty($orphan_hashes),
			'orphan_count' => count($orphan_hashes),
			'message' => empty($orphan_hashes)
				? 'All anchor links point to valid elements'
				: sprintf('%d anchor links missing target elements', count($orphan_hashes)),
			'impact' => 'Invalid anchors create broken navigation',
		];
	}

	/**
	 * Analyze HTML for broken link issues
	 *
	 * @param string $html HTML content
	 * @param string $checked_url URL that was checked
	 * @return array|null Finding or null
	 */
	protected static function analyze_html(string $html, string $checked_url): ?array
	{
		$suspicious = self::find_suspicious_links(self::extract_internal_links($html));
		$empty_hrefs = self::find_empty_hrefs($html);
		$orphan_hashes = self::find_orphan_hash_links($html);

		$total_issues = count($suspicious) + count($empty_hrefs) + count($orphan_hashes);

		// No issues = PASS
		if ($total_issues === 0) {
			return null; // PASS
		}

		// Collect all issues
		$issues = [];

		if (!empty($empty_hrefs)) {
			$issues[] = sprintf('%d empty href attributes', count($empty_hrefs));
		}

		if (!empty($orphan_hashes)) {
			$issues[] = sprintf('%d anchor links without targets', count($orphan_hashes));
		}

		if (!empty($suspicious)) {
			$issues[] = sprintf('%d suspicious link patterns', count($suspicious));
		}

		$threat_level = 40;
		if ($total_issues > 5) {
			$threat_level = 60;
		}

		return [
			'id' => 'seo-broken-links',
			'title' => 'Potentially Broken Links Found',
			'description' => sprintf(
				'Found %d potential link issues: %s. These may create a poor user experience and hurt SEO.',
				$total_issues,
				implode('; ', array_slice($issues, 0, 3))
			)
			'kb_link' => 'https://wpshadow.com/kb/broken-links/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
			'training_link' => 'https://wpshadow.com/training/link-maintenance/',
			'auto_fixable' => false,
			'threat_level' => $threat_level,
			'module' => 'SEO',
			'priority' => 2,
			'meta' => [
				'total_issues' => $total_issues,
				'empty_hrefs' => count($empty_hrefs),
				'orphan_hashes' => count($orphan_hashes),
				'suspicious_patterns' => count($suspicious),
				'sample_issues' => array_merge(
					array_slice($empty_hrefs, 0, 3),
					array_slice($orphan_hashes, 0, 3)
				),
				'checked_url' => $checked_url,
			],
		];
	}

	/**
	 * Extract internal links from HTML
	 *
	 * @param string $html HTML content
	 * @return array Internal links
	 */
	protected static function extract_internal_links(string $html): array
	{
		if (empty($html)) {
			return [];
		}

		preg_match_all('/<a\s+([^>]*href=["\']([^"\']+)["\'][^>]*)>/i', $html, $matches, PREG_SET_ORDER);

		$site_url = home_url();
		$links = [];

		foreach ($matches as $match) {
			$href = $match[2];

			// Filter for internal links only
			if (strpos($href, $site_url) === 0 || strpos($href, '/') === 0) {
				$links[] = $href;
			}
		}

		return array_slice($links, 0, self::MAX_LINKS_TO_CHECK); // Limit for performance
	}

	/**
	 * Find suspicious link patterns
	 *
	 * @param array $links Links to check
	 * @return array Suspicious links
	 */
	protected static function find_suspicious_links(array $links): array
	{
		$suspicious = [];

		foreach ($links as $link) {
			// Very suspicious patterns
			if (empty($link) || $link === '#' || $link === 'javascript:void(0)') {
				$suspicious[] = $link;
			}
		}

		return $suspicious;
	}

	/**
	 * Find empty href attributes
	 *
	 * @param string $html HTML content
	 * @return array Empty hrefs
	 */
	protected static function find_empty_hrefs(string $html): array
	{
		if (empty($html)) {
			return [];
		}

		preg_match_all('/<a\s+[^>]*href=["\']["\'][^>]*>/i', $html, $matches);
		return $matches[0] ?? [];
	}

	/**
	 * Find orphan hash links (no matching id)
	 *
	 * @param string $html HTML content
	 * @return array Orphan hash links
	 */
	protected static function find_orphan_hash_links(string $html): array
	{
		if (empty($html)) {
			return [];
		}

		// Extract all hash links
		preg_match_all('/href\s*=\s*["\']#([^"\']+)["\']/i', $html, $href_matches);
		$hash_links = array_unique($href_matches[1] ?? []);

		// Extract all IDs
		preg_match_all('/\bid\s*=\s*["\']([^"\']+)["\']/i', $html, $id_matches);
		$ids = array_unique($id_matches[1] ?? []);

		// Find orphans (hash links without matching id)
		$orphans = [];
		foreach ($hash_links as $hash) {
			if (!in_array($hash, $ids, true)) {
				$orphans[] = '#' . $hash;
			}
		}

		return $orphans;
	}

	/**
	 * Fetch HTML from URL
	 *
	 * @param string $url URL to fetch
	 * @return string|false HTML or false on error
	 */
	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, [
			'timeout' => 10,
			'user-agent' => 'WPShadow-Diagnostic/1.0 (SEO Checker)',
			'sslverify' => false,
		]);

		if (is_wp_error($response)) {
			return false;
		}

		return wp_remote_retrieve_body($response);
	}

	/**
	 * Check if URL is internal
	 *
	 * @param string $url URL to check
	 * @return bool
	 */
	protected static function is_internal_url(string $url): bool
	{
		$site_host = wp_parse_url(home_url(), PHP_URL_HOST);
		$test_host = wp_parse_url($url, PHP_URL_HOST);
		return $site_host === $test_host;
	}

	/**
	 * Generate error result
	 *
	 * @param string $title Error title
	 * @param string $description Error description
	 * @return array Error result
	 */
	protected static function error_result(string $title, string $description): array
	{
		return [
			'id' => 'seo-broken-links',
			'title' => $title,
			'description' => $description
			'kb_link' => 'https://wpshadow.com/kb/broken-links/',
			'training_link' => 'https://wpshadow.com/training/link-maintenance/',
			'auto_fixable' => false,
			'threat_level' => 30,
			'module' => 'SEO',
			'priority' => 3,
		];
	}

	/**
	 * Get the name for display
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return __('Broken Links Check', 'wpshadow');
	}

	/**
	 * Get the description for display
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return __('Checks HTML for potentially broken internal links (empty hrefs, orphan anchors).', 'wpshadow');
	}
}
