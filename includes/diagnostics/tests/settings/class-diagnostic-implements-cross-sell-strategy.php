<?php
/**
 * Cross-Sell Strategy Active Diagnostic
 *
 * Tests whether the site implements an effective cross-sell strategy that increases
 * average order value through relevant product suggestions. Cross-sells add complementary items.
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
 * Diagnostic_Implements_Cross_Sell_Strategy Class
 *
 * Diagnostic #4: Cross-Sell Strategy Active from Specialized & Emerging Success Habits.
 * Checks if the site implements effective cross-selling.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Implements_Cross_Sell_Strategy extends Diagnostic_Base {

	protected static $slug = 'implements-cross-sell-strategy';
	protected static $title = 'Cross-Sell Strategy Active';
	protected static $description = 'Tests whether the site implements an effective cross-sell strategy';
	protected static $family = 'ecommerce-optimization';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check WooCommerce active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null;
		}

		// Check cross-sell products configured.
		$products_with_crosssells = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 50,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'     => '_crosssell_ids',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		if ( count( $products_with_crosssells ) >= 10 ) {
			$score += 2;
			$score_details[] = sprintf(
				/* translators: %d: number of products */
				__( '✓ %d+ products with cross-sells configured', 'wpshadow' ),
				count( $products_with_crosssells )
			);
		} elseif ( ! empty( $products_with_crosssells ) ) {
			++$score;
			$score_details[]   = sprintf( __( '◐ %d product(s) with cross-sells', 'wpshadow' ), count( $products_with_crosssells ) );
			$recommendations[] = __( 'Add cross-sell recommendations to more products', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No cross-sell products configured', 'wpshadow' );
			$recommendations[] = __( 'Configure cross-sells for complementary products (e.g., camera + memory card)', 'wpshadow' );
		}

		// Check related products.
		$related_enabled = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		if ( ! empty( $related_enabled ) ) {
			++$score;
			$score_details[] = __( '✓ Related products feature available', 'wpshadow' );
		}

		// Check "customers also bought" plugins.
		$recommendation_plugins = array(
			'woocommerce-frequently-bought-together/woocommerce-frequently-bought-together.php',
			'frequently-bought-together-for-woocommerce/frequently-bought-together-for-woocommerce.php',
		);

		$has_recommendation_plugin = false;
		foreach ( $recommendation_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_recommendation_plugin = true;
				++$score;
				$score_details[] = __( '✓ Product recommendation plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_recommendation_plugin ) {
			$score_details[]   = __( '✗ No recommendation engine', 'wpshadow' );
			$recommendations[] = __( 'Install "Frequently Bought Together" plugin for automated cross-sell suggestions', 'wpshadow' );
		}

		// Check cart cross-sells visibility.
		$cart_content = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'cart',
			)
		);

		if ( ! empty( $cart_content ) ) {
			++$score;
			$score_details[] = __( '✓ Cart page available for cross-sell display', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Cart page not optimized', 'wpshadow' );
			$recommendations[] = __( 'Display cross-sell recommendations on cart page before checkout', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 20;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 10;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Cross-sell strategy score: %d%%. Effective cross-sells increase AOV by 10-30%% and basket size by 15-25%%. "Customers also bought" recommendations convert 3x better than generic suggestions. Timing matters - cart page cross-sells convert at 12%%.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/cross-sell-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Cross-selling introduces customers to complementary products they need, creating more complete solutions and higher satisfaction.', 'wpshadow' ),
		);
	}
}
