<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Excerpt_Quality extends Diagnostic_Base
{

	protected static $slug = 'test-content-excerpt-quality';
	protected static $title = 'Excerpt Quality Test';
	protected static $description = 'Tests for meta description and excerpt quality';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		if ($html !== null) {
			return self::analyze_html($html, $url ?? 'provided-html');
		}

		$html = self::fetch_html($url ?? home_url('/'));
		if ($html === false) {
			return null;
		}

		return self::analyze_html($html, $url ?? home_url('/'));
	}

	protected static function analyze_html(string $html, string $checked_url): ?array
	{
		// Extract meta description
		$meta_description = '';
		if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $desc_match)) {
			$meta_description = trim($desc_match[1]);
		}

		$desc_length = strlen($meta_description);

		// Check quality
		if (empty($meta_description)) {
			return [
				'id' => 'content-no-meta-description',
				'title' => 'Missing Meta Description',
				'description' => 'No meta description found. Meta descriptions show in search results and can improve click-through rate by 5-15%.',
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/meta-description/',
				'training_link' => 'https://wpshadow.com/training/seo-fundamentals/',
				'auto_fixable' => false,
				'threat_level' => 50,
				'module' => 'Content Quality',
				'priority' => 2,
				'meta' => ['has_description' => false],
			];
		}

		if ($desc_length < 50) {
			return [
				'id' => 'content-meta-description-too-short',
				'title' => 'Meta Description Too Short',
				'description' => sprintf('Meta description is only %d characters. Google recommends 150-160 characters for optimal display.', $desc_length),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/meta-description-length/',
				'training_link' => 'https://wpshadow.com/training/seo-fundamentals/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'Content Quality',
				'priority' => 3,
				'meta' => ['description_length' => $desc_length],
			];
		}

		if ($desc_length > 160) {
			return [
				'id' => 'content-meta-description-too-long',
				'title' => 'Meta Description Too Long',
				'description' => sprintf('Meta description is %d characters. Google truncates at ~160 characters. Important info may be cut off.', $desc_length),
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/meta-description-length/',
				'training_link' => 'https://wpshadow.com/training/seo-fundamentals/',
				'auto_fixable' => false,
				'threat_level' => 25,
				'module' => 'Content Quality',
				'priority' => 3,
				'meta' => ['description_length' => $desc_length],
			];
		}

		return null;
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Excerpt Quality', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for meta description and excerpt quality.', 'wpshadow');
	}
}
