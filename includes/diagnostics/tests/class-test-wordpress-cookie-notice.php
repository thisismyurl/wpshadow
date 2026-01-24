<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_WordPress_Cookie_Notice extends Diagnostic_Base
{

	protected static $slug = 'test-wordpress-cookie-notice';
	protected static $title = 'Cookie Notice Test';
	protected static $description = 'Tests for GDPR/privacy cookie notice';

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
		// Check if site uses analytics or tracking (indicates cookies)
		$uses_tracking = preg_match('/google-analytics|gtag|analytics\.js|ga\(|fbq\(|_gaq|googletagmanager/i', $html);

		if (!$uses_tracking) {
			return null; // No tracking, cookie notice less critical
		}

		// Check for cookie notice/consent indicators
		$has_cookie_notice = preg_match('/cookie[_-]?(?:notice|consent|banner|policy)|gdpr[_-]?(?:notice|banner)/i', $html) ||
			preg_match('/class=["\'][^"\']*cookie[_-]?(?:notice|consent|banner)[^"\']*["\']/i', $html);

		if (!$has_cookie_notice) {
			return [
				'id' => 'wordpress-no-cookie-notice',
				'title' => 'No Cookie Notice',
				'description' => 'Tracking scripts detected but no cookie consent notice. GDPR/CCPA require user consent before setting cookies.'
				'kb_link' => 'https://wpshadow.com/kb/cookie-consent/',
				'training_link' => 'https://wpshadow.com/training/gdpr-compliance/',
				'auto_fixable' => false,
				'threat_level' => 40,
				'module' => 'WordPress',
				'priority' => 2,
				'meta' => ['uses_tracking' => true, 'has_consent' => false],
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
		return __('Cookie Notice', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for GDPR/privacy cookie notice.', 'wpshadow');
	}
}
