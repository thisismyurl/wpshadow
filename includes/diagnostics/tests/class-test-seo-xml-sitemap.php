<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_XML_Sitemap extends Diagnostic_Base
{

	protected static $slug = 'test-seo-xml-sitemap';
	protected static $title = 'XML Sitemap Test';
	protected static $description = 'Tests for XML sitemap existence and validity';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$sitemap_urls = [
			home_url('/sitemap.xml'),
			home_url('/sitemap_index.xml'),
			home_url('/wp-sitemap.xml'),
		];

		$sitemap_found = false;
		$valid_xml = false;

		foreach ($sitemap_urls as $sitemap_url) {
			$response = wp_remote_get($sitemap_url, ['timeout' => 10, 'sslverify' => false]);

			if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
				$content = wp_remote_retrieve_body($response);

				if (preg_match('/<urlset|<sitemapindex/i', $content)) {
					$sitemap_found = true;
					$valid_xml = true;
					break;
				}
			}
		}

		if (!$sitemap_found) {
			return [
				'id' => 'seo-xml-sitemap-missing',
				'title' => 'XML Sitemap Not Found',
				'description' => 'No XML sitemap detected at common locations. XML sitemaps help search engines discover and index your content efficiently.'
				'kb_link' => 'https://wpshadow.com/kb/xml-sitemap/',
				'training_link' => 'https://wpshadow.com/training/seo-fundamentals/',
				'auto_fixable' => false,
				'threat_level' => 45,
				'module' => 'SEO',
				'priority' => 2,
				'meta' => ['checked_urls' => $sitemap_urls],
			];
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('XML Sitemap', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for XML sitemap existence and validity.', 'wpshadow');
	}
}
