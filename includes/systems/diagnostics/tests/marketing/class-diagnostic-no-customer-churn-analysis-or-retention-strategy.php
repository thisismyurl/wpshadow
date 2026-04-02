<?php
/**
 * No Customer Churn Analysis or Retention Strategy Diagnostic
 *
 * Checks if churn analysis and retention strategy exist.
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
 * Customer Churn Analysis Diagnostic
 *
 * A 5% increase in retention yields 25-95% increase in profit.
 * Retention is your foundation.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Customer_Churn_Analysis_Or_Retention_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-churn-analysis-retention-strategy';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Customer Churn Analysis/Retention Strategy';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if churn analysis and retention strategy exist';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_churn_analysis() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No churn analysis or retention strategy detected. A 5% increase in retention yields 25-95% increase in profit. Retention is more important than acquisition. Track: 1) Churn rate (% customers lost per month), 2) When churn happens (first week? 3 months?), 3) Why churn happens (interview lost customers), 4) Cohort retention (track by signup month), 5) Retention by segment (which customers stay longer?). Common churn reasons: 1) Not seeing value quickly (poor onboarding), 2) Missing features (not using it), 3) Product issues (bugs, slow), 4) Support (problems get ignored), 5) Price (too expensive). Fixes: Better onboarding (first value in 7 days), proactive outreach (health checks), customer success (ensure they win), quick support (hours not days).', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/customer-churn-retention-strategy',
				'details'     => array(
					'issue'               => __( 'No churn analysis or retention strategy detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement churn tracking and retention improvement plan', 'wpshadow' ),
					'business_impact'     => __( 'Missing 25-95% profit increase from 5% retention improvement', 'wpshadow' ),
					'churn_metrics'       => self::get_churn_metrics(),
					'retention_strategies' => self::get_retention_strategies(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if churn analysis exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if analysis detected, false otherwise.
	 */
	private static function has_churn_analysis() {
		// Check for churn/retention content
		$churn_posts = self::count_posts_by_keywords(
			array(
				'churn',
				'retention',
				'customer lifetime',
				'keep customers',
				'engagement',
			)
		);

		return $churn_posts > 0;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since 1.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return int Number of matching posts.
	 */
	private static function count_posts_by_keywords( $keywords ) {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'posts_per_page' => 1,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				++$total;
			}
		}

		return $total;
	}

	/**
	 * Get churn metrics to track.
	 *
	 * @since 1.6093.1200
	 * @return array Churn metrics with definitions.
	 */
	private static function get_churn_metrics() {
		return array(
			'rate'             => array(
				'metric'      => __( 'Monthly Churn Rate', 'wpshadow' ),
				'formula'     => __( 'Customers lost / Starting customers × 100', 'wpshadow' ),
				'example'     => __( '5 lost / 100 starting = 5% churn', 'wpshadow' ),
				'benchmark'   => __( '<2% = excellent, 2-5% = good, >5% = concerning', 'wpshadow' ),
			),
			'timing'           => array(
				'metric'      => __( 'When Churn Happens', 'wpshadow' ),
				'focus'       => __( 'Do customers churn in week 1 (bad onboarding) or month 3 (lost interest)?', 'wpshadow' ),
				'example'     => __( 'Plot "% remaining customers" by days/weeks/months', 'wpshadow' ),
				'action'      => __( 'Focus on highest churn period first', 'wpshadow' ),
			),
			'cohort_retention' => array(
				'metric'      => __( 'Cohort Retention', 'wpshadow' ),
				'focus'       => __( 'Track "% of Jan signups still here in Feb, Mar, Apr..."', 'wpshadow' ),
				'example'     => __( '100 Jan signups → 80 in Feb (80%) → 50 in May (50%)', 'wpshadow' ),
				'action'      => __( 'Should improve with better onboarding/product', 'wpshadow' ),
			),
			'segmentation'     => array(
				'metric'      => __( 'Retention by Segment', 'wpshadow' ),
				'focus'       => __( 'Which segments stay longest? (by company size, industry, etc)', 'wpshadow' ),
				'example'     => __( 'Enterprise 85% retention | SMB 60% | Startup 30%', 'wpshadow' ),
				'action'      => __( 'Double down on segments that retain best', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get retention improvement strategies.
	 *
	 * @since 1.6093.1200
	 * @return array Retention strategies.
	 */
	private static function get_retention_strategies() {
		return array(
			'onboarding'       => array(
				'strategy'  => __( '1. Better Onboarding (First Value in 7 Days)', 'wpshadow' ),
				'tactics'   => array(
					__( 'Welcome email immediately', 'wpshadow' ),
					__( 'Guided tour or checklist', 'wpshadow' ),
					__( 'Quick win (do something useful in 30min)', 'wpshadow' ),
					__( 'Success metrics defined', 'wpshadow' ),
				),
			),
			'engagement'       => array(
				'strategy'  => __( '2. Engagement & Usage (Keep Using It)', 'wpshadow' ),
				'tactics'   => array(
					__( 'Email tips and feature highlights', 'wpshadow' ),
					__( 'In-app nudges (unused features)', 'wpshadow' ),
					__( 'Gamification (achievements, milestones)', 'wpshadow' ),
					__( 'Community (forums, user group)', 'wpshadow' ),
				),
			),
			'success_proactive' => array(
				'strategy'  => __( '3. Proactive Customer Success (Ensure They Win)', 'wpshadow' ),
				'tactics'   => array(
					__( 'Health scores (green/yellow/red)', 'wpshadow' ),
					__( 'Monthly check-ins', 'wpshadow' ),
					__( 'Business reviews (ROI demonstration)', 'wpshadow' ),
					__( 'Expansion opportunities', 'wpshadow' ),
				),
			),
			'support'          => array(
				'strategy'  => __( '4. Great Support (Fast Problem Resolution)', 'wpshadow' ),
				'tactics'   => array(
					__( 'Support response within 4 hours', 'wpshadow' ),
					__( 'Knowledge base/FAQ self-serve', 'wpshadow' ),
					__( 'Video tutorials', 'wpshadow' ),
					__( 'Community support (power users helping)', 'wpshadow' ),
				),
			),
			'win_back'         => array(
				'strategy'  => __( '5. Win-Back Campaigns (For Churned Customers)', 'wpshadow' ),
				'tactics'   => array(
					__( 'Email 30 days after cancellation', 'wpshadow' ),
					__( 'Ask why they left (feedback)', 'wpshadow' ),
					__( 'Offer to fix (most common reason)', 'wpshadow' ),
					__( 'Discount or special offer', 'wpshadow' ),
				),
			),
		);
	}
}
