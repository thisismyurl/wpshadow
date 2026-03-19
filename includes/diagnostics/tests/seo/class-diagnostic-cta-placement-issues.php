<?php
/**
 * CTA Placement Issues Diagnostic
 *
 * Identifies posts with poor call-to-action placement or visibility.
 * CTAs should be strategically positioned throughout content.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Engagement
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CTA Placement Issues Diagnostic Class
 *
 * Analyzes where CTAs appear in content to ensure optimal placement for
 * maximum visibility and conversion potential.
 *
 * **Why This Matters:**
 * - CTA placement affects conversion rates by up to 300%
 * - Above-the-fold CTAs convert 20% better
 * - Multiple CTAs in long content improve conversions
 * - Poor placement = lost opportunities
 *
 * **Optimal CTA Placement:**
 * - Within first 300 words (above the fold)
 * - Middle of long-form content (800+ words)
 * - End of every post
 * - After key value points
 *
 * @since 1.6093.1200
 */
class Diagnostic_CTA_Placement_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cta-placement-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CTA Placement Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies posts lacking clear calls-to-action in strategic positions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'engagement';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if posts have CTA placement issues, null otherwise.
	 */
	public static function check() {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$posts_with_issues = array();
		$cta_patterns = array(
			'subscribe',
			'download',
			'get started',
			'sign up',
			'try free',
			'contact',
			'learn more',
			'buy now',
			'get it now',
			'click here',
			'join',
			'register',
		);

		foreach ( $posts as $post ) {
			$content = wp_strip_all_tags( $post->post_content );
			$word_count = str_word_count( $content );

			// Split content into sections for placement analysis
			$words = explode( ' ', $content );
			$first_300_words = implode( ' ', array_slice( $words, 0, 300 ) );
			$last_200_words = implode( ' ', array_slice( $words, -200 ) );

			$has_early_cta = false;
			$has_end_cta = false;

			// Check for CTA in first 300 words
			$first_300_lower = strtolower( $first_300_words );
			foreach ( $cta_patterns as $pattern ) {
				if ( strpos( $first_300_lower, $pattern ) !== false ) {
					$has_early_cta = true;
					break;
				}
			}

			// Check for CTA in last 200 words
			$last_200_lower = strtolower( $last_200_words );
			foreach ( $cta_patterns as $pattern ) {
				if ( strpos( $last_200_lower, $pattern ) !== false ) {
					$has_end_cta = true;
					break;
				}
			}

			// Issue if no CTA in either position
			if ( ! $has_early_cta || ! $has_end_cta ) {
				$issues = array();
				if ( ! $has_early_cta ) {
					$issues[] = 'No CTA in first 300 words';
				}
				if ( ! $has_end_cta ) {
					$issues[] = 'No CTA at end';
				}

				$posts_with_issues[] = array(
					'id'         => $post->ID,
					'title'      => $post->post_title,
					'word_count' => $word_count,
					'issues'     => $issues,
				);
			}
		}

		if ( empty( $posts_with_issues ) ) {
			return null;
		}

		$count = count( $posts_with_issues );
		$percentage = round( ( $count / count( $posts ) ) * 100 );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of posts, 2: percentage */
				__( '%1$d post(s) (%2$d%%) have poor CTA placement. Optimize placement for better conversions.', 'wpshadow' ),
				$count,
				$percentage
			),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/engagement-cta-placement',
			'details'      => array(
				'posts_with_issues' => $count,
				'percentage'        => $percentage,
				'sample_posts'      => array_slice( $posts_with_issues, 0, 10 ),
			),
		);
	}
}
