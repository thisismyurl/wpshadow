<?php
/**
 * Keyword Cannibalization Detection Diagnostic
 *
 * Identifies multiple pages competing for same keywords in search results.
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
 * Keyword Cannibalization Detection Class
 *
 * Tests for keyword cannibalization.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Keyword_Cannibalization_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'keyword-cannibalization-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Keyword Cannibalization Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies multiple pages competing for same keywords in search results';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cannibalization = self::detect_keyword_cannibalization();
		
		if ( $cannibalization['total_conflicts'] > 0 ) {
			$issues = array();
			
			$issues[] = sprintf(
				/* translators: %d: number of keyword conflicts */
				__( '%d keyword conflicts detected (multiple pages competing)', 'wpshadow' ),
				$cannibalization['total_conflicts']
			);

			if ( $cannibalization['title_duplicates'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of duplicate titles */
					__( '%d duplicate title patterns', 'wpshadow' ),
					$cannibalization['title_duplicates']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/keyword-cannibalization-detection',
				'meta'         => array(
					'total_conflicts'  => $cannibalization['total_conflicts'],
					'title_duplicates' => $cannibalization['title_duplicates'],
					'slug_similarity'  => $cannibalization['slug_similarity'],
					'h1_duplicates'    => $cannibalization['h1_duplicates'],
				),
			);
		}

		return null;
	}

	/**
	 * Detect keyword cannibalization.
	 *
	 * @since  1.26028.1905
	 * @return array Detection results.
	 */
	private static function detect_keyword_cannibalization() {
		global $wpdb;

		$results = array(
			'total_conflicts'  => 0,
			'title_duplicates' => 0,
			'slug_similarity'  => 0,
			'h1_duplicates'    => 0,
		);

		// Get all published posts/pages.
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, post_name, post_content
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')
				ORDER BY post_date DESC
				LIMIT 200",
				'publish'
			)
		);

		if ( count( $posts ) < 2 ) {
			return $results;
		}

		$titles = array();
		$slugs = array();
		$h1_tags = array();

		// Extract data for comparison.
		foreach ( $posts as $post ) {
			// Title analysis.
			$title = strtolower( $post->post_title );
			$title_words = self::extract_keywords( $title );
			$titles[ $post->ID ] = $title_words;

			// Slug analysis.
			$slug = strtolower( $post->post_name );
			$slugs[ $post->ID ] = $slug;

			// H1 extraction.
			preg_match( '/<h1[^>]*>(.*?)<\/h1>/is', $post->post_content, $h1_match );
			if ( ! empty( $h1_match[1] ) ) {
				$h1_text = strtolower( wp_strip_all_tags( $h1_match[1] ) );
				$h1_tags[ $post->ID ] = self::extract_keywords( $h1_text );
			}
		}

		// Compare titles for duplicates.
		$results['title_duplicates'] = self::count_duplicate_keyword_sets( $titles );
		$results['total_conflicts'] += $results['title_duplicates'];

		// Compare H1s for duplicates.
		$results['h1_duplicates'] = self::count_duplicate_keyword_sets( $h1_tags );
		$results['total_conflicts'] += $results['h1_duplicates'];

		// Check slug similarity.
		$results['slug_similarity'] = self::count_similar_slugs( $slugs );
		$results['total_conflicts'] += $results['slug_similarity'];

		return $results;
	}

	/**
	 * Extract keywords from text.
	 *
	 * @since  1.26028.1905
	 * @param  string $text Text to analyze.
	 * @return array Keywords.
	 */
	private static function extract_keywords( $text ) {
		// Remove common stop words.
		$stop_words = array( 'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'been' );
		
		$words = preg_split( '/\s+/', $text );
		$keywords = array();

		foreach ( $words as $word ) {
			$word = preg_replace( '/[^a-z0-9]/', '', $word );
			if ( strlen( $word ) > 3 && ! in_array( $word, $stop_words, true ) ) {
				$keywords[] = $word;
			}
		}

		return $keywords;
	}

	/**
	 * Count duplicate keyword sets.
	 *
	 * @since  1.26028.1905
	 * @param  array $keyword_sets Array of keyword arrays keyed by post ID.
	 * @return int Number of duplicates.
	 */
	private static function count_duplicate_keyword_sets( $keyword_sets ) {
		$duplicates = 0;
		$seen = array();

		foreach ( $keyword_sets as $post_id => $keywords ) {
			if ( empty( $keywords ) ) {
				continue;
			}

			sort( $keywords );
			$signature = implode( '-', $keywords );

			if ( isset( $seen[ $signature ] ) ) {
				++$duplicates;
			} else {
				$seen[ $signature ] = $post_id;
			}
		}

		return $duplicates;
	}

	/**
	 * Count similar slugs.
	 *
	 * @since  1.26028.1905
	 * @param  array $slugs Array of slugs keyed by post ID.
	 * @return int Number of similar slugs.
	 */
	private static function count_similar_slugs( $slugs ) {
		$similar = 0;
		$slug_array = array_values( $slugs );

		for ( $i = 0; $i < count( $slug_array ); $i++ ) {
			for ( $j = $i + 1; $j < count( $slug_array ); $j++ ) {
				similar_text( $slug_array[ $i ], $slug_array[ $j ], $percent );
				if ( $percent > 70 ) {
					++$similar;
				}
			}
		}

		return $similar;
	}
}
