<?php

declare(strict_types=1);
/**
 * Test: Meta Robots Tag Check
 *
 * Tests for proper meta robots configuration in HTML.
 *
 * Philosophy: Educate (#5) - Help users understand indexing control
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Meta_Robots extends Diagnostic_Base {


	protected static $slug        = 'test-seo-meta-robots';
	protected static $title       = 'Meta Robots Test';
	protected static $description = 'Tests for meta robots tag configuration';

	/**
	 * Run the diagnostic check
	 *
	 * PASS (returns null): Meta robots properly configured
	 * FAIL (returns array): Issues with meta robots configuration
	 *
	 * @param string|null $url URL to test (defaults to homepage)
	 * @param string|null $html Pre-fetched HTML to analyze
	 * @return array|null Finding data or null if no issue
	 */
	public static function check( ?string $url = null, ?string $html = null ): ?array {
		if ( $html !== null ) {
			return self::analyze_html( $html, $url ?? 'provided-html' );
		}

		$site_url = $url ?? home_url( '/' );

		if ( $url !== null && ! self::is_internal_url( $url ) ) {
			return self::error_result( 'Invalid URL', 'URL must be from this WordPress site' );
		}

		$html = self::fetch_html( $site_url );
		if ( $html === false ) {
			return self::error_result( 'Fetch Failed', 'Could not retrieve page HTML' );
		}

		return self::analyze_html( $html, $site_url );
	}

	/**
	 * Run comprehensive meta robots tests
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test results
	 */
	public static function run_meta_robots_tests( ?string $url = null, ?string $html = null ): array {
		$html = $html ?? self::fetch_html( $url ?? home_url( '/' ) );

		if ( $html === false ) {
			return array(
				'success' => false,
				'error'   => 'Could not fetch HTML',
				'url'     => $url ?? home_url( '/' ),
			);
		}

		$robots_tag = self::extract_meta_robots( $html );

		return array(
			'success'     => true,
			'url'         => $url ?? home_url( '/' ),
			'meta_robots' => $robots_tag,
			'tests'       => array(
				'no_noindex'                => self::test_no_noindex( $html ),
				'no_nofollow'               => self::test_no_nofollow( $html ),
				'allows_indexing'           => self::test_allows_indexing( $html ),
				'no_conflicting_directives' => self::test_no_conflicting_directives( $html ),
			),
			'summary'     => array(
				'passed'           => empty( $robots_tag ) || ( ! stripos( $robots_tag, 'noindex' ) && ! stripos( $robots_tag, 'nofollow' ) ),
				'blocks_indexing'  => stripos( $robots_tag, 'noindex' ) !== false,
				'blocks_following' => stripos( $robots_tag, 'nofollow' ) !== false,
			),
		);
	}

	/**
	 * Test for noindex directive
	 */
	public static function test_no_noindex( ?string $url = null, ?string $html = null ): array {
		$html       = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$robots_tag = self::extract_meta_robots( $html );

		$has_noindex = stripos( $robots_tag, 'noindex' ) !== false;

		return array(
			'test'    => 'no_noindex',
			'passed'  => ! $has_noindex,
			'value'   => $robots_tag,
			'message' => $has_noindex
				? 'Page is blocked from indexing (noindex)'
				: 'Page allows indexing',
			'impact'  => 'noindex prevents page from appearing in search results',
		);
	}

	/**
	 * Test for nofollow directive
	 */
	public static function test_no_nofollow( ?string $url = null, ?string $html = null ): array {
		$html       = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$robots_tag = self::extract_meta_robots( $html );

		$has_nofollow = stripos( $robots_tag, 'nofollow' ) !== false;

		return array(
			'test'    => 'no_nofollow',
			'passed'  => ! $has_nofollow,
			'value'   => $robots_tag,
			'message' => $has_nofollow
				? 'Page blocks link following (nofollow)'
				: 'Page allows link following',
			'impact'  => 'nofollow prevents search engines from following links',
		);
	}

	/**
	 * Test that page allows indexing
	 */
	public static function test_allows_indexing( ?string $url = null, ?string $html = null ): array {
		$html       = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$robots_tag = self::extract_meta_robots( $html );

		$blocks_indexing = stripos( $robots_tag, 'noindex' ) !== false ||
			stripos( $robots_tag, 'none' ) !== false;

		return array(
			'test'    => 'allows_indexing',
			'passed'  => ! $blocks_indexing,
			'value'   => $robots_tag,
			'message' => $blocks_indexing
				? 'Page blocks search engine indexing'
				: 'Page is indexable',
			'impact'  => 'Blocked pages won\'t appear in search results',
		);
	}

	/**
	 * Test for conflicting directives
	 */
	public static function test_no_conflicting_directives( ?string $url = null, ?string $html = null ): array {
		$html       = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$robots_tag = strtolower( self::extract_meta_robots( $html ) );

		// Check for conflicts
		$has_index_and_noindex   = strpos( $robots_tag, 'index' ) !== false &&
			strpos( $robots_tag, 'noindex' ) !== false;
		$has_follow_and_nofollow = strpos( $robots_tag, 'follow' ) !== false &&
			strpos( $robots_tag, 'nofollow' ) !== false;

		$conflicts = array();
		if ( $has_index_and_noindex ) {
			$conflicts[] = 'index + noindex';
		}
		if ( $has_follow_and_nofollow ) {
			$conflicts[] = 'follow + nofollow';
		}

		return array(
			'test'      => 'no_conflicting_directives',
			'passed'    => empty( $conflicts ),
			'conflicts' => $conflicts,
			'message'   => empty( $conflicts )
				? 'No conflicting meta robots directives'
				: sprintf( 'Conflicting directives: %s', implode( ', ', $conflicts ) ),
			'impact'    => 'Conflicting directives create unpredictable behavior',
		);
	}

	/**
	 * Analyze HTML for meta robots issues
	 *
	 * @param string $html HTML content
	 * @param string $checked_url URL that was checked
	 * @return array|null Finding or null
	 */
	protected static function analyze_html( string $html, string $checked_url ): ?array {
		$robots_tag = self::extract_meta_robots( $html );

		if ( empty( $robots_tag ) ) {
			return null; // PASS - No restrictive meta robots tag
		}

		$robots_lower = strtolower( $robots_tag );

		// Check for indexing blocks
		$blocks_indexing = stripos( $robots_lower, 'noindex' ) !== false ||
			stripos( $robots_lower, 'none' ) !== false;

		$blocks_following = stripos( $robots_lower, 'nofollow' ) !== false ||
			stripos( $robots_lower, 'none' ) !== false;

		if ( ! $blocks_indexing && ! $blocks_following ) {
			return null; // PASS - Allows indexing and following
		}

		// Build issue description
		$issues = array();
		if ( $blocks_indexing ) {
			$issues[] = 'blocks indexing (noindex)';
		}
		if ( $blocks_following ) {
			$issues[] = 'blocks link following (nofollow)';
		}

		$threat_level = 70; // High impact for SEO
		if ( $blocks_indexing && $blocks_following ) {
			$threat_level = 80; // Very high if both blocked
		}

		return array(
			'id'            => 'seo-meta-robots',
			'title'         => 'Page Blocking Search Engines',
			'description'   => sprintf(
				'This page %s. Unless intentional, this prevents the page from appearing in search results and/or passing link equity.',
				implode( ' and ', $issues )
			)
			'kb_link' => 'https://wpshadow.com/kb/meta-robots/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
			'training_link' => 'https://wpshadow.com/training/seo-indexing/',
			'auto_fixable'  => false,
			'threat_level'  => $threat_level,
			'module'        => 'SEO',
			'priority'      => 1,
			'meta'          => array(
				'robots_content'   => $robots_tag,
				'blocks_indexing'  => $blocks_indexing,
				'blocks_following' => $blocks_following,
				'checked_url'      => $checked_url,
			),
		);
	}

	/**
	 * Extract meta robots content
	 *
	 * @param string $html HTML content
	 * @return string Robots content or empty
	 */
	protected static function extract_meta_robots( string $html ): string {
		if ( empty( $html ) ) {
			return '';
		}

		// Meta robots tag
		if ( preg_match( '/<meta\s+name=["\']robots["\']\s+content=["\']([^"\']+)["\']/i', $html, $match ) ) {
			return $match[1];
		}

		// Also check reversed order (content before name)
		if ( preg_match( '/<meta\s+content=["\']([^"\']+)["\']\s+name=["\']robots["\']/i', $html, $match ) ) {
			return $match[1];
		}

		return '';
	}

	/**
	 * Fetch HTML from URL
	 *
	 * @param string $url URL to fetch
	 * @return string|false HTML or false on error
	 */
	protected static function fetch_html( string $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'WPShadow-Diagnostic/1.0 (SEO Checker)',
				'sslverify'  => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Check if URL is internal
	 *
	 * @param string $url URL to check
	 * @return bool
	 */
	protected static function is_internal_url( string $url ): bool {
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$test_host = wp_parse_url( $url, PHP_URL_HOST );
		return $site_host === $test_host;
	}

	/**
	 * Generate error result
	 *
	 * @param string $title Error title
	 * @param string $description Error description
	 * @return array Error result
	 */
	protected static function error_result( string $title, string $description ): array {
		return array(
			'id'            => 'seo-meta-robots',
			'title'         => $title,
			'description'   => $description
			'kb_link' => 'https://wpshadow.com/kb/meta-robots/',
			'training_link' => 'https://wpshadow.com/training/seo-indexing/',
			'auto_fixable'  => false,
			'threat_level'  => 50,
			'module'        => 'SEO',
			'priority'      => 2,
		);
	}

	/**
	 * Get the name for display
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Meta Robots Check', 'wpshadow' );
	}

	/**
	 * Get the description for display
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Checks HTML for meta robots directives that may block search engine indexing.', 'wpshadow' );
	}
}
