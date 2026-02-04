<?php
/**
 * URL Parsing and Regex Pattern Helper
 *
 * Provides standardized URL parsing, validation, and common regex patterns
 * for meta tag extraction used across multiple diagnostics.
 *
 * @since   1.6032.0900
 * @package WPShadow\Diagnostics\Helpers
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_URL_And_Pattern_Helper Class
 *
 * Consolidates URL parsing methods and regex patterns to eliminate
 * duplicated URL extraction and meta tag detection code across diagnostics.
 *
 * @since 1.6032.0900
 */
class Diagnostic_URL_And_Pattern_Helper {

	/**
	 * Regex pattern for Open Graph meta tags.
	 *
	 * @var string
	 */
	const PATTERN_OG_TAG = '/<meta\s+property=["\']og:[^"\']*["\'][^>]*content=["\']([^"\']+)["\']|<meta\s+content=["\']([^"\']+)["\'][^>]*property=["\']og:[^"\']*["\']/i';

	/**
	 * Regex pattern for Open Graph title.
	 *
	 * @var string
	 */
	const PATTERN_OG_TITLE = '/<meta\s+property=["\']og:title["\']/i';

	/**
	 * Regex pattern for Open Graph image.
	 *
	 * @var string
	 */
	const PATTERN_OG_IMAGE = '/<meta\s+property=["\']og:image["\']/i';

	/**
	 * Regex pattern for Open Graph image dimensions.
	 *
	 * @var string
	 */
	const PATTERN_OG_IMAGE_DIMENSIONS = '/<meta\s+property=["\']og:image:(width|height)["\']/i';

	/**
	 * Regex pattern for Open Graph locale variants.
	 *
	 * @var string
	 */
	const PATTERN_OG_LOCALE_ALTERNATE = '/<meta\s+property=["\']og:locale:alternate["\']/i';

	/**
	 * Regex pattern for article:published_time (Article schema).
	 *
	 * @var string
	 */
	const PATTERN_ARTICLE_PUBLISHED_TIME = '/<meta\s+property=["\']article:published_time["\']/i';

	/**
	 * Regex pattern for Twitter Card.
	 *
	 * @var string
	 */
	const PATTERN_TWITTER_CARD = '/<meta\s+name=["\']twitter:card["\']/i';

	/**
	 * Regex pattern for Twitter Creator/Site.
	 *
	 * @var string
	 */
	const PATTERN_TWITTER_CREATOR = '/<meta\s+name=["\']twitter:(creator|site)["\']/i';

	/**
	 * Regex pattern for Twitter Image.
	 *
	 * @var string
	 */
	const PATTERN_TWITTER_IMAGE = '/<meta\s+name=["\']twitter:image["\']/i';

	/**
	 * Regex pattern for Twitter Image dimensions.
	 *
	 * @var string
	 */
	const PATTERN_TWITTER_IMAGE_DIMENSIONS = '/<meta\s+name=["\']twitter:image:(width|height)["\']/i';

	/**
	 * Regex pattern for Twitter Image Alt.
	 *
	 * @var string
	 */
	const PATTERN_TWITTER_IMAGE_ALT = '/<meta\s+name=["\']twitter:image:alt["\']/i';

	/**
	 * Regex pattern for Twitter Description.
	 *
	 * @var string
	 */
	const PATTERN_TWITTER_DESCRIPTION = '/<meta\s+name=["\']twitter:description["\']/i';

	/**
	 * Regex pattern for Pinterest meta tag.
	 *
	 * @var string
	 */
	const PATTERN_PINTEREST = '/<meta\s+property=["\']pinterest:[^"\']*["\']/i';

	/**
	 * Regex pattern for Article Author.
	 *
	 * @var string
	 */
	const PATTERN_ARTICLE_AUTHOR = '/<meta\s+property=["\']article:author["\']/i';

	/**
	 * Regex pattern for Pinterest Media.
	 *
	 * @var string
	 */
	const PATTERN_PINTEREST_MEDIA = '/<meta\s+property=["\']pinterest:media["\']/i';

