<?php
/**
 * Upsell Optimization Diagnostic
 *
 * Tests whether the site strategically offers higher-value alternatives that customers
 * genuinely find valuable. Smart upsells increase revenue without harming experience.
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
 * Diagnostic_Optimizes_Upsell_Offers Class
 *
 * Diagnostic #5: Upsell Optimization from Specialized & Emerging Success Habits.
 * Checks if the site implements strategic upsell offers.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Optimizes_Upsell_Offers extends Diagnostic_Base {

	protected static $slug = 'optimizes-upsell-offers';
	protected static $title = 'Upsell Optimization';
	protected static $description = 'Tests whether the site strategically offers higher-value alternatives';
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

		// Check upsell products configured.
		$products_with_upsells = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 50,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'     => '_upsell_ids',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		if ( count( $products_with_upsells ) >= 10 ) {
			$score += 2;
			$score_details[] = sprintf(
				/* translators: %d: number of products */
				__( '✓ %d+ products with upsells configured', 'wpshadow' ),
				count( $products_with_upsells )
			);
		} elseif ( ! empty( $products_with_upsells ) ) {
			++$score;
			$score_details[]   = sprintf( __( '◐ %d product(s) with upsells', 'wpshadow' ), count( $products_with_upsells ) );
			$recommendations[] = __( 'Configure upsells for at least 50% of your product catalog', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No upsell products configured', 'wpshadow' );
			$recommendations[] = __( 'Set up upsells on product pages linking to premium/upgraded versions', 'wpshadow' );
		}

		// Check upsell plugins.
		$upsell_plugins = array(
			'woocommerce-one-click-upsell-funnel/woocommerce-one-click-upsell-funnel.php',
			'checkout-upsell-woocommerce/checkout-upsell-woocommerce.php',
		);

		$has_upsell_plugin = false;
		foreach ( $upsell_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_upsell_plugin = true;
				++$score;
				$score_details[] = __( '✓ Upsell optimization plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_upsell_plugin ) {
			$score_details[]   = __( '✗ No upsell plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install an upsell plugin for cart/checkout upsell offers', 'wpshadow' );
		}

		// Check upgrade/premium tier messaging.
		$upgrade_content = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				's'              => 'upgrade premium deluxe pro',
			)
		);

		if ( ! empty( $upgrade_content ) ) {
			++$score;
			$score_details[] = __( '✓ Premium/upgrade tiers available', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No premium tier products', 'wpshadow' );
			$recommendations[] = __( 'Create higher-value product versions to upsell to', 'wpshadow' );
		}

		// Check comparison/benefit messaging.
		$comparison_content = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'compare upgrade benefit includes',
			)
		);

		if ( ! empty( $comparison_content ) ) {
			++$score;
			$score_details[] = __( '✓ Comparison/benefit messaging present', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No upgrade benefit communication', 'wpshadow' );
			$recommendations[] = __( 'Clearly communicate the value of upgrading (e.g., "Upgrade for 2x capacity")', 'wpshadow' );
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
				__( 'Upsell optimization score: %d%%. Strategic upsells increase AOV by 20-40%% when the upgrade provides clear value. 70%% of customers appreciate relevant upgrade suggestions. Focus on 25-50%% price increase for optimal acceptance rates.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/upsell-optimization',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Effective upsells help customers discover better solutions while dramatically increasing per-transaction revenue.', 'wpshadow' ),
		);
	}
}
