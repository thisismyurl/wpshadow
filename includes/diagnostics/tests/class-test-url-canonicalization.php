<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: URL Canonicalization
 *
 * Checks if canonical URLs are properly set to prevent duplicate content issues.
 * Duplicate content hurts SEO and confuses search engines.
 *
 * @since 1.2.0
 */
class Test_Url_Canonicalization extends Diagnostic_Base {


	/**
	 * Check URL canonicalization
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$canonicalization = self::check_canonicalization();

		if ( $canonicalization['threat_level'] === 0 ) {
			return null;
		}

		return array(
			'threat_level'  => $canonicalization['threat_level'],
			'threat_color'  => 'yellow',
			'passed'        => false,
			'issue'         => $canonicalization['issue'],
			'metadata'      => $canonicalization,
			'kb_link'       => 'https://wpshadow.com/kb/wordpress-seo-canonicalization/',
			'training_link' => 'https://wpshadow.com/training/wordpress-seo-canonical-urls/',
		);
	}

	/**
	 * Guardian Sub-Test: Canonical tag output
	 *
	 * @return array Test result
	 */
	public static function test_canonical_tag(): array {
		ob_start();
		wp_head();
		$head_output = ob_get_clean();

		$has_canonical = strpos( $head_output, 'rel="canonical"' ) !== false;

		return array(
			'test_name'     => 'Canonical Tag',
			'has_canonical' => $has_canonical,
			'passed'        => $has_canonical,
			'description'   => $has_canonical ? 'Canonical tags being output' : 'No canonical tags found in head',
		);
	}

	/**
	 * Guardian Sub-Test: WWW canonicalization
	 *
	 * @return array Test result
	 */
	public static function test_www_canonicalization(): array {
		$home_url    = get_home_url();
		$www_version = str_replace( 'https://', 'https://www.', $home_url );
		$www_version = str_replace( 'http://', 'http://www.', $www_version );

		$has_www = strpos( $home_url, 'www.' ) !== false;

		// Check both versions are accessible
		$response_no_www   = wp_remote_get( $home_url, array( 'timeout' => 5 ) );
		$response_with_www = wp_remote_get( $www_version, array( 'timeout' => 5 ) );

		$both_accessible = ! is_wp_error( $response_no_www ) && ! is_wp_error( $response_with_www );

		return array(
			'test_name'       => 'WWW Canonicalization',
			'has_www'         => $has_www,
			'both_accessible' => $both_accessible,
			'passed'          => $both_accessible && $has_www,
			'description'     => sprintf( 'Current: %s, Both accessible: %s', $has_www ? 'with www' : 'without www', $both_accessible ? 'Yes' : 'No' ),
		);
	}

	/**
	 * Guardian Sub-Test: HTTPS canonicalization
	 *
	 * @return array Test result
	 */
	public static function test_https_canonicalization(): array {
		$home_url      = get_home_url();
		$is_https      = is_ssl();
		$http_version  = str_replace( 'https://', 'http://', $home_url );
		$https_version = str_replace( 'http://', 'https://', $home_url );

		// Check if redirect from HTTP to HTTPS works
		$response_http   = wp_remote_get(
			$http_version,
			array(
				'timeout'     => 5,
				'redirection' => 0,
			)
		);
		$redirect_status = is_wp_error( $response_http ) ? null : wp_remote_retrieve_response_code( $response_http );

		// 301 or 302 means redirect
		$has_redirect = in_array( $redirect_status, array( 301, 302, 307, 308 ), true );

		return array(
			'test_name'    => 'HTTPS Canonicalization',
			'using_https'  => $is_https,
			'has_redirect' => $has_redirect,
			'passed'       => $is_https && $has_redirect,
			'description'  => sprintf( 'HTTPS: %s, HTTP→HTTPS Redirect: %s', $is_https ? 'Yes' : 'No', $has_redirect ? 'Yes' : 'No' ),
		);
	}

	/**
	 * Guardian Sub-Test: Duplicate content detection
	 *
	 * @return array Test result
	 */
	public static function test_duplicate_content(): array {
		// Check for common duplicate content issues
		global $wpdb;

		// Posts with duplicate slugs (unlikely but possible)
		$duplicates = $wpdb->get_results(
			"SELECT post_name, COUNT(*) as cnt FROM {$wpdb->posts} WHERE post_status = 'publish' GROUP BY post_name HAVING cnt > 1"
		);

		// Category duplicates
		$cat_duplicates = $wpdb->get_results(
			"SELECT slug, COUNT(*) as cnt FROM {$wpdb->terms} WHERE slug IS NOT NULL GROUP BY slug HAVING cnt > 1"
		);

		$has_duplicates = ! empty( $duplicates ) || ! empty( $cat_duplicates );

		return array(
			'test_name'       => 'Duplicate Content',
			'post_duplicates' => count( $duplicates ),
			'term_duplicates' => count( $cat_duplicates ),
			'passed'          => ! $has_duplicates,
			'description'     => $has_duplicates ? sprintf( 'Found %d duplicate content issues', count( $duplicates ) + count( $cat_duplicates ) ) : 'No duplicate content detected',
		);
	}

	/**
	 * Check canonicalization setup
	 *
	 * @return array Canonicalization check
	 */
	private static function check_canonicalization(): array {
		$threat_level = 0;
		$issues       = array();

		// Check for canonical tag
		ob_start();
		wp_head();
		$head_output = ob_get_clean();

		if ( strpos( $head_output, 'rel="canonical"' ) === false ) {
			$issues[]     = 'Canonical tags not being output';
			$threat_level = 25;
		}

		// Check HTTPS canonicalization
		if ( ! is_ssl() ) {
			$issues[]     = 'Not using HTTPS';
			$threat_level = max( $threat_level, 30 );
		}

		// Check for WWW canonicalization
		$home_url = get_home_url();
		$siteurl  = get_option( 'siteurl' );

		if ( $home_url !== $siteurl ) {
			$issues[]     = 'Home URL and site URL mismatch';
			$threat_level = max( $threat_level, 20 );
		}

		// Check for duplicate content
		global $wpdb;
		$duplicates = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' GROUP BY post_name HAVING COUNT(*) > 1"
		);

		if ( $duplicates > 0 ) {
			$issues[]     = sprintf( '%d posts with duplicate slugs', $duplicates );
			$threat_level = max( $threat_level, 35 );
		}

		$issue = ! empty( $issues ) ? implode( '; ', $issues ) : 'URL canonicalization is properly configured';

		return array(
			'threat_level'  => $threat_level,
			'issue'         => $issue,
			'is_https'      => is_ssl(),
			'has_canonical' => strpos( $head_output, 'rel="canonical"' ) !== false,
		);
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'URL Canonicalization';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Checks if canonical URLs are properly set to prevent duplicate content issues';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'SEO';
	}
}
