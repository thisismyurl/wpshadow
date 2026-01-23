<?php

declare(strict_types=1);
/**
 * Test: Title Tag Check
 *
 * Tests for proper HTML title tag presence and optimization.
 *
 * Philosophy: Educate (#5) - Help users optimize for search engines
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Title_Tag extends Diagnostic_Base
{

	protected static $slug = 'test-seo-title-tag';
	protected static $title = 'Title Tag Test';
	protected static $description = 'Tests for title tag presence and optimization';

	/**
	 * Optimal title length range
	 */
	const MIN_LENGTH = 30;
	const MAX_LENGTH = 60;
	const TRUNCATION_RISK = 70;

	/**
	 * Run the diagnostic check
	 *
	 * PASS (returns null): Title tag present and optimized
	 * FAIL (returns array): Missing or poorly optimized title
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
	 * Run comprehensive title tag tests
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test results
	 */
	public static function run_title_tests(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));

		if ($html === false) {
			return [
				'success' => false,
				'error' => 'Could not fetch HTML',
				'url' => $url ?? home_url('/'),
			];
		}

		$title = self::extract_title($html);

		return [
			'success' => true,
			'url' => $url ?? home_url('/'),
			'title' => $title,
			'title_length' => mb_strlen($title),
			'tests' => [
				'has_title' => self::test_has_title($html),
				'not_empty' => self::test_not_empty($html),
				'optimal_length' => self::test_optimal_length($html),
				'no_truncation_risk' => self::test_no_truncation_risk($html),
				'unique_content' => self::test_unique_content($html),
			],
			'summary' => [
				'passed' => !empty($title) && mb_strlen($title) >= self::MIN_LENGTH && mb_strlen($title) <= self::MAX_LENGTH,
				'status' => self::get_length_status(mb_strlen($title)),
			],
		];
	}

	/**
	 * Test if title tag exists
	 */
	public static function test_has_title(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$title = self::extract_title($html);

		return [
			'test' => 'has_title',
			'passed' => !empty($title),
			'value' => $title,
			'message' => !empty($title)
				? 'Title tag present'
				: 'No title tag found',
			'impact' => 'Title tag is the most important on-page SEO element',
		];
	}

	/**
	 * Test if title is not empty
	 */
	public static function test_not_empty(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$title = trim(self::extract_title($html));

		return [
			'test' => 'not_empty',
			'passed' => !empty($title),
			'value' => $title,
			'message' => !empty($title)
				? 'Title has content'
				: 'Title tag is empty',
			'impact' => 'Empty titles appear as "Untitled" in search results',
		];
	}

	/**
	 * Test if title is optimal length
	 */
	public static function test_optimal_length(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$title = self::extract_title($html);
		$length = mb_strlen($title);

		$is_optimal = $length >= self::MIN_LENGTH && $length <= self::MAX_LENGTH;

		return [
			'test' => 'optimal_length',
			'passed' => $is_optimal,
			'length' => $length,
			'optimal_range' => [self::MIN_LENGTH, self::MAX_LENGTH],
			'message' => $is_optimal
				? sprintf('Title length optimal (%d chars)', $length)
				: sprintf(
					'Title length %s (%d chars)',
					$length < self::MIN_LENGTH ? 'too short' : 'too long',
					$length
				),
			'impact' => 'Optimal length (30-60 chars) maximizes click-through rates',
		];
	}

	/**
	 * Test for truncation risk
	 */
	public static function test_no_truncation_risk(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$title = self::extract_title($html);
		$length = mb_strlen($title);

		$at_risk = $length > self::TRUNCATION_RISK;

		return [
			'test' => 'no_truncation_risk',
			'passed' => !$at_risk,
			'length' => $length,
			'truncation_threshold' => self::TRUNCATION_RISK,
			'message' => $at_risk
				? sprintf('Title may be truncated in search results (%d chars)', $length)
				: 'Title length safe from truncation',
			'impact' => 'Truncated titles show "..." and lose impact',
		];
	}

	/**
	 * Test for unique content (not just site name)
	 */
	public static function test_unique_content(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$title = self::extract_title($html);
		$site_name = get_bloginfo('name');

		// Check if title is ONLY site name or very generic
		$is_unique = mb_strlen($title) > mb_strlen($site_name) + 3;
		$is_just_site_name = strtolower(trim($title)) === strtolower(trim($site_name));

		return [
			'test' => 'unique_content',
			'passed' => $is_unique && !$is_just_site_name,
			'title' => $title,
			'site_name' => $site_name,
			'message' => ($is_unique && !$is_just_site_name)
				? 'Title has unique page-specific content'
				: 'Title appears generic or only contains site name',
			'impact' => 'Unique titles help pages rank for specific keywords',
		];
	}

	/**
	 * Get length status message
	 *
	 * @param int $length Title length
	 * @return string Status
	 */
	protected static function get_length_status(int $length): string
	{
		if ($length === 0) {
			return 'missing';
		}
		if ($length < self::MIN_LENGTH) {
			return 'too_short';
		}
		if ($length <= self::MAX_LENGTH) {
			return 'optimal';
		}
		if ($length <= self::TRUNCATION_RISK) {
			return 'acceptable';
		}
		return 'too_long';
	}

	/**
	 * Analyze HTML for title issues
	 *
	 * @param string $html HTML content
	 * @param string $checked_url URL that was checked
	 * @return array|null Finding or null
	 */
	protected static function analyze_html(string $html, string $checked_url): ?array
	{
		$title = self::extract_title($html);
		$length = mb_strlen($title);

		// Missing title = FAIL
		if (empty($title)) {
			return [
				'id' => 'seo-title-tag',
				'title' => 'Missing Title Tag',
				'description' => 'This page has no title tag. The title is the most important on-page SEO element and appears as the clickable headline in search results.',
				'color' => '#ff5722',
				'bg_color' => '#ffebee',
				'kb_link' => 'https://wpshadow.com/kb/title-tag/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
				'training_link' => 'https://wpshadow.com/training/seo-basics/',
				'auto_fixable' => false,
				'threat_level' => 80,
				'module' => 'SEO',
				'priority' => 1,
				'meta' => [
					'issue' => 'missing',
					'checked_url' => $checked_url,
				],
			];
		}

		// Poor length = warning
		$is_optimal = $length >= self::MIN_LENGTH && $length <= self::MAX_LENGTH;

		if (!$is_optimal) {
			$issue_type = $length < self::MIN_LENGTH ? 'too short' : 'too long';
			$threat_level = $length < self::MIN_LENGTH ? 60 : 50;

			if ($length > self::TRUNCATION_RISK) {
				$threat_level = 65; // Higher risk if truncation likely
			}

			return [
				'id' => 'seo-title-tag',
				'title' => sprintf('Title Tag %s', ucfirst($issue_type)),
				'description' => sprintf(
					'Your title is %d characters (%s). Optimal titles are 30-60 characters to maximize visibility and click-through rates in search results.',
					$length,
					$issue_type
				),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/title-tag/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
				'training_link' => 'https://wpshadow.com/training/seo-basics/',
				'auto_fixable' => false,
				'threat_level' => $threat_level,
				'module' => 'SEO',
				'priority' => 2,
				'meta' => [
					'title' => $title,
					'length' => $length,
					'optimal_min' => self::MIN_LENGTH,
					'optimal_max' => self::MAX_LENGTH,
					'issue' => $issue_type,
					'checked_url' => $checked_url,
				],
			];
		}

		return null; // PASS - Title present and optimal length
	}

	/**
	 * Extract title from HTML
	 *
	 * @param string $html HTML content
	 * @return string Title or empty
	 */
	protected static function extract_title(string $html): string
	{
		if (empty($html)) {
			return '';
		}

		if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $match)) {
			return html_entity_decode(trim($match[1]), ENT_QUOTES, 'UTF-8');
		}

		return '';
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
			'id' => 'seo-title-tag',
			'title' => $title,
			'description' => $description,
			'color' => '#ff5722',
			'bg_color' => '#ffebee',
			'kb_link' => 'https://wpshadow.com/kb/title-tag/',
			'training_link' => 'https://wpshadow.com/training/seo-basics/',
			'auto_fixable' => false,
			'threat_level' => 50,
			'module' => 'SEO',
			'priority' => 2,
		];
	}

	/**
	 * Get the name for display
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return __('Title Tag Check', 'wpshadow');
	}

	/**
	 * Get the description for display
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return __('Checks HTML for title tag presence and optimization (30-60 chars optimal).', 'wpshadow');
	}
}
