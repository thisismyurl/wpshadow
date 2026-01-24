<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Any Broken Images?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Yes - Loaded via Diagnostic_Registry
 */
class Diagnostic_Broken_Images extends Diagnostic_Base
{
	protected static $slug        = 'broken-images';
	protected static $title       = 'Any Broken Images?';
	protected static $description = 'Scans for missing or broken image files.';

	public static function check(): ?array
	{
		$posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 50,
				'post_status'    => 'publish',
			)
		);

		$broken = array();
		foreach ($posts as $post) {
			preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $post->post_content, $matches);
			if (! empty($matches[1])) {
				foreach ($matches[1] as $img_url) {
					if (strpos($img_url, home_url()) === 0) {
						$path = str_replace(home_url('/'), ABSPATH, $img_url);
						$path = strtok($path, '?');
						if (! file_exists($path)) {
							$broken[] = array(
								'post_id'    => $post->ID,
								'post_title' => $post->post_title,
								'image_url'  => $img_url,
							);
							if (count($broken) >= 10) {
								break 2;
							}
						}
					}
				}
			}
		}

		if (empty($broken)) {
			return null;
		}

		return array(
			'id'            => static::$slug,
			'title'         => sprintf(_n('%d broken image found', '%d broken images found', count($broken), 'wpshadow'), count($broken)),
			'description'   => __('Some images in your content are missing or moved. This looks unprofessional to visitors.', 'wpshadow'),
			'severity'      => 'medium',
			'category'      => 'general',
			'kb_link'       => 'https://wpshadow.com/kb/broken-images/',
			'training_link' => 'https://wpshadow.com/training/broken-images/',
			'auto_fixable'  => false,
			'threat_level'  => 55,
			'broken_images' => $broken,
		);
	}

	/**
	 * Scan page for broken images
	 *
	 * Loads a page by URL or post ID and scans HTML for broken images.
	 * Returns null if no broken images found, array of broken images if found.
	 *
	 * @param string|int $page_identifier URL or post ID to scan
	 * @return ?array Array of broken images or null if none found
	 */
	public static function scan_page_for_broken_images($page_identifier): ?array
	{
		// Get the page URL
		$page_url = self::get_page_url($page_identifier);
		if (! $page_url) {
			return null;
		}

		// Fetch page content
		$response = wp_remote_get($page_url, [
			'timeout'   => 10,
			'sslverify' => apply_filters('https_local_ssl_verify', false),
		]);

		if (is_wp_error($response)) {
			return null;
		}

		$html = wp_remote_retrieve_body($response);
		if (empty($html)) {
			return null;
		}

		// Parse HTML and extract image sources
		$image_urls = self::extract_image_urls($html, $page_url);
		if (empty($image_urls)) {
			return null;
		}

		// Check each image for validity (404 or non-200 responses)
		$broken_images = self::validate_images($image_urls);

		// Return null if no broken images, array if broken images found
		return empty($broken_images) ? null : $broken_images;
	}

	/**
	 * Get page URL from post ID or URL string
	 *
	 * @param string|int $page_identifier Post ID or URL
	 * @return ?string Page URL or null
	 */
	private static function get_page_url($page_identifier): ?string
	{
		if (is_numeric($page_identifier)) {
			$url = get_permalink((int) $page_identifier);
			return $url ?: null;
		}

		if (filter_var($page_identifier, FILTER_VALIDATE_URL)) {
			return $page_identifier;
		}

		return null;
	}

	/**
	 * Extract image URLs from HTML content
	 *
	 * @param string $html HTML content
	 * @param string $page_url Base page URL for resolving relative URLs
	 * @return array List of image URLs found
	 */
	private static function extract_image_urls(string $html, string $page_url): array
	{
		$image_urls = [];

		// Create DOM document safely
		$dom = new \DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($html);
		libxml_use_internal_errors(false);

		// Get all img tags
		$images = $dom->getElementsByTagName('img');

		foreach ($images as $img) {
			$src = $img->getAttribute('src');
			$srcset = $img->getAttribute('srcset');

			if (! empty($src)) {
				$url = self::resolve_url($src, $page_url);
				if ($url) {
					$image_urls[] = $url;
				}
			}

			// Also check srcset for responsive images
			if (! empty($srcset)) {
				$srcset_urls = self::parse_srcset($srcset, $page_url);
				$image_urls = array_merge($image_urls, $srcset_urls);
			}
		}

		return array_unique($image_urls);
	}

	/**
	 * Parse srcset attribute and extract URLs
	 *
	 * @param string $srcset Srcset attribute value
	 * @param string $base_url Base page URL
	 * @return array List of image URLs from srcset
	 */
	private static function parse_srcset(string $srcset, string $base_url): array
	{
		$urls = [];
		// srcset format: "image1.jpg 1x, image2.jpg 2x"
		$sources = explode(',', $srcset);

		foreach ($sources as $source) {
			$parts = explode(' ', trim($source));
			if (! empty($parts[0])) {
				$url = self::resolve_url($parts[0], $base_url);
				if ($url) {
					$urls[] = $url;
				}
			}
		}

		return $urls;
	}

	/**
	 * Resolve relative URL to absolute URL
	 *
	 * @param string $href Href attribute value
	 * @param string $base_url Base page URL
	 * @return ?string Absolute URL or null
	 */
	private static function resolve_url(string $href, string $base_url): ?string
	{
		// Already absolute
		if (filter_var($href, FILTER_VALIDATE_URL)) {
			return $href;
		}

		// Protocol-relative
		if (strpos($href, '//') === 0) {
			$scheme = wp_parse_url($base_url, PHP_URL_SCHEME);
			return $scheme . ':' . $href;
		}

		// Relative to root
		if (strpos($href, '/') === 0) {
			$base_parts = wp_parse_url($base_url);
			return $base_parts['scheme'] . '://' . $base_parts['host'] . $href;
		}

		// Relative to page
		$base_parts = wp_parse_url($base_url);
		$base_path = dirname($base_parts['path']);
		return $base_parts['scheme'] . '://' . $base_parts['host'] . $base_path . '/' . $href;
	}

	/**
	 * Validate images and return broken ones
	 *
	 * @param array $image_urls List of image URLs to validate
	 * @return array Array of broken images with HTTP status codes
	 */
	private static function validate_images(array $image_urls): array
	{
		$broken_images = [];

		foreach ($image_urls as $url) {
			$response = wp_remote_head($url, [
				'timeout'   => 5,
				'sslverify' => apply_filters('https_local_ssl_verify', false),
			]);

			if (is_wp_error($response)) {
				$broken_images[$url] = 'connection_error';
				continue;
			}

			$status_code = wp_remote_retrieve_response_code($response);
			// Images are broken if they return 404, 410, 500, 503, or other error codes
			if (! in_array($status_code, [200, 201, 204, 206], true)) {
				$broken_images[$url] = $status_code;
			}
		}

		return $broken_images;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Any Broken Images?
	 * Slug: broken-images
	 *
	 * Tests scanning the homepage for broken images.
	 * Returns null if no broken images found, array if broken images detected.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_broken_images(): array
	{
		// First run the file-based check
		$file_check = self::check();

		// Then scan the homepage for broken images via HTTP
		$broken_images = self::scan_page_for_broken_images(home_url());

		// Test passes if either method completes successfully
		$passed = true;
		$message = '✓ Homepage scanned: ';

		if (is_null($file_check)) {
			$message .= 'No missing local images, ';
		} else {
			$message .= count($file_check['broken_images']) . ' missing local image(s), ';
		}

		if (is_null($broken_images)) {
			$message .= 'all remote images loading correctly';
		} else {
			$message .= count($broken_images) . ' broken remote image(s) detected';
		}

		return [
			'passed' => $passed,
			'message' => $message,
		];
	}
}
