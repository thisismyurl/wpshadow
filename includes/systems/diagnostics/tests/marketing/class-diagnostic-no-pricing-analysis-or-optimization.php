<?php
/**
 * No Pricing Analysis or Optimization Diagnostic
 *
 * Checks if pricing is analyzed and optimized.
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
 * Pricing Analysis Diagnostic
 *
 * Pricing is one of the highest-leverage decisions.
 * A 10% price increase = 25-50% profit increase.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Pricing_Analysis_Or_Optimization extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-pricing-analysis-optimization';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Pricing Analysis/Optimization';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if pricing is analyzed and optimized';

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
		if ( ! self::has_pricing_analysis() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No pricing analysis or optimization detected. Pricing is highest-leverage decision: 10% increase = 25-50% profit increase. Analyze: 1) Cost (what does it cost to deliver?), 2) Competitor pricing (what do they charge?), 3) Customer willingness to pay (max they\'d pay?), 4) Value (what\'s the value/savings?). Pricing methods: Cost+ (cost × markup), Value-based (% of value created), Competitive (match market), Psychological ($99 vs $100). Test: Try 10% increase with small segment. Do conversions drop >10%? If not, raise prices. Most underpriced. Review annually.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/pricing-analysis-optimization',
				'details'     => array(
					'issue'          => __( 'No pricing analysis or optimization detected', 'wpshadow' ),
					'recommendation' => __( 'Implement pricing analysis and optimization strategy', 'wpshadow' ),
					'business_impact' => __( '10% price increase = 25-50% profit increase potential', 'wpshadow' ),
					'analysis_framework' => self::get_analysis_framework(),
					'pricing_methods' => self::get_pricing_methods(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if pricing analysis exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if analysis detected, false otherwise.
	 */
	private static function has_pricing_analysis() {
		$pricing_posts = self::count_posts_by_keywords(
			array(
				'pricing',
				'price',
				'cost',
				'value',
				'willingness to pay',
			)
		);

		return $pricing_posts > 0;
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
	 * Get analysis framework.
	 *
	 * @since  1.6035.0000
	 * @return array Analysis framework.
	 */
	private static function get_analysis_framework() {
		return array(
			'cost'       => __( 'Cost Analysis: What does it cost to deliver? (COGS + allocated overhead)', 'wpshadow' ),
			'competitor' => __( 'Competitive Analysis: What do competitors charge? (create pricing chart)', 'wpshadow' ),
			'value'      => __( 'Value Analysis: What value does it create? (what\'s the ROI/savings?)', 'wpshadow' ),
			'wtp'        => __( 'Willingness to Pay: Interview customers. "Would you pay $X?" Increase until hesitation.', 'wpshadow' ),
		);
	}

	/**
	 * Get pricing methods.
	 *
	 * @since  1.6035.0000
	 * @return array Pricing methods.
	 */
	private static function get_pricing_methods() {
		return array(
			'cost_plus'   => __( 'Cost+: (Cost × Markup) e.g., cost $10, markup 3x = $30', 'wpshadow' ),
			'value_based' => __( 'Value-Based: Price based on value created (e.g., saves $100/month, charge $25-50/mo)', 'wpshadow' ),
			'competitive' => __( 'Competitive: Match market rate (middle ground)', 'wpshadow' ),
			'psychological' => __( 'Psychological: $99 vs $100 (charm pricing, anchoring)', 'wpshadow' ),
			'tiered'      => __( 'Tiered: Multiple price points (basic/pro/enterprise)', 'wpshadow' ),
		);
	}
}
