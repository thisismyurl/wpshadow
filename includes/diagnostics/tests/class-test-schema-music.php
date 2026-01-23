<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_Music extends Diagnostic_Base
{

	protected static $slug = 'test-schema-music';
	protected static $title = 'MusicRecording Schema Test';
	protected static $description = 'Tests for MusicRecording/MusicAlbum structured data';

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
		// Check for music indicators
		$has_music_keywords = preg_match('/\b(album|track|song|artist|band|music|recording|release|listen|play|stream)\b/i', $html);
		$has_audio_player = preg_match('/<audio[^>]*>|spotify|soundcloud|apple music/i', $html);
		$has_tracklist = preg_match('/track\s*[0-9]+|tracklist/i', $html);

		// Check for MusicRecording/MusicAlbum schema
		$has_music_schema = preg_match('/"@type"\s*:\s*"Music(Recording|Album)"/i', $html);

		// If looks like music page but no schema
		if ($has_music_keywords && ($has_audio_player || $has_tracklist) && !$has_music_schema) {
			return [
				'id' => 'schema-music-missing',
				'title' => 'Music Schema Missing',
				'description' => 'Music content detected (audio player, tracks, album) but no MusicRecording or MusicAlbum structured data found. Music schema enables rich results with playable previews.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/music-schema/',
				'training_link' => 'https://wpshadow.com/training/media-seo/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'SEO',
				'priority' => 3,
				'meta' => [
					'has_music_keywords' => $has_music_keywords,
					'has_audio_player' => $has_audio_player,
					'has_tracklist' => $has_tracklist,
					'has_schema' => $has_music_schema,
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
		return __('Music Schema', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for MusicRecording/Album structured data.', 'wpshadow');
	}
}