	/**
	 * Regex pattern for WooCommerce Product Price.
	 *
	 * @var string
	 */
	const PATTERN_PRODUCT_PRICE = '/<meta\s+property=["\']product:price["\']/i';

	/**
	 * Regex pattern for WooCommerce Product Availability.
	 *
	 * @var string
	 */
	const PATTERN_PRODUCT_AVAILABILITY = '/<meta\s+property=["\']product:availability["\']/i';

	/**
	 * Regex pattern for WooCommerce Product Rating.
	 *
	 * @var string
	 */
	const PATTERN_PRODUCT_RATING = '/<meta\s+property=["\']product:rating["\']/i';

	/**
	 * Regex pattern for WooCommerce Product Brand.
	 *
	 * @var string
	 */
	const PATTERN_PRODUCT_BRAND = '/<meta\s+property=["\']product:brand["\']/i';

	/**
	 * Regex pattern for viewport meta tag.
	 *
	 * @var string
	 */
	const PATTERN_VIEWPORT = '/<meta[^>]*name=["\']viewport["\'][^>]*content=["\']([^"\']*)["\']|<meta[^>]*content=["\']([^"\']*)["\'][^>]*name=["\']viewport["\']/i';

	/**
	 * Extract a URL component (host, scheme, path, etc).
	 *
	 * Uses wp_parse_url() which is WordPress-aware and safe.
	 *
	 * @since  1.6032.0900
	 * @param  string $url   The URL to parse.
	 * @param  int    $component PHP_URL_* constant (SCHEME, HOST, PORT, USER, PASS, PATH, QUERY, FRAGMENT).
	 * @return string|null The requested component, or null if not found.
	 */
	public static function get_url_component( $url, $component = PHP_URL_HOST ) {
		$parsed = wp_parse_url( $url );
		if ( ! is_array( $parsed ) ) {
			return null;
		}

		// Map PHP_URL_* constants to array keys.
		$component_map = array(
			PHP_URL_SCHEME   => 'scheme',
			PHP_URL_HOST     => 'host',
			PHP_URL_PORT     => 'port',
			PHP_URL_USER     => 'user',
			PHP_URL_PASS     => 'pass',
			PHP_URL_PATH     => 'path',
			PHP_URL_QUERY    => 'query',
			PHP_URL_FRAGMENT => 'fragment',
		);

		$key = $component_map[ $component ] ?? null;
		return $key ? $parsed[ $key ] ?? null : null;
	}

	/**
	 * Get the domain/host from a URL.
	 *
	 * @since  1.6032.0900
	 * @param  string $url The URL to extract domain from.
	 * @return string|null The domain/host, or null if not extractable.
	 */
	public static function get_domain( $url ) {
		return self::get_url_component( $url, PHP_URL_HOST );
	}

	/**
	 * Get the scheme (http/https) from a URL.
	 *
	 * @since  1.6032.0900
	 * @param  string $url The URL to extract scheme from.
	 * @return string|null The scheme ('http', 'https'), or null.
	 */
	public static function get_scheme( $url ) {
		return self::get_url_component( $url, PHP_URL_SCHEME );
	}

	/**
	 * Check if a URL has a www subdomain.
	 *
	 * @since  1.6032.0900
	 * @param  string $url The URL to check.
	 * @return bool True if URL contains www subdomain.
	 */
	public static function has_www( $url ) {
		return false !== strpos( $url, '://www.' );
	}

	/**
	 * Check if two URLs have the same domain.
	 *
	 * Compares just the host portions, ignoring scheme and path.
	 *
	 * @since  1.6032.0900
	 * @param  string $url1 First URL to compare.
	 * @param  string $url2 Second URL to compare.
	 * @return bool True if domains match.
	 */
	public static function same_domain( $url1, $url2 ) {
		$domain1 = self::get_domain( $url1 );
		$domain2 = self::get_domain( $url2 );
		return $domain1 && $domain2 && $domain1 === $domain2;
	}

