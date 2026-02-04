<?php
/**
 * Diagnostic: Non-Descriptive Headings
 *
 * Detects vague headings like "Introduction" which hurt scannability and SEO.
 * Headings should be specific and keyword-rich.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7030.1505
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vague Headings Diagnostic Class
 *
 * Checks for generic, non-descriptive heading text.
 *
 * Detection methods:
 * - Pattern matching for generic terms
 * - Heading length analysis
 * - Keyword presence in headings
 *
 * @since 1.7030.1505
 */
class Diagnostic_Vague_Headings extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'vague-headings';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Non-Descriptive Headings';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Headings like "Introduction" hurt scannability - use specific, keyword-rich';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'structure';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (3 points):
	 * - 3 points: <15% of headings are vague
	 * - 2 points: <30% are vague
	 * - 0 points: ≥30% are vague
	 *
	 * @since  1.7030.1505
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score           = 0;
		$max_score       = 3;
		$total_headings  = 0;
		$vague_headings  = 0;
		$problem_posts   = array();

		// Generic heading patterns to flag.
		$vague_patterns = array(
			'introduction',
			'conclusion',
			'overview',
			'background',
			'details',
			'more information',
			'additional info',
			'the basics',
			'getting started',
			'what is',
			'about',
			'summary',
			'final thoughts',
			'wrapping up',
		);

		// Get sample posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 30,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Extract H2-H4 headings.
			preg_match_all( '/<h[2-4][^>]*>(.*?)<\/h[2-4]>/is', $content, $matches );

			if ( empty( $matches[1] ) ) {
				continue;
			}

			$post_vague_headings = array();

			foreach ( $matches[1] as $heading_text ) {
				$heading_text = wp_strip_all_tags( $heading_text );
				$heading_text = trim( $heading_text );

				if ( empty( $heading_text ) ) {
					continue;
				}

				$total_headings++;

				// Check if heading matches vague patterns.
				$is_vague = false;
				foreach ( $vague_patterns as $pattern ) {
					if ( stripos( $heading_text, $pattern ) !== false ) {
						$is_vague = true;
						$vague_headings++;
						$post_vague_headings[] = $heading_text;
						break;
					}
				}

				// Also flag very short headings (<3 words).
				if ( ! $is_vague && str_word_count( $heading_text ) < 3 ) {
					$is_vague = true;
					$vague_headings++;
					$post_vague_headings[] = $heading_text;
				}
			}

			if ( ! empty( $post_vague_headings ) && count( $problem_posts ) < 10 ) {
				$problem_posts[] = array(
					'post_id'        => $post->ID,
					'title'          => $post->post_title,
					'vague_headings' => $post_vague_headings,
					'url'            => get_permalink( $post->ID ),
				);
			}
		}

		if ( $total_headings === 0 ) {
			return null;
		}

		$vague_percentage = ( $vague_headings / $total_headings ) * 100;

		// Scoring.
		if ( $vague_percentage < 15 ) {
			$score = 3;
		} elseif ( $vague_percentage < 30 ) {
			$score = 2;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: percentage, 2: number of vague headings, 3: total headings */
				__( '%1$d%% of headings (%2$d/%3$d) are vague or generic. Good headings: Tell readers exactly what section covers, Include keywords naturally, Enable effective scanning (70%% of readers scan), Improve SEO (Google uses headings for context), Work standalone (if headline shared). Bad: "Introduction", "Overview", "Details", "More Info", "Getting Started" (generic). Good: "How to Install WordPress in 5 Minutes", "3 Proven Strategies for Email Marketing", "Understanding Python List Comprehensions" (specific, keyword-rich). Formula: Action/Benefit + Specific Topic. Headings = mini-titles, should be compelling enough to click if standalone.', 'wpshadow' ),
				round( $vague_percentage ),
				$vague_headings,
				$total_headings
			),
			'severity'      => 'medium',
			'threat_level'  => 30,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/vague-headings',
			'problem_posts' => $problem_posts,
			'stats'         => array(
				'total_headings'   => $total_headings,
				'vague_headings'   => $vague_headings,
				'percentage'       => round( $vague_percentage, 1 ),
			),
			'recommendation' => __( 'Review vague headings. Make them specific and descriptive. Include keywords naturally. Test: Would heading make sense without context? Can reader skip to any section and understand topic? Aim for 4-8 words per heading.', 'wpshadow' ),
		);
	}
}
