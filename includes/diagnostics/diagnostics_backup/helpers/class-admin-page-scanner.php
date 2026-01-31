<?php
/**
 * Admin Page Scanner Helper
 *
 * Provides utility methods for capturing and analyzing admin page output
 * for diagnostic tests that need to scan HTML/accessibility/SEO.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin_Page_Scanner Class
 *
 * Captures admin page output using output buffering for analysis.
 */
class Admin_Page_Scanner {

	/**
	 * Capture output of an admin page
	 *
	 * @since  1.2601.2148
	 * @param  string $page_slug Admin page slug (e.g., 'index.php', 'options-general.php').
	 * @param  array  $query_args Optional. Additional query arguments.
	 * @return string|false Captured HTML output or false on failure.
	 */
	public static function capture_admin_page( string $page_slug, array $query_args = array() ) {
		// Verify we're in admin context
		if ( ! is_admin() ) {
			return false;
		}

		// Build URL with query args
		$url = admin_url( $page_slug );
		if ( ! empty( $query_args ) ) {
			$url = add_query_arg( $query_args, $url );
		}

		// Use WordPress HTTP API to fetch the page
		$response = wp_remote_get(
			$url,
			array(
				'timeout'     => 30,
				'redirection' => 5,
				'httpversion' => '1.1',
				'cookies'     => $_COOKIE,
				'headers'     => array(
					'Cookie' => self::get_cookie_header(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		return ! empty( $body ) ? $body : false;
	}

	/**
	 * Capture output of current admin page using output buffering
	 *
	 * This method should be called early (e.g., on 'admin_init' hook)
	 * to capture the entire page output.
	 *
	 * @since  1.2601.2148
	 * @return void
	 */
	public static function start_capture(): void {
		if ( ! is_admin() ) {
			return;
		}

		ob_start();
		add_action( 'shutdown', array( __CLASS__, 'end_capture' ), 0 );
	}

	/**
	 * End output capture and store result
	 *
	 * @since  1.2601.2148
	 * @return string Captured HTML output.
	 */
	public static function end_capture(): string {
		$output = ob_get_clean();
		
		// Store in transient for diagnostic access (5 minute expiry)
		\WPShadow\Core\Cache_Manager::set(
			'admin_page_capture',
			$output,
			'wpshadow_scanning',
			300
		);
		
		// Re-output the content so page renders normally
		echo $output;
		
		return $output;
	}

	/**
	 * Get stored captured output
	 *
	 * @since  1.2601.2148
	 * @return string|false Captured HTML or false if not available.
	 */
	public static function get_captured_output() {
		return \WPShadow\Core\Cache_Manager::get(
			'admin_page_capture',
			'wpshadow_scanning'
		);
	}

	/**
	 * Clear stored capture
	 *
	 * @since  1.2601.2148
	 * @return void
	 */
	public static function clear_capture(): void {
		\WPShadow\Core\Cache_Manager::delete(
			'admin_page_capture',
			'wpshadow_scanning'
		);
	}

	/**
	 * Get cookie header for authenticated requests
	 *
	 * @since  1.2601.2148
	 * @return string Cookie header string.
	 */
	private static function get_cookie_header(): string {
		$cookies = array();
		foreach ( $_COOKIE as $name => $value ) {
			$cookies[] = $name . '=' . $value;
		}
		return implode( '; ', $cookies );
	}

	/**
	 * Analyze HTML for common issues
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content to analyze.
	 * @return array Analysis results with various checks.
	 */
	public static function analyze_html( string $html ): array {
		$results = array(
			'has_doctype'         => self::has_doctype( $html ),
			'has_html_tag'        => self::has_html_tag( $html ),
			'has_head_tag'        => self::has_head_tag( $html ),
			'has_body_tag'        => self::has_body_tag( $html ),
			'has_title_tag'       => self::has_title_tag( $html ),
			'has_meta_charset'    => self::has_meta_charset( $html ),
			'has_meta_viewport'   => self::has_meta_viewport( $html ),
			'title_length'        => self::get_title_length( $html ),
			'h1_count'            => self::count_h1_tags( $html ),
			'missing_alt_images'  => self::count_missing_alt_images( $html ),
			'external_links'      => self::count_external_links( $html ),
			'inline_styles_count' => self::count_inline_styles( $html ),
		);

		return $results;
	}

	/**
	 * Check if HTML has DOCTYPE declaration
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return bool
	 */
	private static function has_doctype( string $html ): bool {
		return preg_match( '/<!DOCTYPE\s+html/i', $html ) === 1;
	}

	/**
	 * Check if HTML has <html> tag
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return bool
	 */
	private static function has_html_tag( string $html ): bool {
		return preg_match( '/<html[^>]*>/i', $html ) === 1;
	}

	/**
	 * Check if HTML has <head> tag
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return bool
	 */
	private static function has_head_tag( string $html ): bool {
		return preg_match( '/<head[^>]*>/i', $html ) === 1;
	}

	/**
	 * Check if HTML has <body> tag
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return bool
	 */
	private static function has_body_tag( string $html ): bool {
		return preg_match( '/<body[^>]*>/i', $html ) === 1;
	}

	/**
	 * Check if HTML has <title> tag
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return bool
	 */
	private static function has_title_tag( string $html ): bool {
		return preg_match( '/<title[^>]*>.*?<\/title>/is', $html ) === 1;
	}

	/**
	 * Check if HTML has meta charset
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return bool
	 */
	private static function has_meta_charset( string $html ): bool {
		return preg_match( '/<meta[^>]+charset=["\']?([^"\'>]+)/i', $html ) === 1;
	}

	/**
	 * Check if HTML has meta viewport
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return bool
	 */
	private static function has_meta_viewport( string $html ): bool {
		return preg_match( '/<meta[^>]+name=["\']viewport["\']/i', $html ) === 1;
	}

	/**
	 * Get title tag length
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return int Title length in characters, 0 if not found.
	 */
	private static function get_title_length( string $html ): int {
		if ( preg_match( '/<title[^>]*>(.*?)<\/title>/is', $html, $matches ) ) {
			return strlen( strip_tags( $matches[1] ) );
		}
		return 0;
	}

	/**
	 * Count H1 tags
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return int Number of H1 tags.
	 */
	private static function count_h1_tags( string $html ): int {
		return preg_match_all( '/<h1[^>]*>/i', $html );
	}

	/**
	 * Count images missing alt text
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return int Number of images without alt attribute.
	 */
	private static function count_missing_alt_images( string $html ): int {
		// Find all img tags
		preg_match_all( '/<img[^>]*>/i', $html, $matches );
		
		$missing_alt = 0;
		foreach ( $matches[0] as $img_tag ) {
			// Check if alt attribute exists
			if ( ! preg_match( '/\balt=["\']([^"\']*)["\']/', $img_tag ) ) {
				$missing_alt++;
			}
		}
		
		return $missing_alt;
	}

	/**
	 * Count external links
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return int Number of external links.
	 */
	private static function count_external_links( string $html ): int {
		$site_url = get_site_url();
		preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\']/i', $html, $matches );
		
		$external_count = 0;
		foreach ( $matches[1] as $url ) {
			// Skip anchors, mailto, tel, etc.
			if ( strpos( $url, '#' ) === 0 || strpos( $url, 'mailto:' ) === 0 || strpos( $url, 'tel:' ) === 0 ) {
				continue;
			}
			
			// Check if external
			if ( strpos( $url, 'http' ) === 0 && strpos( $url, $site_url ) === false ) {
				$external_count++;
			}
		}
		
		return $external_count;
	}

	/**
	 * Count inline style attributes
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return int Number of elements with inline styles.
	 */
	private static function count_inline_styles( string $html ): int {
		return preg_match_all( '/\sstyle=["\'][^"\']+["\']/i', $html );
	}

	/**
	 * Extract meta tags
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return array Array of meta tag attributes.
	 */
	public static function extract_meta_tags( string $html ): array {
		preg_match_all( '/<meta[^>]+>/i', $html, $matches );
		
		$meta_tags = array();
		foreach ( $matches[0] as $meta ) {
			$attributes = array();
			
			// Extract name
			if ( preg_match( '/name=["\']([^"\']+)["\']/i', $meta, $name_match ) ) {
				$attributes['name'] = $name_match[1];
			}
			
			// Extract property
			if ( preg_match( '/property=["\']([^"\']+)["\']/i', $meta, $prop_match ) ) {
				$attributes['property'] = $prop_match[1];
			}
			
			// Extract content
			if ( preg_match( '/content=["\']([^"\']+)["\']/i', $meta, $content_match ) ) {
				$attributes['content'] = $content_match[1];
			}
			
			if ( ! empty( $attributes ) ) {
				$meta_tags[] = $attributes;
			}
		}
		
		return $meta_tags;
	}

	/**
	 * Extract heading structure
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return array Array of headings with levels and text.
	 */
	public static function extract_headings( string $html ): array {
		$headings = array();
		
		for ( $level = 1; $level <= 6; $level++ ) {
			preg_match_all( "/<h{$level}[^>]*>(.*?)<\/h{$level}>/is", $html, $matches, PREG_SET_ORDER );
			
			foreach ( $matches as $match ) {
				$headings[] = array(
					'level' => $level,
					'text'  => strip_tags( $match[1] ),
				);
			}
		}
		
		return $headings;
	}
}
