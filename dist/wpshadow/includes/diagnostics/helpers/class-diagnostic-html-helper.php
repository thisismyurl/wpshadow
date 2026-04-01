<?php
/**
 * Diagnostic HTML Helper
 *
 * Centralized helpers for fetching and parsing HTML in diagnostics.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Helpers;

use WPShadow\Core\External_Request_Guard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_HTML_Helper Class
 *
 * Provides shared utilities for HTTP fetching and DOM parsing.
 *
 * @since 0.6093.1200
 */
class Diagnostic_HTML_Helper {

	/**
	 * Fetch HTML from a URL.
	 *
	 * @since 0.6093.1200
	 * @param  string $url URL to request.
	 * @param  array  $args Optional. wp_remote_get args.
	 * @param  bool   $allow_empty Optional. Whether to allow empty body. Default false.
	 * @return string|null HTML content or null on failure.
	 */
	public static function fetch_html( string $url, array $args = array(), bool $allow_empty = false ): ?string {
		if ( ! function_exists( 'wp_remote_get' ) ) {
			return null;
		}

		$guard_purpose = isset( $args['guard_purpose'] ) ? sanitize_key( (string) $args['guard_purpose'] ) : 'diagnostics_html_fetch';
		unset( $args['guard_purpose'] );

		if ( ! External_Request_Guard::is_allowed( $guard_purpose ) ) {
			return null;
		}

		$defaults = array(
			'timeout'   => 5,
			'sslverify' => false,
		);

		$response = wp_remote_get( $url, wp_parse_args( $args, $defaults ) );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( '' === $body && ! $allow_empty ) {
			return null;
		}

		return $body;
	}

	/**
	 * Fetch HTML from the site homepage.
	 *
	 * @since 0.6093.1200
	 * @param  array $args Optional. wp_remote_get args.
	 * @return string|null HTML content or null on failure.
	 */
	public static function fetch_homepage_html( array $args = array() ): ?string {
		return self::fetch_html( home_url( '/' ), $args );
	}

	/**
	 * Fetch homepage HTML with transient caching.
	 *
	 * @since 0.6093.1200
	 * @param  string $cache_key Optional. Transient key. Default 'wpshadow_diagnostic_homepage_html'.
	 * @param  int    $ttl       Optional. Cache TTL in seconds. Default 300.
	 * @param  array  $args      Optional. wp_remote_get args.
	 * @return string|null HTML content or null on failure.
	 */
	public static function fetch_homepage_html_cached( string $cache_key = 'wpshadow_diagnostic_homepage_html', int $ttl = 300, array $args = array() ): ?string {
		$html = get_transient( $cache_key );
		if ( $html ) {
			return $html;
		}

		$html = self::fetch_homepage_html( $args );
		if ( $html ) {
			set_transient( $cache_key, $html, $ttl );
		}

		return $html;
	}

	/**
	 * Parse HTML into a DOMDocument.
	 *
	 * @since 0.6093.1200
	 * @param  string $html HTML markup.
	 * @return \DOMDocument|null Parsed DOM or null on failure.
	 */
	public static function parse_html( string $html ): ?\DOMDocument {
		$dom = new \DOMDocument();
		libxml_use_internal_errors( true );
		@$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
		libxml_clear_errors();

		return $dom;
	}

	/**
	 * Create a DOMXPath instance for a document.
	 *
	 * @since 0.6093.1200
	 * @param  \DOMDocument $dom DOM document.
	 * @return \DOMXPath XPath instance.
	 */
	public static function create_xpath( \DOMDocument $dom ): \DOMXPath {
		return new \DOMXPath( $dom );
	}
}
