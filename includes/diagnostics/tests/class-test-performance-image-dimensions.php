<?php

declare(strict_types=1);
/**
 * Test: Image Dimensions Check
 *
 * Tests if images have explicit width/height attributes to prevent CLS.
 *
 * Philosophy: Inspire confidence (#8) - Help users create stable, professional pages
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Image_Dimensions extends Diagnostic_Base
{

	protected static $slug = 'test-performance-image-dimensions';
	protected static $title = 'Image Dimensions Test';
	protected static $description = 'Tests for explicit width/height on images (prevents CLS)';

	/**
	 * Run the diagnostic check
	 *
	 * PASS (returns null): All images have width/height attributes
	 * FAIL (returns array): Images missing dimensions
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
	 * Run comprehensive image dimensions tests
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test results
	 */
	public static function run_dimensions_tests(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));

		if ($html === false) {
			return [
				'success' => false,
				'error' => 'Could not fetch HTML',
				'url' => $url ?? home_url('/'),
			];
		}

		$analysis = self::analyze_images($html);

		return [
			'success' => true,
			'url' => $url ?? home_url('/'),
			'total_images' => $analysis['total'],
			'with_dimensions' => $analysis['with_dimensions'],
			'without_dimensions' => $analysis['without_dimensions'],
			'tests' => [
				'all_have_dimensions' => self::test_all_have_dimensions($html),
				'has_width' => self::test_has_width($html),
				'has_height' => self::test_has_height($html),
				'has_aspect_ratio' => self::test_has_aspect_ratio($html),
			],
			'summary' => [
				'passed' => $analysis['without_dimensions'] === 0,
				'compliance_rate' => $analysis['total'] > 0
					? round(($analysis['with_dimensions'] / $analysis['total']) * 100, 1)
					: 100,
			],
			'problematic_images' => $analysis['problematic'],
		];
	}

	/**
	 * Test if all images have dimensions
	 */
	public static function test_all_have_dimensions(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$analysis = self::analyze_images($html);

		return [
			'test' => 'all_have_dimensions',
			'passed' => $analysis['without_dimensions'] === 0,
			'total_images' => $analysis['total'],
			'missing_count' => $analysis['without_dimensions'],
			'message' => $analysis['without_dimensions'] === 0
				? 'All images have width/height attributes'
				: sprintf('%d images missing width/height attributes', $analysis['without_dimensions']),
			'impact' => 'Explicit dimensions prevent Cumulative Layout Shift (CLS)',
		];
	}

	/**
	 * Test for width attributes
	 */
	public static function test_has_width(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$analysis = self::analyze_images($html);

		return [
			'test' => 'has_width',
			'passed' => $analysis['missing_width'] === 0,
			'missing_count' => $analysis['missing_width'],
			'message' => $analysis['missing_width'] === 0
				? 'All images have width attribute'
				: sprintf('%d images missing width attribute', $analysis['missing_width']),
			'impact' => 'Width attribute helps browser reserve space before image loads',
		];
	}

	/**
	 * Test for height attributes
	 */
	public static function test_has_height(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$analysis = self::analyze_images($html);

		return [
			'test' => 'has_height',
			'passed' => $analysis['missing_height'] === 0,
			'missing_count' => $analysis['missing_height'],
			'message' => $analysis['missing_height'] === 0
				? 'All images have height attribute'
				: sprintf('%d images missing height attribute', $analysis['missing_height']),
			'impact' => 'Height attribute helps browser reserve space before image loads',
		];
	}

	/**
	 * Test for aspect-ratio CSS (modern alternative)
	 */
	public static function test_has_aspect_ratio(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$analysis = self::analyze_images($html);

		return [
			'test' => 'has_aspect_ratio',
			'passed' => true, // Informational
			'count' => $analysis['with_aspect_ratio'],
			'message' => $analysis['with_aspect_ratio'] > 0
				? sprintf('%d images use aspect-ratio CSS (modern alternative)', $analysis['with_aspect_ratio'])
				: 'No aspect-ratio CSS found (width/height attributes work too)',
			'impact' => 'aspect-ratio is a modern way to prevent CLS',
		];
	}

	/**
	 * Analyze HTML for image dimension issues
	 *
	 * @param string $html HTML content
	 * @param string $checked_url URL that was checked
	 * @return array|null Finding or null
	 */
	protected static function analyze_html(string $html, string $checked_url): ?array
	{
		$analysis = self::analyze_images($html);

		// No images = N/A
		if ($analysis['total'] === 0) {
			return null; // PASS (no images)
		}

		// All images have dimensions = PASS
		if ($analysis['without_dimensions'] === 0) {
			return null; // PASS
		}

		// Calculate severity
		$percentage = round(($analysis['without_dimensions'] / $analysis['total']) * 100);

		$threat_level = 40;
		if ($percentage > 50) {
			$threat_level = 60;
		}
		if ($percentage > 75) {
			$threat_level = 70;
		}

		return [
			'id' => 'performance-image-dimensions',
			'title' => 'Images Missing Dimensions',
			'description' => sprintf(
				'%d of %d images are missing explicit width/height attributes. This causes Cumulative Layout Shift (CLS) as images load, negatively impacting user experience and Core Web Vitals.',
				$analysis['without_dimensions'],
				$analysis['total']
			)
			'kb_link' => 'https://wpshadow.com/kb/image-dimensions-cls/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
			'training_link' => 'https://wpshadow.com/training/core-web-vitals/',
			'auto_fixable' => false,
			'threat_level' => $threat_level,
			'module' => 'Performance',
			'priority' => 1,
			'meta' => [
				'total_images' => $analysis['total'],
				'with_dimensions' => $analysis['with_dimensions'],
				'without_dimensions' => $analysis['without_dimensions'],
				'missing_width' => $analysis['missing_width'],
				'missing_height' => $analysis['missing_height'],
				'compliance_rate' => $analysis['total'] > 0
					? round(($analysis['with_dimensions'] / $analysis['total']) * 100, 1)
					: 100,
				'problematic_images' => array_slice($analysis['problematic'], 0, 10),
				'checked_url' => $checked_url,
			],
		];
	}

	/**
	 * Analyze all images for dimension attributes
	 *
	 * @param string $html HTML content
	 * @return array Analysis results
	 */
	protected static function analyze_images(string $html): array
	{
		if (empty($html)) {
			return [
				'total' => 0,
				'with_dimensions' => 0,
				'without_dimensions' => 0,
				'missing_width' => 0,
				'missing_height' => 0,
				'with_aspect_ratio' => 0,
				'problematic' => [],
			];
		}

		preg_match_all('/<img\s+([^>]+)>/i', $html, $matches);
		$images = $matches[1] ?? [];

		$total = count($images);
		$with_dimensions = 0;
		$missing_width = 0;
		$missing_height = 0;
		$with_aspect_ratio = 0;
		$problematic = [];

		foreach ($images as $img_attrs) {
			$has_width = preg_match('/\bwidth\s*=\s*["\']?\d+["\']?/i', $img_attrs);
			$has_height = preg_match('/\bheight\s*=\s*["\']?\d+["\']?/i', $img_attrs);
			$has_aspect_ratio = preg_match('/style\s*=\s*["\'][^"\']*aspect-ratio:/i', $img_attrs);

			if ($has_aspect_ratio) {
				$with_aspect_ratio++;
			}

			// Has both width and height (or aspect-ratio) = good
			if (($has_width && $has_height) || $has_aspect_ratio) {
				$with_dimensions++;
			} else {
				// Missing dimensions
				if (!$has_width) {
					$missing_width++;
				}
				if (!$has_height) {
					$missing_height++;
				}

				$problematic[] = [
					'src' => self::extract_src($img_attrs),
					'missing' => [
						'width' => !$has_width,
						'height' => !$has_height,
					],
				];
			}
		}

		return [
			'total' => $total,
			'with_dimensions' => $with_dimensions,
			'without_dimensions' => $total - $with_dimensions,
			'missing_width' => $missing_width,
			'missing_height' => $missing_height,
			'with_aspect_ratio' => $with_aspect_ratio,
			'problematic' => $problematic,
		];
	}

	/**
	 * Extract src from image attributes
	 *
	 * @param string $img_attrs Image attributes
	 * @return string
	 */
	protected static function extract_src(string $img_attrs): string
	{
		if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/', $img_attrs, $src_match)) {
			return $src_match[1];
		}
		return 'unknown';
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
			'user-agent' => 'WPShadow-Diagnostic/1.0 (Performance Checker)',
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
			'id' => 'performance-image-dimensions',
			'title' => $title,
			'description' => $description
			'kb_link' => 'https://wpshadow.com/kb/image-dimensions-cls/',
			'training_link' => 'https://wpshadow.com/training/core-web-vitals/',
			'auto_fixable' => false,
			'threat_level' => 30,
			'module' => 'Performance',
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
		return __('Image Dimensions Check', 'wpshadow');
	}

	/**
	 * Get the description for display
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return __('Checks HTML images for explicit width/height to prevent Cumulative Layout Shift.', 'wpshadow');
	}
}
