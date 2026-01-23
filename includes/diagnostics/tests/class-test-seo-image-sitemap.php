<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Image_Sitemap extends Diagnostic_Base
{

	protected static $slug = 'test-seo-image-sitemap';
	protected static $title = 'Image Sitemap Test';
	protected static $description = 'Tests for image sitemap or images in XML sitemap';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$sitemap_url = home_url('/sitemap.xml');
		$response = wp_remote_get($sitemap_url, ['timeout' => 10, 'sslverify' => false]);

		if (is_wp_error($response)) {
			return null; // Main sitemap not accessible
		}

		$content = wp_remote_retrieve_body($response);

		// Check for image namespace
		$has_image_namespace = preg_match('/xmlns:image=/i', $content);
		$has_image_tags = preg_match('/<image:image>|<image:loc>/i', $content);

		if (!$has_image_namespace && !$has_image_tags) {
			return [
				'id' => 'seo-image-sitemap-missing',
				'title' => 'Images Not in Sitemap',
				'description' => 'XML sitemap doesn\'t include images. Adding images to your sitemap helps them appear in Google Image Search.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/image-sitemap/',
				'training_link' => 'https://wpshadow.com/training/image-seo/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'SEO',
				'priority' => 3,
				'meta' => ['has_image_namespace' => false, 'checked_url' => $sitemap_url],
			];
		}

		return null;
	}

	public static function get_name(): string
	{
		return __('Image Sitemap', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for images in XML sitemap.', 'wpshadow');
	}
}
