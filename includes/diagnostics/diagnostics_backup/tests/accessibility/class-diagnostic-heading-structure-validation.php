<?php
/**
 * Heading Structure Validation Diagnostic
 *
 * Ensures proper H1-H6 heading hierarchy for accessibility and SEO.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Heading Structure Validation Class
 *
 * Tests heading hierarchy for accessibility and SEO.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Heading_Structure_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'heading-structure-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Heading Structure Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures proper H1-H6 heading hierarchy for accessibility and SEO';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$heading_issues = self::analyze_heading_structure();
		
		if ( $heading_issues['total_issues'] > 0 ) {
			$issues = array();
			
			if ( $heading_issues['multiple_h1_count'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of pages with multiple H1 tags */
					__( '%d pages with multiple H1 tags', 'wpshadow' ),
					$heading_issues['multiple_h1_count']
				);
			}

			if ( $heading_issues['no_h1_count'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of pages without H1 */
					__( '%d pages missing H1 tag', 'wpshadow' ),
					$heading_issues['no_h1_count']
				);
			}

			if ( $heading_issues['skipped_levels_count'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of pages with skipped heading levels */
					__( '%d pages with skipped heading levels', 'wpshadow' ),
					$heading_issues['skipped_levels_count']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/heading-structure-validation',
				'meta'         => array(
					'pages_analyzed'        => $heading_issues['pages_analyzed'],
					'multiple_h1_count'     => $heading_issues['multiple_h1_count'],
					'no_h1_count'           => $heading_issues['no_h1_count'],
					'skipped_levels_count'  => $heading_issues['skipped_levels_count'],
					'no_headings_count'     => $heading_issues['no_headings_count'],
				),
			);
		}

		return null;
	}

	/**
	 * Analyze heading structure across pages.
	 *
	 * @since  1.26028.1905
	 * @return array Analysis results.
	 */
	private static function analyze_heading_structure() {
		global $wpdb;

		$analysis = array(
			'pages_analyzed'       => 0,
			'multiple_h1_count'    => 0,
			'no_h1_count'          => 0,
			'skipped_levels_count' => 0,
			'no_headings_count'    => 0,
			'total_issues'         => 0,
		);

		// Sample recent posts to avoid performance issues.
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_content
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')
				ORDER BY post_date DESC
				LIMIT 50",
				'publish'
			)
		);

		$analysis['pages_analyzed'] = count( $posts );

		foreach ( $posts as $post ) {
			$content = apply_filters( 'the_content', $post->post_content );
			$heading_structure = self::extract_heading_structure( $content );

			// Check for multiple H1s.
			if ( $heading_structure['h1_count'] > 1 ) {
				++$analysis['multiple_h1_count'];
				++$analysis['total_issues'];
			}

			// Check for missing H1.
			if ( 0 === $heading_structure['h1_count'] ) {
				++$analysis['no_h1_count'];
				++$analysis['total_issues'];
			}

			// Check for skipped heading levels.
			if ( $heading_structure['has_skipped_levels'] ) {
				++$analysis['skipped_levels_count'];
				++$analysis['total_issues'];
			}

			// Check for no headings at all.
			if ( $heading_structure['total_headings'] === 0 ) {
				++$analysis['no_headings_count'];
			}
		}

		return $analysis;
	}

	/**
	 * Extract heading structure from content.
	 *
	 * @since  1.26028.1905
	 * @param  string $content Post content.
	 * @return array Heading structure data.
	 */
	private static function extract_heading_structure( $content ) {
		$structure = array(
			'h1_count'           => 0,
			'total_headings'     => 0,
			'has_skipped_levels' => false,
			'levels_present'     => array(),
		);

		// Extract all headings.
		preg_match_all( '/<h([1-6])[^>]*>.*?<\/h\1>/is', $content, $matches );
		
		if ( ! empty( $matches[1] ) ) {
			$structure['total_headings'] = count( $matches[1] );
			
			foreach ( $matches[1] as $level ) {
				$level = (int) $level;
				
				if ( 1 === $level ) {
					++$structure['h1_count'];
				}

				if ( ! in_array( $level, $structure['levels_present'], true ) ) {
					$structure['levels_present'][] = $level;
				}
			}

			// Check for skipped levels (e.g., H2 to H4 without H3).
			sort( $structure['levels_present'] );
			for ( $i = 0; $i < count( $structure['levels_present'] ) - 1; $i++ ) {
				if ( $structure['levels_present'][ $i + 1 ] - $structure['levels_present'][ $i ] > 1 ) {
					$structure['has_skipped_levels'] = true;
					break;
				}
			}
		}

		return $structure;
	}
}
