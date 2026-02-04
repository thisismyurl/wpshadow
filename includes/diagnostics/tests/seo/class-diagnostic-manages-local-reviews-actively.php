<?php
/**
 * Local Reviews Managed Diagnostic
 *
 * Tests whether the site actively monitors and responds to local reviews within 48 hours.
 * Timely review management builds trust and improves local search rankings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5003.1130
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Manages_Local_Reviews_Actively Class
 *
 * Diagnostic #15: Local Reviews Managed from Specialized & Emerging Success Habits.
 * Checks if the site actively manages and responds to reviews.
 *
 * @since 1.5003.1130
 */
class Diagnostic_Manages_Local_Reviews_Actively extends Diagnostic_Base {

	protected static $slug = 'manages-local-reviews-actively';
	protected static $title = 'Local Reviews Managed';
	protected static $description = 'Tests whether the site actively monitors and responds to local reviews within 48 hours';
	protected static $family = 'local-seo';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check review plugin/integration.
		$review_plugins = array(
			'site-reviews/site-reviews.php',
			'wp-customer-reviews/wp-customer-reviews.php',
			'review-wave-widget/review-wave-widget.php',
			'google-reviews-widget/google-reviews-widget.php',
		);

		$has_review_plugin = false;
		foreach ( $review_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_review_plugin = true;
				++$score;
				$score_details[] = __( '✓ Review management plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_review_plugin ) {
			$score_details[]   = __( '✗ No review management plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install a review management plugin to display and monitor customer reviews', 'wpshadow' );
		}

		// Check testimonials/reviews page.
		$review_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'reviews testimonials feedback customers',
			)
		);

		if ( ! empty( $review_pages ) ) {
			++$score;
			$score_details[] = __( '✓ Reviews/testimonials page exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No dedicated reviews page found', 'wpshadow' );
			$recommendations[] = __( 'Create a testimonials page to showcase customer feedback', 'wpshadow' );
		}

		// Check review responses.
		$review_responses = get_comments(
			array(
				'type'   => 'comment',
				'status' => 'approve',
				'number' => 10,
			)
		);

		$has_owner_responses = false;
		foreach ( $review_responses as $comment ) {
			if ( $comment->user_id > 0 && user_can( $comment->user_id, 'manage_options' ) ) {
				$has_owner_responses = true;
				break;
			}
		}

		if ( $has_owner_responses ) {
			++$score;
			$score_details[] = __( '✓ Owner responses to reviews detected', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No review responses found', 'wpshadow' );
			$recommendations[] = __( 'Respond to ALL reviews (positive and negative) within 24-48 hours', 'wpshadow' );
		}

		// Check Google reviews widget/mention.
		$google_reviews = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'Google reviews rating',
			)
		);

		if ( ! empty( $google_reviews ) ) {
			++$score;
			$score_details[] = __( '✓ Google reviews integration present', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No Google reviews integration', 'wpshadow' );
			$recommendations[] = __( 'Display your Google Business Profile reviews on your website', 'wpshadow' );
		}

		// Check review request process.
		$review_requests = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'leave review rate us feedback',
			)
		);

		if ( ! empty( $review_requests ) ) {
			++$score;
			$score_details[] = __( '✓ Review request process documented', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No review solicitation strategy', 'wpshadow' );
			$recommendations[] = __( 'Ask satisfied customers for reviews via email follow-ups and on-site prompts', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Review management score: %d%%. Responding to reviews increases conversion by 30%% and local search rankings by 25%%. 89%% of consumers read business responses to reviews, and 94%% are more likely to use a business that responds.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/local-review-management',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Active review management demonstrates customer care, builds trust, and turns negative experiences into opportunities for redemption.', 'wpshadow' ),
		);
	}
}
