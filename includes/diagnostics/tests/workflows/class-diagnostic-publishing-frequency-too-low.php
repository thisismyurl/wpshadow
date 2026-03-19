<?php
/**
 * Publishing Frequency Too Low Diagnostic
 *
 * Detects infrequent publishing that hurts SEO rankings and
 * audience retention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Publishing
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Publishing Frequency Too Low Diagnostic Class
 *
 * Monitors publishing rate to detect insufficient content production
 * that impacts SEO and audience engagement.
 *
 * **Why This Matters:**
 * - Google favors sites with fresh content
 * - Infrequent posting loses audience interest
 * - < 1 post/month signals inactive site
 * - Competitors posting more will outrank you
 * - Reduces total indexed pages
 *
 * **Minimum Publishing Frequencies:**
 * - To maintain rankings: 1x per month
 * - To grow: 1x per week
 * - To compete: 2-3x per week
 * - Industry average: 2.5x per week
 *
 * @since 1.6093.1200
 */
class Diagnostic_Publishing_Frequency_Too_Low extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'publishing-frequency-too-low';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Publishing Frequency Too Low';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Infrequent publishing hurts SEO and audience retention';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publishing';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if frequency too low, null otherwise.
	 */
	public static function check() {
		// Get posts from last 90 days
		$ninety_days_ago = date( 'Y-m-d H:i:s', strtotime( '-90 days' ) );

		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'date_query'     => array(
					array(
						'after' => $ninety_days_ago,
					),
				),
			)
		);

		$posts_per_month = ( count( $recent_posts ) / 90 ) * 30;

		// Flag if < 2 posts per month (below growth threshold)
		if ( $posts_per_month >= 2 ) {
			return null;
		}

		// Get time since last post
		$latest_post = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
			)
		);

		$days_since_last = 0;
		if ( ! empty( $latest_post ) ) {
			$last_post_time = strtotime( $latest_post[0]->post_date );
			$days_since_last = ( time() - $last_post_time ) / DAY_IN_SECONDS;
		}

		$severity = 'low';
		$threat_level = 55;

		if ( $posts_per_month < 1 ) {
			$severity = 'high';
			$threat_level = 70;
		} elseif ( $posts_per_month <1.0 ) {
			$severity = 'medium';
			$threat_level = 60;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: posts per month, 2: days since last post */
				__( 'Publishing only %1$s posts per month (last post: %2$d days ago). Increase frequency to improve SEO and retention.', 'wpshadow' ),
				number_format_i18n( $posts_per_month, 1 ),
				round( $days_since_last )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/publishing-frequency',
			'details'      => array(
				'posts_per_month'       => round( $posts_per_month, 1 ),
				'posts_last_90_days'    => count( $recent_posts ),
				'days_since_last_post'  => round( $days_since_last ),
				'recommendation'        => 'Aim for minimum 4 posts per month (1 per week) for growth',
			),
		);
	}
}
