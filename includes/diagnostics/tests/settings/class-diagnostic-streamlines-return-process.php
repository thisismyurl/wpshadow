<?php
/**
 * Return Process Streamlined Diagnostic
 *
 * Tests whether the site offers an easy, clear return process that builds customer
 * confidence. Easy returns reduce purchase anxiety and increase conversion rates.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5003.1145
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Streamlines_Return_Process Class
 *
 * Diagnostic #9: Return Process Streamlined from Specialized & Emerging Success Habits.
 * Checks if the site has a clear, easy return policy and process.
 *
 * @since 1.5003.1145
 */
class Diagnostic_Streamlines_Return_Process extends Diagnostic_Base {

	protected static $slug = 'streamlines-return-process';
	protected static $title = 'Return Process Streamlined';
	protected static $description = 'Tests whether the site offers an easy, clear return process that builds confidence';
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

		// Check return policy page.
		$return_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'return policy refund exchange',
			)
		);

		if ( ! empty( $return_pages ) ) {
			++$score;
			$score_details[] = __( '✓ Return policy page exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No return policy page found', 'wpshadow' );
			$recommendations[] = __( 'Create a clear return/refund policy page outlining your process and timeframes', 'wpshadow' );
		}

		// Check return timeframe mentioned.
		$timeframe_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => '30 days 60 days return window',
			)
		);

		if ( ! empty( $timeframe_content ) ) {
			++$score;
			$score_details[] = __( '✓ Return timeframe clearly stated', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No return timeframe specified', 'wpshadow' );
			$recommendations[] = __( 'Specify return window (e.g., "30-day money-back guarantee") prominently', 'wpshadow' );
		}

		// Check returns plugin/system.
		$return_plugins = array(
			'yith-woocommerce-rma/init.php',
			'woocommerce-returns-warranty-requests/woocommerce-warranty.php',
		);

		$has_return_plugin = false;
		foreach ( $return_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_return_plugin = true;
				++$score;
				$score_details[] = __( '✓ Return management system active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_return_plugin ) {
			$score_details[]   = __( '✗ No automated return system', 'wpshadow' );
			$recommendations[] = __( 'Install YITH Returns or similar plugin to streamline return requests', 'wpshadow' );
		}

		// Check prepaid return label mentions.
		$prepaid_returns = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'prepaid label free return shipping',
			)
		);

		if ( ! empty( $prepaid_returns ) ) {
			++$score;
			$score_details[] = __( '✓ Prepaid return shipping offered', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No prepaid return shipping mentioned', 'wpshadow' );
			$recommendations[] = __( 'Offer free return shipping with prepaid labels to reduce friction', 'wpshadow' );
		}

		// Check satisfaction guarantee.
		$guarantee_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'satisfaction guarantee money back',
			)
		);

		if ( ! empty( $guarantee_content ) ) {
			++$score;
			$score_details[] = __( '✓ Satisfaction guarantee present', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No satisfaction guarantee', 'wpshadow' );
			$recommendations[] = __( 'Add a "100% Satisfaction Guarantee" badge to build trust', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Return process score: %d%%. Easy returns increase conversion by 35%% and customer loyalty by 28%%. 67%% of shoppers check return policies before buying, and hassle-free returns reduce negative reviews by 50%%.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/return-process',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Streamlined returns reduce purchase anxiety and turn potentially negative experiences into positive brand interactions.', 'wpshadow' ),
		);
	}
}
