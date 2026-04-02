<?php
/**
 * No Pricing Strategy Documentation Diagnostic
 *
 * Checks if pricing strategy is documented and optimized.
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
 * Pricing Strategy Documentation Diagnostic
 *
 * A 1% price increase can boost profits by 11% (McKinsey).
 * Most businesses don't systematically test and optimize pricing.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Pricing_Strategy_Documentation extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-pricing-strategy-documentation';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Pricing Strategy Documentation';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if pricing strategy is documented and optimized';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_pricing_strategy() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No pricing strategy documentation detected. A 1% price increase boosts profits by 11% (McKinsey), yet most businesses never optimize pricing. Document: 1) Pricing model (value-based, competitive, cost-plus), 2) Price tiers (good-better-best, freemium), 3) Price testing results (A/B tests, experiments), 4) Discount strategy (when/how much), 5) Competitor pricing analysis, 6) Customer willingness-to-pay data, 7) Price sensitivity by segment. Strategic pricing is the fastest lever for profit growth.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/pricing-strategy-documentation',
				'details'     => array(
					'issue'               => __( 'No pricing strategy documentation detected', 'wpshadow' ),
					'recommendation'      => __( 'Document pricing strategy and conduct regular pricing optimization tests', 'wpshadow' ),
					'business_impact'     => __( 'Missing up to 11% profit increase from optimized pricing', 'wpshadow' ),
					'pricing_models'      => self::get_pricing_models(),
					'optimization_tests'  => self::get_optimization_tests(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if pricing strategy exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if strategy detected, false otherwise.
	 */
	private static function has_pricing_strategy() {
		// Check for pricing strategy content
		$pricing_posts = self::count_posts_by_keywords(
			array(
				'pricing strategy',
				'price optimization',
				'pricing model',
				'pricing tiers',
				'price testing',
				'willingness to pay',
			)
		);

		if ( $pricing_posts > 0 ) {
			return true;
		}

		// Check for pricing plugins (A/B testing, dynamic pricing)
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$pricing_keywords = array(
			'pricing',
			'dynamic price',
			'price test',
			'discount rule',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $pricing_keywords as $keyword ) {
				if ( false !== strpos( $plugin_name, $keyword ) ) {
					if ( is_plugin_active( $plugin_file ) ) {
						return true;
					}
				}
			}
		}

		return false;
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
	 * Get pricing models.
	 *
	 * @since 1.6093.1200
	 * @return array Pricing models with descriptions.
	 */
	private static function get_pricing_models() {
		return array(
			'value_based'      => __( 'Price based on customer value (what they\'ll pay for outcomes)', 'wpshadow' ),
			'tiered'           => __( 'Good-Better-Best tiers (3 options, middle most popular)', 'wpshadow' ),
			'freemium'         => __( 'Free base + paid upgrades (land-and-expand)', 'wpshadow' ),
			'usage_based'      => __( 'Pay per use (aligns cost with value, scales with customer)', 'wpshadow' ),
			'subscription'     => __( 'Recurring revenue (predictable, compounds over time)', 'wpshadow' ),
			'competitive'      => __( 'Match or undercut competitors (commodity pricing)', 'wpshadow' ),
			'penetration'      => __( 'Low initial price to gain market share fast', 'wpshadow' ),
			'premium'          => __( 'High price signals quality (luxury positioning)', 'wpshadow' ),
		);
	}

	/**
	 * Get pricing optimization tests.
	 *
	 * @since 1.6093.1200
	 * @return array Optimization test types.
	 */
	private static function get_optimization_tests() {
		return array(
			'ab_test_price'      => __( 'A/B test different price points (measure conversion)', 'wpshadow' ),
			'anchoring'          => __( 'Test anchor pricing (high option makes middle look attractive)', 'wpshadow' ),
			'bundling'           => __( 'Test bundled vs individual pricing (perceived value)', 'wpshadow' ),
			'payment_frequency'  => __( 'Test monthly vs annual pricing ($99/mo vs $999/yr)', 'wpshadow' ),
			'currency_display'   => __( 'Test $99 vs $99.00 vs 99 dollars (psychology)', 'wpshadow' ),
			'discount_depth'     => __( 'Test 10% vs 20% vs 30% discounts (revenue vs volume)', 'wpshadow' ),
			'van_westendorp'     => __( 'Survey: too cheap, cheap, expensive, too expensive', 'wpshadow' ),
			'conjoint_analysis'  => __( 'Test feature/price combinations to find optimal', 'wpshadow' ),
		);
	}
}
