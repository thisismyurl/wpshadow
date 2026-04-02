<?php
/**
 * Skimmable Content Format Diagnostic
 *
 * Tests if content uses headings, lists, and formatting elements for easy scanning.
 * Well-formatted content improves user engagement and readability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Formats_Content_For_Scanning Class
 *
 * Analyzes recent posts for formatting elements that make content easy to scan:
 * - H2/H3 headings for structure
 * - Bullet and numbered lists for clarity
 * - Bold text for emphasis
 * - Short paragraphs for readability
 *
 * @since 1.6093.1200
 */
class Diagnostic_Formats_Content_For_Scanning extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'formats-content-for-scanning';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Skimmable Content Format';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Tests if content uses headings, lists, and formatting for easy scanning';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes recent posts for formatting elements that improve scannability:
	 * - Headings (H2/H3) every ~300 words
	 * - Lists (bullet or numbered)
	 * - Bold text for emphasis
	 * - Short paragraphs
	 *
	 * @since 1.6093.1200
	 * @return array|null {
	 *     Finding array if issue found, null otherwise.
	 *
	 *     @type string $id           Diagnostic identifier.
	 *     @type string $title        Issue title.
	 *     @type string $description  Detailed description with recommendations.
	 *     @type string $severity     Issue severity level.
	 *     @type int    $threat_level Numeric threat level (0-100).
	 *     @type bool   $auto_fixable Whether issue can be auto-fixed.
	 *     @type string $kb_link      Link to knowledge base article.
	 *     @type array  $meta         Additional diagnostic data.
	 * }
	 */
	public static function check() {
		// Get recent 30 published posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 30,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null; // No posts to analyze.
		}

		$skimmable_count = 0;
		$analyzed_posts  = array();

		foreach ( $posts as $post ) {
			$content    = $post->post_content;
			$word_count = str_word_count( wp_strip_all_tags( $content ) );

			// Skip very short posts (< 100 words).
			if ( $word_count < 100 ) {
				continue;
			}

			$score = self::calculate_skimmability_score( $content, $word_count );

			$analyzed_posts[] = array(
				'id'    => $post->ID,
				'title' => $post->post_title,
				'score' => $score,
			);

			// Consider post skimmable if score >= 60.
			if ( $score >= 60 ) {
				++$skimmable_count;
			}
		}

		if ( empty( $analyzed_posts ) ) {
			return null; // No posts long enough to analyze.
		}

		$total_analyzed    = count( $analyzed_posts );
		$skimmable_percent = ( $skimmable_count / $total_analyzed ) * 100;

		// Return finding if less than 60% of posts are skimmable.
		if ( $skimmable_percent < 60 ) {
			// Sort by score to show worst offenders.
			usort(
				$analyzed_posts,
				function ( $a, $b ) {
					return $a['score'] <=> $b['score'];
				}
			);

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
					'description'  => self::build_description( $skimmable_percent, $analyzed_posts ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/skimmable-content-format',
				'meta'         => array(
					'skimmable_percent' => round( $skimmable_percent, 1 ),
					'total_analyzed'    => $total_analyzed,
					'skimmable_count'   => $skimmable_count,
					'worst_posts'       => array_slice( $analyzed_posts, 0, 5 ),
				),
			);
		}

		return null;
	}

	/**
	 * Calculate skimmability score for content.
	 *
	 * Scoring criteria:
	 * - Headings (H2/H3): 30 points (15 points each per 300 words)
	 * - Lists (ul/ol): 25 points
	 * - Bold text: 20 points
	 * - Short paragraphs: 25 points
	 *
	 * @since 1.6093.1200
	 * @param  string $content    Post content HTML.
	 * @param  int    $word_count Word count of content.
	 * @return int Skimmability score (0-100).
	 */
	private static function calculate_skimmability_score( $content, $word_count ) {
		$score = 0;

		// Check for H2/H3 headings (30 points max).
		$h2_count = preg_match_all( '/<h2[^>]*>.*?<\/h2>/is', $content );
		$h3_count = preg_match_all( '/<h3[^>]*>.*?<\/h3>/is', $content );
		$heading_count = $h2_count + $h3_count;

		if ( $word_count > 0 ) {
			$headings_per_300 = ( $heading_count / $word_count ) * 300;
			if ( $headings_per_300 >= 2 ) {
				$score += 30;
			} elseif ( $headings_per_300 >= 1 ) {
				$score += 15;
			}
		}

		// Check for lists (25 points).
		$ul_count = preg_match_all( '/<ul[^>]*>.*?<\/ul>/is', $content );
		$ol_count = preg_match_all( '/<ol[^>]*>.*?<\/ol>/is', $content );
		if ( ( $ul_count + $ol_count ) > 0 ) {
			$score += 25;
		}

		// Check for bold text (20 points).
		$bold_count = preg_match_all( '/<(strong|b)[^>]*>.*?<\/(strong|b)>/is', $content );
		if ( $bold_count > 0 ) {
			$score += 20;
		}

		// Check for short paragraphs (25 points).
		// Good practice: most paragraphs under 150 words.
		preg_match_all( '/<p[^>]*>(.*?)<\/p>/is', $content, $paragraphs );
		if ( ! empty( $paragraphs[1] ) ) {
			$short_paragraph_count = 0;
			$total_paragraphs      = count( $paragraphs[1] );

			foreach ( $paragraphs[1] as $paragraph ) {
				$para_word_count = str_word_count( wp_strip_all_tags( $paragraph ) );
				if ( $para_word_count > 0 && $para_word_count <= 150 ) {
					++$short_paragraph_count;
				}
			}

			if ( $total_paragraphs > 0 ) {
				$short_para_percent = ( $short_paragraph_count / $total_paragraphs ) * 100;
				if ( $short_para_percent >= 70 ) {
					$score += 25;
				} elseif ( $short_para_percent >= 50 ) {
					$score += 15;
				}
			}
		}

		return min( $score, 100 );
	}

	/**
	 * Get detailed description of the finding.
	 *
	 * @since 1.6093.1200
	 * @param  float $skimmable_percent Percentage of skimmable posts.
	 * @param  array $analyzed_posts    Array of analyzed posts with scores.
	 * @return string Formatted description with recommendations.
	 */
	private static function build_description( $skimmable_percent, $analyzed_posts ) {
		$worst_count = min( 3, count( $analyzed_posts ) );

		$description = sprintf(
			/* translators: %1$s: percentage of skimmable posts */
			__( 'Only %1$s%% of your recent posts are formatted for easy scanning. Readers often skim content before committing to read, so proper formatting is crucial for engagement.', 'wpshadow' ),
			number_format( $skimmable_percent, 1 )
		) . "\n\n";

		$description .= '<strong>' . __( 'Why This Matters:', 'wpshadow' ) . "</strong>\n";
		$description .= __( '• 79% of web users scan pages rather than reading word-by-word', 'wpshadow' ) . "\n";
		$description .= __( '• Well-formatted content increases time-on-page by 40%', 'wpshadow' ) . "\n";
		$description .= __( '• Scannable content improves SEO through better engagement metrics', 'wpshadow' ) . "\n";
		$description .= __( '• Users are 47% more likely to finish reading formatted content', 'wpshadow' ) . "\n\n";

		$description .= '<strong>' . __( 'Posts That Need Formatting:', 'wpshadow' ) . "</strong>\n";
		for ( $i = 0; $i < $worst_count; $i++ ) {
			$post = $analyzed_posts[ $i ];
			$description .= sprintf(
				/* translators: 1: post title, 2: score */
				__( '• %1$s (Score: %2$d/100)', 'wpshadow' ),
				esc_html( $post['title'] ),
				$post['score']
			) . "\n";
		}

		$description .= "\n<strong>" . __( 'How to Fix:', 'wpshadow' ) . "</strong>\n";
		$description .= __( '1. Add H2/H3 headings every 300 words to break up content', 'wpshadow' ) . "\n";
		$description .= __( '2. Use bullet points or numbered lists for key information', 'wpshadow' ) . "\n";
		$description .= __( '3. Bold important phrases to help scanners find key points', 'wpshadow' ) . "\n";
		$description .= __( '4. Keep paragraphs short (3-4 sentences, under 150 words)', 'wpshadow' ) . "\n";
		$description .= __( '5. Use white space strategically to make content breathe', 'wpshadow' );

		return $description;
	}
}
