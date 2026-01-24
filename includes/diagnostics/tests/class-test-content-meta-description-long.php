<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Meta_Description_Long extends Diagnostic_Base
{

	protected static $slug = 'test-content-meta-description-long';
	protected static $title = 'Meta Description Length (Long) Test';
	protected static $description = 'Tests for meta descriptions that are too long (>160 chars).';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$body = $html ?? self::fetch_html($url ?? home_url('/'));
		if ($body === false) {
			return null;
		}

		$meta_description = self::extract_description($body);

		if ($meta_description === '') {
			return null; // Missing handled separately
		}

		$desc_length = strlen($meta_description);

		if ($desc_length > 160) {
			return [
				'id' => 'content-meta-description-too-long',
				'title' => 'Meta Description Too Long',
				'description' => sprintf('Meta description is %d characters. Google truncates at ~160 characters. Important info may be cut off.', $desc_length)
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

	protected static function extract_description(string $html): string
	{
		if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $desc_match)) {
			return trim($desc_match[1]);
		}

		return '';
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Meta Description Too Long', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for meta descriptions exceeding 160 characters.', 'wpshadow');
	}
}
