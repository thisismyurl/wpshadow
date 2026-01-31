<?php
/**
 * Content Readability Score Assessment Diagnostic
 *
 * Calculates Flesch-Kincaid readability scores to ensure content accessibility.
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
 * Content Readability Score Assessment Class
 *
 * Tests content readability.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Content_Readability_Score_Assessment extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-readability-score-assessment';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Readability Score Assessment';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Calculates Flesch-Kincaid readability scores to ensure content accessibility';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-experience';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$readability = self::assess_readability();
		
		if ( $readability['avg_grade_level'] > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: average grade level */
					__( 'Average content requires grade %d reading level (target: 8th grade)', 'wpshadow' ),
					$readability['avg_grade_level']
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-readability-score-assessment',
				'meta'         => array(
					'avg_grade_level'      => $readability['avg_grade_level'],
					'avg_sentence_length'  => $readability['avg_sentence_length'],
					'difficult_posts'      => $readability['difficult_posts'],
					'total_analyzed'       => $readability['total_analyzed'],
				),
			);
		}

		return null;
	}

	/**
	 * Assess content readability.
	 *
	 * @since  1.26028.1905
	 * @return array Readability assessment.
	 */
	private static function assess_readability() {
		global $wpdb;

		$assessment = array(
			'avg_grade_level'     => 0,
			'avg_sentence_length' => 0,
			'difficult_posts'     => 0,
			'total_analyzed'      => 0,
		);

		// Sample recent posts.
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_content
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')
				ORDER BY post_date DESC
				LIMIT 50",
				'publish'
			)
		);

		$total_grade = 0;
		$total_sentence_length = 0;

		foreach ( $posts as $post ) {
			$content = wp_strip_all_tags( $post->post_content );
			$content = preg_replace( '/\s+/', ' ', $content );

			if ( strlen( $content ) < 100 ) {
				continue; // Skip short content.
			}

			++$assessment['total_analyzed'];

			$scores = self::calculate_readability( $content );
			$total_grade += $scores['grade_level'];
			$total_sentence_length += $scores['avg_sentence_length'];

			if ( $scores['grade_level'] > 12 ) {
				++$assessment['difficult_posts'];
			}
		}

		if ( $assessment['total_analyzed'] > 0 ) {
			$assessment['avg_grade_level'] = round( $total_grade / $assessment['total_analyzed'] );
			$assessment['avg_sentence_length'] = round( $total_sentence_length / $assessment['total_analyzed'] );
		}

		return $assessment;
	}

	/**
	 * Calculate readability scores.
	 *
	 * @since  1.26028.1905
	 * @param  string $text Text to analyze.
	 * @return array Readability scores.
	 */
	private static function calculate_readability( $text ) {
		// Count sentences (approximate).
		$sentence_count = preg_match_all( '/[.!?]+/', $text );
		if ( $sentence_count === 0 ) {
			$sentence_count = 1;
		}

		// Count words.
		$word_count = str_word_count( $text );

		// Count syllables (approximate).
		$syllable_count = self::count_syllables( $text );

		// Flesch-Kincaid Grade Level formula.
		// 0.39 * (words / sentences) + 11.8 * (syllables / words) - 15.59.
		$grade_level = 0;
		if ( $word_count > 0 && $sentence_count > 0 ) {
			$grade_level = ( 0.39 * ( $word_count / $sentence_count ) ) + ( 11.8 * ( $syllable_count / $word_count ) ) - 15.59;
			$grade_level = max( 0, $grade_level ); // Floor at 0.
		}

		return array(
			'grade_level'         => round( $grade_level ),
			'avg_sentence_length' => $sentence_count > 0 ? round( $word_count / $sentence_count ) : 0,
		);
	}

	/**
	 * Count syllables in text (approximate).
	 *
	 * @since  1.26028.1905
	 * @param  string $text Text to analyze.
	 * @return int Syllable count.
	 */
	private static function count_syllables( $text ) {
		$words = str_word_count( strtolower( $text ), 1 );
		$syllables = 0;

		foreach ( $words as $word ) {
			// Approximate syllable count based on vowel groups.
			$syllables += preg_match_all( '/[aeiouy]+/', $word );
			
			// Adjust for silent 'e' at end.
			if ( substr( $word, -1 ) === 'e' ) {
				--$syllables;
			}

			// Minimum 1 syllable per word.
			if ( $syllables < 1 ) {
				$syllables = 1;
			}
		}

		return $syllables;
	}
}
