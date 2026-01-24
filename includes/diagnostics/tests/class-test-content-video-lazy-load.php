<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Video_Lazy_Load extends Diagnostic_Base
{

	protected static $slug = 'test-content-video-lazy-load';
	protected static $title = 'Video Lazy Load Test';
	protected static $description = 'Tests that multiple video embeds use lazy loading.';

	public static function check(?string $url = null, ?string $html = null): ?array
	{
		$body = $html ?? self::fetch_html($url ?? home_url('/'));
		if ($body === false) {
			return null;
		}

		preg_match_all('/<iframe[^>]+src=["\']([^"\']*(?:youtube|vimeo|dailymotion)[^"\']*)["\']/', $body, $video_iframes);
		preg_match_all('/<video[^>]*>/', $body, $html5_videos);

		$total_videos = count($video_iframes[0]) + count($html5_videos[0]);

		if ($total_videos === 0) {
			return null;
		}

		$lazy_videos = 0;
		foreach ($video_iframes[0] as $iframe) {
			if (preg_match('/loading=["\']lazy["\']|data-src=/i', $iframe)) {
				$lazy_videos++;
			}
		}

		if ($total_videos > 2 && $lazy_videos === 0) {
			return [
				'id' => 'content-video-no-lazy-load',
				'title' => 'Videos Not Lazy Loaded',
				'description' => sprintf('%d video embeds without lazy loading. Video embeds are heavy (500KB+) and should load on-demand.', $total_videos)
				'kb_link' => 'https://wpshadow.com/kb/lazy-load-videos/',
				'training_link' => 'https://wpshadow.com/training/video-optimization/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'Content Quality',
				'priority' => 3,
				'meta' => ['total_videos' => $total_videos, 'lazy_videos' => $lazy_videos],
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
		return __('Video Lazy Loading', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks if multiple video embeds are lazy loaded.', 'wpshadow');
	}
}
