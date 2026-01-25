<?php
declare(strict_types=1);
/**
 * Test: Meta Description Check
 *
 * Tests if HTML contains proper meta description tags with appropriate length.
 *
 * Philosophy: Educate (#5, #6) - Help users optimize meta descriptions for CTR
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Meta_Description extends Diagnostic_Base {

	protected static $slug        = 'test-seo-meta-description';
	protected static $title       = 'Meta Description Test';
	protected static $description = 'Tests for missing or poorly optimized meta descriptions';

	/**
	 * Optimal length range
	 */
	const MIN_LENGTH   = 120;
	const MAX_LENGTH   = 160;
	const ABSOLUTE_MAX = 320; // Google sometimes shows longer on mobile

	/**
	 * Run the diagnostic check
	 *
	 * PASS (returns null): Meta description exists and is 120-160 characters
	 * FAIL (returns array): Missing, too short, too long, or empty
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
	 * Run comprehensive meta description tests
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test results
	 */
	public static function run_meta_description_tests( ?string $url = null, ?string $html = null ): array {
		$html = $html ?? self::fetch_html( $url ?? home_url( '/' ) );

		if ( $html === false ) {
			return array(
				'success' => false,
				'error'   => 'Could not fetch HTML',
				'url'     => $url ?? home_url( '/' ),
			);
		}

		$description = self::extract_meta_description( $html );
		$length      = mb_strlen( $description );

		return array(
			'success'     => true,
			'url'         => $url ?? home_url( '/' ),
			'description' => $description,
			'length'      => $length,
			'tests'       => array(
				'has_description' => self::test_has_description( $html ),
				'optimal_length'  => self::test_optimal_length( $html ),
				'not_empty'       => self::test_not_empty( $html ),
				'not_too_short'   => self::test_not_too_short( $html ),
				'not_too_long'    => self::test_not_too_long( $html ),
			),
			'summary'     => array(
				'passed'         => ! empty( $description ) && $length >= self::MIN_LENGTH && $length <= self::MAX_LENGTH,
				'optimal'        => $length >= self::MIN_LENGTH && $length <= self::MAX_LENGTH,
				'recommendation' => self::get_recommendation( $description, $length ),
			),
		);
	}

	/**
	 * Test if meta description exists
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test result
	 */
	public static function test_has_description( ?string $url = null, ?string $html = null ): array {
		$html        = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$description = self::extract_meta_description( $html );

		return array(
			'test'    => 'has_description',
			'passed'  => ! empty( $description ),
			'message' => ! empty( $description )
				? 'Meta description tag found'
				: 'No meta description tag',
			'impact'  => 'Meta descriptions improve click-through rates from search results',
		);
	}

	/**
	 * Test if description is optimal length
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test result
	 */
	public static function test_optimal_length( ?string $url = null, ?string $html = null ): array {
		$html        = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$description = self::extract_meta_description( $html );
		$length      = mb_strlen( $description );

		$optimal = $length >= self::MIN_LENGTH && $length <= self::MAX_LENGTH;

		return array(
			'test'          => 'optimal_length',
			'passed'        => $optimal,
			'length'        => $length,
			'optimal_range' => self::MIN_LENGTH . '-' . self::MAX_LENGTH,
			'message'       => $optimal
				? "Length is optimal ({$length} characters)"
				: "Length is {$length} characters (optimal: " . self::MIN_LENGTH . '-' . self::MAX_LENGTH . ')',
			'impact'        => 'Optimal length ensures full description appears in search results',
		);
	}

	/**
	 * Test if description is not empty
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test result
	 */
	public static function test_not_empty( ?string $url = null, ?string $html = null ): array {
		$html        = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$description = self::extract_meta_description( $html );
		$is_empty    = empty( trim( $description ) );

		return array(
			'test'    => 'not_empty',
			'passed'  => ! $is_empty,
			'message' => $is_empty
				? 'Meta description is empty'
				: 'Meta description has content',
			'impact'  => 'Empty descriptions provide no value in search results',
		);
	}

	/**
	 * Test if description is not too short
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test result
	 */
	public static function test_not_too_short( ?string $url = null, ?string $html = null ): array {
		$html        = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$description = self::extract_meta_description( $html );
		$length      = mb_strlen( $description );

		return array(
			'test'    => 'not_too_short',
			'passed'  => $length >= self::MIN_LENGTH,
			'length'  => $length,
			'minimum' => self::MIN_LENGTH,
			'message' => $length >= self::MIN_LENGTH
				? "Length is adequate ({$length} chars)"
				: "Too short ({$length} chars, minimum " . self::MIN_LENGTH . ')',
			'impact'  => 'Short descriptions miss opportunity to engage users',
		);
	}

	/**
	 * Test if description is not too long
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test result
	 */
	public static function test_not_too_long( ?string $url = null, ?string $html = null ): array {
		$html        = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$description = self::extract_meta_description( $html );
		$length      = mb_strlen( $description );

		return array(
			'test'    => 'not_too_long',
			'passed'  => $length <= self::MAX_LENGTH,
			'length'  => $length,
			'maximum' => self::MAX_LENGTH,
			'message' => $length <= self::MAX_LENGTH
				? "Length is within limits ({$length} chars)"
				: "Too long ({$length} chars, maximum " . self::MAX_LENGTH . ')',
			'impact'  => 'Long descriptions get truncated in search results',
		);
	}

	/**
	 * Analyze HTML for meta description issues
	 *
	 * @param string $html HTML content
	 * @param string $checked_url URL that was checked
	 * @return array|null Finding or null
	 */
	protected static function analyze_html( string $html, string $checked_url ): ?array {
		$description = self::extract_meta_description( $html );
		$length      = mb_strlen( $description );

		// Perfect: description exists and is optimal length
		if ( ! empty( $description ) && $length >= self::MIN_LENGTH && $length <= self::MAX_LENGTH ) {
			return null; // PASS
		}

		// Missing = FAIL
		if ( empty( $description ) ) {
			return array(
				'id'            => 'seo-meta-description',
				'title'         => 'Missing Meta Description',
				'description'   => 'Your page is missing a meta description tag. Meta descriptions appear in search results and significantly impact click-through rates.'
				'kb_link' => 'https://wpshadow.com/kb/meta-description/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
				'training_link' => 'https://wpshadow.com/training/seo-meta-tags/',
				'auto_fixable'  => false,
				'threat_level'  => 70,
				'module'        => 'SEO',
				'priority'      => 1,
				'meta'          => array(
					'length'      => 0,
					'issue'       => 'missing',
					'checked_url' => $checked_url,
				),
			);
		}

		// Too short = FAIL
		if ( $length < self::MIN_LENGTH ) {
			return array(
				'id'            => 'seo-meta-description',
				'title'         => 'Meta Description Too Short',
				'description'   => sprintf(
					'Your meta description is only %d characters. Aim for %d-%d characters to maximize impact in search results.',
					$length,
					self::MIN_LENGTH,
					self::MAX_LENGTH
				)
				'kb_link' => 'https://wpshadow.com/kb/meta-description/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
				'training_link' => 'https://wpshadow.com/training/seo-meta-tags/',
				'auto_fixable'  => false,
				'threat_level'  => 50,
				'module'        => 'SEO',
				'priority'      => 2,
				'meta'          => array(
					'length'      => $length,
					'description' => $description,
					'issue'       => 'too_short',
					'checked_url' => $checked_url,
				),
			);
		}

		// Too long = FAIL
		return array(
			'id'            => 'seo-meta-description',
			'title'         => 'Meta Description Too Long',
			'description'   => sprintf(
				'Your meta description is %d characters. It will be truncated in search results. Aim for %d-%d characters.',
				$length,
				self::MIN_LENGTH,
				self::MAX_LENGTH
			)
			'kb_link' => 'https://wpshadow.com/kb/meta-description/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
			'training_link' => 'https://wpshadow.com/training/seo-meta-tags/',
			'auto_fixable'  => false,
			'threat_level'  => 40,
			'module'        => 'SEO',
			'priority'      => 2,
			'meta'          => array(
				'length'      => $length,
				'description' => $description,
				'issue'       => 'too_long',
				'checked_url' => $checked_url,
			),
		);
	}

	/**
	 * Extract meta description from HTML
	 *
	 * @param string $html HTML content
	 * @return string Meta description or empty string
	 */
	protected static function extract_meta_description( string $html ): string {
		if ( empty( $html ) ) {
			return '';
		}

		// Try standard meta description
		if ( preg_match( '/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/i', $html, $matches ) ) {
			return html_entity_decode( $matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		}

		// Try OG description as fallback
		if ( preg_match( '/<meta\s+property=["\']og:description["\']\s+content=["\'](.*?)["\']/i', $html, $matches ) ) {
			return html_entity_decode( $matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		}

		return '';
	}

	/**
	 * Get recommendation based on description and length
	 *
	 * @param string $description Description text
	 * @param int $length Character count
	 * @return string Recommendation
	 */
	protected static function get_recommendation( string $description, int $length ): string {
		if ( empty( $description ) ) {
			return 'Add a meta description tag to your page';
		}
		if ( $length < self::MIN_LENGTH ) {
			return sprintf( 'Expand description to at least %d characters', self::MIN_LENGTH );
		}
		if ( $length > self::MAX_LENGTH ) {
			return sprintf( 'Shorten description to %d characters or less', self::MAX_LENGTH );
		}
		return 'Description is optimized!';
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
			'id'            => 'seo-meta-description',
			'title'         => $title,
			'description'   => $description
			'kb_link' => 'https://wpshadow.com/kb/meta-description/',
			'training_link' => 'https://wpshadow.com/training/seo-meta-tags/',
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
		return __( 'Meta Description Check', 'wpshadow' );
	}

	/**
	 * Get the description for display
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Checks HTML for proper meta description tags with optimal length (120-160 characters).', 'wpshadow' );
	}
}
