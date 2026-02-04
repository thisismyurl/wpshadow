<?php
/**
 * No Growth Rate Tracking or Growth Strategy Diagnostic
 *
 * Checks if growth metrics are tracked systematically.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Growth Rate Tracking Diagnostic
 *
 * Companies that track growth rates grow 3x faster than those that don't.
 * What gets measured gets managed.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Growth_Rate_Tracking_Or_Growth_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-growth-rate-tracking-growth-strategy';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Growth Rate Tracking/Growth Strategy';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if growth metrics are tracked systematically';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_growth_tracking() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No growth tracking or strategy detected. What gets measured gets managed. Companies tracking growth rates grow 3x faster. Track: 1) Monthly Recurring Revenue (MRR) or Annual (ARR), 2) Customer count (daily active, monthly active, total), 3) Growth rate (MoM % growth), 4) Churn rate (% customers lost per month), 5) Net growth = new customers - churned. Benchmark: SaaS startups target 7-10% monthly growth (84-213% annually). Set 90-day goals: "Grow from $50k to $75k MRR" then break into weekly milestones. Publish growth publicly (builds credibility). Review weekly: what worked, what didn\'t, where to double down.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/growth-rate-tracking',
				'details'     => array(
					'issue'               => __( 'No growth tracking or strategy detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement growth tracking and set ambitious growth goals', 'wpshadow' ),
					'business_impact'     => __( 'Missing 3x faster growth from measurement and focus', 'wpshadow' ),
					'metrics_to_track'    => self::get_metrics_to_track(),
					'growth_goals'        => self::get_growth_goals(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if growth tracking exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if tracking detected, false otherwise.
	 */
	private static function has_growth_tracking() {
		// Check for growth-related content
		$growth_posts = self::count_posts_by_keywords(
			array(
				'growth rate',
				'mrr',
				'arr',
				'growth strategy',
				'scaling',
			)
		);

		return $growth_posts > 0;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since  1.6035.0000
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
	 * Get key metrics to track.
	 *
	 * @since  1.6035.0000
	 * @return array Key metrics with definitions.
	 */
	private static function get_metrics_to_track() {
		return array(
			'mrr'         => array(
				'metric'      => __( 'Monthly Recurring Revenue (MRR)', 'wpshadow' ),
				'definition'  => __( 'Predictable monthly revenue (subscriptions)', 'wpshadow' ),
				'example'     => __( '100 customers × $500/month = $50,000 MRR', 'wpshadow' ),
				'frequency'   => __( 'Track daily, report monthly', 'wpshadow' ),
			),
			'growth_rate' => array(
				'metric'      => __( 'Month-over-Month Growth Rate', 'wpshadow' ),
				'definition'  => __( '(This month - Last month) / Last month × 100', 'wpshadow' ),
				'example'     => __( '($50k - $45k) / $45k = 11.1% growth', 'wpshadow' ),
				'benchmark'   => __( '7-10% monthly = healthy SaaS growth', 'wpshadow' ),
			),
			'churn'       => array(
				'metric'      => __( 'Churn Rate (Customer Loss %)', 'wpshadow' ),
				'definition'  => __( 'Customers lost / Starting customers × 100', 'wpshadow' ),
				'example'     => __( '5 lost / 100 starting = 5% monthly churn', 'wpshadow' ),
				'benchmark'   => __( '<2% monthly churn is excellent', 'wpshadow' ),
			),
			'net_growth'  => array(
				'metric'      => __( 'Net Growth (New - Churned)', 'wpshadow' ),
				'definition'  => __( 'Net change in customers per month', 'wpshadow' ),
				'example'     => __( '+15 new customers - 5 churned = +10 net growth', 'wpshadow' ),
				'benchmark'   => __( 'Should exceed churn significantly', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get growth goal framework.
	 *
	 * @since  1.6035.0000
	 * @return array Growth goal examples.
	 */
	private static function get_growth_goals() {
		return array(
			'monthly'    => __( 'Monthly Goal: Grow from $50k to $55k MRR (10% growth)', 'wpshadow' ),
			'quarterly'  => __( 'Quarterly Goal: Grow from $50k to $75k MRR (50% growth)', 'wpshadow' ),
			'annual'     => __( 'Annual Goal: Grow from $50k to $150k MRR (200% growth)', 'wpshadow' ),
			'breakdown'  => __( 'Break into weekly milestones: $50k → $52k → $54k → $57k', 'wpshadow' ),
			'initiatives' => __( 'Identify 3-5 growth initiatives to hit goal (content, ads, sales)', 'wpshadow' ),
		);
	}
}
