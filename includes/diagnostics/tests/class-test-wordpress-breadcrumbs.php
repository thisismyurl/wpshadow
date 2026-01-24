<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_WordPress_Breadcrumbs extends Diagnostic_Base
{

	protected static $slug = 'test-wordpress-breadcrumbs';
	protected static $title = 'Breadcrumbs Test';
	protected static $description = 'Tests for breadcrumb navigation';

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
		// Check if it's a deep page (category, post, page)
		$is_deep_page = (preg_match('/<article[^>]*>/i', $html) ||
			preg_match('/class=["\'][^"\']*(?:category|archive|single)[^"\']*["\']/i', $html));

		if (!$is_deep_page) {
			return null; // Homepage or simple page, breadcrumbs not as critical
		}

		// Check for breadcrumb indicators
		$has_breadcrumbs = preg_match('/<nav[^>]+class=["\'][^"\']*breadcrumb[^"\']*["\']/i', $html) ||
			preg_match('/typeof=["\']BreadcrumbList["\']/i', $html) ||
			preg_match('/itemtype=["\'][^"\']*BreadcrumbList[^"\']*["\']/i', $html) ||
			preg_match('/<ol[^>]+class=["\'][^"\']*breadcrumb[^"\']*["\']/i', $html);

		if (!$has_breadcrumbs) {
			return [
				'id' => 'wordpress-no-breadcrumbs',
				'title' => 'No Breadcrumb Navigation',
				'description' => 'No breadcrumbs detected on content page. Breadcrumbs improve UX, SEO, and can appear in search results.'
				'kb_link' => 'https://wpshadow.com/kb/breadcrumbs/',
				'training_link' => 'https://wpshadow.com/training/navigation-ux/',
				'auto_fixable' => false,
				'threat_level' => 25,
				'module' => 'WordPress',
				'priority' => 3,
				'meta' => ['has_breadcrumbs' => false],
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
		return __('Breadcrumbs', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for breadcrumb navigation.', 'wpshadow');
	}
}
