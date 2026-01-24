<?php

declare(strict_types=1);
/**
 * Test: ARIA Landmarks Check
 *
 * Tests for proper ARIA landmark roles.
 *
 * Philosophy: Inspire confidence (#8) - Structure for screen readers
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Accessibility_ARIA_Landmarks extends Diagnostic_Base
{

	protected static $slug = 'test-accessibility-aria-landmarks';
	protected static $title = 'ARIA Landmarks Test';
	protected static $description = 'Tests for ARIA landmark roles';

	const REQUIRED_LANDMARKS = ['main', 'navigation'];
	const RECOMMENDED_LANDMARKS = ['banner', 'contentinfo', 'complementary'];

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

	public static function run_landmark_tests(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));

		if ($html === false) {
			return ['success' => false, 'error' => 'Could not fetch HTML'];
		}

		$landmarks = self::extract_landmarks($html);

		return [
			'success' => true,
			'landmarks_found' => $landmarks,
			'tests' => [
				'has_main' => self::test_has_main($html),
				'has_navigation' => self::test_has_navigation($html),
				'has_header' => self::test_has_header($html),
				'has_footer' => self::test_has_footer($html),
			],
		];
	}

	public static function test_has_main(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$has_main = preg_match('/<main[\s>]|role=["\']main["\']/i', $html);

		return [
			'test' => 'has_main',
			'passed' => $has_main,
			'message' => $has_main ? 'Main landmark present' : 'Missing main landmark',
			'impact' => 'Main landmark identifies primary content area',
		];
	}

	public static function test_has_navigation(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$has_nav = preg_match('/<nav[\s>]|role=["\']navigation["\']/i', $html);

		return [
			'test' => 'has_navigation',
			'passed' => $has_nav,
			'message' => $has_nav ? 'Navigation landmark present' : 'Missing navigation landmark',
			'impact' => 'Navigation landmarks help users skip to menus',
		];
	}

	public static function test_has_header(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$has_header = preg_match('/<header[\s>]|role=["\']banner["\']/i', $html);

		return [
			'test' => 'has_header',
			'passed' => $has_header,
			'message' => $has_header ? 'Header/banner landmark present' : 'Missing header landmark',
			'impact' => 'Header landmark identifies site masthead',
		];
	}

	public static function test_has_footer(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$has_footer = preg_match('/<footer[\s>]|role=["\']contentinfo["\']/i', $html);

		return [
			'test' => 'has_footer',
			'passed' => $has_footer,
			'message' => $has_footer ? 'Footer/contentinfo landmark present' : 'Missing footer landmark',
			'impact' => 'Footer landmark identifies site footer information',
		];
	}

	protected static function analyze_html(string $html, string $checked_url): ?array
	{
		$landmarks = self::extract_landmarks($html);

		// Check required landmarks
		$missing_required = array_diff(self::REQUIRED_LANDMARKS, $landmarks);

		if (empty($missing_required)) {
			return null; // PASS - has required landmarks
		}

		return [
			'id' => 'accessibility-aria-landmarks',
			'title' => 'Missing ARIA Landmarks',
			'description' => sprintf(
				'Page is missing %d required ARIA landmarks: %s. Landmarks help screen reader users navigate your site structure.',
				count($missing_required),
				implode(', ', $missing_required)
			)
			'kb_link' => 'https://wpshadow.com/kb/aria-landmarks/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
			'training_link' => 'https://wpshadow.com/training/accessibility-structure/',
			'auto_fixable' => false,
			'threat_level' => 40,
			'module' => 'Accessibility',
			'priority' => 2,
			'meta' => [
				'missing_required' => $missing_required,
				'landmarks_found' => $landmarks,
				'checked_url' => $checked_url,
			],
		];
	}

	protected static function extract_landmarks(string $html): array
	{
		if (empty($html)) {
			return [];
		}

		$landmarks = [];

		// HTML5 semantic elements
		if (preg_match('/<main[\s>]/i', $html)) {
			$landmarks[] = 'main';
		}
		if (preg_match('/<nav[\s>]/i', $html)) {
			$landmarks[] = 'navigation';
		}
		if (preg_match('/<header[\s>]/i', $html)) {
			$landmarks[] = 'banner';
		}
		if (preg_match('/<footer[\s>]/i', $html)) {
			$landmarks[] = 'contentinfo';
		}
		if (preg_match('/<aside[\s>]/i', $html)) {
			$landmarks[] = 'complementary';
		}

		// ARIA roles
		if (preg_match('/role=["\']main["\']/i', $html)) {
			$landmarks[] = 'main';
		}
		if (preg_match('/role=["\']navigation["\']/i', $html)) {
			$landmarks[] = 'navigation';
		}
		if (preg_match('/role=["\']banner["\']/i', $html)) {
			$landmarks[] = 'banner';
		}
		if (preg_match('/role=["\']contentinfo["\']/i', $html)) {
			$landmarks[] = 'contentinfo';
		}
		if (preg_match('/role=["\']complementary["\']/i', $html)) {
			$landmarks[] = 'complementary';
		}

		return array_unique($landmarks);
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, [
			'timeout' => 10,
			'user-agent' => 'WPShadow-Diagnostic/1.0',
			'sslverify' => false,
		]);

		if (is_wp_error($response)) {
			return false;
		}

		return wp_remote_retrieve_body($response);
	}

	protected static function is_internal_url(string $url): bool
	{
		$site_host = wp_parse_url(home_url(), PHP_URL_HOST);
		$test_host = wp_parse_url($url, PHP_URL_HOST);
		return $site_host === $test_host;
	}

	protected static function error_result(string $title, string $description): array
	{
		return [
			'id' => 'accessibility-aria-landmarks',
			'title' => $title,
			'description' => $description
			'kb_link' => 'https://wpshadow.com/kb/aria-landmarks/',
			'training_link' => 'https://wpshadow.com/training/accessibility-structure/',
			'auto_fixable' => false,
			'threat_level' => 30,
			'module' => 'Accessibility',
			'priority' => 3,
		];
	}

	public static function get_name(): string
	{
		return __('ARIA Landmarks Check', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for ARIA landmark roles (main, nav, header, footer).', 'wpshadow');
	}
}
