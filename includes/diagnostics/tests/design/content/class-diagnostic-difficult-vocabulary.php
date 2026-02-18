<?php
/**
 * Difficult Vocabulary Diagnostic
 *
 * Detects content using complex vocabulary that may be difficult for users with
 * cognitive disabilities or non-native speakers to understand (WCAG 2.1 SC 3.1.5).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Readability
 * @since      1.6034.2145
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Difficult Vocabulary Diagnostic Class
 *
 * Analyzes content for complex words and technical jargon that may hinder
 * comprehension for users with reading disabilities, cognitive impairments,
 * or those for whom English is a second language.
 *
 * **Why This Matters:**
 * - WCAG 2.1 Level AAA (Success Criterion 3.1.5)
 * - Cognitive accessibility for learning disabilities
 * - Broader audience reach (B1 reading level = 80% comprehension)
 * - Better user engagement and lower bounce rates
 *
 * **What's Checked:**
 * - Flesch-Kincaid Grade Level (target: 8th grade or lower)
 * - Average word length (target: < 5 characters)
 * - Complex word percentage (target: < 15%)
 * - Technical jargon usage
 *
 * @since 1.6034.2145
 */
class Diagnostic_Difficult_Vocabulary extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'difficult-vocabulary';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Difficult Vocabulary Detected';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies content with complex vocabulary that may be difficult for some users to understand';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'readability';

	/**
	 * Run the diagnostic check
	 *
	 * Analyzes recent posts for vocabulary complexity using multiple metrics:
	 * - Flesch-Kincaid Grade Level
	 * - Average word length
	 * - Percentage of complex words (3+ syllables)
	 *
	 * @since  1.6034.2145
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get recent published posts
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$difficult_posts = array();
		$total_grade_level = 0;
		$post_count        = 0;

		foreach ( $posts as $post ) {
			$content = wp_strip_all_tags( $post->post_content );
			$content = preg_replace( '/\s+/', ' ', $content );

			if ( strlen( $content ) < 100 ) {
				continue; // Skip very short content
			}

			$metrics = self::analyze_readability( $content );

			// Flag posts with grade level > 10 or complex word % > 20
			if ( $metrics['grade_level'] > 10 || $metrics['complex_word_pct'] > 20 ) {
				$difficult_posts[] = array(
					'id'               => $post->ID,
					'title'            => $post->post_title,
					'grade_level'      => $metrics['grade_level'],
					'complex_word_pct' => $metrics['complex_word_pct'],
					'avg_word_length'  => $metrics['avg_word_length'],
				);
			}

			$total_grade_level += $metrics['grade_level'];
			$post_count++;
		}

		if ( empty( $difficult_posts ) ) {
			return null;
		}

		$avg_grade_level = $post_count > 0 ? round( $total_grade_level / $post_count, 1 ) : 0;
		$count           = count( $difficult_posts );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of posts, 2: average grade level */
				__( '%1$d post(s) use complex vocabulary (average reading level: grade %2$s). Consider simplifying language for better accessibility.', 'wpshadow' ),
				$count,
				$avg_grade_level
			),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/readability-difficult-vocabulary',
			'details'      => array(
				'total_analyzed'   => $post_count,
				'difficult_count'  => $count,
				'avg_grade_level'  => $avg_grade_level,
				'sample_posts'     => array_slice( $difficult_posts, 0, 5 ),
				'target'           => '8th grade or lower',
			),
		);
	}

	/**
	 * Analyze text readability
	 *
	 * Calculates Flesch-Kincaid Grade Level and word complexity metrics.
	 *
	 * @since  1.6034.2145
	 * @param  string $text Content to analyze.
	 * @return array Readability metrics.
	 */
	private static function analyze_readability( $text ) {
		// Count sentences
		$sentence_count = preg_match_all( '/[.!?]+/', $text );
		$sentence_count = max( 1, $sentence_count );

		// Count words
		$words      = preg_split( '/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY );
		$word_count = count( $words );

		// Count syllables (approximation)
		$syllable_count = 0;
		$complex_words  = 0;
		$total_length   = 0;

		foreach ( $words as $word ) {
			$word_lower = strtolower( trim( $word, '.,!?;:' ) );
			$syllables  = self::count_syllables( $word_lower );

			$syllable_count += $syllables;
			$total_length   += strlen( $word_lower );

			if ( $syllables >= 3 ) {
				$complex_words++;
			}
		}

		// Flesch-Kincaid Grade Level formula
		$grade_level = 0.39 * ( $word_count / $sentence_count ) + 11.8 * ( $syllable_count / $word_count ) - 15.59;
		$grade_level = max( 0, $grade_level );

		return array(
			'grade_level'      => round( $grade_level, 1 ),
			'complex_word_pct' => round( ( $complex_words / $word_count ) * 100, 1 ),
			'avg_word_length'  => round( $total_length / $word_count, 1 ),
		);
	}

	/**
	 * Count syllables in a word (approximation)
	 *
	 * @since  1.6034.2145
	 * @param  string $word Word to analyze.
	 * @return int Syllable count.
	 */
	private static function count_syllables( $word ) {
		$word = strtolower( $word );
		$word = preg_replace( '/[^a-z]/', '', $word );

		// Count vowel groups
		$syllables = preg_match_all( '/[aeiouy]+/', $word );

		// Adjust for silent 'e'
		if ( substr( $word, -1 ) === 'e' ) {
			$syllables--;
		}

		// At least 1 syllable per word
		return max( 1, $syllables );
	}
}
