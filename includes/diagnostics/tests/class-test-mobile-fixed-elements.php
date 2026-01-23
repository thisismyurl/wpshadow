<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Fixed_Elements extends Diagnostic_Base
{

	protected static $slug = 'test-mobile-fixed-elements';
	protected static $title = 'Fixed Elements Test';
	protected static $description = 'Tests for potentially problematic fixed/sticky elements on mobile';

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
		// Count position: fixed elements
		preg_match_all('/position:\s*fixed/i', $html, $fixed_matches);
		$fixed_count = count($fixed_matches[0]);

		// Count position: sticky elements
		preg_match_all('/position:\s*sticky/i', $html, $sticky_matches);
		$sticky_count = count($sticky_matches[0]);

		$total_fixed = $fixed_count + $sticky_count;

		// Too many fixed elements can interfere with mobile UX
		if ($total_fixed > 3) {
			return [
				'id' => 'mobile-too-many-fixed',
				'title' => 'Multiple Fixed/Sticky Elements',
				'description' => sprintf(
					'Found %d fixed/sticky positioned elements. On mobile, too many fixed elements reduce usable screen space and can cause layout issues.',
					$total_fixed
				),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/fixed-elements-mobile/',
				'training_link' => 'https://wpshadow.com/training/mobile-layout/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'Mobile',
				'priority' => 3,
				'meta' => [
					'fixed_count' => $fixed_count,
					'sticky_count' => $sticky_count,
					'total' => $total_fixed,
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
		return __('Fixed/Sticky Elements', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for problematic fixed elements on mobile.', 'wpshadow');
	}
}
