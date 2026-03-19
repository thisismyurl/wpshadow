<?php
/**
 * Evergreen vs Timely Content Balance Diagnostic
 *
 * Verifies site maintains healthy balance between evergreen content
 * and timely/trending topics.
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
 * Evergreen Content Balance Diagnostic Class
 *
 * Analyzes content to detect balance between enduring evergreen topics
 * and timely news/trends.
 *
 * **Why This Matters:**
 * - Evergreen content provides sustained traffic
 * - 70-80% traffic should come from evergreen
 * - Timely content drives spikes and relevance
 * - Balance maximizes short and long-term value
 * - Evergreen builds authority over time
 *
 * **Content Balance:**
 * - 70-80% evergreen (timeless topics)
 * - 20-30% timely (news, trends, current events)
 * - Evergreen = foundations that compound
 * - Timely = relevance and fresh traffic
 *
 * @since 1.6093.1200
 */
class Diagnostic_Balances_Evergreen_Vs_Timely extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'balances-evergreen-vs-timely';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Evergreen Content Balance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site balances evergreen content with timely topics';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if poor balance, null otherwise.
	 */
	public static function check() {
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( count( $recent_posts ) < 15 ) {
			return null; // Need sufficient content to assess
		}

		$timely_count = 0;
		$timely_posts = array();

		foreach ( $recent_posts as $post ) {
			$title = strtolower( $post->post_title );
			$content = strtolower( $post->post_content );
			$combined = $title . ' ' . $content;

			// Detect timely indicators
			if ( self::is_timely_content( $combined, $post->post_date ) ) {
				$timely_count++;
				$timely_posts[] = array(
					'id'    => $post->ID,
					'title' => $post->post_title,
					'date'  => $post->post_date,
				);
			}
		}

		$timely_percentage = ( $timely_count / count( $recent_posts ) ) * 100;

		// Good balance: 15-40% timely content
		if ( $timely_percentage >= 15 && $timely_percentage <= 40 ) {
			return null; // Healthy balance
		}

		$severity = 'medium';
		$threat_level = 40;
		$issue_type = '';

		if ( $timely_percentage < 15 ) {
			$issue_type = 'too_little_timely';
			$description = sprintf(
				/* translators: %d: timely percentage */
				__( 'Too little timely content (%d%%). Add news and trends to stay relevant and capture fresh traffic.', 'wpshadow' ),
				round( $timely_percentage )
			);
		} else {
			$issue_type = 'too_much_timely';
			$threat_level = 50;
			$description = sprintf(
				/* translators: %d: timely percentage */
				__( 'Too much timely content (%d%%). Increase evergreen posts for sustained long-term traffic.', 'wpshadow' ),
				round( $timely_percentage )
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/evergreen-content',
			'details'      => array(
				'timely_percentage' => round( $timely_percentage, 1 ),
				'timely_count'      => $timely_count,
				'total_posts'       => count( $recent_posts ),
				'issue_type'        => $issue_type,
				'recommendation'    => __( 'Target 70-80% evergreen, 20-30% timely content', 'wpshadow' ),
				'sample_timely'     => array_slice( $timely_posts, 0, 5 ),
				'evergreen_examples' => array(
					'How-to guides and tutorials',
					'Ultimate guides and resources',
					'Beginner guides and FAQs',
					'Best practices and frameworks',
					'Case studies and success stories',
				),
				'timely_examples'   => array(
					'Industry news and announcements',
					'Trending topics and discussions',
					'Event coverage and recaps',
					'Product launches and reviews',
					'Seasonal and holiday content',
				),
			),
		);
	}

	/**
	 * Detect if content is timely/trending
	 *
	 * @since 1.6093.1200
	 * @param  string $content Combined title and content.
	 * @param  string $post_date Post publish date.
	 * @return bool True if content appears timely.
	 */
	private static function is_timely_content( $content, $post_date ) {
		$current_year = (int) date( 'Y' );
		$post_year = (int) date( 'Y', strtotime( $post_date ) );

		// Contains current/recent year in title
		if ( preg_match( '/\b(20\d{2}|' . $current_year . ')\b/', $content ) &&
			 abs( $current_year - $post_year ) <= 1 ) {
			return true;
		}

		// Timely keywords
		$timely_patterns = array(
			'\b(news|breaking|update|announcement|just released)\b',
			'\b(trend|trending|latest|new|recently)\b',
			'\b(this (week|month|year)|last (week|month))\b',
			'\b(event|conference|webinar|happening)\b',
			'\b(review|roundup|recap|wrap[- ]?up)\b',
		);

		foreach ( $timely_patterns as $pattern ) {
			if ( preg_match( '/' . $pattern . '/i', $content ) ) {
				return true;
			}
		}

		return false;
	}
}
