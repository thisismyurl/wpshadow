<?php
/**
 * Diagnostic: Poor Heading Hierarchy
 *
 * Detects heading hierarchy violations (e.g., H4 after H2) which confuse
 * screen readers and search engines. Proper hierarchy is critical for
 * accessibility (WCAG) and SEO.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7030.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Broken Heading Hierarchy Diagnostic Class
 *
 * Checks for proper heading structure (H1 > H2 > H3 > H4 > H5 > H6).
 *
 * Detection methods:
 * - Extract headings via regex
 * - Validate sequential hierarchy
 * - Detect skipped levels
 *
 * @since 1.7030.1500
 */
class Diagnostic_Broken_Heading_Hierarchy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'broken-heading-hierarchy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Poor Heading Hierarchy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'H4 after H2 confuses screen readers and SEO';

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
	 * - 3 points: <10% of posts have hierarchy issues
	 * - 2 points: <25% have issues
	 * - 0 points: ≥25% have issues
	 *
	 * @since  1.7030.1500
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score                = 0;
		$max_score            = 3;
		$posts_with_issues    = 0;
		$problem_posts        = array();

		// Get sample of recent posts.
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

			// Extract all headings with their levels.
			preg_match_all( '/<h([1-6])[^>]*>(.*?)<\/h\1>/is', $content, $matches, PREG_SET_ORDER );

			if ( empty( $matches ) ) {
				continue;
			}

			$heading_levels = array();
			foreach ( $matches as $match ) {
				$heading_levels[] = intval( $match[1] );
			}

			// Check for hierarchy violations.
			$has_violation = false;
			$violations    = array();

			for ( $i = 1; $i < count( $heading_levels ); $i++ ) {
				$prev_level    = $heading_levels[ $i - 1 ];
				$current_level = $heading_levels[ $i ];

				// Check if we skipped a level (e.g., H2 to H4).
				if ( $current_level > $prev_level + 1 ) {
					$has_violation = true;
					$violations[]  = sprintf(
						'H%d → H%d (skipped H%d)',
						$prev_level,
						$current_level,
						$prev_level + 1
					);
				}
			}

			if ( $has_violation ) {
				$posts_with_issues++;
				if ( count( $problem_posts ) < 10 ) {
					$problem_posts[] = array(
						'post_id'    => $post->ID,
						'title'      => $post->post_title,
						'url'        => get_permalink( $post->ID ),
						'violations' => $violations,
					);
				}
			}
		}

		$issue_percentage = ( $posts_with_issues / count( $posts ) ) * 100;

		// Scoring.
		if ( $issue_percentage < 10 ) {
			$score = 3;
		} elseif ( $issue_percentage < 25 ) {
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
				/* translators: 1: percentage, 2: number of posts with issues, 3: total posts */
				__( '%1$d%% of posts (%2$d/%3$d) have heading hierarchy violations. Proper hierarchy: H1 (page title) > H2 (sections) > H3 (subsections) > H4 (details). Never skip levels. Why it matters: Screen readers use headings for navigation - broken hierarchy makes content inaccessible. Search engines use hierarchy to understand content structure - violations weaken topical relevance. WCAG 2.1 Level A requirement. Common mistakes: Choosing headings by size (visual) not structure (semantic), Skipping H3 to use H4 for smaller text, Multiple H1s on page. Fix: Review content outline, Use heading levels sequentially, Install editor plugins that show heading structure.', 'wpshadow' ),
				round( $issue_percentage ),
				$posts_with_issues,
				count( $posts )
			),
			'severity'      => 'medium',
			'threat_level'  => 40,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/broken-heading-hierarchy',
			'problem_posts' => $problem_posts,
			'stats'         => array(
				'total_posts'      => count( $posts ),
				'posts_with_issues' => $posts_with_issues,
				'percentage'       => round( $issue_percentage, 1 ),
			),
			'recommendation' => __( 'Review posts with heading violations. Ensure proper sequence: H1→H2→H3→H4 (never skip levels). Use browser extensions or accessibility checkers to visualize heading structure. Consider headings editor plugins.', 'wpshadow' ),
		);
	}
}
