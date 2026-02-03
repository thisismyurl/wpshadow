<?php
/**
 * Keyword Stuffing Detected Diagnostic
 *
 * Tests for keyword stuffing (keyword density > 5%) which triggers Google's
 * over-optimization penalty and harms user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5003.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Keyword_Stuffing Class
 *
 * Detects keyword over-optimization (density > 5%) which triggers penalties
 * and makes content unreadable. Natural language beats keyword density.
 *
 * @since 1.5003.1200
 */
class Diagnostic_Keyword_Stuffing extends Diagnostic_Base {

	protected static $slug = 'keyword-stuffing';
	protected static $title = 'Keyword Stuffing Detected';
	protected static $description = 'Tests for keyword stuffing (keyword density > 5%)';
	protected static $family = 'keyword-strategy';

	public static function check() {
		$score          = 0;
		$max_score      = 3;
		$score_details  = array();
		$recommendations = array();
		$problem_posts   = array();

		// Get sample of recent posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 20,
				'post_status'    => 'publish',
			)
		);

		$posts_checked = 0;
		$posts_with_stuffing = 0;

		foreach ( $posts as $post ) {
			++$posts_checked;
			
			// Get content as plain text.
			$content = wp_strip_all_tags( $post->post_content );
			$title = $post->post_title;
			
			// Count total words.
			$total_words = str_word_count( $content );
			
			if ( $total_words < 100 ) {
				continue; // Skip very short posts.
			}

			// Extract potential focus keyword from title (first 3 words).
			$title_words = explode( ' ', strtolower( $title ) );
			$potential_keyword = implode( ' ', array_slice( $title_words, 0, 3 ) );
			
			// Count keyword occurrences.
			$keyword_count = substr_count( strtolower( $content ), $potential_keyword );
			
			// Calculate density.
			$density = ( $keyword_count / $total_words ) * 100;

			if ( $density > 5 ) {
				++$posts_with_stuffing;
				$problem_posts[] = array(
					'title'    => $title,
					'url'      => get_permalink( $post ),
					'density'  => round( $density, 1 ),
					'keyword'  => $potential_keyword,
					'count'    => $keyword_count,
				);
			}
		}

		// Score based on stuffing prevalence.
		if ( $posts_with_stuffing === 0 ) {
			$score = 3;
			$score_details[] = __( '✓ No obvious keyword stuffing detected', 'wpshadow' );
		} elseif ( $posts_with_stuffing < 3 ) {
			$score = 2;
			$score_details[]   = sprintf( __( '◐ %d post(s) show keyword over-optimization', 'wpshadow' ), $posts_with_stuffing );
			$recommendations[] = __( 'Reduce keyword density to 1-2% for natural reading', 'wpshadow' );
		} else {
			$score = 0;
			$score_details[]   = sprintf( __( '✗ %d posts with keyword stuffing detected', 'wpshadow' ), $posts_with_stuffing );
			$recommendations[] = __( 'Critical: Rewrite over-optimized content naturally - keyword stuffing triggers penalties', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'critical';
		$threat_level = 60;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage, %d: posts with stuffing */
				__( 'Keyword stuffing score: %d%% (%d posts flagged). Keyword density >5%% triggers Google over-optimization penalties and makes content unreadable. Optimal density: 1-2%%. Modern SEO prioritizes natural language and semantic relevance over keyword repetition. Write for humans, not algorithms.', 'wpshadow' ),
				$score_percentage,
				$posts_with_stuffing
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/keyword-stuffing',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'problem_examples' => array_slice( $problem_posts, 0, 5 ),
			'impact'           => __( 'Keyword stuffing harms rankings, triggers penalties, and makes content unreadable for users. Natural writing wins.', 'wpshadow' ),
		);
	}
}
