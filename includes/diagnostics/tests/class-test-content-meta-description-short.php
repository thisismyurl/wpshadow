<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Meta_Description_Short extends Diagnostic_Base
{

	protected static $slug = 'test-content-meta-description-short';
	protected static $title = 'Meta Description Length (Short) Test';
	protected static $description = 'Tests for meta descriptions that are too short (<50 chars).';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$body = $html ?? self::fetch_html($url ?? home_url('/'));
		if ($body === false) {
			return null;
		}

		$meta_description = self::extract_description($body);

		if ($meta_description === '') {
			return null; // Missing handled by dedicated test
		}

		$desc_length = strlen($meta_description);

		if ($desc_length < 50) {
			return [
				'id' => 'content-meta-description-too-short',
				'title' => 'Meta Description Too Short',
				'description' => sprintf('Meta description is only %d characters. Google recommends 150-160 characters for optimal display.', $desc_length)
				'kb_link' => 'https://wpshadow.com/kb/meta-description-length/',
				'training_link' => 'https://wpshadow.com/training/seo-fundamentals/',
				'auto_fixable' => false,
				'threat_level' => 35,
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
		return __('Meta Description Too Short', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for meta descriptions that are under 50 characters.', 'wpshadow');
	}
}
