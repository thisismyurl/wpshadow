<?php

declare(strict_types=1);
/**
 * Test: Heading Hierarchy Check
 *
 * Tests if HTML uses proper heading hierarchy (H1 > H2 > H3, etc.).
 *
 * Philosophy: Inspire confidence (#8) - Help users create accessible, well-structured pages
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Heading_Hierarchy extends Diagnostic_Base {


	protected static $slug        = 'test-seo-heading-hierarchy';
	protected static $title       = 'Heading Hierarchy Test';
	protected static $description = 'Tests for proper heading structure (H1-H6 order)';

	/**
	 * Run the diagnostic check
	 *
	 * PASS (returns null): Headings follow proper hierarchy
	 * FAIL (returns array): Skipped levels or improper structure
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
	 * Run comprehensive heading hierarchy tests
	 *
	 * @param string|null $url URL to test
	 * @param string|null $html Pre-fetched HTML
	 * @return array Test results
	 */
	public static function run_hierarchy_tests( ?string $url = null, ?string $html = null ): array {
		$html = $html ?? self::fetch_html( $url ?? home_url( '/' ) );

		if ( $html === false ) {
			return array(
				'success' => false,
				'error'   => 'Could not fetch HTML',
				'url'     => $url ?? home_url( '/' ),
			);
		}

		$headings = self::extract_headings( $html );
		$issues   = self::detect_hierarchy_issues( $headings );

		return array(
			'success'           => true,
			'url'               => $url ?? home_url( '/' ),
			'heading_structure' => $headings,
			'tests'             => array(
				'no_skipped_levels'   => self::test_no_skipped_levels( $html ),
				'proper_h1_position'  => self::test_proper_h1_position( $html ),
				'logical_progression' => self::test_logical_progression( $html ),
				'no_orphan_headings'  => self::test_no_orphan_headings( $html ),
			),
			'summary'           => array(
				'passed'         => empty( $issues ),
				'total_headings' => array_sum( array_column( $headings, 'count' ) ),
				'issues'         => $issues,
			),
		);
	}

	/**
	 * Test for skipped heading levels
	 */
	public static function test_no_skipped_levels( ?string $url = null, ?string $html = null ): array {
		$html     = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$headings = self::extract_headings( $html );
		$skips    = self::find_skipped_levels( $headings );

		return array(
			'test'           => 'no_skipped_levels',
			'passed'         => empty( $skips ),
			'skipped_levels' => $skips,
			'message'        => empty( $skips )
				? 'No skipped heading levels'
				: sprintf( 'Skipped levels found: %s', implode( ', ', $skips ) ),
			'impact'         => 'Skipped levels confuse screen readers and harm accessibility',
		);
	}

	/**
	 * Test if H1 comes first
	 */
	public static function test_proper_h1_position( ?string $url = null, ?string $html = null ): array {
		$html     = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$sequence = self::extract_heading_sequence( $html );

		$h1_first = ! empty( $sequence ) && $sequence[0] === 1;

		return array(
			'test'          => 'proper_h1_position',
			'passed'        => $h1_first,
			'first_heading' => ! empty( $sequence ) ? 'H' . $sequence[0] : 'none',
			'message'       => $h1_first
				? 'H1 is the first heading (correct)'
				: 'H1 is not the first heading (should be first)',
			'impact'        => 'H1 should be the first heading for proper document structure',
		);
	}

	/**
	 * Test for logical progression (no backward jumps)
	 */
	public static function test_logical_progression( ?string $url = null, ?string $html = null ): array {
		$html     = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$sequence = self::extract_heading_sequence( $html );
		$jumps    = self::find_backward_jumps( $sequence );

		return array(
			'test'           => 'logical_progression',
			'passed'         => empty( $jumps ),
			'backward_jumps' => $jumps,
			'message'        => empty( $jumps )
				? 'Headings progress logically'
				: sprintf( '%d backward jumps found (e.g., H4 → H2)', count( $jumps ) ),
			'impact'         => 'Backward jumps indicate poor content structure',
		);
	}

	/**
	 * Test for orphan headings (H3 without H2, etc.)
	 */
	public static function test_no_orphan_headings( ?string $url = null, ?string $html = null ): array {
		$html     = $html ?? self::fetch_html( $url ?? home_url( '/' ) );
		$headings = self::extract_headings( $html );
		$orphans  = self::find_orphans( $headings );

		return array(
			'test'    => 'no_orphan_headings',
			'passed'  => empty( $orphans ),
			'orphans' => $orphans,
			'message' => empty( $orphans )
				? 'No orphan headings'
				: sprintf( 'Found orphan headings: %s', implode( ', ', $orphans ) ),
			'impact'  => 'Orphan headings indicate missing parent sections',
		);
	}

	/**
	 * Analyze HTML for heading hierarchy issues
	 *
	 * @param string $html HTML content
	 * @param string $checked_url URL that was checked
	 * @return array|null Finding or null
	 */
	protected static function analyze_html( string $html, string $checked_url ): ?array {
		$headings = self::extract_headings( $html );
		$issues   = self::detect_hierarchy_issues( $headings );

		// No headings = N/A
		if ( array_sum( array_column( $headings, 'count' ) ) === 0 ) {
			return null; // PASS (no headings to check)
		}

		// Perfect hierarchy = PASS
		if ( empty( $issues ) ) {
			return null; // PASS
		}

		// Has issues = FAIL
		$threat_level = 40;
		if ( count( $issues ) > 2 ) {
			$threat_level = 60;
		}

		return array(
			'id'            => 'seo-heading-hierarchy',
			'title'         => 'Heading Hierarchy Issues',
			'description'   => sprintf(
				'Your page has %d heading structure issue(s): %s. Proper heading hierarchy is important for accessibility and SEO.',
				count( $issues ),
				implode( '; ', array_slice( $issues, 0, 3 ) )
			)
			'kb_link' => 'https://wpshadow.com/kb/heading-hierarchy/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
			'training_link' => 'https://wpshadow.com/training/content-structure/',
			'auto_fixable'  => false,
			'threat_level'  => $threat_level,
			'module'        => 'SEO',
			'priority'      => 2,
			'meta'          => array(
				'heading_counts' => $headings,
				'issues'         => $issues,
				'total_headings' => array_sum( array_column( $headings, 'count' ) ),
				'checked_url'    => $checked_url,
			),
		);
	}

	/**
	 * Extract all headings with counts
	 *
	 * @param string $html HTML content
	 * @return array Heading counts by level
	 */
	protected static function extract_headings( string $html ): array {
		if ( empty( $html ) ) {
			return array();
		}

		$headings = array();
		for ( $i = 1; $i <= 6; $i++ ) {
			preg_match_all( "/<h{$i}[^>]*>(.*?)<\/h{$i}>/is", $html, $matches );
			$headings[] = array(
				'level' => $i,
				'count' => count( $matches[0] ),
				'texts' => array_slice( array_map( 'strip_tags', $matches[1] ?? array() ), 0, 5 ), // First 5
			);
		}

		return $headings;
	}

	/**
	 * Extract heading sequence (levels only)
	 *
	 * @param string $html HTML content
	 * @return array Sequence of heading levels
	 */
	protected static function extract_heading_sequence( string $html ): array {
		if ( empty( $html ) ) {
			return array();
		}

		preg_match_all( '/<h([1-6])[^>]*>/i', $html, $matches );
		return array_map( 'intval', $matches[1] ?? array() );
	}

	/**
	 * Detect all hierarchy issues
	 *
	 * @param array $headings Heading data
	 * @return array List of issues
	 */
	protected static function detect_hierarchy_issues( array $headings ): array {
		$issues = array();

		// Check for skipped levels
		$skips = self::find_skipped_levels( $headings );
		if ( ! empty( $skips ) ) {
			$issues[] = 'Skipped heading levels: ' . implode( ', ', $skips );
		}

		// Check if H1 exists
		if ( $headings[0]['count'] === 0 ) {
			$issues[] = 'No H1 heading found';
		}

		// Check for orphans
		$orphans = self::find_orphans( $headings );
		if ( ! empty( $orphans ) ) {
			$issues[] = 'Orphan headings: ' . implode( ', ', $orphans );
		}

		return $issues;
	}

	/**
	 * Find skipped heading levels
	 *
	 * @param array $headings Heading data
	 * @return array Skipped levels (e.g., ['H2→H4'])
	 */
	protected static function find_skipped_levels( array $headings ): array {
		$skips          = array();
		$previous_level = 0;

		foreach ( $headings as $heading ) {
			if ( $heading['count'] > 0 ) {
				$level = $heading['level'];

				// Skip more than 1 level
				if ( $previous_level > 0 && $level > $previous_level + 1 ) {
					$skips[] = sprintf( 'H%d→H%d', $previous_level, $level );
				}

				$previous_level = $level;
			}
		}

		return $skips;
	}

	/**
	 * Find backward jumps in sequence
	 *
	 * @param array $sequence Heading level sequence
	 * @return array Backward jumps
	 */
	protected static function find_backward_jumps( array $sequence ): array {
		$jumps = array();

		for ( $i = 1; $i < count( $sequence ); $i++ ) {
			$prev = $sequence[ $i - 1 ];
			$curr = $sequence[ $i ];

			// Jumping back more than 1 level is suspicious
			if ( $curr < $prev - 1 ) {
				$jumps[] = sprintf( 'H%d→H%d at position %d', $prev, $curr, $i + 1 );
			}
		}

		return $jumps;
	}

	/**
	 * Find orphan headings
	 *
	 * @param array $headings Heading data
	 * @return array Orphan headings
	 */
	protected static function find_orphans( array $headings ): array {
		$orphans = array();

		// H3 without H2, H4 without H3, etc.
		for ( $i = 2; $i <= 6; $i++ ) {
			if ( $headings[ $i - 1 ]['count'] > 0 && $headings[ $i - 2 ]['count'] === 0 ) {
				$orphans[] = sprintf( 'H%d (no H%d parent)', $i, $i - 1 );
			}
		}

		return $orphans;
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
			'id'            => 'seo-heading-hierarchy',
			'title'         => $title,
			'description'   => $description
			'kb_link' => 'https://wpshadow.com/kb/heading-hierarchy/',
			'training_link' => 'https://wpshadow.com/training/content-structure/',
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
		return __( 'Heading Hierarchy Check', 'wpshadow' );
	}

	/**
	 * Get the description for display
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Checks HTML for proper heading hierarchy (H1 > H2 > H3 structure).', 'wpshadow' );
	}
}
