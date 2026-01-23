<?php

declare(strict_types=1);
/**
 * Test: Favicon Check
 *
 * Tests if site has proper favicon configuration.
 *
 * Philosophy: Inspire confidence (#8) - Professional branding builds trust
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Design_Favicon extends Diagnostic_Base
{

	protected static $slug = 'test-design-favicon';
	protected static $title = 'Favicon Test';
	protected static $description = 'Tests for favicon presence and formats';

	/**
	 * Common favicon sizes
	 */
	const RECOMMENDED_SIZES = [16, 32, 180, 192];

	/**
	 * Run the diagnostic check
	 *
	 * PASS (returns null): Favicon exists and is properly configured
	 * FAIL (returns array): Missing favicon or incomplete configuration
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
	 * Run comprehensive favicon tests
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test results
	 */
	public static function run_favicon_tests(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));

		if ($html === false) {
			return [
				'success' => false,
				'error' => 'Could not fetch HTML',
				'url' => $url ?? home_url('/'),
			];
		}

		$favicons = self::extract_favicons($html);

		return [
			'success' => true,
			'url' => $url ?? home_url('/'),
			'favicon_links' => $favicons,
			'tests' => [
				'has_favicon' => self::test_has_favicon($html),
				'has_apple_touch_icon' => self::test_has_apple_touch_icon($html),
				'has_multiple_sizes' => self::test_has_multiple_sizes($html),
				'has_svg_favicon' => self::test_has_svg_favicon($html),
			],
			'summary' => [
				'passed' => !empty($favicons['standard']),
				'total_favicons' => count($favicons['standard']) + count($favicons['apple']) + count($favicons['other']),
				'has_modern_formats' => !empty($favicons['svg']),
			],
		];
	}

	/**
	 * Test if favicon exists
	 */
	public static function test_has_favicon(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$favicons = self::extract_favicons($html);

		$has_favicon = !empty($favicons['standard']) || !empty($favicons['shortcut']);

		return [
			'test' => 'has_favicon',
			'passed' => $has_favicon,
			'count' => count($favicons['standard']),
			'message' => $has_favicon
				? 'Favicon link found'
				: 'No favicon link found',
			'impact' => 'Favicon appears in browser tabs and bookmarks, improving brand recognition',
		];
	}

	/**
	 * Test for Apple Touch Icon
	 */
	public static function test_has_apple_touch_icon(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$favicons = self::extract_favicons($html);

		return [
			'test' => 'has_apple_touch_icon',
			'passed' => !empty($favicons['apple']),
			'count' => count($favicons['apple']),
			'message' => !empty($favicons['apple'])
				? 'Apple Touch Icon present (good for iOS)'
				: 'No Apple Touch Icon (recommended for iOS devices)',
			'impact' => 'Apple Touch Icon appears when users save site to iOS home screen',
		];
	}

	/**
	 * Test for multiple favicon sizes
	 */
	public static function test_has_multiple_sizes(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$favicons = self::extract_favicons($html);

		$sizes = self::extract_sizes($favicons);
		$has_multiple = count($sizes) > 1;

		return [
			'test' => 'has_multiple_sizes',
			'passed' => $has_multiple,
			'sizes_found' => $sizes,
			'recommended_sizes' => self::RECOMMENDED_SIZES,
			'message' => $has_multiple
				? sprintf('Multiple sizes found: %s', implode(', ', $sizes))
				: 'Single size only (consider adding multiple sizes)',
			'impact' => 'Multiple sizes ensure sharp icons across different devices',
		];
	}

	/**
	 * Test for SVG favicon (modern format)
	 */
	public static function test_has_svg_favicon(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$favicons = self::extract_favicons($html);

		return [
			'test' => 'has_svg_favicon',
			'passed' => !empty($favicons['svg']),
			'message' => !empty($favicons['svg'])
				? 'SVG favicon present (modern, scalable)'
				: 'No SVG favicon (recommended for modern browsers)',
			'impact' => 'SVG favicons scale perfectly and support dark mode',
		];
	}

	/**
	 * Analyze HTML for favicon issues
	 *
	 * @param string $html HTML content
	 * @param string $checked_url URL that was checked
	 * @return array|null Finding or null
	 */
	protected static function analyze_html(string $html, string $checked_url): ?array
	{
		$favicons = self::extract_favicons($html);

		// Missing favicon = FAIL
		if (empty($favicons['standard']) && empty($favicons['shortcut'])) {
			return [
				'id' => 'design-favicon',
				'title' => 'Missing Favicon',
				'description' => 'Your site is missing a favicon. Favicons appear in browser tabs, bookmarks, and search results, helping users identify your site at a glance.',
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/favicon/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
				'training_link' => 'https://wpshadow.com/training/branding-basics/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'Design',
				'priority' => 3,
				'meta' => [
					'issue' => 'missing',
					'has_apple_icon' => !empty($favicons['apple']),
					'checked_url' => $checked_url,
				],
			];
		}

		// Check for recommended improvements
		$recommendations = [];

		if (empty($favicons['apple'])) {
			$recommendations[] = 'Add Apple Touch Icon for iOS devices';
		}

		$sizes = self::extract_sizes($favicons);
		if (count($sizes) < 2) {
			$recommendations[] = 'Add multiple favicon sizes';
		}

		if (empty($favicons['svg'])) {
			$recommendations[] = 'Consider adding SVG favicon for modern browsers';
		}

		// Has favicon but could improve = warning (or pass if no major issues)
		if (count($recommendations) <= 1) {
			return null; // PASS (good enough)
		}

		return [
			'id' => 'design-favicon',
			'title' => 'Favicon Could Be Improved',
			'description' => sprintf(
				'Your site has a basic favicon but is missing %d recommended improvements: %s.',
				count($recommendations),
				implode('; ', $recommendations)
			),
			'color' => '#ff9800',
			'bg_color' => '#fff3e0',
			'kb_link' => 'https://wpshadow.com/kb/favicon/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
			'training_link' => 'https://wpshadow.com/training/branding-basics/',
			'auto_fixable' => false,
			'threat_level' => 20,
			'module' => 'Design',
			'priority' => 3,
			'meta' => [
				'recommendations' => $recommendations,
				'current_favicons' => $favicons,
				'sizes_found' => $sizes,
				'checked_url' => $checked_url,
			],
		];
	}

	/**
	 * Extract all favicon links from HTML
	 *
	 * @param string $html HTML content
	 * @return array Favicon links by type
	 */
	protected static function extract_favicons(string $html): array
	{
		if (empty($html)) {
			return [
				'standard' => [],
				'shortcut' => [],
				'apple' => [],
				'svg' => [],
				'other' => [],
			];
		}

		$favicons = [
			'standard' => [],
			'shortcut' => [],
			'apple' => [],
			'svg' => [],
			'other' => [],
		];

		// Standard favicon
		preg_match_all('/<link\s+([^>]*rel=["\']icon["\'][^>]*)>/i', $html, $matches);
		foreach ($matches[1] as $attrs) {
			$href = self::extract_href($attrs);
			$type = self::extract_type($attrs);
			$sizes = self::extract_sizes_attr($attrs);

			if (strpos($type, 'svg') !== false) {
				$favicons['svg'][] = ['href' => $href, 'type' => $type, 'sizes' => $sizes];
			} else {
				$favicons['standard'][] = ['href' => $href, 'type' => $type, 'sizes' => $sizes];
			}
		}

		// Shortcut icon (legacy)
		preg_match_all('/<link\s+([^>]*rel=["\']shortcut\s+icon["\'][^>]*)>/i', $html, $matches);
		foreach ($matches[1] as $attrs) {
			$favicons['shortcut'][] = [
				'href' => self::extract_href($attrs),
				'type' => self::extract_type($attrs),
			];
		}

		// Apple Touch Icon
		preg_match_all('/<link\s+([^>]*rel=["\']apple-touch-icon[^"\']*["\'][^>]*)>/i', $html, $matches);
		foreach ($matches[1] as $attrs) {
			$favicons['apple'][] = [
				'href' => self::extract_href($attrs),
				'sizes' => self::extract_sizes_attr($attrs),
			];
		}

		return $favicons;
	}

	/**
	 * Extract href from link attributes
	 *
	 * @param string $attrs Link attributes
	 * @return string
	 */
	protected static function extract_href(string $attrs): string
	{
		if (preg_match('/href\s*=\s*["\']([^"\']+)["\']/', $attrs, $match)) {
			return $match[1];
		}
		return '';
	}

	/**
	 * Extract type from link attributes
	 *
	 * @param string $attrs Link attributes
	 * @return string
	 */
	protected static function extract_type(string $attrs): string
	{
		if (preg_match('/type\s*=\s*["\']([^"\']+)["\']/', $attrs, $match)) {
			return $match[1];
		}
		return '';
	}

	/**
	 * Extract sizes attribute
	 *
	 * @param string $attrs Link attributes
	 * @return string
	 */
	protected static function extract_sizes_attr(string $attrs): string
	{
		if (preg_match('/sizes\s*=\s*["\']([^"\']+)["\']/', $attrs, $match)) {
			return $match[1];
		}
		return '';
	}

	/**
	 * Extract all unique sizes from favicon data
	 *
	 * @param array $favicons Favicon data
	 * @return array Unique sizes
	 */
	protected static function extract_sizes(array $favicons): array
	{
		$sizes = [];

		foreach ($favicons as $type => $items) {
			foreach ($items as $item) {
				if (!empty($item['sizes'])) {
					$size_parts = explode('x', $item['sizes']);
					if (isset($size_parts[0]) && is_numeric($size_parts[0])) {
						$sizes[] = (int)$size_parts[0];
					}
				}
			}
		}

		return array_unique($sizes);
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
			'user-agent' => 'WPShadow-Diagnostic/1.0 (Design Checker)',
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
			'id' => 'design-favicon',
			'title' => $title,
			'description' => $description,
			'color' => '#ff5722',
			'bg_color' => '#ffebee',
			'kb_link' => 'https://wpshadow.com/kb/favicon/',
			'training_link' => 'https://wpshadow.com/training/branding-basics/',
			'auto_fixable' => false,
			'threat_level' => 20,
			'module' => 'Design',
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
		return __('Favicon Check', 'wpshadow');
	}

	/**
	 * Get the description for display
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return __('Checks HTML for proper favicon configuration (brand identity in browser tabs).', 'wpshadow');
	}
}
