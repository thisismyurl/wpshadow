<?php
/**
 * Diagnostic: No Lists or Bullets
 *
 * Detects posts with 1,500+ words and zero lists. Lists increase
 * scannability by 300% and improve content comprehension.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Lists Diagnostic Class
 *
 * Checks for proper list usage in content.
 *
 * Detection methods:
 * - <ul> and <ol> tag counting
 * - Word count analysis
 * - List-to-content ratio
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Lists extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-lists';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Lists or Bullets';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = '1,500+ words with zero lists - Lists increase scannability 300%';

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
	 * - 3 points: <20% of long posts without lists
	 * - 2 points: <40% without lists
	 * - 0 points: ≥40% without lists
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score                = 0;
		$max_score            = 3;
		$long_posts_count     = 0;
		$posts_without_lists  = 0;
		$problem_posts        = array();

		// Get sample posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 40,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		foreach ( $posts as $post ) {
			$content    = $post->post_content;
			$word_count = str_word_count( wp_strip_all_tags( $content ) );

			// Only check longer posts (1,500+ words).
			if ( $word_count < 1500 ) {
				continue;
			}

			$long_posts_count++;

			// Count lists (ul and ol).
			$ul_count = substr_count( $content, '<ul' );
			$ol_count = substr_count( $content, '<ol' );
			$total_lists = $ul_count + $ol_count;

			if ( $total_lists === 0 ) {
				$posts_without_lists++;
				if ( count( $problem_posts ) < 10 ) {
					$problem_posts[] = array(
						'post_id'    => $post->ID,
						'title'      => $post->post_title,
						'word_count' => $word_count,
						'url'        => get_permalink( $post->ID ),
					);
				}
			}
		}

		if ( $long_posts_count === 0 ) {
			// No long posts to check.
			return null;
		}

		$no_list_percentage = ( $posts_without_lists / $long_posts_count ) * 100;

		// Scoring.
		if ( $no_list_percentage < 20 ) {
			$score = 3;
		} elseif ( $no_list_percentage < 40 ) {
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
				/* translators: 1: percentage, 2: number without lists, 3: total long posts */
				__( '%1$d%% of long posts (%2$d/%3$d) contain no lists or bullets. Lists provide: 300%% increased scannability (brain processes lists faster), Better comprehension (sequential/grouped info easier to digest), Featured snippet opportunities (Google loves lists), Improved retention (numbered lists = 72%% better recall), Mobile-friendly (compact, scannable on small screens). When to use lists: Steps/procedures (ordered lists), Features/benefits (unordered), Comparisons (parallel structure), Tips/examples (bullets), Requirements/specifications (checklists). Avoid: Single-item lists (use paragraph instead), Inconsistent formatting (mixing sentences/fragments), Over-nesting (stick to 2 levels max). Optimal: 2-3 lists per 1,000 words.', 'wpshadow' ),
				round( $no_list_percentage ),
				$posts_without_lists,
				$long_posts_count
			),
			'severity'      => 'medium',
			'threat_level'  => 30,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/no-lists',
			'problem_posts' => $problem_posts,
			'stats'         => array(
				'long_posts'      => $long_posts_count,
				'without_lists'   => $posts_without_lists,
				'percentage'      => round( $no_list_percentage, 1 ),
			),
			'recommendation' => __( 'Review long posts without lists. Convert sequential info to ordered lists. Group related items as bullets. Use lists for: steps, tips, features, examples, requirements. Aim for 2-3 lists per 1,000 words.', 'wpshadow' ),
		);
	}
}
