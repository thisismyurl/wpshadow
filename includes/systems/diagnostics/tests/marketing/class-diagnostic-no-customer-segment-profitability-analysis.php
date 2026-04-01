<?php
/**
 * No Customer Segment Profitability Analysis Diagnostic
 *
 * Checks whether customer segment profitability is measured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Segment Profitability Diagnostic
 *
 * Detects when businesses are not measuring which customer segments are most
 * profitable. Segment insights help focus time and marketing on the best-fit
 * customers.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Customer_Segment_Profitability_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-segment-profitability-analysis';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Customer Segment Profitability Tracked';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether segment-level profitability is being measured';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_segments = self::has_segment_analysis();

		if ( ! $has_segments ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Segment profitability isn\'t visible yet. When every customer looks the same, it\'s easy to spend time on low-value segments. A simple segment view (by product, industry, or plan) helps you focus on the customers who drive the most impact.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/customer-segment-profitability?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'segmentation_detected' => false,
					'recommendation'        => __( 'Track revenue, cost, and retention by customer segment to identify your highest-value audiences.', 'wpshadow' ),
					'segment_examples'      => self::get_segment_examples(),
				),
			);
		}

		return null;
	}

	/**
	 * Check for evidence of segment analysis.
	 *
	 * @since 0.6093.1200
	 * @return bool True if segment analysis appears to exist.
	 */
	private static function has_segment_analysis(): bool {
		$keywords = array(
			'segment',
			'segmentation',
			'cohort',
			'lifetime value',
			'ltv',
			'customer tier',
		);

		if ( self::count_posts_by_keywords( $keywords ) > 0 ) {
			return true;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();
		$segment_plugins = array(
			'woocommerce-admin/woocommerce-admin.php',
			'woocommerce/woocommerce.php',
			'metorik/woocommerce-metorik.php',
			'woo-customer-history/woo-customer-history.php',
		);

		foreach ( $segment_plugins as $plugin_file ) {
			if ( isset( $plugins[ $plugin_file ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Count posts/pages containing any keyword.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return int Count of matching posts/pages.
	 */
	private static function count_posts_by_keywords( array $keywords ): int {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$matches = get_posts( array(
				'post_type'   => array( 'page', 'post' ),
				'numberposts' => 5,
				's'           => $keyword,
			) );

			$total += count( $matches );
		}

		return $total;
	}

	/**
	 * Provide examples of profitable segments to track.
	 *
	 * @since 0.6093.1200
	 * @return array Example segments.
	 */
	private static function get_segment_examples(): array {
		return array(
			__( 'By product line or service tier', 'wpshadow' ),
			__( 'By customer size (small business, mid-market, enterprise)', 'wpshadow' ),
			__( 'By acquisition channel (search, referrals, paid ads)', 'wpshadow' ),
			__( 'By region or industry', 'wpshadow' ),
			__( 'By repeat purchase behavior', 'wpshadow' ),
		);
	}
}
