<?php
/**
 * No Customer Acquisition Cost or Lifetime Value Tracking Diagnostic
 *
 * Checks if CAC and LTV metrics are tracked.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CAC and LTV Tracking Diagnostic
 *
 * If you don't know how much you spend to acquire a customer or
 * how much they're worth, you can't make smart business decisions.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Customer_Acquisition_Cost_Or_Lifetime_Value_Tracking extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-cac-ltv-tracking';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Customer Acquisition Cost/Lifetime Value Tracking';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CAC and LTV metrics are tracked';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_cac_ltv_tracking() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No CAC/LTV tracking detected. These two metrics determine if your business can scale. CAC (Customer Acquisition Cost) = total marketing spend / customers acquired. LTV (Lifetime Value) = total revenue from customer / lifespan. Rule: LTV:CAC ratio must be 3:1 or better (spend $1 to get $3). Calculate: 1) Monthly marketing spend, 2) Customers acquired this month, 3) CAC = spend/customers, 4) Average revenue per customer, 5) Average customer lifespan (months), 6) LTV = revenue × lifespan. Example: $10,000 marketing → 100 customers = $100 CAC. Customer pays $50/month, stays 12 months = $600 LTV. Ratio 6:1 = healthy. Track monthly: improve CAC (better marketing), improve LTV (retention, upsells).', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cac-ltv-tracking?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'               => __( 'No CAC/LTV tracking detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement CAC and LTV tracking to optimize profitability', 'wpshadow' ),
					'business_impact'     => __( 'Cannot determine if business model is profitable or scalable', 'wpshadow' ),
					'metrics'             => self::get_metrics(),
					'improvement_levers'  => self::get_improvement_levers(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if CAC/LTV tracking exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if tracking detected, false otherwise.
	 */
	private static function has_cac_ltv_tracking() {
		// Check for CAC/LTV-related content
		$cac_posts = self::count_posts_by_keywords(
			array(
				'customer acquisition cost',
				'lifetime value',
				'cac',
				'ltv',
				'unit economics',
			)
		);

		return $cac_posts > 0;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since 0.6093.1200
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
	 * Get CAC/LTV metrics.
	 *
	 * @since 0.6093.1200
	 * @return array Metrics definitions with formulas.
	 */
	private static function get_metrics() {
		return array(
			'cac'        => array(
				'name'       => __( 'Customer Acquisition Cost (CAC)', 'wpshadow' ),
				'formula'    => __( 'Total Marketing Spend / New Customers = CAC', 'wpshadow' ),
				'example'    => __( '$50,000 marketing / 100 customers = $500 CAC', 'wpshadow' ),
				'benchmark'  => __( 'Lower is better (you want efficient acquisition)', 'wpshadow' ),
			),
			'ltv'        => array(
				'name'       => __( 'Customer Lifetime Value (LTV)', 'wpshadow' ),
				'formula'    => __( 'Average Revenue per Customer × Customer Lifespan = LTV', 'wpshadow' ),
				'example'    => __( '$100/month × 24 months = $2,400 LTV', 'wpshadow' ),
				'benchmark'  => __( 'Higher is better (you want valuable customers)', 'wpshadow' ),
			),
			'ratio'      => array(
				'name'       => __( 'LTV:CAC Ratio (Most Important)', 'wpshadow' ),
				'formula'    => __( 'LTV / CAC = Ratio (should be 3:1 or better)', 'wpshadow' ),
				'example'    => __( '$2,400 LTV / $500 CAC = 4.8:1 (healthy)', 'wpshadow' ),
				'benchmark'  => __( '<1:1 = losing money, 1-3:1 = break even to ok, >3:1 = healthy', 'wpshadow' ),
			),
			'payback'    => array(
				'name'       => __( 'CAC Payback Period', 'wpshadow' ),
				'formula'    => __( 'CAC / Monthly Profit per Customer = Months to Payback', 'wpshadow' ),
				'example'    => __( '$500 CAC / $50 profit/month = 10 months payback', 'wpshadow' ),
				'benchmark'  => __( '<12 months is good, <6 months is excellent', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get levers to improve CAC/LTV.
	 *
	 * @since 0.6093.1200
	 * @return array Improvement levers.
	 */
	private static function get_improvement_levers() {
		return array(
			'lower_cac'  => array(
				'lever'     => __( 'Lower CAC (Reduce Acquisition Cost)', 'wpshadow' ),
				'tactics'   => array(
					__( 'Organic growth (content, SEO, word-of-mouth)', 'wpshadow' ),
					__( 'Improve conversion rates (better landing pages)', 'wpshadow' ),
					__( 'Target cheaper channels (email vs paid ads)', 'wpshadow' ),
					__( 'Better targeting (focus on high-intent segments)', 'wpshadow' ),
				),
			),
			'raise_ltv'  => array(
				'lever'     => __( 'Raise LTV (Increase Customer Value)', 'wpshadow' ),
				'tactics'   => array(
					__( 'Improve retention (reduce churn)', 'wpshadow' ),
					__( 'Increase price (capture more value)', 'wpshadow' ),
					__( 'Upsell/cross-sell (add more products)', 'wpshadow' ),
					__( 'Extend lifespan (make customers stay longer)', 'wpshadow' ),
				),
			),
		);
	}
}
