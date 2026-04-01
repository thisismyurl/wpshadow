<?php
/**
 * Diagnostic: No Featured Snippets Targeting
 *
 * Detects missing optimization for position 0 rankings.
 * Featured snippets (position 0) capture 35% of all clicks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Featured Snippets Targeting Diagnostic Class
 *
 * Checks content for snippet optimization patterns.
 *
 * Detection methods:
 * - List usage (<ul>/<ol>)
 * - Table presence
 * - Definition paragraphs
 * - Question-answer format
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Featured_Snippets extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-featured-snippets';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Featured Snippets Targeting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Position 0 (featured snippets) = 35% of all clicks, missing optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'keyword-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (5 points):
	 * - 1 point: 50%+ posts have lists
	 * - 1 point: 30%+ posts have tables
	 * - 1 point: 60%+ posts have definition patterns
	 * - 1 point: Strong question-answer formatting
	 * - 1 point: Proper heading hierarchy
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 5;

		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 100,
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$posts_with_lists  = 0;
		$posts_with_tables = 0;
		$posts_with_definitions = 0;
		$posts_with_qa = 0;
		$posts_with_good_headings = 0;

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Check for lists.
			if ( substr_count( $content, '<ul' ) > 0 || substr_count( $content, '<ol' ) > 0 ) {
				$posts_with_lists++;
			}

			// Check for tables.
			if ( substr_count( $content, '<table' ) > 0 ) {
				$posts_with_tables++;
			}

			// Check for definition patterns (paragraph starting with "X is").
			if ( preg_match( '/<p>[A-Z][^.]+\s+is\s+/i', $content ) ) {
				$posts_with_definitions++;
			}

			// Check for question-answer format.
			if ( preg_match( '/<h[2-6][^>]*>.*?\?.*?<\/h[2-6]>/i', $content ) ) {
				$posts_with_qa++;
			}

			// Check heading hierarchy.
			preg_match_all( '/<h([1-6])[^>]*>/i', $content, $heading_matches );
			if ( ! empty( $heading_matches[1] ) && count( $heading_matches[1] ) >= 3 ) {
				$posts_with_good_headings++;
			}
		}

		$total_posts = count( $posts );

		// Scoring.
		if ( ( $posts_with_lists / $total_posts ) >= 0.5 ) {
			$score++;
		}
		if ( ( $posts_with_tables / $total_posts ) >= 0.3 ) {
			$score++;
		}
		if ( ( $posts_with_definitions / $total_posts ) >= 0.6 ) {
			$score++;
		}
		if ( ( $posts_with_qa / $total_posts ) >= 0.4 ) {
			$score++;
		}
		if ( ( $posts_with_good_headings / $total_posts ) >= 0.7 ) {
			$score++;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.6 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __( 'Featured snippets (position 0) appear above all organic results = 35% of clicks, 8% of all Google searches show them. Types: Paragraph snippets (40-60 word definitions), List snippets (numbered steps or bullets), Table snippets (comparison data), Video snippets (YouTube with timestamps). How to optimize: Paragraph snippets: Answer question in first paragraph (40-60 words), Format as "X is [definition]", Place near top of content, Use target keyword in answer. List snippets: Use numbered lists (<ol>) for steps/rankings, Use bullet lists (<ul>) for features/tips, 3-8 items optimal (too short = not enough value, too long = truncated), Start with action verbs. Table snippets: Use <table> tags (not images), Include header row, 3-6 columns optimal, Compare features/prices/specs. Video snippets: Upload to YouTube, Add detailed descriptions, Use chapter markers (timestamps), Embed on page with transcription. Target "question keywords": What is, How to, Best ways to, Why does, When should, Where can. Tools: SEMrush Position Tracking (shows snippet opportunities), Ahrefs SERP features filter (find snippet keywords), AnswerThePublic (question ideas), Google Search Console (queries you rank 5-10 for = snippet targets). Strategy: Find keywords where you rank 5-10, Check if snippet exists, Optimize content for snippet format, Monitor rankings for movement to position 0.', 'wpshadow' ),
			'severity'    => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/no-featured-snippets?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'stats'       => array(
				'total_posts'          => $total_posts,
				'with_lists'           => $posts_with_lists,
				'with_tables'          => $posts_with_tables,
				'with_definitions'     => $posts_with_definitions,
				'with_qa_format'       => $posts_with_qa,
				'with_good_headings'   => $posts_with_good_headings,
			),
			'recommendation' => __( 'Add 40-60 word definition paragraphs answering "what is X". Use numbered lists for step-by-step content. Add comparison tables where relevant. Format Q&A sections with question headings. Target keywords ranking 5-10 with snippet optimization. Use GSC to find opportunities.', 'wpshadow' ),
		);
	}
}
