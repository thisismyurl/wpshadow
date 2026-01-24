<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Robots_Txt extends Diagnostic_Base
{

	protected static $slug = 'test-seo-robots-txt';
	protected static $title = 'Robots.txt Test';
	protected static $description = 'Tests for robots.txt file existence and configuration';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$robots_url = home_url('/robots.txt');
		$response = wp_remote_get($robots_url, ['timeout' => 10, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return [
				'id' => 'seo-robots-txt-missing',
				'title' => 'Robots.txt Not Accessible',
				'description' => 'robots.txt file cannot be accessed. Search engines expect this file to understand crawling rules.'
				'kb_link' => 'https://wpshadow.com/kb/robots-txt/',
				'training_link' => 'https://wpshadow.com/training/seo-fundamentals/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'SEO',
				'priority' => 2,
				'meta' => ['checked_url' => $robots_url],
			];
		}

		$content = wp_remote_retrieve_body($response);

		// Check for sitemap reference
		$has_sitemap = preg_match('/Sitemap:\s*https?:\/\//i', $content);

		if (!$has_sitemap) {
			return [
				'id' => 'seo-robots-txt-no-sitemap',
				'title' => 'Robots.txt Missing Sitemap Reference',
				'description' => 'robots.txt exists but doesn\'t reference your XML sitemap. Add "Sitemap: ' . home_url('/sitemap.xml') . '" to help search engines discover your content.'
				'kb_link' => 'https://wpshadow.com/kb/robots-txt-sitemap/',
				'training_link' => 'https://wpshadow.com/training/seo-fundamentals/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'SEO',
				'priority' => 3,
				'meta' => ['has_sitemap_reference' => false, 'checked_url' => $robots_url],
			];
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('Robots.txt Configuration', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for robots.txt file and proper configuration.', 'wpshadow');
	}
}
