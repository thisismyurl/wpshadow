<?php
declare(strict_types=1);
/**
 * Test: Missing H1 Tag Check
 *
 * Tests if HTML contains proper H1 tags (exactly one per page).
 *
 * Philosophy: Educate (#5, #6) - Help users understand H1 importance for SEO
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Missing_H1_Tag extends Diagnostic_Base {

	protected static $slug        = 'test-seo-missing-h1-tag';
	protected static $title       = 'H1 Tag Test';
	protected static $description = 'Tests for missing or multiple H1 tags';

	/**
	 * Test types
	 */
	const TEST_MISSING  = 'missing';
	const TEST_MULTIPLE = 'multiple';
	const TEST_EMPTY    = 'empty';

	/**
	 * Run the diagnostic check
	 *
	 * PASS (returns null): Exactly one H1 tag with content
	 * FAIL (returns array): No H1, multiple H1s, or empty H1
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
	 * Run comprehensive H1 tests
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test results
	 */
	public static function run_h1_tests( ?string $url = null, ?string $html = null ): array {
		$html = $html ?? self::fetch_html( $url ?? home_url( '/' ) );

		if ( $html === false ) {
			return array(
				'success' => false,
				'error'   => 'Could not fetch HTML',
				'url'     => $url ?? home_url( '/' ),
			);
		}

		$h1_tags = self::extract_h1_tags( $html );
		$count   = count( $h1_tags );

		return array(
			'success'  => true,
			'url'      => $url ?? home_url( '/' ),
			'h1_count' => $count,
			'h1_tags'  => $h1_tags,
			'tests'    => array(
				'has_h1'       => self::test_has_h1( $html ),
				'single_h1'    => self::test_single_h1( $html ),
				'h1_not_empty' => self::test_h1_not_empty( $html ),
			),
			'summary'  => array(
				'passed' => $count === 1 && ! empty( trim( strip_tags( $h1_tags[0] ?? '' ) ) ),
				'issue'  => self::determine_issue( $count, $h1_tags ),
			),
		);
	}

	/**
	 * Test if page has at least one H1
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test result
	 */
	public static function test_has_h1( ?string $url = null, ?string $html = null ): array {
		$html    = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$h1_tags = self::extract_h1_tags( $html );

		return array(
			'test'    => 'has_h1',
			'passed'  => count( $h1_tags ) > 0,
			'found'   => count( $h1_tags ),
			'message' => count( $h1_tags ) > 0
				? 'Page has H1 tag(s)'
				: 'No H1 tags found',
			'impact'  => 'H1 tags help search engines understand page topic',
		);
	}

	/**
	 * Test if page has exactly one H1
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test result
	 */
	public static function test_single_h1( ?string $url = null, ?string $html = null ): array {
		$html    = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$h1_tags = self::extract_h1_tags( $html );
		$count   = count( $h1_tags );

		return array(
			'test'    => 'single_h1',
			'passed'  => $count === 1,
			'found'   => $count,
			'message' => $count === 1
				? 'Page has exactly one H1 tag (ideal)'
				: "Page has {$count} H1 tags (should be 1)",
			'impact'  => 'Multiple H1 tags can dilute SEO value and confuse search engines',
		);
	}

	/**
	 * Test if H1 is not empty
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test result
	 */
	public static function test_h1_not_empty( ?string $url = null, ?string $html = null ): array {
		$html    = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$h1_tags = self::extract_h1_tags( $html );

		if ( empty( $h1_tags ) ) {
			return array(
				'test'    => 'h1_not_empty',
				'passed'  => false,
				'message' => 'No H1 tags to check',
				'impact'  => 'Empty H1 provides no SEO value',
			);
		}

		$h1_text  = trim( strip_tags( $h1_tags[0] ) );
		$is_empty = empty( $h1_text );

		return array(
			'test'    => 'h1_not_empty',
			'passed'  => ! $is_empty,
			'h1_text' => $h1_text,
			'message' => $is_empty
				? 'H1 tag is empty'
				: 'H1 has content',
			'impact'  => 'Empty H1 provides no SEO value',
		);
	}

	/**
	 * Analyze HTML for H1 issues
	 *
	 * @param string $html HTML content
	 * @param string $checked_url URL that was checked
	 * @return array|null Finding or null
	 */
	protected static function analyze_html( string $html, string $checked_url ): ?array {
		$h1_tags = self::extract_h1_tags( $html );
		$count   = count( $h1_tags );

		// Perfect: exactly one non-empty H1
		if ( $count === 1 ) {
			$h1_text = trim( strip_tags( $h1_tags[0] ) );
			if ( ! empty( $h1_text ) ) {
				return null; // PASS
			}
			// Empty H1 = FAIL
			return array(
				'id'            => 'seo-missing-h1-tag',
				'title'         => 'Empty H1 Tag',
				'description'   => 'Your page has an H1 tag but it is empty. Add meaningful content to help search engines understand your page topic.'
				'kb_link' => 'https://wpshadow.com/kb/h1-tags/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
				'training_link' => 'https://wpshadow.com/training/seo-h1-tags/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
				'module'        => 'SEO',
				'priority'      => 1,
				'meta'          => array(
					'h1_count'    => $count,
					'issue'       => 'empty',
					'checked_url' => $checked_url,
				),
			);
		}

		// No H1 = FAIL
		if ( $count === 0 ) {
			return array(
				'id'            => 'seo-missing-h1-tag',
				'title'         => 'Missing H1 Tag',
				'description'   => 'Your page is missing an H1 tag. H1 tags are crucial for SEO as they tell search engines what your page is about.'
				'kb_link' => 'https://wpshadow.com/kb/h1-tags/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
				'training_link' => 'https://wpshadow.com/training/seo-h1-tags/',
				'auto_fixable'  => false,
				'threat_level'  => 70,
				'module'        => 'SEO',
				'priority'      => 1,
				'meta'          => array(
					'h1_count'    => 0,
					'issue'       => 'missing',
					'checked_url' => $checked_url,
				),
			);
		}

		// Multiple H1s = FAIL
		return array(
			'id'            => 'seo-missing-h1-tag',
			'title'         => 'Multiple H1 Tags',
			'description'   => sprintf(
				'Your page has %d H1 tags. Best practice is to use exactly one H1 per page to avoid diluting SEO value.',
				$count
			)
			'kb_link' => 'https://wpshadow.com/kb/h1-tags/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
			'training_link' => 'https://wpshadow.com/training/seo-h1-tags/',
			'auto_fixable'  => false,
			'threat_level'  => 50,
			'module'        => 'SEO',
			'priority'      => 2,
			'meta'          => array(
				'h1_count'    => $count,
				'h1_tags'     => array_map( 'strip_tags', $h1_tags ),
				'issue'       => 'multiple',
				'checked_url' => $checked_url,
			),
		);
	}

	/**
	 * Extract H1 tags from HTML
	 *
	 * @param string $html HTML content
	 * @return array H1 tags found
	 */
	protected static function extract_h1_tags( string $html ): array {
		if ( $html === false || empty( $html ) ) {
			return array();
		}

		preg_match_all( '/<h1[^>]*>(.*?)<\/h1>/is', $html, $matches );
		return $matches[1] ?? array();
	}

	/**
	 * Determine the specific issue
	 *
	 * @param int $count H1 count
	 * @param array $h1_tags H1 tags
	 * @return string Issue type
	 */
	protected static function determine_issue( int $count, array $h1_tags ): string {
		if ( $count === 0 ) {
			return 'missing';
		}
		if ( $count > 1 ) {
			return 'multiple';
		}
		if ( empty( trim( strip_tags( $h1_tags[0] ?? '' ) ) ) ) {
			return 'empty';
		}
		return 'none';
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
				'timeout'     => 10,
				'redirection' => 5,
				'user-agent'  => 'WPShadow-Diagnostic/1.0 (SEO Checker)',
				'sslverify'   => false,
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
			'id'            => 'seo-missing-h1-tag',
			'title'         => $title,
			'description'   => $description
			'kb_link' => 'https://wpshadow.com/kb/h1-tags/',
			'training_link' => 'https://wpshadow.com/training/seo-h1-tags/',
			'auto_fixable'  => false,
			'threat_level'  => 30,
			'module'        => 'SEO',
			'priority'      => 3,
		);
	}

	/**
	 * Get the name for display
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'H1 Tag Check', 'wpshadow' );
	}

	/**
	 * Get the description for display
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Checks HTML for proper H1 tag usage (should be exactly one per page).', 'wpshadow' );
	}
}
