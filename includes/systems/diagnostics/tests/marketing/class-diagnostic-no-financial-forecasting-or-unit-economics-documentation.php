<?php
/**
 * No Financial Forecasting or Unit Economics Documentation Diagnostic
 *
 * Checks if financial forecasting and unit economics are documented.
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
 * Financial Forecasting Diagnostic
 *
 * Companies that forecast outperform those that don't by 2-3x.
 * Know your unit economics to scale.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Financial_Forecasting_Or_Unit_Economics_Documentation extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-financial-forecasting-unit-economics';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Financial Forecasting/Unit Economics';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if financial forecasting and unit economics are documented';

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
		if ( ! self::has_financial_forecast() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No financial forecast or unit economics documented. You\'re flying blind. Companies that forecast outperform 2-3x. Document: 1) Unit economics (revenue - costs = margin per customer), 2) Gross margin % (after COGS/production), 3) Operating margin % (after all costs), 4) CAC, LTV, payback period (can we afford to acquire?), 5) Monthly forecast (revenue, costs, profit 12 months ahead), 6) Scenarios (best case, likely, worst case). Example: Gross margin 70%, Operating margin 20%, CAC $1000, LTV $5000 = sustainable. If costs exceed margin, business doesn\'t work. Forecast helps: Identify breakeven, know if you\'ll run out of cash, plan hiring, set pricing. Review monthly and adjust.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/financial-forecasting-unit-economics',
				'details'     => array(
					'issue'               => __( 'No financial forecasting or unit economics documented', 'wpshadow' ),
					'recommendation'      => __( 'Document unit economics and create 12-month financial forecast', 'wpshadow' ),
					'business_impact'     => __( 'Missing 2-3x better decision making from financial visibility', 'wpshadow' ),
					'key_metrics'         => self::get_key_metrics(),
					'forecast_structure'  => self::get_forecast_structure(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if financial forecast exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if forecast detected, false otherwise.
	 */
	private static function has_financial_forecast() {
		// Check for financial content
		$financial_posts = self::count_posts_by_keywords(
			array(
				'financial forecast',
				'unit economics',
				'margin',
				'revenue forecast',
				'financial plan',
			)
		);

		return $financial_posts > 0;
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
	 * Get key financial metrics.
	 *
	 * @since  1.6035.0000
	 * @return array Key financial metrics with definitions.
	 */
	private static function get_key_metrics() {
		return array(
			'gross_margin'   => array(
				'metric'    => __( 'Gross Margin %', 'wpshadow' ),
				'formula'   => __( '(Revenue - COGS) / Revenue × 100', 'wpshadow' ),
				'example'   => __( '$10,000 revenue - $3,000 cost = 70% gross margin', 'wpshadow' ),
				'benchmark' => __( '>60% = healthy for SaaS', 'wpshadow' ),
			),
			'operating_margin' => array(
				'metric'    => __( 'Operating Margin %', 'wpshadow' ),
				'formula'   => __( '(Revenue - All Costs) / Revenue × 100', 'wpshadow' ),
				'example'   => __( '$10,000 - $8,000 total costs = 20% operating margin', 'wpshadow' ),
				'benchmark' => __( '>15% = healthy SaaS at scale', 'wpshadow' ),
			),
			'runway'         => array(
				'metric'    => __( 'Runway (Months of Cash)', 'wpshadow' ),
				'formula'   => __( 'Cash in Bank / Monthly Burn Rate', 'wpshadow' ),
				'example'   => __( '$100,000 cash / $10,000 monthly burn = 10 months', 'wpshadow' ),
				'benchmark' => __( '>12 months = safe, <6 months = urgent', 'wpshadow' ),
			),
			'breakeven'      => array(
				'metric'    => __( 'Breakeven Point', 'wpshadow' ),
				'focus'     => __( 'At what revenue do we stop losing money?', 'wpshadow' ),
				'example'   => __( '$50,000 MRR breakeven (fixed costs covered)', 'wpshadow' ),
				'action'    => __( 'Know date to breakeven (plan to reach it)', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get forecast structure.
	 *
	 * @since  1.6035.0000
	 * @return array Forecast structure components.
	 */
	private static function get_forecast_structure() {
		return array(
			'revenue'    => array(
				'items'     => __( 'Revenue forecast: MRR growth by product/segment', 'wpshadow' ),
				'approach'  => __( 'Conservative (7-10% MoM), likely (15%), optimistic (25%)', 'wpshadow' ),
			),
			'cogs'       => array(
				'items'     => __( 'Cost of Goods Sold (production, infrastructure, shipping)', 'wpshadow' ),
				'approach'  => __( 'Variable costs (increases with sales)', 'wpshadow' ),
			),
			'personnel'  => array(
				'items'     => __( 'Salaries & benefits (biggest cost usually)', 'wpshadow' ),
				'approach'  => __( 'Assume 2-3 hires per quarter, salary + 30% benefits', 'wpshadow' ),
			),
			'marketing'  => array(
				'items'     => __( 'Marketing spend (ads, content, tools)', 'wpshadow' ),
				'approach'  => __( 'Allocate % of revenue (10-30% typical)', 'wpshadow' ),
			),
			'ops'        => array(
				'items'     => __( 'Operations (tools, rent, utilities, insurance)', 'wpshadow' ),
				'approach'  => __( 'Fixed base + variable', 'wpshadow' ),
			),
			'scenarios'  => array(
				'items'     => __( 'Model 3 scenarios: Best, Likely, Worst', 'wpshadow' ),
				'approach'  => __( 'Help understand range of outcomes', 'wpshadow' ),
			),
		);
	}
}
