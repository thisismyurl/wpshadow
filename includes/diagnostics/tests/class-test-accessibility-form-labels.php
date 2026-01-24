<?php

declare(strict_types=1);
/**
 * Test: Form Labels Accessibility Check
 *
 * Tests for proper form label associations.
 *
 * Philosophy: Inspire confidence (#8) - Accessible forms for all users
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Accessibility_Form_Labels extends Diagnostic_Base
{

	protected static $slug = 'test-accessibility-form-labels';
	protected static $title = 'Form Labels Test';
	protected static $description = 'Tests for proper form input labeling';

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

	public static function run_label_tests(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));

		if ($html === false) {
			return ['success' => false, 'error' => 'Could not fetch HTML'];
		}

		$inputs = self::extract_inputs($html);
		$unlabeled = self::find_unlabeled_inputs($html);

		return [
			'success' => true,
			'total_inputs' => count($inputs),
			'unlabeled_count' => count($unlabeled),
			'tests' => [
				'all_inputs_labeled' => self::test_all_inputs_labeled($html),
				'explicit_labels' => self::test_explicit_labels($html),
				'aria_labels' => self::test_aria_labels($html),
			],
		];
	}

	public static function test_all_inputs_labeled(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));
		$unlabeled = self::find_unlabeled_inputs($html);

		return [
			'test' => 'all_inputs_labeled',
			'passed' => empty($unlabeled),
			'unlabeled_count' => count($unlabeled),
			'message' => empty($unlabeled) ? 'All inputs have labels' : sprintf('%d inputs missing labels', count($unlabeled)),
			'impact' => 'Unlabeled inputs are inaccessible to screen reader users',
		];
	}

	public static function test_explicit_labels(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));

		preg_match_all('/<label[^>]+for=["\']([^"\']+)["\']/i', $html, $matches);
		$explicit_labels = count($matches[1]);

		return [
			'test' => 'explicit_labels',
			'passed' => $explicit_labels > 0,
			'count' => $explicit_labels,
			'message' => $explicit_labels > 0 ? sprintf('%d explicit labels found', $explicit_labels) : 'No explicit label associations',
			'impact' => 'Explicit labels (for/id) provide strongest associations',
		];
	}

	public static function test_aria_labels(?string $url = null, ?string $html = null): array
	{
		$html = $html ?? self::fetch_html($url ?? home_url('/'));

		preg_match_all('/<input[^>]+aria-label=["\']([^"\']+)["\']/i', $html, $matches);
		$aria_labels = count($matches[1]);

		return [
			'test' => 'aria_labels',
			'passed' => true, // Optional, just informational
			'count' => $aria_labels,
			'message' => $aria_labels > 0 ? sprintf('%d ARIA labels found', $aria_labels) : 'No ARIA labels (optional)',
			'impact' => 'ARIA labels provide alternative labeling method',
		];
	}

	protected static function analyze_html(string $html, string $checked_url): ?array
	{
		$unlabeled = self::find_unlabeled_inputs($html);

		if (empty($unlabeled)) {
			return null; // PASS
		}

		$threat_level = 50;
		if (count($unlabeled) > 5) {
			$threat_level = 70;
		}

		return [
			'id' => 'accessibility-form-labels',
			'title' => 'Form Inputs Missing Labels',
			'description' => sprintf(
				'Found %d form inputs without proper labels. Unlabeled inputs are inaccessible to screen reader users and violate WCAG guidelines.',
				count($unlabeled)
			)
			'kb_link' => 'https://wpshadow.com/kb/form-labels/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
			'training_link' => 'https://wpshadow.com/training/accessibility-forms/',
			'auto_fixable' => false,
			'threat_level' => $threat_level,
			'module' => 'Accessibility',
			'priority' => 2,
			'meta' => [
				'unlabeled_count' => count($unlabeled),
				'sample_inputs' => array_slice($unlabeled, 0, 3),
				'checked_url' => $checked_url,
			],
		];
	}

	protected static function extract_inputs(string $html): array
	{
		if (empty($html)) {
			return [];
		}

		preg_match_all('/<input[^>]*>/i', $html, $matches);
		return $matches[0] ?? [];
	}

	protected static function find_unlabeled_inputs(string $html): array
	{
		if (empty($html)) {
			return [];
		}

		$unlabeled = [];

		// Find all inputs with IDs
		preg_match_all('/<input[^>]+id=["\']([^"\']+)["\'][^>]*>/i', $html, $input_matches, PREG_SET_ORDER);

		foreach ($input_matches as $match) {
			$input_id = '';
			if (preg_match('/id=["\']([^"\']+)["\']/i', $match[0], $id_match)) {
				$input_id = $id_match[1];
			}

			// Skip hidden and submit inputs
			if (preg_match('/type=["\'](?:hidden|submit|button)["\']/i', $match[0])) {
				continue;
			}

			// Check if has associated label
			$has_label = preg_match('/<label[^>]+for=["\']' . preg_quote($input_id, '/') . '["\']/i', $html);

			// Check if has aria-label
			$has_aria_label = preg_match('/aria-label=/i', $match[0]);

			// Check if wrapped in label
			$pattern = '/<label[^>]*>.*?' . preg_quote($match[0], '/') . '.*?<\/label>/is';
			$is_wrapped = preg_match($pattern, $html);

			if (!$has_label && !$has_aria_label && !$is_wrapped) {
				$unlabeled[] = $match[0];
			}
		}

		return $unlabeled;
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
			'id' => 'accessibility-form-labels',
			'title' => $title,
			'description' => $description
			'kb_link' => 'https://wpshadow.com/kb/form-labels/',
			'training_link' => 'https://wpshadow.com/training/accessibility-forms/',
			'auto_fixable' => false,
			'threat_level' => 40,
			'module' => 'Accessibility',
			'priority' => 3,
		];
	}

	public static function get_name(): string
	{
		return __('Form Labels Check', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for proper form input labels (WCAG accessibility).', 'wpshadow');
	}
}
