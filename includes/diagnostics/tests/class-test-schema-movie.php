<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Schema_Movie extends Diagnostic_Base
{

	protected static $slug = 'test-schema-movie';
	protected static $title = 'Movie Schema Test';
	protected static $description = 'Tests for Movie structured data';

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
		// Check for movie indicators
		$has_movie_keywords = preg_match('/\b(movie|film|director|cast|actor|actress|runtime|genre|release date|trailer)\b/i', $html);
		$has_imdb = preg_match('/IMDB|imdb\.com/i', $html);
		$has_duration = preg_match('/\b([0-9]+\s*min|[0-9]+h\s*[0-9]+m|runtime)/i', $html);

		// Check for Movie schema
		$has_movie_schema = preg_match('/"@type"\s*:\s*"Movie"/i', $html);

		// If looks like movie page but no schema
		if ($has_movie_keywords && ($has_imdb || $has_duration) && !$has_movie_schema) {
			return [
				'id' => 'schema-movie-missing',
				'title' => 'Movie Schema Missing',
				'description' => 'Movie content detected but no Movie structured data found. Movie schema enables rich results with cast, director, ratings, and trailers.',
				'color' => '#2196f3',
				'bg_color' => '#e3f2fd',
				'kb_link' => 'https://wpshadow.com/kb/movie-schema/',
				'training_link' => 'https://wpshadow.com/training/media-seo/',
				'auto_fixable' => false,
				'threat_level' => 30,
				'module' => 'SEO',
				'priority' => 3,
				'meta' => [
					'has_movie_keywords' => $has_movie_keywords,
					'has_imdb' => $has_imdb,
					'has_duration' => $has_duration,
					'has_schema' => $has_movie_schema,
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
		return __('Movie Schema', 'wpshadow');
	}

	public static function get_description(): string
	{
		return __('Checks for Movie structured data (film sites).', 'wpshadow');
	}
}
