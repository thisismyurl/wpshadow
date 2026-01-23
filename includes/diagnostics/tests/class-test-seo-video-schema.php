<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Video_Schema extends Diagnostic_Base
{

	protected static $slug = 'test-seo-video-schema';
	protected static $title = 'Video Schema Test';
	protected static $description = 'Tests for VideoObject schema markup';

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
		// Check for video embeds
		$has_video = preg_match('/<(video|iframe)[^>]*(youtube|vimeo|mp4|webm)/i', $html);

		if (!$has_video) {
			return null; // No videos on page
		}

		// Check for VideoObject schema
		$has_video_schema = preg_match('/"@type"\s*:\s*"VideoObject"/i', $html);

		if ($has_video_schema) {
			// Check completeness
			$has_name = preg_match('/"name"\s*:\s*"[^"]+"/i', $html);
			$has_description = preg_match('/"description"\s*:\s*"[^"]+"/i', $html);
			$has_thumbnail = preg_match('/"thumbnailUrl"\s*:\s*"[^"]+"/i', $html);
			$has_upload_date = preg_match('/"uploadDate"\s*:\s*"[^"]+"/i', $html);

			$missing = [];
			if (!$has_name) $missing[] = 'name';
			if (!$has_description) $missing[] = 'description';
			if (!$has_thumbnail) $missing[] = 'thumbnailUrl';
			if (!$has_upload_date) $missing[] = 'uploadDate';

			if (!empty($missing)) {
				return [
					'id' => 'seo-video-schema',
					'title' => 'Incomplete Video Schema',
					'description' => sprintf('VideoObject schema missing: %s', implode(', ', $missing)),
					'color' => '#ff9800',
					'bg_color' => '#fff3e0',
					'kb_link' => 'https://wpshadow.com/kb/video-schema/',
					'training_link' => 'https://wpshadow.com/training/structured-data/',
					'auto_fixable' => false,
					'threat_level' => 35,
					'module' => 'SEO',
					'priority' => 2,
					'meta' => ['missing' => $missing, 'checked_url' => $checked_url],
				];
			}

			return null; // Complete
		}

		return [
			'id' => 'seo-video-schema',
			'title' => 'Missing Video Schema',
			'description' => 'This page contains video content but lacks VideoObject schema. Adding video schema enables video rich results in search.',
			'color' => '#ff9800',
			'bg_color' => '#fff3e0',
			'kb_link' => 'https://wpshadow.com/kb/video-schema/',
			'training_link' => 'https://wpshadow.com/training/structured-data/',
			'auto_fixable' => false,
			'threat_level' => 40,
			'module' => 'SEO',
			'priority' => 3,
			'meta' => ['checked_url' => $checked_url],
		];
	}

	protected static function fetch_html(string $url)
	{
		$response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
		return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
	}

	public static function get_name(): string
	{
		return __('Video Schema Check', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for VideoObject schema on video content.', 'wpshadow');
	}
}
