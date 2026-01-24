<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_External_Links extends Diagnostic_Base
{

	protected static $slug = 'test-seo-external-links';
	protected static $title = 'External Links Test';
	protected static $description = 'Tests external links for proper attributes (nofollow, noopener)';

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
		$site_url = home_url('/');

		// Find external links
		preg_match_all('/<a[^>]+href=["\']https?:\/\/([^"\']+)["\'][^>]*>/i', $html, $links);

		$external_links = 0;
		$missing_noopener = 0;

		foreach ($links[0] as $link_tag) {
			// Check if it's external
			if (strpos($link_tag, $site_url) === false) {
				$external_links++;

				// Check for target=_blank
				if (preg_match('/target=["\']_blank["\']/i', $link_tag)) {
					// Should have rel=noopener for security
					if (!preg_match('/rel=["\'][^"\']*noopener/i', $link_tag)) {
						$missing_noopener++;
					}
				}
			}
		}

		if ($external_links > 0 && $missing_noopener > 0) {
			return [
				'id' => 'seo-external-links-no-noopener',
				'title' => 'External Links Missing rel=noopener',
				'description' => sprintf('%d external links with target="_blank" are missing rel="noopener". This is a security risk (tabnabbing).', $missing_noopener)
				'kb_link' => 'https://wpshadow.com/kb/noopener-noreferrer/',
				'training_link' => 'https://wpshadow.com/training/security-basics/',
				'auto_fixable' => false,
				'threat_level' => 45,
				'module' => 'Security',
				'priority' => 2,
				'meta' => ['external_links' => $external_links, 'missing_noopener' => $missing_noopener],
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
		return __('External Links Security', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks external links for proper security attributes.', 'wpshadow');
	}
}
