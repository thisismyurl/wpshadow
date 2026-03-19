<?php
/**
 * Diagnostic: Missing Call-to-Action
 *
 * Detects posts without CTAs, missing conversion opportunities.
 * Every post should guide readers to a next action.
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
 * Missing CTA Diagnostic Class
 *
 * Checks for call-to-action presence in posts.
 *
 * Detection methods:
 * - CTA pattern matching
 * - Button/link detection
 * - Action keywords
 *
 * @since 1.6093.1200
 */
class Diagnostic_Missing_CTA extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-cta';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Call-to-Action';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Posts without CTA miss conversion opportunities';

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
	 * - 3 points: <10% posts without CTA
	 * - 2 points: <25% without CTA
	 * - 0 points: ≥25% without CTA
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score                = 0;
		$max_score            = 3;
		$posts_without_cta    = 0;
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
			$content = strtolower( $post->post_content );

			// Check for CTA indicators.
			$has_cta = false;

			// Button/link patterns.
			if ( strpos( $content, '<button' ) !== false || strpos( $content, '[button' ) !== false ) {
				$has_cta = true;
			}

			// Action keywords.
			$cta_keywords = array(
				'download',
				'subscribe',
				'sign up',
				'get started',
				'learn more',
				'read more',
				'try free',
				'buy now',
				'get access',
				'join',
				'register',
				'contact us',
				'get quote',
			);

			if ( ! $has_cta ) {
				foreach ( $cta_keywords as $keyword ) {
					if ( strpos( $content, $keyword ) !== false ) {
						$has_cta = true;
						break;
					}
				}
			}

			if ( ! $has_cta ) {
				$posts_without_cta++;
				if ( count( $problem_posts ) < 15 ) {
					$problem_posts[] = array(
						'post_id' => $post->ID,
						'title'   => $post->post_title,
						'date'    => $post->post_date,
						'url'     => get_permalink( $post->ID ),
					);
				}
			}
		}

		$missing_cta_percentage = ( $posts_without_cta / count( $posts ) ) * 100;

		// Scoring.
		if ( $missing_cta_percentage < 10 ) {
			$score = 3;
		} elseif ( $missing_cta_percentage < 25 ) {
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
				/* translators: 1: percentage, 2: number without CTA, 3: total posts */
				__( '%1$d%% of posts (%2$d/%3$d) lack calls-to-action. Every post should guide readers to next step. Types of CTAs: Primary (main conversion goal - email signup, product purchase), Secondary (engagement - share, comment, related posts), Soft (low-commitment - "learn more", "read next"). CTA placement: Top (high-intent visitors), After intro (they\'re interested), Mid-content (natural break), End (finished reading). Best practices: Specific action words ("Download Free Guide" not "Click Here"), Clear benefit ("Get 50%% More Traffic"), Visual prominence (buttons, not just text links), One primary CTA per post (multiple secondary ok). Without CTAs = 0%% conversion. With CTAs = 2-5%% industry average.', 'wpshadow' ),
				round( $missing_cta_percentage ),
				$posts_without_cta,
				count( $posts )
			),
			'severity'      => 'medium',
			'threat_level'  => 35,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/missing-cta',
			'problem_posts' => $problem_posts,
			'stats'         => array(
				'total_posts'       => count( $posts ),
				'without_cta'       => $posts_without_cta,
				'percentage'        => round( $missing_cta_percentage, 1 ),
			),
			'recommendation' => __( 'Add CTA to every post. Match CTA to content topic. Use specific action language. Create button-style CTAs (not just text links). Test different placements. Track conversion rates to optimize.', 'wpshadow' ),
		);
	}
}