	/**
	 * Check if two URLs have the same scheme (http or https).
	 *
	 * @since  1.6032.0900
	 * @param  string $url1 First URL to compare.
	 * @param  string $url2 Second URL to compare.
	 * @return bool True if schemes match.
	 */
	public static function same_scheme( $url1, $url2 ) {
		$scheme1 = self::get_scheme( $url1 );
		$scheme2 = self::get_scheme( $url2 );
		return $scheme1 && $scheme2 && $scheme1 === $scheme2;
	}

	/**
	 * Check if a meta tag exists in HTML content.
	 *
	 * Supports both attribute orders: property/name="..." content="..."
	 * and content="..." property/name="...".
	 *
	 * @since  1.6032.0900
	 * @param  string $html_content The HTML content to search.
	 * @param  string $pattern      Regex pattern (use PATTERN_* constants).
	 * @return bool True if meta tag is found.
	 */
	public static function has_meta_tag( $html_content, $pattern ) {
		return (bool) preg_match( $pattern, $html_content );
	}

	/**
	 * Extract meta tag content attribute value.
	 *
	 * Retrieves the content attribute value from a matching meta tag.
	 *
	 * @since  1.6032.0900
	 * @param  string $html_content The HTML content to search.
	 * @param  string $pattern      Regex pattern (use PATTERN_* constants).
	 * @return string|null The content value, or null if not found.
	 */
	public static function get_meta_tag_content( $html_content, $pattern ) {
		if ( preg_match( $pattern, $html_content, $matches ) ) {
			// Handle both attribute orders by checking matches.
			return $matches[1] ?? $matches[2] ?? null;
		}
		return null;
	}

	/**
	 * Extract meta tag content with full pattern matching.
	 *
	 * More flexible variant that allows custom patterns.
	 *
	 * @since  1.6032.0900
	 * @param  string $html_content The HTML content to search.
	 * @param  string $tag_name     The tag name to search for (e.g., 'og:image').
	 * @param  string $attribute    The attribute name ('property' or 'name').
	 * @return string|null The content value, or null if not found.
	 */
	public static function get_meta_tag_content_by_name( $html_content, $tag_name, $attribute = 'property' ) {
		$tag_name_escaped = preg_quote( $tag_name, '/' );
		$pattern           = '/<meta\s+' . $attribute . '=["\']' . $tag_name_escaped . '["\'].*?content=["\']([^"\']+)["\']|<meta\s+content=["\']([^"\']+)["\'].*?' . $attribute . '=["\']' . $tag_name_escaped . '["\']/i';

		return self::get_meta_tag_content( $html_content, $pattern );
	}

	/**
	 * Find all external URLs in HTML content.
	 *
	 * Extracts all URLs from src/href attributes that don't match the site domain.
	 *
	 * @since  1.6032.0900
	 * @param  string $html_content The HTML content to search.
	 * @param  string $site_domain  The site's domain (for comparison).
	 * @return array Array of external URLs found.
	 */
	public static function find_external_urls( $html_content, $site_domain ) {
		$external_urls = array();

		// Find all src and href attributes.
		if ( preg_match_all( '/(src|href)=["\']([^"\']+)["\']/', $html_content, $matches ) ) {
			foreach ( $matches[2] as $url ) {
				$url_domain = self::get_domain( $url );
				if ( $url_domain && $url_domain !== $site_domain ) {
					$external_urls[] = $url;
				}
			}
		}

		return array_unique( $external_urls );
	}

	/**
	 * Check if URL is internal (same domain as site).
	 *
	 * @since  1.6032.0900
	 * @param  string $url         The URL to check.
	 * @param  string $site_domain Optional. The site domain. Default: uses home_url().
	 * @return bool True if URL is internal (same domain).
	 */
	public static function is_internal_url( $url, $site_domain = null ) {
		if ( ! $site_domain ) {
			$site_domain = self::get_domain( home_url() );
		}
		return self::same_domain( $url, $site_domain );
	}
}
