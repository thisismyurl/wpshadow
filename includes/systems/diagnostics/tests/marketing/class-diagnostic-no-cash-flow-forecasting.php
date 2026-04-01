<?php
/**
 * No Cash Flow Forecasting Diagnostic
 *
 * Checks if cash flow forecasting exists.
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
 * Cash Flow Forecasting Diagnostic
 *
 * Profit doesn't equal cash. Many companies fail with positive profit.
 * Cash flow is oxygen.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Cash_Flow_Forecasting extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-cash-flow-forecasting';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Cash Flow Forecasting';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cash flow forecasting exists';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_cash_flow_forecast() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No cash flow forecasting detected. Profit ≠ cash. Many companies fail with positive profit (cash was slow/tied up). Cash flow is oxygen. Track: 1) Cash in (when do customers pay? immediate? net-30? net-60?), 2) Cash out (when do you pay employees? suppliers? rent?), 3) Timing gaps (e.g., owe suppliers in 30 days but customers pay in 60 = cash problem), 4) Seasonal patterns (vacation rental more in summer? gift sales in December?). Forecast 12 months ahead. Use: Operating activities (revenue minus expenses), investing (buying equipment), financing (loans, investors). When cash dips below reserves, you can\'t operate. Mitigate: Invoice faster (reduce payment terms), delay payables (negotiate terms), get line of credit, build reserves.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cash-flow-forecasting?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'          => __( 'No cash flow forecasting detected', 'wpshadow' ),
					'recommendation' => __( 'Implement 12-month cash flow forecasting', 'wpshadow' ),
					'business_impact' => __( 'Risk of cash shortage despite profitability', 'wpshadow' ),
					'forecast_components' => self::get_forecast_components(),
					'mitigation'     => self::get_mitigation_strategies(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if cash flow forecast exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if forecast detected, false otherwise.
	 */
	private static function has_cash_flow_forecast() {
		$cash_posts = self::count_posts_by_keywords(
			array(
				'cash flow',
				'cash forecast',
				'payment terms',
				'cash position',
				'liquidity',
			)
		);

		return $cash_posts > 0;
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
	 * Get forecast components.
	 *
	 * @since 0.6093.1200
	 * @return array Forecast components.
	 */
	private static function get_forecast_components() {
		return array(
			'cash_in'    => __( 'Cash In: When do customers pay? (terms: immediate, net-30, net-60, net-90)', 'wpshadow' ),
			'cash_out'   => __( 'Cash Out: Salaries (bi-weekly), suppliers (net-30), rent (monthly), taxes (quarterly)', 'wpshadow' ),
			'timing'     => __( 'Timing Gap: If you collect in 60 days but owe in 30, you have a 30-day gap', 'wpshadow' ),
			'seasonal'   => __( 'Seasonal: Revenue highs/lows by month (Dec holidays, summer vacations)', 'wpshadow' ),
			'beginning'  => __( 'Beginning Cash: Start with current cash balance', 'wpshadow' ),
			'ending'     => __( 'Ending Cash: Cash in - cash out = ending position (minimum needed?)', 'wpshadow' ),
		);
	}

	/**
	 * Get mitigation strategies.
	 *
	 * @since 0.6093.1200
	 * @return array Mitigation strategies for cash shortages.
	 */
	private static function get_mitigation_strategies() {
		return array(
			'invoice'      => __( '1. Accelerate Collections: Invoice immediately, offer early payment discounts', 'wpshadow' ),
			'terms'        => __( '2. Negotiate Terms: Ask suppliers for net-60 instead of net-30', 'wpshadow' ),
			'credit_line'  => __( '3. Line of Credit: Get $X credit line for seasonal gaps', 'wpshadow' ),
			'reserves'     => __( '4. Cash Reserves: Keep 3-6 months expenses in reserve', 'wpshadow' ),
			'timing'       => __( '5. Manage Timing: Delay big expenses until after revenue collection', 'wpshadow' ),
			'funding'      => __( '6. Raise Capital: If growing fast, investor money bridges cash gaps', 'wpshadow' ),
		);
	}
}
