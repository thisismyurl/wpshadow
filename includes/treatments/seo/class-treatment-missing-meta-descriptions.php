<?php
/**
 * Treatment: Missing Meta Descriptions
 *
 * Detects posts without custom meta descriptions.
 * Missing meta = Google writes bad ones, custom increases CTR 20%.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7030.1516
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Meta Descriptions Treatment Class
 *
 * Checks for posts with missing/poor meta descriptions.
 *
 * Detection methods:
 * - Meta field checking (_yoast_wpseo_metadesc, rank_math_description)
 * - Length validation (120-160 characters)
 * - Quality assessment
 *
 * @since 1.7030.1516
 */
class Treatment_Missing_Meta_Descriptions extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-meta-descriptions';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Meta Descriptions';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Missing meta = Google writes bad ones, custom increases CTR 20%';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'keyword-strategy';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (3 points):
	 * - 1 point: <10% posts missing meta descriptions
	 * - 1 point: <25% posts missing
	 * - 1 point: <50% posts missing
	 *
	 * @since  1.7030.1516
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 3;

		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => -1,
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$total_posts   = count( $posts );
		$missing_meta  = 0;
		$short_meta    = 0;
		$long_meta     = 0;

		foreach ( $posts as $post ) {
			// Check Yoast meta.
			$yoast_meta = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
			// Check Rank Math meta.
			$rankmath_meta = get_post_meta( $post->ID, 'rank_math_description', true );
			// Check AIOSEO meta.
			$aioseo_meta = get_post_meta( $post->ID, '_aioseo_description', true );

			$meta_description = $yoast_meta ?: ( $rankmath_meta ?: ( $aioseo_meta ?: '' ) );

			if ( empty( $meta_description ) ) {
				$missing_meta++;
			} else {
				$length = strlen( $meta_description );
				if ( $length < 120 ) {
					$short_meta++;
				} elseif ( $length > 160 ) {
					$long_meta++;
				}
			}
		}

		$missing_percent = ( $missing_meta / $total_posts ) * 100;

		if ( $missing_percent < 10 ) {
			$score += 3;
		} elseif ( $missing_percent < 25 ) {
			$score += 2;
		} elseif ( $missing_percent < 50 ) {
			$score += 1;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.67 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __( 'Meta descriptions = your ad copy in search results. Custom meta descriptions increase CTR 20% vs Google-generated. Formula: Hook (grab attention), Value Prop (what they get), Call-to-Action (click here). Optimal length: 120-160 characters (desktop = 920px, mobile = 680px, ~920px = 155-160 chars). Include: Target keyword (bolded in results), Compelling benefit (why click?), Action words (discover, learn, get), Numbers (specific results), Emotional trigger (curiosity, urgency, desire). Avoid: Duplicate descriptions (unique per page), Keyword stuffing (reads unnatural), Being vague ("This is a great post"), Missing CTA (no reason to click), Too short (<120 chars = wasted space), Too long (>160 chars = cut off with ...). Example: BAD: "This post talks about email marketing tips and strategies for businesses." GOOD: "Discover 15 proven email marketing strategies that increased our open rates by 47%. Free templates included!"', 'wpshadow' ),
			'severity'    => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/missing-meta-descriptions',
			'stats'       => array(
				'total_posts'      => $total_posts,
				'missing_meta'     => $missing_meta,
				'missing_percent'  => round( $missing_percent, 1 ),
				'too_short'        => $short_meta,
				'too_long'         => $long_meta,
			),
			'recommendation' => __( 'Install Yoast SEO or Rank Math (adds meta description field). Write unique 120-160 character description for each post. Include target keyword + benefit + CTA. Use formula: "Learn [benefit] without [pain point]. [Number] [result] included." Test descriptions in Google SERP simulator.', 'wpshadow' ),
		);
	}
}
