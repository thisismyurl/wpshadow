<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_HowTo extends Diagnostic_Base
{

	protected static $slug = 'test-schema-howto';
	protected static $title = 'HowTo Schema Test';
	protected static $description = 'Tests for HowTo structured data';

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
		// Check for how-to indicators
		$has_howto_title = preg_match('/\b(how to|tutorial|guide|step by step)\b/i', $html);

		// Check for ordered steps
		preg_match_all('/<ol[^>]*>.*?<\/ol>/is', $html, $ordered_lists);
		$step_count = 0;
		foreach ($ordered_lists[0] as $ol) {
			preg_match_all('/<li[^>]*>/i', $ol, $list_items);
			$step_count += count($list_items[0]);
		}

		// Check for HowTo schema
		$has_howto_schema = preg_match('/"@type"\s*:\s*"HowTo"/i', $html);

		// If looks like how-to guide but no schema
		if ($has_howto_title && $step_count >= 3 && !$has_howto_schema) {
			return [
				'id' => 'schema-howto-missing',
				'title' => 'HowTo Schema Missing',
				'description' => sprintf(
					'How-to content detected (%d steps) but no HowTo structured data found. HowTo schema enables rich results with step-by-step cards in search.',
					$step_count
				)
				'kb_link' => 'https://wpshadow.com/kb/howto-schema/',
				'training_link' => 'https://wpshadow.com/training/structured-data/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'SEO',
				'priority' => 2,
				'meta' => [
					'has_howto_title' => $has_howto_title,
					'step_count' => $step_count,
					'has_schema' => $has_howto_schema,
					'checked_url' => $checked_url,
				],
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
		return __('HowTo Schema', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for HowTo structured data (guides/tutorials).', 'wpshadow');
	}
}
