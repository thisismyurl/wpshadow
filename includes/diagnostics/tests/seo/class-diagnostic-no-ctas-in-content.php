<?php
/**
 * No CTAs in Content Diagnostic
 *
 * Detects posts that lack calls-to-action, missing opportunities for conversion.
 * CTAs are essential for guiding users toward desired actions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Engagement
 * @since      1.6034.2156
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No CTAs in Content Diagnostic Class
 *
 * Analyzes published content for the presence of calls-to-action. Posts without
 * CTAs represent lost conversion opportunities and reduced engagement.
 *
 * **Why This Matters:**
 * - CTAs increase conversion rates by 83%
 * - No CTA = no direction for readers
 * - Lost leads, subscribers, sales
 * - Reduced engagement metrics
 *
 * **Common CTAs:**
 * - "Subscribe to newsletter"
 * - "Download free guide"
 * - "Start free trial"
 * - "Contact us today"
 * - "Learn more"
 *
 * @since 1.6034.2156
 */
class Diagnostic_No_CTAs_In_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-ctas-in-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No CTAs in Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies posts without calls-to-action that could drive conversions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'engagement';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6034.2156
	 * @return array|null Finding array if posts lack CTAs, null otherwise.
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

		$posts_without_ctas = array();
		$cta_patterns = array(
			'subscribe',
			'download',
			'get started',
			'sign up',
			'try free',
			'contact us',
			'learn more',
			'buy now',
			'get it now',
			'click here',
		);

		foreach ( $posts as $post ) {
			$content = strtolower( wp_strip_all_tags( $post->post_content ) );
			$has_cta = false;

			foreach ( $cta_patterns as $pattern ) {
				if ( strpos( $content, $pattern ) !== false ) {
					$has_cta = true;
					break;
				}
			}

			if ( ! $has_cta ) {
				$posts_without_ctas[] = array(
					'id'    => $post->ID,
					'title' => $post->post_title,
					'date'  => get_the_date( '', $post ),
				);
			}
		}

		if ( empty( $posts_without_ctas ) ) {
			return null;
		}

		$count = count( $posts_without_ctas );
		$percentage = round( ( $count / count( $posts ) ) * 100 );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of posts, 2: percentage */
				__( '%1$d post(s) (%2$d%%) lack calls-to-action. You\'re missing conversion opportunities.', 'wpshadow' ),
				$count,
				$percentage
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/engagement-no-ctas',
			'details'      => array(
				'posts_without_ctas' => $count,
				'percentage'         => $percentage,
				'sample_posts'       => array_slice( $posts_without_ctas, 0, 10 ),
			),
		);
	}
}
