<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Content_Video_Embed extends Diagnostic_Base
{

	protected static $slug = 'test-content-video-embed';
	protected static $title = 'Video Embed Test';
	protected static $description = 'Tests for proper video embed implementation';

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
		// Find video embeds
		preg_match_all('/<iframe[^>]+src=["\']([^"\']*(?:youtube|vimeo|dailymotion)[^"\']*)["\']/', $html, $video_iframes);
		preg_match_all('/<video[^>]*>/', $html, $html5_videos);

		$total_videos = count($video_iframes[0]) + count($html5_videos[0]);

		if ($total_videos === 0) {
			return null;
		}

		// Check for lazy loading on video embeds
		$lazy_videos = 0;
		foreach ($video_iframes[0] as $iframe) {
			if (preg_match('/loading=["\']lazy["\']|data-src=/i', $iframe)) {
				$lazy_videos++;
			}
		}

		// Check for autoplay (bad UX)
		$autoplay_videos = preg_match_all('/(?:autoplay|auto-play)[=\s]/i', $html, $autoplay_matches);

		if ($autoplay_videos > 0) {
			return [
				'id' => 'content-video-autoplay',
				'title' => 'Video Autoplay Detected',
				'description' => sprintf('%d video(s) set to autoplay. Autoplay is poor UX, wastes bandwidth, and harms accessibility.', $autoplay_videos),
				'color' => '#ff9800',
				'bg_color' => '#fff3e0',
				'kb_link' => 'https://wpshadow.com/kb/video-autoplay/',
				'training_link' => 'https://wpshadow.com/training/multimedia-best-practices/',
				'auto_fixable' => false,
				'threat_level' => 35,
				'module' => 'Content Quality',
				'priority' => 3,
				'meta' => ['autoplay_count' => $autoplay_videos],
			];
		}

		if ($total_videos > 2 && $lazy_videos === 0) {
			return [
				'id' => 'content-video-no-lazy-load',
				'title' => 'Videos Not Lazy Loaded',
				'description' => sprintf('%d video embeds without lazy loading. Video embeds are heavy (500KB+) and should load on-demand.', $total_videos),
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
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
		return __('Video Embed', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for proper video embed implementation.', 'wpshadow');
	}
}
