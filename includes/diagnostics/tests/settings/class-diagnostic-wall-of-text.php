<?php
/**
 * Diagnostic: Wall of Text
 *
 * Detects posts with 1,000+ words without visual breaks. 73% of users
 * abandon dense text blocks. Content needs white space and visual variety.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7030.1502
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wall of Text Diagnostic Class
 *
 * Checks for proper content formatting with visual breaks.
 *
 * Detection methods:
 * - Paragraph count vs word count
 * - Image distribution
 * - Heading frequency
 *
 * @since 1.7030.1502
 */
class Diagnostic_Wall_Of_Text extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wall-of-text';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Wall of Text';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = '1,000+ words without visual breaks - 73% abandon';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'structure';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (4 points):
	 * - 1 point: Average paragraph <150 words
	 * - 1 point: Images every 300-500 words
	 * - 1 point: Headings every 300 words
	 * - 1 point: <20% of posts are walls of text
	 *
	 * @since  1.7030.1502
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score      = 0;
		$max_score  = 4;
		$wall_posts = 0;
		$problem_posts = array();

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

		$total_long_posts = 0;

		foreach ( $posts as $post ) {
			$content    = $post->post_content;
			$word_count = str_word_count( wp_strip_all_tags( $content ) );

			// Only check posts with 1,000+ words.
			if ( $word_count < 1000 ) {
				continue;
			}

			$total_long_posts++;

			// Count paragraphs (approximate via <p> tags and double line breaks).
			$p_count = substr_count( $content, '<p>' ) + substr_count( $content, "\n\n" );
			$p_count = max( $p_count, 1 );

			// Count images.
			$img_count = substr_count( $content, '<img' );

			// Count headings (H2-H4).
			$heading_count = 0;
			for ( $i = 2; $i <= 4; $i++ ) {
				$heading_count += substr_count( $content, '<h' . $i );
			}

			// Calculate metrics.
			$avg_words_per_paragraph = $word_count / $p_count;
			$words_per_image         = $img_count > 0 ? $word_count / $img_count : 9999;
			$words_per_heading       = $heading_count > 0 ? $word_count / $heading_count : 9999;

			// Check if this is a "wall of text".
			$is_wall = false;
			$issues  = array();

			if ( $avg_words_per_paragraph > 150 ) {
				$is_wall  = true;
				$issues[] = sprintf( 'Average paragraph: %d words (>150)', round( $avg_words_per_paragraph ) );
			}

			if ( $words_per_image > 500 ) {
				$is_wall  = true;
				$issues[] = sprintf( 'Words per image: %d (>500)', round( $words_per_image ) );
			}

			if ( $words_per_heading > 300 ) {
				$is_wall  = true;
				$issues[] = sprintf( 'Words per heading: %d (>300)', round( $words_per_heading ) );
			}

			if ( $is_wall ) {
				$wall_posts++;
				if ( count( $problem_posts ) < 10 ) {
					$problem_posts[] = array(
						'post_id'    => $post->ID,
						'title'      => $post->post_title,
						'word_count' => $word_count,
						'issues'     => $issues,
						'url'        => get_permalink( $post->ID ),
					);
				}
			}
		}

		if ( $total_long_posts === 0 ) {
			return null;
		}

		$wall_percentage = ( $wall_posts / $total_long_posts ) * 100;

		// Scoring.
		if ( $wall_percentage < 20 ) {
			$score += 4;
		} elseif ( $wall_percentage < 40 ) {
			$score += 2;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: percentage, 2: number of wall posts, 3: total checked */
				__( '%1$d%% of long posts (%2$d/%3$d) are walls of text. Dense text blocks cause: 73%% immediate abandonment, Cognitive overload (brain can\'t process), Poor scannability (no entry points), Mobile horror (tiny screens), Accessibility barriers. Visual breaks needed: Paragraphs: 3-5 sentences max (100-150 words), Headings: Every 300 words (H2/H3), Images: Every 300-500 words, Lists/bullets: Break up concepts, Blockquotes: Highlight key points, White space: Breathing room. F-pattern reading: Users scan left side, need visual hooks to continue. Fix: Break long paragraphs, Add subheadings, Insert relevant images, Use lists for steps/tips.', 'wpshadow' ),
				round( $wall_percentage ),
				$wall_posts,
				$total_long_posts
			),
			'severity'      => 'medium',
			'threat_level'  => 35,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/wall-of-text',
			'problem_posts' => $problem_posts,
			'stats'         => array(
				'total_long_posts' => $total_long_posts,
				'wall_posts'       => $wall_posts,
				'percentage'       => round( $wall_percentage, 1 ),
			),
			'recommendation' => __( 'Review dense posts. Break paragraphs into 3-5 sentences. Add H2/H3 every 300 words. Insert images every 300-500 words. Use lists for sequential/grouped info. Add white space via line breaks.', 'wpshadow' ),
		);
	}
}
