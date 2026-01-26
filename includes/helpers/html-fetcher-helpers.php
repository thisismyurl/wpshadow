<?php
/**
 * HTML Fetcher Helper Functions
 *
 * Provides utilities for fetching and caching HTML from pages (frontend/admin).
 * Used by diagnostics that need to analyze rendered HTML output.
 *
 * @package WPShadow
 * @subpackage Helpers
 * @since 1.2601.2200
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetch and cache HTML from a URL.
 *
 * Retrieves HTML content via wp_remote_get() and caches it using WordPress transients.
 * Automatically handles errors and returns WP_Error on failure.
 *
 * Cache is invalidated when treatments run (via wpshadow_clear_html_cache).
 *
 * @since  1.2601.2200
 * @param  string $url         URL to fetch. Should be local domain for security.
 * @param  int    $cache_ttl   Cache duration in seconds. Default 3600 (1 hour).
 * @param  string $cache_group Cache group for categorization. Default 'wpshadow_html'.
 * @return string|WP_Error HTML content on success, WP_Error on failure.
 */
function wpshadow_fetch_page_html( string $url, int $cache_ttl = 3600, string $cache_group = 'wpshadow_html' ) {
	// Validate URL is from same domain (security)
	$site_url = get_site_url();
	if ( strpos( $url, $site_url ) !== 0 && strpos( $url, home_url() ) !== 0 ) {
		return new WP_Error(
			'invalid_url',
			__( 'URL must be from the same domain for security reasons.', 'wpshadow' )
		);
	}

	// Check cache first
	$cache_key = 'html_' . md5( $url );
	$cached    = get_transient( $cache_group . '_' . $cache_key );

	if ( false !== $cached ) {
		return $cached;
	}

	// Fetch via wp_remote_get
	$response = wp_remote_get(
		$url,
		array(
			'timeout'     => 15,
			'user-agent'  => 'WPShadow/' . WPSHADOW_VERSION,
			'sslverify'   => apply_filters( 'wpshadow_html_fetch_ssl_verify', true ),
			'redirection' => 5,
		)
	);

	if ( is_wp_error( $response ) ) {
		// Log error but don't cache failures
		if ( function_exists( 'wpshadow_log_error' ) ) {
			wpshadow_log_error(
				sprintf(
					/* translators: 1: URL, 2: error message */
					__( 'Failed to fetch HTML from %1$s: %2$s', 'wpshadow' ),
					$url,
					$response->get_error_message()
				)
			);
		}
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		$error = new WP_Error(
			'http_error',
			sprintf(
				/* translators: 1: HTTP status code, 2: URL */
				__( 'HTTP %1$d when fetching %2$s', 'wpshadow' ),
				$code,
				$url
			)
		);

		// Log non-200 responses
		if ( function_exists( 'wpshadow_log_error' ) ) {
			wpshadow_log_error( $error->get_error_message() );
		}

		return $error;
	}

	$html = wp_remote_retrieve_body( $response );

	if ( empty( $html ) ) {
		return new WP_Error(
			'empty_response',
			__( 'Empty HTML response received.', 'wpshadow' )
		);
	}

	// Cache the result
	set_transient( $cache_group . '_' . $cache_key, $html, $cache_ttl );

	return $html;
}

/**
 * Get homepage HTML.
 *
 * Convenience wrapper for fetching frontend homepage with appropriate caching.
 *
 * @since  1.2601.2200
 * @param  int $cache_ttl Cache duration in seconds. Default 3600 (1 hour).
 * @return string|WP_Error HTML content or WP_Error on failure.
 */
function wpshadow_get_homepage_html( int $cache_ttl = 3600 ) {
	return wpshadow_fetch_page_html( home_url(), $cache_ttl, 'wpshadow_frontend' );
}

/**
 * Get admin page HTML.
 *
 * Convenience wrapper for fetching admin pages with appropriate caching.
 *
 * @since  1.2601.2200
 * @param  string $page_slug Admin page slug (e.g., 'index.php', 'plugins.php').
 * @param  int    $cache_ttl Cache duration in seconds. Default 1800 (30 minutes).
 * @return string|WP_Error HTML content or WP_Error on failure.
 */
function wpshadow_get_admin_page_html( string $page_slug, int $cache_ttl = 1800 ) {
	$url = admin_url( $page_slug );
	return wpshadow_fetch_page_html( $url, $cache_ttl, 'wpshadow_admin' );
}

/**
 * Get single post/page HTML.
 *
 * Convenience wrapper for fetching individual post/page HTML.
 *
 * @since  1.2601.2200
 * @param  int $post_id   Post ID.
 * @param  int $cache_ttl Cache duration in seconds. Default 3600 (1 hour).
 * @return string|WP_Error HTML content or WP_Error on failure.
 */
function wpshadow_get_post_html( int $post_id, int $cache_ttl = 3600 ) {
	$url = get_permalink( $post_id );
	if ( ! $url ) {
		return new WP_Error( 'invalid_post', __( 'Invalid post ID or no permalink set.', 'wpshadow' ) );
	}
	return wpshadow_fetch_page_html( $url, $cache_ttl, 'wpshadow_posts' );
}

/**
 * Clear all HTML caches.
 *
 * Called after treatments are applied to ensure diagnostics get fresh HTML.
 * Hook this to 'wpshadow_after_treatment_apply' action.
 *
 * @since 1.2601.2200
 * @return int Number of transients deleted.
 */
function wpshadow_clear_html_cache(): int {
	global $wpdb;

	$deleted = 0;
	$groups  = array( 'wpshadow_html', 'wpshadow_frontend', 'wpshadow_admin', 'wpshadow_posts' );

	foreach ( $groups as $group ) {
		// Get all transient names starting with this group prefix
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- No native WP function for bulk transient deletion
		$transient_names = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT REPLACE(option_name, '_transient_', '') FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_' . $group ) . '%'
			)
		);

		// Use native WordPress function to delete each transient
		foreach ( $transient_names as $transient_name ) {
			if ( delete_transient( $transient_name ) ) {
				++$deleted;
			}
		}
	}

	return $deleted;
}

/**
 * Hook to clear HTML cache after treatments run.
 *
 * Ensures diagnostics get fresh HTML after site changes.
 *
 * @since 1.2601.2200
 */
add_action( 'wpshadow_after_treatment_apply', 'wpshadow_clear_html_cache' );

/**
 * Parse HTML for specific elements using simple string matching.
 *
 * More efficient than DOMDocument for simple checks.
 *
 * @since  1.2601.2200
 * @param  string $html    HTML content to search.
 * @param  string $pattern Pattern to search for (can be regex if $is_regex is true).
 * @param  bool   $is_regex Whether pattern is regex. Default false (simple string search).
 * @return bool True if pattern found, false otherwise.
 */
function wpshadow_html_contains( string $html, string $pattern, bool $is_regex = false ): bool {
	if ( $is_regex ) {
		return (bool) preg_match( $pattern, $html );
	}

	return false !== strpos( $html, $pattern );
}

/**
 * Count occurrences of pattern in HTML.
 *
 * @since  1.2601.2200
 * @param  string $html    HTML content to search.
 * @param  string $pattern Pattern to count.
 * @return int Number of occurrences.
 */
function wpshadow_html_count_pattern( string $html, string $pattern ): int {
	return substr_count( $html, $pattern );
}
